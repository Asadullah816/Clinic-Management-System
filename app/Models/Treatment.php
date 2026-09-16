<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Patient;
use App\Models\Treatment;
use App\Models\User;
use App\Models\Appointment;
use App\Models\PatientTreatment;
class Treatment extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    // ---- Relationships (full map completed in Phase 6) ----

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }


        public function patientTreatments()
    {
        return $this->hasMany(PatientTreatment::class);
    }

    // Table arrives in Phase 11 — same early-declaration pattern as Patient.
    public function medicineUsages()
    {
        return $this->hasMany(MedicineUsage::class);
    }
}
