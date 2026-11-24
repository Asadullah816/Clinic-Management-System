<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    // ---- Status constants ----
    const STATUS_ACTIVE = 'active';

    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'patient_number',
        'assigned_doctor_id',
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
        'is_historical',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date', // lets us use $patient->date_of_birth->age etc.
            'is_historical' => 'boolean',
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

    public function assignedDoctor()
    {
        return $this->belongsTo(User::class, 'assigned_doctor_id');
    }

    public function scopeForDoctor($query, int $doctorId)
    {
        return $query->where(function ($q) use ($doctorId) {
            $q->where('assigned_doctor_id', $doctorId)
                ->orWhereHas('appointments', fn ($a) => $a->where('doctor_id', $doctorId))
                ->orWhereHas('patientTreatments', fn ($t) => $t->where('doctor_id', $doctorId))
                ->orWhereHas('patientHistories', fn ($h) => $h->where('doctor_id', $doctorId));
        });
    }

    public function patientHistories()
    {
        return $this->hasMany(PatientHistory::class);
    }

    public function patientHistoryPayments()
    {
        return $this->hasMany(PatientHistoryPayment::class);
    }

    public function getHistoricalTotalAmountAttribute(): float
    {
        return (float) $this->patientHistories()->sum('total_cost');
    }

    public function getHistoricalPaidAmountAttribute(): float
    {
        return (float) $this->patientHistories()->sum('paid_amount');
    }

    public function getHistoricalDueAmountAttribute(): float
    {
        return (float) $this->patientHistories()->sum('due_amount');
    }

    public function getCombinedTotalDueAttribute(): float
    {
        return $this->historical_due_amount + (float) $this->invoices()->sum('due_amount');
    }

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
