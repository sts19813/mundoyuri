<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('direct_messages', function (Blueprint $table): void {
            $table->uuid('client_id')->nullable();
            $table->unique(['sender_id', 'client_id'], 'direct_messages_sender_client_unique');
        });
    }

    public function down(): void
    {
        Schema::table('direct_messages', function (Blueprint $table): void {
            $table->dropUnique('direct_messages_sender_client_unique');
            $table->dropColumn('client_id');
        });
    }
};
