<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('username', 50)->unique();
            $table->string('email', 150)->unique();
            $table->string('password');
            // role_id FK ditambahkan via migration terpisah (setelah roles table dibuat)
            $table->unsignedBigInteger('role_id');
            // branch_id nullable — NULL = akses semua cabang (super_admin, owner)
            // FK ke branches ditambahkan di Module 02 setelah branches table dibuat
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('role_id');
            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
