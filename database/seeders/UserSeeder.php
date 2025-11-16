<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create main user
        User::factory()->create([
            'first_name' => 'Brandon',
            'last_name' => 'Maynard',
            'email' => 'bmmaynard87@gmail.com',
            'password' => bcrypt('MySecurePass2025!'),
            'native_language' => 'English',
            'target_language' => 'Japanese',
            'proficiency_level' => 'B2',
            'auto_update_proficiency' => true,
        ]);

        // Beginner French learner
        User::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
            'email' => 'alice@example.com',
            'native_language' => 'English',
            'target_language' => 'French',
            'proficiency_level' => 'A1',
            'auto_update_proficiency' => false,
        ]);

        // Intermediate German learner
        User::factory()->create([
            'first_name' => 'Bob',
            'last_name' => 'Smith',
            'email' => 'bob@example.com',
            'native_language' => 'English',
            'target_language' => 'German',
            'proficiency_level' => 'B1',
            'auto_update_proficiency' => true,
        ]);

        // Advanced Italian learner
        User::factory()->create([
            'first_name' => 'Carlos',
            'last_name' => 'García',
            'email' => 'carlos@example.com',
            'native_language' => 'Spanish',
            'target_language' => 'Italian',
            'proficiency_level' => 'C1',
            'auto_update_proficiency' => true,
        ]);

        // Test user
        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'native_language' => 'English',
            'target_language' => 'Japanese',
            'proficiency_level' => 'A2',
        ]);

        $this->command->info('✓ Created 5 users:');
        $this->command->info('  - bmmaynard87@gmail.com (password: MySecurePass2025!) - Japanese B2');
        $this->command->info('  - alice@example.com (password: password) - French A1 beginner');
        $this->command->info('  - bob@example.com (password: password) - German B1 intermediate');
        $this->command->info('  - carlos@example.com (password: password) - Italian C1 advanced');
        $this->command->info('  - test@example.com (password: password) - Japanese A2');
    }
}
