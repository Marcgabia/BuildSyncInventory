<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A3 / F4: low-stock alerts (LSA-20261123-0001). Each one becomes a
 * purchase request in PROC; pr_number is PROC's answer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('low_stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('alert_number', 30)->unique();
            $table->string('sku', 20)->index();
            $table->string('warehouse_code', 20);
            $table->string('branch_code', 20);
            $table->unsignedInteger('quantity_at_alert');
            $table->unsignedInteger('reorder_level');
            $table->enum('status', ['Open', 'Closed'])->default('Open');
            $table->string('pr_number', 30)->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->foreign('sku')->references('sku')->on('products')->restrictOnUpdate()->restrictOnDelete();
        });

        // "Wala pang Open na alert para sa SKU + warehouse" (A4), enforced by the
        // database: only Open alerts get a value here, and it must be unique.
        Schema::table('low_stock_alerts', function (Blueprint $table) {
            $table->string('open_key', 50)
                ->nullable()
                ->storedAs("IF(status = 'Open', CONCAT(sku, '|', warehouse_code), NULL)")
                ->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('low_stock_alerts');
    }
};
