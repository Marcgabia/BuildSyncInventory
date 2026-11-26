<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A3 / B7: link users to HR employees, and the INV role.
 * can_login comes from HR; when false the user cannot log in (F1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_no', 20)->nullable()->unique()->after('id');
            $table->string('employment_status', 30)->nullable()->after('email');
            $table->boolean('can_login')->default(true)->after('employment_status');
            // INV roles (A1): Admin, Manager, Inventory Clerk.
            $table->string('role', 30)->nullable()->after('can_login');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['employee_no']);
            $table->dropColumn(['employee_no', 'employment_status', 'can_login', 'role']);
        });
    }
};
