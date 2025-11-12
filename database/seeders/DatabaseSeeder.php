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
        // Create test users with different proficiency levels
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'name' => 'Alice Johnson',
            'email' => 'alice@example.com',
        ]);

        User::factory()->create([
            'name' => 'Bob Smith',
            'email' => 'bob@example.com',
        ]);

        User::factory()->create([
            'name' => 'Carlos García',
            'email' => 'carlos@example.com',
        ]);

        User::factory()->create([
            'name' => 'Diana Wong',
            'email' => 'diana@example.com',
        ]);

        $this->command->info('Created 5 test users:');
        $this->command->info('- test@example.com (password: password)');
        $this->command->info('- alice@example.com (password: password)');
        $this->command->info('- bob@example.com (password: password)');
        $this->command->info('- carlos@example.com (password: password)');
        $this->command->info('- diana@example.com (password: password)');
    }
}
