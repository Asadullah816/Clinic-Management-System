<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laser_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laser_patient_id')->constrained('laser_patients')->cascadeOnDelete();
            $table->foreignId('laser_treatment_id')->nullable()->constrained('laser_treatments')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->date('session_date');
            $table->integer('session_number')->default(1);
            $table->integer('total_sessions')->nullable()->default(6);
            $table->string('laser_machine')->nullable(); // e.g. Diode Triple Wavelength, Nd:YAG, CO2
            $table->string('fluence')->nullable();       // e.g. 18 J/cm²
            $table->string('pulse_width')->nullable();   // e.g. 30 ms
            $table->string('spot_size')->nullable();     // e.g. 12x12 mm
            $table->integer('pulses_count')->nullable(); // Shots count
            $table->decimal('price', 10, 2);
            $table->string('discount_type')->default('fixed'); // fixed, percentage
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->string('status')->default('pending'); // paid, partial, pending
            $table->string('payment_method')->nullable();  // cash, bank, card, online, other
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laser_sessions');
    }
};
