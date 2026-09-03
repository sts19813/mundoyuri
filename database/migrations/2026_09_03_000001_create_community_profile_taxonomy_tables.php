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

        Schema::create('badges', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('type', 30);
            $table->unsignedSmallInteger('priority')->default(0);
            $table->string('color', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'type', 'priority'], 'badges_listing_index');
        });

        Schema::create('badge_user', function (Blueprint $table): void {
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('awarded_at');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->primary(['badge_id', 'user_id']);
            $table->index(['user_id', 'awarded_at'], 'badge_user_awarded_index');
        });

        $now = now();

        DB::table('community_ranks')->insert([
            ['name' => 'Nuevo miembro', 'slug' => 'nuevo-miembro', 'description' => 'Primeros pasos en la comunidad.', 'minimum_posts' => 0, 'priority' => 10, 'icon' => '✦', 'is_special' => false, 'is_legacy' => false, 'is_active' => true, 'color' => '#a08faa', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Kohai', 'slug' => 'kohai', 'description' => 'Miembro que comienza a participar.', 'minimum_posts' => 10, 'priority' => 20, 'icon' => '♡', 'is_special' => false, 'is_legacy' => false, 'is_active' => true, 'color' => '#c084fc', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Yuri Fan', 'slug' => 'yuri-fan', 'description' => 'Participante habitual de Mundo Yuri.', 'minimum_posts' => 50, 'priority' => 30, 'icon' => '❀', 'is_special' => false, 'is_legacy' => false, 'is_active' => true, 'color' => '#f472b6', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Yuri Senpai', 'slug' => 'yuri-senpai', 'description' => 'Miembro con una trayectoria destacada.', 'minimum_posts' => 200, 'priority' => 40, 'icon' => '✧', 'is_special' => false, 'is_legacy' => false, 'is_active' => true, 'color' => '#fb7185', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Onee-sama', 'slug' => 'onee-sama', 'description' => 'Rango superior de participación comunitaria.', 'minimum_posts' => 500, 'priority' => 50, 'icon' => '♛', 'is_special' => false, 'is_legacy' => false, 'is_active' => true, 'color' => '#f43f8e', 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('badges')->insert([
            ['name' => 'Miembro Histórico', 'slug' => 'miembro-historico', 'description' => 'Formó parte del Mundo Yuri original.', 'icon' => '✦', 'type' => 'legacy', 'priority' => 50, 'color' => '#f43f8e', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pionera 2007', 'slug' => 'pionera-2007', 'description' => 'Participó en la comunidad original desde 2007.', 'icon' => '✧', 'type' => 'legacy', 'priority' => 80, 'color' => '#c084fc', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Fundadora', 'slug' => 'fundadora', 'description' => 'Reconocimiento a quienes fundaron Mundo Yuri.', 'icon' => '♛', 'type' => 'special', 'priority' => 100, 'color' => '#f59e0b', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Staff', 'slug' => 'staff', 'description' => 'Reconocimiento visual al equipo de Mundo Yuri.', 'icon' => '◆', 'type' => 'staff', 'priority' => 90, 'color' => '#38bdf8', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Moderación', 'slug' => 'moderacion', 'description' => 'Reconocimiento visual al equipo de moderación.', 'icon' => '◇', 'type' => 'staff', 'priority' => 70, 'color' => '#34d399', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_user');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('community_ranks');
    }
};
