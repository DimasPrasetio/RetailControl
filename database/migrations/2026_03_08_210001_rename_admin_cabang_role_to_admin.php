<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
        $legacyRoleId = DB::table('roles')->where('name', 'admin_cabang')->value('id');

        if (! $legacyRoleId) {
            return;
        }

        if ($adminRoleId) {
            DB::table('users')
                ->where('role_id', $legacyRoleId)
                ->update(['role_id' => $adminRoleId]);

            DB::table('role_permissions')->where('role_id', $legacyRoleId)->delete();
            DB::table('roles')->where('id', $legacyRoleId)->delete();

            return;
        }

        DB::table('roles')
            ->where('id', $legacyRoleId)
            ->update([
                'name' => 'admin',
                'description' => 'Kelola operasional cabang yang ditugaskan.',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');

        if (! $adminRoleId) {
            return;
        }

        DB::table('roles')
            ->where('id', $adminRoleId)
            ->update([
                'name' => 'admin_cabang',
                'description' => 'Kelola operasional cabang yang ditugaskan.',
                'updated_at' => now(),
            ]);
    }
};
