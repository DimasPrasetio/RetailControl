<?php

namespace Tests\Feature\Module01;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Enums\AuditActionEnum;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * T01-4 — Role constraint
 *
 *   Given: Admin (admin_cabang) is logged in
 *   When:  Admin tries to POST /admin/users (requires users.create permission)
 *   Then:  403 Forbidden
 *
 * T01-5 — Audit log immutability
 *
 *   Given: Any audit_log entry exists
 *   When:  Any code path attempts to UPDATE or DELETE audit_logs
 *   Then:  LogicException is thrown (model-level guard)
 */
class T0104RoleConstraintAndAuditImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // ─── T01-4 ────────────────────────────────────────────────────────────────

    /** @test */
    public function admin_cabang_cannot_create_users(): void
    {
        $adminRoleId = Role::where('name', 'admin_cabang')->value('id');
        $kasirRoleId = Role::where('name', 'kasir')->value('id');

        $admin = User::create([
            'name'      => 'Admin Cabang Test',
            'username'  => 'admin_test',
            'email'     => 'admin_test@test.com',
            'password'  => bcrypt('Password1'),
            'role_id'   => $adminRoleId,
            'branch_id' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name'                  => 'User Baru',
                'username'              => 'user_baru',
                'email'                 => 'user_baru@test.com',
                'password'              => 'Password1',
                'password_confirmation' => 'Password1',
                'role_id'               => $kasirRoleId,
                'branch_id'             => 1,
                'is_active'             => 1,
            ])
            ->assertForbidden();
    }

    /** @test */
    public function admin_cabang_cannot_list_users(): void
    {
        $adminRoleId = Role::where('name', 'admin_cabang')->value('id');

        $admin = User::create([
            'name'      => 'Admin Cabang View',
            'username'  => 'admin_view',
            'email'     => 'admin_view@test.com',
            'password'  => bcrypt('Password1'),
            'role_id'   => $adminRoleId,
            'branch_id' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    /** @test */
    public function kasir_cannot_access_user_management(): void
    {
        $kasirRoleId = Role::where('name', 'kasir')->value('id');

        $kasir = User::create([
            'name'      => 'Kasir No Access',
            'username'  => 'kasir_noaccess',
            'email'     => 'kasir_noaccess@test.com',
            'password'  => bcrypt('Password1'),
            'role_id'   => $kasirRoleId,
            'branch_id' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($kasir)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    // ─── T01-5 ────────────────────────────────────────────────────────────────

    /** @test */
    public function audit_log_cannot_be_updated(): void
    {
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        // Buat sebuah audit log entry (gunakan data valid — auditable_type tidak boleh null)
        $log = AuditLog::create([
            'user_id'        => $superAdmin->id,
            'action'         => AuditActionEnum::Login,
            'auditable_type' => User::class,
            'auditable_id'   => $superAdmin->id,
            'old_values'     => null,
            'new_values'     => null,
            'ip_address'     => '127.0.0.1',
            'user_agent'     => 'TestBrowser',
        ]);

        $this->assertNotNull($log->id);

        // Update harus diblokir oleh model guard
        $this->expectException(\LogicException::class);
        $log->update(['ip_address' => '1.2.3.4']);
    }

    /** @test */
    public function audit_log_cannot_be_deleted(): void
    {
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $log = AuditLog::create([
            'user_id'        => $superAdmin->id,
            'action'         => AuditActionEnum::Login,
            'auditable_type' => User::class,
            'auditable_id'   => $superAdmin->id,
            'old_values'     => null,
            'new_values'     => null,
            'ip_address'     => '127.0.0.1',
            'user_agent'     => 'TestBrowser',
        ]);

        $this->assertNotNull($log->id);

        // Delete harus diblokir oleh model guard
        $this->expectException(\LogicException::class);
        $log->delete();
    }

    /** @test */
    public function inactive_user_cannot_login(): void
    {
        $kasirRoleId = Role::where('name', 'kasir')->value('id');

        User::create([
            'name'      => 'Kasir Nonaktif',
            'username'  => 'kasir_inactive',
            'email'     => 'kasir_inactive@test.com',
            'password'  => bcrypt('Password1'),
            'role_id'   => $kasirRoleId,
            'branch_id' => 1,
            'is_active' => false, // nonaktif
        ]);

        $this->post(route('login.store'), [
            'login'    => 'kasir_inactive',
            'password' => 'Password1',
        ]);

        $this->assertGuest();
    }
}
