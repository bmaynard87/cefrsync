<?php

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\LanguageInsight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can soft delete their account', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->delete('/account', [
            'password' => 'password',
        ]);

    $response->assertRedirect('/');

    // User should be soft deleted (still in database but deleted_at is set)
    $this->assertSoftDeleted('users', ['id' => $user->id]);

    // User should not be retrievable via normal queries
    expect(User::find($user->id))->toBeNull();

    // But should be retrievable with trashed
    expect(User::withTrashed()->find($user->id))->not->toBeNull();
});

test('guest cannot delete account', function () {
    $response = $this->delete('/account');

    $response->assertRedirect('/login');
});

test('user cannot delete another user account', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $response = $this->actingAs($user1)
        ->delete("/users/{$user2->id}");

    $response->assertNotFound();

    // User 2 should still exist
    expect(User::find($user2->id))->not->toBeNull();
});

test('soft deleted user data is preserved', function () {
    $user = User::factory()->create([
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'target_language' => 'Japanese',
        'proficiency_level' => 'B1',
    ]);

    $this->actingAs($user)->delete('/account', [
        'password' => 'password',
    ]);

    $deletedUser = User::withTrashed()->find($user->id);

    expect($deletedUser->first_name)->toBe('Test');
    expect($deletedUser->last_name)->toBe('User');
    expect($deletedUser->email)->toBe('test@example.com');
    expect($deletedUser->target_language)->toBe('Japanese');
    expect($deletedUser->proficiency_level)->toBe('B1');
    expect($deletedUser->deleted_at)->not->toBeNull();
});

test('soft deleted user cannot login', function () {
    $user = User::factory()->create([
        'email' => 'deleted@example.com',
        'password' => bcrypt('testpassword'),
    ]);

    // Delete the account
    $this->actingAs($user)->delete('/account', [
        'password' => 'testpassword',
    ]);

    // Verify user is soft deleted
    $this->assertSoftDeleted('users', ['id' => $user->id]);

    // Attempt to login should fail
    $response = $this->post('/login', [
        'email' => 'deleted@example.com',
        'password' => 'testpassword',
    ]);

    // Should remain on login page or redirect back
    $this->assertGuest();
});

test('user sessions are preserved when account is soft deleted', function () {
    $user = User::factory()->create(['target_language' => 'French']);
    $session = ChatSession::factory()
        ->for($user)
        ->create();

    $this->actingAs($user)->delete('/account', [
        'password' => 'password',
    ]);

    // Session should still exist (not cascade deleted)
    expect(ChatSession::find($session->id))->not->toBeNull();

    // But user should be soft deleted
    $this->assertSoftDeleted('users', ['id' => $user->id]);
});

test('user messages are preserved when account is soft deleted', function () {
    $user = User::factory()->create(['target_language' => 'Spanish']);
    $session = ChatSession::factory()
        ->for($user)
        ->create();

    $message = ChatMessage::create([
        'chat_session_id' => $session->id,
        'sender_type' => 'user',
        'role' => 'user',
        'content' => 'Hola, ¿cómo estás?',
    ]);

    $this->actingAs($user)->delete('/account', [
        'password' => 'password',
    ]);

    // Message should still exist
    expect(ChatMessage::find($message->id))->not->toBeNull();

    // User should be soft deleted
    $this->assertSoftDeleted('users', ['id' => $user->id]);
});

test('user insights are preserved when account is soft deleted', function () {
    $user = User::factory()->create(['target_language' => 'German']);
    $session = ChatSession::factory()
        ->for($user)
        ->create();

    $insight = LanguageInsight::factory()
        ->for($user)
        ->for($session, 'chatSession')
        ->create();

    $this->actingAs($user)->delete('/account', [
        'password' => 'password',
    ]);

    // Insight should still exist
    expect(LanguageInsight::find($insight->id))->not->toBeNull();

    // User should be soft deleted
    $this->assertSoftDeleted('users', ['id' => $user->id]);
});

test('deleting account logs user out', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete('/account', [
            'password' => 'password',
        ])
        ->assertRedirect('/');

    // User should no longer be authenticated
    $this->assertGuest();
});

test('deleted account can be restored by admin', function () {
    $user = User::factory()->create(['email' => 'restore@example.com']);

    // Soft delete the user
    $this->actingAs($user)->delete('/account', [
        'password' => 'password',
    ]);

    $this->assertSoftDeleted('users', ['id' => $user->id]);

    // Restore the user (simulating admin action)
    $deletedUser = User::withTrashed()->find($user->id);
    $deletedUser->restore();

    // User should be active again
    expect(User::find($user->id))->not->toBeNull();
    expect(User::find($user->id)->deleted_at)->toBeNull();
});
