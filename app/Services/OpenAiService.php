<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

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

            // Log the system prompt for debugging
            Log::info('OpenAI System Prompt', [
                'proficiency_level' => $proficiencyLevel,
                'prompt' => $systemPrompt,
            ]);

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
        $levelGuidance = match ($proficiencyLevel) {
            'A1' => 'This is a complete beginner. Use ONLY the most basic vocabulary (hello, yes, no, numbers, colors, etc.) and simple present tense. Keep sentences VERY short (3-5 words). Example: "こんにちは。元気ですか。" (Hello. How are you?)',
            'A2' => 'This is an elementary learner. Use simple vocabulary and basic grammar (present, past simple). Avoid complex sentences, idioms, and advanced grammar. Keep it simple.',
            'B1' => 'This is an intermediate learner. Use everyday vocabulary and common grammar structures. Some complexity is okay, but avoid very advanced language.',
            'B2' => 'This is an upper-intermediate learner. Use varied vocabulary and grammar. You can introduce some advanced structures and expressions.',
            'C1' => 'This is an advanced learner. Use sophisticated vocabulary and complex grammar freely. Natural, nuanced language is appropriate.',
            'C2' => 'This is a proficient learner. Use native-level language including idioms, colloquialisms, and nuanced expressions.',
            default => 'Adapt your language complexity based on their responses.',
        };

        $basePrompt = "You are a friendly and helpful language learning assistant. You are helping someone practice {$targetLanguage} at a {$proficiencyLevel} (CEFR) proficiency level.

CRITICAL INSTRUCTION: {$levelGuidance}

Your goals:
- Engage in natural conversation appropriate for their level
- Use vocabulary and grammar structures suitable for {$proficiencyLevel} level
- Be encouraging and supportive
- Gently correct mistakes when needed
- Ask follow-up questions to keep the conversation flowing
- Adapt your complexity based on their responses
- IMPORTANT: Maintain conversation continuity - remember what has already been discussed and avoid asking questions about information already shared in this conversation

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
                ->map(fn ($msg) => "{$msg['role']}: {$msg['content']}")
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

    /**
     * Detect if a message is in the target language
     *
     * @param  string  $message  The user's message to check
     * @param  string  $targetLanguage  The expected language
     * @return array ['is_target_language' => bool, 'detected_language' => string|null]
     */
    public function detectLanguage(string $message, string $targetLanguage): array
    {
        try {
            $systemPrompt = 'You are a language detection expert. Analyze the provided text and determine what language it is written in. Be precise and only respond with a JSON object.';

            $userPrompt = "Analyze this text and determine if it is written in {$targetLanguage}. If it is, respond with {\"is_target_language\": true, \"detected_language\": \"{$targetLanguage}\"}. If it is NOT in {$targetLanguage}, respond with {\"is_target_language\": false, \"detected_language\": \"[the actual language name]\"}.

Text to analyze: \"{$message}\"

Respond ONLY with the JSON object, nothing else.";

            $response = OpenAI::chat()->create([
                'model' => config('services.openai.model'),
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'max_tokens' => 100,
                'temperature' => 0.1, // Low temperature for more deterministic results
            ]);

            $content = $response->choices[0]->message->content;

            // Parse JSON response
            $result = json_decode(trim($content), true);

            if (! $result || ! isset($result['is_target_language'])) {
                throw new \Exception('Invalid response format from OpenAI');
            }

            return [
                'is_target_language' => (bool) $result['is_target_language'],
                'detected_language' => $result['detected_language'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Language Detection Error', [
                'error' => $e->getMessage(),
                'message' => $message,
                'target_language' => $targetLanguage,
            ]);

            // Fallback: assume it's in the target language
            return [
                'is_target_language' => true,
                'detected_language' => $targetLanguage,
            ];
        }
    }

    /**
     * Translate text from target language to native language with parenthetical format
     *
     * @param  string  $text  The text to translate (in target language)
     * @param  string  $targetLanguage  The source language
     * @param  string  $nativeLanguage  The destination language
     * @return string The original text with parenthetical translations
     */
    public function translateWithParenthetical(string $text, string $targetLanguage, string $nativeLanguage): string
    {
        try {
            $systemPrompt = "You are a translation assistant for language learners. Your task is to add parenthetical translations to help beginner-level learners (A1-A2) understand {$targetLanguage} text in their native {$nativeLanguage}.

Instructions:
- Add {$nativeLanguage} translations in parentheses after key words, phrases, or full sentences
- Focus on vocabulary and expressions that A1-A2 learners might not know
- Keep translations concise and natural
- Don't translate every single word - focus on important content words
- Maintain the original text structure and formatting
- Make it helpful but not overwhelming

Example for Spanish to English:
Input: \"Hola, ¿cómo estás? Me alegro de verte.\"
Output: \"Hola (hello), ¿cómo estás? (how are you?) Me alegro de verte (I'm happy to see you).\"";

            $response = OpenAI::chat()->create([
                'model' => config('services.openai.model'),
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => "Add {$nativeLanguage} parenthetical translations to this {$targetLanguage} text:\n\n{$text}"],
                ],
                'max_tokens' => 500,
                'temperature' => 0.3,
            ]);

            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            Log::error('Translation Error', [
                'error' => $e->getMessage(),
                'text' => $text,
                'target_language' => $targetLanguage,
                'native_language' => $nativeLanguage,
            ]);

            // Fallback: return original text
            return $text;
        }
    }
}
