<?php

namespace Tests\Feature\Module01;

use App\Models\Role;
use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * T01-2 — Branch scoping enforced
 * T01-3 — Owner sees all branches
 *
 * T01-2:
 *   Given: Kasir is assigned to Branch A only
 *   When:  Kasir tries to access a resource belonging to Branch B
 *   Then:  Access is denied (canAccessBranch returns false)
 *
 * T01-3:
 *   Given: User has role `owner`
 *   When:  Owner requests audit logs
 *   Then:  Returns 200 OK (not 403)
 */
class T0102BranchScopingTest extends TestCase
{
    use RefreshDatabase;

    private int $jakartaBranchId;
    private int $bandungBranchId;
    private int $tenantId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->tenantId = Tenant::query()->value('id');
        $this->jakartaBranchId = Branch::query()->value('id');
        $this->bandungBranchId = Branch::create([
            'tenant_id' => $this->tenantId,
            'branch_code' => 'CAB2',
            'name' => 'Cabang Kedua',
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ])->id;
    }

    // ─── T01-2 ────────────────────────────────────────────────────────────────

    /** @test */
    public function kasir_can_only_access_their_own_branch(): void
    {
        $kasirRoleId = Role::where('name', 'kasir')->value('id');

        $kasir = User::create([
            'name' => 'Kasir Cabang A',
            'username' => 'kasir_a',
            'email' => 'kasir_a@test.com',
            'password' => bcrypt('Password1'),
            'role_id' => $kasirRoleId,
            'branch_id' => $this->jakartaBranchId,
            'is_active' => true,
        ]);

        $this->assertTrue($kasir->canAccessBranch($this->jakartaBranchId), 'Kasir harus bisa akses Branch A (sendiri)');
        $this->assertFalse($kasir->canAccessBranch($this->bandungBranchId), 'Kasir tidak boleh akses Branch B (cabang lain)');
    }

    /** @test */
    public function kasir_accessible_branch_id_returns_their_branch(): void
    {
        $kasirRoleId = Role::where('name', 'kasir')->value('id');

        $kasir = User::create([
            'name' => 'Kasir Query Scope',
            'username' => 'kasir_scope',
            'email' => 'kasir_scope@test.com',
            'password' => bcrypt('Password1'),
            'role_id' => $kasirRoleId,
            'branch_id' => $this->bandungBranchId,
            'is_active' => true,
        ]);

        $this->assertSame($this->bandungBranchId, $kasir->getAccessibleBranchId());
    }

    /** @test */
    public function kasir_cannot_access_audit_logs(): void
    {
        $kasirRoleId = Role::where('name', 'kasir')->value('id');

        $kasir = User::create([
            'name' => 'Kasir No Log',
            'username' => 'kasir_nolog',
            'email' => 'kasir_nolog@test.com',
            'password' => bcrypt('Password1'),
            'role_id' => $kasirRoleId,
            'branch_id' => $this->jakartaBranchId,
            'is_active' => true,
        ]);

        $this->actingAs($kasir)
            ->get(route('admin.audit-logs.index'))
            ->assertForbidden();
    }

    // ─── T01-3 ────────────────────────────────────────────────────────────────

    /** @test */
    public function owner_has_global_branch_access(): void
    {
        $ownerRoleId = Role::where('name', 'owner')->value('id');

        $owner = User::create([
            'name' => 'Owner Global',
            'username' => 'owner_global',
            'email' => 'owner@test.com',
            'password' => bcrypt('Password1'),
            'role_id' => $ownerRoleId,
            'tenant_id' => $this->tenantId,
            'branch_id' => null,
            'is_active' => true,
        ]);

        $this->assertTrue($owner->isGlobal(), 'Owner harus isGlobal = true');
        $this->assertNull($owner->getAccessibleBranchId(), 'Owner tidak difilter ke cabang tertentu');
        $this->assertTrue($owner->canAccessBranch($this->jakartaBranchId));
        $this->assertFalse($owner->canAccessBranch(99), 'Owner tidak boleh lolos ke branch yang tidak ada / di luar tenant.');
    }

    /** @test */
    public function owner_can_access_audit_logs(): void
    {
        $ownerRoleId = Role::where('name', 'owner')->value('id');

        $owner = User::create([
            'name' => 'Owner Audit',
            'username' => 'owner_audit',
            'email' => 'owner_audit@test.com',
            'password' => bcrypt('Password1'),
            'role_id' => $ownerRoleId,
            'tenant_id' => $this->tenantId,
            'branch_id' => null,
            'is_active' => true,
        ]);

        $this->actingAs($owner)
            ->get(route('admin.audit-logs.index'))
            ->assertOk();
    }

    /** @test */
    public function super_admin_has_global_branch_access(): void
    {
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->assertTrue($superAdmin->isGlobal());
        $this->assertNull($superAdmin->getAccessibleBranchId());
        $this->assertTrue($superAdmin->canAccessBranch($this->jakartaBranchId));
        $this->assertTrue($superAdmin->canAccessBranch(999));
    }
}
