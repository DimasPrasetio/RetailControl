<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('stock_location_id')->nullable()->constrained('stock_locations')->nullOnDelete();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->foreignId('uom_id')->constrained('uoms')->restrictOnDelete();
            $table->foreignId('transaction_uom_id')->nullable()->constrained('uoms')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('movement_type', 50);
            $table->decimal('qty_in', 15, 4)->default(0);
            $table->decimal('qty_out', 15, 4)->default(0);
            $table->decimal('transaction_qty_in', 15, 4)->nullable();
            $table->decimal('transaction_qty_out', 15, 4)->nullable();
            $table->decimal('balance_after', 15, 4)->nullable();
            $table->string('source_type', 100)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->json('notes_json')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['tenant_id', 'item_id', 'created_at']);
            $table->index(['stock_location_id', 'created_at']);
            $table->index(['item_id', 'transaction_uom_id', 'created_at'], 'inventory_ledgers_item_transaction_uom_created_idx');
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_ledgers');
    }
};
