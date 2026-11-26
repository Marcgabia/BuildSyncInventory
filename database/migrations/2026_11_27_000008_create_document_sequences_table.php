<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Internal helper (INV only, not shared): the last number used per prefix
 * and day, e.g. "SM-20261127" -> 7. DocumentNumber::next() locks the row
 * inside the transaction, so two requests never get the same number (A3).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->string('sequence_key', 30)->primary();   // PREFIX-YYYYMMDD
            $table->unsignedInteger('last_number')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
