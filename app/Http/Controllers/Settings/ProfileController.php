<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\Language;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
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

        return to_route('settings.profile.edit');
    }

    /**
     * Delete the user's profile.
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

        return redirect('/');
    }
}
