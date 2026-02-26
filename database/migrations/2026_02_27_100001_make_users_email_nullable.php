<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Email bersifat opsional: tidak semua user (kasir, helper) punya email kerja.
 * Login tetap bisa pakai username. Jika email diisi, wajib unik.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // unique index already exists from create_users_table; only make nullable
            $table->string('email', 150)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 150)->nullable(false)->change();
        });
    }
};
