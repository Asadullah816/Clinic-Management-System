<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('duration')->nullable(); // minutes
            $table->string('status')->default('active');     // active, inactive
            $table->timestamps();
            $table->softDeletes();                           // treatments are referenced everywhere
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
