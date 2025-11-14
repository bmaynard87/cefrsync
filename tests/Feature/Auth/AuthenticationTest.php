<?php

use App\Models\User;
use App\Services\ReCaptchaService;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    // Mock the ReCaptchaService
    $this->mock(ReCaptchaService::class, function ($mock) {
        $mock->shouldReceive('verify')
            ->once()
            ->andReturn(true);
    });

    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
        'recaptcha_token' => 'valid-token',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('language-chat.index', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    // Mock the ReCaptchaService
    $this->mock(ReCaptchaService::class, function ($mock) {
        $mock->shouldReceive('verify')
            ->once()
            ->andReturn(true);
    });

    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
        'recaptcha_token' => 'valid-token',
    ]);

    $this->assertGuest();
});

test('login fails without recaptcha token', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('recaptcha_token');
});

test('login fails with invalid recaptcha token', function () {
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
