<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    private array $brands = [
        'RUCIKA', 'TRILLIUN', 'MASPION', 'HCL', 'EXTRANA',
        'MILLIARD', 'MU', 'NO DROP', 'POWER PRALON', 'SEAGULL',
    ];

    public function run(): void
    {
        $tenantId = Tenant::query()->firstOrFail()->id;

        foreach ($this->brands as $name) {
            Brand::updateOrCreate(
                ['tenant_id' => $tenantId, 'name' => $name],
                ['tenant_id' => $tenantId, 'is_active' => true]
            );
        }
    }
}
