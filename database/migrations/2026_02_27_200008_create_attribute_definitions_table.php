<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();      // machine key used in attributes_json
            $table->string('label', 100);             // label tampil di UI
            $table->enum('data_type', ['number', 'text', 'select', 'boolean'])->default('text');
            $table->string('unit', 20)->nullable();   // mm, m, W, kg, dll
            $table->json('options_json')->nullable();  // untuk data_type=select
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });

        Schema::create('category_attribute_sets', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('attribute_definition_id');
            $table->primary(['category_id', 'attribute_definition_id']);

            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->foreign('attribute_definition_id')->references('id')->on('attribute_definitions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_attribute_sets');
        Schema::dropIfExists('attribute_definitions');
    }
};
