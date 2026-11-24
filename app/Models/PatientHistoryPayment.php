<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientHistoryPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_history_id',
        'patient_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference',
        'notes',
        'received_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function patientHistory()
    {
        return $this->belongsTo(PatientHistory::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
