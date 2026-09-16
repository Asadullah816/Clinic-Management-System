<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Patient;
use App\Models\User;
use App\Models\Payment;
class Invoice extends Model
{
    use HasFactory;

    const STATUS_PAID    = 'paid';
    const STATUS_PARTIAL = 'partial';
    const STATUS_PENDING = 'pending';

    protected $fillable = [
        'patient_id',
        'invoice_number',
        'invoice_date',
        'subtotal',
        'discount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'subtotal'     => 'decimal:2',
            'discount'     => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount'  => 'decimal:2',
            'due_amount'   => 'decimal:2',
        ];
    }

    /**
     * THE status rule — derived from the numbers, never chosen by a user.
     * Called by InvoiceController (store/update) and later by PaymentController.
     */
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
            self::STATUS_PAID    => 'Paid',
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
            self::STATUS_PAID    => 'success',
            self::STATUS_PARTIAL => 'warning',
            default              => 'danger', // pending
        };
    }

    // ---- Relationships ----
    // (hasMany payments arrives with the Payment model in Phase 8)

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

        public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
