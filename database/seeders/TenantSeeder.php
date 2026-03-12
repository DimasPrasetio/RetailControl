<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        Tenant::updateOrCreate(
            ['code' => 'TBSAK'],
            [
                'name' => 'TB. SUMBER ABADI KAMOLAN',
                'is_active' => true,
            ],
        );

        Tenant::where('code', 'DEFAULT')
            ->where('name', 'Default Tenant')
            ->update([
                'name' => 'Legacy Default Tenant',
                'is_active' => false,
            ]);
    }
}
