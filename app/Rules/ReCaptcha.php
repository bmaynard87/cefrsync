<?php

namespace App\Rules;

use App\Services\ReCaptchaService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ReCaptcha implements ValidationRule
{
    /**
     * Create a new rule instance.
     */
    public function __construct(
        private float $minScore = 0.5
    ) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $fail('The reCAPTCHA verification failed. Please try again.');
            return;
        }

        try {
            $service = app(ReCaptchaService::class);
            $isValid = $service->verify($value, $this->minScore);

            if (!$isValid) {
                $fail('The reCAPTCHA verification failed. Please try again.');
            }
        } catch (\Exception $e) {
            $fail('The reCAPTCHA verification failed. Please try again.');
        }
    }
}
