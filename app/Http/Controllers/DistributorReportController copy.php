<?php

namespace App\Http\Controllers;

use App\Models\Onboarding;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DistributorReportExport;
use Illuminate\Support\Facades\Auth;

class DistributorReportController extends Controller
{
    // Consolidated Distributor Summary Report
    public function distributorSummary(Request $request)
    {
        $reportType = $request->get('report_type', 'summary');

        // Set simplified filters
        $filters = [
            'status' => $request->input('status', 'All'),
            'search' => $request->input('search'),
        ];

        // Build query with role-based access
        $query = $this->buildReportQuery($filters, $reportType);

        // Export functionality
        if ($request->has('export') && $request->export === 'excel') {
            $distributors = $query->get();
            $filename = $reportType === 'summary' ? 'distributor_summary.xlsx' : "{$reportType}_report.xlsx";
            
            return Excel::download(new DistributorReportExport($distributors, $reportType), $filename);
        }

        $distributors = $query->paginate(50)->appends($request->all());

        return view('distributor.reports.summary', compact(
            'distributors', 
            'reportType',
            'filters'
        ));
    }

    // TAT Report
    public function tatReport(Request $request)
    {
        // Set simplified filters
        $filters = [
            'status' => $request->input('status', 'All'),
            'search' => $request->input('search'),
        ];

        // Build query for TAT report with role-based access
        $query = $this->buildReportQuery($filters, 'tat');

        // Export functionality
        if ($request->has('export') && $request->export === 'excel') {
            $distributors = $query->get();
            return Excel::download(new DistributorReportExport($distributors, 'tat'), 'tat_report.xlsx');
        }

        $distributors = $query->paginate(50)->appends($request->all());

        return view('distributor.reports.tat-report', compact(
            'distributors',
            'filters'
        ));
    }

    // Common method to build report query with role-based access
    protected function buildReportQuery($filters, $reportType = 'summary')
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
                'authorizedPersons'
            ]);

        // Apply role-based data access
        $this->applyRoleBasedAccess($query, $user);

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('application_code', 'like', "%{$filters['search']}%")
                  ->orWhereHas('entityDetails', function($q2) use ($filters) {
                      $q2->where('establishment_name', 'like', "%{$filters['search']}%");
                  });
            });
        }

        // Apply status filter
        $this->applyStatusFilter($query, $filters);

        // Report type specific modifications
        switch ($reportType) {
            case 'approval':
                $query->whereIn('status', ['under_level1_review', 'under_level2_review', 'under_level3_review', 'approved', 'reverted', 'on_hold']);
                break;
            case 'verification':
                $query->whereIn('status', [
                    'mis_processing', 'documents_pending', 'documents_resubmitted', 
                    'documents_verified', 'physical_docs_pending', 'physical_docs_redispatched', 
                    'physical_docs_verified', 'agreement_created'
                ]);
                break;
            case 'pending':
                $query->where('status', 'documents_pending');
                break;
            case 'rejected':
                $query->where('status', 'rejected');
                break;
            case 'completed':
                $query->where('status', 'distributorship_created');
                break;
            case 'tat':
                // For TAT report, include all accessible applications
                $query->orderBy('created_at', 'desc');
                break;
            default: // summary
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query;
    }

    // Apply role-based data access
    protected function applyRoleBasedAccess($query, $user)
    {
        // Super Admin, Admin, MIS users can see all data
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Mis Admin', 'Mis User', 'Management'])) {
            return $query; // No restrictions
        }

        // Approvers can see applications they need to approve or have approved
        if ($this->isApprover($user)) {
            return $query->where(function($q) use ($user) {
                $q->where('current_approver_id', $user->emp_id)
                  ->orWhere('final_approver_id', $user->emp_id)
                  ->orWhereHas('approvalLogs', function($q2) use ($user) {
                      $q2->where('user_id', $user->emp_id);
                  });
            });
        }

        // Sales users can only see applications they created
        return $query->where('created_by', $user->emp_id);
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
                $query->whereIn('status', $statusGroups[$filters['status']]);
            } else {
                $query->where('status', $filters['status']);
            }
        }
    }

    protected function getStatusGroups()
    {
        return [
            'draft' => ['draft'],
            'sales_approval' => ['under_level1_review', 'under_level2_review', 'under_level3_review', 'approved', 'reverted', 'on_hold'],
            'mis_verification' => [
                'mis_processing', 'documents_pending', 'documents_resubmitted', 
                'documents_verified', 'physical_docs_pending', 'physical_docs_redispatched', 
                'physical_docs_verified', 'agreement_created'
            ],
            'completed' => ['distributorship_created'],
            'rejected' => ['rejected']
        ];
    }
}