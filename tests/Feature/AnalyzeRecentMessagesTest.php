<?php

use App\Jobs\AnalyzeRecentMessages;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\LanguageInsight;
use App\Models\User;
use App\Services\LangGptService;
use App\Services\OpenAiService;
use Illuminate\Support\Facades\Queue;

test('job can be dispatched', function () {
    Queue::fake();

    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    AnalyzeRecentMessages::dispatch($session);

    Queue::assertPushed(AnalyzeRecentMessages::class);
});

test('job does nothing if no user messages exist', function () {
    $user = User::factory()->create();
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    // Create only assistant messages
    ChatMessage::factory()->count(3)->create([
        'chat_session_id' => $session->id,
        'sender_type' => 'assistant',
    ]);

    $job = new AnalyzeRecentMessages($session);
    $langGptService = $this->mock(LangGptService::class);
    $openAiService = $this->mock(OpenAiService::class);

    // Should not call any services
    $langGptService->shouldNotReceive('evaluateProgress');

    $job->handle($langGptService, $openAiService);

    expect(LanguageInsight::count())->toBe(0);
});

test('job filters out non-target language messages', function () {
    $user = User::factory()->create([
        'native_language' => 'English',
        'target_language' => 'Japanese',
        'proficiency_level' => 'B1',
    ]);

    $session = ChatSession::factory()->create([
        'user_id' => $user->id,
        'native_language' => $user->native_language,
        'target_language' => $user->target_language,
        'proficiency_level' => $user->proficiency_level,
    ]);

    // Create messages in different languages
    $japaneseMsg = ChatMessage::factory()->create([
        'chat_session_id' => $session->id,
        'sender_type' => 'user',
        'content' => 'こんにちは、元気ですか？',
    ]);

    $englishMsg = ChatMessage::factory()->create([
        'chat_session_id' => $session->id,
        'sender_type' => 'user',
        'content' => 'Hello, how are you?',
    ]);

    $openAiService = $this->mock(OpenAiService::class);
    $langGptService = $this->mock(LangGptService::class);

    // Mock language detection - Japanese message is target language
    $openAiService->shouldReceive('detectLanguage')
        ->with($japaneseMsg->content, 'Japanese')
        ->andReturn(['is_target_language' => true, 'detected_language' => 'Japanese']);

    // English message is not target language
    $openAiService->shouldReceive('detectLanguage')
        ->with($englishMsg->content, 'Japanese')
        ->andReturn(['is_target_language' => false, 'detected_language' => 'English']);

    // Should only analyze the Japanese message
    $langGptService->shouldReceive('evaluateProgress')
        ->once()
        ->with(\Mockery::on(function ($payload) use ($japaneseMsg) {
            return count($payload['messages']) === 1
                && $payload['messages'][0]['content'] === $japaneseMsg->content;
        }))
        ->andReturn([
            'success' => true,
            'data' => [
                'grammar_patterns' => [],
                'vocabulary_assessment' => [],
            ],
        ]);

    $job = new AnalyzeRecentMessages($session);
    $job->handle($langGptService, $openAiService);
});

test('job creates grammar pattern insights', function () {
    $user = User::factory()->create([
        'target_language' => 'Spanish',
        'proficiency_level' => 'A2',
    ]);

    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    ChatMessage::factory()->create([
        'chat_session_id' => $session->id,
        'sender_type' => 'user',
        'content' => 'Yo va al parque',
    ]);

    $openAiService = $this->mock(OpenAiService::class);
    $langGptService = $this->mock(LangGptService::class);

    $openAiService->shouldReceive('detectLanguage')
        ->andReturn(['is_target_language' => true]);

    $langGptService->shouldReceive('evaluateProgress')
        ->andReturn([
            'success' => true,
            'data' => [
                'grammar_patterns' => [
                    [
                        'pattern' => 'Incorrect verb conjugation',
                        'frequency' => 'common',
                        'examples' => ['Yo va al parque'],
                        'severity' => 'moderate',
                    ],
                ],
                'grammar_summary' => 'Common errors with verb conjugation',
                'vocabulary_assessment' => [],
            ],
        ]);

    $job = new AnalyzeRecentMessages($session);
    $job->handle($langGptService, $openAiService);

    expect(LanguageInsight::count())->toBe(1);

    $insight = LanguageInsight::first();
    expect($insight->insight_type)->toBe('grammar_pattern');
    expect($insight->user_id)->toBe($user->id);
    expect($insight->data)->toHaveKey('patterns');
});

test('job creates vocabulary strength insights', function () {
    $user = User::factory()->create([
        'target_language' => 'French',
        'proficiency_level' => 'B1',
    ]);
    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    ChatMessage::factory()->count(5)->create([
        'chat_session_id' => $session->id,
        'sender_type' => 'user',
    ]);

    $openAiService = $this->mock(OpenAiService::class);
    $langGptService = $this->mock(LangGptService::class);

    $openAiService->shouldReceive('detectLanguage')
        ->andReturn(['is_target_language' => true]);

    $langGptService->shouldReceive('evaluateProgress')
        ->andReturn([
            'success' => true,
            'data' => [
                'grammar_patterns' => [],
                'vocabulary_assessment' => [
                    'complexity_level' => 'intermediate',
                    'variety_score' => 0.8,
                ],
                'vocabulary_summary' => 'Strong vocabulary usage',
            ],
        ]);

    $job = new AnalyzeRecentMessages($session);
    $job->handle($langGptService, $openAiService);

    $insight = LanguageInsight::where('insight_type', 'vocabulary_strength')->first();
    expect($insight)->not->toBeNull();
    expect($insight->data['insights'])->toHaveKey('complexity_level');
});

test('job creates proficiency suggestion when level changes', function () {
    $user = User::factory()->create([
        'proficiency_level' => 'B1',
        'target_language' => 'Spanish',
        'auto_update_proficiency' => false, // Don't auto-update for this test
    ]);

    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    ChatMessage::factory()->count(10)->create([
        'chat_session_id' => $session->id,
        'sender_type' => 'user',
    ]);

    $openAiService = $this->mock(OpenAiService::class);
    $langGptService = $this->mock(LangGptService::class);

    $openAiService->shouldReceive('detectLanguage')
        ->andReturn(['is_target_language' => true]);

    $langGptService->shouldReceive('evaluateProgress')
        ->andReturn([
            'success' => true,
            'data' => [
                'grammar_patterns' => [],
                'vocabulary_assessment' => [],
                'suggested_level' => 'B2',
                'confidence' => 0.85,
                'proficiency_message' => 'Ready to advance to B2!',
                'reasoning' => 'Consistent complex structure usage',
            ],
        ]);

    $job = new AnalyzeRecentMessages($session);
    $job->handle($langGptService, $openAiService);

    $insight = LanguageInsight::where('insight_type', 'proficiency_suggestion')->first();
    expect($insight)->not->toBeNull();
    expect($insight->data['current_level'])->toBe('B1');
    expect($insight->data['suggested_level'])->toBe('B2');
});

test('job updates proficiency when user opted in and confidence is high', function () {
    $user = User::factory()->create([
        'proficiency_level' => 'A2',
        'target_language' => 'German',
        'auto_update_proficiency' => true,
    ]);

    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    ChatMessage::factory()->count(10)->create([
        'chat_session_id' => $session->id,
        'sender_type' => 'user',
    ]);

    $openAiService = $this->mock(OpenAiService::class);
    $langGptService = $this->mock(LangGptService::class);

    $openAiService->shouldReceive('detectLanguage')
        ->andReturn(['is_target_language' => true]);

    $langGptService->shouldReceive('evaluateProgress')
        ->andReturn([
            'success' => true,
            'data' => [
                'grammar_patterns' => [],
                'vocabulary_assessment' => [],
                'suggested_level' => 'B1',
                'confidence' => 0.9, // High confidence
            ],
        ]);

    $job = new AnalyzeRecentMessages($session);
    $job->handle($langGptService, $openAiService);

    expect($user->fresh()->proficiency_level)->toBe('B1');
});

test('job does not update proficiency when user opted out', function () {
    $user = User::factory()->create([
        'proficiency_level' => 'A2',
        'target_language' => 'Italian',
        'auto_update_proficiency' => false,
    ]);

    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    ChatMessage::factory()->count(10)->create([
        'chat_session_id' => $session->id,
        'sender_type' => 'user',
    ]);

    $openAiService = $this->mock(OpenAiService::class);
    $langGptService = $this->mock(LangGptService::class);

    $openAiService->shouldReceive('detectLanguage')
        ->andReturn(['is_target_language' => true]);

    $langGptService->shouldReceive('evaluateProgress')
        ->andReturn([
            'success' => true,
            'data' => [
                'grammar_patterns' => [],
                'vocabulary_assessment' => [],
                'suggested_level' => 'B1',
                'confidence' => 0.9,
            ],
        ]);

    $job = new AnalyzeRecentMessages($session);
    $job->handle($langGptService, $openAiService);

    expect($user->fresh()->proficiency_level)->toBe('A2');
});

test('job does not update proficiency when confidence is low', function () {
    $user = User::factory()->create([
        'proficiency_level' => 'A2',
        'target_language' => 'Portuguese',
        'auto_update_proficiency' => true,
    ]);

    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    ChatMessage::factory()->count(10)->create([
        'chat_session_id' => $session->id,
        'sender_type' => 'user',
    ]);

    $openAiService = $this->mock(OpenAiService::class);
    $langGptService = $this->mock(LangGptService::class);

    $openAiService->shouldReceive('detectLanguage')
        ->andReturn(['is_target_language' => true]);

    $langGptService->shouldReceive('evaluateProgress')
        ->andReturn([
            'success' => true,
            'data' => [
                'grammar_patterns' => [],
                'vocabulary_assessment' => [],
                'suggested_level' => 'B1',
                'confidence' => 0.5, // Low confidence
            ],
        ]);

    $job = new AnalyzeRecentMessages($session);
    $job->handle($langGptService, $openAiService);

    expect($user->fresh()->proficiency_level)->toBe('A2');
});

test('job does not downgrade proficiency level', function () {
    $user = User::factory()->create([
        'proficiency_level' => 'B2',
        'target_language' => 'Russian',
        'auto_update_proficiency' => true,
    ]);

    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    ChatMessage::factory()->count(10)->create([
        'chat_session_id' => $session->id,
        'sender_type' => 'user',
    ]);

    $openAiService = $this->mock(OpenAiService::class);
    $langGptService = $this->mock(LangGptService::class);

    $openAiService->shouldReceive('detectLanguage')
        ->andReturn(['is_target_language' => true]);

    $langGptService->shouldReceive('evaluateProgress')
        ->andReturn([
            'success' => true,
            'data' => [
                'grammar_patterns' => [],
                'vocabulary_assessment' => [],
                'suggested_level' => 'B1', // Lower level
                'confidence' => 0.9,
            ],
        ]);

    $job = new AnalyzeRecentMessages($session);
    $job->handle($langGptService, $openAiService);

    // Should remain at B2
    expect($user->fresh()->proficiency_level)->toBe('B2');
});

test('job sends localize_insights flag when user has it enabled', function () {
    $user = User::factory()->create([
        'native_language' => 'Spanish',
        'target_language' => 'French',
        'proficiency_level' => 'A2',
        'localize_insights' => true,
    ]);

    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    ChatMessage::factory()->create([
        'chat_session_id' => $session->id,
        'sender_type' => 'user',
        'content' => 'Je vais à l\'école',
    ]);

    $openAiService = $this->mock(OpenAiService::class);
    $langGptService = $this->mock(LangGptService::class);

    $openAiService->shouldReceive('detectLanguage')
        ->andReturn(['is_target_language' => true]);

    // Verify that localize_insights is sent to LangGPT
    $langGptService->shouldReceive('evaluateProgress')
        ->once()
        ->with(\Mockery::on(function ($payload) {
            return $payload['localize_insights'] === true
                && $payload['native_language'] === 'Spanish';
        }))
        ->andReturn([
            'success' => true,
            'data' => [
                'grammar_patterns' => [],
                'vocabulary_assessment' => [],
                'localized' => true,
                'language' => 'Spanish',
            ],
        ]);

    $job = new AnalyzeRecentMessages($session);
    $job->handle($langGptService, $openAiService);
});

test('job does not send localize_insights when user has it disabled', function () {
    $user = User::factory()->create([
        'native_language' => 'German',
        'target_language' => 'English',
        'proficiency_level' => 'B1',
        'localize_insights' => false,
    ]);

    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    ChatMessage::factory()->create([
        'chat_session_id' => $session->id,
        'sender_type' => 'user',
        'content' => 'Hello world',
    ]);

    $openAiService = $this->mock(OpenAiService::class);
    $langGptService = $this->mock(LangGptService::class);

    $openAiService->shouldReceive('detectLanguage')
        ->andReturn(['is_target_language' => true]);

    // Verify that localize_insights defaults to false
    $langGptService->shouldReceive('evaluateProgress')
        ->once()
        ->with(\Mockery::on(function ($payload) {
            return $payload['localize_insights'] === false;
        }))
        ->andReturn([
            'success' => true,
            'data' => [
                'grammar_patterns' => [],
                'vocabulary_assessment' => [],
                'localized' => false,
                'language' => 'English',
            ],
        ]);

    $job = new AnalyzeRecentMessages($session);
    $job->handle($langGptService, $openAiService);
});

test('job creates proficiency insight on initial proficiency assignment', function () {
    $user = User::factory()->create([
        'proficiency_level' => null, // No proficiency level set
        'target_language' => 'Spanish',
        'auto_update_proficiency' => true,
    ]);

    $session = ChatSession::factory()->create(['user_id' => $user->id]);

    ChatMessage::factory()->create([
        'chat_session_id' => $session->id,
        'sender_type' => 'user',
        'content' => 'Hola, ¿cómo estás?',
    ]);

    $openAiService = $this->mock(OpenAiService::class);
    $langGptService = $this->mock(LangGptService::class);

    $openAiService->shouldReceive('detectLanguage')
        ->andReturn(['is_target_language' => true]);

    $langGptService->shouldReceive('evaluateProgress')
        ->once()
        ->andReturn([
            'success' => true,
            'data' => [
                'grammar_patterns' => ['pattern1'],
                'grammar_summary' => 'Good grammar',
                'vocabulary_assessment' => ['assessment1'],
                'vocabulary_summary' => 'Nice vocabulary',
                'suggested_level' => 'B1',
                'confidence' => 0.8,
                'proficiency_message' => 'You are at B1 level',
                'reasoning' => 'Based on conversation analysis',
            ],
        ]);

    $job = new AnalyzeRecentMessages($session);
    $job->handle($langGptService, $openAiService);

    // User should have proficiency level set
    $user->refresh();
    expect($user->proficiency_level)->toBe('B1');

    // All 3 insight types should be created, including proficiency suggestion
    expect(LanguageInsight::where('chat_session_id', $session->id)->count())->toBe(3);

    $proficiencyInsight = LanguageInsight::where('insight_type', 'proficiency_suggestion')->first();
    expect($proficiencyInsight)->not->toBeNull();
    expect($proficiencyInsight->title)->toBe('Initial Proficiency Assessment');
    expect($proficiencyInsight->data['current_level'])->toBe('B1');
    expect($proficiencyInsight->data['suggested_level'])->toBe('B1');
});
