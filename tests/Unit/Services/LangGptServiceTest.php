<?php

use App\Services\LangGptService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

uses(Tests\TestCase::class);

beforeEach(function () {
    // Set up test configuration
    Config::set('services.langgpt', [
        'url' => 'http://test-langgpt.local:8000',
        'version' => 'v2',
        'key' => 'test_api_key_123',
        'timeout' => 30,
    ]);
});

describe('LangGptService Configuration', function () {
    it('initializes with correct configuration', function () {
        $service = new LangGptService();

        expect($service->getBaseUrl())->toBe('http://test-langgpt.local:8000');
        expect($service->getApiVersion())->toBe('v2');
        expect($service->hasApiKey())->toBeTrue();
    });

    it('handles missing API key configuration', function () {
        Config::set('services.langgpt.key', null);

        $service = new LangGptService();

        expect($service->hasApiKey())->toBeFalse();
    });
});

describe('LangGptService HTTP Requests', function () {
    it('makes successful ping request', function () {
        Http::fake([
            'http://test-langgpt.local:8000/v2/ping' => Http::response([
                'message' => 'pong from LangGPT API v2!',
                'version' => '2.0',
                'timestamp' => '2025-11-12T07:00:00Z',
                'features' => ['api_keys', 'versioning']
            ], 200, [
                'API-Version' => 'v2',
                'API-Latest-Version' => 'v2'
            ])
        ]);

        $service = new LangGptService();
        $result = $service->ping();

        expect($result['success'])->toBeTrue();
        expect($result['status'])->toBe(200);
        expect($result['data']['message'])->toBe('pong from LangGPT API v2!');
        expect($result['data']['version'])->toBe('2.0');

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-API-Key', 'test_api_key_123') &&
                $request->url() === 'http://test-langgpt.local:8000/v2/ping';
        });
    });

    it('makes successful health request', function () {
        Http::fake([
            'http://test-langgpt.local:8000/v2/health' => Http::response([
                'status' => 'healthy',
                'service' => 'LangGPT',
                'version' => '2.0',
                'components' => [
                    'api' => 'healthy',
                    'auth' => 'healthy'
                ]
            ], 200)
        ]);

        $service = new LangGptService();
        $result = $service->health();

        expect($result['success'])->toBeTrue();
        expect($result['data']['status'])->toBe('healthy');
        expect($result['data']['service'])->toBe('LangGPT');
    });

    it('makes successful features request', function () {
        Http::fake([
            'http://test-langgpt.local:8000/v2/features' => Http::response([
                'version' => '2.0',
                'features' => [
                    'api_key_authentication',
                    'version_management',
                    'enhanced_responses'
                ],
                'deprecated_features' => [],
                'coming_soon' => ['rate_limiting']
            ], 200)
        ]);

        $service = new LangGptService();
        $result = $service->features();

        expect($result['success'])->toBeTrue();
        expect($result['data']['features'])->toContain('api_key_authentication');
    });

    it('prevents features request on v1 API', function () {
        Config::set('services.langgpt.version', 'v1');

        $service = new LangGptService();
        $result = $service->features();

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toBe('Features endpoint only available in API v2');
    });
});

describe('LangGptService Protected Endpoints', function () {
    it('makes successful protected ping request', function () {
        Http::fake([
            'http://test-langgpt.local:8000/v2/protected/ping' => Http::response([
                'message' => 'pong from protected endpoint v2!',
                'authenticated_with' => 'test-key',
                'version' => '2.0'
            ], 200)
        ]);

        $service = new LangGptService();
        $result = $service->protectedPing();

        expect($result['success'])->toBeTrue();
        expect($result['data']['authenticated_with'])->toBe('test-key');
    });

    it('makes successful profile request', function () {
        Http::fake([
            'http://test-langgpt.local:8000/v2/protected/profile' => Http::response([
                'profile' => [
                    'key_name' => 'test-key',
                    'created_at' => '2025-11-12T07:00:00Z',
                    'is_active' => true,
                    'version' => '2.0'
                ]
            ], 200)
        ]);

        $service = new LangGptService();
        $result = $service->profile();

        expect($result['success'])->toBeTrue();
        expect($result['data']['profile']['key_name'])->toBe('test-key');
    });

    it('prevents protected requests without API key', function () {
        Config::set('services.langgpt.key', null);

        $service = new LangGptService();

        $pingResult = $service->protectedPing();
        $profileResult = $service->profile();

        expect($pingResult['success'])->toBeFalse();
        expect($pingResult['error'])->toBe('API key required for protected endpoints');

        expect($profileResult['success'])->toBeFalse();
        expect($profileResult['error'])->toBe('API key required for profile endpoint');
    });
});

describe('LangGptService Error Handling', function () {
    it('handles network timeout errors', function () {
        Http::fake([
            'http://test-langgpt.local:8000/v2/ping' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
            }
        ]);

        $service = new LangGptService();
        $result = $service->ping();

        expect($result['success'])->toBeFalse();
        expect($result['error'])->toContain('Connection timeout');
        expect($result['status'])->toBe(0);
    });

    it('handles HTTP error responses', function () {
        Http::fake([
            'http://test-langgpt.local:8000/v2/ping' => Http::response([
                'detail' => 'Internal Server Error'
            ], 500)
        ]);

        $service = new LangGptService();
        $result = $service->ping();

        expect($result['success'])->toBeFalse();
        expect($result['status'])->toBe(500);
        expect($result['data']['detail'])->toBe('Internal Server Error');
    });

    it('handles unauthorized responses', function () {
        Http::fake([
            'http://test-langgpt.local:8000/v2/protected/ping' => Http::response([
                'detail' => 'Invalid or inactive API key'
            ], 401)
        ]);

        $service = new LangGptService();
        $result = $service->protectedPing();

        expect($result['success'])->toBeFalse();
        expect($result['status'])->toBe(401);
        expect($result['data']['detail'])->toBe('Invalid or inactive API key');
    });

    it('handles malformed JSON responses', function () {
        Http::fake([
            'http://test-langgpt.local:8000/v2/ping' => Http::response('invalid json', 200)
        ]);

        $service = new LangGptService();
        $result = $service->ping();

        expect($result['success'])->toBeTrue(); // HTTP was successful
        expect($result['data'])->toBeNull(); // But JSON parsing failed
    });
});

describe('LangGptService HTTP Methods', function () {
    it('supports GET requests', function () {
        Http::fake([
            'http://test-langgpt.local:8000/v2/ping' => Http::response(['method' => 'GET'], 200),
        ]);

        $service = new LangGptService();

        // Test GET (via ping which uses GET internally)
        $getResult = $service->ping();
        expect($getResult['success'])->toBeTrue();
    });

    it('throws exception for unsupported HTTP methods', function () {
        $service = new LangGptService();

        expect(function () use ($service) {
            $reflection = new \ReflectionClass($service);
            $method = $reflection->getMethod('makeRequest');
            $method->setAccessible(true);
            $method->invoke($service, 'PATCH', 'test');
        })->toThrow(\InvalidArgumentException::class, 'Unsupported HTTP method: PATCH');
    });
});