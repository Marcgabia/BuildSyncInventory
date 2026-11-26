<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B7: requests waiting to be sent to another system.
 * Outbox::push() adds a row; integration:process-outbox sends it later.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_outbox', function (Blueprint $table) {
            $table->id();
            $table->string('target_system', 10);       // CRM, PROC or HR
            $table->string('method', 10);              // POST, ...
            $table->string('endpoint', 191);           // e.g. /purchase-requests
            $table->json('payload');
            $table->string('reference_number', 100);
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('next_attempt_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            // The same request is never queued twice.
            $table->unique(['target_system', 'endpoint', 'reference_number'], 'integration_outbox_target_endpoint_ref_unique');
            // ProcessOutbox looks for pending rows that are due.
            $table->index(['status', 'next_attempt_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_outbox');
    }
};
