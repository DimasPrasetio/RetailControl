<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    private array $brands = [
        'RUCIKA', 'TRILLIUN', 'MASPION', 'HCL', 'EXTRANA',
        'MILLIARD', 'MU', 'NO DROP', 'POWER PRALON', 'SEAGULL',
    ];

    public function run(): void
    {
        foreach ($this->brands as $name) {
            Brand::updateOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}
