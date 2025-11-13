<?php

use App\Models\User;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Services\OpenAiService;

test('can analyze messages for langgpt', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create([
        'user_id' => $user->id,
        'target_language' => 'Spanish',
        'proficiency_level' => 'B1',
    ]);

    // Create some messages
    ChatMessage::factory()->create([
        'chat_session_id' => $session->id,
        'sender_type' => 'user',
        'content' => 'Hola, ¿cómo estás?',
    ]);

    ChatMessage::factory()->create([
        'chat_session_id' => $session->id,
        'sender_type' => 'user',
        'content' => 'Me gusta el café.',
    ]);

    ChatMessage::factory()->create([
        'chat_session_id' => $session->id,
        'sender_type' => 'user',
        'content' => 'Hello, how are you?', // English - should be invalid
    ]);

    // Mock the OpenAI service
    $this->mock(OpenAiService::class, function ($mock) {
        $mock->shouldReceive('detectLanguage')
            ->with('Hola, ¿cómo estás?', 'Spanish')
            ->once()
            ->andReturn([
                'is_target_language' => true,
                'detected_language' => 'Spanish',
            ]);

        $mock->shouldReceive('detectLanguage')
            ->with('Me gusta el café.', 'Spanish')
            ->once()
            ->andReturn([
                'is_target_language' => true,
                'detected_language' => 'Spanish',
            ]);

        $mock->shouldReceive('detectLanguage')
            ->with('Hello, how are you?', 'Spanish')
            ->once()
            ->andReturn([
                'is_target_language' => false,
                'detected_language' => 'English',
            ]);
    });

    $response = $this->actingAs($user)->postJson(
        route('language-chat.analyze-for-langgpt', $session)
    );

    $response->assertOk()
        ->assertJsonStructure([
            'chat_session_id',
            'target_language',
            'proficiency_level',
            'analysis' => [
                'total_processed',
                'valid_messages',
                'invalid_messages',
                'accuracy_rate',
            ],
            'valid_messages',
            'invalid_messages',
            'langgpt_payload' => [
                'target_language',
                'proficiency_level',
                'messages',
                'total_messages',
            ],
        ])
        ->assertJson([
            'target_language' => 'Spanish',
            'proficiency_level' => 'B1',
            'analysis' => [
                'total_processed' => 3,
                'valid_messages' => 2,
                'invalid_messages' => 1,
                'accuracy_rate' => 66.67,
            ],
        ]);

    expect($response->json('valid_messages'))->toHaveCount(2);
    expect($response->json('invalid_messages'))->toHaveCount(1);
    expect($response->json('langgpt_payload.total_messages'))->toBe(2);
});

test('analyze messages respects limit parameter', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create([
        'user_id' => $user->id,
        'target_language' => 'Spanish',
    ]);

    // Create 10 messages
    for ($i = 0; $i < 10; $i++) {
        ChatMessage::factory()->create([
            'chat_session_id' => $session->id,
            'sender_type' => 'user',
            'content' => "Message $i",
        ]);
    }

    // Mock to expect only 5 calls
    $this->mock(OpenAiService::class, function ($mock) {
        $mock->shouldReceive('detectLanguage')
            ->times(5)
            ->andReturn([
                'is_target_language' => true,
                'detected_language' => 'Spanish',
            ]);
    });

    $response = $this->actingAs($user)->postJson(
        route('language-chat.analyze-for-langgpt', $session),
        ['limit' => 5]
    );

    $response->assertOk()
        ->assertJson([
            'analysis' => [
                'total_processed' => 5,
            ],
        ]);
});

test('analyze messages requires authentication', function () {
    $session = ChatSession::factory()->create();

    $response = $this->postJson(
        route('language-chat.analyze-for-langgpt', $session)
    );

    $response->assertUnauthorized();
});

test('user can only analyze their own chat sessions', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user)->postJson(
        route('language-chat.analyze-for-langgpt', $session)
    );

    $response->assertForbidden();
});

test('analyze messages validates limit parameter', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson(
        route('language-chat.analyze-for-langgpt', $session),
        ['limit' => 0] // Invalid: below minimum
    );

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['limit']);

    $response = $this->actingAs($user)->postJson(
        route('language-chat.analyze-for-langgpt', $session),
        ['limit' => 101] // Invalid: above maximum
    );

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['limit']);
});

test('analyze messages handles empty message list', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create([
        'user_id' => $user->id,
        'target_language' => 'Spanish',
    ]);

    // No messages created

    $response = $this->actingAs($user)->postJson(
        route('language-chat.analyze-for-langgpt', $session)
    );

    $response->assertOk()
        ->assertJson([
            'analysis' => [
                'total_processed' => 0,
                'valid_messages' => 0,
                'invalid_messages' => 0,
                'accuracy_rate' => 0,
            ],
        ]);
});

test('analyze messages only processes user messages not assistant messages', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create([
        'user_id' => $user->id,
        'target_language' => 'Spanish',
    ]);

    ChatMessage::factory()->create([
        'chat_session_id' => $session->id,
        'sender_type' => 'user',
        'content' => 'Hola',
    ]);

    ChatMessage::factory()->create([
        'chat_session_id' => $session->id,
        'sender_type' => 'assistant',
        'content' => '¿Cómo estás?',
    ]);

    $this->mock(OpenAiService::class, function ($mock) {
        $mock->shouldReceive('detectLanguage')
            ->once() // Should only be called once for the user message
            ->andReturn([
                'is_target_language' => true,
                'detected_language' => 'Spanish',
            ]);
    });

    $response = $this->actingAs($user)->postJson(
        route('language-chat.analyze-for-langgpt', $session)
    );

    $response->assertOk()
        ->assertJson([
            'analysis' => [
                'total_processed' => 1,
                'valid_messages' => 1,
            ],
        ]);
});
