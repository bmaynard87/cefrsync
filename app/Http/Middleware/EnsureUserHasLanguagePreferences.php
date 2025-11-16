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
     * Redirects to proficiency opt-in if user doesn't have language preferences set.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is authenticated but missing language preferences, redirect to opt-in
        if ($user && ($user->native_language === null || $user->target_language === null)) {
            return redirect()->route('proficiency-opt-in.show');
        }

        // If user has languages but no proficiency level and hasn't enabled auto-update, redirect to opt-in
        if ($user && $user->proficiency_level === null && ! $user->auto_update_proficiency) {
            return redirect()->route('proficiency-opt-in.show');
        }

        return $next($request);
    }
}
