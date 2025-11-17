<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LearningProfileSetupController extends Controller
{
    /**
     * Display the learning profile setup page.
     */
    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        // If user already has languages and proficiency set up, redirect to home
        if ($user->native_language !== null && $user->target_language !== null && $user->proficiency_level !== null) {
            return redirect()->route('language-chat.index');
        }

        return Inertia::render('Auth/LearningProfileSetup', [
            'user' => [
                'first_name' => $user->first_name,
            ],
        ]);
    }

    /**
     * Handle the learning profile setup submission.
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'native_language' => 'required|string|max:255',
            'target_language' => 'required|string|max:255|different:native_language',
            'auto_update_proficiency' => 'required|boolean',
            // Only require proficiency_level if NOT auto-updating
            'proficiency_level' => 'required_if:auto_update_proficiency,false|nullable|string|in:A1,A2,B1,B2,C1,C2',
        ];

        $validated = $request->validate($rules);

        // Convert language names/keys to IDs
        $nativeLanguage = Language::where('name', $validated['native_language'])
            ->orWhere('key', $validated['native_language'])
            ->first();
        $targetLanguage = Language::where('name', $validated['target_language'])
            ->orWhere('key', $validated['target_language'])
            ->first();

        if (! $nativeLanguage || ! $targetLanguage) {
            return back()->withErrors([
                'native_language' => ! $nativeLanguage ? 'Invalid native language selected.' : null,
                'target_language' => ! $targetLanguage ? 'Invalid target language selected.' : null,
            ]);
        }

        $updateData = [
            'native_language_id' => $nativeLanguage->id,
            'target_language_id' => $targetLanguage->id,
            'auto_update_proficiency' => $validated['auto_update_proficiency'],
        ];

        // Only set proficiency_level if provided (i.e., not auto-updating)
        if (isset($validated['proficiency_level']) && $validated['proficiency_level']) {
            $updateData['proficiency_level'] = $validated['proficiency_level'];
        }

        $request->user()->update($updateData);

        return redirect()->route('language-chat.index');
    }
}
