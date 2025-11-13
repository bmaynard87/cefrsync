<?php

use App\Models\User;
use App\Models\ChatSession;
use App\Models\LanguageInsight;

test('can retrieve insights for authenticated user', function () {
    $user = User::factory()->create([
        'native_language' => 'English',
        'target_language' => 'Japanese',
        'proficiency_level' => 'B1',
    ]);

    $session = ChatSession::factory()->create([
        'user_id' => $user->id,
        'native_language' => $user->native_language,
        'target_language' => $user->target_language,
        'proficiency_level' => $user->proficiency_level,
    ]);

    LanguageInsight::factory()->count(3)->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
    ]);

    $response = $this->actingAs($user)->getJson('/insights');

    $response->assertOk()
        ->assertJsonStructure([
            'insights' => [
                '*' => ['id', 'insight_type', 'title', 'message', 'data', 'is_read', 'created_at'],
            ],
            'unread_count',
        ])
        ->assertJsonCount(3, 'insights');
});

test('insights endpoint requires authentication', function () {
    $response = $this->getJson('/insights');

    $response->assertUnauthorized();
});

test('user can only see their own insights', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);
    
    $otherUser = User::factory()->create();
    $otherSession = ChatSession::factory()->create(['user_id' => $otherUser->id]);

    LanguageInsight::factory()->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
    ]);

    LanguageInsight::factory()->count(2)->create([
        'user_id' => $otherUser->id,
        'chat_session_id' => $otherSession->id,
    ]);

    $response = $this->actingAs($user)->getJson('/insights');

    $response->assertOk()
        ->assertJsonCount(1, 'insights');
});

test('can filter insights by unread status', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    LanguageInsight::factory()->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
        'is_read' => false,
    ]);

    LanguageInsight::factory()->count(2)->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
        'is_read' => true,
    ]);

    $response = $this->actingAs($user)->getJson('/insights?unread_only=1');

    $response->assertOk()
        ->assertJsonCount(1, 'insights');
});

test('can limit number of insights returned', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    LanguageInsight::factory()->count(15)->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
    ]);

    $response = $this->actingAs($user)->getJson('/insights?limit=5');

    $response->assertOk()
        ->assertJsonCount(5, 'insights');
});

test('can mark an insight as read', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    $insight = LanguageInsight::factory()->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
        'is_read' => false,
    ]);

    $response = $this->actingAs($user)
        ->patchJson("/insights/{$insight->id}/read");

    $response->assertOk()
        ->assertJsonFragment(['message' => 'Insight marked as read']);

    expect($insight->fresh()->is_read)->toBeTrue();
});

test('cannot mark another users insight as read', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherSession = ChatSession::factory()->create(['user_id' => $otherUser->id]);

    $insight = LanguageInsight::factory()->create([
        'user_id' => $otherUser->id,
        'chat_session_id' => $otherSession->id,
    ]);

    $response = $this->actingAs($user)
        ->patchJson("/insights/{$insight->id}/read");

    $response->assertForbidden();
});

test('can mark all insights as read', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    LanguageInsight::factory()->count(3)->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
        'is_read' => false,
    ]);

    $response = $this->actingAs($user)
        ->postJson('/insights/mark-all-read');

    $response->assertOk()
        ->assertJsonFragment(['count' => 3]);

    expect($user->languageInsights()->unread()->count())->toBe(0);
});

test('can delete an insight', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    $insight = LanguageInsight::factory()->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
    ]);

    $response = $this->actingAs($user)
        ->deleteJson("/insights/{$insight->id}");

    $response->assertOk()
        ->assertJsonFragment(['message' => 'Insight deleted']);

    expect(LanguageInsight::find($insight->id))->toBeNull();
});

test('cannot delete another users insight', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $otherSession = ChatSession::factory()->create(['user_id' => $otherUser->id]);

    $insight = LanguageInsight::factory()->create([
        'user_id' => $otherUser->id,
        'chat_session_id' => $otherSession->id,
    ]);

    $response = $this->actingAs($user)
        ->deleteJson("/insights/{$insight->id}");

    $response->assertForbidden();

    expect(LanguageInsight::find($insight->id))->not->toBeNull();
});

test('unread count is accurate', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    LanguageInsight::factory()->count(5)->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
        'is_read' => false,
    ]);

    LanguageInsight::factory()->count(3)->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
        'is_read' => true,
    ]);

    $response = $this->actingAs($user)->getJson('/insights');

    $response->assertOk()
        ->assertJsonFragment(['unread_count' => 5]);
});

test('insights are ordered by creation date descending', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    $oldest = LanguageInsight::factory()->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
        'created_at' => now()->subDays(3),
    ]);

    $middle = LanguageInsight::factory()->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
        'created_at' => now()->subDays(1),
    ]);

    $newest = LanguageInsight::factory()->create([
        'user_id' => $user->id,
        'chat_session_id' => $session->id,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user)->getJson('/insights');

    $insights = $response->json('insights');
    expect($insights[0]['id'])->toBe($newest->id);
    expect($insights[1]['id'])->toBe($middle->id);
    expect($insights[2]['id'])->toBe($oldest->id);
});

