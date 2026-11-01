<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badge_award_logs', function (Blueprint $table): void {
            $table->timestamp('notified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('badge_award_logs', function (Blueprint $table): void {
            $table->dropColumn('notified_at');
        });
    }
};
