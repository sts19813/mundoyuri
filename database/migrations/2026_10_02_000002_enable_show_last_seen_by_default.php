<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'show_last_seen')) {
            return;
        }

        DB::table('users')->update(['show_last_seen' => true]);

        try {
            DB::statement('ALTER TABLE users ALTER COLUMN show_last_seen SET DEFAULT 1');
        } catch (Throwable) {
            // SQLite and some hosted MySQL variants cannot alter boolean defaults in-place.
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'show_last_seen')) {
            return;
        }

        try {
            DB::statement('ALTER TABLE users ALTER COLUMN show_last_seen SET DEFAULT 0');
        } catch (Throwable) {
            // Kept intentionally non-destructive for existing user privacy choices.
        }
    }
};
