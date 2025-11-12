<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Http;
use App\Services\LangGptService;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/ping-langgpt', function (LangGptService $langGpt) {
    $result = $langGpt->ping();

    return [
        'ping_result' => $result,
        'api_info' => [
            'version' => $langGpt->getApiVersion(),
            'base_url' => $langGpt->getBaseUrl(),
            'has_api_key' => $langGpt->hasApiKey(),
        ]
    ];
});

Route::get('/langgpt-status', function (LangGptService $langGpt) {
    $ping = $langGpt->ping();
    $health = $langGpt->health();
    $features = $langGpt->features();

    $status = [
        'ping' => $ping,
        'health' => $health,
        'features' => $features,
        'api_info' => [
            'version' => $langGpt->getApiVersion(),
            'base_url' => $langGpt->getBaseUrl(),
            'has_api_key' => $langGpt->hasApiKey(),
        ]
    ];

    // Test protected endpoints if API key is available
    if ($langGpt->hasApiKey()) {
        $status['protected_ping'] = $langGpt->protectedPing();
        $status['profile'] = $langGpt->profile();
    }

    return $status;
});
require __DIR__ . '/settings.php';
