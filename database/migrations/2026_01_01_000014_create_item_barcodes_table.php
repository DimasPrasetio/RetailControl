<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_barcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('item_unit_id')->nullable()->constrained('item_units')->nullOnDelete();
            $table->string('barcode', 100);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'barcode']);
            $table->index(['item_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_barcodes');
    }
};
