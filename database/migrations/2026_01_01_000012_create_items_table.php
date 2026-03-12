<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();
            $table->string('sku_code', 100);
            $table->string('name', 255);
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('base_uom_id')->constrained('uoms')->restrictOnDelete();
            $table->foreignId('selling_uom_id')->nullable()->constrained('uoms')->nullOnDelete();
            $table->foreignId('purchase_uom_id')->nullable()->constrained('uoms')->nullOnDelete();
            $table->decimal('pack_qty', 12, 4)->default(1);
            $table->boolean('tax_included')->default(true);
            $table->boolean('is_stockable')->default(true);
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->decimal('minimum_selling_price', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('attributes_json')->nullable();
            $table->json('custom_fields_json')->nullable();
            $table->json('raw_source_json')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'sku_code']);
            $table->index(['tenant_id', 'brand_id', 'category_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
