<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AdminBadgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_initial_badges_exist_without_automatic_assignments(): void
    {
        $this->assertDatabaseHas('badges', ['slug' => 'miembro-historico', 'type' => 'legacy']);
        $this->assertDatabaseHas('badges', ['slug' => 'pionera-2007', 'type' => 'legacy']);
        $this->assertDatabaseHas('badges', ['slug' => 'fundadora', 'type' => 'special']);
        $this->assertDatabaseHas('badges', ['slug' => 'staff', 'type' => 'staff']);
        $this->assertDatabaseHas('badges', ['slug' => 'moderacion', 'type' => 'staff']);
        $this->assertDatabaseCount('badge_user', 0);
    }

    public function test_admin_can_create_update_and_delete_a_badge(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.badges.store'), [
            'name' => 'Gran colaboradora',
            'slug' => '',
            'description' => 'Reconoce una aportación destacada.',
            'icon' => '★',
            'type' => 'achievement',
            'priority' => 25,
            'color' => '#8b5cf6',
            'is_active' => true,
        ])->assertRedirect(route('admin.badges.index'));

        $badge = Badge::query()->where('slug', 'gran-colaboradora')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.badges.update', $badge), [
            'name' => 'Colaboradora ejemplar',
            'slug' => 'colaboradora-ejemplar',
            'description' => $badge->description,
            'icon' => '★',
            'type' => 'special',
            'priority' => 40,
            'color' => '#8b5cf6',
            'is_active' => false,
        ])->assertRedirect(route('admin.badges.index'));

        $this->assertDatabaseHas('badges', [
            'id' => $badge->id,
            'slug' => 'colaboradora-ejemplar',
            'type' => 'special',
            'priority' => 40,
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.badges.destroy', $badge))
            ->assertRedirect(route('admin.badges.index'));

        $this->assertDatabaseMissing('badges', ['id' => $badge->id]);
    }

    public function test_admin_can_award_and_revoke_with_audit_data(): void
    {
        $admin = User::factory()->create(['name' => 'Administradora', 'role' => 'admin']);
        $member = User::factory()->create();
        $badge = Badge::query()->where('slug', 'pionera-2007')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.users.badges.store', $member), [
            'badge_id' => $badge->id,
            'note' => 'Consta en el respaldo del foro de 2007.',
        ])->assertRedirect();

        $award = $member->badges()->whereKey($badge->id)->firstOrFail()->pivot;

        $this->assertSame($admin->id, $award->awarded_by);
        $this->assertNotNull($award->awarded_at);
        $this->assertSame('Consta en el respaldo del foro de 2007.', $award->note);

        $this->actingAs($admin)
            ->get(route('admin.users.show', $member))
            ->assertOk()
            ->assertSee('Administradora')
            ->assertSee('Consta en el respaldo del foro de 2007.');

        $this->actingAs($admin)
            ->delete(route('admin.users.badges.destroy', [$member, $badge]))
            ->assertRedirect();

        $this->assertDatabaseMissing('badge_user', [
            'user_id' => $member->id,
            'badge_id' => $badge->id,
        ]);
    }

    public function test_duplicate_award_keeps_original_audit_record(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();
        $badge = Badge::query()->where('slug', 'miembro-historico')->firstOrFail();

        $member->badges()->attach($badge, [
            'awarded_by' => $admin->id,
            'awarded_at' => now()->subYear(),
            'note' => 'Concesión original.',
        ]);

        $this->actingAs($otherAdmin)->post(route('admin.users.badges.store', $member), [
            'badge_id' => $badge->id,
            'note' => 'Intento posterior.',
        ])->assertRedirect()->assertSessionHas('info');

        $this->assertDatabaseHas('badge_user', [
            'user_id' => $member->id,
            'badge_id' => $badge->id,
            'awarded_by' => $admin->id,
            'note' => 'Concesión original.',
        ]);
        $this->assertDatabaseCount('badge_user', 1);
    }

    public function test_regular_users_cannot_manage_or_assign_badges(): void
    {
        $regular = User::factory()->create(['role' => 'user']);
        $member = User::factory()->create();
        $badge = Badge::query()->where('slug', 'staff')->firstOrFail();

        $this->assertFalse(Gate::forUser($regular)->allows('manage', Badge::class));
        $this->assertFalse(Gate::forUser($regular)->allows('assign', $badge));
        $this->assertFalse(Gate::forUser($regular)->allows('revoke', $badge));

        $this->actingAs($regular)->get(route('admin.badges.index'))->assertRedirect('/');
        $this->actingAs($regular)->post(route('admin.users.badges.store', $member), [
            'badge_id' => $badge->id,
        ])->assertRedirect('/');

        $this->assertDatabaseCount('badge_user', 0);
    }

    public function test_inactive_badge_cannot_be_awarded(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create();
        $badge = Badge::query()->where('slug', 'staff')->firstOrFail();
        $badge->update(['is_active' => false]);

        $this->actingAs($admin)->post(route('admin.users.badges.store', $member), [
            'badge_id' => $badge->id,
        ])->assertSessionHasErrors('badge_id');

        $this->assertDatabaseCount('badge_user', 0);
    }
}
