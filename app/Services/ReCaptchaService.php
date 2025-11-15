<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReCaptchaService
{
    /**
     * Verify a reCAPTCHA token with Google's API.
     *
     * @param  string  $token  The reCAPTCHA token to verify
     * @param  float  $minScore  Minimum score required (0.0 to 1.0)
     */
    public function verify(string $token, float $minScore = 0.5): bool
    {
        // Validate token is not empty
        if (empty($token)) {
            return false;
        }

        // Validate secret key is configured
        $secretKey = config('services.recaptcha.secret_key');
        if (empty($secretKey)) {
            Log::warning('reCAPTCHA secret key is not configured');

            return false;
        }

        try {
            $response = Http::asForm()->post(config('services.recaptcha.verify_url'), [
                'secret' => $secretKey,
                'response' => $token,
            ]);

            if (! $response->successful()) {
                Log::error('reCAPTCHA verification request failed', [
                    'status' => $response->status(),
                ]);

                return false;
            }

            $data = $response->json();

            // Check if verification was successful
            if (! ($data['success'] ?? false)) {
                Log::warning('reCAPTCHA verification failed', [
                    'errors' => $data['error-codes'] ?? [],
                ]);

                return false;
            }

            // Check score if provided in response
            $score = $data['score'] ?? 1.0;
            if ($score < $minScore) {
                Log::warning('reCAPTCHA score too low', [
                    'score' => $score,
                    'min_score' => $minScore,
                ]);

                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification exception', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
