<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImplantSale extends Model
{
    use HasFactory;

    const STATUS_PAID = 'paid';

    const STATUS_PARTIAL = 'partial';

    const STATUS_PENDING = 'pending';

    protected $fillable = [
        'sale_number',
        'implant_buyer_id',
        'sale_date',
        'subtotal',
        'discount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'patient_reference',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_amount' => 'decimal:2',
        ];
    }

    public function recalculatePaymentStatus(): string
    {
        if ($this->due_amount <= 0) {
            return self::STATUS_PAID;
        }

        return $this->paid_amount > 0 ? self::STATUS_PARTIAL : self::STATUS_PENDING;
    }

    public function statusLabel(): string
    {
        return match ($this->payment_status) {
            self::STATUS_PAID => 'Paid',
            self::STATUS_PARTIAL => 'Partial',
            default => 'Pending',
        };
    }

    public function statusColor(): string
    {
        return match ($this->payment_status) {
            self::STATUS_PAID => 'success',
            self::STATUS_PARTIAL => 'warning',
            default => 'danger',
        };
    }

    /**
     * WhatsApp reminder URL for this specific implant sale invoice.
     */
    public function whatsappReminderUrl(): ?string
    {
        $phone = $this->buyer?->phone;
        if (empty($phone) || $this->buyer?->is_internal || $this->due_amount <= 0) {
            return null;
        }

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
        $doctorName = $this->buyer?->doctor_name ?? 'Doctor';
        $saleNo = $this->sale_number;
        $saleDate = $this->sale_date?->format('d M Y') ?? '';
        $total = number_format((float) $this->total_amount, 2);
        $paid = number_format((float) $this->paid_amount, 2);
        $due = number_format((float) $this->due_amount, 2);

        $itemsSummary = '';
        foreach ($this->items as $item) {
            $implantName = $item->implant?->fullSpecification() ?? 'Dental Implant';
            $itemsSummary .= "  • {$item->quantity}x {$implantName}\n";
        }

        $message = "Respected {$doctorName},\n\n"
            ."This is a polite invoice reminder from *{$clinicName}* (Dental Implants & Surgery) regarding Sale Invoice *#{$saleNo}* ({$saleDate}).\n\n"
            ."📦 *Supplied Implants:*\n"
            .$itemsSummary."\n"
            ."💰 *Financial Details:*\n"
            ."• Total Amount: PKR {$total}\n"
            ."• Paid Amount: PKR {$paid}\n"
            ."• *Outstanding Balance Due: PKR {$due}*\n\n"
            ."Kindly arrange the payment settlement at your earliest convenience.\n"
            ."For payment details or queries, contact: {$clinicPhone}.\n\n"
            ."Thank you,\n"
            ."*{$clinicName}*";

        return 'https://wa.me/'.$clean.'?text='.rawurlencode($message);
    }

    public function buyer()
    {
        return $this->belongsTo(ImplantBuyer::class, 'implant_buyer_id');
    }

    public function items()
    {
        return $this->hasMany(ImplantSaleItem::class);
    }

    public function failedImplants()
    {
        return $this->hasMany(FailedImplant::class);
    }

    public function totalFailedQuantity(): int
    {
        return (int) $this->failedImplants()->sum('quantity');
    }

    public function totalFailedCost(): float
    {
        return (float) $this->failedImplants()->sum('total_cost');
    }

    public function payments()
    {
        return $this->hasMany(ImplantPayment::class)->latest();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
