<?php

use App\Models\User;

it('shows welcome page for guests', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Welcome')
        ->has('canLogin')
        ->has('canRegister')
    );
});

it('redirects authenticated users to language chat', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertRedirect(route('language-chat.index'));
});
