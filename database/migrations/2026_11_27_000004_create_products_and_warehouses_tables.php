<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A3 "Sariling data": products (SKU-0001) and warehouses (WH-001), owned by INV.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 20)->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('unit', 30)->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('cost', 12, 2)->default(0);
            $table->unsignedInteger('reorder_level')->default(0);
            $table->unsignedInteger('reorder_quantity')->default(0);
            // Filled from PROC's supplier_products (is_preferred = true), A6.
            $table->string('preferred_supplier_code', 20)->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();

            $table->index('updated_at'); // GET /products?updated_since=
        });

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('warehouse_code', 20)->unique();
            $table->string('name');
            $table->string('branch_code', 20)->index();
            $table->boolean('is_default')->default(false);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();

            $table->index('updated_at');
        });

        // "Isa lang na default bawat branch" (A3), enforced by the database:
        // this column holds the branch_code only for the default warehouse,
        // and a UNIQUE index allows only one such row per branch.
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('default_branch_code', 20)
                ->nullable()
                ->storedAs('IF(is_default, branch_code, NULL)')
                ->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('products');
    }
};
