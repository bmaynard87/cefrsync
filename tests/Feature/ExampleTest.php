<?php

it('home page redirects to language chat', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('language-chat.index'));
});
