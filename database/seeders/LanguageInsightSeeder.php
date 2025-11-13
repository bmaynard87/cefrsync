<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ChatSession;
use App\Models\LanguageInsight;
use Illuminate\Database\Seeder;

class LanguageInsightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainUser = User::where('email', 'bmmaynard87@gmail.com')->first();
        $session = ChatSession::where('user_id', $mainUser->id)->first();

        // Grammar patterns insight
        LanguageInsight::create([
            'user_id' => $mainUser->id,
            'chat_session_id' => $session->id,
            'insight_type' => 'grammar_pattern',
            'title' => 'Grammar Patterns Detected',
            'message' => 'Your use of particles (は、が、を、に) is mostly correct. You\'re effectively using polite forms (です/ます) and showing good command of basic grammar structures.',
            'data' => [
                'patterns' => [
                    [
                        'pattern' => 'Correct particle usage',
                        'frequency' => 'common',
                        'examples' => ['ラーメンが大好きです', '日本語を練習したい', '東京に行きたい'],
                        'severity' => 'minor'
                    ],
                    [
                        'pattern' => 'Proper use of polite forms',
                        'frequency' => 'common',
                        'examples' => ['元気です', '好きです', '行きたいです'],
                        'severity' => 'minor'
                    ]
                ]
            ],
            'is_read' => false,
            'created_at' => now()->subHours(2),
        ]);

        // Vocabulary assessment insight
        LanguageInsight::create([
            'user_id' => $mainUser->id,
            'chat_session_id' => $session->id,
            'insight_type' => 'vocabulary_strength',
            'title' => 'Vocabulary Assessment',
            'message' => 'Your vocabulary demonstrates strong intermediate-level Japanese with good use of compound words and topic-specific terms. You\'re incorporating cultural vocabulary appropriately.',
            'data' => [
                'insights' => [
                    'complexity_level' => 'intermediate',
                    'variety_score' => 0.78,
                    'advanced_words_used' => ['伝統的', '風味豊か', '天下の台所', '精神性', '継続は力なり'],
                    'recommendations' => [
                        'Practice using more keigo (honorific language) forms',
                        'Expand vocabulary around abstract concepts and emotions',
                        'Work on casual speech patterns for informal situations'
                    ]
                ]
            ],
            'is_read' => false,
            'created_at' => now()->subHours(2),
        ]);

        $this->command->info('✓ Created 2 sample insights for main user');
    }
}
