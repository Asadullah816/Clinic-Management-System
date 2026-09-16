<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Models\MedicalHistory;
use App\Models\Appointment;
use App\Models\PatientTreatment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\StockTransaction;
use App\Models\MedicineUsage;
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    // ---- Role constants ----
    const ROLE_ADMIN       = 'admin';
    const ROLE_ACCOUNTANT  = 'accountant';
    const ROLE_RECEPTIONIST= 'receptionist';
    const ROLE_STAFF       = 'staff';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed', // auto-hashes any plain password assigned
        ];
    }

    /**
     * All available roles — used for form dropdowns and validation.
     */
    public static function roles(): array
    {
        return [
            self::ROLE_ADMIN        => 'Administrator',
            self::ROLE_ACCOUNTANT   => 'Accountant',
            self::ROLE_RECEPTIONIST => 'Receptionist',
            self::ROLE_STAFF        => 'Staff',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Usage: $user->hasRole('admin', 'accountant')
     */
    public function hasRole(...$roles): bool
    {
        return in_array($this->role, $roles);
    }
      public function medicalHistories()
    {
        return $this->hasMany(MedicalHistory::class, 'created_by');
    }

        public function appointments()
    {
        return $this->hasMany(Appointment::class, 'created_by');
    }


        public function patientTreatments()
    {
        return $this->hasMany(PatientTreatment::class, 'created_by');
    }
        public function invoices()
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

        public function payments()
    {
        return $this->hasMany(Payment::class, 'received_by');
    }
        public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class, 'created_by');
    }
        public function medicineUsages()
    {
        return $this->hasMany(MedicineUsage::class, 'used_by');
    }
}
