<?php

use App\Models\ChatSession;
use App\Models\User;
use App\Services\LangGptService;
use App\Services\OpenAiService;

beforeEach(function () {
    // Mock OpenAI to avoid real API calls
    $this->mock(OpenAiService::class, function ($mock) {
        $mock->shouldReceive('formatConversationHistory')->andReturn([]);
        $mock->shouldReceive('generateChatResponse')->andReturn('Great job! Keep practicing.');
        $mock->shouldReceive('generateChatTitle')->andReturn('Spanish Practice');
        $mock->shouldReceive('detectLanguage')->andReturn([
            'is_target_language' => true,
            'detected_language' => 'Spanish',
        ]);
    });
});

test('critical correction message is created when user makes critical error', function () {
    $user = User::factory()->create([
        'proficiency_level' => 'A1',
    ]);

    $session = ChatSession::factory()->create([
        'user_id' => $user->id,
        'target_language' => 'Spanish',
        'proficiency_level' => 'A1',
    ]);

    // Mock LangGPT to return a critical error
    $this->mock(LangGptService::class, function ($mock) {
        $mock->shouldReceive('checkCriticalErrors')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => [
                    'has_critical_error' => true,
                    'error_type' => 'offensive',
                    'severity' => 'high',
                    'original_text' => 'Tu eres un idiota',
                    'corrected_text' => 'Eres muy inteligente',
                    'explanation' => 'This phrase is offensive. Consider using positive language instead.',
                    'context' => 'The word "idiota" is insulting and should be avoided in polite conversation.',
                ],
            ]);
    });

    $response = $this->actingAs($user)->postJson(
        route('language-chat.message', ['chatSession' => $session]),
        ['message' => 'Tu eres un idiota']
    );

    $response->assertOk();

    // Should have user message, correction message, and AI response
    expect($session->messages()->count())->toBe(3);

    // Check correction message was created
    $correctionMessage = $session->messages()
        ->where('message_type', 'correction')
        ->first();

    expect($correctionMessage)->not->toBeNull();
    expect($correctionMessage->content)->toContain('offensive');
    expect($correctionMessage->correction_data)->toHaveKey('corrected_text');
});

test('no correction message created when message is acceptable', function () {
    $user = User::factory()->create([
        'proficiency_level' => 'A1',
    ]);

    $session = ChatSession::factory()->create([
        'user_id' => $user->id,
        'target_language' => 'Spanish',
        'proficiency_level' => 'A1',
    ]);

    // Mock LangGPT to return no critical error
    $this->mock(LangGptService::class, function ($mock) {
        $mock->shouldReceive('checkCriticalErrors')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => [
                    'has_critical_error' => false,
                ],
            ]);
    });

    $response = $this->actingAs($user)->postJson(
        route('language-chat.message', ['chatSession' => $session]),
        ['message' => 'Hola, ¿cómo estás?']
    );

    $response->assertOk();

    // Should only have user message and AI response, no correction
    expect($session->messages()->count())->toBe(2);

    $correctionMessage = $session->messages()
        ->where('message_type', 'correction')
        ->first();

    expect($correctionMessage)->toBeNull();
});

test('correction message includes all necessary data', function () {
    $user = User::factory()->create([
        'proficiency_level' => 'B1',
    ]);

    $session = ChatSession::factory()->create([
        'user_id' => $user->id,
        'target_language' => 'Spanish',
        'proficiency_level' => 'B1',
    ]);

    // Mock LangGPT with detailed correction
    $this->mock(LangGptService::class, function ($mock) {
        $mock->shouldReceive('checkCriticalErrors')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => [
                    'has_critical_error' => true,
                    'error_type' => 'meaningless',
                    'severity' => 'critical',
                    'original_text' => 'Yo soy hace la tienda ayer',
                    'corrected_text' => 'Yo fui a la tienda ayer',
                    'explanation' => 'The sentence structure is incorrect and doesn\'t convey clear meaning. The verb "hacer" is misused here.',
                    'context' => 'Use "ir" (to go) with the preterite tense "fui" instead of "ser/hacer".',
                    'recommendations' => [
                        'Review preterite tense conjugation of irregular verbs',
                        'Practice using "ir" for movement/going',
                    ],
                ],
            ]);
    });

    $response = $this->actingAs($user)->postJson(
        route('language-chat.message', ['chatSession' => $session]),
        ['message' => 'Yo soy hace la tienda ayer']
    );

    $response->assertOk();

    $correctionMessage = $session->messages()
        ->where('message_type', 'correction')
        ->first();

    expect($correctionMessage)->not->toBeNull();
    expect($correctionMessage->correction_data)->toHaveKeys([
        'error_type',
        'severity',
        'original_text',
        'corrected_text',
        'explanation',
        'context',
        'recommendations',
    ]);
});

test('correction is returned in API response', function () {
    $user = User::factory()->create();

    $session = ChatSession::factory()->create([
        'user_id' => $user->id,
        'target_language' => 'French',
    ]);

    // Mock LangGPT to return critical error
    $this->mock(LangGptService::class, function ($mock) {
        $mock->shouldReceive('checkCriticalErrors')
            ->once()
            ->andReturn([
                'success' => true,
                'data' => [
                    'has_critical_error' => true,
                    'error_type' => 'unnatural',
                    'severity' => 'medium',
                    'original_text' => 'Je suis très beaucoup fatigué',
                    'corrected_text' => 'Je suis très fatigué',
                    'explanation' => '"Très beaucoup" is redundant. Use either "très" or "beaucoup" but not both.',
                    'context' => 'Intensity adverbs in French should not be stacked like this.',
                ],
            ]);
    });

    $response = $this->actingAs($user)->postJson(
        route('language-chat.message', ['chatSession' => $session]),
        ['message' => 'Je suis très beaucoup fatigué']
    );

    $response->assertOk();
    $response->assertJsonStructure([
        'user_message',
        'ai_response',
        'correction_message' => [
            'id',
            'content',
            'created_at',
            'correction_data' => [
                'error_type',
                'severity',
                'original_text',
                'corrected_text',
                'explanation',
            ],
        ],
    ]);
});

test('correction only checks messages in target language', function () {
    $user = User::factory()->create();

    $session = ChatSession::factory()->create([
        'user_id' => $user->id,
        'target_language' => 'Spanish',
    ]);

    // Mock OpenAI language detection to say it's NOT in target language
    $this->mock(OpenAiService::class, function ($mock) {
        $mock->shouldReceive('formatConversationHistory')->andReturn([]);
        $mock->shouldReceive('generateChatResponse')->andReturn('Please practice in Spanish.');
        $mock->shouldReceive('generateChatTitle')->andReturn('English Practice');  // Mock title generation for first message
        $mock->shouldReceive('detectLanguage')
            ->once()
            ->andReturn([
                'is_target_language' => false,
                'detected_language' => 'English',
            ]);
    });

    // LangGPT should NOT be called for non-target language messages
    $this->mock(LangGptService::class, function ($mock) {
        $mock->shouldReceive('checkCriticalErrors')->never();
    });

    $response = $this->actingAs($user)->postJson(
        route('language-chat.message', ['chatSession' => $session]),
        ['message' => 'Hello, how are you?']
    );

    $response->assertOk();

    // No correction message should exist
    $correctionMessage = $session->messages()
        ->where('message_type', 'correction')
        ->first();

    expect($correctionMessage)->toBeNull();
});

test('LangGPT service has checkCriticalErrors method', function () {
    $service = new LangGptService;

    expect(method_exists($service, 'checkCriticalErrors'))->toBeTrue();
});

test('handles LangGPT API failure gracefully', function () {
    $user = User::factory()->create();

    $session = ChatSession::factory()->create([
        'user_id' => $user->id,
        'target_language' => 'Spanish',
    ]);

    // Mock LangGPT to fail
    $this->mock(LangGptService::class, function ($mock) {
        $mock->shouldReceive('checkCriticalErrors')
            ->once()
            ->andReturn([
                'success' => false,
                'error' => 'API timeout',
            ]);
    });

    $response = $this->actingAs($user)->postJson(
        route('language-chat.message', ['chatSession' => $session]),
        ['message' => 'Test message']
    );

    // Should still succeed even if correction check fails
    $response->assertOk();

    // Should have user and AI messages but no correction
    expect($session->messages()->count())->toBe(2);
});
