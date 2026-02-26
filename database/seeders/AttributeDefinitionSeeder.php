<?php

namespace Database\Seeders;

use App\Models\AttributeDefinition;
use Illuminate\Database\Seeder;

class AttributeDefinitionSeeder extends Seeder
{
    /**
     * Attribute definitions covering all Excel sheets in MANAGEMENT_PRODUCT.txt.
     */
    private array $definitions = [
        // Pipa PVC
        ['key' => 'diameter_mm',       'label' => 'Diameter (mm)',       'data_type' => 'number', 'unit' => 'mm'],
        ['key' => 'diameter_inch',      'label' => 'Diameter (inch)',     'data_type' => 'text',   'unit' => 'inch'],
        ['key' => 'od_mm',              'label' => 'OD (mm)',             'data_type' => 'number', 'unit' => 'mm'],
        ['key' => 'panjang_m',          'label' => 'Panjang (m)',         'data_type' => 'number', 'unit' => 'm'],
        ['key' => 'spec',               'label' => 'Spesifikasi (AW/D/dll)', 'data_type' => 'text', 'unit' => null],
        ['key' => 'model',              'label' => 'Model (POLOS/MOF/dll)',  'data_type' => 'text', 'unit' => null],
        // Fitting PVC
        ['key' => 'jenis',              'label' => 'Jenis',               'data_type' => 'text',   'unit' => null],
        ['key' => 'class',              'label' => 'Class',               'data_type' => 'text',   'unit' => null],
        ['key' => 'isi_per_box',        'label' => 'Isi per Box (pcs)',   'data_type' => 'number', 'unit' => 'pcs'],
        // Pompa
        ['key' => 'outlet',             'label' => 'Outlet',              'data_type' => 'text',   'unit' => null],
        ['key' => 'power_watt',         'label' => 'Daya (Watt)',         'data_type' => 'number', 'unit' => 'W'],
        ['key' => 'type_text',          'label' => 'Tipe',                'data_type' => 'text',   'unit' => null],
        // Kabel (EXTRANA)
        ['key' => 'cable_size',         'label' => 'Ukuran Kabel',        'data_type' => 'text',   'unit' => null],
        ['key' => 'cable_size_unit',    'label' => 'Satuan Ukuran Kabel', 'data_type' => 'text',   'unit' => null],
        ['key' => 'cable_type',         'label' => 'Tipe Kabel (NYM/NYY/dll)', 'data_type' => 'select', 'unit' => null,
         'options' => ['NYM','NYY','NYMHY','NYYHY','NFYGbY','NYA','NYAF','NA2XSY','N2XSY','N2XSEBY','NA2XSEBY','MVTIC','NA2XSEYBY','NFA2X','AAAC','AAACS','NFA2X-T']],
        // Milliard / selang gulungan
        ['key' => 'length_per_roll_m',  'label' => 'Panjang per Roll (m)','data_type' => 'number', 'unit' => 'm'],
        // No Drop / Chemical
        ['key' => 'pack_size',          'label' => 'Ukuran Kemasan',      'data_type' => 'number', 'unit' => null],
        ['key' => 'pack_size_unit',     'label' => 'Satuan Kemasan',      'data_type' => 'text',   'unit' => null],
        // Mortar (MU)
        ['key' => 'weight_kg',          'label' => 'Berat (kg)',          'data_type' => 'number', 'unit' => 'kg'],
    ];

    public function run(): void
    {
        foreach ($this->definitions as $def) {
            $options = $def['options'] ?? null;
            AttributeDefinition::updateOrCreate(
                ['key' => $def['key']],
                [
                    'label'       => $def['label'],
                    'data_type'   => $def['data_type'],
                    'unit'        => $def['unit'],
                    'options_json'=> $options ? json_encode($options) : null,
                    'is_required' => false,
                ]
            );
        }
    }
}
