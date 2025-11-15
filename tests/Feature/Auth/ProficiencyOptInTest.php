<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('proficiency opt-in page is displayed for users without proficiency level', function () {
    $user = User::factory()->create([
        'proficiency_level' => null,
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/proficiency-opt-in');

    $response->assertOk();
});

test('proficiency opt-in page redirects users who already have proficiency level', function () {
    $user = User::factory()->create([
        'proficiency_level' => 'B1',
    ]);

    $response = $this
        ->actingAs($user)
        ->get('/proficiency-opt-in');

    $response->assertRedirect(route('language-chat.index'));
});

test('user can opt in to auto update proficiency', function () {
    $user = User::factory()->create([
        'proficiency_level' => null,
        'auto_update_proficiency' => false,
    ]);

    $response = $this
        ->actingAs($user)
        ->post('/proficiency-opt-in', [
            'auto_update_proficiency' => true,
        ]);

    $response->assertRedirect(route('language-chat.index'));

    expect($user->fresh()->auto_update_proficiency)->toBeTrue();
});

test('user can opt out of auto update proficiency', function () {
    $user = User::factory()->create([
        'proficiency_level' => null,
        'auto_update_proficiency' => true,
    ]);

    $response = $this
        ->actingAs($user)
        ->post('/proficiency-opt-in', [
            'auto_update_proficiency' => false,
        ]);

    $response->assertRedirect(route('language-chat.index'));

    expect($user->fresh()->auto_update_proficiency)->toBeFalse();
});

test('proficiency opt-in requires auto_update_proficiency field', function () {
    $user = User::factory()->create([
        'proficiency_level' => null,
    ]);

    $response = $this
        ->actingAs($user)
        ->post('/proficiency-opt-in', []);

    $response->assertSessionHasErrors('auto_update_proficiency');
});

test('proficiency opt-in does not update users who already have proficiency level', function () {
    $user = User::factory()->create([
        'proficiency_level' => 'B2',
        'auto_update_proficiency' => false,
    ]);

    $response = $this
        ->actingAs($user)
        ->post('/proficiency-opt-in', [
            'auto_update_proficiency' => true,
        ]);

    $response->assertRedirect(route('language-chat.index'));

    // Should not update because user already has a proficiency level
    expect($user->fresh()->auto_update_proficiency)->toBeFalse();
});

test('users without proficiency level are redirected to opt-in page after registration', function () {
    // Mock the ReCaptchaService
    $this->mock(\App\Services\ReCaptchaService::class)
        ->shouldReceive('verify')
        ->once()
        ->andReturn(true);

    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'SuperStrongPassword123!@#',
        'password_confirmation' => 'SuperStrongPassword123!@#',
        'native_language' => 'English',
        'target_language' => 'Spanish',
        'proficiency_level' => '', // No proficiency level selected
        'recaptcha_token' => 'test-token',
    ]);

    $response->assertRedirect(route('proficiency-opt-in.show'));
});

test('users with proficiency level skip opt-in page after registration', function () {
    // Mock the ReCaptchaService
    $this->mock(\App\Services\ReCaptchaService::class)
        ->shouldReceive('verify')
        ->once()
        ->andReturn(true);

    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test2@example.com',
        'password' => 'SuperStrongPassword123!@#',
        'password_confirmation' => 'SuperStrongPassword123!@#',
        'native_language' => 'English',
        'target_language' => 'Spanish',
        'proficiency_level' => 'B1',
        'recaptcha_token' => 'test-token',
    ]);

    $response->assertRedirect('/');
});
