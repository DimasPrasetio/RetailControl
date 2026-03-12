<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => RoleEnum::SuperAdmin->value,
                'description' => 'Akses penuh ke seluruh sistem.',
            ],
            [
                'name' => RoleEnum::Owner->value,
                'description' => 'Visibilitas penuh ke semua cabang (read-only operasional).',
            ],
            [
                'name' => RoleEnum::Admin->value,
                'description' => 'Kelola operasional cabang yang ditugaskan.',
            ],
            [
                'name' => RoleEnum::Kasir->value,
                'description' => 'Input transaksi POS di cabang yang ditugaskan.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
