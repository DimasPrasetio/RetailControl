<?php

namespace App\Console\Commands;

use App\Services\Product\ExcelImportService;
use Illuminate\Console\Command;

/**
 * php artisan products:import {path?}
 *
 * Import master produk SKU dari file Excel MASTER BOOK TOKO.
 * Jika {path} tidak diberikan, akan mencari file di storage/app/imports/
 */
class ImportProductsCommand extends Command
{
    protected $signature   = 'products:import {path? : Path ke file Excel (.xlsx)}';
    protected $description = 'Import master produk SKU dari Excel (MASTER BOOK TOKO format)';

    public function handle(ExcelImportService $service): int
    {
        $path = $this->argument('path');

        if (! $path) {
            // Cari file di storage/app/imports/
            $dir   = storage_path('app/imports');
            $files = glob($dir . '/*.xlsx') ?: [];
            if (empty($files)) {
                $this->error("Tidak ada file .xlsx. Letakkan file di {$dir} atau berikan path sebagai argumen.");
                return Command::FAILURE;
            }
            $path = $files[0];
            $this->info("Menggunakan file: {$path}");
        }

        if (! file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");
            return Command::FAILURE;
        }

        $this->info("Memulai import dari: {$path}");
        $this->newLine();

        $result = $service->import($path);

        $this->table(
            ['Metric', 'Jumlah'],
            [
                ['SKU Berhasil Dibuat', $result['created']],
                ['SKU Sudah Ada (Skip)', $result['skipped']],
                ['Baris Gagal',          $result['failed']],
            ]
        );

        if (! empty($result['errors'])) {
            $this->newLine();
            $this->warn('Detail Error:');
            foreach (array_slice($result['errors'], 0, 50) as $err) {
                $this->line("  • {$err}");
            }
            if (count($result['errors']) > 50) {
                $this->line('  ... dan ' . (count($result['errors']) - 50) . ' error lainnya.');
            }
        }

        $this->newLine();
        if ($result['failed'] === 0) {
            $this->info("Import selesai. {$result['created']} SKU dibuat.");
            return Command::SUCCESS;
        }

        $this->warn("Import selesai dengan {$result['failed']} kegagalan. Periksa log di atas.");
        return Command::SUCCESS;
    }
}
