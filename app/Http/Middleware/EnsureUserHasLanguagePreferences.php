<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasLanguagePreferences
{
    /**
     * Handle an incoming request.
     *
     * Redirects to learning profile setup if user doesn't have language preferences set.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Allow unverified users to access verification routes
        if ($user && ! $user->hasVerifiedEmail()) {
            return $next($request);
        }

        // If user is authenticated but missing language setup (native/target languages), redirect to setup
        if ($user && ($user->native_language === null || $user->target_language === null)) {
            return redirect()->route('learning-profile.show');
        }

        // If user has languages but no proficiency level and hasn't enabled auto-update, redirect to setup
        if ($user && $user->proficiency_level === null && ! $user->auto_update_proficiency) {
            return redirect()->route('learning-profile.show');
        }

        return $next($request);
    }
}
