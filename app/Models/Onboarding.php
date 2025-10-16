<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Onboarding extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
        'application_code',
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
        'is_hierarchy_approved',
        'mis_rejected_at',
        'resubmitted_at',
        'mis_verified_at',
        'doc_verification_status',
        'agreement_status',
        'physical_docs_status',
        'final_status',
        'distributor_code',
        'date_of_appointment',
        'authorized_person_name',
        'authorized_person_designation',
        'distributorship_confirmed_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_hierarchy_approved' => 'boolean',
        'mis_rejected_at' => 'datetime',
        'resubmitted_at' => 'datetime',
        'mis_verified_at' => 'datetime',
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
        return $this->belongsTo(Employee::class, 'created_by', 'employee_id');
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
        return $this->belongsTo(Employee::class, 'final_approver_id', 'employee_id');
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

    public function physicalDocumentChecks()
    {
        return $this->hasMany(PhysicalDocumentCheck::class, 'application_id');
    }

    public function distributorAgreements()
    {
        return $this->hasOne(DistributorAgreement::class, 'application_id');
    }

    public function distributorMaster()
    {
        return $this->hasOne(DistributorMaster::class, 'application_id');
    }

    public function vertical()
    {
        return $this->belongsTo(CoreVertical::class, 'crop_vertical', 'id');
    }

    public function checkpoints()
    {
        return $this->hasMany(ApplicationCheckpoint::class, 'application_id');
    }

    public function additionalDocs()
    {
        return $this->hasMany(ApplicationAdditionalDocument::class, 'application_id');
    }

    public function individualDetails()
    {
        return $this->hasOne(IndividualDetails::class, 'application_id', 'id');
    }

    public function proprietorDetails()
    {
        return $this->hasOne(ProprietorDetails::class, 'application_id', 'id');
    }

    public function authorizedPersons()
    {
        return $this->hasMany(AuthorizedPerson::class, 'application_id', 'id');
    }

    public function partnershipPartners()
    {
        return $this->hasMany(PartnershipPartner::class, 'application_id', 'id');
    }
    public function partnershipSignatories()
    {
        return $this->hasMany(PartnershipSignatory::class, 'application_id', 'id');
    }
    public function llpDetails()
    {
        return $this->hasOne(LlpDetails::class, 'application_id', 'id');
    }
    public function llpPartners()
    {
        return $this->hasMany(LlpPartner::class, 'application_id', 'id');
    }
    public function companyDetails()
    {
        return $this->hasOne(CompanyDetails::class, 'application_id', 'id');
    }
    public function directors()
    {
        return $this->hasMany(Director::class, 'application_id', 'id');
    }
    public function cooperativeDetails()
    {
        return $this->hasOne(CooperativeDetails::class, 'application_id', 'id');
    }
    public function committeeMembers()
    {
        return $this->hasMany(CommitteeMember::class, 'application_id', 'id');
    }
    public function trustDetails()
    {
        return $this->hasOne(TrustDetails::class, 'application_id', 'id');
    }
    public function trustees()
    {
        return $this->hasMany(Trustee::class, 'application_id', 'id');
    }

    public function physicalDispatch()
    {
        return $this->hasOne(PhysicalDispatch::class, 'application_id', 'id');
    }

    public function documentChecklists()
    {
        return $this->hasMany(ApplicationCheckpoint::class, 'application_id');
    }

    public function getAuthorizedOrEntityName()
    {
        // 1️⃣ Authorized person (highest priority)
        $authorizedPerson = $this->authorizedPersons->first();
        if ($authorizedPerson) {
            return $authorizedPerson->name;
        }

        // 2️⃣ Fallback by entity type
        $entityType = $this->entityDetails->entity_type ?? null;

        switch ($entityType) {
            case 'individual_person':
                return $this->individualDetails?->name;

            case 'sole_proprietorship':
                return $this->proprietorDetails?->name;

            case 'partnership':
                return $this->partnershipPartners?->first()?->name;

            case 'llp':
                return $this->llpPartners?->first()?->name;

            case 'private_company':
            case 'public_company':
                return $this->directors?->first()?->name;

            case 'cooperative_society':
                return $this->committeeMembers?->first()?->name;

            case 'trust':
                return $this->trustees?->first()?->name;

            default:
                return null;
        }
    }

    public function isRedispatched()
    {
        return PhysicalDispatch::where('application_id', $this->id)->count() > 1;
    }

    public function getLatestDispatch()
    {
        return PhysicalDispatch::where('application_id', $this->id)
            ->latest('created_at')
            ->first();
    }

    public function getPreviousDispatch()
    {
        return PhysicalDispatch::where('application_id', $this->id)
            ->orderBy('created_at', 'desc')
            ->skip(1)
            ->first();
    }
}
