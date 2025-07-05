<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributorApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'territory',
        'crop_vertical',
        'region',
        'zone',
        'business_unit',
        'district',
        'state',
        'status',
        'created_by',
        'current_approver_id',
        'approval_level',        
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


     // Add these properties
    protected $appends = ['status_badge', 'status_label'];
    
    // Status to badge color mapping
    const STATUS_BADGES = [
        'draft' => 'secondary',
        'submitted' => 'primary',
        'on_hold' => 'warning',
        'reverted' => 'info',
        'approved' => 'success',
        'rejected' => 'danger',
    ];
    
    // Status to human-readable labels
    const STATUS_LABELS = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'on_hold' => 'On Hold',
        'reverted' => 'Reverted',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    /**
     * Get the badge class for the current status
     */
    public function getStatusBadgeAttribute()
    {
        return self::STATUS_BADGES[strtolower($this->status)] ?? 'secondary';
    }

    /**
     * Get the human-readable status label
     */
    public function getStatusLabelAttribute()
    {
        return self::STATUS_LABELS[strtolower($this->status)] ?? ucfirst($this->status);
    }

    // Relationships
    public function entityDetails()
    {
        return $this->hasOne(EntityDetails::class, 'application_id');
    }

    public function distributionDetail()
    {
        return $this->hasOne(DistributionDetail::class, 'application_id', 'id');
    }

    public function bankDetail()
    {
        return $this->hasOne(BankDetail::class, 'application_id', 'id');
    }

    public function businessPlan()
    {
        return $this->hasMany(BusinessPlan::class, 'application_id', 'id');
    }

    public function financialInfo()
    {
        return $this->hasOne(FinancialInfo::class, 'application_id');
    }

    public function existingDistributorships()
    {
        return $this->hasMany(ExistingDistributorship::class, 'application_id');
    }

    public function declarations()
    {
        return $this->hasMany(Declaration::class, 'application_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'application_id');
    }


    public function securityDeposits()
    {
        return $this->hasMany(SecurityDeposit::class);
    }

    public function securityCheques()
    {
        return $this->hasMany(SecurityCheque::class);
    }


    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'id');
    }
    /**
     * Get the current approver.
     */
    public function currentApprover()
    {
        return $this->belongsTo(User::class, 'current_approver_id', 'emp_id');
    }

    /**
     * Get the approval logs for this application.
     */
    public function approvalLogs()
    {
        return $this->hasMany(ApprovalLog::class, 'application_id');
    }

    public function territoryDetail()
    {
        return $this->belongsTo(CoreTerritory::class, 'territory');
    }

    public function regionDetail()
    {
        return $this->belongsTo(CoreRegion::class, 'region');
    }

    public function zoneDetail()
    {
        return $this->belongsTo(CoreZone::class, 'zone');
    }

    public function businessUnit()
    {
        return $this->belongsTo(CoreBusinessUnit::class, 'business_unit');
    }
}
