<?php

namespace App\Jobs;

use App\Models\ChatSession;
use App\Models\LanguageInsight;
use App\Models\User;
use App\Services\LangGptService;
use App\Services\OpenAiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AnalyzeRecentMessages implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ChatSession $chatSession,
        public int $messageCount = 20
    ) {}

    /**
     * Execute the job.
     */
    public function handle(LangGptService $langGptService, OpenAiService $openAiService): void
    {
        $user = $this->chatSession->user;

        // Get recent user messages (not assistant messages)
        $messages = $this->chatSession->messages()
            ->where('sender_type', 'user')
            ->orderBy('created_at', 'desc')
            ->limit($this->messageCount)
            ->get()
            ->reverse()
            ->values();

        if ($messages->isEmpty()) {
            return;
        }

        // Filter messages to only include those in the target language
        $validMessages = [];
        foreach ($messages as $msg) {
            $detection = $openAiService->detectLanguage(
                $msg->content,
                $this->chatSession->target_language
            );

            // Only include messages that are in the target language
            if ($detection['is_target_language']) {
                $validMessages[] = [
                    'content' => $msg->content,
                    'timestamp' => $msg->created_at->toIso8601String(),
                ];
            }
        }

        // Need at least some valid messages to analyze
        if (empty($validMessages)) {
            Log::info('No valid target language messages to analyze', [
                'chat_session_id' => $this->chatSession->id,
                'total_messages_checked' => $messages->count(),
            ]);

            return;
        }

        // If session doesn't have a proficiency level set, use user's or default to B1
        $currentLevel = $this->chatSession->proficiency_level ?? $user->proficiency_level ?? 'B1';

        // Call LangGPT to evaluate progress
        $payload = [
            'messages' => $validMessages,
            'current_level' => $currentLevel,
            'target_language' => $this->chatSession->target_language,
            'native_language' => $this->chatSession->native_language,
            'localize_insights' => $this->chatSession->localize_insights ?? false,
        ];

        try {
            $response = $langGptService->evaluateProgress($payload);

            Log::info('LangGPT evaluate-progress response', [
                'chat_session_id' => $this->chatSession->id,
                'success' => $response['success'] ?? null,
                'has_data' => isset($response['data']),
                'status' => $response['status'] ?? null,
            ]);

            if (! $response['success']) {
                Log::warning('LangGPT progress evaluation failed', [
                    'chat_session_id' => $this->chatSession->id,
                    'response' => $response,
                ]);

                // Check if this is an API key authentication error
                if (isset($response['status']) && $response['status'] === 401) {
                    // Create a system insight to notify the user
                    LanguageInsight::create([
                        'user_id' => $user->id,
                        'chat_session_id' => $this->chatSession->id,
                        'insight_type' => 'system_error',
                        'title' => 'Insights Service Configuration Issue',
                        'message' => 'We\'re experiencing a temporary issue with the insights analysis service. Your conversations are being saved, and insights will be generated once the issue is resolved.',
                        'data' => [
                            'error_type' => 'api_authentication',
                            'status_code' => 401,
                        ],
                    ]);
                }

                return;
            }

            $analysis = $response['data'];

            // Track if this is the first proficiency level assignment
            $isInitialProficiencyAssignment = false;

            // If session didn't have a proficiency level, set it to the suggested level
            if (! $this->chatSession->proficiency_level && isset($analysis['suggested_level'])) {
                $this->chatSession->update(['proficiency_level' => $analysis['suggested_level']]);
                $isInitialProficiencyAssignment = true;

                Log::info('Set initial proficiency level from LangGPT analysis', [
                    'user_id' => $user->id,
                    'chat_session_id' => $this->chatSession->id,
                    'proficiency_level' => $analysis['suggested_level'],
                ]);
            }

            // Store insights
            $this->storeInsights($user, $analysis, $isInitialProficiencyAssignment);

            // Update proficiency if user opted in and LangGPT suggests a change
            if ($user->auto_update_proficiency && isset($analysis['suggested_level'])) {
                $this->updateProficiencyIfNeeded($user, $analysis);
            }
        } catch (\Exception $e) {
            Log::error('Error analyzing recent messages', [
                'chat_session_id' => $this->chatSession->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Store generated insights in database
     */
    private function storeInsights(User $user, array $analysis, bool $isInitialProficiencyAssignment = false): void
    {
        // Store grammar patterns insight
        if (! empty($analysis['grammar_patterns'])) {
            LanguageInsight::create([
                'user_id' => $user->id,
                'chat_session_id' => $this->chatSession->id,
                'insight_type' => 'grammar_pattern',
                'title' => 'Grammar Patterns Detected',
                'message' => $analysis['grammar_summary'] ?? 'We noticed some patterns in your grammar usage.',
                'data' => ['patterns' => $analysis['grammar_patterns']],
            ]);
        }

        // Store vocabulary strength insight
        if (! empty($analysis['vocabulary_assessment'])) {
            LanguageInsight::create([
                'user_id' => $user->id,
                'chat_session_id' => $this->chatSession->id,
                'insight_type' => 'vocabulary_strength',
                'title' => 'Vocabulary Assessment',
                'message' => $analysis['vocabulary_summary'] ?? 'Your vocabulary usage shows interesting patterns.',
                'data' => ['insights' => $analysis['vocabulary_assessment']],
            ]);
        }

        // Store proficiency suggestion if different from current OR if this is the initial assignment
        if (isset($analysis['suggested_level']) && ($isInitialProficiencyAssignment || $analysis['suggested_level'] !== $this->chatSession->proficiency_level)) {
            LanguageInsight::create([
                'user_id' => $user->id,
                'chat_session_id' => $this->chatSession->id,
                'insight_type' => 'proficiency_suggestion',
                'title' => $isInitialProficiencyAssignment ? 'Initial Proficiency Assessment' : 'Proficiency Level Update',
                'message' => $isInitialProficiencyAssignment
                    ? ($analysis['proficiency_message'] ?? "Based on your conversation, we've assessed your level as {$analysis['suggested_level']}.")
                    : ($analysis['proficiency_message'] ?? "Based on your recent progress, you might be ready for {$analysis['suggested_level']}!"),
                'data' => [
                    'current_level' => $this->chatSession->fresh()->proficiency_level, // Get fresh value after potential update
                    'suggested_level' => $analysis['suggested_level'],
                    'reasoning' => $analysis['reasoning'] ?? null,
                ],
            ]);
        }
    }

    /**
     * Update user proficiency if conditions are met
     */
    private function updateProficiencyIfNeeded(User $user, array $analysis): void
    {
        if (! isset($analysis['suggested_level']) || ! isset($analysis['confidence'])) {
            return;
        }

        // Only update if confidence is high enough (e.g., > 0.7)
        if ($analysis['confidence'] < 0.7) {
            return;
        }

        $suggestedLevel = $analysis['suggested_level'];
        $currentLevel = $this->chatSession->proficiency_level;

        // Only allow progression, not regression
        $levels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
        $currentIndex = array_search($currentLevel, $levels);
        $suggestedIndex = array_search($suggestedLevel, $levels);

        if ($suggestedIndex > $currentIndex) {
            $this->chatSession->update(['proficiency_level' => $suggestedLevel]);

            Log::info('Session proficiency updated automatically', [
                'user_id' => $user->id,
                'chat_session_id' => $this->chatSession->id,
                'from' => $currentLevel,
                'to' => $suggestedLevel,
                'confidence' => $analysis['confidence'],
            ]);
        }
    }
}
