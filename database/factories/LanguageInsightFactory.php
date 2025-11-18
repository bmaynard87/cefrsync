<?php

namespace Database\Factories;

use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LanguageInsight>
 */
class LanguageInsightFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['grammar_pattern', 'vocabulary_strength', 'proficiency_suggestion'];
        $type = $this->faker->randomElement($types);

        return [
            'user_id' => User::factory(),
            'chat_session_id' => ChatSession::factory(),
            'insight_type' => $type,
            'title' => $this->getTitleForType($type),
            'message' => $this->faker->sentence(12),
            'data' => $this->getDataForType($type),
            'is_read' => $this->faker->boolean(30), // 30% chance of being read
        ];
    }

    /**
     * Indicate that the insight is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => false,
        ]);
    }

    /**
     * Indicate that the insight is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
        ]);
    }

    /**
     * Create a grammar pattern insight.
     */
    public function grammarPattern(): static
    {
        return $this->state(fn (array $attributes) => [
            'insight_type' => 'grammar_pattern',
            'title' => 'Grammar Patterns Detected',
            'data' => [
                'patterns' => [
                    [
                        'pattern' => 'Subject-verb agreement',
                        'frequency' => 'common',
                        'examples' => ['He go to school', 'She have a car'],
                        'severity' => 'moderate',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Create a vocabulary strength insight.
     */
    public function vocabularyStrength(): static
    {
        return $this->state(fn (array $attributes) => [
            'insight_type' => 'vocabulary_strength',
            'title' => 'Vocabulary Assessment',
            'data' => [
                'insights' => [
                    'complexity_level' => 'intermediate',
                    'variety_score' => 0.75,
                    'advanced_words_used' => ['sophisticated', 'elaborate', 'comprehensive'],
                    'recommendations' => ['Expand academic vocabulary', 'Practice idioms'],
                ],
            ],
        ]);
    }

    /**
     * Create a proficiency suggestion insight.
     */
    public function proficiencySuggestion(): static
    {
        return $this->state(fn (array $attributes) => [
            'insight_type' => 'proficiency_suggestion',
            'title' => 'Proficiency Level Update',
            'data' => [
                'current_level' => 'B1',
                'suggested_level' => 'B2',
                'reasoning' => 'Consistent use of complex structures and expanded vocabulary',
            ],
        ]);
    }

    /**
     * Get a title based on insight type.
     */
    private function getTitleForType(string $type): string
    {
        return match ($type) {
            'grammar_pattern' => 'Grammar Patterns Detected',
            'vocabulary_strength' => 'Vocabulary Assessment',
            'proficiency_suggestion' => 'Proficiency Level Update',
            default => 'Language Insight',
        };
    }

    /**
     * Get sample data based on insight type.
     */
    private function getDataForType(string $type): array
    {
        return match ($type) {
            'grammar_pattern' => [
                'patterns' => [
                    [
                        'pattern' => $this->faker->sentence(3),
                        'frequency' => $this->faker->randomElement(['rare', 'occasional', 'common']),
                        'examples' => [$this->faker->sentence(5), $this->faker->sentence(6)],
                        'severity' => $this->faker->randomElement(['minor', 'moderate', 'significant']),
                    ],
                ],
            ],
            'vocabulary_strength' => [
                'insights' => [
                    'complexity_level' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced']),
                    'variety_score' => $this->faker->randomFloat(2, 0, 1),
                    'advanced_words_used' => [$this->faker->word(), $this->faker->word(), $this->faker->word()],
                    'recommendations' => [$this->faker->sentence(), $this->faker->sentence()],
                ],
            ],
            'proficiency_suggestion' => [
                'current_level' => $this->faker->randomElement(['A1', 'A2', 'B1', 'B2', 'C1']),
                'suggested_level' => $this->faker->randomElement(['A2', 'B1', 'B2', 'C1', 'C2']),
                'reasoning' => $this->faker->sentence(),
            ],
            default => [],
        };
    }
}
