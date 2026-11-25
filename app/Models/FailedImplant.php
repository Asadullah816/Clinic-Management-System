<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedImplant extends Model
{
    use HasFactory;

    // Sources
    const SOURCE_SALE = 'sale';

    const SOURCE_STOCK = 'stock';

    const SOURCE_INTERNAL = 'internal';

    // Failure Reasons
    const REASON_LACK_OF_STABILITY = 'lack_of_stability';

    const REASON_OSSEOINTEGRATION_FAILURE = 'osseointegration_failure';

    const REASON_CONTAMINATION = 'contamination';

    const REASON_INFECTION = 'infection';

    const REASON_BONE_RESORPTION = 'bone_resorption';

    const REASON_MECHANICAL_FRACTURE = 'mechanical_fracture';

    const REASON_PACKAGING_DEFECT = 'packaging_defect';

    const REASON_OTHER = 'other';

    // Failure Stages
    const STAGE_SURGICAL = 'surgical_placement';

    const STAGE_HEALING = 'osseointegration_early';

    const STAGE_POST_RESTORATION = 'post_restoration';

    const STAGE_PACKAGING = 'packaging_defect';

    const STAGE_OTHER = 'other';

    // Warranty Statuses
    const WARRANTY_PENDING = 'pending_claim';

    const WARRANTY_REPLACED = 'vendor_replaced';

    const WARRANTY_CLINIC_LOSS = 'clinic_loss';

    const WARRANTY_DOCTOR_ABSORBED = 'doctor_absorbed';

    const WARRANTY_CREDIT_NOTE = 'credit_note';

    protected $fillable = [
        'failure_number',
        'implant_id',
        'implant_buyer_id',
        'implant_sale_id',
        'failure_date',
        'quantity',
        'unit_cost',
        'total_cost',
        'source',
        'failure_stage',
        'failure_reason',
        'patient_name',
        'tooth_number',
        'warranty_status',
        'replacement_implant_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'failure_date' => 'date',
            'quantity' => 'integer',
            'unit_cost' => 'float',
            'total_cost' => 'float',
        ];
    }

    public static function reasons(): array
    {
        return [
            self::REASON_LACK_OF_STABILITY => 'Lack of Primary Stability / Bone Density',
            self::REASON_OSSEOINTEGRATION_FAILURE => 'Loss of Osseointegration (Early/Late)',
            self::REASON_CONTAMINATION => 'Surgical Contamination / Dropped Implant',
            self::REASON_INFECTION => 'Peri-Implantitis / Infection',
            self::REASON_BONE_RESORPTION => 'Bone Resorption / Dehiscence',
            self::REASON_MECHANICAL_FRACTURE => 'Thread Stripping / Mechanical Fracture',
            self::REASON_PACKAGING_DEFECT => 'Packaging Defect / Compromised Sterility',
            self::REASON_OTHER => 'Other Clinical Complication',
        ];
    }

    public function reasonLabel(): string
    {
        return self::reasons()[$this->failure_reason] ?? ucfirst(str_replace('_', ' ', $this->failure_reason));
    }

    public static function stages(): array
    {
        return [
            self::STAGE_SURGICAL => 'Stage 1: Surgical Placement',
            self::STAGE_HEALING => 'Stage 2: Early Healing (Osseointegration)',
            self::STAGE_POST_RESTORATION => 'Stage 3: Post-Restoration / Loading',
            self::STAGE_PACKAGING => 'Unboxing / Packaging Defect',
            self::STAGE_OTHER => 'Other Stage',
        ];
    }

    public function stageLabel(): string
    {
        return self::stages()[$this->failure_stage] ?? ucfirst(str_replace('_', ' ', $this->failure_stage));
    }

    public static function warrantyStatuses(): array
    {
        return [
            self::WARRANTY_PENDING => 'Pending Vendor Claim',
            self::WARRANTY_REPLACED => 'Replaced by Manufacturer/Vendor',
            self::WARRANTY_CLINIC_LOSS => 'Written Off (Clinic Loss)',
            self::WARRANTY_DOCTOR_ABSORBED => 'Doctor Billed / Absorbed',
            self::WARRANTY_CREDIT_NOTE => 'Credit Note Issued',
        ];
    }

    public function warrantyLabel(): string
    {
        return self::warrantyStatuses()[$this->warranty_status] ?? ucfirst(str_replace('_', ' ', $this->warranty_status));
    }

    public function warrantyColor(): string
    {
        return match ($this->warranty_status) {
            self::WARRANTY_REPLACED, self::WARRANTY_CREDIT_NOTE => 'success',
            self::WARRANTY_PENDING => 'warning',
            self::WARRANTY_CLINIC_LOSS => 'danger',
            default => 'secondary',
        };
    }

    // Relationships
    public function implant()
    {
        return $this->belongsTo(Implant::class);
    }

    public function buyer()
    {
        return $this->belongsTo(ImplantBuyer::class, 'implant_buyer_id');
    }

    public function sale()
    {
        return $this->belongsTo(ImplantSale::class, 'implant_sale_id');
    }

    public function replacementImplant()
    {
        return $this->belongsTo(Implant::class, 'replacement_implant_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
