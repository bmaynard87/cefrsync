<?php

namespace App\Console\Commands;

use App\Models\ChatSession;
use App\Services\OpenAiService;
use Illuminate\Console\Command;

class ShowAnalysisPayload extends Command
{
    protected $signature = 'show:analysis-payload {session_id?}';
    protected $description = 'Show example of LangGPT analysis payload';

    public function handle(OpenAiService $openAiService)
    {
        $sessionId = $this->argument('session_id') ?? 1;
        $session = ChatSession::find($sessionId);

        if (!$session) {
            $this->error("Session {$sessionId} not found");
            return 1;
        }

        $messages = $session->messages()
            ->where('sender_type', 'user')
            ->latest()
            ->limit(5)
            ->get();

        $validMessages = [];
        $invalidMessages = [];

        foreach ($messages as $message) {
            $detection = $openAiService->detectLanguage(
                $message->content,
                $session->target_language
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
                ];
            }
        }

        $langGptPayload = [
            'target_language' => $session->target_language,
            'proficiency_level' => $session->proficiency_level,
            'messages' => array_map(fn($msg) => [
                'content' => $msg['content'],
                'timestamp' => $msg['created_at']->toIso8601String(),
            ], $validMessages),
            'total_messages' => count($validMessages),
        ];

        $fullResponse = [
            'chat_session_id' => $session->id,
            'target_language' => $session->target_language,
            'proficiency_level' => $session->proficiency_level,
            'analysis' => [
                'total_processed' => count($messages),
                'valid_messages' => count($validMessages),
                'invalid_messages' => count($invalidMessages),
                'accuracy_rate' => count($messages) > 0 
                    ? round((count($validMessages) / count($messages)) * 100, 2) 
                    : 0,
            ],
            'valid_messages' => $validMessages,
            'invalid_messages' => $invalidMessages,
            'langgpt_payload' => $langGptPayload,
        ];

        $this->newLine();
        $this->info('Full API Response:');
        $this->line(json_encode($fullResponse, JSON_PRETTY_PRINT));

        return 0;
    }
}
