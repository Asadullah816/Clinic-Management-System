<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaserExpense extends Model
{
    use HasFactory;

    const METHOD_CASH = 'cash';

    const METHOD_BANK = 'bank';

    const METHOD_CARD = 'card';

    const METHOD_ONLINE = 'online';

    const METHOD_OTHER = 'other';

    protected $fillable = [
        'laser_expense_category_id',
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
            'amount' => 'decimal:2',
        ];
    }

    public static function methods(): array
    {
        return [
            self::METHOD_CASH => 'Cash',
            self::METHOD_BANK => 'Bank Transfer',
            self::METHOD_CARD => 'Card',
            self::METHOD_ONLINE => 'Online',
            self::METHOD_OTHER => 'Other',
        ];
    }

    public function methodLabel(): string
    {
        return self::methods()[$this->payment_method] ?? $this->payment_method;
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(LaserExpenseCategory::class, 'laser_expense_category_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
