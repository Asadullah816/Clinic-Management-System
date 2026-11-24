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
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('doctor_id')->nullable()->after('patient_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('patient_treatments', function (Blueprint $table) {
            $table->foreignId('doctor_id')->nullable()->after('treatment_id')->constrained('users')->nullOnDelete();
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->foreignId('assigned_doctor_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropForeign(['assigned_doctor_id']);
            $table->dropColumn('assigned_doctor_id');
        });

        Schema::table('patient_treatments', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->dropColumn('doctor_id');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->dropColumn('doctor_id');
        });
    }
};
