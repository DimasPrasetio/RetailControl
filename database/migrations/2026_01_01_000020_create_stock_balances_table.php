<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();
            $table->foreignId('item_id')->constrained('items')->restrictOnDelete();
            $table->foreignId('stock_location_id')->constrained('stock_locations')->restrictOnDelete();
            $table->decimal('qty', 15, 4)->default(0);
            $table->timestamps();

            $table->unique(['item_id', 'stock_location_id']);
            $table->index(['tenant_id', 'stock_location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_balances');
    }
};
