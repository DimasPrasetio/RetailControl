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
        'users.view'       => 'Lihat Daftar User',
        'users.create'     => 'Tambah User',
        'users.update'     => 'Edit User',
        'users.deactivate' => 'Nonaktifkan User',
        'users.delete'     => 'Hapus User (Permanen)',

        // Transaksi POS
        'transactions.view'           => 'Lihat Transaksi',
        'transactions.create'         => 'Buat Transaksi',
        'transactions.void'           => 'Void Transaksi',
        'transactions.apply_discount' => 'Terapkan Diskon Transaksi',
        'transactions.override_price' => 'Override Harga Transaksi',

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

        // Master Data — Items (SKU)
        'items.view'       => 'Lihat Master Produk',
        'items.create'     => 'Tambah Master Produk',
        'items.update'     => 'Edit Master Produk',
        'items.deactivate' => 'Nonaktifkan Master Produk',
        'items.import'     => 'Import Master Produk dari Excel',

        // Master Data — Brands
        'brands.view'       => 'Lihat Brand',
        'brands.create'     => 'Tambah Brand',
        'brands.update'     => 'Edit Brand',
        'brands.deactivate' => 'Nonaktifkan Brand',

        // Master Data — Categories
        'categories.view'       => 'Lihat Kategori',
        'categories.create'     => 'Tambah Kategori',
        'categories.update'     => 'Edit Kategori',
        'categories.deactivate' => 'Nonaktifkan Kategori',

        // Master Data — Units of Measure
        'uoms.view'       => 'Lihat Satuan (UoM)',
        'uoms.create'     => 'Tambah Satuan (UoM)',
        'uoms.update'     => 'Edit Satuan (UoM)',
        'uoms.deactivate' => 'Nonaktifkan Satuan (UoM)',
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
