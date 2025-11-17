<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('key', 10)->unique(); // e.g., 'en', 'es', 'zh-CN'
            $table->string('name'); // e.g., 'English', 'Spanish'
            $table->string('native_name'); // e.g., 'English', 'Español'
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed initial languages
        $languages = [
            ['key' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية'],
            ['key' => 'zh', 'name' => 'Chinese (Mandarin)', 'native_name' => '中文 (普通话)'],
            ['key' => 'zh-Hant', 'name' => 'Chinese (Cantonese)', 'native_name' => '中文 (粤语)'],
            ['key' => 'nl', 'name' => 'Dutch', 'native_name' => 'Nederlands'],
            ['key' => 'en', 'name' => 'English', 'native_name' => 'English'],
            ['key' => 'fr', 'name' => 'French', 'native_name' => 'Français'],
            ['key' => 'de', 'name' => 'German', 'native_name' => 'Deutsch'],
            ['key' => 'el', 'name' => 'Greek', 'native_name' => 'Ελληνικά'],
            ['key' => 'he', 'name' => 'Hebrew', 'native_name' => 'עברית'],
            ['key' => 'hi', 'name' => 'Hindi', 'native_name' => 'हिन्दी'],
            ['key' => 'it', 'name' => 'Italian', 'native_name' => 'Italiano'],
            ['key' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語'],
            ['key' => 'ko', 'name' => 'Korean', 'native_name' => '한국어'],
            ['key' => 'pl', 'name' => 'Polish', 'native_name' => 'Polski'],
            ['key' => 'pt', 'name' => 'Portuguese', 'native_name' => 'Português'],
            ['key' => 'ru', 'name' => 'Russian', 'native_name' => 'Русский'],
            ['key' => 'es', 'name' => 'Spanish', 'native_name' => 'Español'],
            ['key' => 'sv', 'name' => 'Swedish', 'native_name' => 'Svenska'],
            ['key' => 'tr', 'name' => 'Turkish', 'native_name' => 'Türkçe'],
            ['key' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt'],
        ];

        foreach ($languages as $language) {
            DB::table('languages')->insert(array_merge($language, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
