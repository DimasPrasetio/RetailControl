<?php

namespace Tests\Feature\Module03;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class T0301BranchCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $adminCabang;
    private Branch $jakarta;
    private Branch $bandung;
    private int $tenantId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $this->tenantId = Tenant::query()->value('id');
        $this->jakarta = Branch::query()->firstOrFail();
        $this->bandung = Branch::create([
            'tenant_id' => $this->tenantId,
            'branch_code' => 'CAB2',
            'name' => 'Cabang Kedua',
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ]);

        $adminRoleId = Role::where('name', 'admin')->value('id');
        $this->adminCabang = User::create([
            'name' => 'Admin Branch Test',
            'username' => 'admin_branch_test',
            'email' => null,
            'password' => bcrypt('Password1'),
            'role_id' => $adminRoleId,
            'branch_id' => $this->jakarta->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function super_admin_can_create_branch_and_default_main_warehouse(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.branches.store'), [
                'tenant_id' => $this->tenantId,
                'branch_code' => 'SBY',
                'name' => 'Cabang Surabaya',
                'address' => 'Jl. Veteran No. 10',
                'phone' => '031-555000',
                'timezone' => 'Asia/Jakarta',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.branches.index'));

        $branch = Branch::where('branch_code', 'SBY')->firstOrFail();
        $warehouse = Warehouse::where('warehouse_code', 'SBY-MAIN')->firstOrFail();

        $this->assertSame($branch->id, $warehouse->branch_id);
        $this->assertSame('MAIN', $warehouse->type);
        $this->assertSame('Gudang Utama', $warehouse->name);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'create',
            'auditable_type' => Branch::class,
            'auditable_id' => $branch->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'create',
            'auditable_type' => Warehouse::class,
            'auditable_id' => $warehouse->id,
        ]);
    }

    /** @test */
    public function admin_only_sees_their_assigned_branch(): void
    {
        $response = $this->actingAs($this->adminCabang)
            ->get(route('admin.branches.index'));

        $response->assertOk();
        $response->assertSee($this->jakarta->name);
        $response->assertDontSee($this->bandung->name);
    }

    /** @test */
    public function admin_cannot_create_branch(): void
    {
        $this->actingAs($this->adminCabang)
            ->post(route('admin.branches.store'), [
                'branch_code' => 'MLG',
                'name' => 'Cabang Malang',
                'is_active' => 1,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('branches', [
            'branch_code' => 'MLG',
        ]);
    }

    /** @test */
    public function branch_creation_is_visible_in_audit_log_filters(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.branches.store'), [
                'tenant_id' => $this->tenantId,
                'branch_code' => 'DPK',
                'name' => 'Cabang Depok',
                'timezone' => 'Asia/Jakarta',
                'is_active' => 1,
            ]);

        $branch = Branch::where('branch_code', 'DPK')->firstOrFail();

        $this->assertTrue(
            AuditLog::where('auditable_type', Branch::class)
                ->where('auditable_id', $branch->id)
                ->exists()
        );
    }

    /** @test */
    public function branch_tenant_cannot_be_changed_after_creation(): void
    {
        $newTenant = Tenant::create([
            'code' => 'OTHER',
            'name' => 'Other Tenant',
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin)
            ->put(route('admin.branches.update', $this->jakarta), [
                'tenant_id' => $newTenant->id,
                'name' => 'Cabang Jakarta Update',
                'address' => $this->jakarta->address,
                'phone' => $this->jakarta->phone,
                'timezone' => $this->jakarta->timezone,
                'notes' => $this->jakarta->notes,
            ])
            ->assertSessionHasErrors('tenant_id');

        $this->assertDatabaseHas('branches', [
            'id' => $this->jakarta->id,
            'tenant_id' => $this->tenantId,
        ]);
    }
}
