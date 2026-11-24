<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Implant extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'name',
        'brand',
        'diameter',
        'length',
        'platform',
        'connection_type',
        'material',
        'lot_number',
        'purchase_price',
        'sale_price',
        'stock_quantity',
        'min_stock_alert',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'min_stock_alert' => 'integer',
        ];
    }

    /**
     * Complete specification string (e.g. "Straumann BLX Ø 3.5mm × 10mm (Regular CrossFit)").
     */
    public function fullSpecification(): string
    {
        $parts = [];
        if ($this->brand) {
            $parts[] = $this->brand;
        }
        $parts[] = $this->name;

        $dim = [];
        if ($this->diameter) {
            $dim[] = 'Ø '.$this->diameter;
        }
        if ($this->length) {
            $dim[] = 'L: '.$this->length;
        }
        if (! empty($dim)) {
            $parts[] = '('.implode(' × ', $dim).')';
        }

        if ($this->platform) {
            $parts[] = '['.$this->platform.']';
        }

        return implode(' ', $parts);
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= $this->min_stock_alert;
    }

    public function stockStatusLabel(): string
    {
        if ($this->isOutOfStock()) {
            return 'Out of Stock';
        }
        if ($this->isLowStock()) {
            return 'Low Stock ('.$this->stock_quantity.')';
        }

        return 'In Stock ('.$this->stock_quantity.')';
    }

    public function stockStatusColor(): string
    {
        if ($this->isOutOfStock()) {
            return 'danger';
        }
        if ($this->isLowStock()) {
            return 'warning';
        }

        return 'success';
    }

    public function saleItems()
    {
        return $this->hasMany(ImplantSaleItem::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(ImplantStockTransaction::class)->latest();
    }
}
