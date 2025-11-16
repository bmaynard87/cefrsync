<?php

use App\Services\LangGptService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->service = new LangGptService;
});

it('detects when LangGPT service is available', function () {
    Http::fake([
        '*/v2/health' => Http::response([
            'status' => 'healthy',
            'api_version' => 'v2',
        ], 200),
    ]);

    $result = $this->service->health();

    expect($result)->toHaveKey('success', true);
    expect($result)->toHaveKey('status', 200);
});

it('detects when LangGPT service is unavailable', function () {
    Http::fake([
        '*/v2/health' => Http::response(null, 503),
    ]);

    $result = $this->service->health();

    expect($result['success'])->toBe(false);
});

it('handles connection timeout', function () {
    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
    });

    $result = $this->service->health();

    expect($result['success'])->toBe(false);
    expect($result)->toHaveKey('error');
});

it('handles network errors gracefully', function () {
    Http::fake(function () {
        throw new \Exception('Network unreachable');
    });

    $result = $this->service->health();

    expect($result['success'])->toBe(false);
    expect($result)->toHaveKey('error');
    expect($result['error'])->toContain('Network unreachable');
});

it('isAvailable method returns true when service is healthy', function () {
    Http::fake([
        '*/v2/health' => Http::response([
            'status' => 'healthy',
            'api_version' => 'v2',
        ], 200),
    ]);

    expect($this->service->isAvailable())->toBe(true);
});

it('isAvailable method returns false when service is down', function () {
    Http::fake([
        '*/v2/health' => Http::response(null, 503),
    ]);

    expect($this->service->isAvailable())->toBe(false);
});

it('isAvailable method returns false on connection error', function () {
    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
    });

    expect($this->service->isAvailable())->toBe(false);
});
