<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_ranks', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('minimum_posts')->nullable();
            $table->unsignedSmallInteger('priority')->default(0);
            $table->string('icon', 50)->nullable();
            $table->string('css_class', 120)->nullable();
            $table->boolean('is_special')->default(false);
            $table->boolean('is_legacy')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('color', 20)->nullable();
            $table->timestamps();

            $table->index(['is_active', 'is_special', 'minimum_posts', 'priority'], 'community_ranks_resolution_index');
        });

        Schema::create('community_badges', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('color', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('community_badge_user', function (Blueprint $table): void {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('community_badge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamp('awarded_at')->nullable();
            $table->timestamps();

            $table->primary(['user_id', 'community_badge_id']);
            $table->index(['community_badge_id', 'awarded_at'], 'community_badges_awarded_index');
        });

        $now = now();

        DB::table('community_ranks')->insert([
            ['name' => 'Nuevo miembro', 'slug' => 'nuevo-miembro', 'description' => 'Primeros pasos en la comunidad.', 'minimum_posts' => 0, 'priority' => 10, 'icon' => '✦', 'is_special' => false, 'is_legacy' => false, 'is_active' => true, 'color' => '#a08faa', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kohai', 'slug' => 'kohai', 'description' => 'Miembro que comienza a participar.', 'minimum_posts' => 10, 'priority' => 20, 'icon' => '♡', 'is_special' => false, 'is_legacy' => false, 'is_active' => true, 'color' => '#c084fc', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Yuri Fan', 'slug' => 'yuri-fan', 'description' => 'Participante habitual de Mundo Yuri.', 'minimum_posts' => 50, 'priority' => 30, 'icon' => '❀', 'is_special' => false, 'is_legacy' => false, 'is_active' => true, 'color' => '#f472b6', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Yuri Senpai', 'slug' => 'yuri-senpai', 'description' => 'Miembro con una trayectoria destacada.', 'minimum_posts' => 200, 'priority' => 40, 'icon' => '✧', 'is_special' => false, 'is_legacy' => false, 'is_active' => true, 'color' => '#fb7185', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Onee-sama', 'slug' => 'onee-sama', 'description' => 'Rango superior de participación comunitaria.', 'minimum_posts' => 500, 'priority' => 50, 'icon' => '♛', 'is_special' => false, 'is_legacy' => false, 'is_active' => true, 'color' => '#f43f8e', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('community_badges')->insert([
            [
                'name' => 'Miembro histórico',
                'slug' => 'miembro-historico',
                'description' => 'Formó parte del Mundo Yuri original.',
                'icon' => '✦',
                'color' => '#f43f8e',
                'is_active' => true,
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('community_badge_user');
        Schema::dropIfExists('community_badges');
        Schema::dropIfExists('community_ranks');
    }
};
