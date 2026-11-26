<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A3: stock per warehouse and its history.
 * Rule (A2): stocks.quantity never changes without a stock_movements row,
 * both written in the same transaction by StockMovementService.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 20);
            $table->string('warehouse_code', 20)->index();
            // Unsigned: MySQL refuses a negative quantity.
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();

            $table->unique(['sku', 'warehouse_code']);
            $table->index('updated_at');
            $table->foreign('sku')->references('sku')->on('products')->restrictOnUpdate()->restrictOnDelete();
            $table->foreign('warehouse_code')->references('warehouse_code')->on('warehouses')->restrictOnUpdate()->restrictOnDelete();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_number', 30)->unique();     // SM-20261123-0001
            $table->enum('movement_type', [
                'SALE_OUT', 'SALE_RETURN',                         // from CRM
                'PURCHASE_IN', 'SUPPLIER_RETURN_OUT',              // from PROC
                'OPENING_BALANCE', 'ADJUSTMENT_IN', 'ADJUSTMENT_OUT',
                'TRANSFER_OUT', 'TRANSFER_IN',                     // inside INV only
            ]);
            $table->string('source_system', 10);                   // CRM, PROC or INV
            $table->string('reference_number', 100);               // e.g. TXN-..., GRN-..., OPEN-...
            $table->string('branch_code', 20)->index();
            $table->string('warehouse_code', 20)->index();
            $table->string('performed_by', 20)->nullable();        // employee_no
            $table->string('remarks', 500)->nullable();
            $table->string('status', 20)->default('posted');
            $table->timestamps();

            // B5: the same source_system + reference_number is never posted twice.
            $table->unique(['source_system', 'reference_number']);
            $table->index('movement_type');
            $table->index('created_at');
        });

        Schema::create('stock_movement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_movement_id')->constrained('stock_movements')->restrictOnDelete();
            $table->string('sku', 20)->index();
            // Always positive; movement_type says whether it is added or removed.
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('quantity_before');
            $table->unsignedInteger('quantity_after');

            $table->foreign('sku')->references('sku')->on('products')->restrictOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movement_items');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stocks');
    }
};
