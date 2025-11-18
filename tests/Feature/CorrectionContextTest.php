<?php

use App\Models\ChatSession;
use App\Models\User;
use App\Services\LangGptService;
use App\Services\OpenAiService;

beforeEach(function () {
    // Mock OpenAI to avoid real API calls
    $this->mock(OpenAiService::class, function ($mock) {
        $mock->shouldReceive('formatConversationHistory')->andReturn([]);
        $mock->shouldReceive('generateChatResponse')->andReturn('Great! Keep practicing.');
        $mock->shouldReceive('generateChatTitle')->andReturn('Practice Chat');
        $mock->shouldReceive('detectLanguage')->andReturn([
            'is_target_language' => true,
            'detected_language' => 'English',
        ]);
    });
});

test('correction check includes recent conversation context', function () {
    $user = User::factory()->create([
        'proficiency_level' => 'B1',
    ]);

    $session = ChatSession::factory()->create([
        'user_id' => $user->id,
        'target_language' => 'English',
    ]);

    // Create conversation history
    $session->messages()->create([
        'sender_type' => 'assistant',
        'content' => 'You love snakes! That\'s interesting. Do you have a pet snake?',
    ]);

    // Mock LangGPT service to verify context is passed
    $this->mock(LangGptService::class, function ($mock) {
        $mock->shouldReceive('checkCriticalErrors')
            ->once()
            ->withArgs(function ($payload) {
                // Verify context_messages is present and contains the AI's question
                return isset($payload['context_messages'])
                    && is_array($payload['context_messages'])
                    && count($payload['context_messages']) > 0
                    && str_contains($payload['context_messages'][0]['content'] ?? '', 'pet snake');
            })
            ->andReturn([
                'success' => true,
                'data' => [
                    'has_critical_error' => true,
                    'error_type' => 'unnatural',
                    'severity' => 'high',
                    'original_text' => 'no i love but scared lol',
                    'corrected_text' => 'I love you, but I\'m scared.',
                    'explanation' => 'The sentence is missing key words and has grammatical issues.',
                    'context' => 'Based on the previous question about pet snakes, this response should clarify the subject.',
                ],
            ]);
    });

    $response = $this->actingAs($user)->postJson(
        route('language-chat.message', ['chatSession' => $session]),
        ['message' => 'no i love but scared lol']
    );

    $response->assertOk();

    // Verify correction was created with proper context understanding
    $correctionMessage = $session->messages()
        ->where('message_type', 'correction')
        ->first();

    expect($correctionMessage)->not->toBeNull();
    expect($correctionMessage->correction_data['context'] ?? '')->toContain('pet snake');
});

test('correction check includes multiple context messages', function () {
    $user = User::factory()->create([
        'proficiency_level' => 'A2',
    ]);

    $session = ChatSession::factory()->create([
        'user_id' => $user->id,
        'target_language' => 'Spanish',
    ]);

    // Create multi-turn conversation
    $session->messages()->create(['sender_type' => 'user', 'content' => 'Hola']);
    $session->messages()->create(['sender_type' => 'assistant', 'content' => '¡Hola! ¿Cómo estás?']);
    $session->messages()->create(['sender_type' => 'user', 'content' => 'Bien, gracias']);

    // Mock OpenAI service
    $this->mock(OpenAiService::class, function ($mock) {
        $mock->shouldReceive('formatConversationHistory')->andReturn([]);
        $mock->shouldReceive('generateChatResponse')->andReturn('¡Muy bien!');
        $mock->shouldReceive('detectLanguage')->andReturn([
            'is_target_language' => true,
            'detected_language' => 'Spanish',
        ]);
        $mock->shouldReceive('translateWithParenthetical')->andReturn('¡Muy bien! (Very good!)');
    });

    // Mock LangGPT service to verify multiple context messages
    $this->mock(LangGptService::class, function ($mock) {
        $mock->shouldReceive('checkCriticalErrors')
            ->once()
            ->withArgs(function ($payload) {
                // Should have all 3 previous messages as context
                return isset($payload['context_messages'])
                    && count($payload['context_messages']) === 3;
            })
            ->andReturn([
                'success' => true,
                'data' => ['has_critical_error' => false],
            ]);
    });

    $response = $this->actingAs($user)->postJson(
        route('language-chat.message', ['chatSession' => $session]),
        ['message' => '¿Y tú?']
    );

    $response->assertOk();
});

test('correction check handles empty conversation context', function () {
    $user = User::factory()->create([
        'proficiency_level' => 'B1',
    ]);

    $session = ChatSession::factory()->create([
        'user_id' => $user->id,
        'target_language' => 'French',
    ]);

    // No previous messages - first message in conversation
    // Note: The current user message IS stored before checking, so context will include it
    // This is expected behavior - the API can still understand this is the first message

    // Mock LangGPT service - simple mock without strict expectations
    $this->mock(LangGptService::class, function ($mock) {
        $mock->shouldReceive('checkCriticalErrors')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => ['has_critical_error' => false],
            ]);
    });

    // Mock OpenAI to avoid real API calls
    $this->mock(OpenAiService::class, function ($mock) {
        $mock->shouldReceive('formatConversationHistory')->andReturn([]);
        $mock->shouldReceive('generateChatResponse')->andReturn('Bonjour! Comment allez-vous?');
        $mock->shouldReceive('generateChatTitle')->andReturn('French Practice');
        $mock->shouldReceive('detectLanguage')->andReturn([
            'is_target_language' => true,
            'detected_language' => 'French',
        ]);
    });

    $response = $this->actingAs($user)->postJson(
        route('language-chat.message', ['chatSession' => $session]),
        ['message' => 'Bonjour']
    );

    $response->assertOk();
});

test('correction context excludes correction messages', function () {
    $user = User::factory()->create([
        'proficiency_level' => 'B2',
    ]);

    $session = ChatSession::factory()->create([
        'user_id' => $user->id,
        'target_language' => 'German',
    ]);

    // Create conversation with correction message
    $session->messages()->create(['sender_type' => 'user', 'content' => 'Ich gehen zum Markt']);
    $session->messages()->create([
        'sender_type' => 'system',
        'message_type' => 'correction',
        'content' => 'Correction: Use "gehe" not "gehen"',
    ]);
    $session->messages()->create(['sender_type' => 'assistant', 'content' => 'Gut! Was kaufst du?']);

    // Mock LangGPT service - simple mock that verifies correction messages excluded
    $this->mock(LangGptService::class, function ($mock) {
        $mock->shouldReceive('checkCriticalErrors')
            ->once()
            ->andReturnUsing(function ($payload) {
                // Verify correction messages are NOT in context
                if (str_contains(json_encode($payload['context_messages']), 'Correction:')) {
                    throw new \RuntimeException('Context should not include correction messages');
                }

                return [
                    'success' => true,
                    'data' => ['has_critical_error' => false],
                ];
            });
    });

    // Mock OpenAI to avoid real API calls
    $this->mock(OpenAiService::class, function ($mock) {
        $mock->shouldReceive('formatConversationHistory')->andReturn([]);
        $mock->shouldReceive('generateChatResponse')->andReturn('Sehr gut!');
        $mock->shouldReceive('generateChatTitle')->andReturn('German Practice');
        $mock->shouldReceive('detectLanguage')->andReturn([
            'is_target_language' => true,
            'detected_language' => 'German',
        ]);
    });

    $response = $this->actingAs($user)->postJson(
        route('language-chat.message', ['chatSession' => $session]),
        ['message' => 'Ich kaufe Äpfel']
    );

    $response->assertOk();
});
