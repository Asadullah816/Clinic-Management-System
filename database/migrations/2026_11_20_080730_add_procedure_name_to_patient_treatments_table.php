<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_treatments', function (Blueprint $table) {
            $table->string('procedure_name')->nullable()->after('treatment_id');
        });
    }

    public function down(): void
    {
        Schema::table('patient_treatments', function (Blueprint $table) {
            $table->dropColumn('procedure_name');
        });
    }
};
