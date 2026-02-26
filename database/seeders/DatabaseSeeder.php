<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // Module 01 — Auth & RBAC
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,
            // Module 02 — Master Data
            UomSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            AttributeDefinitionSeeder::class,
        ]);
    }
}
