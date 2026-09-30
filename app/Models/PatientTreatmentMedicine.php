<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientTreatmentMedicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_treatment_id',
        'medicine_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function patientTreatment()
    {
        return $this->belongsTo(PatientTreatment::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class)->withTrashed();
    }
}
