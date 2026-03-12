<?php

namespace Tests\Feature\Module03;

use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class T0302WarehouseCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $adminCabang;
    private User $kasir;
    private Branch $jakarta;
    private Branch $bandung;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $this->jakarta = Branch::query()->firstOrFail();
        $this->bandung = Branch::create([
            'tenant_id' => $this->jakarta->tenant_id,
            'branch_code' => 'CAB2',
            'name' => 'Cabang Kedua',
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ]);

        $adminRoleId = Role::where('name', 'admin')->value('id');
        $kasirRoleId = Role::where('name', 'kasir')->value('id');

        $this->adminCabang = User::create([
            'name' => 'Admin Warehouse Test',
            'username' => 'admin_warehouse_test',
            'email' => null,
            'password' => bcrypt('Password1'),
            'role_id' => $adminRoleId,
            'branch_id' => $this->jakarta->id,
            'is_active' => true,
        ]);

        $this->kasir = User::create([
            'name' => 'Kasir Warehouse Test',
            'username' => 'kasir_warehouse_test',
            'email' => null,
            'password' => bcrypt('Password1'),
            'role_id' => $kasirRoleId,
            'branch_id' => $this->jakarta->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function admin_can_create_warehouse_for_their_branch(): void
    {
        $this->actingAs($this->adminCabang)
            ->post(route('admin.warehouses.store'), [
                'branch_id' => $this->jakarta->id,
                'warehouse_code' => "{$this->jakarta->branch_code}-SEC-01",
                'name' => 'Gudang Sekunder Jakarta',
                'type' => 'SECONDARY',
                'notes' => 'Overflow stock',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.warehouses.index'));

        $this->assertDatabaseHas('warehouses', [
            'warehouse_code' => "{$this->jakarta->branch_code}-SEC-01",
            'branch_id' => $this->jakarta->id,
            'type' => 'SECONDARY',
        ]);
    }

    /** @test */
    public function admin_cannot_create_warehouse_for_other_branch(): void
    {
        $this->actingAs($this->adminCabang)
            ->post(route('admin.warehouses.store'), [
                'branch_id' => $this->bandung->id,
                'warehouse_code' => "{$this->bandung->branch_code}-SEC-01",
                'name' => 'Gudang Sekunder Bandung',
                'type' => 'SECONDARY',
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('branch_id');

        $this->assertDatabaseMissing('warehouses', [
            'warehouse_code' => "{$this->bandung->branch_code}-SEC-01",
        ]);
    }

    /** @test */
    public function kasir_can_view_warehouse_index_but_cannot_create(): void
    {
        $this->actingAs($this->kasir)
            ->get(route('admin.warehouses.index'))
            ->assertOk();

        $this->actingAs($this->kasir)
            ->post(route('admin.warehouses.store'), [
                'branch_id' => $this->jakarta->id,
                'warehouse_code' => "{$this->jakarta->branch_code}-SEC-99",
                'name' => 'Gudang Kasir',
                'type' => 'SECONDARY',
            ])
            ->assertForbidden();
    }

    /** @test */
    public function main_warehouse_cannot_be_deactivated(): void
    {
        $mainWarehouse = Warehouse::where('warehouse_code', "{$this->jakarta->branch_code}-MAIN")->firstOrFail();

        $this->actingAs($this->adminCabang)
            ->patch(route('admin.warehouses.deactivate', $mainWarehouse))
            ->assertRedirect(route('admin.warehouses.index'));

        $this->assertDatabaseHas('warehouses', [
            'id' => $mainWarehouse->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function super_admin_can_create_warehouse_for_any_branch(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.warehouses.store'), [
                'branch_id' => $this->bandung->id,
                'warehouse_code' => "{$this->bandung->branch_code}-RET-01",
                'name' => 'Gudang Retur Bandung',
                'type' => 'RETURNS',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.warehouses.index'));

        $this->assertDatabaseHas('warehouses', [
            'warehouse_code' => "{$this->bandung->branch_code}-RET-01",
            'branch_id' => $this->bandung->id,
        ]);
    }
}
