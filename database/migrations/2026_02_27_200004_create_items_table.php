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
            $table->string('sku_code', 100)->unique();
            $table->string('name', 255);
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('base_uom_id');
            $table->unsignedBigInteger('purchase_uom_id')->nullable();
            // pack_qty: jumlah base_uom per purchase_uom (misal 1 BOX = 36 PCS)
            $table->decimal('pack_qty', 12, 4)->default(1);
            $table->boolean('tax_included')->default(true);
            $table->boolean('is_active')->default(true);
            // 3 JSON cadangan sesuai dokumen
            $table->json('attributes_json')->nullable();    // atribut teknis dinamis
            $table->json('custom_fields_json')->nullable(); // data tambahan masa depan
            $table->json('raw_source_json')->nullable();    // jejak sumber import
            $table->timestamps();

            $table->foreign('brand_id')->references('id')->on('brands')->nullOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('base_uom_id')->references('id')->on('uoms');
            $table->foreign('purchase_uom_id')->references('id')->on('uoms')->nullOnDelete();

            $table->index(['brand_id', 'category_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
