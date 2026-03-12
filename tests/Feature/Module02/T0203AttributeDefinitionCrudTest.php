<?php

namespace Tests\Feature\Module02;

use App\Models\AttributeDefinition;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class T0203AttributeDefinitionCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $adminCabang;
    private User $kasir;
    private int $tenantId;
    private int $branchId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $this->tenantId = Tenant::query()->value('id');
        $this->branchId = Branch::query()->value('id');

        $adminRoleId = Role::where('name', 'admin')->value('id');
        $kasirRoleId = Role::where('name', 'kasir')->value('id');

        $this->adminCabang = User::create([
            'name' => 'Admin Attribute Test',
            'username' => 'admin_attribute_test',
            'email' => null,
            'password' => bcrypt('Password1'),
            'role_id' => $adminRoleId,
            'branch_id' => $this->branchId,
            'is_active' => true,
        ]);

        $this->kasir = User::create([
            'name' => 'Kasir Attribute Test',
            'username' => 'kasir_attribute_test',
            'email' => null,
            'password' => bcrypt('Password1'),
            'role_id' => $kasirRoleId,
            'branch_id' => $this->branchId,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function super_admin_can_create_attribute_definition_and_assign_categories(): void
    {
        $category = Category::where('tenant_id', $this->tenantId)->where('code', 'PIPA_PVC')->firstOrFail();

        $this->actingAs($this->superAdmin)
            ->post(route('admin.attribute-definitions.store'), [
                'tenant_id' => $this->tenantId,
                'key' => 'pressure_class',
                'label' => 'Pressure Class',
                'data_type' => 'select',
                'unit' => null,
                'options_text' => 'A, B, C',
                'category_ids' => [$category->id],
                'is_required' => 1,
            ])
            ->assertRedirect(route('admin.attribute-definitions.index', ['tenant_id' => $this->tenantId]));

        $attributeDefinition = AttributeDefinition::where('tenant_id', $this->tenantId)
            ->where('key', 'pressure_class')
            ->firstOrFail();

        $this->assertSame(['A', 'B', 'C'], $attributeDefinition->options_json);
        $this->assertTrue($attributeDefinition->categories()->whereKey($category->id)->exists());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'create',
            'auditable_type' => AttributeDefinition::class,
            'auditable_id' => $attributeDefinition->id,
        ]);
    }

    /** @test */
    public function admin_can_create_attribute_definition_for_their_own_tenant(): void
    {
        $category = Category::where('tenant_id', $this->tenantId)->where('code', 'KABEL')->firstOrFail();

        $this->actingAs($this->adminCabang)
            ->post(route('admin.attribute-definitions.store'), [
                'key' => 'insulation_grade',
                'label' => 'Insulation Grade',
                'data_type' => 'text',
                'category_ids' => [$category->id],
                'is_required' => 0,
            ])
            ->assertRedirect(route('admin.attribute-definitions.index', ['tenant_id' => $this->tenantId]));

        $this->assertDatabaseHas('attribute_definitions', [
            'tenant_id' => $this->tenantId,
            'key' => 'insulation_grade',
        ]);
    }

    /** @test */
    public function kasir_cannot_create_attribute_definition(): void
    {
        $this->actingAs($this->kasir)
            ->post(route('admin.attribute-definitions.store'), [
                'key' => 'blocked_attr',
                'label' => 'Blocked Attr',
                'data_type' => 'text',
            ])
            ->assertForbidden();
    }

    /** @test */
    public function super_admin_can_update_attribute_definition(): void
    {
        $attributeDefinition = AttributeDefinition::create([
            'tenant_id' => $this->tenantId,
            'key' => 'surface_finish',
            'label' => 'Surface Finish',
            'data_type' => 'text',
            'is_required' => false,
        ]);

        $this->actingAs($this->superAdmin)
            ->put(route('admin.attribute-definitions.update', $attributeDefinition), [
                'tenant_id' => $this->tenantId,
                'key' => 'surface_finish',
                'label' => 'Surface Finish Updated',
                'data_type' => 'boolean',
                'unit' => null,
                'options_text' => null,
                'is_required' => 1,
            ])
            ->assertRedirect(route('admin.attribute-definitions.index', ['tenant_id' => $this->tenantId]));

        $this->assertDatabaseHas('attribute_definitions', [
            'id' => $attributeDefinition->id,
            'label' => 'Surface Finish Updated',
            'data_type' => 'boolean',
            'is_required' => true,
        ]);

        $this->assertTrue(
            AuditLog::where('auditable_type', AttributeDefinition::class)
                ->where('auditable_id', $attributeDefinition->id)
                ->where('action', 'update')
                ->exists()
        );
    }
}
