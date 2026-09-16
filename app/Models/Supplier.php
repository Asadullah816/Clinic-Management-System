<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company',
        'phone',
        'email',
        'address',
        'notes',
        'status',
    ];

    public function medicines()
    {
        return $this->hasMany(Medicine::class);
    }

    // Table arrives in Phase 10
    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }
}
