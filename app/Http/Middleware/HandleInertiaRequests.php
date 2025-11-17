<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'googleClientId' => config('services.google.client_id'),
            ],
            'csrf_token' => csrf_token(),
            'recaptcha' => [
                'siteKey' => config('services.recaptcha.site_key'),
            ],
            'languages' => Language::active()
                ->orderBy('name')
                ->get(['id', 'key', 'name', 'native_name'])
                ->map(fn ($lang) => [
                    'value' => $lang->key,
                    'label' => $lang->name,
                    'native_name' => $lang->native_name,
                ]),
            'flash' => [
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
