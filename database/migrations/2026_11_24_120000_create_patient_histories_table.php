<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add is_historical flag to patients table if not already present
        if (! Schema::hasColumn('patients', 'is_historical')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->boolean('is_historical')->default(false)->after('status');
            });
        }

        // 2. Create patient_histories table (Pre-App Historical Records)
        Schema::create('patient_histories', function (Blueprint $table) {
            $table->id();
            $table->string('record_number')->unique(); // e.g. HIST-0001
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('doctor_name')->nullable();
            $table->date('visit_date'); // Date attended before software launch
            $table->text('procedures'); // Procedures/treatments performed in the past
            $table->string('tooth_numbers')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('total_cost', 12, 2)->default(0.00);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->decimal('due_amount', 12, 2)->default(0.00);
            $table->string('status')->default('pending'); // 'paid', 'partial', 'pending'
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Create patient_history_payments table (Payments against historical dues)
        Schema::create('patient_history_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_history_id')->constrained('patient_histories')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('cash'); // cash, bank, card, online, other
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_history_payments');
        Schema::dropIfExists('patient_histories');

        if (Schema::hasColumn('patients', 'is_historical')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->dropColumn('is_historical');
            });
        }
    }
};
