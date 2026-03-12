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
            $table->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();
            $table->string('key', 50);
            $table->string('label', 100);
            $table->enum('data_type', ['number', 'text', 'select', 'boolean'])->default('text');
            $table->string('unit', 20)->nullable();
            $table->json('options_json')->nullable();
            $table->boolean('is_required')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'key']);
        });

        Schema::create('category_attribute_sets', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('attribute_definition_id')->constrained('attribute_definitions')->cascadeOnDelete();
            $table->primary(['category_id', 'attribute_definition_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_attribute_sets');
        Schema::dropIfExists('attribute_definitions');
    }
};
