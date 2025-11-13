<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ChatSession;
use App\Models\ChatMessage;
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

        // Main user's Japanese conversation
        $japaneseSession = ChatSession::create([
            'user_id' => $mainUser->id,
            'native_language' => $mainUser->native_language,
            'target_language' => $mainUser->target_language,
            'proficiency_level' => $mainUser->proficiency_level,
            'title' => 'Learning Japanese - Daily Life',
            'conversation_summary' => 'Discussing daily routines, food preferences, and travel plans in Japanese.',
            'topics_discussed' => ['daily routines', 'food', 'travel', 'hobbies'],
            'last_message_at' => now(),
        ]);

        $japaneseConversations = [
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

        foreach ($japaneseConversations as $index => $conv) {
            ChatMessage::create([
                'chat_session_id' => $japaneseSession->id,
                'sender_type' => $conv[0],
                'content' => $conv[1],
                'created_at' => now()->subMinutes(count($japaneseConversations) - $index),
            ]);
        }

        // Alice's French conversation
        $frenchSession = ChatSession::create([
            'user_id' => $aliceUser->id,
            'native_language' => $aliceUser->native_language,
            'target_language' => $aliceUser->target_language,
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
            'native_language' => $bobUser->native_language,
            'target_language' => $bobUser->target_language,
            'proficiency_level' => $bobUser->proficiency_level,
            'title' => 'German Practice',
            'topics_discussed' => ['work', 'family', 'weather'],
            'last_message_at' => now()->subHours(5),
        ]);

        $this->command->info('✓ Created 3 chat sessions with messages:');
        $this->command->info('  - Japanese conversation (19 messages)');
        $this->command->info('  - French conversation (5 messages)');
        $this->command->info('  - German session (empty)');
    }
}
