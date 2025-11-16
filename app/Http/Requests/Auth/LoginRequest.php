<?php

namespace App\Http\Requests\Auth;

use App\Rules\ReCaptcha;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        \Log::info('[LOGIN DEBUG] Validation rules being applied', [
            'email' => $this->input('email'),
            'has_recaptcha_token' => ! empty($this->input('recaptcha_token')),
            'recaptcha_token_length' => strlen((string) $this->input('recaptcha_token')),
            'recaptcha_configured' => ! empty(config('services.google.recaptcha.site_key')),
        ]);

        $rules = [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];

        // Only validate reCAPTCHA if it's configured
        if (! empty(config('services.google.recaptcha.site_key'))) {
            $rules['recaptcha_token'] = ['required', 'string', new ReCaptcha];
        }

        return $rules;
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        \Log::info('[LOGIN DEBUG] Attempting authentication', [
            'email' => $this->input('email'),
            'has_password' => ! empty($this->input('password')),
            'remember' => $this->boolean('remember'),
        ]);

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            \Log::warning('[LOGIN DEBUG] Authentication failed');
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        \Log::info('[LOGIN DEBUG] Authentication successful');
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
