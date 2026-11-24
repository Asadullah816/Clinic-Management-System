<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImplantPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'implant_sale_id',
        'implant_buyer_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function sale()
    {
        return $this->belongsTo(ImplantSale::class, 'implant_sale_id');
    }

    public function buyer()
    {
        return $this->belongsTo(ImplantBuyer::class, 'implant_buyer_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
