<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', RoleEnum::SuperAdmin->value)->firstOrFail();

        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name'      => 'Super Admin',
                'email'     => 'superadmin@retailcontrol.local',
                'password'  => Hash::make('SuperAdmin@123'),
                'role_id'   => $role->id,
                'branch_id' => null,
                'is_active' => true,
            ],
        );
    }
}
