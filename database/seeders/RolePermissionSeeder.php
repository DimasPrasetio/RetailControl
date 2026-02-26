<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Mapping role → daftar permission slug.
     * super_admin tidak perlu di sini karena di-bypass di User::hasPermission().
     */
    private array $map = [
        RoleEnum::Owner->value => [
            // User management — read only
            'users.view',
            // Transaksi — read only
            'transactions.view',
            // Delivery Order — read only
            'do.view',
            // Inventory — read only
            'stock.view',
            // Pricing — read only
            'prices.view',
            // Laporan — semua cabang
            'reports.view_branch',
            'reports.view_all',
            // Audit
            'audit_logs.view',
            // Master Data — read only
            'items.view',
            'brands.view',
            'categories.view',
            'uoms.view',
        ],

        RoleEnum::AdminCabang->value => [
            // User management cabang
            'users.view',
            'users.deactivate',
            // Transaksi
            'transactions.view',
            'transactions.create',
            'transactions.void',
            'transactions.apply_discount',
            'transactions.override_price',
            // Delivery Order
            'do.view',
            'do.release',
            'do.update_status',
            // Inventory
            'stock.view',
            'stock.adjust',
            // Pricing
            'prices.view',
            'prices.override_branch',
            // Laporan cabang
            'reports.view_branch',
            // Master Data — full CRUD (katalog global)
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
        ],

        RoleEnum::Kasir->value => [
            // Transaksi POS
            'transactions.view',
            'transactions.create',
            'transactions.apply_discount',
            // Delivery Order — lihat saja
            'do.view',
            // Inventory — lihat saja
            'stock.view',
            // Pricing — lihat saja
            'prices.view',
            // Master Data — lihat saja (untuk POS search)
            'items.view',
            'brands.view',
            'categories.view',
            'uoms.view',
        ],

        RoleEnum::Accounting->value => [
            // Transaksi — lihat saja
            'transactions.view',
            // Inventory — lihat saja
            'stock.view',
            // Pricing — lihat saja
            'prices.view',
            // Laporan — semua cabang
            'reports.view_branch',
            'reports.view_all',
            // Audit
            'audit_logs.view',
            // Master Data — lihat saja
            'items.view',
            'brands.view',
            'categories.view',
            'uoms.view',
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
