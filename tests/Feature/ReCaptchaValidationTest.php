<?php

use App\Rules\ReCaptcha;
use App\Services\ReCaptchaService;
use Illuminate\Support\Facades\Validator;

test('recaptcha validation passes with valid token', function () {
    $this->mock(ReCaptchaService::class, function ($mock) {
        $mock->shouldReceive('verify')
            ->with('valid-token', 0.5)
            ->once()
            ->andReturn(true);
    });

    $rule = new ReCaptcha;
    $validator = Validator::make(
        ['recaptcha_token' => 'valid-token'],
        ['recaptcha_token' => $rule]
    );

    expect($validator->passes())->toBeTrue();
});

test('recaptcha validation fails with invalid token', function () {
    $this->mock(ReCaptchaService::class, function ($mock) {
        $mock->shouldReceive('verify')
            ->with('invalid-token', 0.5)
            ->once()
            ->andReturn(false);
    });

    $rule = new ReCaptcha;
    $validator = Validator::make(
        ['recaptcha_token' => 'invalid-token'],
        ['recaptcha_token' => $rule]
    );

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->first('recaptcha_token'))
        ->toBe('The reCAPTCHA verification failed. Please try again.');
});

test('recaptcha validation fails with empty token', function () {
    $rule = new ReCaptcha;
    $validator = Validator::make(
        ['recaptcha_token' => ''],
        ['recaptcha_token' => ['required', $rule]]
    );

    expect($validator->fails())->toBeTrue();
});

test('recaptcha validation fails when service throws exception', function () {
    $this->mock(ReCaptchaService::class, function ($mock) {
        $mock->shouldReceive('verify')
            ->with('error-token', 0.5)
            ->once()
            ->andThrow(new \Exception('API error'));
    });

    $rule = new ReCaptcha;
    $validator = Validator::make(
        ['recaptcha_token' => 'error-token'],
        ['recaptcha_token' => $rule]
    );

    expect($validator->fails())->toBeTrue();
});
