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
        // 1. Dental Implants Catalog / Inventory
        Schema::create('implants', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('name');
            $table->string('brand')->nullable(); // Optional brand name (e.g. Straumann, Osstem, Dentium)
            $table->string('diameter')->nullable(); // Diameter (e.g. 3.5mm, 4.0mm, 4.5mm)
            $table->string('length')->nullable(); // Length (e.g. 8mm, 10mm, 11.5mm)
            $table->string('platform')->nullable(); // Platform/Connection (e.g. Regular, Conical, Internal Hex)
            $table->string('connection_type')->nullable();
            $table->string('material')->nullable(); // Material (e.g. Grade 4 Titanium, Roxolid)
            $table->string('lot_number')->nullable(); // Batch/Lot #
            $table->decimal('purchase_price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock_alert')->default(5);
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('active'); // active, inactive
            $table->timestamps();
        });

        // 2. Implant Buyers (External Doctors / Clinics or In-House Self-Use)
        Schema::create('implant_buyers', function (Blueprint $table) {
            $table->id();
            $table->string('doctor_name');
            $table->string('clinic_hospital_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_internal')->default(false); // True for internal clinic / admin self-use
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        // 3. Implant Sales / Invoices
        Schema::create('implant_sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_number')->unique();
            $table->foreignId('implant_buyer_id')->constrained('implant_buyers')->cascadeOnDelete();
            $table->date('sale_date');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->string('payment_status', 20)->default('pending'); // paid, partial, pending
            $table->string('patient_reference')->nullable(); // Reference patient name
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 4. Implant Sale Items
        Schema::create('implant_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('implant_sale_id')->constrained('implant_sales')->cascadeOnDelete();
            $table->foreignId('implant_id')->constrained('implants')->restrictOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. Implant Payments
        Schema::create('implant_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('implant_sale_id')->constrained('implant_sales')->cascadeOnDelete();
            $table->foreignId('implant_buyer_id')->constrained('implant_buyers')->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('payment_method', 50)->default('cash'); // cash, bank_transfer, online, cheque
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 6. Implant Stock Transactions (Audit Trail)
        Schema::create('implant_stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('implant_id')->constrained('implants')->cascadeOnDelete();
            $table->string('type', 30); // restock, sale, adjustment, return
            $table->integer('quantity'); // positive for addition, negative for reduction
            $table->integer('balance_after');
            $table->unsignedBigInteger('reference_id')->nullable();
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
        Schema::dropIfExists('implant_stock_transactions');
        Schema::dropIfExists('implant_payments');
        Schema::dropIfExists('implant_sale_items');
        Schema::dropIfExists('implant_sales');
        Schema::dropIfExists('implant_buyers');
        Schema::dropIfExists('implants');
    }
};
