<?php

use App\Services\ReCaptchaService;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register without language data', function () {
    // Disable reCAPTCHA for this test by setting config to null
    config(['services.recaptcha.secret_key' => null]);

    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'SecureP@ssw0rd2024!',
        'password_confirmation' => 'SecureP@ssw0rd2024!',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/');

    $user = \App\Models\User::where('email', 'test@example.com')->first();
    expect($user->native_language)->toBeNull();
    expect($user->target_language)->toBeNull();
    expect($user->proficiency_level)->toBeNull();
});

test('registration with recaptcha enabled requires token', function () {
    config(['services.recaptcha.secret_key' => 'test-secret']);

    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'SecureP@ssw0rd2024!',
        'password_confirmation' => 'SecureP@ssw0rd2024!',
    ]);

    $response->assertSessionHasErrors('recaptcha_token');
});

test('registration with recaptcha validates token', function () {
    config(['services.recaptcha.secret_key' => 'test-secret']);

    // Mock the ReCaptchaService
    $this->mock(ReCaptchaService::class, function ($mock) {
        $mock->shouldReceive('verify')
            ->once()
            ->andReturn(true);
    });

    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'SecureP@ssw0rd2024!',
        'password_confirmation' => 'SecureP@ssw0rd2024!',
        'recaptcha_token' => 'valid-token',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/');
});

test('registration fails with invalid recaptcha token', function () {
    config(['services.recaptcha.secret_key' => 'test-secret']);

    // Mock the ReCaptchaService to return false
    $this->mock(ReCaptchaService::class, function ($mock) {
        $mock->shouldReceive('verify')
            ->once()
            ->andReturn(false);
    });

    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'SecureP@ssw0rd2024!',
        'password_confirmation' => 'SecureP@ssw0rd2024!',
        'recaptcha_token' => 'invalid-token',
    ]);

    $response->assertSessionHasErrors('recaptcha_token');
});

test('registration requires first and last name', function () {
    config(['services.recaptcha.secret_key' => null]);

    $response = $this->post('/register', [
        'email' => 'test@example.com',
        'password' => 'SecureP@ssw0rd2024!',
        'password_confirmation' => 'SecureP@ssw0rd2024!',
    ]);

    $response->assertSessionHasErrors(['first_name', 'last_name']);
});

test('registration requires valid email', function () {
    config(['services.recaptcha.secret_key' => null]);

    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'not-an-email',
        'password' => 'SecureP@ssw0rd2024!',
        'password_confirmation' => 'SecureP@ssw0rd2024!',
    ]);

    $response->assertSessionHasErrors('email');
});

test('registration requires password confirmation', function () {
    config(['services.recaptcha.secret_key' => null]);

    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'SecureP@ssw0rd2024!',
        'password_confirmation' => 'DifferentPassword123!',
    ]);

    $response->assertSessionHasErrors('password');
});
