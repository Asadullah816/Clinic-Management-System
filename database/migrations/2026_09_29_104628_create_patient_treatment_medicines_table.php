<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('patient_treatments', function (Blueprint $table) {
            $table->decimal('medicine_price', 10, 2)->default(0)->after('price');
            $table->decimal('medicine_discount', 10, 2)->default(0)->after('discount');
            $table->decimal('medicine_total', 10, 2)->default(0)->after('medicine_discount');
        });

        Schema::create('patient_treatment_medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_treatment_id')->constrained('patient_treatments')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_treatment_medicines');

        Schema::table('patient_treatments', function (Blueprint $table) {
            $table->dropColumn(['medicine_price', 'medicine_discount', 'medicine_total']);
        });
    }
};
