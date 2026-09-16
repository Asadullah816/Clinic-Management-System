<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();

            // The treatment performed. nullOnDelete: deleting the catalog item
            // must not erase the patient's historical record.
            $table->foreignId('treatment_id')->nullable()->constrained('treatments')->nullOnDelete();

            $table->date('treatment_date');
            $table->decimal('price', 10, 2);            // price snapshot at time of treatment
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);     // stored: price - discount
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_treatments');
    }
};
