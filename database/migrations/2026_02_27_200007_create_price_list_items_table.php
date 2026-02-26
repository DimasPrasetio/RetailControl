<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_list_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('price_list_id');
            $table->unsignedBigInteger('item_id');
            // Harga jual dalam satuan base_uom item
            $table->decimal('sell_price', 15, 2);
            $table->unsignedBigInteger('price_uom_id')->nullable();
            // Simpan breakdown diskon dari sumber Excel (audit trail)
            $table->json('notes_json')->nullable();
            $table->timestamps();

            $table->unique(['price_list_id', 'item_id']);
            $table->foreign('price_list_id')->references('id')->on('price_lists')->cascadeOnDelete();
            $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
            $table->foreign('price_uom_id')->references('id')->on('uoms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_items');
    }
};
