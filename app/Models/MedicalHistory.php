<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Patient;
use App\Models\User;
class MedicalHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'visit_date',
        'chief_complaint',
        'diagnosis',
        'previous_treatment',
        'allergies',
        'medical_conditions',
        'current_medications',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
