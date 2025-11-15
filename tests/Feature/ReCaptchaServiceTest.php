<?php

use App\Services\ReCaptchaService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.recaptcha.secret_key' => 'test-secret-key',
        'services.recaptcha.verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
    ]);
});

test('verify method returns true for successful verification', function () {
    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
            'score' => 0.9,
            'action' => 'submit',
            'challenge_ts' => '2024-01-01T00:00:00Z',
            'hostname' => 'localhost',
        ], 200),
    ]);

    $service = new ReCaptchaService;
    $result = $service->verify('valid-token');

    expect($result)->toBeTrue();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://www.google.com/recaptcha/api/siteverify' &&
               $request['secret'] === 'test-secret-key' &&
               $request['response'] === 'valid-token';
    });
});

test('verify method returns false for failed verification', function () {
    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => false,
            'error-codes' => ['invalid-input-response'],
        ], 200),
    ]);

    $service = new ReCaptchaService;
    $result = $service->verify('invalid-token');

    expect($result)->toBeFalse();
});

test('verify method returns false for low score', function () {
    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
            'score' => 0.3,
            'action' => 'submit',
        ], 200),
    ]);

    $service = new ReCaptchaService;
    $result = $service->verify('low-score-token', 0.5);

    expect($result)->toBeFalse();
});

test('verify method passes with acceptable score', function () {
    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response([
            'success' => true,
            'score' => 0.6,
            'action' => 'submit',
        ], 200),
    ]);

    $service = new ReCaptchaService;
    $result = $service->verify('good-token', 0.5);

    expect($result)->toBeTrue();
});

test('verify method returns false on http exception', function () {
    Http::fake([
        'www.google.com/recaptcha/api/siteverify' => Http::response([], 500),
    ]);

    $service = new ReCaptchaService;
    $result = $service->verify('token');

    expect($result)->toBeFalse();
});

test('verify method returns false with empty token', function () {
    $service = new ReCaptchaService;
    $result = $service->verify('');

    expect($result)->toBeFalse();
});

test('verify method passes when secret key is not configured', function () {
    config(['services.recaptcha.secret_key' => null]);

    $service = new ReCaptchaService;
    $result = $service->verify('token');

    // When no secret key is configured, verification should pass (for dev/testing)
    expect($result)->toBeTrue();
});
