<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            // patients soft-delete, so cascade rarely fires — consistent with other patient children
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            // restrictOnDelete: an invoice with payments must never be force-deleted
            // (the controller guard blocks it too — this is the database-level second lock)
            $table->foreignId('invoice_id')->constrained('invoices')->restrictOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // cash, bank, card, online, other
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
