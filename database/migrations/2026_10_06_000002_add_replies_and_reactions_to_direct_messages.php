<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('direct_messages', function (Blueprint $table): void {
            $table
                ->foreignId('reply_to_message_id')
                ->nullable()
                ->after('recipient_id')
                ->constrained('direct_messages')
                ->nullOnDelete();
        });

        Schema::create('direct_message_reactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('direct_message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 24);
            $table->timestamps();

            $table->unique(['direct_message_id', 'user_id'], 'direct_message_reactions_one_per_user');
            $table->index(['direct_message_id', 'type'], 'direct_message_reactions_summary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direct_message_reactions');

        Schema::table('direct_messages', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('reply_to_message_id');
        });
    }
};
