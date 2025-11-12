<?php

use App\Models\User;

test('language chat page can be rendered for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('language-chat.index'));

    $response->assertOk();
    $response->assertInertia(
        fn($page) => $page
            ->component('LanguageChat')
            ->has('chatHistory')
            ->has('userSettings')
    );
});

test('language chat page requires authentication', function () {
    $response = $this->get(route('language-chat.index'));

    $response->assertRedirect(route('login'));
});

test('language chat page requires email verification', function () {
    $user = User::factory()->unverified()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('language-chat.index'));

    $response->assertRedirect(route('verification.notice'));
});

test('can create a new chat session', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('language-chat.create'), [
            'native_language' => 'Spanish',
            'target_language' => 'English',
            'proficiency_level' => 'B1',
        ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'id',
        'native_language',
        'target_language',
        'proficiency_level',
        'created_at',
    ]);

    $this->assertDatabaseHas('chat_sessions', [
        'user_id' => $user->id,
        'native_language' => 'Spanish',
        'target_language' => 'English',
        'proficiency_level' => 'B1',
    ]);
});

test('can send a message in a chat session', function () {
    $user = User::factory()->create();

    // Create a chat session first
    $sessionResponse = $this
        ->actingAs($user)
        ->post(route('language-chat.create'), [
            'native_language' => 'Spanish',
            'target_language' => 'English',
            'proficiency_level' => 'B1',
        ]);

    $sessionId = $sessionResponse->json('id');

    $response = $this
        ->actingAs($user)
        ->post(route('language-chat.message', $sessionId), [
            'message' => 'I would like to practice talking about my hobbies.',
        ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'user_message' => [
            'id',
            'content',
            'created_at',
        ],
        'ai_response' => [
            'id',
            'content',
            'created_at',
        ],
    ]);

    $this->assertDatabaseHas('chat_messages', [
        'chat_session_id' => $sessionId,
        'sender_type' => 'user',
        'content' => 'I would like to practice talking about my hobbies.',
    ]);
});

test('can retrieve chat history', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('language-chat.history'));

    $response->assertOk();
    $response->assertJsonStructure([
        'sessions' => [
            '*' => [
                'id',
                'title',
                'last_message_at',
                'native_language',
                'target_language',
            ]
        ]
    ]);
});

test('can retrieve messages for a specific chat session', function () {
    $user = User::factory()->create();

    // Create a chat session
    $sessionResponse = $this
        ->actingAs($user)
        ->post(route('language-chat.create'), [
            'native_language' => 'Spanish',
            'target_language' => 'English',
            'proficiency_level' => 'B1',
        ]);

    $sessionId = $sessionResponse->json('id');

    $response = $this
        ->actingAs($user)
        ->get(route('language-chat.messages', $sessionId));

    $response->assertOk();
    $response->assertJsonStructure([
        'messages' => [],
        'session' => [
            'id',
            'native_language',
            'target_language',
            'proficiency_level',
        ],
    ]);
});

test('cannot access another users chat session', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // User 1 creates a session
    $sessionResponse = $this
        ->actingAs($user1)
        ->post(route('language-chat.create'), [
            'native_language' => 'Spanish',
            'target_language' => 'English',
            'proficiency_level' => 'B1',
        ]);

    $sessionId = $sessionResponse->json('id');

    // User 2 tries to access it
    $response = $this
        ->actingAs($user2)
        ->get(route('language-chat.messages', $sessionId));

    $response->assertForbidden();
});

test('validates required fields when creating chat session', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(route('language-chat.create'), []);

    $response->assertSessionHasErrors(['native_language', 'target_language', 'proficiency_level']);
});

test('validates message content is not empty', function () {
    $user = User::factory()->create();

    $sessionResponse = $this
        ->actingAs($user)
        ->post(route('language-chat.create'), [
            'native_language' => 'Spanish',
            'target_language' => 'English',
            'proficiency_level' => 'B1',
        ]);

    $sessionId = $sessionResponse->json('id');

    $response = $this
        ->actingAs($user)
        ->post(route('language-chat.message', $sessionId), [
            'message' => '',
        ]);

    $response->assertSessionHasErrors(['message']);
});

test('generates title from first user message', function () {
    $user = User::factory()->create();

    // Create a chat session
    $sessionResponse = $this
        ->actingAs($user)
        ->post(route('language-chat.create'), [
            'native_language' => 'Spanish',
            'target_language' => 'English',
            'proficiency_level' => 'B1',
        ]);

    $sessionId = $sessionResponse->json('id');

    // Send first message
    $response = $this
        ->actingAs($user)
        ->post(route('language-chat.message', $sessionId), [
            'message' => 'I want to talk about my favorite hobbies and interests.',
        ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'user_message',
        'ai_response',
        'new_title',
    ]);

    // Title should be generated
    expect($response->json('new_title'))->not->toBeNull();

    // Check database has the new title
    $this->assertDatabaseHas('chat_sessions', [
        'id' => $sessionId,
    ]);

    $session = \App\Models\ChatSession::find($sessionId);
    expect($session->title)->not->toBe('New Conversation');
});

test('does not generate title after first message', function () {
    $user = User::factory()->create();

    $sessionResponse = $this
        ->actingAs($user)
        ->post(route('language-chat.create'), [
            'native_language' => 'Spanish',
            'target_language' => 'English',
            'proficiency_level' => 'B1',
        ]);

    $sessionId = $sessionResponse->json('id');

    // Send first message
    $this->actingAs($user)
        ->post(route('language-chat.message', $sessionId), [
            'message' => 'Hello, how are you?',
        ]);

    // Send second message
    $response = $this
        ->actingAs($user)
        ->post(route('language-chat.message', $sessionId), [
            'message' => 'I am doing well, thanks!',
        ]);

    $response->assertOk();
    expect($response->json('new_title'))->toBeNull();
});

test('can update chat session title', function () {
    $user = User::factory()->create();

    $sessionResponse = $this
        ->actingAs($user)
        ->post(route('language-chat.create'), [
            'native_language' => 'Spanish',
            'target_language' => 'English',
            'proficiency_level' => 'B1',
        ]);

    $sessionId = $sessionResponse->json('id');

    $response = $this
        ->actingAs($user)
        ->patch(route('language-chat.update-title', $sessionId), [
            'title' => 'My Custom Chat Title',
        ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'title',
    ]);

    $this->assertDatabaseHas('chat_sessions', [
        'id' => $sessionId,
        'title' => 'My Custom Chat Title',
    ]);
});

test('validates title is required when updating', function () {
    $user = User::factory()->create();

    $sessionResponse = $this
        ->actingAs($user)
        ->post(route('language-chat.create'), [
            'native_language' => 'Spanish',
            'target_language' => 'English',
            'proficiency_level' => 'B1',
        ]);

    $sessionId = $sessionResponse->json('id');

    $response = $this
        ->actingAs($user)
        ->patch(route('language-chat.update-title', $sessionId), [
            'title' => '',
        ]);

    $response->assertSessionHasErrors(['title']);
});

test('cannot update another users chat title', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $sessionResponse = $this
        ->actingAs($user1)
        ->post(route('language-chat.create'), [
            'native_language' => 'Spanish',
            'target_language' => 'English',
            'proficiency_level' => 'B1',
        ]);

    $sessionId = $sessionResponse->json('id');

    $response = $this
        ->actingAs($user2)
        ->patch(route('language-chat.update-title', $sessionId), [
            'title' => 'Hacked Title',
        ]);

    $response->assertForbidden();
});

test('can delete a chat session', function () {
    $user = User::factory()->create();

    $sessionResponse = $this
        ->actingAs($user)
        ->post(route('language-chat.create'), [
            'native_language' => 'Spanish',
            'target_language' => 'English',
            'proficiency_level' => 'B1',
        ]);

    $sessionId = $sessionResponse->json('id');

    $response = $this
        ->actingAs($user)
        ->delete(route('language-chat.destroy', $sessionId));

    $response->assertOk();

    $this->assertDatabaseMissing('chat_sessions', [
        'id' => $sessionId,
    ]);
});

test('cannot delete another users chat session', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $sessionResponse = $this
        ->actingAs($user1)
        ->post(route('language-chat.create'), [
            'native_language' => 'Spanish',
            'target_language' => 'English',
            'proficiency_level' => 'B1',
        ]);

    $sessionId = $sessionResponse->json('id');

    $response = $this
        ->actingAs($user2)
        ->delete(route('language-chat.destroy', $sessionId));

    $response->assertForbidden();
});

test('can update chat session parameters', function () {
    $user = User::factory()->create();

    $sessionResponse = $this
        ->actingAs($user)
        ->post(route('language-chat.create'), [
            'native_language' => 'Spanish',
            'target_language' => 'English',
            'proficiency_level' => 'B1',
        ]);

    $sessionId = $sessionResponse->json('id');

    $response = $this
        ->actingAs($user)
        ->patch(route('language-chat.update-parameters', $sessionId), [
            'native_language' => 'French',
            'target_language' => 'German',
            'proficiency_level' => 'B2',
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('chat_sessions', [
        'id' => $sessionId,
        'native_language' => 'French',
        'target_language' => 'German',
        'proficiency_level' => 'B2',
    ]);
});

test('validates parameters when updating chat session', function () {
    $user = User::factory()->create();

    $sessionResponse = $this
        ->actingAs($user)
        ->post(route('language-chat.create'), [
            'native_language' => 'Spanish',
            'target_language' => 'English',
            'proficiency_level' => 'B1',
        ]);

    $sessionId = $sessionResponse->json('id');

    $response = $this
        ->actingAs($user)
        ->patch(route('language-chat.update-parameters', $sessionId), [
            'native_language' => '',
            'target_language' => '',
            'proficiency_level' => 'Invalid',
        ]);

    $response->assertSessionHasErrors(['native_language', 'target_language', 'proficiency_level']);
});

test('cannot update another users chat session parameters', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $sessionResponse = $this
        ->actingAs($user1)
        ->post(route('language-chat.create'), [
            'native_language' => 'Spanish',
            'target_language' => 'English',
            'proficiency_level' => 'B1',
        ]);

    $sessionId = $sessionResponse->json('id');

    $response = $this
        ->actingAs($user2)
        ->patch(route('language-chat.update-parameters', $sessionId), [
            'native_language' => 'French',
            'target_language' => 'German',
            'proficiency_level' => 'B2',
        ]);

    $response->assertForbidden();
});

