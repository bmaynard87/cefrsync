<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\User;

test('languages table exists with correct structure', function () {
    expect(Language::count())->toBeGreaterThan(0);
});

test('can retrieve a language by key', function () {
    $language = Language::where('key', 'en')->first();

    expect($language)->not->toBeNull()
        ->and($language->key)->toBe('en')
        ->and($language->name)->toBe('English')
        ->and($language->native_name)->toBe('English');
});

test('language has users as native language relationship', function () {
    $language = Language::where('key', 'en')->first();

    $user = User::factory()->create([
        'native_language_id' => $language->id,
    ]);

    expect($language->nativeLanguageUsers)->toHaveCount(1)
        ->and($language->nativeLanguageUsers->first()->id)->toBe($user->id);
});

test('language has users as target language relationship', function () {
    $language = Language::where('key', 'es')->first();

    $user = User::factory()->create([
        'target_language_id' => $language->id,
    ]);

    expect($language->targetLanguageUsers)->toHaveCount(1)
        ->and($language->targetLanguageUsers->first()->id)->toBe($user->id);
});

test('can get all active languages', function () {
    $languages = Language::where('is_active', true)->get();

    expect($languages)->toHaveCount(20); // 20 languages seeded
});

test('language key is unique', function () {
    $language = Language::where('key', 'en')->first();

    expect($language)->not->toBeNull();

    // Attempting to create duplicate should fail
    expect(fn () => Language::create([
        'key' => 'en',
        'name' => 'English',
        'native_name' => 'English',
    ]))->toThrow(\Exception::class);
});

test('inactive languages can be excluded from queries', function () {
    $inactiveCount = Language::where('is_active', false)->count();
    $activeCount = Language::where('is_active', true)->count();

    expect($inactiveCount)->toBe(0) // Initially all active
        ->and($activeCount)->toBeGreaterThan(0);
});
