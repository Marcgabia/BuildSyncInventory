<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A3 "Mga kopya": read-only copies of data owned by other systems.
 *   HR   -> branches, departments, employees
 *   PROC -> suppliers, supplier_products
 * The code is unique (B4), never the id. source_updated_at is the owner's
 * updated_at, used as updated_since by integration:sync-masterdata (A6).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('branch_code', 20)->unique();
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('status', 20)->default('Active');
            $table->timestamp('source_updated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('department_code', 20)->unique();
            $table->string('name');
            $table->string('status', 20)->default('Active');
            $table->timestamp('source_updated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_no', 20)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('branch_code', 20)->nullable()->index();
            $table->string('department_code', 20)->nullable()->index();
            $table->string('position')->nullable();
            $table->string('employment_status', 30);
            $table->boolean('can_login')->default(true);
            $table->timestamp('source_updated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code', 20)->unique();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('status', 20)->default('Active');
            $table->timestamp('source_updated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code', 20);
            $table->string('sku', 20)->index();
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->unsignedInteger('lead_time_days')->nullable();
            $table->boolean('is_preferred')->default(false);
            $table->timestamp('source_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['supplier_code', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_products');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('branches');
    }
};
