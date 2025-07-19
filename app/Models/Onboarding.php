<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Onboarding extends Model
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
        'current_progress_step',
        'current_approver_id',
        'final_approver_id',
        'approval_level',  
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


     // Add these properties
    
  
    /**
     * Get the badge class for the current status
     */
       public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'initiated' => 'primary',
            'under_review' => 'info',
            'on_hold' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'reverted' => 'secondary',
            'mis_processing' => 'info',
            'document_verified' => 'primary',
            'agreement_created' => 'info',
            'documents_received' => 'warning',
            'distributorship_created' => 'success',
            default => 'secondary', // A default color for unknown statuses
        };
    }


     protected static function boot()
    {
        parent::boot();

        static::deleting(function ($application) {
            $deletedRecords = [];

            // Delete related records and log which ones exist
            if ($application->entityDetails) {
                $application->entityDetails()->delete();
                $deletedRecords[] = 'entity_details';
            }
            if ($application->distributionDetail) {
                $application->distributionDetail()->delete();
                $deletedRecords[] = 'distribution_details';
            }
            if ($application->businessPlans()->exists()) {
                $application->businessPlans()->delete();
                $deletedRecords[] = 'business_plans';
            }
            if ($application->financialInfo) {
                $application->financialInfo()->delete();
                $deletedRecords[] = 'financial_info';
            }
            if ($application->existingDistributorships()->exists()) {
                $application->existingDistributorships()->delete();
                $deletedRecords[] = 'existing_distributorships';
            }
            if ($application->bankDetail) {
                $application->bankDetail()->delete();
                $deletedRecords[] = 'bank_details';
            }
            if ($application->declarations) {
                $application->declarations()->delete();
                $deletedRecords[] = 'declarations';
            }
            Log::info("Deleted related records for application_id: {$application->id}", [
                'deleted_tables' => $deletedRecords ?: ['none'],
            ]);
        });
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

    public function businessPlans()
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


    public function createdBy()
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

    public function finalApprover() 
    { 
        return $this->belongsTo(Employee::class, 'final_approver_id', 'id'); 
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

    public function documentVerifications()
    {
         return $this->hasMany(DocumentVerification::class, 'application_id'); 
    }

    public function physicalDocuments() 
    { 
        return $this->hasMany(PhysicalDocument::class, 'application_id'); 
    }

    public function distributorAgreements()
    {
        return $this->hasMany(DistributorAgreement::class, 'application_id');
    }

    public function distributorMaster()
    {
        return $this->hasOne(DistributorMaster::class, 'application_id');
    }

}
