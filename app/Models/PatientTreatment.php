<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientTreatment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'treatment_id',
        'treatment_date',
        'price',
        'discount',
        'medicine_price',
        'medicine_discount',
        'medicine_total',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'treatment_date' => 'date',
            'price' => 'decimal:2',
            'discount' => 'decimal:2',
            'medicine_price' => 'decimal:2',
            'medicine_discount' => 'decimal:2',
            'medicine_total' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * withTrashed: if the catalog treatment is ever soft-deleted,
     * historical records still display its name.
     */
    public function treatment()
    {
        return $this->belongsTo(Treatment::class)->withTrashed();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function treatmentMedicines()
    {
        return $this->hasMany(PatientTreatmentMedicine::class);
    }

    public function getTreatmentNetAttribute(): float
    {
        return max(0, (float) $this->price - (float) $this->discount);
    }

    public function getMedicineNetAttribute(): float
    {
        return max(0, (float) $this->medicine_price - (float) $this->medicine_discount);
    }
}
