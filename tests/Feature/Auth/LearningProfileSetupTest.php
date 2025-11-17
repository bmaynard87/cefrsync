<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('learning profile setup page is displayed for users without language setup', function () {
    $user = User::factory()->withoutLanguageSetup()->create();

    $response = $this
        ->actingAs($user)
        ->get('/learning-profile');

    $response->assertOk();
});

test('learning profile setup page redirects users with complete setup', function () {
    $user = User::factory()->create([
        'proficiency_level' => 'B1',
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/learning-profile');

    $response->assertRedirect(route('language-chat.index'));
});

test('user can complete learning profile setup with auto-update enabled', function () {
    $user = User::factory()->withoutLanguageSetup()->create();

    $response = $this
        ->actingAs($user)
        ->post('/learning-profile', [
            'native_language' => 'English',
            'target_language' => 'Spanish',
            'auto_update_proficiency' => true,
        ]);

    $response->assertRedirect(route('language-chat.index'));

    $user->refresh();
    expect($user->native_language)->toBe('English');
    expect($user->target_language)->toBe('Spanish');
    expect($user->auto_update_proficiency)->toBeTrue();
    expect($user->proficiency_level)->toBeNull(); // Null when auto-updating
});

test('user can complete learning profile setup with manual proficiency level', function () {
    $user = User::factory()->withoutLanguageSetup()->create();

    $response = $this
        ->actingAs($user)
        ->post('/learning-profile', [
            'native_language' => 'en',
            'target_language' => 'es',
            'proficiency_level' => 'B1',
            'auto_update_proficiency' => false,
        ]);

    $response->assertRedirect(route('language-chat.index'));

    $user->refresh();
    expect($user->native_language)->toBe('English');
    expect($user->target_language)->toBe('Spanish');
    expect($user->proficiency_level)->toBe('B1');
    expect($user->auto_update_proficiency)->toBeFalse();
});

test('learning profile setup requires all required fields', function () {
    $user = User::factory()->withoutLanguageSetup()->create();

    $response = $this
        ->actingAs($user)
        ->post('/learning-profile', []);

    $response->assertSessionHasErrors(['native_language', 'target_language', 'auto_update_proficiency']);
});

test('learning profile setup requires proficiency level when auto-update is disabled', function () {
    $user = User::factory()->withoutLanguageSetup()->create();

    $response = $this
        ->actingAs($user)
        ->post('/learning-profile', [
            'native_language' => 'English',
            'target_language' => 'Spanish',
            'auto_update_proficiency' => false,
            // Missing proficiency_level
        ]);

    $response->assertSessionHasErrors('proficiency_level');
});

test('learning profile setup accepts language keys', function () {
    $user = User::factory()->withoutLanguageSetup()->create();

    $response = $this
        ->actingAs($user)
        ->post('/learning-profile', [
            'native_language' => 'ja',
            'target_language' => 'en',
            'auto_update_proficiency' => true,
        ]);

    $response->assertRedirect(route('language-chat.index'));

    $user->refresh();
    expect($user->native_language)->toBe('Japanese');
    expect($user->target_language)->toBe('English');
});

test('learning profile setup accepts language names', function () {
    $user = User::factory()->withoutLanguageSetup()->create();

    $response = $this
        ->actingAs($user)
        ->post('/learning-profile', [
            'native_language' => 'French',
            'target_language' => 'German',
            'auto_update_proficiency' => true,
        ]);

    $response->assertRedirect(route('language-chat.index'));

    $user->refresh();
    expect($user->native_language)->toBe('French');
    expect($user->target_language)->toBe('German');
});

test('languages must be different', function () {
    $user = User::factory()->withoutLanguageSetup()->create();

    $response = $this
        ->actingAs($user)
        ->post('/learning-profile', [
            'native_language' => 'English',
            'target_language' => 'English',
            'auto_update_proficiency' => false,
            'proficiency_level' => 'B1',
        ]);

    $response->assertSessionHasErrors('target_language');
});

test('invalid language names return errors', function () {
    $user = User::factory()->withoutLanguageSetup()->create();

    $response = $this
        ->actingAs($user)
        ->post('/learning-profile', [
            'native_language' => 'InvalidLanguage',
            'target_language' => 'AnotherInvalidLanguage',
            'auto_update_proficiency' => true,
        ]);

    $response->assertSessionHasErrors(['native_language', 'target_language']);
});

test('proficiency level must be valid CEFR level', function () {
    $user = User::factory()->withoutLanguageSetup()->create();

    $response = $this
        ->actingAs($user)
        ->post('/learning-profile', [
            'native_language' => 'English',
            'target_language' => 'Spanish',
            'proficiency_level' => 'D1', // Invalid CEFR level
            'auto_update_proficiency' => false,
        ]);

    $response->assertSessionHasErrors('proficiency_level');
});

test('users without language setup are redirected after email verification', function () {
    $user = User::factory()->withoutLanguageSetup()->unverified()->create();

    // Simulate accessing protected route after verification
    $user->markEmailAsVerified();

    $response = $this
        ->actingAs($user)
        ->get('/language-chat');

    $response->assertRedirect(route('learning-profile.show'));
});

test('google oauth users without languages are redirected to learning profile setup', function () {
    // This is tested in GoogleAuthTest, but we verify the middleware behavior here
    $user = User::factory()->withoutLanguageSetup()->create([
        'google_id' => 'google-123',
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/language-chat');

    $response->assertRedirect(route('learning-profile.show'));
});
