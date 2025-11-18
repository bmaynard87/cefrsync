<?php

namespace Database\Factories;

use App\Models\ChatSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChatMessage>
 */
class ChatMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chat_session_id' => ChatSession::factory(),
            'sender_type' => $this->faker->randomElement(['user', 'assistant']),
            'content' => $this->faker->paragraph(),
        ];
    }

    /**
     * Indicate that the message is from the user.
     */
    public function fromUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_type' => 'user',
        ]);
    }

    /**
     * Indicate that the message is from the assistant.
     */
    public function fromAssistant(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_type' => 'assistant',
        ]);
    }
}
