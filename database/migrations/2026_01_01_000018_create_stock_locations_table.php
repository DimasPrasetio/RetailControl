<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('stock_locations')->nullOnDelete();
            $table->string('code', 40);
            $table->string('name', 100);
            $table->enum('type', ['BRANCH', 'WAREHOUSE', 'AREA', 'SUB_AREA'])->default('WAREHOUSE');
            $table->json('metadata_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'branch_id', 'warehouse_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_locations');
    }
};
