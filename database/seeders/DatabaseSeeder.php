<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\LanguageInsight;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create main user with chat history
        $mainUser = User::factory()->create([
            'first_name' => 'Brandon',
            'last_name' => 'Maynard',
            'email' => 'bmmaynard87@gmail.com',
            'password' => bcrypt('MySecurePass2025!'),
            'native_language' => 'English',
            'target_language' => 'Japanese',
            'proficiency_level' => 'B2',
            'auto_update_proficiency' => true,
        ]);

        // Create a chat session with conversation
        $session = ChatSession::create([
            'user_id' => $mainUser->id,
            'native_language' => $mainUser->native_language,
            'target_language' => $mainUser->target_language,
            'proficiency_level' => $mainUser->proficiency_level,
            'title' => 'Learning Japanese - Daily Life',
            'conversation_summary' => 'Discussing daily routines, food preferences, and travel plans in Japanese.',
            'topics_discussed' => ['daily routines', 'food', 'travel', 'hobbies'],
            'last_message_at' => now(),
        ]);

        // Add sample conversation in Japanese
        $conversations = [
            ['user', 'こんにちは！今日はどうですか？'],
            ['assistant', 'こんにちは！とても元気です。あなたはどうですか？今日はいい日ですか？'],
            ['user', '元気です、ありがとうございます。日本語を練習したいです。食べ物について話しませんか？'],
            ['assistant', 'もちろんです！食べ物の話が大好きです。好きな料理は何ですか？'],
            ['user', 'ラーメンが大好きです。とても美味しいですね。'],
            ['assistant', '素晴らしい選択ですね！ラーメンは日本の伝統的な料理です。どんな種類のラーメンが好きですか？'],
            ['user', '味噌ラーメンと豚骨ラーメンが好きです。あなたのおすすめは何ですか？'],
            ['assistant', '両方とも美味しいですね！個人的には、北海道の味噌ラーメンが特に美味しいと思います。濃厚で風味豊かですよ。'],
            ['user', 'いつか日本に行ってみたいです。本場のラーメンを食べたいです。'],
            ['assistant', 'それは素晴らしい目標ですね！日本のどの都市を訪れたいですか？'],
            ['user', '東京、京都、大阪に行きたいです。'],
            ['assistant', '良い選択ですね。東京は現代的で活気があり、京都は伝統的で美しく、大阪は食べ物が有名です。'],
            ['user', '大阪の食べ物は本当に美味しいと聞きました。'],
            ['assistant', 'はい、大阪は「天下の台所」として知られています。たこ焼きやお好み焼きが特に有名です。'],
            ['user', '日本の文化にも興味があります。特に茶道と書道を学びたいです。'],
            ['assistant', '素晴らしいですね！茶道と書道は日本の伝統文化の重要な部分です。どちらも精神性と美しさを重視します。'],
            ['user', '毎日少しずつ日本語を勉強しています。'],
            ['assistant', '継続は力なりですね。毎日の練習が上達の鍵です。どのくらい勉強していますか？'],
            ['user', '毎朝30分間、漢字と文法を勉強しています。'],
            ['assistant', '素晴らしい習慣ですね！定期的な学習が最も効果的です。他に趣味はありますか？'],
        ];

        foreach ($conversations as $index => $conv) {
            ChatMessage::create([
                'chat_session_id' => $session->id,
                'sender_type' => $conv[0],
                'content' => $conv[1],
                'created_at' => now()->subMinutes(count($conversations) - $index),
            ]);
        }

        // Add some sample insights
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

        // Create additional test users with different scenarios
        $beginnerUser = User::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
            'email' => 'alice@example.com',
            'native_language' => 'English',
            'target_language' => 'French',
            'proficiency_level' => 'A1',
            'auto_update_proficiency' => false,
        ]);

        $intermediateUser = User::factory()->create([
            'first_name' => 'Bob',
            'last_name' => 'Smith',
            'email' => 'bob@example.com',
            'native_language' => 'English',
            'target_language' => 'German',
            'proficiency_level' => 'B1',
            'auto_update_proficiency' => true,
        ]);

        $advancedUser = User::factory()->create([
            'first_name' => 'Carlos',
            'last_name' => 'García',
            'email' => 'carlos@example.com',
            'native_language' => 'Spanish',
            'target_language' => 'Italian',
            'proficiency_level' => 'C1',
            'auto_update_proficiency' => true,
        ]);

        // Create chat session for beginner with simple French conversation
        $beginnerSession = ChatSession::create([
            'user_id' => $beginnerUser->id,
            'native_language' => $beginnerUser->native_language,
            'target_language' => $beginnerUser->target_language,
            'proficiency_level' => $beginnerUser->proficiency_level,
            'title' => 'First French Conversation',
            'last_message_at' => now()->subDay(),
        ]);

        $frenchConversations = [
            ['user', 'Bonjour!'],
            ['assistant', 'Bonjour! Comment allez-vous?'],
            ['user', 'Je vais bien, merci.'],
            ['assistant', 'Très bien! Comment vous appelez-vous?'],
            ['user', 'Je m\'appelle Alice.'],
        ];

        foreach ($frenchConversations as $index => $conv) {
            ChatMessage::create([
                'chat_session_id' => $beginnerSession->id,
                'sender_type' => $conv[0],
                'content' => $conv[1],
                'created_at' => now()->subDay()->addMinutes($index),
            ]);
        }

        // Create chat for intermediate user
        $germanSession = ChatSession::create([
            'user_id' => $intermediateUser->id,
            'native_language' => $intermediateUser->native_language,
            'target_language' => $intermediateUser->target_language,
            'proficiency_level' => $intermediateUser->proficiency_level,
            'title' => 'German Practice',
            'topics_discussed' => ['work', 'family', 'weather'],
            'last_message_at' => now()->subHours(5),
        ]);

        // Create test user
        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'native_language' => 'English',
            'target_language' => 'Japanese',
            'proficiency_level' => 'A2',
        ]);

        $this->command->info('✓ Created 5 users with sample data:');
        $this->command->info('  - bmmaynard87@gmail.com (password: MySecurePass2025!) - Japanese B2 with full chat + insights');
        $this->command->info('  - alice@example.com (password: password) - French A1 beginner');
        $this->command->info('  - bob@example.com (password: password) - German B1 intermediate');
        $this->command->info('  - carlos@example.com (password: password) - Italian C1 advanced');
        $this->command->info('  - test@example.com (password: password) - Japanese A2');
        $this->command->info('');
        $this->command->info('✓ Created sample chat sessions with messages');
        $this->command->info('✓ Created 2 unread insights for main user');
    }
}
