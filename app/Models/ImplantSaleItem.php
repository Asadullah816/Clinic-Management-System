<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImplantSaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'implant_sale_id',
        'implant_id',
        'quantity',
        'unit_price',
        'total_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function sale()
    {
        return $this->belongsTo(ImplantSale::class, 'implant_sale_id');
    }

    public function implant()
    {
        return $this->belongsTo(Implant::class);
    }
}
