<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
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

    $this->assertAuthenticated();
    $response->assertRedirect('/');
});
