<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Daftar semua permission sistem.
     * Format: 'slug' => 'Nama Tampilan'
     */
    private array $permissions = [
        // User Management
        'users.view'   => 'Lihat Daftar User',
        'users.create' => 'Tambah User',
        'users.update' => 'Edit User',
        'users.delete' => 'Hapus User',

        // Transaksi POS
        'transactions.view'   => 'Lihat Transaksi',
        'transactions.create' => 'Buat Transaksi',
        'transactions.void'   => 'Void Transaksi',

        // Delivery Order
        'do.view'          => 'Lihat Delivery Order',
        'do.release'       => 'Release Delivery Order',
        'do.update_status' => 'Update Status Delivery Order',

        // Inventory
        'stock.view'   => 'Lihat Stok',
        'stock.adjust' => 'Penyesuaian Stok',

        // Pricing
        'prices.view'            => 'Lihat Harga',
        'prices.update_global'   => 'Update Harga Global',
        'prices.override_branch' => 'Override Harga per Cabang',

        // Laporan
        'reports.view_branch' => 'Lihat Laporan Cabang',
        'reports.view_all'    => 'Lihat Laporan Semua Cabang',

        // Audit
        'audit_logs.view' => 'Lihat Audit Log',
    ];

    public function run(): void
    {
        foreach ($this->permissions as $slug => $name) {
            Permission::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name],
            );
        }
    }
}
