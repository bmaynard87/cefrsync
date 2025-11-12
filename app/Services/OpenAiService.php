<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class OpenAiService
{
    /**
     * Maximum number of message pairs to include in conversation history
     */
    protected const MAX_HISTORY_MESSAGES = 20;

    /**
     * Generate a chat response for language learning conversation
     */
    public function generateChatResponse(
        array $conversationHistory,
        string $targetLanguage,
        string $proficiencyLevel,
        ?string $sessionContext = null,
        ?string $userContext = null
    ): string {
        try {
            $systemPrompt = $this->buildSystemPrompt($targetLanguage, $proficiencyLevel, $sessionContext, $userContext);

            // Limit conversation history to prevent token overflow
            $limitedHistory = $this->limitConversationHistory($conversationHistory);

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ...$limitedHistory,
            ];

            $response = OpenAI::chat()->create([
                'model' => config('services.openai.model'),
                'messages' => $messages,
                'max_tokens' => config('services.openai.max_tokens'),
                'temperature' => config('services.openai.temperature'),
            ]);

            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            Log::error('OpenAI API Error', [
                'error' => $e->getMessage(),
                'target_language' => $targetLanguage,
                'proficiency_level' => $proficiencyLevel,
            ]);

            // Return a fallback response
            return $this->getFallbackResponse($targetLanguage);
        }
    }

    /**
     * Build system prompt for language learning context with enhanced memory
     */
    protected function buildSystemPrompt(
        string $targetLanguage,
        string $proficiencyLevel,
        ?string $sessionContext = null,
        ?string $userContext = null
    ): string {
        $basePrompt = "You are a friendly and helpful language learning assistant. You are helping someone practice {$targetLanguage} at a {$proficiencyLevel} (CEFR) proficiency level.

Your goals:
- Engage in natural conversation appropriate for their level
- Use vocabulary and grammar structures suitable for {$proficiencyLevel} level
- Be encouraging and supportive
- Gently correct mistakes when needed
- Ask follow-up questions to keep the conversation flowing
- Adapt your complexity based on their responses
- Remember topics and context from this conversation

Keep your responses conversational and concise (2-4 sentences usually). Focus on helping them practice the language naturally.";

        // Add session-specific context if available
        if ($sessionContext) {
            $basePrompt .= "\n\nPrevious conversation context:\n{$sessionContext}";
        }

        // Add user-specific learning context if available
        if ($userContext) {
            $basePrompt .= "\n\nLearner profile:\n{$userContext}";
        }

        return $basePrompt;
    }

    /**
     * Limit conversation history to prevent token overflow while maintaining context
     */
    protected function limitConversationHistory(array $conversationHistory): array
    {
        $totalMessages = count($conversationHistory);

        if ($totalMessages <= self::MAX_HISTORY_MESSAGES) {
            return $conversationHistory;
        }

        // Keep the first 2 messages (initial context) and the most recent messages
        $recentMessages = array_slice($conversationHistory, -self::MAX_HISTORY_MESSAGES + 2);
        $initialMessages = array_slice($conversationHistory, 0, 2);

        return array_merge($initialMessages, $recentMessages);
    }

    /**
     * Generate a summary of conversation history for persistent memory
     */
    public function summarizeConversation(array $conversationHistory, string $targetLanguage): ?string
    {
        if (count($conversationHistory) < 10) {
            return null; // Don't summarize short conversations
        }

        try {
            $conversationText = collect($conversationHistory)
                ->map(fn($msg) => "{$msg['role']}: {$msg['content']}")
                ->join("\n");

            $response = OpenAI::chat()->create([
                'model' => config('services.openai.model'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Summarize the following language learning conversation in 2-3 sentences. Focus on: topics discussed, corrections made, learner\'s strengths/weaknesses, and conversation flow.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $conversationText,
                    ],
                ],
                'max_tokens' => 150,
                'temperature' => 0.5,
            ]);

            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            Log::error('Failed to summarize conversation', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Generate a short, descriptive title for a chat session based on the first message
     */
    public function generateChatTitle(string $firstMessage): string
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => config('services.openai.model'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Generate a short, descriptive title (3-5 words max) for a language learning conversation based on the user\'s first message. The title should capture the main topic or intent. Return ONLY the title, no quotes or additional text.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $firstMessage,
                    ],
                ],
                'max_tokens' => 20,
                'temperature' => 0.7,
            ]);

            $title = trim($response->choices[0]->message->content);

            // Remove quotes if present
            $title = trim($title, '"\'');

            // Limit length as a safety measure
            return mb_substr($title, 0, 60);
        } catch (\Exception $e) {
            Log::error('Failed to generate chat title', [
                'error' => $e->getMessage(),
            ]);

            // Fallback: use first few words of the message
            $words = explode(' ', $firstMessage);
            return mb_substr(implode(' ', array_slice($words, 0, 4)), 0, 60);
        }
    }

    /**
     * Get a fallback response if OpenAI fails
     */
    protected function getFallbackResponse(string $targetLanguage): string
    {
        $fallbackResponses = [
            "I'm here to help you practice! What would you like to talk about?",
            "That's interesting! Can you tell me more about that?",
            "Great! Let's continue practicing together.",
            "I'd love to hear more. Please go on!",
        ];

        return $fallbackResponses[array_rand($fallbackResponses)];
    }

    /**
     * Format conversation history for OpenAI API
     */
    public function formatConversationHistory(array $messages): array
    {
        return collect($messages)->map(function ($message) {
            return [
                'role' => $message['sender_type'] === 'user' ? 'user' : 'assistant',
                'content' => $message['content'],
            ];
        })->toArray();
    }
}
