<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

        // Bersihkan audit log yang tercipta otomatis saat seeding.
        // Hindari TRUNCATE agar transaksi test MySQL tetap terisolasi.
        DB::table('audit_logs')->delete();
    }
}
