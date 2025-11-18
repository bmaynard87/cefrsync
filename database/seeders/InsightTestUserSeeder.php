<?php

namespace Database\Seeders;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Database\Seeder;

class InsightTestUserSeeder extends Seeder
{
    /**
     * Seed a test user with 9 user messages (18 total with assistant responses)
     * to test insight generation trigger on 10th user message.
     */
    public function run(): void
    {
        // Create test user
        $user = User::factory()->create([
            'first_name' => 'Insight',
            'last_name' => 'Tester',
            'email' => 'insight.test@example.com',
            'password' => bcrypt('InsightTest2024!SecurePass'),
            'native_language' => 'English',
            'target_language' => 'Spanish',
            'proficiency_level' => null, // No proficiency set - will be assigned by LangGPT
            'auto_update_proficiency' => true,
        ]);

        // Create a chat session
        $session = ChatSession::factory()->create([
            'user_id' => $user->id,
            'title' => 'Spanish Practice - Insight Test',
            'native_language' => $user->native_language,
            'target_language' => $user->target_language,
            'proficiency_level' => $user->proficiency_level,
        ]);

        // Create 9 user messages with corresponding assistant responses
        $conversations = [
            [
                'user' => '¡Hola! ¿Cómo estás?',
                'assistant' => '¡Hola! Estoy bien, gracias. ¿Y tú? ¿Cómo te va?',
            ],
            [
                'user' => 'Estoy muy bien. Me gusta practicar español.',
                'assistant' => '¡Qué bueno! Es importante practicar todos los días. ¿Cuánto tiempo llevas estudiando español?',
            ],
            [
                'user' => 'Llevo estudiando aproximadamente seis meses.',
                'assistant' => '¡Excelente! Seis meses es un buen comienzo. ¿Qué te parece más difícil del español?',
            ],
            [
                'user' => 'Los verbos son difíciles, especialmente el subjuntivo.',
                'assistant' => 'Sí, el subjuntivo puede ser complicado. Pero con práctica se vuelve más fácil. ¿Quieres practicar algunos ejemplos?',
            ],
            [
                'user' => 'Sí, por favor. Necesito más práctica.',
                'assistant' => 'Perfecto. Vamos a empezar con frases simples. Por ejemplo: "Espero que tengas un buen día." ¿Puedes hacer una frase similar?',
            ],
            [
                'user' => 'Espero que tú también tengas un buen día.',
                'assistant' => '¡Muy bien! Esa frase está perfecta. ¿Qué otras cosas te interesan aprender en español?',
            ],
            [
                'user' => 'Me interesa la cultura española y la comida.',
                'assistant' => '¡Fantástico! La cultura española es muy rica y la comida es deliciosa. ¿Has probado alguna comida española?',
            ],
            [
                'user' => 'Sí, he probado paella y tapas. Son muy deliciosas.',
                'assistant' => '¡Qué rico! La paella es un plato tradicional de Valencia. ¿Te gustaría aprender a cocinar comida española?',
            ],
            [
                'user' => 'Me encantaría aprender. ¿Tienes algunas recetas?',
                'assistant' => 'Sí, hay muchas recetas fáciles para empezar. El gazpacho es muy simple y perfecto para el verano. ¿Quieres que hablemos de más platos?',
            ],
        ];

        foreach ($conversations as $conversation) {
            // Create user message
            ChatMessage::factory()->create([
                'chat_session_id' => $session->id,
                'sender_type' => 'user',
                'content' => $conversation['user'],
            ]);

            // Create assistant response
            ChatMessage::factory()->create([
                'chat_session_id' => $session->id,
                'sender_type' => 'assistant',
                'content' => $conversation['assistant'],
            ]);
        }

        $this->command->info("✅ Created test user: {$user->email}");
        $this->command->info('✅ Created chat session with 9 user messages (18 total)');
        $this->command->info('💡 Send one more message to trigger insight generation!');
        $this->command->info("🔗 Login with: {$user->email} / InsightTest2024!SecurePass");
    }
}
