<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
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
            'proficiency_level' => 'C2',
        ]);

        // Create test users with different proficiency levels
        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'first_name' => 'Alice',
            'last_name' => 'Johnson',
            'email' => 'alice@example.com',
        ]);

        User::factory()->create([
            'first_name' => 'Bob',
            'last_name' => 'Smith',
            'email' => 'bob@example.com',
        ]);

        User::factory()->create([
            'first_name' => 'Carlos',
            'last_name' => 'García',
            'email' => 'carlos@example.com',
        ]);

        User::factory()->create([
            'first_name' => 'Diana',
            'last_name' => 'Wong',
            'email' => 'diana@example.com',
        ]);

        $this->command->info('Created 6 users:');
        $this->command->info('- bmmaynard87@gmail.com (password: MySecurePass2025!)');
        $this->command->info('- test@example.com (password: password)');
        $this->command->info('- alice@example.com (password: password)');
        $this->command->info('- bob@example.com (password: password)');
        $this->command->info('- carlos@example.com (password: password)');
        $this->command->info('- diana@example.com (password: password)');
    }
}
