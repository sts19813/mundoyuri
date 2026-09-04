<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('reportable');
            $table->string('reason', 40);
            $table->text('details')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at'], 'community_reports_queue_index');
            $table->index(['reportable_type', 'reportable_id', 'status'], 'community_reports_target_index');
            $table->index(['reporter_id', 'created_at'], 'community_reports_reporter_index');
        });

        Schema::create('community_moderation_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('community_report_id')->nullable()->constrained('community_reports')->nullOnDelete();
            $table->morphs('moderatable');
            $table->string('action', 60);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['moderatable_type', 'moderatable_id', 'created_at'], 'community_moderation_target_index');
            $table->index(['community_report_id', 'created_at'], 'community_moderation_report_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_moderation_logs');
        Schema::dropIfExists('community_reports');
    }
};
