<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\ReCaptcha;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'native_language' => 'required|string|max:255',
            'target_language' => 'required|string|max:255|different:native_language',
            'proficiency_level' => 'nullable|string|in:A1,A2,B1,B2,C1,C2',
        ];

        // Only require reCAPTCHA token if it's configured
        if (config('services.recaptcha.secret_key')) {
            $rules['recaptcha_token'] = ['required', 'string', new ReCaptcha];
        }

        $request->validate($rules);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'native_language' => $request->native_language,
            'target_language' => $request->target_language,
            'proficiency_level' => $request->proficiency_level ?: null,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // If user didn't select a proficiency level, redirect to opt-in page
        if ($user->proficiency_level === null) {
            return redirect()->route('proficiency-opt-in.show');
        }

        return redirect('/');
    }
}
