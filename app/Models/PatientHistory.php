<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientHistory extends Model
{
    use HasFactory;

    const STATUS_PAID = 'paid';

    const STATUS_PARTIAL = 'partial';

    const STATUS_PENDING = 'pending';

    protected $fillable = [
        'record_number',
        'patient_id',
        'doctor_id',
        'doctor_name',
        'visit_date',
        'procedures',
        'tooth_numbers',
        'diagnosis',
        'notes',
        'total_cost',
        'paid_amount',
        'due_amount',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'total_cost' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
        ];
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments()
    {
        return $this->hasMany(PatientHistoryPayment::class);
    }

    public function getDoctorDisplayNameAttribute(): string
    {
        return $this->doctor?->name ?: ($this->doctor_name ?: 'Lead Dentist');
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PAID => 'Paid',
            self::STATUS_PARTIAL => 'Partial Due',
            self::STATUS_PENDING => 'Unpaid / Pending',
        ];
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? ucfirst($this->status);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'success',
            self::STATUS_PARTIAL => 'warning',
            default => 'danger',
        };
    }

    public function scopeOutstanding($query)
    {
        return $query->where('due_amount', '>', 0);
    }

    public function scopeForDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Generate 1-click WhatsApp message reminder for past historical outstandings.
     */
    public function whatsappReminderUrl(): ?string
    {
        if (empty($this->patient?->phone) || $this->due_amount <= 0) {
            return null;
        }

        $rawPhone = preg_replace('/[^0-9]/', '', $this->patient->phone);
        if (str_starts_with($rawPhone, '0')) {
            $cleanPhone = '92'.substr($rawPhone, 1);
        } elseif (str_starts_with($rawPhone, '92')) {
            $cleanPhone = $rawPhone;
        } else {
            $cleanPhone = '92'.$rawPhone;
        }

        $clinicName = Setting::get('clinic_name', 'Dr Badshah Afsar Dental Surgery & Care');
        $patientName = $this->patient->full_name;
        $dueFormatted = number_format($this->due_amount, 2);
        $totalFormatted = number_format($this->total_cost, 2);
        $paidFormatted = number_format($this->paid_amount, 2);
        $visitDate = $this->visit_date ? $this->visit_date->format('d M Y') : 'previous visit';

        $message = "Dear {$patientName},\n\n"
            ."Greetings from {$clinicName}.\n"
            ."This is a reminder regarding your previous dental treatment history records from {$visitDate}.\n\n"
            ."*Record #:* {$this->record_number}\n"
            ."*Procedures:* {$this->procedures}\n"
            ."*Total Amount:* PKR {$totalFormatted}\n"
            ."*Paid So Far:* PKR {$paidFormatted}\n"
            ."*Outstanding Due:* PKR {$dueFormatted}\n\n"
            ."Kindly arrange payment at your earliest convenience. If you have already paid, please ignore this message.\n\n"
            ."Thank you,\n{$clinicName}";

        return 'https://wa.me/'.$cleanPhone.'?text='.urlencode($message);
    }
}
