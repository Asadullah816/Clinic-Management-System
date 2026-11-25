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
        Schema::create('failed_implants', function (Blueprint $table) {
            $table->id();
            $table->string('failure_number')->unique();
            $table->foreignId('implant_id')->constrained('implants')->cascadeOnDelete();
            $table->foreignId('implant_buyer_id')->nullable()->constrained('implant_buyers')->nullOnDelete();
            $table->foreignId('implant_sale_id')->nullable()->constrained('implant_sales')->nullOnDelete();
            $table->date('failure_date');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->string('source', 30)->default('sale'); // sale, stock, internal
            $table->string('failure_stage', 50)->default('surgical_placement'); // surgical_placement, osseointegration_early, post_restoration, packaging_defect, other
            $table->string('failure_reason', 100); // lack_of_stability, osseointegration_failure, contamination, infection, bone_resorption, mechanical_fracture, other
            $table->string('patient_name')->nullable();
            $table->string('tooth_number')->nullable();
            $table->string('warranty_status', 50)->default('pending_claim'); // pending_claim, vendor_replaced, clinic_loss, doctor_absorbed, credit_note
            $table->foreignId('replacement_implant_id')->nullable()->constrained('implants')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('failed_implants');
    }
};
