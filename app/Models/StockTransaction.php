<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Medicine;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Expense;
class StockTransaction extends Model
{
    use HasFactory;

    const TYPE_IN  = 'in';
    const TYPE_OUT = 'out';

    protected $fillable = [
        'medicine_id',
        'type',
        'quantity',
        'transaction_date',
        'supplier_id',
        'unit_cost',
        'reference',
        'expense_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'unit_cost'        => 'decimal:2',
        ];
    }

    public static function types(): array
    {
        return [
            self::TYPE_IN  => 'Stock In',
            self::TYPE_OUT => 'Stock Out',
        ];
    }

    public function typeLabel(): string
    {
        return self::types()[$this->type] ?? $this->type;
    }

    public function typeColor(): string
    {
        return $this->type === self::TYPE_IN ? 'success' : 'danger';
    }

    /**
     * Purchase total (quantity × unit cost), or null for stock-outs.
     */
    public function totalCost(): ?float
    {
        return $this->unit_cost !== null
            ? (float) $this->quantity * (float) $this->unit_cost
            : null;
    }

    // ---- Relationships ----

    public function medicine()
    {
        // withTrashed: the ledger survives product deletion and keeps the name visible
        return $this->belongsTo(Medicine::class)->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
        /**
     * The expense auto-created along with this purchase (Stock In only).
     */
    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }
}
