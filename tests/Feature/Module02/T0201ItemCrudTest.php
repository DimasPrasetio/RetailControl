<?php

namespace Tests\Feature\Module02;

use App\Models\AuditLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\Role;
use App\Models\Uom;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * T02-1 — Item (SKU) CRUD & permission guard
 *
 * Given: seeded DB with roles, UoMs, brands, categories
 * Tests:
 *   - super_admin can create/update/deactivate items
 *   - creation is recorded in audit_logs
 *   - deactivation flips is_active + records audit
 *   - kasir can view list but cannot create/update/deactivate
 *   - admin can create/update/deactivate items
 *   - sku_code must be unique (duplicate rejected)
 *   - base_uom_id is required
 */
class T0201ItemCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $kasir;
    private User $adminCabang;
    private Uom  $uomPcs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $this->uomPcs     = Uom::where('code', 'PCS')->firstOrFail();

        $kasirRoleId = Role::where('name', 'kasir')->value('id');
        $this->kasir = User::create([
            'name'      => 'Kasir Test',
            'username'  => 'kasir_item_test',
            'email'     => null,
            'password'  => bcrypt('Password1'),
            'role_id'   => $kasirRoleId,
            'branch_id' => 1,
            'is_active' => true,
        ]);

        $adminRoleId = Role::where('name', 'admin')->value('id');
        $this->adminCabang = User::create([
            'name'      => 'Admin Test',
            'username'  => 'admin_item_test',
            'email'     => null,
            'password'  => bcrypt('Password1'),
            'role_id'   => $adminRoleId,
            'branch_id' => 1,
            'is_active' => true,
        ]);
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    /** @test */
    public function super_admin_can_create_an_item(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.items.store'), [
                'sku_code'    => 'TEST-001',
                'name'        => 'Produk Test Satu',
                'base_uom_id' => $this->uomPcs->id,
                'is_active'   => 1,
            ])
            ->assertRedirect(route('admin.items.index'));

        $this->assertDatabaseHas('items', [
            'sku_code' => 'TEST-001',
            'name'     => 'Produk Test Satu',
        ]);

        $item = Item::where('sku_code', 'TEST-001')->firstOrFail();

        // Audit log: action=create
        $this->assertDatabaseHas('audit_logs', [
            'action'         => 'create',
            'auditable_type' => Item::class,
            'auditable_id'   => $item->id,
        ]);
    }

    /** @test */
    public function item_requires_base_uom_id(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.items.store'), [
                'sku_code' => 'TEST-NO-UOM',
                'name'     => 'Produk Tanpa UoM',
                // base_uom_id missing
            ])
            ->assertSessionHasErrors('base_uom_id');

        $this->assertDatabaseMissing('items', ['sku_code' => 'TEST-NO-UOM']);
    }

    /** @test */
    public function duplicate_sku_code_is_rejected(): void
    {
        // First item
        $this->actingAs($this->superAdmin)
            ->post(route('admin.items.store'), [
                'sku_code'    => 'DUPLI-001',
                'name'        => 'Produk Pertama',
                'base_uom_id' => $this->uomPcs->id,
                'is_active'   => 1,
            ]);

        // Second item with same sku_code
        $this->actingAs($this->superAdmin)
            ->post(route('admin.items.store'), [
                'sku_code'    => 'DUPLI-001',
                'name'        => 'Produk Duplikat',
                'base_uom_id' => $this->uomPcs->id,
                'is_active'   => 1,
            ])
            ->assertSessionHasErrors('sku_code');
    }

    /** @test */
    public function kasir_cannot_create_an_item(): void
    {
        $this->actingAs($this->kasir)
            ->post(route('admin.items.store'), [
                'sku_code'    => 'KASIR-001',
                'name'        => 'Produk Kasir',
                'base_uom_id' => $this->uomPcs->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('items', ['sku_code' => 'KASIR-001']);
    }

    /** @test */
    public function kasir_can_view_items_list(): void
    {
        $this->actingAs($this->kasir)
            ->get(route('admin.items.index'))
            ->assertOk();
    }

    /** @test */
    public function admin_can_create_an_item(): void
    {
        $this->actingAs($this->adminCabang)
            ->post(route('admin.items.store'), [
                'sku_code'    => 'ADM-001',
                'name'        => 'Produk Admin',
                'base_uom_id' => $this->uomPcs->id,
                'is_active'   => 1,
            ])
            ->assertRedirect(route('admin.items.index'));

        $this->assertDatabaseHas('items', ['sku_code' => 'ADM-001']);
    }

    // ─── Update ───────────────────────────────────────────────────────────────

    /** @test */
    public function super_admin_can_update_an_item(): void
    {
        $item = Item::create([
            'sku_code'    => 'UPD-001',
            'name'        => 'Nama Lama',
            'base_uom_id' => $this->uomPcs->id,
            'is_active'   => true,
        ]);

        $this->actingAs($this->superAdmin)
            ->put(route('admin.items.update', $item), [
                'sku_code'    => 'UPD-001',
                'name'        => 'Nama Baru',
                'base_uom_id' => $this->uomPcs->id,
                'is_active'   => 1,
            ])
            ->assertRedirect(route('admin.items.index'));

        $this->assertDatabaseHas('items', [
            'id'   => $item->id,
            'name' => 'Nama Baru',
        ]);

        // Audit log: action=update
        $this->assertDatabaseHas('audit_logs', [
            'action'         => 'update',
            'auditable_type' => Item::class,
            'auditable_id'   => $item->id,
        ]);
    }

    /** @test */
    public function kasir_cannot_update_an_item(): void
    {
        $item = Item::create([
            'sku_code'    => 'RO-001',
            'name'        => 'Produk Readonly',
            'base_uom_id' => $this->uomPcs->id,
            'is_active'   => true,
        ]);

        $this->actingAs($this->kasir)
            ->put(route('admin.items.update', $item), [
                'sku_code'    => 'RO-001',
                'name'        => 'Percobaan Edit',
                'base_uom_id' => $this->uomPcs->id,
            ])
            ->assertForbidden();
    }

    // ─── Deactivate ───────────────────────────────────────────────────────────

    /** @test */
    public function super_admin_can_deactivate_an_item(): void
    {
        $item = Item::create([
            'sku_code'    => 'DEACT-001',
            'name'        => 'Produk Aktif',
            'base_uom_id' => $this->uomPcs->id,
            'is_active'   => true,
        ]);

        $this->actingAs($this->superAdmin)
            ->patch(route('admin.items.deactivate', $item))
            ->assertRedirect(route('admin.items.index'));

        $this->assertDatabaseHas('items', [
            'id'        => $item->id,
            'is_active' => false,
        ]);

        // Audit log tercatat
        $this->assertDatabaseHas('audit_logs', [
            'action'         => 'update',
            'auditable_type' => Item::class,
            'auditable_id'   => $item->id,
        ]);
    }

    /** @test */
    public function kasir_cannot_deactivate_an_item(): void
    {
        $item = Item::create([
            'sku_code'    => 'DEACT-002',
            'name'        => 'Produk Aktif 2',
            'base_uom_id' => $this->uomPcs->id,
            'is_active'   => true,
        ]);

        $this->actingAs($this->kasir)
            ->patch(route('admin.items.deactivate', $item))
            ->assertForbidden();

        $this->assertDatabaseHas('items', [
            'id'        => $item->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function admin_can_deactivate_an_item(): void
    {
        $item = Item::create([
            'sku_code'    => 'DEACT-003',
            'name'        => 'Produk Cabang',
            'base_uom_id' => $this->uomPcs->id,
            'is_active'   => true,
        ]);

        $this->actingAs($this->adminCabang)
            ->patch(route('admin.items.deactivate', $item))
            ->assertRedirect(route('admin.items.index'));

        $this->assertDatabaseHas('items', [
            'id'        => $item->id,
            'is_active' => false,
        ]);
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    /** @test */
    public function super_admin_can_view_item_detail(): void
    {
        $item = Item::create([
            'sku_code'    => 'SHOW-001',
            'name'        => 'Produk Detail',
            'base_uom_id' => $this->uomPcs->id,
            'is_active'   => true,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('admin.items.show', $item))
            ->assertOk();
    }

    // ─── Filter / Index ───────────────────────────────────────────────────────

    /** @test */
    public function items_index_filters_by_active_status(): void
    {
        Item::create(['sku_code' => 'ACT-001',   'name' => 'Produk Masih Aktif',     'base_uom_id' => $this->uomPcs->id, 'is_active' => true]);
        Item::create(['sku_code' => 'INACT-001', 'name' => 'Produk Sudah Dimatikan', 'base_uom_id' => $this->uomPcs->id, 'is_active' => false]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.items.index', ['active' => '1']));

        $response->assertOk();
        $response->assertSee('Produk Masih Aktif');
        $response->assertDontSee('Produk Sudah Dimatikan');
    }
}
