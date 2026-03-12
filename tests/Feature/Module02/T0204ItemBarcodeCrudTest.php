<?php

namespace Tests\Feature\Module02;

use App\Models\Item;
use App\Models\ItemBarcode;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\Uom;
use App\Models\User;
use App\Models\Branch;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * T02-4 — Item Barcode CRUD (PRD §11.3)
 *
 * Given: Seeded DB, item produk tersedia
 * Tests:
 *   - Admin dapat menambah barcode ke item
 *   - Barcode pertama otomatis menjadi primary
 *   - Barcode harus unique per tenant
 *   - Admin dapat set barcode sebagai primary
 *   - Admin dapat hapus barcode, primary baru otomatis dipilih
 *   - Kasir tidak bisa kelola barcode
 */
class T0204ItemBarcodeCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $admin;
    private User $kasir;
    private Item $item;
    private int $branchId;
    private int $tenantId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $this->branchId = Branch::query()->value('id');
        $this->tenantId = Tenant::query()->value('id');

        $uomPcs = Uom::where('code', 'PCS')->firstOrFail();

        $adminRoleId = Role::where('name', 'admin')->value('id');
        $this->admin = User::create([
            'name' => 'Admin Test',
            'username' => 'admin_barcode_test',
            'password' => bcrypt('Password1'),
            'role_id' => $adminRoleId,
            'branch_id' => $this->branchId,
            'is_active' => true,
        ]);

        $kasirRoleId = Role::where('name', 'kasir')->value('id');
        $this->kasir = User::create([
            'name' => 'Kasir Test',
            'username' => 'kasir_barcode_test',
            'password' => bcrypt('Password1'),
            'role_id' => $kasirRoleId,
            'branch_id' => $this->branchId,
            'is_active' => true,
        ]);

        $this->item = Item::create([
            'tenant_id' => $this->tenantId,
            'sku_code' => 'BAR-001',
            'name' => 'Produk Barcode Test',
            'base_uom_id' => $uomPcs->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function admin_can_add_barcode_to_item(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.items.barcodes.store', $this->item), [
                'barcode' => 'BC-TEST-12345',
            ])
            ->assertRedirect(route('admin.items.barcodes.index', $this->item));

        $this->assertDatabaseHas('item_barcodes', [
            'item_id' => $this->item->id,
            'barcode' => 'BC-TEST-12345',
        ]);
    }

    /** @test */
    public function first_barcode_is_automatically_set_as_primary(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.items.barcodes.store', $this->item), [
                'barcode' => 'BC-FIRST-001',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('item_barcodes', [
            'item_id' => $this->item->id,
            'barcode' => 'BC-FIRST-001',
            'is_primary' => true,
        ]);
    }

    /** @test */
    public function duplicate_barcode_within_tenant_is_rejected(): void
    {
        // Create first barcode
        ItemBarcode::create([
            'tenant_id' => $this->tenantId,
            'item_id' => $this->item->id,
            'barcode' => 'BC-DUPLI-001',
            'is_primary' => true,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.items.barcodes.store', $this->item), [
                'barcode' => 'BC-DUPLI-001', // duplicate
            ])
            ->assertSessionHasErrors('barcode');

        $this->assertDatabaseCount('item_barcodes', 1);
    }

    /** @test */
    public function admin_can_set_barcode_as_primary(): void
    {
        $primary = ItemBarcode::create([
            'tenant_id' => $this->tenantId,
            'item_id' => $this->item->id,
            'barcode' => 'BC-P-001',
            'is_primary' => true,
        ]);

        $secondary = ItemBarcode::create([
            'tenant_id' => $this->tenantId,
            'item_id' => $this->item->id,
            'barcode' => 'BC-S-001',
            'is_primary' => false,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.items.barcodes.set-primary', [$this->item, $secondary]))
            ->assertRedirect();

        $this->assertDatabaseHas('item_barcodes', ['id' => $primary->id, 'is_primary' => false]);
        $this->assertDatabaseHas('item_barcodes', ['id' => $secondary->id, 'is_primary' => true]);
    }

    /** @test */
    public function admin_can_delete_barcode(): void
    {
        $barcode = ItemBarcode::create([
            'tenant_id' => $this->tenantId,
            'item_id' => $this->item->id,
            'barcode' => 'BC-DEL-001',
            'is_primary' => true,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.items.barcodes.destroy', [$this->item, $barcode]))
            ->assertRedirect();

        $this->assertDatabaseMissing('item_barcodes', ['id' => $barcode->id]);
    }

    /** @test */
    public function deleting_primary_barcode_promotes_next_barcode_as_primary(): void
    {
        $primary = ItemBarcode::create([
            'tenant_id' => $this->tenantId,
            'item_id' => $this->item->id,
            'barcode' => 'BC-PRI-001',
            'is_primary' => true,
        ]);

        $secondary = ItemBarcode::create([
            'tenant_id' => $this->tenantId,
            'item_id' => $this->item->id,
            'barcode' => 'BC-SEC-001',
            'is_primary' => false,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.items.barcodes.destroy', [$this->item, $primary]))
            ->assertRedirect();

        $this->assertDatabaseHas('item_barcodes', [
            'id' => $secondary->id,
            'is_primary' => true,
        ]);
    }

    /** @test */
    public function kasir_cannot_add_barcode(): void
    {
        $this->actingAs($this->kasir)
            ->post(route('admin.items.barcodes.store', $this->item), [
                'barcode' => 'BC-KASIR-001',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('item_barcodes', ['barcode' => 'BC-KASIR-001']);
    }

    /** @test */
    public function kasir_cannot_delete_barcode(): void
    {
        $barcode = ItemBarcode::create([
            'tenant_id' => $this->tenantId,
            'item_id' => $this->item->id,
            'barcode' => 'BC-KASIR-DEL-001',
            'is_primary' => true,
        ]);

        $this->actingAs($this->kasir)
            ->delete(route('admin.items.barcodes.destroy', [$this->item, $barcode]))
            ->assertForbidden();

        $this->assertDatabaseHas('item_barcodes', ['id' => $barcode->id]);
    }

    /** @test */
    public function super_admin_can_view_barcodes_index(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.items.barcodes.index', $this->item))
            ->assertOk();
    }
}
