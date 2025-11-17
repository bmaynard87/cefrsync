<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add new foreign key columns
            $table->foreignId('native_language_id')->nullable()->after('email')->constrained('languages')->nullOnDelete();
            $table->foreignId('target_language_id')->nullable()->after('native_language_id')->constrained('languages')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['native_language_id']);
            $table->dropForeign(['target_language_id']);
            $table->dropColumn(['native_language_id', 'target_language_id']);
        });
    }
};
