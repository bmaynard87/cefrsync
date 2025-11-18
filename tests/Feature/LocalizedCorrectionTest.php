<?php

use App\Models\ChatSession;
use App\Models\User;
use App\Services\LangGptService;
use App\Services\OpenAiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->session = ChatSession::factory()->create([
        'user_id' => $this->user->id,
        'native_language_id' => \App\Models\Language::findByKey('es')->id,
        'target_language_id' => \App\Models\Language::findByKey('en')->id,
        'proficiency_level' => 'B1',
        'localize_corrections' => true,
    ]);

    $this->openAiService = Mockery::mock(OpenAiService::class);
    $this->langGptService = Mockery::mock(LangGptService::class);

    $this->app->instance(OpenAiService::class, $this->openAiService);
    $this->app->instance(LangGptService::class, $this->langGptService);
});

test('correction message is sent in native language when localize_corrections is enabled', function () {
    // Mock language detection - message is in target language
    $this->openAiService
        ->shouldReceive('detectLanguage')
        ->once()
        ->with('I no enjoy but enjoy', 'English')
        ->andReturn(['is_target_language' => true]);

    // Mock generateChatTitle
    $this->openAiService
        ->shouldReceive('generateChatTitle')
        ->once()
        ->andReturn('Chat Title');

    // Mock critical error check with localized response
    $this->langGptService
        ->shouldReceive('checkCriticalErrors')
        ->once()
        ->with(Mockery::on(function ($payload) {
            return $payload['message'] === 'I no enjoy but enjoy'
                && $payload['target_language'] === 'English'
                && $payload['proficiency_level'] === 'B1'
                && $payload['native_language'] === 'Spanish'
                && $payload['localize'] === true;
        }))
        ->andReturn([
            'success' => true,
            'data' => [
                'has_critical_error' => true,
                'error_type' => 'meaningless',
                'severity' => 'high',
                'original_text' => 'I no enjoy but enjoy',
                'corrected_text' => 'I do not enjoy it, but I enjoy something else.',
                'explanation' => 'El mensaje es gramaticalmente incorrecto y confuso, lo que dificulta entender el significado previsto.',
                'context' => 'La estructura adecuada es esencial para una comunicación clara.',
                'recommendations' => [
                    'Use "do not" en lugar de "no" para la negación.',
                    'Aclare qué se disfruta o no se disfruta.',
                ],
            ],
        ]);

    // Mock conversation history formatting and AI response
    $this->openAiService
        ->shouldReceive('formatConversationHistory')
        ->once()
        ->andReturn([]);

    $this->openAiService
        ->shouldReceive('generateChatResponse')
        ->once()
        ->andReturn('I understand! Sometimes we can have mixed feelings about our jobs.');

    // Send message
    $response = $this->actingAs($this->user)
        ->postJson(route('language-chat.message', $this->session), [
            'message' => 'I no enjoy but enjoy',
        ]);

    $response->assertOk();

    // Verify correction message is in Spanish
    expect($response->json('correction_message.correction_data.explanation'))
        ->toContain('gramaticalmente')
        ->toContain('dificulta');

    expect($response->json('correction_message.correction_data.context'))
        ->toContain('estructura')
        ->toContain('comunicación');

    expect($response->json('correction_message.correction_data.recommendations.0'))
        ->toContain('Use "do not" en lugar de "no"');
});

test('correction message is in English when localize_corrections is disabled', function () {
    $this->session->update(['localize_corrections' => false]);

    // Mock language detection
    $this->openAiService
        ->shouldReceive('detectLanguage')
        ->once()
        ->andReturn(['is_target_language' => true]);

    // Mock generateChatTitle
    $this->openAiService
        ->shouldReceive('generateChatTitle')
        ->once()
        ->andReturn('Chat Title');

    // Mock critical error check WITHOUT localization
    $this->langGptService
        ->shouldReceive('checkCriticalErrors')
        ->once()
        ->with(Mockery::on(function ($payload) {
            return ! isset($payload['localize']) || $payload['localize'] === false;
        }))
        ->andReturn([
            'success' => true,
            'data' => [
                'has_critical_error' => true,
                'error_type' => 'meaningless',
                'severity' => 'high',
                'original_text' => 'I no enjoy but enjoy',
                'corrected_text' => 'I do not enjoy it, but I enjoy something else.',
                'explanation' => 'The message is grammatically incorrect and confusing.',
                'context' => 'Proper structure is essential for clear communication.',
                'recommendations' => ['Use "do not" instead of "no" for negation.'],
            ],
        ]);

    $this->openAiService->shouldReceive('formatConversationHistory')->andReturn([]);
    $this->openAiService->shouldReceive('generateChatResponse')->andReturn('I understand!');

    $response = $this->actingAs($this->user)
        ->postJson(route('language-chat.message', $this->session), [
            'message' => 'I no enjoy but enjoy',
        ]);

    $response->assertOk();

    // Verify correction message is in English
    expect($response->json('correction_message.correction_data.explanation'))
        ->toBe('The message is grammatically incorrect and confusing.');
});

test('correction message defaults to English when native language is not passed', function () {
    // Session has native language but localize_corrections is false
    $this->session->update([
        'localize_corrections' => false, // Localization disabled
    ]);

    $this->openAiService->shouldReceive('detectLanguage')->andReturn(['is_target_language' => true]);
    $this->openAiService->shouldReceive('generateChatTitle')->andReturn('Chat Title');

    // Should NOT pass native_language or localize when localization is disabled
    $this->langGptService
        ->shouldReceive('checkCriticalErrors')
        ->once()
        ->with(Mockery::on(function ($payload) {
            return ! isset($payload['native_language']) && (! isset($payload['localize']) || $payload['localize'] === false);
        }))
        ->andReturn([
            'success' => true,
            'data' => [
                'has_critical_error' => true,
                'error_type' => 'unnatural',
                'severity' => 'medium',
                'original_text' => 'I does work',
                'corrected_text' => 'I do work',
                'explanation' => 'Incorrect verb conjugation.',
                'context' => 'Subject-verb agreement is important.',
            ],
        ]);

    $this->openAiService->shouldReceive('formatConversationHistory')->andReturn([]);
    $this->openAiService->shouldReceive('generateChatResponse')->andReturn('Great!');

    $response = $this->actingAs($this->user)
        ->postJson(route('language-chat.message', $this->session), [
            'message' => 'I does work',
        ]);

    $response->assertOk();
    expect($response->json('correction_message.correction_data.explanation'))
        ->toBe('Incorrect verb conjugation.');
});
