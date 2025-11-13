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
        Schema::table('users', function (Blueprint $table) {
            // Add new columns
            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');

            // Migrate existing data - split name into first and last
            // This assumes name format is "FirstName LastName"
        });

        // Migrate existing data
        DB::table('users')->get()->each(function ($user) {
            $nameParts = explode(' ', $user->name, 2);
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'first_name' => $nameParts[0] ?? '',
                    'last_name' => $nameParts[1] ?? '',
                ]);
        });

        Schema::table('users', function (Blueprint $table) {
            // Drop old name column
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add name column back
            $table->string('name')->after('id');
        });

        // Migrate data back
        DB::table('users')->get()->each(function ($user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'name' => trim($user->first_name.' '.$user->last_name),
                ]);
        });

        Schema::table('users', function (Blueprint $table) {
            // Drop first_name and last_name columns
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
