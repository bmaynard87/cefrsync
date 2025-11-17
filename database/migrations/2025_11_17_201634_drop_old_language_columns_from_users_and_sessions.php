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
            $table->dropColumn(['native_language', 'target_language']);
        });

        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->dropColumn(['native_language', 'target_language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('native_language')->nullable()->after('email');
            $table->string('target_language')->nullable()->after('native_language');
        });

        Schema::table('chat_sessions', function (Blueprint $table) {
            $table->string('native_language')->nullable()->after('user_id');
            $table->string('target_language')->nullable()->after('native_language');
        });
    }
};
