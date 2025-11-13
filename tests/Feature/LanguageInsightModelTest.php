<?php

use App\Models\ChatSession;
use App\Models\LanguageInsight;
use App\Models\User;

test('language insight has user relationship', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);
    $insight = LanguageInsight::factory()->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
    ]);

    expect($insight->user)->toBeInstanceOf(User::class);
    expect($insight->user->id)->toBe($user->id);
});

test('language insight has chat session relationship', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);
    $insight = LanguageInsight::factory()->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
    ]);

    expect($insight->chatSession)->toBeInstanceOf(ChatSession::class);
    expect($insight->chatSession->id)->toBe($session->id);
});

test('unread scope filters only unread insights', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    LanguageInsight::factory()->count(3)->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
        'is_read' => false,
    ]);

    LanguageInsight::factory()->count(2)->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
        'is_read' => true,
    ]);

    $unread = LanguageInsight::unread()->get();

    expect($unread)->toHaveCount(3);
    expect($unread->every(fn ($insight) => ! $insight->is_read))->toBeTrue();
});

test('forUser scope filters by user id', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $session1 = ChatSession::factory()->create(['user_id' => $user1->id]);
    $session2 = ChatSession::factory()->create(['user_id' => $user2->id]);

    LanguageInsight::factory()->count(3)->create([
        'user_id' => $user1->id,
        'chat_session_id' => $session1->id,
    ]);

    LanguageInsight::factory()->count(2)->create([
        'user_id' => $user2->id,
        'chat_session_id' => $session2->id,
    ]);

    $user1Insights = LanguageInsight::forUser($user1->id)->get();

    expect($user1Insights)->toHaveCount(3);
    expect($user1Insights->every(fn ($insight) => $insight->user_id === $user1->id))->toBeTrue();
});

test('data field is cast to array', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    $testData = [
        'patterns' => ['pattern1', 'pattern2'],
        'examples' => ['example1'],
    ];

    $insight = LanguageInsight::factory()->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
        'data' => $testData,
    ]);

    expect($insight->data)->toBeArray();
    expect($insight->data)->toBe($testData);
});

test('is_read field is cast to boolean', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    $insight = LanguageInsight::factory()->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
        'is_read' => true,
    ]);

    expect($insight->is_read)->toBeTrue();
    expect($insight->is_read)->toBeBool();
});

test('user has many language insights relationship', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    LanguageInsight::factory()->count(5)->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
    ]);

    expect($user->languageInsights)->toHaveCount(5);
    expect($user->languageInsights->first())->toBeInstanceOf(LanguageInsight::class);
});

test('chat session has many language insights relationship', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    LanguageInsight::factory()->count(3)->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
    ]);

    expect($session->insights)->toHaveCount(3);
    expect($session->insights->first())->toBeInstanceOf(LanguageInsight::class);
});
