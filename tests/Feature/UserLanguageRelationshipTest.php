<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\User;

test('user can have native language relationship', function () {
    $language = Language::where('key', 'es')->first();

    $user = User::factory()->create([
        'native_language_id' => $language->id,
    ]);

    expect($user->nativeLanguage)->not->toBeNull()
        ->and($user->nativeLanguage->key)->toBe('es')
        ->and($user->nativeLanguage->name)->toBe('Spanish');
});

test('user can have target language relationship', function () {
    $language = Language::where('key', 'en')->first();

    $user = User::factory()->create([
        'target_language_id' => $language->id,
    ]);

    expect($user->targetLanguage)->not->toBeNull()
        ->and($user->targetLanguage->key)->toBe('en')
        ->and($user->targetLanguage->name)->toBe('English');
});

test('user native_language accessor returns language name', function () {
    $language = Language::where('key', 'es')->first();

    $user = User::factory()->create([
        'native_language' => null, // Clear factory default
        'target_language' => null, // Clear factory default
        'native_language_id' => $language->id,
    ]);

    // For backward compatibility, should still work with string accessor
    expect($user->native_language)->toBe('Spanish');
});

test('user target_language accessor returns language name', function () {
    $language = Language::where('key', 'ja')->first(); // Use Japanese

    $user = User::factory()->create([
        'native_language' => null, // Clear factory default
        'target_language' => null, // Clear factory default
        'target_language_id' => $language->id,
    ]);

    expect($user->target_language)->toBe('Japanese');
});

test('user can be created with language keys instead of IDs', function () {
    $user = User::factory()->create([
        'native_language_key' => 'es',
        'target_language_key' => 'en',
    ]);

    expect($user->nativeLanguage->key)->toBe('es')
        ->and($user->targetLanguage->key)->toBe('en');
});
