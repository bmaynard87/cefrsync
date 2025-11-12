<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    // Set up test configuration
    Config::set('services.langgpt', [
        'url' => 'http://test-langgpt.local:8000',
        'version' => 'v2',
        'key' => 'test_api_key_123',
        'timeout' => 30,
    ]);
});

describe('LangGPT Integration Endpoints', function () {
    describe('/ping-langgpt endpoint', function () {
        it('returns successful ping response with API info', function () {
            Http::fake([
                'http://test-langgpt.local:8000/v2/ping' => Http::response([
                    'message' => 'pong from LangGPT API v2!',
                    'version' => '2.0',
                    'timestamp' => '2025-11-12T07:00:00Z',
                    'features' => ['api_keys', 'versioning', 'enhanced_responses']
                ], 200, [
                    'API-Version' => 'v2',
                    'API-Latest-Version' => 'v2',
                    'API-Supported-Versions' => 'v1, v2'
                ])
            ]);

            $response = $this->get('/ping-langgpt');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'ping_result' => [
                            'success',
                            'data' => [
                                'message',
                                'version',
                                'timestamp',
                                'features'
                            ],
                            'status',
                            'headers'
                        ],
                        'api_info' => [
                            'version',
                            'base_url',
                            'has_api_key'
                        ]
                    ]);

            $data = $response->json();
            expect($data['ping_result']['success'])->toBeTrue();
            expect($data['ping_result']['data']['message'])->toBe('pong from LangGPT API v2!');
            expect($data['api_info']['version'])->toBe('v2');
            expect($data['api_info']['has_api_key'])->toBeTrue();
        });

        it('handles LangGPT API failure gracefully', function () {
            Http::fake([
                'http://test-langgpt.local:8000/v2/ping' => Http::response([
                    'detail' => 'Service unavailable'
                ], 503)
            ]);

            $response = $this->get('/ping-langgpt');

            $response->assertStatus(200); // Endpoint should still return 200
            
            $data = $response->json();
            expect($data['ping_result']['success'])->toBeFalse();
            expect($data['ping_result']['status'])->toBe(503);
        });

        it('handles network connectivity issues', function () {
            Http::fake([
                'http://test-langgpt.local:8000/v2/ping' => function () {
                    throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
                }
            ]);

            $response = $this->get('/ping-langgpt');

            $response->assertStatus(200);
            
            $data = $response->json();
            expect($data['ping_result']['success'])->toBeFalse();
            expect($data['ping_result']['error'])->toContain('Connection refused');
        });
    });

    describe('/langgpt-status endpoint', function () {
        it('returns comprehensive status when all services are healthy', function () {
            Http::fake([
                'http://test-langgpt.local:8000/v2/ping' => Http::response([
                    'message' => 'pong from LangGPT API v2!',
                    'version' => '2.0'
                ], 200),
                'http://test-langgpt.local:8000/v2/health' => Http::response([
                    'status' => 'healthy',
                    'service' => 'LangGPT',
                    'version' => '2.0',
                    'components' => [
                        'api' => 'healthy',
                        'auth' => 'healthy',
                        'storage' => 'healthy'
                    ]
                ], 200),
                'http://test-langgpt.local:8000/v2/features' => Http::response([
                    'version' => '2.0',
                    'features' => [
                        'api_key_authentication',
                        'version_management',
                        'enhanced_responses'
                    ]
                ], 200),
                'http://test-langgpt.local:8000/v2/protected/ping' => Http::response([
                    'message' => 'pong from protected endpoint v2!',
                    'authenticated_with' => 'test-key',
                    'version' => '2.0'
                ], 200),
                'http://test-langgpt.local:8000/v2/protected/profile' => Http::response([
                    'profile' => [
                        'key_name' => 'test-key',
                        'created_at' => '2025-11-12T07:00:00Z',
                        'is_active' => true,
                        'version' => '2.0'
                    ]
                ], 200)
            ]);

            $response = $this->get('/langgpt-status');

            $response->assertStatus(200)
                    ->assertJsonStructure([
                        'ping' => ['success', 'data', 'status'],
                        'health' => ['success', 'data', 'status'],
                        'features' => ['success', 'data', 'status'],
                        'api_info' => ['version', 'base_url', 'has_api_key'],
                        'protected_ping' => ['success', 'data', 'status'],
                        'profile' => ['success', 'data', 'status']
                    ]);

            $data = $response->json();
            expect($data['ping']['success'])->toBeTrue();
            expect($data['health']['success'])->toBeTrue();
            expect($data['features']['success'])->toBeTrue();
            expect($data['protected_ping']['success'])->toBeTrue();
            expect($data['profile']['success'])->toBeTrue();
        });

        it('handles mixed success/failure scenarios', function () {
            Http::fake([
                'http://test-langgpt.local:8000/v2/ping' => Http::response([
                    'message' => 'pong from LangGPT API v2!'
                ], 200),
                'http://test-langgpt.local:8000/v2/health' => Http::response([
                    'detail' => 'Service degraded'
                ], 503),
                'http://test-langgpt.local:8000/v2/features' => Http::response([
                    'version' => '2.0',
                    'features' => []
                ], 200),
                'http://test-langgpt.local:8000/v2/protected/ping' => Http::response([
                    'detail' => 'Invalid or inactive API key'
                ], 401),
                'http://test-langgpt.local:8000/v2/protected/profile' => Http::response([
                    'detail' => 'Invalid or inactive API key'
                ], 401)
            ]);

            $response = $this->get('/langgpt-status');

            $response->assertStatus(200);
            
            $data = $response->json();
            expect($data['ping']['success'])->toBeTrue();
            expect($data['health']['success'])->toBeFalse();
            expect($data['health']['status'])->toBe(503);
            expect($data['protected_ping']['success'])->toBeFalse();
            expect($data['protected_ping']['status'])->toBe(401);
        });

        it('works without API key (skips protected endpoints)', function () {
            Config::set('services.langgpt.key', null);
            
            Http::fake([
                'http://test-langgpt.local:8000/v2/ping' => Http::response([
                    'message' => 'pong from LangGPT API v2!'
                ], 200),
                'http://test-langgpt.local:8000/v2/health' => Http::response([
                    'status' => 'healthy'
                ], 200),
                'http://test-langgpt.local:8000/v2/features' => Http::response([
                    'version' => '2.0',
                    'features' => []
                ], 200)
            ]);

            $response = $this->get('/langgpt-status');

            $response->assertStatus(200);
            
            $data = $response->json();
            expect($data['api_info']['has_api_key'])->toBeFalse();
            expect(array_key_exists('protected_ping', $data))->toBeFalse();
            expect(array_key_exists('profile', $data))->toBeFalse();
        });
    });

    describe('API Configuration Tests', function () {
        it('adapts to different API versions', function () {
            Config::set('services.langgpt.version', 'v1');
            
            Http::fake([
                'http://test-langgpt.local:8000/v1/ping' => Http::response([
                    'message' => 'pong from LangGPT API v1!',
                    'version' => '1.0'
                ], 200)
            ]);

            $response = $this->get('/ping-langgpt');

            $response->assertStatus(200);
            
            $data = $response->json();
            expect($data['api_info']['version'])->toBe('v1');
            expect($data['ping_result']['data']['version'])->toBe('1.0');

            Http::assertSent(function ($request) {
                return str_contains($request->url(), '/v1/ping');
            });
        });

        it('handles different base URLs', function () {
            Config::set('services.langgpt.url', 'https://production-langgpt.example.com');
            
            Http::fake([
                'https://production-langgpt.example.com/v2/ping' => Http::response([
                    'message' => 'pong from production!'
                ], 200)
            ]);

            $response = $this->get('/ping-langgpt');

            $response->assertStatus(200);
            
            $data = $response->json();
            expect($data['api_info']['base_url'])->toBe('https://production-langgpt.example.com');
        });

        it('respects timeout configuration', function () {
            Config::set('services.langgpt.timeout', 5);
            
            Http::fake([
                'http://test-langgpt.local:8000/v2/ping' => function () {
                    // Simulate a timeout error
                    throw new \Illuminate\Http\Client\ConnectionException('Operation timed out after 5000 milliseconds');
                }
            ]);

            $response = $this->get('/ping-langgpt');

            $response->assertStatus(200);
            
            $data = $response->json();
            expect($data['ping_result']['success'])->toBeFalse();
            expect($data['ping_result']['error'])->toContain('timed out');
        });
    });

    describe('Authentication Headers', function () {
        it('sends API key in X-API-Key header', function () {
            Http::fake([
                'http://test-langgpt.local:8000/v2/ping' => Http::response(['message' => 'pong'], 200)
            ]);

            $this->get('/ping-langgpt');

            Http::assertSent(function ($request) {
                return $request->hasHeader('X-API-Key', 'test_api_key_123');
            });
        });

        it('does not send API key header when not configured', function () {
            Config::set('services.langgpt.key', null);
            
            Http::fake([
                'http://test-langgpt.local:8000/v2/ping' => Http::response(['message' => 'pong'], 200)
            ]);

            $this->get('/ping-langgpt');

            Http::assertSent(function ($request) {
                return !$request->hasHeader('X-API-Key');
            });
        });
    });
});