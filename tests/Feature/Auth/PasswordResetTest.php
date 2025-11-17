<?php

use App\Models\User;
use App\Services\ReCaptchaService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();

    config(['services.recaptcha.secret_key' => 'test-secret-key']);

    // Mock the ReCaptchaService
    $this->mock(ReCaptchaService::class, function ($mock) {
        $mock->shouldReceive('verify')
            ->once()
            ->andReturn(true);
    });

    $user = User::factory()->create();

    $this->post('/forgot-password', [
        'email' => $user->email,
        'recaptcha_token' => 'valid-token',
    ]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('password reset fails without recaptcha token', function () {
    config(['services.recaptcha.secret_key' => 'test-secret-key']);

    $user = User::factory()->create();

    $response = $this->post('/forgot-password', [
        'email' => $user->email,
    ]);

    $response->assertSessionHasErrors('recaptcha_token');
});

test('password reset fails with invalid recaptcha token', function () {
    config(['services.recaptcha.secret_key' => 'test-secret-key']);

    // Mock the ReCaptchaService to return false
    $this->mock(ReCaptchaService::class, function ($mock) {
        $mock->shouldReceive('verify')
            ->once()
            ->andReturn(false);
    });

    $user = User::factory()->create();

    $response = $this->post('/forgot-password', [
        'email' => $user->email,
        'recaptcha_token' => 'invalid-token',
    ]);

    $response->assertSessionHasErrors('recaptcha_token');
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    config(['services.recaptcha.secret_key' => 'test-secret-key']);

    // Mock the ReCaptchaService
    $this->mock(ReCaptchaService::class, function ($mock) {
        $mock->shouldReceive('verify')
            ->once()
            ->andReturn(true);
    });

    $user = User::factory()->create();

    $this->post('/forgot-password', [
        'email' => $user->email,
        'recaptcha_token' => 'valid-token',
    ]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    config(['services.recaptcha.secret_key' => 'test-secret-key']);

    // Mock the ReCaptchaService
    $this->mock(ReCaptchaService::class, function ($mock) {
        $mock->shouldReceive('verify')
            ->once()
            ->andReturn(true);
    });

    $user = User::factory()->create();

    $this->post('/forgot-password', [
        'email' => $user->email,
        'recaptcha_token' => 'valid-token',
    ]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'SecureP@ssw0rd2024!',
            'password_confirmation' => 'SecureP@ssw0rd2024!',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });
});
