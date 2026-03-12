<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\StockLocation;
use App\Models\Tenant;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    private array $branches = [
        [
            'branch_code' => 'UTAMA',
            'name' => 'Cabang Utama',
            'address' => 'Jl. Mr. Iskandar No.KM.3, RT.05/RW.01, Bangeran, Kamolan, Kec. Blora, Kabupaten Blora, Jawa Tengah 58219',
            'timezone' => 'Asia/Jakarta',
        ],
    ];

    public function run(): void
    {
        $tenantId = Tenant::query()->firstOrFail()->id;

        foreach ($this->branches as $data) {
            $branch = Branch::updateOrCreate(
                ['tenant_id' => $tenantId, 'branch_code' => $data['branch_code']],
                [
                    'tenant_id' => $tenantId,
                    'name' => $data['name'],
                    'address' => $data['address'],
                    'timezone' => $data['timezone'],
                    'is_active' => true,
                ]
            );

            Warehouse::updateOrCreate(
                ['tenant_id' => $tenantId, 'warehouse_code' => "{$branch->branch_code}-MAIN"],
                [
                    'tenant_id' => $tenantId,
                    'branch_id' => $branch->id,
                    'name' => 'Toko / Gudang Utama',
                    'type' => 'MAIN',
                    'is_active' => true,
                ]
            );

            $mainWarehouse = Warehouse::where('tenant_id', $tenantId)
                ->where('warehouse_code', "{$branch->branch_code}-MAIN")
                ->first();

            if ($mainWarehouse) {
                StockLocation::updateOrCreate(
                    ['tenant_id' => $tenantId, 'code' => "{$branch->branch_code}-MAIN-ROOT"],
                    [
                        'tenant_id' => $tenantId,
                        'branch_id' => $branch->id,
                        'warehouse_id' => $mainWarehouse->id,
                        'name' => "Lokasi Utama {$branch->name}",
                        'type' => 'WAREHOUSE',
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
