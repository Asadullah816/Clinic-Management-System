<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Patient;
use App\Models\Treatment;
use App\Models\Medicine;
use App\Models\User;
class MedicineUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'treatment_id',
        'medicine_id',
        'quantity',
        'usage_date',
        'notes',
        'used_by',
    ];

    protected function casts(): array
    {
        return [
            'usage_date' => 'date',
        ];
    }

    // ---- Relationships ----

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // withTrashed: historical records survive catalog deletion — same as PatientTreatment
    public function treatment()
    {
        return $this->belongsTo(Treatment::class)->withTrashed();
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class)->withTrashed();
    }

    public function usedBy()
    {
        return $this->belongsTo(User::class, 'used_by');
    }
}
