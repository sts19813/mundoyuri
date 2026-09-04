<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_threads', function (Blueprint $table): void {
            $table->dropForeign(['forum_id']);
        });

        Schema::table('forum_threads', function (Blueprint $table): void {
            $table->unsignedBigInteger('forum_id')->nullable()->change();
            $table->foreign('forum_id')->references('id')->on('forums')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::table('forum_threads')->whereNull('forum_id')->exists()) {
            throw new LogicException('No se puede restaurar forum_id como obligatorio mientras existan preguntas independientes.');
        }

        Schema::table('forum_threads', function (Blueprint $table): void {
            $table->dropForeign(['forum_id']);
        });

        Schema::table('forum_threads', function (Blueprint $table): void {
            $table->unsignedBigInteger('forum_id')->nullable(false)->change();
            $table->foreign('forum_id')->references('id')->on('forums')->cascadeOnDelete();
        });
    }
};
