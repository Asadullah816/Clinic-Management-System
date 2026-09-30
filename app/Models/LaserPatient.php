<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LaserPatient extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ACTIVE = 'active';

    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'patient_number',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'phone',
        'email',
        'address',
        'emergency_contact',
        'skin_type',
        'medical_notes',
        'referred_by',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? (int) $this->date_of_birth->age : null;
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(LaserSession::class, 'laser_patient_id')->orderByDesc('session_date')->orderByDesc('id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function totalSpent(): float
    {
        return (float) $this->sessions()->sum('paid_amount');
    }

    public function totalDue(): float
    {
        return (float) $this->sessions()->sum('due_amount');
    }

    public function statusColor(): string
    {
        return $this->status === self::STATUS_ACTIVE ? 'success' : 'secondary';
    }

    public static function fitzpatrickTypes(): array
    {
        return [
            'Type I' => 'Type I (Very fair, always burns, never tans)',
            'Type II' => 'Type II (Fair, usually burns, tans with difficulty)',
            'Type III' => 'Type III (Medium, sometimes mild burn, gradually tans)',
            'Type IV' => 'Type IV (Olive/Moderate brown, rarely burns, tans easily)',
            'Type V' => 'Type V (Brown/Dark brown, very rarely burns, tans very easily)',
            'Type VI' => 'Type VI (Black, deeply pigmented, never burns)',
        ];
    }
}
