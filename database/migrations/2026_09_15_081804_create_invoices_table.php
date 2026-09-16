<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            // restrictOnDelete: financial records never auto-delete with a patient.
            // Patients are soft-deleted anyway, so this only guards rare force-deletes.
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->string('invoice_number')->unique();   // auto-generated: INV-0001
            $table->date('invoice_date');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);       // stored: subtotal - discount
            $table->decimal('paid_amount', 10, 2)->default(0);  // updated by payments (Phase 8)
            $table->decimal('due_amount', 10, 2);         // stored: total - paid
            $table->string('status')->default('pending'); // derived: paid / partial / pending
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
