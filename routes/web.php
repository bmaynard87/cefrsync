<?php

use App\Http\Controllers\ProfileController;
use App\Services\LangGptService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect(route('language-chat.index'));
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

Route::middleware(['auth', 'verified'])->prefix('language-chat')->name('language-chat')->group(function () {
    Route::get('/', [\App\Http\Controllers\LanguageChatController::class, 'index'])->name('.index');
    Route::post('/', [\App\Http\Controllers\LanguageChatController::class, 'create'])->name('.create');
    Route::get('/history', [\App\Http\Controllers\LanguageChatController::class, 'history'])->name('.history');
    Route::get('/{chatSession}/messages', [\App\Http\Controllers\LanguageChatController::class, 'messages'])->name('.messages');
    Route::post('/{chatSession}/message', [\App\Http\Controllers\LanguageChatController::class, 'sendMessage'])->name('.message');
    Route::post('/{chatSession}/detect-language', [\App\Http\Controllers\LanguageChatController::class, 'detectLanguage'])->name('.detect-language');
    Route::post('/{chatSession}/analyze-for-langgpt', [\App\Http\Controllers\LanguageChatController::class, 'analyzeForLangGpt'])->name('.analyze-for-langgpt');
    Route::patch('/{chatSession}/title', [\App\Http\Controllers\LanguageChatController::class, 'updateTitle'])->name('.update-title');
    Route::patch('/{chatSession}/parameters', [\App\Http\Controllers\LanguageChatController::class, 'updateParameters'])->name('.update-parameters');
    Route::delete('/{chatSession}', [\App\Http\Controllers\LanguageChatController::class, 'destroy'])->name('.destroy');
});

Route::middleware(['auth', 'verified'])->prefix('insights')->name('insights')->group(function () {
    Route::get('/', [\App\Http\Controllers\LanguageInsightController::class, 'index'])->name('.index');
    Route::post('/mark-all-read', [\App\Http\Controllers\LanguageInsightController::class, 'markAllAsRead'])->name('.mark-all-read');
    Route::patch('/{insight}/read', [\App\Http\Controllers\LanguageInsightController::class, 'markAsRead'])->name('.mark-read');
    Route::delete('/{insight}', [\App\Http\Controllers\LanguageInsightController::class, 'destroy'])->name('.destroy');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
