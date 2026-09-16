<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Patient;
use App\Models\Invoice;
use App\Models\User;
class Payment extends Model
{
    use HasFactory;

    const METHOD_CASH   = 'cash';
    const METHOD_BANK   = 'bank';
    const METHOD_CARD   = 'card';
    const METHOD_ONLINE = 'online';
    const METHOD_OTHER  = 'other';

    protected $fillable = [
        'patient_id',
        'invoice_id',
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
            'amount'       => 'decimal:2',
        ];
    }

    /**
     * "RCP-0001" — display-only receipt number for the printable receipt.
     * Not stored; derived from the id like patient/invoice numbers, but without a column.
     */
    public function receiptNumber(): string
    {
        return 'RCP-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

    public static function methods(): array
    {
        return [
            self::METHOD_CASH   => 'Cash',
            self::METHOD_BANK   => 'Bank Transfer',
            self::METHOD_CARD   => 'Card',
            self::METHOD_ONLINE => 'Online',
            self::METHOD_OTHER  => 'Other',
        ];
    }

    public function methodLabel(): string
    {
        return self::methods()[$this->payment_method] ?? $this->payment_method;
    }

    // ---- Relationships ----

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
