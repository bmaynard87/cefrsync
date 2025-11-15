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
            // Keep nullable for Google OAuth users who haven't set preferences yet
            $table->string('native_language')->nullable()->change();
            $table->string('target_language')->nullable()->change();
            $table->string('proficiency_level')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('native_language')->nullable()->change();
            $table->string('target_language')->nullable()->change();
            $table->string('proficiency_level')->nullable()->change();
        });
    }
};
