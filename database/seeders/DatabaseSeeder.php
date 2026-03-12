<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TenantSeeder::class,

            // Module 01 - Auth & RBAC
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,

            // Module 03 - Branch & Warehouse
            BranchSeeder::class,
            SuperAdminSeeder::class,

            // Module 02 - Master Data
            UomSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            AttributeDefinitionSeeder::class,
        ]);

        if (app()->runningUnitTests()) {
            DB::table('audit_logs')->delete();
        }
    }
}
