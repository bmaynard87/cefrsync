<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Language;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Convert language names/keys to IDs if provided
        if (isset($validated['native_language'])) {
            $nativeLanguage = Language::where('name', $validated['native_language'])
                ->orWhere('key', $validated['native_language'])
                ->first();
            if ($nativeLanguage) {
                $validated['native_language_id'] = $nativeLanguage->id;
            }
            unset($validated['native_language']);
        }

        if (isset($validated['target_language'])) {
            $targetLanguage = Language::where('name', $validated['target_language'])
                ->orWhere('key', $validated['target_language'])
                ->first();
            if ($targetLanguage) {
                $validated['target_language_id'] = $targetLanguage->id;
            }
            unset($validated['target_language']);
        }

        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
