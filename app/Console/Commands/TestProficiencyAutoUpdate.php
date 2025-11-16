<?php

namespace App\Console\Commands;

use App\Jobs\AnalyzeRecentMessages;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\User;
use App\Services\LangGptService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;

class TestProficiencyAutoUpdate extends Command
{
    protected $signature = 'test:proficiency-auto-update 
                            {--cleanup : Clean up test data after running}
                            {--force-update : Mock LangGPT response to force a proficiency update}';

    protected $description = 'Smoke test for the proficiency auto-update system';

    public function handle()
    {
        $this->info('=== Starting Proficiency Auto-Update Smoke Test ===');
        $this->newLine();

        // Clean up any existing test user (including soft-deleted)
        $existingUser = User::withTrashed()->where('email', 'proficiency-test@example.com')->first();
        if ($existingUser) {
            $this->warn('Cleaning up existing test user...');
            $existingUser->chatSessions()->delete();
            $existingUser->languageInsights()->delete();
            $existingUser->forceDelete();
        }

        // 1. Create test user with auto-update enabled
        $this->info('1. Creating test user with auto-update enabled...');
        $user = User::factory()->create([
            'first_name' => 'Proficiency',
            'last_name' => 'Test',
            'email' => 'proficiency-test@example.com',
            'proficiency_level' => 'A1',
            'auto_update_proficiency' => true,
            'native_language' => 'English',
            'target_language' => 'Spanish',
            'email_verified_at' => now(),
        ]);
        $this->line("   ✓ User created: {$user->email}");
        $this->line("   Initial proficiency: {$user->proficiency_level}");
        $this->line('   Auto-update enabled: '.($user->auto_update_proficiency ? 'Yes' : 'No'));
        $this->newLine();

        // 2. Create chat session
        $this->info('2. Creating chat session...');
        $session = ChatSession::factory()->create([
            'user_id' => $user->id,
            'native_language' => $user->native_language,
            'target_language' => $user->target_language,
            'proficiency_level' => $user->proficiency_level,
            'title' => 'Proficiency Test Session',
            'last_message_at' => now(),
        ]);
        $this->line("   ✓ Session created: ID {$session->id}");
        $this->newLine();

        // 3. Create test messages in Spanish
        $this->info('3. Creating 15 test messages in Spanish...');
        $spanishMessages = [
            'Hola, ¿cómo estás?',
            'Me llamo Juan y vivo en Madrid.',
            '¿Qué tiempo hace hoy?',
            'Me gusta mucho la comida española.',
            'Estoy aprendiendo español porque quiero viajar a España.',
            '¿Dónde está el restaurante más cercano?',
            'Necesito comprar un billete de tren.',
            'Mi familia es muy importante para mí.',
            '¿Puedes ayudarme con mi tarea?',
            'El libro que estoy leyendo es muy interesante.',
            'Trabajo en una oficina en el centro de la ciudad.',
            'Los fines de semana me gusta ir al cine.',
            '¿Has visitado alguna vez Barcelona?',
            'La cultura española es muy rica y diversa.',
            'Espero poder hablar español con fluidez pronto.',
        ];

        foreach ($spanishMessages as $index => $content) {
            ChatMessage::factory()->create([
                'chat_session_id' => $session->id,
                'sender_type' => 'user',
                'content' => $content,
                'created_at' => now()->subMinutes(15 - $index),
            ]);
        }
        $this->line('   ✓ Created 15 user messages');
        $this->newLine();

        // 4. Display before state
        $this->info('4. Before analysis:');
        $this->line("   User proficiency level: {$user->proficiency_level}");
        $this->line('   Insights count: '.$user->languageInsights()->count());
        $this->line('   Auto-update enabled: '.($user->auto_update_proficiency ? 'Yes' : 'No'));
        $this->newLine();

        // 5. Run the analysis job synchronously
        $this->info('5. Running AnalyzeRecentMessages job...');

        if ($this->option('force-update')) {
            $this->line('   (Using MOCKED LangGPT response to force proficiency update)');
            $this->mockLangGptForUpdate();
        } else {
            $this->line('   (This will call OpenAI for language detection and LangGPT for analysis)');
        }

        try {
            AnalyzeRecentMessages::dispatchSync($session, 20);
            $this->line('   ✓ Analysis job completed');
        } catch (\Exception $e) {
            $this->error('   ✗ Error running analysis: '.$e->getMessage());
            $this->line('   Stack trace: '.$e->getTraceAsString());
        }
        $this->newLine();

        // 6. Refresh and display results
        $user->refresh();

        $this->info('6. After analysis:');

        if ($this->option('force-update')) {
            $this->line('   Initial proficiency: A1 (Beginner)');
            $this->line("   Final proficiency: {$user->proficiency_level} ({$this->getLevelName($user->proficiency_level)})");

            if ($user->proficiency_level === 'A2') {
                $this->line('   ✓ Proficiency level was UPDATED as expected!');
            } else {
                $this->error('   ✗ Proficiency level was NOT updated (expected A2, got '.$user->proficiency_level.')');
            }
        } else {
            $this->line('   User proficiency level: '.($user->proficiency_level ?? 'null'));
            if ($user->proficiency_level === 'A1') {
                $this->line('   (Level unchanged - LangGPT may not have suggested an update)');
            } elseif ($user->proficiency_level !== 'A1') {
                $this->line('   (Level was updated by LangGPT)');
            }
        }

        $this->line('   Insights created: '.$user->languageInsights()->count());
        $this->newLine();

        // 7. Display insights details
        if ($user->languageInsights()->count() > 0) {
            $this->info('7. Insights created:');
            foreach ($user->languageInsights as $insight) {
                $this->line("   - {$insight->insight_type}: {$insight->title}");
                if ($insight->insight_type === 'proficiency_suggestion') {
                    $this->line('     Current: '.($insight->data['current_level'] ?? 'N/A'));
                    $this->line('     Suggested: '.($insight->data['suggested_level'] ?? 'N/A'));
                    if (isset($insight->data['reasoning'])) {
                        $this->line('     Reasoning: '.$insight->data['reasoning']);
                    }
                }
            }
        } else {
            $this->warn('7. No insights were created');
            $this->line('   This might indicate:');
            $this->line('   - LangGPT API is not configured');
            $this->line('   - API returned no analysis data');
            $this->line('   - Language detection filtered out all messages');
        }
        $this->newLine();

        $this->info('=== Test Summary ===');
        $this->line("Test user email: {$user->email}");
        $this->line("Session ID: {$session->id}");
        $this->line('Final proficiency: '.($user->proficiency_level ?? 'null'));
        $this->line('Total insights: '.$user->languageInsights()->count());
        $this->newLine();

        // Cleanup if requested
        if ($this->option('cleanup')) {
            $this->warn('=== Cleaning up test data ===');
            $user->chatSessions()->delete();
            $user->languageInsights()->delete();
            $user->forceDelete();
            $this->line('✓ Test data cleaned up');
        } else {
            $this->info('=== Cleanup ===');
            $this->line('To clean up, run:');
            $this->line('  sail artisan test:proficiency-auto-update --cleanup');
        }
        $this->newLine();

        $this->info('=== Test Complete ===');

        return Command::SUCCESS;
    }

    /**
     * Mock LangGPT service to force a proficiency update
     */
    protected function mockLangGptForUpdate(): void
    {
        $mockService = \Mockery::mock(LangGptService::class);
        $mockService->shouldReceive('evaluateProgress')
            ->andReturn([
                'success' => true,
                'data' => [
                    'suggested_level' => 'A2',
                    'confidence' => 0.85,
                    'grammar_patterns' => [
                        'basic_tenses' => 'Good grasp of present and past tense',
                        'sentence_structure' => 'Progressing beyond simple sentences',
                    ],
                    'vocabulary_assessment' => [
                        'range' => 'Expanding beyond basic vocabulary',
                        'accuracy' => 'Consistent use of common words',
                    ],
                    'proficiency_message' => 'Ready to advance to A2 level!',
                    'reasoning' => 'Consistent performance above A1 level with growing vocabulary and grammar complexity',
                ],
            ]);

        App::instance(LangGptService::class, $mockService);
    }

    /**
     * Get human-readable name for CEFR level
     */
    protected function getLevelName(?string $level): string
    {
        $levels = [
            'A1' => 'Beginner',
            'A2' => 'Elementary',
            'B1' => 'Intermediate',
            'B2' => 'Upper Intermediate',
            'C1' => 'Advanced',
            'C2' => 'Proficient',
        ];

        return $levels[$level] ?? 'Unknown';
    }
}
