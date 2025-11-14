<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

test('regenerate langgpt key command successfully updates env file', function () {
    // Mock the HTTP request to LangGPT
    Http::fake([
        '*/api/keys' => Http::response([
            'key' => 'lgpt_test_key_123456789',
            'name' => 'CefrSync',
            'created_at' => now()->toIso8601String(),
            'is_active' => true,
        ], 200),
    ]);

    // Create a temporary .env file for testing
    $envPath = base_path('.env');
    $originalContent = File::get($envPath);
    
    // Ensure we have a LANGGPT_API_KEY line to replace
    $testContent = str_replace(
        'LANGGPT_API_KEY=',
        'LANGGPT_API_KEY=lgpt_old_key_to_replace',
        $originalContent
    );
    File::put($envPath, $testContent);

    // Run the command
    $this->artisan('langgpt:regenerate-key')
        ->expectsOutput('Regenerating LangGPT API key...')
        ->expectsOutput('✓ API key regenerated successfully!')
        ->assertExitCode(0);

    // Verify the .env file was updated
    $updatedContent = File::get($envPath);
    expect($updatedContent)->toContain('LANGGPT_API_KEY=lgpt_test_key_123456789');

    // Restore original content
    File::put($envPath, $originalContent);

    // Verify HTTP call was made
    Http::assertSent(function ($request) {
        return $request->url() === config('services.langgpt.url') . '/api/keys' &&
               $request['name'] === 'CefrSync';
    });
});

test('regenerate langgpt key command fails when langgpt url is not configured', function () {
    // Temporarily unset the config
    config(['services.langgpt.url' => '']);

    $this->artisan('langgpt:regenerate-key')
        ->expectsOutput('LANGGPT_URL is not configured in .env file')
        ->assertExitCode(1);
});

test('regenerate langgpt key command fails when langgpt api returns error', function () {
    // Mock failed HTTP request
    Http::fake([
        '*/api/keys' => Http::response(['error' => 'Service unavailable'], 500),
    ]);

    $this->artisan('langgpt:regenerate-key')
        ->expectsOutput('Regenerating LangGPT API key...')
        ->assertExitCode(1);
});

test('regenerate langgpt key command fails when api key is not in response', function () {
    // Mock response without key field
    Http::fake([
        '*/api/keys' => Http::response([
            'name' => 'CefrSync',
            'created_at' => now()->toIso8601String(),
        ], 200),
    ]);

    $this->artisan('langgpt:regenerate-key')
        ->expectsOutput('No API key returned from LangGPT')
        ->assertExitCode(1);
});

test('regenerate langgpt key command fails when env file does not have langgpt_api_key', function () {
    // Mock successful HTTP response
    Http::fake([
        '*/api/keys' => Http::response([
            'key' => 'lgpt_test_key_123456789',
            'name' => 'CefrSync',
            'created_at' => now()->toIso8601String(),
            'is_active' => true,
        ], 200),
    ]);

    $envPath = base_path('.env');
    $originalContent = File::get($envPath);
    
    // Remove LANGGPT_API_KEY line
    $testContent = preg_replace('/LANGGPT_API_KEY=.*\n/', '', $originalContent);
    File::put($envPath, $testContent);

    $this->artisan('langgpt:regenerate-key')
        ->assertExitCode(1);

    // Restore original content
    File::put($envPath, $originalContent);
});
