<?php

use App\Models\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate users table
        $users = DB::table('users')->whereNotNull('native_language')->orWhereNotNull('target_language')->get();

        foreach ($users as $user) {
            $updates = [];

            if ($user->native_language) {
                $language = Language::where('name', $user->native_language)->first();
                if ($language) {
                    $updates['native_language_id'] = $language->id;
                }
            }

            if ($user->target_language) {
                $language = Language::where('name', $user->target_language)->first();
                if ($language) {
                    $updates['target_language_id'] = $language->id;
                }
            }

            if (! empty($updates)) {
                DB::table('users')->where('id', $user->id)->update($updates);
            }
        }

        // Migrate chat_sessions table
        $sessions = DB::table('chat_sessions')->whereNotNull('native_language')->orWhereNotNull('target_language')->get();

        foreach ($sessions as $session) {
            $updates = [];

            if ($session->native_language) {
                $language = Language::where('name', $session->native_language)->first();
                if ($language) {
                    $updates['native_language_id'] = $language->id;
                }
            }

            if ($session->target_language) {
                $language = Language::where('name', $session->target_language)->first();
                if ($language) {
                    $updates['target_language_id'] = $language->id;
                }
            }

            if (! empty($updates)) {
                DB::table('chat_sessions')->where('id', $session->id)->update($updates);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse migration - copy language names back from relationships
        $users = DB::table('users')->whereNotNull('native_language_id')->orWhereNotNull('target_language_id')->get();

        foreach ($users as $user) {
            $updates = [];

            if ($user->native_language_id) {
                $language = Language::find($user->native_language_id);
                if ($language) {
                    $updates['native_language'] = $language->name;
                }
            }

            if ($user->target_language_id) {
                $language = Language::find($user->target_language_id);
                if ($language) {
                    $updates['target_language'] = $language->name;
                }
            }

            if (! empty($updates)) {
                DB::table('users')->where('id', $user->id)->update($updates);
            }
        }

        // Migrate chat_sessions back
        $sessions = DB::table('chat_sessions')->whereNotNull('native_language_id')->orWhereNotNull('target_language_id')->get();

        foreach ($sessions as $session) {
            $updates = [];

            if ($session->native_language_id) {
                $language = Language::find($session->native_language_id);
                if ($language) {
                    $updates['native_language'] = $language->name;
                }
            }

            if ($session->target_language_id) {
                $language = Language::find($session->target_language_id);
                if ($language) {
                    $updates['target_language'] = $language->name;
                }
            }

            if (! empty($updates)) {
                DB::table('chat_sessions')->where('id', $session->id)->update($updates);
            }
        }
    }
};
