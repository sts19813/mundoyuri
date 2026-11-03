<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('copyright_takedowns', function (Blueprint $table) {
            $table->id();
            $table->timestamp('received_at');
            $table->string('claimant_name');
            $table->string('claimant_email');
            $table->string('claimant_organization')->nullable();
            $table->text('work_description');
            $table->text('reported_urls');
            $table->string('status', 32)->default('received');
            $table->string('action_taken', 64)->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('copyright_takedowns');
    }
};
