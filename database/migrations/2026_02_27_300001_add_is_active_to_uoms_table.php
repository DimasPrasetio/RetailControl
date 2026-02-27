<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom is_active pada tabel uoms agar UoM bisa dinonaktifkan,
 * konsisten dengan brands dan categories yang sudah punya is_active.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('uoms', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('uoms', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
