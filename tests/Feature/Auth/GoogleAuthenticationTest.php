<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

test('user can sign in with Google credential', function () {
    Http::fake([
        'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
            'iss' => 'https://accounts.google.com',
            'azp' => config('services.google.client_id'),
            'aud' => config('services.google.client_id'),
            'sub' => '1234567890',
            'email' => 'test@example.com',
            'email_verified' => 'true',
            'name' => 'Test User',
            'given_name' => 'Test',
            'family_name' => 'User',
            'picture' => 'https://example.com/photo.jpg',
            'iat' => time(),
            'exp' => time() + 3600,
        ], 200),
    ]);

    $response = postJson(route('auth.google.callback'), [
        'credential' => 'mock-google-jwt-token',
    ]);

    $response->assertRedirect(route('learning-profile.show'));

    // User should be created
    assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'first_name' => 'Test',
        'last_name' => 'User',
        'google_id' => '1234567890',
    ]);

    // User should be authenticated
    $user = User::where('email', 'test@example.com')->first();
    assertAuthenticatedAs($user);
});

test('existing user with Google ID can sign in', function () {
    Http::fake([
        'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
            'iss' => 'https://accounts.google.com',
            'aud' => config('services.google.client_id'),
            'sub' => '1234567890',
            'email' => 'test@example.com',
            'email_verified' => 'true',
            'name' => 'Test User',
            'given_name' => 'Test',
            'family_name' => 'User',
        ], 200),
    ]);

    $existingUser = User::factory()->create([
        'email' => 'test@example.com',
        'google_id' => '1234567890',
        'first_name' => 'Test',
        'last_name' => 'User',
        'proficiency_level' => 'B1',
    ]);

    $response = postJson(route('auth.google.callback'), [
        'credential' => 'mock-google-jwt-token',
    ]);

    $response->assertRedirect('/');

    assertAuthenticatedAs($existingUser);
});

test('existing user without Google ID gets linked', function () {
    Http::fake([
        'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
            'iss' => 'https://accounts.google.com',
            'aud' => config('services.google.client_id'),
            'sub' => '1234567890',
            'email' => 'test@example.com',
            'email_verified' => 'true',
            'name' => 'Test User',
            'given_name' => 'Test',
            'family_name' => 'User',
        ], 200),
    ]);

    $existingUser = User::factory()->create([
        'email' => 'test@example.com',
        'google_id' => null,
        'first_name' => 'Test',
        'last_name' => 'User',
        'proficiency_level' => 'B1',
    ]);

    $response = postJson(route('auth.google.callback'), [
        'credential' => 'mock-google-jwt-token',
    ]);

    $response->assertRedirect('/');

    assertAuthenticatedAs($existingUser);

    // Google ID should be linked
    $existingUser->refresh();
    expect($existingUser->google_id)->toBe('1234567890');
});

test('Google sign-in fails with invalid credential', function () {
    Http::fake([
        'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
            'error' => 'invalid_token',
            'error_description' => 'Invalid Value',
        ], 400),
    ]);

    $response = postJson(route('auth.google.callback'), [
        'credential' => 'invalid-token',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['credential']);
});

test('Google sign-in requires credential field', function () {
    $response = postJson(route('auth.google.callback'), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['credential']);
});

test('Google sign-in fails if email not verified', function () {
    Http::fake([
        'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
            'iss' => 'https://accounts.google.com',
            'sub' => '1234567890',
            'email' => 'test@example.com',
            'email_verified' => 'false',
            'name' => 'Test User',
        ], 200),
    ]);

    $response = postJson(route('auth.google.callback'), [
        'credential' => 'mock-google-jwt-token',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['credential']);
});

test('new Google user needs language preferences', function () {
    Http::fake([
        'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
            'iss' => 'https://accounts.google.com',
            'aud' => config('services.google.client_id'),
            'sub' => '1234567890',
            'email' => 'test@example.com',
            'email_verified' => 'true',
            'name' => 'Test User',
            'given_name' => 'Test',
            'family_name' => 'User',
        ], 200),
    ]);

    $response = postJson(route('auth.google.callback'), [
        'credential' => 'mock-google-jwt-token',
    ]);

    // Should redirect to learning profile setup (which requires language setup first)
    $response->assertRedirect(route('learning-profile.show'));

    $user = User::where('email', 'test@example.com')->first();

    // Languages should be null for new Google users
    expect($user->native_language)->toBeNull();
    expect($user->target_language)->toBeNull();
    expect($user->proficiency_level)->toBeNull();
});

test('Google sign-in handles network errors gracefully', function () {
    Http::fake([
        'https://oauth2.googleapis.com/tokeninfo*' => Http::response('', 500),
    ]);

    $response = postJson(route('auth.google.callback'), [
        'credential' => 'mock-google-jwt-token',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['credential']);
});

test('new Google user has verified email', function () {
    Http::fake([
        'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
            'iss' => 'https://accounts.google.com',
            'aud' => config('services.google.client_id'),
            'sub' => '1234567890',
            'email' => 'test@example.com',
            'email_verified' => 'true',
            'name' => 'Test User',
            'given_name' => 'Test',
            'family_name' => 'User',
        ], 200),
    ]);

    $response = postJson(route('auth.google.callback'), [
        'credential' => 'mock-google-jwt-token',
    ]);

    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull('User should be created');
    expect($user->email_verified_at)->not->toBeNull('Email should be verified for Google users');
});

test('linked Google user has email verified', function () {
    Http::fake([
        'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
            'iss' => 'https://accounts.google.com',
            'aud' => config('services.google.client_id'),
            'sub' => '1234567890',
            'email' => 'test@example.com',
            'email_verified' => 'true',
            'name' => 'Test User',
            'given_name' => 'Test',
            'family_name' => 'User',
        ], 200),
    ]);

    $existingUser = User::factory()->create([
        'email' => 'test@example.com',
        'google_id' => null,
        'email_verified_at' => null, // Unverified email
        'first_name' => 'Test',
        'last_name' => 'User',
        'proficiency_level' => 'B1',
    ]);

    postJson(route('auth.google.callback'), [
        'credential' => 'mock-google-jwt-token',
    ]);

    $existingUser->refresh();
    expect($existingUser->google_id)->toBe('1234567890');
    expect($existingUser->email_verified_at)->not->toBeNull();
});

test('Google authenticated user can access verified routes', function () {
    Http::fake([
        'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
            'iss' => 'https://accounts.google.com',
            'aud' => config('services.google.client_id'),
            'sub' => '1234567890',
            'email' => 'test@example.com',
            'email_verified' => 'true',
            'name' => 'Test User',
            'given_name' => 'Test',
            'family_name' => 'User',
        ], 200),
    ]);

    // Create user with languages set so they skip proficiency opt-in
    $existingUser = User::factory()->create([
        'email' => 'test@example.com',
        'google_id' => null,
        'email_verified_at' => null, // Unverified
        'native_language' => 'en',
        'target_language' => 'es',
        'proficiency_level' => 'B1',
    ]);

    postJson(route('auth.google.callback'), [
        'credential' => 'mock-google-jwt-token',
    ]);

    // Should be able to access verified route
    $response = $this->get(route('language-chat.index'));
    $response->assertOk();
});
