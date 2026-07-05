<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backblaze_b2_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->string('key_id')->nullable();
            $table->text('application_key')->nullable();
            $table->string('bucket_name')->nullable();
            $table->string('bucket_id')->nullable();
            $table->string('download_url')->nullable();
            $table->unsignedInteger('token_ttl_seconds')->default(3600);
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backblaze_b2_settings');
    }
};
