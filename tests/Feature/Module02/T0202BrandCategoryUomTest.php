<?php

namespace Tests\Feature\Module02;

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
 * T02-2 — Brand, Category, UoM CRUD & permission guard
 *
 * Given: seeded DB with roles/permissions
 * Tests:
 *   - super_admin can create/update/deactivate brands and categories
 *   - super_admin can create/update UoMs
 *   - kasir can view brands/categories/uoms but cannot create/update/deactivate
 *   - duplicate brand name is rejected
 *   - duplicate category code is rejected
 *   - duplicate uom code is rejected
 *   - category supports parent_id (sub-category)
 *   - deactivate records audit log
 */
class T0202BrandCategoryUomTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $kasir;
    private User $adminCabang;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $kasirRoleId = Role::where('name', 'kasir')->value('id');
        $this->kasir = User::create([
            'name'      => 'Kasir Brand Test',
            'username'  => 'kasir_brand_test',
            'email'     => null,
            'password'  => bcrypt('Password1'),
            'role_id'   => $kasirRoleId,
            'branch_id' => 1,
            'is_active' => true,
        ]);

        $adminRoleId = Role::where('name', 'admin_cabang')->value('id');
        $this->adminCabang = User::create([
            'name'      => 'Admin Brand Test',
            'username'  => 'admin_brand_test',
            'email'     => null,
            'password'  => bcrypt('Password1'),
            'role_id'   => $adminRoleId,
            'branch_id' => 1,
            'is_active' => true,
        ]);
    }

    // ─── Brand ────────────────────────────────────────────────────────────────

    /** @test */
    public function super_admin_can_create_a_brand(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.brands.store'), [
                'name'      => 'Merk Baru Test',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.brands.index'));

        $this->assertDatabaseHas('brands', ['name' => 'Merk Baru Test']);

        $brand = Brand::where('name', 'Merk Baru Test')->firstOrFail();
        $this->assertDatabaseHas('audit_logs', [
            'action'         => 'create',
            'auditable_type' => Brand::class,
            'auditable_id'   => $brand->id,
        ]);
    }

    /** @test */
    public function duplicate_brand_name_is_rejected(): void
    {
        Brand::create(['name' => 'Merk Duplikat', 'is_active' => true]);

        $this->actingAs($this->superAdmin)
            ->post(route('admin.brands.store'), [
                'name'      => 'Merk Duplikat',
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('name');
    }

    /** @test */
    public function kasir_cannot_create_a_brand(): void
    {
        $this->actingAs($this->kasir)
            ->post(route('admin.brands.store'), [
                'name'      => 'Merk Kasir',
                'is_active' => 1,
            ])
            ->assertForbidden();
    }

    /** @test */
    public function kasir_can_view_brands_list(): void
    {
        $this->actingAs($this->kasir)
            ->get(route('admin.brands.index'))
            ->assertOk();
    }

    /** @test */
    public function super_admin_can_update_a_brand(): void
    {
        $brand = Brand::create(['name' => 'Merk Lama', 'is_active' => true]);

        $this->actingAs($this->superAdmin)
            ->put(route('admin.brands.update', $brand), [
                'name'      => 'Merk Diperbarui',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.brands.index'));

        $this->assertDatabaseHas('brands', [
            'id'   => $brand->id,
            'name' => 'Merk Diperbarui',
        ]);
    }

    /** @test */
    public function super_admin_can_deactivate_a_brand(): void
    {
        $brand = Brand::create(['name' => 'Merk Aktif', 'is_active' => true]);

        $this->actingAs($this->superAdmin)
            ->patch(route('admin.brands.deactivate', $brand))
            ->assertRedirect(route('admin.brands.index'));

        $this->assertDatabaseHas('brands', [
            'id'        => $brand->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action'         => 'update',
            'auditable_type' => Brand::class,
            'auditable_id'   => $brand->id,
        ]);
    }

    /** @test */
    public function kasir_cannot_deactivate_a_brand(): void
    {
        $brand = Brand::create(['name' => 'Merk Aktif 2', 'is_active' => true]);

        $this->actingAs($this->kasir)
            ->patch(route('admin.brands.deactivate', $brand))
            ->assertForbidden();
    }

    // ─── Category ─────────────────────────────────────────────────────────────

    /** @test */
    public function super_admin_can_create_a_category(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.categories.store'), [
                'name'      => 'Kategori Test',
                'code'      => 'KAT_TEST',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Kategori Test',
            'code' => 'KAT_TEST',
        ]);
    }

    /** @test */
    public function duplicate_category_code_is_rejected(): void
    {
        Category::create(['name' => 'Kategori Satu', 'code' => 'DUPKAT', 'is_active' => true]);

        $this->actingAs($this->superAdmin)
            ->post(route('admin.categories.store'), [
                'name'      => 'Kategori Dua',
                'code'      => 'DUPKAT',
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('code');
    }

    /** @test */
    public function category_can_have_a_parent(): void
    {
        $parent = Category::create(['name' => 'Induk', 'code' => 'INDUK', 'is_active' => true]);

        $this->actingAs($this->superAdmin)
            ->post(route('admin.categories.store'), [
                'name'      => 'Sub Kategori',
                'code'      => 'SUB_KAT',
                'parent_id' => $parent->id,
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name'      => 'Sub Kategori',
            'parent_id' => $parent->id,
        ]);
    }

    /** @test */
    public function kasir_cannot_create_a_category(): void
    {
        $this->actingAs($this->kasir)
            ->post(route('admin.categories.store'), [
                'name'      => 'Kategori Kasir',
                'code'      => 'KAS_KAT',
                'is_active' => 1,
            ])
            ->assertForbidden();
    }

    /** @test */
    public function kasir_can_view_categories_list(): void
    {
        $this->actingAs($this->kasir)
            ->get(route('admin.categories.index'))
            ->assertOk();
    }

    /** @test */
    public function super_admin_can_deactivate_a_category(): void
    {
        $category = Category::create(['name' => 'Kategori Aktif', 'code' => 'KATAKTIF', 'is_active' => true]);

        $this->actingAs($this->superAdmin)
            ->patch(route('admin.categories.deactivate', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id'        => $category->id,
            'is_active' => false,
        ]);
    }

    // ─── UoM ─────────────────────────────────────────────────────────────────

    /** @test */
    public function super_admin_can_create_a_uom(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.uoms.store'), [
                'code' => 'LUSIN',
                'name' => 'Lusin',
            ])
            ->assertRedirect(route('admin.uoms.index'));

        $this->assertDatabaseHas('uoms', [
            'code' => 'LUSIN',
            'name' => 'Lusin',
        ]);
    }

    /** @test */
    public function duplicate_uom_code_is_rejected(): void
    {
        // PCS already seeded by UomSeeder
        $this->actingAs($this->superAdmin)
            ->post(route('admin.uoms.store'), [
                'code' => 'PCS',
                'name' => 'Piece Duplikat',
            ])
            ->assertSessionHasErrors('code');
    }

    /** @test */
    public function kasir_cannot_create_a_uom(): void
    {
        $this->actingAs($this->kasir)
            ->post(route('admin.uoms.store'), [
                'code' => 'KAS_UOM',
                'name' => 'UoM Kasir',
            ])
            ->assertForbidden();
    }

    /** @test */
    public function kasir_can_view_uoms_list(): void
    {
        $this->actingAs($this->kasir)
            ->get(route('admin.uoms.index'))
            ->assertOk();
    }

    /** @test */
    public function super_admin_can_update_a_uom(): void
    {
        $uom = Uom::create(['code' => 'TMPUOM', 'name' => 'Nama Lama']);

        $this->actingAs($this->superAdmin)
            ->put(route('admin.uoms.update', $uom), [
                'code' => 'TMPUOM',
                'name' => 'Nama Baru',
            ])
            ->assertRedirect(route('admin.uoms.index'));

        $this->assertDatabaseHas('uoms', [
            'id'   => $uom->id,
            'name' => 'Nama Baru',
        ]);
    }

    /** @test */
    public function admin_cabang_can_create_brand_and_category(): void
    {
        // Brand
        $this->actingAs($this->adminCabang)
            ->post(route('admin.brands.store'), [
                'name'      => 'Merk Admin Cabang',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.brands.index'));

        $this->assertDatabaseHas('brands', ['name' => 'Merk Admin Cabang']);

        // Category
        $this->actingAs($this->adminCabang)
            ->post(route('admin.categories.store'), [
                'name'      => 'Kategori Admin Cabang',
                'code'      => 'KAT_ADM',
                'is_active' => 1,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['name' => 'Kategori Admin Cabang']);
    }
}
