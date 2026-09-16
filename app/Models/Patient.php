<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    // ---- Status constants ----
    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'patient_number',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'phone',
        'email',
        'address',
        'emergency_contact',
        'occupation',
        'allergies',
        'skin_type',
        'medical_notes',
        'referred_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date', // lets us use $patient->date_of_birth->age etc.
        ];
    }

    /**
     * "John Doe" — used all over the views.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // ---- Relationships (their tables arrive in later phases) ----

    public function medicalHistories()
    {
        return $this->hasMany(MedicalHistory::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function patientTreatments()
    {
        return $this->hasMany(PatientTreatment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function medicineUsages()
    {
        return $this->hasMany(MedicineUsage::class);
    }
}
