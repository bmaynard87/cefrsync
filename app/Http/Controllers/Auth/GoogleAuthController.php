<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class GoogleAuthController extends Controller
{
    /**
     * Handle the Google OAuth callback.
     */
    public function callback(Request $request): RedirectResponse
    {
        $request->validate([
            'credential' => 'required|string',
        ]);

        try {
            // Verify the Google JWT token
            $tokenInfo = $this->verifyGoogleToken($request->credential);

            if (! $tokenInfo || ! ($tokenInfo['email_verified'] ?? false)) {
                throw ValidationException::withMessages([
                    'credential' => 'Unable to verify your Google account. Please try again.',
                ]);
            }

            // Find or create user
            $user = $this->findOrCreateUser($tokenInfo);

            // Refresh user to ensure email_verified_at is loaded
            $user->refresh();

            // Log the user in
            Auth::login($user, true);

            // Regenerate session
            $request->session()->regenerate();

            // Redirect based on user setup status
            if ($user->proficiency_level === null) {
                return redirect()->route('proficiency-opt-in.show');
            }

            return redirect()->intended('/');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Google authentication failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw ValidationException::withMessages([
                'credential' => 'Authentication failed. Please try again.',
            ]);
        }
    }

    /**
     * Verify the Google JWT token with Google's API.
     */
    protected function verifyGoogleToken(string $token): ?array
    {
        try {
            $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $token,
            ]);

            if (! $response->successful()) {
                return null;
            }

            $tokenInfo = $response->json();

            // Verify the token is for our app
            $clientId = config('services.google.client_id');
            if (($tokenInfo['aud'] ?? null) !== $clientId) {
                Log::warning('Google token audience mismatch', [
                    'expected' => $clientId,
                    'actual' => $tokenInfo['aud'] ?? null,
                ]);

                return null;
            }

            // Verify issuer
            if (! in_array($tokenInfo['iss'] ?? null, ['accounts.google.com', 'https://accounts.google.com'])) {
                Log::warning('Google token issuer mismatch', [
                    'issuer' => $tokenInfo['iss'] ?? null,
                ]);

                return null;
            }

            return $tokenInfo;
        } catch (\Exception $e) {
            Log::error('Failed to verify Google token', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Find or create a user based on Google token info.
     */
    protected function findOrCreateUser(array $tokenInfo): User
    {
        $googleId = $tokenInfo['sub'];
        $email = $tokenInfo['email'];

        // Try to find by Google ID first
        $user = User::where('google_id', $googleId)->first();

        if ($user) {
            return $user;
        }

        // Try to find by email and link Google ID
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update([
                'google_id' => $googleId,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);

            return $user;
        }

        // Create new user
        return User::create([
            'google_id' => $googleId,
            'email' => $email,
            'first_name' => $tokenInfo['given_name'] ?? explode(' ', $tokenInfo['name'] ?? 'User')[0],
            'last_name' => $tokenInfo['family_name'] ?? explode(' ', $tokenInfo['name'] ?? ' Name')[1] ?? 'Name',
            'email_verified_at' => now(),
            'password' => null, // Google users don't have passwords
        ]);
    }
}
