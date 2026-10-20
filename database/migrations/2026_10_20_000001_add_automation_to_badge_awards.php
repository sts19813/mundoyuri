<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badge_user', function (Blueprint $table): void {
            $table->string('assignment_source', 20)->default('manual')->after('note');
            $table->string('automation_rule', 100)->nullable()->after('assignment_source');
            $table->index(['assignment_source', 'automation_rule'], 'badge_user_automation_index');
        });

        Schema::create('badge_award_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('badge_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('badge_name', 100);
            $table->string('badge_slug', 100);
            $table->string('action', 20);
            $table->string('source', 20);
            $table->string('automation_rule', 100)->nullable();
            $table->text('condition')->nullable();
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'badge_award_logs_user_date_index');
            $table->index(['badge_id', 'created_at'], 'badge_award_logs_badge_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_award_logs');

        Schema::table('badge_user', function (Blueprint $table): void {
            $table->dropIndex('badge_user_automation_index');
            $table->dropColumn(['assignment_source', 'automation_rule']);
        });
    }
};
