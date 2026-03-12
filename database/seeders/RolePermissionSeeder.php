<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Mapping role -> daftar permission slug.
     * super_admin tidak perlu di sini karena di-bypass di User::hasPermission().
     */
    private array $map = [
        RoleEnum::Owner->value => [
            'users.view',
            'transactions.view',
            'do.view',
            'stock.view',
            'prices.view',
            'reports.view_branch',
            'reports.view_all',
            'audit_logs.view',
            'branches.view',
            'warehouses.view',
            'items.view',
            'brands.view',
            'categories.view',
            'uoms.view',
            'attribute_definitions.view',
            'stock_locations.view',
        ],

        RoleEnum::Admin->value => [
            'users.view',
            'users.deactivate',
            'transactions.view',
            'transactions.create',
            'transactions.void',
            'transactions.apply_discount',
            'transactions.override_price',
            'do.view',
            'do.release',
            'do.update_status',
            'stock.view',
            'stock.adjust',
            'prices.view',
            'prices.override_branch',
            'branches.view',
            'warehouses.view',
            'warehouses.create',
            'warehouses.update',
            'warehouses.deactivate',
            'reports.view_branch',
            'items.view',
            'items.create',
            'items.update',
            'items.deactivate',
            'items.import',
            'brands.view',
            'brands.create',
            'brands.update',
            'brands.deactivate',
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.deactivate',
            'uoms.view',
            'uoms.create',
            'uoms.update',
            'uoms.deactivate',
            'attribute_definitions.view',
            'attribute_definitions.create',
            'attribute_definitions.update',
            'stock_locations.view',
            'stock_locations.create',
            'stock_locations.update',
            'stock_locations.deactivate',
            'items.manage_barcodes',
        ],

        RoleEnum::Kasir->value => [
            'transactions.view',
            'transactions.create',
            'transactions.apply_discount',
            'do.view',
            'stock.view',
            'prices.view',
            'branches.view',
            'warehouses.view',
            'items.view',
            'brands.view',
            'categories.view',
            'uoms.view',
            'stock_locations.view',
        ],
    ];

    public function run(): void
    {
        foreach ($this->map as $roleName => $slugs) {
            $role = Role::where('name', $roleName)->firstOrFail();
            $permissionIds = Permission::whereIn('slug', $slugs)->pluck('id');
            $role->permissions()->sync($permissionIds);
        }
    }
}
