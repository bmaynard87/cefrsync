<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProductionUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder creates essential system and admin users for production.
     * It only runs in production environment to avoid polluting development databases.
     */
    public function run(): void
    {
        // Only run in production environment
        if (config('app.env') !== 'production') {
            $this->command->info('Skipping ProductionUserSeeder - not in production environment');

            return;
        }

        $this->createSystemUser();
        $this->createAdminUser();

        $this->command->info('Production users seeded successfully');
    }

    /**
     * Create the system user (ID 1).
     * This user is used for system-generated content and automated processes.
     */
    private function createSystemUser(): void
    {
        User::firstOrCreate(
            ['id' => 1],
            [
                'email' => 'system@cefrsync.com',
                'first_name' => 'System',
                'last_name' => 'User',
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(64)), // Random unguessable password
                'native_language' => 'English',
                'target_language' => 'Spanish',
                'proficiency_level' => 'C2',
                'auto_update_proficiency' => false,
            ]
        );

        $this->command->info('System user (ID 1) ensured');
    }

    /**
     * Create the admin user (ID 2).
     * Credentials are loaded from environment variables.
     */
    private function createAdminUser(): void
    {
        $adminEmail = config('app.admin_email') ?: 'admin@cefrsync.com';
        $adminPassword = config('app.admin_password') ?: Str::random(32);

        User::firstOrCreate(
            ['id' => 2],
            [
                'email' => $adminEmail,
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email_verified_at' => now(),
                'password' => Hash::make($adminPassword),
                'native_language' => 'English',
                'target_language' => 'Japanese',
                'proficiency_level' => 'C2',
                'auto_update_proficiency' => false,
            ]
        );

        $this->command->info('Admin user (ID 2) ensured with email: '.$adminEmail);
    }
}
