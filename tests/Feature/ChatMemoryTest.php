<?php

use App\Models\ChatSession;
use App\Models\User;
use App\Services\OpenAiService;

beforeEach(function () {
    // Mock OpenAI for these tests
    $this->mock(OpenAiService::class, function ($mock) {
        $mock->shouldReceive('formatConversationHistory')->andReturnUsing(function ($messages) {
            return collect($messages)->map(function ($message) {
                return [
                    'role' => $message['sender_type'] === 'user' ? 'user' : 'assistant',
                    'content' => $message['content'],
                ];
            })->toArray();
        });
    });
});

test('system prompt includes instruction to maintain conversation continuity', function () {
    $service = new OpenAiService;

    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('buildSystemPrompt');
    $method->setAccessible(true);

    $prompt = $method->invoke($service, 'English', 'B1', null, 'Learner name: Brandon');

    expect($prompt)->toContain('Maintain conversation continuity');
    expect($prompt)->toContain('remember what has already been discussed');
    expect($prompt)->toContain('avoid asking questions about information already shared');
});

test('system prompt includes learner name in user context', function () {
    $service = new OpenAiService;

    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('buildSystemPrompt');
    $method->setAccessible(true);

    $prompt = $method->invoke($service, 'English', 'B1', null, 'Learner name: Brandon');

    expect($prompt)->toContain('Learner name: Brandon');
});

test('conversation history is properly formatted for OpenAI', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    // Create a conversation
    $session->messages()->create(['sender_type' => 'user', 'content' => 'Hi']);
    $session->messages()->create(['sender_type' => 'assistant', 'content' => 'Hello!']);
    $session->messages()->create(['sender_type' => 'user', 'content' => 'How are you?']);

    $conversationHistory = $session->messages()
        ->where('message_type', '!=', 'correction')
        ->orderBy('created_at')
        ->get(['sender_type', 'content'])
        ->toArray();

    $service = new OpenAiService;
    $formatted = $service->formatConversationHistory($conversationHistory);

    expect($formatted)->toHaveCount(3);
    expect($formatted[0])->toEqual(['role' => 'user', 'content' => 'Hi']);
    expect($formatted[1])->toEqual(['role' => 'assistant', 'content' => 'Hello!']);
    expect($formatted[2])->toEqual(['role' => 'user', 'content' => 'How are you?']);
});

test('buildUserContext includes learner name', function () {
    $user = User::factory()->create([
        'first_name' => 'Alice',
        'last_name' => 'Smith',
    ]);
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    $controller = new \App\Http\Controllers\LanguageChatController(
        app(OpenAiService::class),
        app(\App\Services\LangGptService::class)
    );

    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('buildUserContext');
    $method->setAccessible(true);

    $context = $method->invoke($controller, $user);

    expect($context)->toBe('Learner name: Alice Smith');
});
