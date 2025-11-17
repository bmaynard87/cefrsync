<?php

namespace Tests\Feature\Settings;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/settings/profile');

        $response->assertOk();
    }

    public function test_settings_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/settings/profile', [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings/profile');

        $user->refresh();

        $this->assertSame('Test', $user->first_name);
        $this->assertSame('User', $user->last_name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_settings_profile_can_update_languages_by_name(): void
    {
        $user = User::factory()->create([
            'native_language' => 'English',
            'target_language' => 'Spanish',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/settings/profile', [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'native_language' => 'Japanese',
                'target_language' => 'Korean',
            ]);

        $response->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertSame('Japanese', $user->native_language);
        $this->assertSame('Korean', $user->target_language);
        $this->assertSame(Language::findByKey('ja')->id, $user->native_language_id);
        $this->assertSame(Language::findByKey('ko')->id, $user->target_language_id);
    }

    public function test_settings_profile_can_update_languages_by_key(): void
    {
        $user = User::factory()->create([
            'native_language' => 'English',
            'target_language' => 'Spanish',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/settings/profile', [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'native_language' => 'fr',
                'target_language' => 'de',
            ]);

        $response->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertSame('French', $user->native_language);
        $this->assertSame('German', $user->target_language);
        $this->assertSame(Language::findByKey('fr')->id, $user->native_language_id);
        $this->assertSame(Language::findByKey('de')->id, $user->target_language_id);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/settings/profile', [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }
}
