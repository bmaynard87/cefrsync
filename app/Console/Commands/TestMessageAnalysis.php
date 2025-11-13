<?php

namespace App\Console\Commands;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\User;
use App\Services\OpenAiService;
use Illuminate\Console\Command;

class TestMessageAnalysis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:message-analysis {session_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test message analysis functionality';

    /**
     * Execute the console command.
     */
    public function handle(OpenAiService $openAiService)
    {
        $sessionId = $this->argument('session_id');

        if ($sessionId) {
            $session = ChatSession::find($sessionId);
        } else {
            // Get or create test data
            $user = User::first();
            if (! $user) {
                $this->info('Creating test user...');
                $user = User::factory()->create([
                    'email' => 'test@example.com',
                    'first_name' => 'Test',
                    'last_name' => 'User',
                ]);
            }

            $session = $user->chatSessions()->first();
            if (! $session) {
                $this->info('Creating test chat session...');
                $session = ChatSession::create([
                    'user_id' => $user->id,
                    'target_language' => 'Spanish',
                    'native_language' => 'English',
                    'proficiency_level' => 'B1',
                    'title' => 'Test Session',
                    'last_message_at' => now(),
                ]);
            }

            // Add test messages if needed
            $existingCount = $session->messages()->where('sender_type', 'user')->count();
            if ($existingCount < 3) {
                $this->info('Adding test messages...');
                $testMessages = [
                    'Hola, ¿cómo estás?',
                    'Me gusta mucho el café.',
                    'Hello, this is in English',
                    'Estoy estudiando español.',
                ];

                foreach ($testMessages as $content) {
                    ChatMessage::create([
                        'chat_session_id' => $session->id,
                        'sender_type' => 'user',
                        'content' => $content,
                    ]);
                }
            }
        }

        if (! $session) {
            $this->error('Session not found');

            return 1;
        }

        $this->info('Testing Message Analysis');
        $this->info("Session ID: {$session->id}");
        $this->info("Target Language: {$session->target_language}");
        $this->newLine();

        // Get user messages
        $messages = $session->messages()
            ->where('sender_type', 'user')
            ->latest()
            ->limit(10)
            ->get();

        $this->info("Found {$messages->count()} user messages to analyze");
        $this->newLine();

        $validCount = 0;
        $invalidCount = 0;

        $this->info('Analyzing messages...');
        $this->newLine();

        foreach ($messages as $message) {
            $result = $openAiService->detectLanguage(
                $message->content,
                $session->target_language
            );

            $status = $result['is_target_language'] ? '✓' : '✗';
            $color = $result['is_target_language'] ? 'green' : 'red';

            $this->line("<fg={$color}>[{$status}]</> {$message->content}");
            $this->line("    Detected: {$result['detected_language']}");

            if ($result['is_target_language']) {
                $validCount++;
            } else {
                $invalidCount++;
            }
        }

        $this->newLine();
        $this->info('Analysis Summary:');
        $this->info("  Valid messages (in {$session->target_language}): {$validCount}");
        $this->info("  Invalid messages: {$invalidCount}");

        $total = $validCount + $invalidCount;
        $accuracy = $total > 0 ? round(($validCount / $total) * 100, 2) : 0;
        $this->info("  Accuracy: {$accuracy}%");

        $this->newLine();
        $this->info("LangGPT Payload would include {$validCount} message(s)");

        return 0;
    }
}
