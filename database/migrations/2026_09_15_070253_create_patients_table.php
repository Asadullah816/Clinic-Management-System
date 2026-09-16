<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_number')->unique()->nullable();   // auto-generated: P-0001
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');                     // male, female, other
            $table->date('date_of_birth')->nullable();
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact', 30)->nullable();
            $table->string('occupation')->nullable();
            $table->text('allergies')->nullable();
            $table->string('skin_type')->nullable();
            $table->text('medical_notes')->nullable();
            $table->string('referred_by')->nullable();
            $table->string('status')->default('active');  // active, inactive
            $table->timestamps();
            $table->softDeletes();                        // patients are never hard-deleted
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
