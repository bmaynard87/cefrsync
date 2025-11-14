<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class RegenerateLangGptKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'langgpt:regenerate-key';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate the LangGPT API key and update the .env file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Regenerating LangGPT API key...');

        $langGptUrl = config('services.langgpt.url');
        
        if (empty($langGptUrl)) {
            $this->error('LANGGPT_URL is not configured in .env file');
            return Command::FAILURE;
        }

        try {
            // Create a new API key
            $response = Http::post("{$langGptUrl}/api/keys", [
                'name' => 'CefrSync'
            ]);

            if (!$response->successful()) {
                $this->error('Failed to generate API key: ' . $response->body());
                return Command::FAILURE;
            }

            $data = $response->json();
            $newKey = $data['key'] ?? null;

            if (empty($newKey)) {
                $this->error('No API key returned from LangGPT');
                return Command::FAILURE;
            }

            // Update the .env file
            $envPath = base_path('.env');
            
            if (!file_exists($envPath)) {
                $this->error('.env file not found');
                return Command::FAILURE;
            }

            $envContent = file_get_contents($envPath);
            
            // Replace the LANGGPT_API_KEY value
            $pattern = '/LANGGPT_API_KEY=.*/';
            $replacement = "LANGGPT_API_KEY={$newKey}";
            
            $updatedContent = preg_replace($pattern, $replacement, $envContent);

            if ($updatedContent === null || $updatedContent === $envContent) {
                $this->error('Failed to update .env file. Make sure LANGGPT_API_KEY exists in your .env file.');
                return Command::FAILURE;
            }

            file_put_contents($envPath, $updatedContent);

            $this->newLine();
            $this->info('✓ API key regenerated successfully!');
            $this->line('  New key: ' . $newKey);
            $this->newLine();
            $this->warn('Note: You may need to restart your application for the changes to take effect.');
            $this->warn('      Run: php artisan config:clear');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
