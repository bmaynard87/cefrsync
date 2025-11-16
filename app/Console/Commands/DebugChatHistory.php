<?php

namespace App\Console\Commands;

use App\Models\ChatSession;
use App\Services\OpenAiService;
use Illuminate\Console\Command;

class DebugChatHistory extends Command
{
    protected $signature = 'debug:chat-history {session_id}';

    protected $description = 'Debug chat history and what gets sent to OpenAI';

    public function handle(OpenAiService $openAiService)
    {
        $sessionId = $this->argument('session_id');
        $session = ChatSession::find($sessionId);

        if (! $session) {
            $this->error("Session {$sessionId} not found");

            return 1;
        }

        $this->info("Session ID: {$session->id}");
        $this->info("User: {$session->user->email}");
        $this->newLine();

        // Get conversation history the same way the controller does
        $conversationHistory = $session->messages()
            ->where('message_type', '!=', 'correction')
            ->orderBy('created_at')
            ->get(['sender_type', 'content'])
            ->toArray();

        $this->info('Raw conversation history ('.count($conversationHistory).' messages):');
        foreach ($conversationHistory as $i => $msg) {
            $this->line("{$i}: [{$msg['sender_type']}] ".substr($msg['content'], 0, 80));
        }

        $this->newLine();

        // Format it
        $formattedHistory = $openAiService->formatConversationHistory($conversationHistory);

        $this->info('Formatted history for OpenAI ('.count($formattedHistory).' messages):');
        foreach ($formattedHistory as $i => $msg) {
            $this->line("{$i}: [{$msg['role']}] ".substr($msg['content'], 0, 80));
        }

        $this->newLine();

        // Build contexts
        $sessionContext = $this->buildSessionContext($session);
        $userContext = $this->buildUserContext($session->user);

        $this->info('Session context:');
        $this->line($sessionContext ?? '(none)');
        $this->newLine();

        $this->info('User context:');
        $this->line($userContext ?? '(none)');

        return 0;
    }

    protected function buildSessionContext(ChatSession $chatSession): ?string
    {
        $context = [];

        if ($chatSession->conversation_summary) {
            $context[] = 'Previous conversation summary: '.$chatSession->conversation_summary;
        }

        if ($chatSession->topics_discussed && count($chatSession->topics_discussed) > 0) {
            $topics = implode(', ', $chatSession->topics_discussed);
            $context[] = 'Topics discussed: '.$topics;
        }

        return empty($context) ? null : implode("\n", $context);
    }

    protected function buildUserContext($user): ?string
    {
        return 'Learner name: '.$user->name;
    }
}
