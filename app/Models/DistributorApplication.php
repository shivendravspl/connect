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
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    

    // Relationships
    public function entityDetails()
    {
        return $this->hasOne(EntityDetails::class,'application_id');
    }
    
    public function distributionDetails()
    {
        return $this->hasOne(DistributionDetail::class,'application_id');
    }
    
    public function bankDetails()
    {
        return $this->hasOne(BankDetail::class,'application_id');
    }
    
    public function businessPlans()
    {
        return $this->hasMany(BusinessPlan::class,'application_id');
    }
    
    public function financialInfo()
    {
        return $this->hasOne(FinancialInfo::class,'application_id');
    }
    
    public function existingDistributorships()
    {
        return $this->hasMany(ExistingDistributorship::class,'application_id');
    }
    
    public function declarations()
    {
        return $this->hasMany(Declaration::class,'application_id');
    }
    
    public function documents()
    {
        return $this->hasMany(Document::class,'application_id');
    }
    
    public function agreement()
    {
        return $this->hasOne(Agreement::class);
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
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the current approver.
     */
    public function currentApprover()
    {
        return $this->belongsTo(User::class, 'current_approver_id');
    }

    /**
     * Get the approval logs for this application.
     */
    public function approvalLogs()
    {
        return $this->hasMany(ApprovalLog::class, 'application_id');
    }
    

}