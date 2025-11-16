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
        // SQLite doesn't support altering enums directly, so we need to recreate the table
        // This is safe in tests since we use RefreshDatabase

        // For SQLite (used in tests)
        if (DB::connection()->getDriverName() === 'sqlite') {
            // Create a new table with the updated enum
            Schema::create('chat_messages_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chat_session_id')->constrained()->onDelete('cascade');
                $table->enum('sender_type', ['user', 'assistant', 'system']);
                $table->string('message_type')->default('user')->after('sender_type');
                $table->text('content');
                $table->json('correction_data')->nullable()->after('content');
                $table->timestamps();
            });

            // Copy data from old table
            DB::statement('INSERT INTO chat_messages_new (id, chat_session_id, sender_type, message_type, content, correction_data, created_at, updated_at) 
                           SELECT id, chat_session_id, sender_type, message_type, content, correction_data, created_at, updated_at FROM chat_messages');

            // Drop old table and rename new one
            Schema::dropIfExists('chat_messages');
            Schema::rename('chat_messages_new', 'chat_messages');
        } else {
            // For MySQL/PostgreSQL
            DB::statement("ALTER TABLE chat_messages MODIFY sender_type ENUM('user', 'assistant', 'system')");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For SQLite
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::create('chat_messages_old', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chat_session_id')->constrained()->onDelete('cascade');
                $table->enum('sender_type', ['user', 'assistant']);
                $table->string('message_type')->default('user')->after('sender_type');
                $table->text('content');
                $table->json('correction_data')->nullable()->after('content');
                $table->timestamps();
            });

            DB::statement('INSERT INTO chat_messages_old (id, chat_session_id, sender_type, message_type, content, correction_data, created_at, updated_at) 
                           SELECT id, chat_session_id, sender_type, message_type, content, correction_data, created_at, updated_at FROM chat_messages WHERE sender_type != "system"');

            Schema::dropIfExists('chat_messages');
            Schema::rename('chat_messages_old', 'chat_messages');
        } else {
            DB::statement("ALTER TABLE chat_messages MODIFY sender_type ENUM('user', 'assistant')");
        }
    }
};
