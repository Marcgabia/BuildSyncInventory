<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B7: every incoming and outgoing integration call is written here.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('direction', ['in', 'out']);
            $table->string('system', 10);              // CRM, PROC, INV or HR (the other side of the call)
            $table->string('method', 10);              // GET, POST, ...
            $table->string('url', 500);
            $table->string('reference_number', 100)->nullable();
            $table->json('request_body')->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->json('response_body')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index('reference_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
    }
};
