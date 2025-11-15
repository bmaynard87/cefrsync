<?php

namespace App\Http\Controllers;

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
        ]);
    }

    /**
     * Handle the proficiency auto-update opt-in submission.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'auto_update_proficiency' => 'required|boolean',
        ]);

        $user = $request->user();

        // Only update if user doesn't have a proficiency level set
        if ($user->proficiency_level === null) {
            $user->update([
                'auto_update_proficiency' => $validated['auto_update_proficiency'],
            ]);
        }

        return redirect()->route('language-chat.index');
    }
}
