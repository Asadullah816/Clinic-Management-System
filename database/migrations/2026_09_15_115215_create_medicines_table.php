<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('generic_name')->nullable();
            // restrictOnDelete: a category with products can never be force-deleted
            $table->foreignId('medicine_category_id')->constrained('medicine_categories')->restrictOnDelete();
            // nullable + nullOnDelete: losing a supplier must not endanger products
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('unit', 20)->nullable();       // pcs, tube, bottle, box...
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->unsignedInteger('stock_quantity')->default(0);  // never negative
            $table->unsignedInteger('minimum_stock')->default(0);   // low-stock threshold
            $table->date('expiry_date')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');  // active, inactive
            $table->timestamps();
            $table->softDeletes();                        // products are referenced everywhere
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
