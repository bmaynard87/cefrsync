<?php

use App\Http\Controllers\ProfileController;
use App\Services\LangGptService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
