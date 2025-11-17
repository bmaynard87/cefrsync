<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProficiencyOptInController extends Controller
{
    /**
     * Display the proficiency auto-update opt-in page.
     */
    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        // If user already has a proficiency level set, redirect to home
        if ($user->proficiency_level !== null) {
            return redirect()->route('language-chat.index');
        }

        return Inertia::render('Auth/ProficiencyOptIn', [
            'user' => [
                'first_name' => $user->first_name,
                'native_language' => $user->native_language,
                'target_language' => $user->target_language,
            ],
            'needsLanguageSetup' => $user->native_language === null || $user->target_language === null,
        ]);
    }

    /**
     * Handle the proficiency auto-update opt-in submission.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $needsLanguageSetup = $user->native_language === null || $user->target_language === null;

        // Validation rules depend on whether user needs language setup
        $rules = [
            'auto_update_proficiency' => 'required|boolean',
        ];

        if ($needsLanguageSetup) {
            $rules['native_language'] = 'required|string|max:255';
            $rules['target_language'] = 'required|string|max:255|different:native_language';
            // Only require proficiency_level if NOT auto-updating
            $rules['proficiency_level'] = 'required_if:auto_update_proficiency,false|nullable|string|in:A1,A2,B1,B2,C1,C2';
        }

        $validated = $request->validate($rules);

        // Only update if user doesn't have a proficiency level set
        if ($user->proficiency_level === null) {
            $updateData = [
                'auto_update_proficiency' => $validated['auto_update_proficiency'],
            ];

            // Add language data if setting up for the first time
            if ($needsLanguageSetup) {
                // Convert language names to IDs
                $nativeLanguage = Language::where('name', $validated['native_language'])->first();
                $targetLanguage = Language::where('name', $validated['target_language'])->first();

                if ($nativeLanguage) {
                    $updateData['native_language_id'] = $nativeLanguage->id;
                }
                if ($targetLanguage) {
                    $updateData['target_language_id'] = $targetLanguage->id;
                }

                // Only set proficiency_level if provided (i.e., not auto-updating)
                if (isset($validated['proficiency_level']) && $validated['proficiency_level']) {
                    $updateData['proficiency_level'] = $validated['proficiency_level'];
                }
            }

            $user->update($updateData);
        }

        return redirect()->route('language-chat.index');
    }
}
