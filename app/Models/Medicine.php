<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\MedicineCategory;
use App\Models\Supplier;
class Medicine extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'generic_name',
        'medicine_category_id',
        'supplier_id',
        'unit',
        'purchase_price',
        'selling_price',
        'stock_quantity',
        'minimum_stock',
        'expiry_date',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date'    => 'date',
            'purchase_price' => 'decimal:2',
            'selling_price'  => 'decimal:2',
        ];
    }

    /**
     * Common units for the dropdown.
     */
    public static function units(): array
    {
        return ['pcs', 'tube', 'bottle', 'box', 'sachet', 'strip', 'ml', 'g'];
    }

    // ---- Stock / expiry status helpers (used by list badges, dashboard, reports) ----

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->minimum_stock;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity === 0;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(): bool
    {
        return ! $this->isExpired()
            && $this->expiry_date !== null
            && $this->expiry_date->diffInDays(now()) <= 30;
    }

    // ---- Relationships ----

    public function category()
    {
        return $this->belongsTo(MedicineCategory::class, 'medicine_category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Tables arrive in Phase 10 / Phase 11 — same early-declaration pattern as Patient
    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function medicineUsages()
    {
        return $this->hasMany(MedicineUsage::class);
    }
}
