<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_usages', function (Blueprint $table) {
            $table->id();
            // patients soft-delete, so cascade rarely fires — consistent with all patient children
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            // Optional: which treatment the product was used during
            $table->foreignId('treatment_id')->nullable()->constrained('treatments')->nullOnDelete();
            // cascade: soft-delete in practice; force-delete would remove the usage rows too
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->unsignedInteger('quantity');   // whole units — never negative
            $table->date('usage_date');
            $table->text('notes')->nullable();
            $table->foreignId('used_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_usages');
    }
};
