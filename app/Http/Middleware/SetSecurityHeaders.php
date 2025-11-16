<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only set Cross-Origin-Opener-Policy in production
        // In development, omit it to allow Vite HMR to work properly
        if (! app()->environment('local')) {
            $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        }

        return $response;
    }
}
