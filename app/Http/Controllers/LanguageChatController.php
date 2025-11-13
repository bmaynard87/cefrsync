<?php

namespace App\Http\Controllers;

use App\Jobs\AnalyzeRecentMessages;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Services\OpenAiService;
use App\Services\LangGptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class LanguageChatController extends Controller
{
    public function __construct(
        protected OpenAiService $openAiService,
        protected LangGptService $langGptService
    ) {
    }

    public function index(Request $request)
    {
        $chatHistory = $request->user()
            ->chatSessions()
            ->latest('last_message_at')
            ->get()
            ->map(fn($session) => [
                'id' => $session->id,
                'title' => $session->title ?? 'New Conversation',
                'last_message_at' => $session->last_message_at,
                'native_language' => $session->native_language,
                'target_language' => $session->target_language,
            ]);

        $user = $request->user();
        $userSettings = [
            'native_language' => $user->native_language ?? 'Spanish',
            'target_language' => $user->target_language ?? 'English',
            'proficiency_level' => $user->proficiency_level ?? 'B1',
        ];

        return Inertia::render('LanguageChat', [
            'chatHistory' => $chatHistory,
            'userSettings' => $userSettings,
        ]);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'native_language' => 'required|string|max:255',
            'target_language' => 'required|string|max:255',
            'proficiency_level' => 'required|string|max:255',
        ]);

        $session = $request->user()->chatSessions()->create([
            'native_language' => $validated['native_language'],
            'target_language' => $validated['target_language'],
            'proficiency_level' => $validated['proficiency_level'],
            'title' => 'New Conversation',
            'last_message_at' => now(),
        ]);

        return response()->json([
            'id' => $session->id,
            'native_language' => $session->native_language,
            'target_language' => $session->target_language,
            'proficiency_level' => $session->proficiency_level,
            'created_at' => $session->created_at,
        ]);
    }

    public function sendMessage(Request $request, ChatSession $chatSession)
    {
        Gate::authorize('view', $chatSession);

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        // Create user message
        $userMessage = $chatSession->messages()->create([
            'sender_type' => 'user',
            'content' => $validated['message'],
        ]);

        // Get conversation history for context
        $conversationHistory = $chatSession->messages()
            ->orderBy('created_at')
            ->get(['sender_type', 'content'])
            ->toArray();

        $formattedHistory = $this->openAiService->formatConversationHistory($conversationHistory);

        // Build session context from summary and topics
        $sessionContext = $this->buildSessionContext($chatSession);

        // Build user context (can be enhanced later with user preferences)
        $userContext = $this->buildUserContext($request->user());

        // Use user's proficiency level instead of session's
        $proficiencyLevel = $request->user()->proficiency_level ?? 'B1';

        // Log for debugging
        \Log::info('Generating chat response', [
            'target_language' => $chatSession->target_language,
            'proficiency_level' => $proficiencyLevel,
            'session_id' => $chatSession->id,
        ]);

        // Generate AI response using OpenAI with enhanced memory
        $aiResponseContent = $this->openAiService->generateChatResponse(
            $formattedHistory,
            $chatSession->target_language,
            $proficiencyLevel,
            $sessionContext,
            $userContext
        );

        // Create AI message
        $aiMessage = $chatSession->messages()->create([
            'sender_type' => 'assistant',
            'content' => $aiResponseContent,
        ]);

        // Update session timestamp
        $chatSession->update([
            'last_message_at' => now(),
        ]);

        // Generate title from first user message
        $messageCount = $chatSession->messages()->where('sender_type', 'user')->count();
        $newTitle = null;
        if ($messageCount === 1) {
            $title = $this->openAiService->generateChatTitle($validated['message']);
            $chatSession->update(['title' => $title]);
            $newTitle = $title;
        }

        // Periodically update conversation summary (every 10 messages)
        if ($chatSession->messages()->count() % 10 === 0) {
            $this->updateSessionSummary($chatSession, $formattedHistory);

            // Dispatch job to analyze recent messages for insights
            AnalyzeRecentMessages::dispatch($chatSession, 20);
        }

        return response()->json([
            'user_message' => [
                'id' => $userMessage->id,
                'content' => $userMessage->content,
                'created_at' => $userMessage->created_at,
            ],
            'ai_response' => [
                'id' => $aiMessage->id,
                'content' => $aiMessage->content,
                'created_at' => $aiMessage->created_at,
            ],
            'new_title' => $newTitle,
        ]);
    }

    /**
     * Build session-specific context for AI memory
     */
    protected function buildSessionContext(ChatSession $chatSession): ?string
    {
        $context = [];

        if ($chatSession->conversation_summary) {
            $context[] = "Previous conversation summary: " . $chatSession->conversation_summary;
        }

        if ($chatSession->topics_discussed && count($chatSession->topics_discussed) > 0) {
            $topics = implode(', ', $chatSession->topics_discussed);
            $context[] = "Topics discussed: " . $topics;
        }

        return empty($context) ? null : implode("\n", $context);
    }

    /**
     * Build user-specific context for AI memory
     */
    protected function buildUserContext($user): ?string
    {
        // For now, just basic info. Can be enhanced with learning patterns later
        return "Learner name: " . $user->name;
    }

    /**
     * Update conversation summary periodically
     */
    protected function updateSessionSummary(ChatSession $chatSession, array $formattedHistory): void
    {
        $summary = $this->openAiService->summarizeConversation(
            $formattedHistory,
            $chatSession->target_language
        );

        if ($summary) {
            $chatSession->update([
                'conversation_summary' => $summary,
            ]);
        }
    }

    public function history(Request $request)
    {
        $sessions = $request->user()
            ->chatSessions()
            ->latest('last_message_at')
            ->get()
            ->map(fn($session) => [
                'id' => $session->id,
                'title' => $session->title ?? 'New Conversation',
                'last_message_at' => $session->last_message_at,
                'native_language' => $session->native_language,
                'target_language' => $session->target_language,
            ]);

        return response()->json([
            'sessions' => $sessions,
        ]);
    }

    public function messages(Request $request, ChatSession $chatSession)
    {
        Gate::authorize('view', $chatSession);

        $messages = $chatSession->messages()
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'messages' => $messages,
            'session' => [
                'id' => $chatSession->id,
                'native_language' => $chatSession->native_language,
                'target_language' => $chatSession->target_language,
                'proficiency_level' => $chatSession->proficiency_level,
            ],
        ]);
    }

    public function destroy(Request $request, ChatSession $chatSession)
    {
        Gate::authorize('view', $chatSession);

        $chatSession->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chat session deleted successfully',
        ]);
    }

    public function updateTitle(Request $request, ChatSession $chatSession)
    {
        Gate::authorize('view', $chatSession);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $chatSession->update([
            'title' => $validated['title'],
        ]);

        return response()->json([
            'success' => true,
            'title' => $chatSession->title,
        ]);
    }

    public function updateParameters(Request $request, ChatSession $chatSession)
    {
        Gate::authorize('view', $chatSession);

        $validated = $request->validate([
            'native_language' => 'required|string|max:255',
            'target_language' => 'required|string|max:255',
            'proficiency_level' => 'required|string|in:A1,A2,B1,B2,C1,C2',
        ]);

        $chatSession->update([
            'native_language' => $validated['native_language'],
            'target_language' => $validated['target_language'],
            'proficiency_level' => $validated['proficiency_level'],
        ]);

        return back();
    }

    /**
     * Detect the language of a message
     */
    public function detectLanguage(Request $request, ChatSession $chatSession)
    {
        Gate::authorize('view', $chatSession);

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $result = $this->openAiService->detectLanguage(
            $validated['message'],
            $chatSession->target_language
        );

        return response()->json($result);
    }

    /**
     * Analyze user messages and prepare valid target language messages for LangGPT
     */
    public function analyzeForLangGpt(Request $request, ChatSession $chatSession)
    {
        Gate::authorize('view', $chatSession);

        $validated = $request->validate([
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        $limit = $validated['limit'] ?? 20;

        // Get recent user messages
        $userMessages = $chatSession->messages()
            ->where('sender_type', 'user')
            ->latest()
            ->limit($limit)
            ->get();

        $validMessages = [];
        $invalidMessages = [];
        $processedCount = 0;

        foreach ($userMessages as $message) {
            $processedCount++;

            // Detect language for each message
            $detection = $this->openAiService->detectLanguage(
                $message->content,
                $chatSession->target_language
            );

            if ($detection['is_target_language']) {
                $validMessages[] = [
                    'id' => $message->id,
                    'content' => $message->content,
                    'created_at' => $message->created_at,
                    'detected_language' => $detection['detected_language'],
                ];
            } else {
                $invalidMessages[] = [
                    'id' => $message->id,
                    'content' => $message->content,
                    'created_at' => $message->created_at,
                    'detected_language' => $detection['detected_language'],
                    'expected_language' => $chatSession->target_language,
                ];
            }
        }

        // Prepare data for LangGPT
        $langGptPayload = [
            'target_language' => $chatSession->target_language,
            'proficiency_level' => $chatSession->proficiency_level,
            'messages' => array_map(fn($msg) => [
                'content' => $msg['content'],
                'timestamp' => $msg['created_at']->toIso8601String(),
            ], $validMessages),
            'total_messages' => count($validMessages),
        ];

        // Get AI-powered analysis from LangGPT
        $langGptAnalysis = null;
        if (count($validMessages) > 0) {
            $langGptResponse = $this->langGptService->analyzeMessages($langGptPayload);
            if ($langGptResponse['success']) {
                $langGptAnalysis = $langGptResponse['data'];
            }
        }

        return response()->json([
            'chat_session_id' => $chatSession->id,
            'target_language' => $chatSession->target_language,
            'proficiency_level' => $chatSession->proficiency_level,
            'analysis' => [
                'total_processed' => $processedCount,
                'valid_messages' => count($validMessages),
                'invalid_messages' => count($invalidMessages),
                'accuracy_rate' => $processedCount > 0
                    ? round((count($validMessages) / $processedCount) * 100, 2)
                    : 0,
            ],
            'valid_messages' => $validMessages,
            'invalid_messages' => $invalidMessages,
            'langgpt_analysis' => $langGptAnalysis,
        ]);
    }
}
