<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedInteger('current_streak_days')->default(0)->after('last_seen_at');
            $table->unsignedInteger('longest_streak_days')->default(0)->after('current_streak_days');
            $table->date('last_streak_date')->nullable()->after('longest_streak_days')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['last_streak_date']);
            $table->dropColumn(['current_streak_days', 'longest_streak_days', 'last_streak_date']);
        });
    }
};
