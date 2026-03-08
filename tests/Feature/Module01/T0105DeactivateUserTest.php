<?php

namespace Tests\Feature\Module01;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * T01-5 — Deactivate user (pisah dari delete)
 *
 * Given: Super Admin is logged in
 * When:  PATCH /admin/users/{id}/deactivate
 * Then:  User is_active=false; audit_log entry recorded
 *
 * Also tests: permission guard, self-deactivate block, super_admin block.
 */
class T0105DeactivateUserTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $kasir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $kasirRoleId = Role::where('name', 'kasir')->value('id');
        $this->kasir = User::create([
            'name'      => 'Kasir Target',
            'username'  => 'kasir_target',
            'email'     => null, // email kini nullable
            'password'  => bcrypt('Password1'),
            'role_id'   => $kasirRoleId,
            'branch_id' => 1,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function super_admin_can_deactivate_a_user(): void
    {
        $this->actingAs($this->superAdmin)
            ->patch(route('admin.users.deactivate', $this->kasir))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id'        => $this->kasir->id,
            'is_active' => false,
        ]);

        // Audit log harus tercatat (Auditable trait fires 'update')
        $this->assertDatabaseHas('audit_logs', [
            'action'         => 'update',
            'auditable_type' => User::class,
            'auditable_id'   => $this->kasir->id,
        ]);
    }

    /** @test */
    public function super_admin_cannot_deactivate_themselves(): void
    {
        $this->actingAs($this->superAdmin)
            ->patch(route('admin.users.deactivate', $this->superAdmin))
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id'        => $this->superAdmin->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function user_without_permission_cannot_deactivate(): void
    {
        // Kasir tidak punya users.deactivate
        $this->actingAs($this->kasir)
            ->patch(route('admin.users.deactivate', $this->superAdmin))
            ->assertForbidden();
    }

    /** @test */
    public function admin_can_deactivate_non_superadmin(): void
    {
        $adminRoleId = Role::where('name', 'admin')->value('id');
        $admin = User::create([
            'name'      => 'Admin',
            'username'  => 'admin_test',
            'email'     => null,
            'password'  => bcrypt('Password1'),
            'role_id'   => $adminRoleId,
            'branch_id' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.deactivate', $this->kasir))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id'        => $this->kasir->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function admin_cannot_deactivate_super_admin(): void
    {
        $adminRoleId = Role::where('name', 'admin')->value('id');
        $admin = User::create([
            'name'      => 'Admin',
            'username'  => 'admin_test_2',
            'email'     => null,
            'password'  => bcrypt('Password1'),
            'role_id'   => $adminRoleId,
            'branch_id' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.deactivate', $this->superAdmin))
            ->assertForbidden();
    }

    /** @test */
    public function user_can_be_created_without_email(): void
    {
        $kasirRoleId = Role::where('name', 'kasir')->value('id');

        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.store'), [
                'name'                  => 'Kasir Tanpa Email',
                'username'              => 'kasir_noemail',
                'email'                 => '', // boleh kosong
                'password'              => 'Password1',
                'password_confirmation' => 'Password1',
                'role_id'               => $kasirRoleId,
                'branch_id'             => 1,
                'is_active'             => 1,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'username' => 'kasir_noemail',
            'email'    => null,
        ]);
    }
}
