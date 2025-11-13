<?php

use App\Models\User;
use App\Models\ChatSession;
use App\Services\OpenAiService;

test('can detect if message is in target language', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create([
        'user_id' => $user->id,
        'target_language' => 'Spanish',
    ]);

    // Mock the OpenAI service
    $this->mock(OpenAiService::class, function ($mock) {
        $mock->shouldReceive('detectLanguage')
            ->once()
            ->with('Hola, ¿cómo estás?', 'Spanish')
            ->andReturn([
                'is_target_language' => true,
                'detected_language' => 'Spanish',
            ]);
    });

    $response = $this->actingAs($user)->postJson(
        route('language-chat.detect-language', $session),
        ['message' => 'Hola, ¿cómo estás?']
    );

    $response->assertOk()
        ->assertJson([
            'is_target_language' => true,
            'detected_language' => 'Spanish',
        ]);
});

test('can detect when message is not in target language', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create([
        'user_id' => $user->id,
        'target_language' => 'Spanish',
    ]);

    // Mock the OpenAI service
    $this->mock(OpenAiService::class, function ($mock) {
        $mock->shouldReceive('detectLanguage')
            ->once()
            ->with('Hello, how are you?', 'Spanish')
            ->andReturn([
                'is_target_language' => false,
                'detected_language' => 'English',
            ]);
    });

    $response = $this->actingAs($user)->postJson(
        route('language-chat.detect-language', $session),
        ['message' => 'Hello, how are you?']
    );

    $response->assertOk()
        ->assertJson([
            'is_target_language' => false,
            'detected_language' => 'English',
        ]);
});

test('language detection requires authentication', function () {
    $session = ChatSession::factory()->create();

    $response = $this->postJson(
        route('language-chat.detect-language', $session),
        ['message' => 'Test message']
    );

    $response->assertUnauthorized();
});

test('language detection validates message is required', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson(
        route('language-chat.detect-language', $session),
        ['message' => '']
    );

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['message']);
});

test('user can only detect language for their own chat sessions', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->postJson(
        route('language-chat.detect-language', $session),
        ['message' => 'Test message']
    );

    $response->assertForbidden();
});
