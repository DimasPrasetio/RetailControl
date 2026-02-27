<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // NULL untuk aksi sistem (cron, seeder)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50);
            // Nama model, contoh: 'User', 'Transaction', 'DeliveryOrder'
            $table->string('auditable_type', 100);
            $table->unsignedBigInteger('auditable_id');
            // Snapshot nilai sebelum dan sesudah perubahan
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable(); // IPv6-safe
            $table->string('user_agent', 255)->nullable();
            // TIDAK ada updated_at — tabel ini append-only selamanya
            $table->timestamp('created_at')->useCurrent();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
