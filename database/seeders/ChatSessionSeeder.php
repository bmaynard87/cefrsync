<?php

namespace Database\Seeders;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainUser = User::where('email', 'bmmaynard87@gmail.com')->first();
        $aliceUser = User::where('email', 'alice@example.com')->first();
        $bobUser = User::where('email', 'bob@example.com')->first();

        // Create multiple chat sessions for main user to demonstrate scrolling
        $chatTitles = [
            'Learning Japanese - Daily Life',
            'Japanese Food & Culture',
            'Travel Planning in Japanese',
            'Japanese Grammar Practice',
            'Kanji Study Session',
            'Business Japanese',
            'Japanese Anime Discussion',
            'Japanese History & Traditions',
            'Casual Japanese Conversation',
            'Japanese Music & Entertainment',
            'Japanese Weather & Seasons',
            'Shopping in Japanese',
            'Japanese Technology Terms',
            'Japanese Sports & Hobbies',
            'Japanese Family & Relationships',
            'Japanese Numbers & Counting',
            'Japanese Politeness Levels',
            'Japanese Idioms & Expressions',
            'Japanese Reading Practice',
            'Japanese Pronunciation Help',
        ];

        foreach ($chatTitles as $index => $title) {
            $session = ChatSession::create([
                'user_id' => $mainUser->id,
                'native_language_id' => $mainUser->native_language_id,
                'target_language_id' => $mainUser->target_language_id,
                'proficiency_level' => $mainUser->proficiency_level,
                'title' => $title,
                'conversation_summary' => 'Practice session focused on '.strtolower(str_replace('Japanese - ', '', $title)),
                'topics_discussed' => ['practice', 'learning', 'conversation'],
                'last_message_at' => now()->subMinutes($index * 30),
            ]);

            // Add a few messages to each session
            if ($index === 0) {
                // First session gets full conversation
                $conversations = [
                    ['user', 'こんにちは！今日はどうですか？'],
                    ['assistant', 'こんにちは！とても元気です。あなたはどうですか？'],
                    ['user', '元気です、ありがとうございます。'],
                ];
            } else {
                // Other sessions get shorter conversations
                $conversations = [
                    ['user', 'こんにちは！'],
                    ['assistant', 'こんにちは！何か手伝いましょうか？'],
                ];
            }

            foreach ($conversations as $msgIndex => $conv) {
                ChatMessage::create([
                    'chat_session_id' => $session->id,
                    'sender_type' => $conv[0],
                    'content' => $conv[1],
                    'created_at' => now()->subMinutes($index * 30 + $msgIndex),
                ]);
            }
        }

        // Main user's primary Japanese conversation (kept for compatibility)
        $japaneseSession = ChatSession::where('user_id', $mainUser->id)
            ->where('title', 'Learning Japanese - Daily Life')
            ->first();

        $japaneseConversations = [
            ['user', '毎日少しずつ日本語を勉強しています。'],
            ['assistant', '継続は力なりですね。毎日の練習が上達の鍵です。どのくらい勉強していますか？'],
            ['user', '毎朝30分間、漢字と文法を勉強しています。'],
            ['assistant', '素晴らしい習慣ですね！定期的な学習が最も効果的です。他に趣味はありますか？'],
        ];

        // Add additional messages to the first session
        foreach ($japaneseConversations as $index => $conv) {
            ChatMessage::create([
                'chat_session_id' => $japaneseSession->id,
                'sender_type' => $conv[0],
                'content' => $conv[1],
                'created_at' => now()->subMinutes(count($japaneseConversations) - $index + 10),
            ]);
        }

        // Alice's French conversation
        $frenchSession = ChatSession::create([
            'user_id' => $aliceUser->id,
            'native_language_id' => $aliceUser->native_language_id,
            'target_language_id' => $aliceUser->target_language_id,
            'proficiency_level' => $aliceUser->proficiency_level,
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
                'chat_session_id' => $frenchSession->id,
                'sender_type' => $conv[0],
                'content' => $conv[1],
                'created_at' => now()->subDay()->addMinutes($index),
            ]);
        }

        // Bob's German conversation
        $germanSession = ChatSession::create([
            'user_id' => $bobUser->id,
            'native_language_id' => $bobUser->native_language_id,
            'target_language_id' => $bobUser->target_language_id,
            'proficiency_level' => $bobUser->proficiency_level,
            'title' => 'German Practice',
            'topics_discussed' => ['work', 'family', 'weather'],
            'last_message_at' => now()->subHours(5),
        ]);

        $this->command->info('✓ Created '.count($chatTitles).' chat sessions for main user with messages');
        $this->command->info('✓ Created French conversation (5 messages)');
        $this->command->info('✓ Created German session (empty)');
    }
}
