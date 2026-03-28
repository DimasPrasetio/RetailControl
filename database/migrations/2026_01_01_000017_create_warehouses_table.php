<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->restrictOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->string('warehouse_code', 30);
            $table->string('name', 100);
            $table->enum('type', ['MAIN', 'SECONDARY', 'RETURNS'])->default('MAIN');
            $table->text('notes')->nullable();
            $table->json('metadata_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'warehouse_code']);
            $table->index(['tenant_id', 'branch_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
