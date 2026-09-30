<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaserTreatment extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ACTIVE = 'active';

    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'code',
        'body_area',
        'price',
        'duration',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration' => 'integer',
        ];
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(LaserSession::class, 'laser_treatment_id');
    }

    public function statusColor(): string
    {
        return $this->status === self::STATUS_ACTIVE ? 'success' : 'secondary';
    }

    public static function commonBodyAreas(): array
    {
        return [
            'Full Body',
            'Full Face',
            'Upper Lip & Chin',
            'Beard Shaping',
            'Underarms',
            'Arms (Full)',
            'Arms (Half)',
            'Legs (Full)',
            'Legs (Half)',
            'Chest',
            'Back',
            'Bikini / Intimate',
            'Neck & Shoulders',
            'Hands / Feet',
        ];
    }
}
