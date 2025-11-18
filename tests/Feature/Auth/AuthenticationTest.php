<?php

use App\Models\User;
use App\Services\ReCaptchaService;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    // Ensure reCAPTCHA is not configured for this test
    config(['services.recaptcha.secret_key' => null]);

    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('language-chat.index', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    // Ensure reCAPTCHA is not configured for this test
    config(['services.recaptcha.secret_key' => null]);

    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('login fails without recaptcha token when recaptcha is configured', function () {
    // Temporarily set reCAPTCHA secret key to make validation required
    config(['services.recaptcha.secret_key' => 'test-secret-key']);

    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('recaptcha_token');
});

test('login fails with invalid recaptcha token when recaptcha is configured', function () {
    // Temporarily set reCAPTCHA secret key to make validation required
    config(['services.recaptcha.secret_key' => 'test-secret-key']);

    // Mock the ReCaptchaService to return false
    $this->mock(ReCaptchaService::class, function ($mock) {
        $mock->shouldReceive('verify')
            ->once()
            ->andReturn(false);
    });

    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'recaptcha_token' => 'invalid-token',
    ]);

    $response->assertSessionHasErrors('recaptcha_token');
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
