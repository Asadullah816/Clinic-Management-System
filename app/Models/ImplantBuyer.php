<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImplantBuyer extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_name',
        'clinic_hospital_name',
        'phone',
        'email',
        'address',
        'is_internal',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    public function displayName(): string
    {
        if ($this->is_internal) {
            return 'Dr Badshah Afsar (In-House Clinic / Self-Use)';
        }

        return $this->doctor_name.($this->clinic_hospital_name ? ' — '.$this->clinic_hospital_name : '');
    }

    public function totalPurchases(): float
    {
        return (float) $this->sales()->sum('total_amount');
    }

    public function totalPaid(): float
    {
        return (float) $this->sales()->sum('paid_amount');
    }

    public function totalDue(): float
    {
        return (float) $this->sales()->sum('due_amount');
    }

    /**
     * WhatsApp reminder URL for general doctor outstandings.
     */
    public function whatsappReminderUrl(): ?string
    {
        if (empty($this->phone) || $this->is_internal) {
            return null;
        }

        $due = $this->totalDue();
        if ($due <= 0) {
            return null;
        }

        $clean = preg_replace('/[^0-9]/', '', $this->phone);
        if (str_starts_with($clean, '03') && strlen($clean) === 11) {
            $clean = '92'.substr($clean, 1);
        } elseif (str_starts_with($clean, '0092')) {
            $clean = substr($clean, 2);
        }

        if (empty($clean)) {
            return null;
        }

        $clinicName = Setting::get('clinic_name', config('app.name', 'Dr Badshah Afsar'));
        $clinicPhone = Setting::get('phone', '03489030035');
        $doctorName = $this->doctor_name;
        $dueFormatted = number_format($due, 2);

        $message = "Respected {$doctorName},\n\n"
            ."Greetings from *{$clinicName}* (Dental Implants & Surgery).\n\n"
            ."This is a polite reminder regarding your outstanding balance for dental implants supply.\n\n"
            ."💼 *Current Outstanding Balance: PKR {$dueFormatted}*\n\n"
            ."Kindly arrange payment clearance at your convenience. For statement details or inquiries, please contact: {$clinicPhone}.\n\n"
            ."Thank you,\n"
            ."*{$clinicName}*";

        return 'https://wa.me/'.$clean.'?text='.rawurlencode($message);
    }

    public function sales()
    {
        return $this->hasMany(ImplantSale::class)->latest();
    }

    public function payments()
    {
        return $this->hasMany(ImplantPayment::class)->latest();
    }
}
