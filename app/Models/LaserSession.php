<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaserSession extends Model
{
    use HasFactory;

    const STATUS_PAID = 'paid';

    const STATUS_PARTIAL = 'partial';

    const STATUS_PENDING = 'pending';

    const METHOD_CASH = 'cash';

    const METHOD_BANK = 'bank';

    const METHOD_CARD = 'card';

    const METHOD_ONLINE = 'online';

    const METHOD_OTHER = 'other';

    protected $fillable = [
        'laser_patient_id',
        'laser_treatment_id',
        'invoice_number',
        'session_date',
        'session_number',
        'total_sessions',
        'laser_machine',
        'fluence',
        'pulse_width',
        'spot_size',
        'pulses_count',
        'price',
        'discount_type',
        'discount_percentage',
        'discount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'status',
        'payment_method',
        'payment_reference',
        'notes',
        'performed_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'session_number' => 'integer',
            'total_sessions' => 'integer',
            'pulses_count' => 'integer',
            'price' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'discount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(LaserPatient::class, 'laser_patient_id');
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(LaserTreatment::class, 'laser_treatment_id')->withTrashed();
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recalculatePaymentStatus(): string
    {
        if ($this->due_amount <= 0) {
            return self::STATUS_PAID;
        }

        return $this->paid_amount > 0
            ? self::STATUS_PARTIAL
            : self::STATUS_PENDING;
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PAID => 'Paid',
            self::STATUS_PARTIAL => 'Partial',
            self::STATUS_PENDING => 'Pending',
        ];
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_PAID => 'success',
            self::STATUS_PARTIAL => 'warning',
            default => 'danger',
        };
    }

    public static function methods(): array
    {
        return [
            self::METHOD_CASH => 'Cash',
            self::METHOD_BANK => 'Bank Transfer',
            self::METHOD_CARD => 'Card',
            self::METHOD_ONLINE => 'Online',
            self::METHOD_OTHER => 'Other',
        ];
    }

    public function methodLabel(): string
    {
        return self::methods()[$this->payment_method] ?? ($this->payment_method ?? '—');
    }
}
