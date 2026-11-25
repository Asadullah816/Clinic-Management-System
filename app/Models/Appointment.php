<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    const STATUS_SCHEDULED = 'scheduled';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_NO_SHOW = 'no_show';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'treatment_id',
        'appointment_date',
        'appointment_time',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
        ];
    }

    /**
     * MySQL stores "14:30:00" — trim to "14:30" for display and form repopulation.
     */
    public function getTimeAttribute($value)
    {
        return substr($value, 0, 5);
    }

    /**
     * Return appointment time in 12-hour format with AM/PM (e.g. "08:00 PM").
     */
    public function getFormattedTimeAttribute(): string
    {
        $time = $this->attributes['appointment_time'] ?? null;
        if (empty($time)) {
            return '';
        }

        try {
            return Carbon::parse($time)->format('h:i A');
        } catch (\Throwable $e) {
            return substr($time, 0, 5);
        }
    }

    /**
     * All statuses with readable labels — used in dropdowns and filters.
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_NO_SHOW => 'No Show',
        ];
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    /**
     * Bootstrap color class for the status badge.
     */
    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_NO_SHOW => 'warning',
            default => 'primary', // scheduled
        };
    }

    // ---- Relationships ----

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function treatment()
    {
        return $this->belongsTo(Treatment::class)->withTrashed();
    }

    public function treatments()
    {
        return $this->belongsToMany(Treatment::class, 'appointment_treatments')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function getTreatmentNamesAttribute(): string
    {
        if ($this->relationLoaded('treatments') && $this->treatments->isNotEmpty()) {
            return $this->treatments->pluck('name')->implode(', ');
        }
        if ($this->treatments()->exists()) {
            return $this->treatments->pluck('name')->implode(', ');
        }

        return $this->treatment?->name ?? '—';
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate 1-click WhatsApp appointment reminder URL
     */
    public function whatsappReminderUrl(): ?string
    {
        // Reminders are ONLY valid for scheduled appointments; completed/cancelled/no-show have no reminder
        if ($this->status !== self::STATUS_SCHEDULED) {
            return null;
        }

        $phone = $this->patient?->phone;
        if (empty($phone)) {
            return null;
        }

        // Clean phone number for wa.me international format
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '03') && strlen($clean) === 11) {
            $clean = '92'.substr($clean, 1);
        } elseif (str_starts_with($clean, '0092')) {
            $clean = substr($clean, 2);
        } elseif (str_starts_with($clean, '0')) {
            $clean = '92'.substr($clean, 1);
        } elseif (! str_starts_with($clean, '92')) {
            $clean = '92'.$clean;
        }

        if (empty($clean)) {
            return null;
        }

        $clinicName = Setting::get('clinic_name', config('app.name', 'Dr Badshah Afsar'));
        $patientName = $this->patient?->full_name ?? 'Valued Patient';
        $appointmentDate = $this->appointment_date?->format('l, d M Y') ?? '';
        $appointmentTime = $this->formatted_time;
        $doctorName = $this->doctor?->name ? 'Dr. '.$this->doctor->name : 'Clinic Specialist';
        $treatments = $this->treatment_names ?: 'General Consultation / Dental Checkup';

        $message = "Dear {$patientName},\n\n"
            ."This is an appointment reminder from *{$clinicName}* regarding your upcoming visit:\n\n"
            ."📅 *Date:* {$appointmentDate}\n"
            ."⏰ *Time:* {$appointmentTime}\n"
            ."👨‍⚕️ *Doctor:* {$doctorName}\n"
            ."🦷 *Treatment:* {$treatments}\n\n"
            ."Please arrive 5 to 10 minutes prior to your scheduled time. If you need to reschedule or have any questions, kindly reply to this message.\n\n"
            ."Thank you,\n"
            ."*{$clinicName}*";

        return 'https://wa.me/'.$clean.'?text='.rawurlencode($message);
    }
}
