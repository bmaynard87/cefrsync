<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ProductionUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductionUserSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set environment variables for testing
        config(['app.env' => 'production']);
        config(['app.admin_email' => 'admin@test.com']);
        config(['app.admin_password' => 'test-password-123']);
    }

    public function test_seeder_creates_system_user_with_id_1(): void
    {
        $this->seed(ProductionUserSeeder::class);

        $systemUser = User::find(1);

        $this->assertNotNull($systemUser);
        $this->assertEquals(1, $systemUser->id);
        $this->assertEquals('system@cefrsync.com', $systemUser->email);
        $this->assertEquals('System', $systemUser->first_name);
        $this->assertEquals('User', $systemUser->last_name);
        $this->assertNotNull($systemUser->email_verified_at);
        $this->assertEquals('English', $systemUser->native_language);
        $this->assertEquals('Spanish', $systemUser->target_language);
        $this->assertEquals('C2', $systemUser->proficiency_level);
        $this->assertFalse($systemUser->auto_update_proficiency);
    }

    public function test_seeder_creates_admin_user_with_id_2(): void
    {
        $this->seed(ProductionUserSeeder::class);

        $adminUser = User::find(2);

        $this->assertNotNull($adminUser);
        $this->assertEquals(2, $adminUser->id);
        $this->assertEquals('admin@test.com', $adminUser->email);
        $this->assertEquals('Admin', $adminUser->first_name);
        $this->assertEquals('User', $adminUser->last_name);
        $this->assertNotNull($adminUser->email_verified_at);
        $this->assertTrue(Hash::check('test-password-123', $adminUser->password));
        $this->assertEquals('English', $adminUser->native_language);
        $this->assertEquals('Japanese', $adminUser->target_language);
        $this->assertEquals('C2', $adminUser->proficiency_level);
        $this->assertFalse($adminUser->auto_update_proficiency);
    }

    public function test_seeder_is_idempotent(): void
    {
        // Run seeder twice
        $this->seed(ProductionUserSeeder::class);
        $this->seed(ProductionUserSeeder::class);

        // Should still only have 2 users with those IDs
        $this->assertCount(2, User::all());
        $this->assertNotNull(User::find(1));
        $this->assertNotNull(User::find(2));
    }

    public function test_seeder_does_not_override_existing_users(): void
    {
        // Create a user with ID 1 first
        User::factory()->create([
            'id' => 1,
            'email' => 'existing@example.com',
            'first_name' => 'Existing',
        ]);

        $this->seed(ProductionUserSeeder::class);

        // User 1 should remain unchanged
        $user1 = User::find(1);
        $this->assertEquals('existing@example.com', $user1->email);
        $this->assertEquals('Existing', $user1->first_name);

        // User 2 should be created
        $this->assertNotNull(User::find(2));
    }

    public function test_system_user_has_unguessable_password(): void
    {
        $this->seed(ProductionUserSeeder::class);

        $systemUser = User::find(1);

        // Password should not be a common value
        $this->assertFalse(Hash::check('password', $systemUser->password));
        $this->assertFalse(Hash::check('system', $systemUser->password));
        $this->assertFalse(Hash::check('', $systemUser->password));
        $this->assertFalse(Hash::check('123456', $systemUser->password));
    }

    public function test_seeder_skips_in_non_production_environment(): void
    {
        config(['app.env' => 'local']);

        $this->seed(ProductionUserSeeder::class);

        // No users should be created in non-production
        $this->assertCount(0, User::all());
    }

    public function test_seeder_uses_environment_variables_for_admin(): void
    {
        config(['app.admin_email' => 'custom@admin.com']);
        config(['app.admin_password' => 'custom-secure-password']);

        $this->seed(ProductionUserSeeder::class);

        $adminUser = User::find(2);

        $this->assertEquals('custom@admin.com', $adminUser->email);
        $this->assertTrue(Hash::check('custom-secure-password', $adminUser->password));
    }

    public function test_seeder_falls_back_to_defaults_when_env_not_set(): void
    {
        config(['app.admin_email' => null]);
        config(['app.admin_password' => null]);

        $this->seed(ProductionUserSeeder::class);

        $adminUser = User::find(2);

        $this->assertEquals('admin@cefrsync.com', $adminUser->email);
        // Password should still be hashed and valid
        $this->assertNotEmpty($adminUser->password);
    }
}
