<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChatSession>
 */
class ChatSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $allLanguages = Language::active()->pluck('id')->toArray();
        $nativeLanguageId = fake()->randomElement($allLanguages);
        // Ensure target is different from native
        $targetLanguageId = fake()->randomElement(array_diff($allLanguages, [$nativeLanguageId]));

        return [
            'user_id' => User::factory(),
            'native_language_id' => $nativeLanguageId,
            'target_language_id' => $targetLanguageId,
            'proficiency_level' => fake()->randomElement(['A1', 'A2', 'B1', 'B2', 'C1', 'C2']),
            'title' => fake()->sentence(3),
            'last_message_at' => fake()->dateTimeBetween('-1 week', 'now'),
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
}
