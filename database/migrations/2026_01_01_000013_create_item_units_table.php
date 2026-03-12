<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('uom_id')->constrained('uoms')->restrictOnDelete();
            $table->decimal('conversion_qty', 15, 4)->default(1);
            $table->boolean('is_base')->default(false);
            $table->boolean('allow_sale')->default(false);
            $table->boolean('allow_purchase')->default(false);
            $table->boolean('is_default_sale')->default(false);
            $table->boolean('is_default_purchase')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['item_id', 'uom_id']);
            $table->index(['tenant_id', 'item_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_units');
    }
};
