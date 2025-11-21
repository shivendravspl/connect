<?php

namespace App\Http\Controllers;

use App\Models\Onboarding;
use App\Models\Status;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DistributorReportExport;
use Illuminate\Support\Facades\Auth;

class DistributorReportController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new \App\Helpers\Helpers();
    }

    // Consolidated Distributor Summary Report
    public function distributorSummary(Request $request)
    {
        $reportType = $request->get('report_type', 'summary');
        $user = Auth::user();

        // Set comprehensive filters
        $filters = [
            'bu' => $request->input('bu', 'All'),
            'zone' => $request->input('zone', 'All'),
            'region' => $request->input('region', 'All'),
            'territory' => $request->input('territory', 'All'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'status' => $request->input('status', 'All'),
            'search' => $request->input('search'),
        ];

        // Define status groups for filtering
        $statusGroups = [
            'draft' => ['draft'],
            'sales_approval' => ['under_level1_review', 'under_level2_review', 'under_level3_review', 'approved', 'reverted', 'on_hold'],
            'mis_verification' => [
                'mis_processing',
                'documents_pending',
                'documents_resubmitted',
                'documents_verified',
                'physical_docs_pending',
                'physical_docs_redispatched',
                'physical_docs_verified',
                'agreement_created'
            ],
            'completed' => ['distributorship_created'],
            'reverted' => ['reverted'],
            'rejected' => ['rejected'],
            'on_hold' => ['on_hold'],
            'security_deposit_not_received' => ['security_deposit_not_received']
        ];

        // Add individual statuses that MIS users should be able to filter by
        $misIndividualStatuses = [
            'mis_processing',
            'documents_pending',
            'documents_resubmitted',
            'documents_verified',
            'physical_docs_pending',
            'physical_docs_redispatched',
            'physical_docs_verified',
            'agreement_created',
            'distributorship_created',
            'security_deposit_not_received'
        ];

        // Add these to status groups for individual filtering
        foreach ($misIndividualStatuses as $status) {
            if (!array_key_exists($status, $statusGroups)) {
                $statusGroups[$status] = [$status];
            }
        }

        // Get common filter data
        $filterData = $this->getFilterData($user);

        // Build query with role-based access, passing status groups for filtering logic
        $query = $this->buildReportQuery($filters, $reportType, $statusGroups);

        // Export functionality
        if ($request->has('export') && $request->export === 'excel') {
            $distributors = $query->get();
            $filename = $reportType === 'summary' ? 'distributor_summary.xlsx' : "{$reportType}_report.xlsx";

            return Excel::download(new DistributorReportExport($distributors, $reportType, $filters), $filename);
        }
        $distributors = $query->paginate(50)->appends($request->all());

        return view('distributor.reports.summary', array_merge(
            compact('distributors', 'reportType', 'filters'),
            $filterData,
            compact('statusGroups')
        ));
    }

    // TAT Report
    public function tatReport(Request $request)
    {
        $user = Auth::user();

        // Set comprehensive filters
        $filters = [
            'bu' => $request->input('bu', 'All'),
            'zone' => $request->input('zone', 'All'),
            'region' => $request->input('region', 'All'),
            'territory' => $request->input('territory', 'All'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'status' => $request->input('status', 'All'),
            'search' => $request->input('search'),
        ];

        // Define status groups for filtering
        $statusGroups = [
            'draft' => ['draft'],
            'sales_approval' => ['under_level1_review', 'under_level2_review', 'under_level3_review', 'approved', 'reverted', 'on_hold'],
            'mis_verification' => [
                'mis_processing',
                'documents_pending',
                'documents_resubmitted',
                'documents_verified',
                'physical_docs_pending',
                'physical_docs_redispatched',
                'physical_docs_verified',
                'agreement_created'
            ],
            'completed' => ['distributorship_created'],
            'reverted' => ['reverted'],
            'rejected' => ['rejected'],
            'on_hold' => ['on_hold'],
            'security_deposit_not_received' => ['security_deposit_not_received'] 
        ];

        // Add individual statuses that MIS users should be able to filter by
        $misIndividualStatuses = [
            'mis_processing',
            'documents_pending',
            'documents_resubmitted',
            'documents_verified',
            'physical_docs_pending',
            'physical_docs_redispatched',
            'physical_docs_verified',
            'agreement_created',
            'distributorship_created',
            'security_deposit_not_received'
        ];

        // Add these to status groups for individual filtering
        foreach ($misIndividualStatuses as $status) {
            if (!array_key_exists($status, $statusGroups)) {
                $statusGroups[$status] = [$status];
            }
        }

        // Get common filter data
        $filterData = $this->getFilterData($user);

        // Build query for TAT report with role-based access
        $query = $this->buildTATReportQuery($filters);

        // Export functionality
        if ($request->has('export') && $request->export === 'excel') {
            $distributors = $query->get();
            return Excel::download(new DistributorReportExport($distributors, 'tat', $filters), 'tat_report.xlsx');
        }

        $distributors = $query->paginate(50)->appends($request->all());

        return view('distributor.reports.tat-report', array_merge(
            compact('distributors', 'filters'),
            $filterData,
            compact('statusGroups')
        ));
    }

    protected function buildTATReportQuery($filters)
{
    $user = Auth::user();

    $query = Onboarding::query()
        ->with([
            'entityDetails', 
            'createdBy', 
            'currentApprover', 
            'approvalLogs' => function($q) {
                $q->orderBy('created_at', 'asc');
            },
            'physicalDispatch',
            'vertical',
            'authorizedPersons',
            'individualDetails',
            'proprietorDetails', 
            'partnershipPartners',
            'llpPartners',
            'directors',
            'committeeMembers',
            'trustees'
        ])
        ->select([
            'onboardings.*',
            'core_region.region_name',
            'core_zone.zone_name',
            'core_territory.territory_name',
            'core_employee.emp_name as created_by_name',
            'ca.emp_name as current_approver_name',
            'entity_details.establishment_name',
            'core_vertical.vertical_name'
        ])
        ->leftJoin('core_territory', 'onboardings.territory', '=', 'core_territory.id')
        ->leftJoin('core_region', 'onboardings.region', '=', 'core_region.id')
        ->leftJoin('core_zone', 'onboardings.zone', '=', 'core_zone.id')
        ->leftJoin('core_employee', 'onboardings.created_by', '=', 'core_employee.employee_id')
        ->leftJoin('core_employee as ca', 'onboardings.current_approver_id', '=', 'ca.employee_id')
        ->leftJoin('entity_details', 'onboardings.id', '=', 'entity_details.application_id')
        ->leftJoin('core_vertical', 'onboardings.crop_vertical', '=', 'core_vertical.id');

    // Apply role-based data access
    $this->applyRoleBasedAccess($query, $user);

    // Apply comprehensive filters
    $this->applyComprehensiveFilters($query, $filters);

    $query->orderBy('onboardings.created_at', 'desc');

    return $query;
}

     public static function calculateTATData($distributor)
    {
        $approvalLogs = $distributor->approvalLogs;
        $physicalDispatch = $distributor->physicalDispatch;

        // Get approval dates
        $rbmApproval = $approvalLogs->where('role', 'Regional Business Manager')->where('action', 'approved')->first();
        $zbmApproval = $approvalLogs->where('role', 'Zonal Business Manager')->where('action', 'approved')->first();
        $gmApproval = $approvalLogs->where('role', 'General Manager')->where('action', 'approved')->first();
        
        // Get revert and reply dates (first instance)
        $revertLog = $approvalLogs->where('action', 'reverted')->first();
        $replyAfterRevert = $approvalLogs->where('action', 'resubmitted')->first();

        // MIS verification date (assuming this is when status changed to documents_verified)
        $misVerificationDate = null;
        if (in_array($distributor->status, ['documents_verified', 'physical_docs_verified', 'agreement_created', 'distributorship_created','security_deposit_not_received'])) {
            $misVerificationDate = $distributor->updated_at;
        }

        // Physical document dates
        $dispatchDate = $physicalDispatch?->dispatch_date;
        $physicalReceiveDate = $physicalDispatch?->received_date;

        // Final creation date (when distributorship is created)
        $finalCreationDate = $distributor->status === 'distributorship_created' ? $distributor->updated_at : null;

        // Deposit date (you'll need to add this field to your database)
        $depositDate = $distributor->deposit_received_date ?? null;
        if ($distributor->status === 'security_deposit_not_received') {
            $depositDate = null; // Still pending
        }
        // Calculate TATs
        $appDate = $distributor->created_at;

        // RBM TAT: RBM approval date - App date
        $rbmTat = $rbmApproval ? $appDate->diffInDays($rbmApproval->created_at) : null;

        // ZBM TAT: ZBM approval date - RBM approval date
        $zbmTat = null;
        if ($zbmApproval && $rbmApproval) {
            $zbmTat = $rbmApproval->created_at->diffInDays($zbmApproval->created_at);
        }

        // GM TAT: GM approval date - ZBM approval date
        $gmTat = null;
        if ($gmApproval && $zbmApproval) {
            $gmTat = $zbmApproval->created_at->diffInDays($gmApproval->created_at);
        } elseif ($gmApproval && $rbmApproval) {
            // If no ZBM, calculate from RBM
            $gmTat = $rbmApproval->created_at->diffInDays($gmApproval->created_at);
        }

        // MIS Doc Verification TAT: Verification date - GM Approval date
        $misDocVerificationTat = null;
        if ($misVerificationDate && $gmApproval) {
            $misDocVerificationTat = $gmApproval->created_at->diffInDays($misVerificationDate);
        }

        // Revert and Reply TAT: Reply date - Revert date
        $revertReplyTat = null;
        if ($replyAfterRevert && $revertLog) {
            $revertReplyTat = $revertLog->created_at->diffInDays($replyAfterRevert->created_at);
        }

        // Dispatch/Physical TAT: Receipt date - Dispatch date
        $dispatchTat = null;
        if ($physicalReceiveDate && $dispatchDate) {
            $dispatchTat = Carbon::parse($dispatchDate)->diffInDays(Carbon::parse($physicalReceiveDate));
        }

        // MIS TAT: Verification date - Receipt date
        $misTat = null;
        if ($misVerificationDate && $physicalReceiveDate) {
            $misTat = Carbon::parse($physicalReceiveDate)->diffInDays($misVerificationDate);
        }

        // Physical doc pendency clearance TAT: Reply date - Revert/Dispatch date
        $physicalPendencyTat = null;
        if ($replyAfterRevert && $dispatchDate) {
            $physicalPendencyTat = Carbon::parse($dispatchDate)->diffInDays($replyAfterRevert->created_at);
        }

        // Deposit TAT: Deposit date - Physical receive date
        $depositTat = null;
        if ($depositDate && $physicalReceiveDate) {
            $depositTat = Carbon::parse($physicalReceiveDate)->diffInDays(Carbon::parse($depositDate));
        }

        // Distributor Finalisation TAT: Final creation date - Verification date
        $distributorFinalisationTat = null;
        if ($finalCreationDate && $misVerificationDate) {
            $distributorFinalisationTat = $misVerificationDate->diffInDays($finalCreationDate);
        }

        // Total TAT: Final approval date - App date
        $totalTat = null;
        $endDate = $finalCreationDate ?? $misVerificationDate ?? $gmApproval?->created_at ?? $zbmApproval?->created_at ?? $rbmApproval?->created_at;
       
        if ($distributor->status === 'security_deposit_not_received') {
             $endDate = now();
        }
         if ($endDate) {
            $totalTat = $appDate->diffInDays($endDate);
        }

        // Determine TAT Status based on SLA
        $tatStatus = self::getTATStatus($totalTat);

        // Pending Level
        $pendingLevel = self::getPendingLevel($distributor);

        // Days Pending
        $daysPending = self::getDaysPending($distributor);

        return [
            'app_date' => $appDate,
            'rbm_approval_date' => $rbmApproval?->created_at,
            'zbm_approval_date' => $zbmApproval?->created_at,
            'gm_approval_date' => $gmApproval?->created_at,
            'revert_date' => $revertLog?->created_at,
            'reply_date' => $replyAfterRevert?->created_at,
            'dispatch_date' => $dispatchDate,
            'physical_receive_date' => $physicalReceiveDate,
            'mis_verification_date' => $misVerificationDate,
            'final_creation_date' => $finalCreationDate,
            'deposit_date' => $depositDate,
            
            'rbm_tat' => $rbmTat,
            'zbm_tat' => $zbmTat,
            'gm_tat' => $gmTat,
            'mis_doc_verification_tat' => $misDocVerificationTat,
            'revert_reply_tat' => $revertReplyTat,
            'dispatch_tat' => $dispatchTat,
            'mis_tat' => $misTat,
            'physical_pendency_tat' => $physicalPendencyTat,
            'deposit_tat' => $depositTat,
            'distributor_finalisation_tat' => $distributorFinalisationTat,
            'total_tat' => $totalTat,
            
            'tat_status' => $tatStatus,
            'pending_level' => $pendingLevel,
            'days_pending' => $daysPending,
        ];
    }

    protected static function getTATStatus($totalTat)
    {
        if ($totalTat === null) return 'Pending';
        
        if ($totalTat <= 20) return 'Within SLA';
        if ($totalTat <= 33) return 'Moderate Delay';
        return 'Extreme Delay';
    }

    protected static function getPendingLevel($distributor)
    {
        $status = $distributor->status;
        $statusMap = [
            'draft' => 'Sales Level',
            'under_level1_review' => 'RBM Level',
            'under_level2_review' => 'ZBM Level', 
            'under_level3_review' => 'GM Level',
            'mis_processing' => 'MIS Level',
            'documents_pending' => 'Sales Level',
            'documents_resubmitted' => 'MIS Level',
            'documents_verified' => 'MIS Level',
            'physical_docs_pending' => 'Sales Level',
            'physical_docs_redispatched' => 'MIS Level',
            'physical_docs_verified' => 'MIS Level',
            'agreement_created' => 'MIS Level',
            'distributorship_created' => 'Completed',
            'reverted' => 'Sales Level',
            'rejected' => 'Completed',
            'on_hold' => 'On Hold',
            'security_deposit_not_received' => 'Security Deposit Pending'
        ];

        return $statusMap[$status] ?? 'Unknown';
    }

    // Get days pending at current level
    protected static function getDaysPending($distributor)
    {
        $lastActionDate = $distributor->approvalLogs->last()?->created_at ?? $distributor->created_at;
        return $lastActionDate->diffInDays(now());
    }
    // Get common filter data
    protected function getFilterData($user)
    {
        return [
            'bu_list' => $this->helper->getAssociatedBusinessUnitList($user->emp_id),
            'zone_list' => $this->helper->getAssociatedZoneList($user->emp_id),
            'region_list' => $this->helper->getAssociatedRegionList($user->emp_id),
            'territory_list' => $this->helper->getAssociatedTerritoryList($user->emp_id),
            'statuses' => Status::where('is_active', 1)->orderBy('sort_order')->get(),
            'statusGroups' => $this->getStatusGroups(),
            'userCapabilities' => $this->getUserCapabilities($user, $user->employee)
        ];
    }

    // Common method to build report query with role-based access
    protected function buildReportQuery($filters, $reportType = 'summary')
    {
        $user = Auth::user();

        // Build base query with joins like in your onboarding controller
        $query = Onboarding::query()
            ->with(['entityDetails', 'createdBy', 'currentApprover', 'approvalLogs'])
            ->select([
                'onboardings.id',
                'onboardings.application_code',
                'onboardings.distributor_code',
                'onboardings.territory',
                'onboardings.region',
                'onboardings.zone',
                'onboardings.business_unit',
                'onboardings.status',
                'onboardings.doc_verification_status',
                'onboardings.agreement_status',
                'onboardings.physical_docs_status',
                'onboardings.final_status',
                'onboardings.created_by',
                'onboardings.current_approver_id',
                'onboardings.final_approver_id',
                'onboardings.approval_level',
                'onboardings.created_at',
                'onboardings.updated_at',
                'onboardings.date_of_appointment',
                'core_region.region_name',
                'core_zone.zone_name',
                'core_territory.territory_name',
                'core_employee.emp_name as created_by_name',
                'ca.emp_name as current_approver_name',
                'entity_details.establishment_name',
            ])
            ->leftJoin('core_territory', 'onboardings.territory', '=', 'core_territory.id')
            ->leftJoin('core_region', 'onboardings.region', '=', 'core_region.id')
            ->leftJoin('core_zone', 'onboardings.zone', '=', 'core_zone.id')
            ->leftJoin('core_employee', 'onboardings.created_by', '=', 'core_employee.employee_id')
            ->leftJoin('core_employee as ca', 'onboardings.current_approver_id', '=', 'ca.employee_id')
            ->leftJoin('entity_details', 'onboardings.id', '=', 'entity_details.application_id');

        // Apply role-based data access
        $this->applyRoleBasedAccess($query, $user);

        // Apply comprehensive filters
        $this->applyComprehensiveFilters($query, $filters);

        // Report type specific modifications
        switch ($reportType) {
            case 'approval':
                $query->whereIn('onboardings.status', ['under_level1_review', 'under_level2_review', 'under_level3_review', 'approved', 'reverted', 'on_hold']);
                break;
            case 'verification':
                $query->whereIn('onboardings.status', [
                    'mis_processing',
                    'documents_pending',
                    'documents_resubmitted',
                    'documents_verified',
                    'physical_docs_pending',
                    'physical_docs_redispatched',
                    'physical_docs_verified',
                    'agreement_created'
                ]);
                break;
            case 'pending':
                $query->where('onboardings.status', 'documents_pending');
                break;
            case 'rejected':
                $query->where('onboardings.status', 'rejected');
                break;
            case 'completed':
                $query->where('onboardings.status', 'distributorship_created');
                break;
            case 'tat':
                // For TAT report, include all accessible applications
                $query->orderBy('onboardings.created_at', 'desc');
                break;
            default: // summary
                $query->orderBy('onboardings.id');
                break;
        }

        return $query;
    }

    // Apply comprehensive filters
    protected function applyComprehensiveFilters($query, $filters)
{
    // Apply search filter
    if (!empty($filters['search'])) {
        $query->where(function ($q) use ($filters) {
            $q->where('onboardings.application_code', 'like', "%{$filters['search']}%")
                ->orWhere('onboardings.distributor_code', 'like', "%{$filters['search']}%")
                ->orWhere('entity_details.establishment_name', 'like', "%{$filters['search']}%")
                // Remove the direct column search and use relationship-based search
                ->orWhereHas('authorizedPersons', function ($subQuery) use ($filters) {
                    $subQuery->where('name', 'like', "%{$filters['search']}%");
                })
                ->orWhereHas('individualDetails', function ($subQuery) use ($filters) {
                    $subQuery->where('name', 'like', "%{$filters['search']}%");
                })
                ->orWhereHas('proprietorDetails', function ($subQuery) use ($filters) {
                    $subQuery->where('name', 'like', "%{$filters['search']}%");
                })
                ->orWhereHas('partnershipPartners', function ($subQuery) use ($filters) {
                    $subQuery->where('name', 'like', "%{$filters['search']}%");
                })
                ->orWhereHas('llpPartners', function ($subQuery) use ($filters) {
                    $subQuery->where('name', 'like', "%{$filters['search']}%");
                })
                ->orWhereHas('directors', function ($subQuery) use ($filters) {
                    $subQuery->where('name', 'like', "%{$filters['search']}%");
                })
                ->orWhereHas('committeeMembers', function ($subQuery) use ($filters) {
                    $subQuery->where('name', 'like', "%{$filters['search']}%");
                })
                ->orWhereHas('trustees', function ($subQuery) use ($filters) {
                    $subQuery->where('name', 'like', "%{$filters['search']}%");
                });
        });
    }

    // Apply BU filter
    if (isset($filters['bu']) && $filters['bu'] !== 'All') {
        $query->where('onboardings.business_unit', $filters['bu']);
    }

    // Apply Zone filter
    if (isset($filters['zone']) && $filters['zone'] !== 'All') {
        $query->where('onboardings.zone', $filters['zone']);
    }

    // Apply Region filter
    if (isset($filters['region']) && $filters['region'] !== 'All') {
        $query->where('onboardings.region', $filters['region']);
    }

    // Apply Territory filter
    if (isset($filters['territory']) && $filters['territory'] !== 'All') {
        $query->where('onboardings.territory', $filters['territory']);
    }

    // Apply date range filter
    if (!empty($filters['date_from'])) {
        $query->whereDate('onboardings.created_at', '>=', $filters['date_from']);
    }
    if (!empty($filters['date_to'])) {
        $query->whereDate('onboardings.created_at', '<=', $filters['date_to']);
    }

    // Apply status filter
    $this->applyStatusFilter($query, $filters);
}

    // Apply role-based data access (copy from your onboarding controller)
   protected function applyRoleBasedAccess($query, $user)
    {
        // Super Admin, Admin, MIS users can see all data
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Mis Admin', 'Mis User', 'Management'])) {
            return $query; // No restrictions
        }

        // Approvers can see applications they need to approve or have approved
        if ($this->isApprover($user)) {
            return $query->where(function ($q) use ($user) {
                $q->where('onboardings.current_approver_id', $user->emp_id)
                    ->orWhere('onboardings.final_approver_id', $user->emp_id)
                    ->orWhereHas('approvalLogs', function ($q2) use ($user) {
                        $q2->where('user_id', $user->emp_id);
                    });
            });
        }

        // Sales users can only see applications they created
        return $query->where('onboardings.created_by', $user->emp_id);
    }

    // Check if user is an approver based on designation
    protected function isApprover($user)
    {
        $employee = $user->employee;
        if (!$employee) {
            return false;
        }

        $approverDesignations = [
            'Regional Business Manager',
            'Zonal Business Manager',
            'General Manager',
            'Senior Executive'
        ];

        return in_array($employee->emp_designation, $approverDesignations);
    }

    // Apply status filter
    protected function applyStatusFilter($query, $filters)
    {
        if (isset($filters['status']) && $filters['status'] !== 'All' && $filters['status'] !== '') {
            $statusGroups = $this->getStatusGroups();

            if (array_key_exists($filters['status'], $statusGroups)) {
                $query->whereIn('onboardings.status', $statusGroups[$filters['status']]);
            } else {
                $query->where('onboardings.status', $filters['status']);
            }
        }
    }

    protected function getStatusGroups()
    {
        return [
            'draft' => ['draft'],
            'sales_approval' => ['under_level1_review', 'under_level2_review', 'under_level3_review', 'approved', 'reverted', 'on_hold'],
            'mis_verification' => [
                'mis_processing',
                'documents_pending',
                'documents_resubmitted',
                'documents_verified',
                'physical_docs_pending',
                'physical_docs_redispatched',
                'physical_docs_verified',
                'agreement_created'
            ],
            'completed' => ['distributorship_created'],
            'rejected' => ['rejected'],
            'security_deposit_not_received' => ['security_deposit_not_received']
        ];
    }

    // Copy getUserCapabilities method from your onboarding controller
    protected function getUserCapabilities($user, $employee)
    {
        // Default capabilities
        $capabilities = [
            'access_level' => 'all',
            'can_view_all' => false,
            'can_approve' => false,
            'can_verify' => false,
            'can_create' => true,
        ];

        // Super Admin and Admin can view all
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Mis Admin', 'Mis User', 'Management'])) {
            $capabilities['access_level'] = 'all';
            $capabilities['can_view_all'] = true;
            $capabilities['can_approve'] = true;
            $capabilities['can_verify'] = true;
        }
        // Approvers based on designation
        elseif ($this->isApprover($user)) {
            $capabilities['access_level'] = 'region';
            $capabilities['can_approve'] = true;
        }
        // Sales users - restricted access
        else {
            $capabilities['access_level'] = 'territory';
            $capabilities['can_view_all'] = false;
        }

        return $capabilities;
    }
}
