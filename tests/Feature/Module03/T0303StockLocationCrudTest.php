<?php

namespace Tests\Feature\Module03;

use App\Models\Branch;
use App\Models\Role;
use App\Models\StockLocation;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class T0303StockLocationCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $adminCabang;
    private User $kasir;
    private Branch $jakarta;
    private Branch $bandung;
    private Warehouse $jakartaWarehouse;

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
        $this->jakartaWarehouse = Warehouse::where('warehouse_code', "{$this->jakarta->branch_code}-MAIN")->firstOrFail();
        Warehouse::create([
            'tenant_id' => $this->bandung->tenant_id,
            'branch_id' => $this->bandung->id,
            'warehouse_code' => "{$this->bandung->branch_code}-MAIN",
            'name' => 'Gudang Cabang Kedua',
            'type' => 'MAIN',
            'is_active' => true,
        ]);

        $adminRoleId = Role::where('name', 'admin')->value('id');
        $kasirRoleId = Role::where('name', 'kasir')->value('id');

        $this->adminCabang = User::create([
            'name' => 'Admin Location Test',
            'username' => 'admin_location_test',
            'email' => null,
            'password' => bcrypt('Password1'),
            'role_id' => $adminRoleId,
            'branch_id' => $this->jakarta->id,
            'is_active' => true,
        ]);

        $this->kasir = User::create([
            'name' => 'Kasir Location Test',
            'username' => 'kasir_location_test',
            'email' => null,
            'password' => bcrypt('Password1'),
            'role_id' => $kasirRoleId,
            'branch_id' => $this->jakarta->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function admin_can_create_stock_location_for_their_branch_warehouse(): void
    {
        $this->actingAs($this->adminCabang)
            ->post(route('admin.stock-locations.store'), [
                'branch_id' => $this->jakarta->id,
                'warehouse_id' => $this->jakartaWarehouse->id,
                'code' => "{$this->jakarta->branch_code}-MAIN-A1",
                'name' => 'Area Rak A1',
                'type' => 'AREA',
            ])
            ->assertRedirect(route('admin.stock-locations.index', [
                'branch_id' => $this->jakarta->id,
                'warehouse_id' => $this->jakartaWarehouse->id,
            ]));

        $this->assertDatabaseHas('stock_locations', [
            'branch_id' => $this->jakarta->id,
            'warehouse_id' => $this->jakartaWarehouse->id,
            'code' => "{$this->jakarta->branch_code}-MAIN-A1",
            'type' => 'AREA',
        ]);
    }

    /** @test */
    public function admin_cannot_create_stock_location_for_other_branch(): void
    {
        $bandungWarehouse = Warehouse::where('warehouse_code', "{$this->bandung->branch_code}-MAIN")->firstOrFail();

        $this->actingAs($this->adminCabang)
            ->post(route('admin.stock-locations.store'), [
                'branch_id' => $this->bandung->id,
                'warehouse_id' => $bandungWarehouse->id,
                'code' => "{$this->bandung->branch_code}-MAIN-A1",
                'name' => 'Area Bandung',
                'type' => 'AREA',
            ])
            ->assertForbidden();
    }

    /** @test */
    public function system_location_cannot_be_deactivated(): void
    {
        $rootLocation = StockLocation::where('code', "{$this->jakarta->branch_code}-MAIN-ROOT")->firstOrFail();

        $this->actingAs($this->adminCabang)
            ->patch(route('admin.stock-locations.deactivate', $rootLocation))
            ->assertRedirect(route('admin.stock-locations.index'));

        $this->assertDatabaseHas('stock_locations', [
            'id' => $rootLocation->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function admin_can_deactivate_custom_stock_location(): void
    {
        $location = StockLocation::create([
            'tenant_id' => $this->jakarta->tenant_id,
            'branch_id' => $this->jakarta->id,
            'warehouse_id' => $this->jakartaWarehouse->id,
            'code' => "{$this->jakarta->branch_code}-MAIN-B1",
            'name' => 'Area Rak B1',
            'type' => 'AREA',
            'is_active' => true,
        ]);

        $this->actingAs($this->adminCabang)
            ->patch(route('admin.stock-locations.deactivate', $location))
            ->assertRedirect(route('admin.stock-locations.index'));

        $this->assertDatabaseHas('stock_locations', [
            'id' => $location->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function kasir_can_view_stock_locations_but_cannot_create(): void
    {
        $this->actingAs($this->kasir)
            ->get(route('admin.stock-locations.index'))
            ->assertOk();

        $this->actingAs($this->kasir)
            ->post(route('admin.stock-locations.store'), [
                'branch_id' => $this->jakarta->id,
                'warehouse_id' => $this->jakartaWarehouse->id,
                'code' => "{$this->jakarta->branch_code}-MAIN-C1",
                'name' => 'Area Rak C1',
                'type' => 'AREA',
            ])
            ->assertForbidden();
    }
}
