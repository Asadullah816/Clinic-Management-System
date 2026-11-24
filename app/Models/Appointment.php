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
}
