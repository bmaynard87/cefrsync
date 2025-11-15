<?php

use App\Services\ReCaptchaService;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
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
        'native_language' => 'English',
        'target_language' => 'Spanish',
        'proficiency_level' => 'B1',
        'recaptcha_token' => 'valid-token',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/');
});

test('registration fails without recaptcha token', function () {
    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'password' => 'SecureP@ssw0rd2024!',
        'password_confirmation' => 'SecureP@ssw0rd2024!',
        'native_language' => 'English',
        'target_language' => 'Spanish',
        'proficiency_level' => 'B1',
    ]);

    $response->assertSessionHasErrors('recaptcha_token');
});

test('registration fails with invalid recaptcha token', function () {
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
        'native_language' => 'English',
        'target_language' => 'Spanish',
        'proficiency_level' => 'B1',
        'recaptcha_token' => 'invalid-token',
    ]);

    $response->assertSessionHasErrors('recaptcha_token');
});

test('new users can register without proficiency level', function () {
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
        'native_language' => 'English',
        'target_language' => 'Spanish',
        'proficiency_level' => '',
        'recaptcha_token' => 'valid-token',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('proficiency-opt-in.show'));

    $user = \App\Models\User::where('email', 'test@example.com')->first();
    expect($user->proficiency_level)->toBeNull();
});

test('registration fails without native language', function () {
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
        'target_language' => 'Spanish',
        'proficiency_level' => 'B1',
        'recaptcha_token' => 'valid-token',
    ]);

    $response->assertSessionHasErrors('native_language');
});

test('registration fails without target language', function () {
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
        'native_language' => 'English',
        'proficiency_level' => 'B1',
        'recaptcha_token' => 'valid-token',
    ]);

    $response->assertSessionHasErrors('target_language');
});
