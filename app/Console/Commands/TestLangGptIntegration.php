<?php

namespace App\Console\Commands;

use App\Models\ChatSession;
use App\Models\User;
use App\Services\LangGptService;
use Illuminate\Console\Command;

class TestLangGptIntegration extends Command
{
    protected $signature = 'test:langgpt-integration {session_id?}';
    protected $description = 'Test the full CEFRSync to LangGPT integration';

    public function handle(LangGptService $langGptService)
    {
        $sessionId = $this->argument('session_id');
        
        if (!$sessionId) {
            $session = ChatSession::with('user')->first();
            if (!$session) {
                $this->error('No chat sessions found. Creating test data...');
                return 1;
            }
            $sessionId = $session->id;
        } else {
            $session = ChatSession::find($sessionId);
            if (!$session) {
                $this->error("Chat session {$sessionId} not found");
                return 1;
            }
        }

        $this->info("Testing with session: {$session->id}");
        $this->info("Target language: {$session->target_language}");
        $this->info("Proficiency: {$session->proficiency_level}");
        
        // Prepare payload similar to controller
        $messages = $session->messages()
            ->where('sender_type', 'user')
            ->latest()
            ->take(5)
            ->get();
            
        if ($messages->isEmpty()) {
            $this->error('No messages found in this session');
            return 1;
        }

        $payload = [
            'target_language' => $session->target_language,
            'proficiency_level' => $session->proficiency_level,
            'messages' => $messages->map(fn($msg) => [
                'content' => $msg->content,
                'timestamp' => $msg->created_at->toIso8601String(),
            ])->toArray(),
            'total_messages' => $messages->count(),
        ];

        $this->info("\nSending {$messages->count()} messages to LangGPT...\n");

        $response = $langGptService->analyzeMessages($payload);

        if (!$response['success']) {
            $this->error('LangGPT request failed:');
            $this->error($response['error'] ?? 'Unknown error');
            if (isset($response['status'])) {
                $this->error("HTTP Status: {$response['status']}");
            }
            $this->line("\nFull response:");
            $this->line(json_encode($response, JSON_PRETTY_PRINT));
            return 1;
        }

        $this->info('✓ Successfully received analysis from LangGPT');
        $this->newLine();
        
        $analysis = $response['data'];
        
        $this->info('=== Analysis Summary ===');
        $this->line("Messages processed: {$analysis['total_messages_analyzed']}");
        $this->line("Target language: {$analysis['target_language']}");
        $this->line("Proficiency level: {$analysis['proficiency_level']}");
        if (isset($analysis['analysis'])) {
            $this->line("Grammar accuracy: {$analysis['analysis']['grammar_accuracy']}%");
            $this->line("Complexity score: {$analysis['analysis']['complexity_score']}/10");
        }
        $this->newLine();
        
        if (isset($analysis['overall_feedback'])) {
            $this->info('=== Overall Feedback ===');
            $this->line($analysis['overall_feedback']);
            $this->newLine();
        }
        
        if (isset($analysis['grammar_issues']) && count($analysis['grammar_issues']) > 0) {
            $this->info('=== Grammar Issues ===');
            foreach ($analysis['grammar_issues'] as $issue) {
                $this->warn("Message: {$issue['message_content']}");
                $this->line("  Type: {$issue['issue_type']}");
                $this->line("  Incorrect: {$issue['incorrect']}");
                $this->line("  Correct: {$issue['correct']}");
                $this->line("  Explanation: {$issue['explanation']}");
                $this->newLine();
            }
        }
        
        if (isset($analysis['vocabulary_insights']) && count($analysis['vocabulary_insights']) > 0) {
            $this->info('=== Vocabulary Insights ===');
            foreach ($analysis['vocabulary_insights'] as $vocab) {
                $this->line("{$vocab['word']} (used {$vocab['usage_count']}x) - Level: {$vocab['proficiency_level']}");
            }
            $this->newLine();
        }
        
        if (isset($analysis['strengths'])) {
            $this->info('=== Strengths ===');
            foreach ($analysis['strengths'] as $strength) {
                $this->line("  ✓ {$strength}");
            }
            $this->newLine();
        }
        
        if (isset($analysis['areas_for_improvement'])) {
            $this->info('=== Areas for Improvement ===');
            foreach ($analysis['areas_for_improvement'] as $area) {
                $this->line("  → {$area}");
            }
        }

        return 0;
    }
}
