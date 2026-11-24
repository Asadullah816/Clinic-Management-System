<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    const STATUS_PAID = 'paid';

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
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
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

    public function getRouteKey(): mixed
    {
        return $this->invoice_number ?? (string) $this->id;
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        if (is_string($value) && str_starts_with(strtoupper($value), 'INV-')) {
            return $this->where('invoice_number', $value)->firstOrFail();
        }

        if (is_numeric($value)) {
            return $this->where('id', (int) $value)->firstOrFail();
        }

        return $this->where($field ?? 'invoice_number', $value)->firstOrFail();
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
            default => 'danger', // pending
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

    /**
     * Generate a WhatsApp reminder link using wa.me with prefilled message details.
     */
    public function whatsappReminderUrl(): ?string
    {
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
        }

        if (empty($clean)) {
            return null;
        }

        $clinicName = Setting::get('clinic_name', config('app.name', 'Dr Badshah Afsar'));
        $clinicPhone = Setting::get('phone', '03489030035');
        $patientName = $this->patient?->full_name ?? 'Valued Patient';
        $invoiceNo = $this->invoice_number;
        $invoiceDate = $this->invoice_date?->format('d M Y') ?? '';
        $total = number_format((float) $this->total_amount, 2);
        $paid = number_format((float) $this->paid_amount, 2);
        $due = number_format((float) $this->due_amount, 2);

        $message = "Dear {$patientName},\n\n"
            ."This is a gentle payment reminder from *{$clinicName}* regarding your dental treatment invoice *#{$invoiceNo}* ({$invoiceDate}).\n\n"
            ."📋 *Invoice Summary:*\n"
            ."• Total Amount: PKR {$total}\n"
            ."• Paid Amount: PKR {$paid}\n"
            ."• *Outstanding Balance Due: PKR {$due}*\n\n"
            ."Kindly arrange to clear the outstanding dues at your earliest convenience. If you have already paid, please ignore this message.\n\n"
            ."For queries or assistance, contact: {$clinicPhone}.\n\n"
            ."Thank you,\n"
            ."*{$clinicName}*";

        return 'https://wa.me/'.$clean.'?text='.rawurlencode($message);
    }
}
