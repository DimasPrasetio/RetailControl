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
            'users.view',
            'transactions.view',
            'do.view',
            'stock.view',
            'prices.view',
            'reports.view_branch',
            'reports.view_all',
            'audit_logs.view',
        ],

        RoleEnum::AdminCabang->value => [
            'transactions.view',
            'transactions.create',
            'transactions.void',
            'do.view',
            'do.release',
            'do.update_status',
            'stock.view',
            'stock.adjust',
            'prices.view',
            'prices.override_branch',
            'reports.view_branch',
        ],

        RoleEnum::Kasir->value => [
            'transactions.view',
            'transactions.create',
            'do.view',
            'stock.view',
            'prices.view',
        ],

        RoleEnum::Accounting->value => [
            'transactions.view',
            'stock.view',
            'prices.view',
            'reports.view_branch',
            'reports.view_all',
            'audit_logs.view',
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
