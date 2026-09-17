<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('visitor_id', 80);
            $table->date('visited_on');
            $table->timestamp('visited_at');
            $table->string('path');
            $table->char('path_hash', 40);

            $table->index('visited_on');
            $table->index('visited_at');
            $table->index(['visited_on', 'user_id']);
            $table->index(['visitor_id', 'path_hash', 'visited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};
