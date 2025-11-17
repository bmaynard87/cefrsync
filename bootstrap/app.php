<?php

use App\Http\Middleware\EnsureUserHasLanguagePreferences;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetSecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetSecurityHeaders::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'language.preferences' => EnsureUserHasLanguagePreferences::class,
        ]);

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function ($response, $exception, $request) {
            // For Inertia requests that encounter server errors, redirect back with error message
            if ($request->inertia() && $response->getStatusCode() >= 500) {
                return back()->with([
                    'error' => 'An unexpected error occurred. Please try again.',
                ]);
            }

            return $response;
        });
    })->create();
