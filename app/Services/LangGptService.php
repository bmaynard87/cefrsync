<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;

class LangGptService
{
    protected string $baseUrl;
    protected string $apiVersion;
    protected ?string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.langgpt.url');
        $this->apiVersion = config('services.langgpt.version', 'v2');
        $this->apiKey = config('services.langgpt.key');
        $this->timeout = config('services.langgpt.timeout', 30);
    }

    /**
     * Make a request to the LangGPT API
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        // Validate HTTP method before making request
        $method = strtoupper($method);
        if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
            throw new \InvalidArgumentException("Unsupported HTTP method: {$method}");
        }

        try {
            $http = Http::timeout($this->timeout);

            // Add API key header if available
            if ($this->apiKey) {
                $http = $http->withHeaders(['X-API-Key' => $this->apiKey]);
            }

            $url = "{$this->baseUrl}/{$this->apiVersion}/{$endpoint}";

            $response = match ($method) {
                'GET' => $http->get($url, $data),
                'POST' => $http->post($url, $data),
                'PUT' => $http->put($url, $data),
                'DELETE' => $http->delete($url, $data),
            };

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
                'status' => $response->status(),
                'headers' => $response->headers(),
            ];
        } catch (\Exception $e) {
            Log::error('LangGPT API Error', [
                'endpoint' => $endpoint,
                'method' => $method,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null,
                'status' => 0,
            ];
        }
    }

    /**
     * Ping the LangGPT API
     */
    public function ping(): array
    {
        return $this->makeRequest('GET', 'ping');
    }

    /**
     * Get health status
     */
    public function health(): array
    {
        return $this->makeRequest('GET', 'health');
    }

    /**
     * Get API features (v2 only)
     */
    public function features(): array
    {
        if ($this->apiVersion === 'v1') {
            return [
                'success' => false,
                'error' => 'Features endpoint only available in API v2',
            ];
        }

        return $this->makeRequest('GET', 'features');
    }

    /**
     * Get API key profile (requires authentication)
     */
    public function profile(): array
    {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'error' => 'API key required for profile endpoint',
            ];
        }

        return $this->makeRequest('GET', 'protected/profile');
    }

    /**
     * Test protected ping (requires authentication)
     */
    public function protectedPing(): array
    {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'error' => 'API key required for protected endpoints',
            ];
        }

        return $this->makeRequest('GET', 'protected/ping');
    }

    /**
     * Check if API key is configured
     */
    public function hasApiKey(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Get current API version
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    /**
     * Get base URL
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}