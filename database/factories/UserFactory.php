<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get random languages for native and target
        $allLanguages = Language::active()->pluck('id')->toArray();
        $nativeLanguageId = $this->faker->randomElement($allLanguages);
        // Ensure target is different from native
        $targetLanguageId = $this->faker->randomElement(array_diff($allLanguages, [$nativeLanguageId]));

        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= 'password',
            'remember_token' => Str::random(10),
            'native_language_id' => $nativeLanguageId,
            'target_language_id' => $targetLanguageId,
            'proficiency_level' => $this->faker->randomElement(['A1', 'A2', 'B1', 'B2', 'C1', 'C2']),
        ];
    }

    /**
     * Override the make method to convert language names to IDs
     */
    public function make($attributes = [], ?Model $parent = null)
    {
        $attributes = $this->convertLanguageAttributes($attributes);

        return parent::make($attributes, $parent);
    }

    /**
     * Override the create method to convert language names to IDs
     */
    public function create($attributes = [], ?Model $parent = null)
    {
        $attributes = $this->convertLanguageAttributes($attributes);

        return parent::create($attributes, $parent);
    }

    /**
     * Convert language name/key attributes to IDs
     */
    protected function convertLanguageAttributes(array $attributes): array
    {
        if (isset($attributes['native_language']) && $attributes['native_language'] !== null) {
            $lang = Language::where('name', $attributes['native_language'])
                ->orWhere('key', $attributes['native_language'])
                ->first();
            if ($lang) {
                $attributes['native_language_id'] = $lang->id;
            }
        }
        // Always remove the old attribute to prevent SQL errors
        unset($attributes['native_language']);

        if (isset($attributes['target_language']) && $attributes['target_language'] !== null) {
            $lang = Language::where('name', $attributes['target_language'])
                ->orWhere('key', $attributes['target_language'])
                ->first();
            if ($lang) {
                $attributes['target_language_id'] = $lang->id;
            }
        }
        // Always remove the old attribute to prevent SQL errors
        unset($attributes['target_language']);

        return $attributes;
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model hasn't completed language setup.
     */
    public function withoutLanguageSetup(): static
    {
        return $this->state(fn (array $attributes) => [
            'native_language_id' => null,
            'target_language_id' => null,
            'proficiency_level' => null,
        ]);
    }
}
