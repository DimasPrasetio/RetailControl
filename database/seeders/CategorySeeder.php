<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    private array $categories = [
        ['code' => 'PIPA_PVC',   'name' => 'Pipa PVC',              'parent' => null],
        ['code' => 'FITTING_PVC','name' => 'Fitting PVC',           'parent' => null],
        ['code' => 'POMPA',      'name' => 'Pompa Air',             'parent' => null],
        ['code' => 'KABEL',      'name' => 'Kabel Listrik',         'parent' => null],
        ['code' => 'CHEM',       'name' => 'Waterproofing / Chemical','parent' => null],
        ['code' => 'MORTAR',     'name' => 'Mortar / Material Bangunan','parent' => null],
        ['code' => 'LAINNYA',    'name' => 'Lainnya',               'parent' => null],
    ];

    public function run(): void
    {
        foreach ($this->categories as $cat) {
            Category::updateOrCreate(
                ['code' => $cat['code']],
                ['name' => $cat['name'], 'is_active' => true]
            );
        }
    }
}
