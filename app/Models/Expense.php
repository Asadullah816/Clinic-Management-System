<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\ExpenseCategory;
use App\Models\User;
class Expense extends Model
{
    use HasFactory;

    // Same payment methods as payments — money leaves the clinic the same ways it arrives
    const METHOD_CASH   = 'cash';
    const METHOD_BANK   = 'bank';
    const METHOD_CARD   = 'card';
    const METHOD_ONLINE = 'online';
    const METHOD_OTHER  = 'other';

    protected $fillable = [
        'expense_category_id',
        'title',
        'amount',
        'expense_date',
        'payment_method',
        'reference',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount'       => 'decimal:2',
        ];
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

    public function expenseCategory()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
