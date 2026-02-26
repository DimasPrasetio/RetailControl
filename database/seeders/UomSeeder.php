<?php

namespace Database\Seeders;

use App\Models\Uom;
use Illuminate\Database\Seeder;

class UomSeeder extends Seeder
{
    private array $uoms = [
        ['code' => 'PCS',    'name' => 'Pieces'],
        ['code' => 'BATANG', 'name' => 'Batang'],
        ['code' => 'BOX',    'name' => 'Box / Dus'],
        ['code' => 'ROLL',   'name' => 'Roll / Gulungan'],
        ['code' => 'METER',  'name' => 'Meter'],
        ['code' => 'KG',     'name' => 'Kilogram'],
        ['code' => 'PACK',   'name' => 'Pack / Sak'],
        ['code' => 'LEMBAR', 'name' => 'Lembar'],
        ['code' => 'SET',    'name' => 'Set'],
    ];

    public function run(): void
    {
        foreach ($this->uoms as $uom) {
            Uom::updateOrCreate(['code' => $uom['code']], ['name' => $uom['name']]);
        }
    }
}
