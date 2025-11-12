<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'user_id' => User::factory(),
            'native_language' => fake()->randomElement(['English', 'Spanish', 'French', 'German', 'Italian']),
            'target_language' => fake()->randomElement(['English', 'Spanish', 'French', 'German', 'Italian']),
            'proficiency_level' => fake()->randomElement(['A1', 'A2', 'B1', 'B2', 'C1', 'C2']),
            'title' => fake()->sentence(3),
            'last_message_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
