<?php

namespace App\Http\Controllers;

use App\Models\Onboarding;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DistributorReportExport;

class DistributorReportController extends Controller
{
    // Common method to build query for all reports
    private function buildQuery(Request $request, $reportType = 'summary')
    {
        $query = Onboarding::query();

        // Common search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('application_code', 'like', "%{$request->search}%")
                    ->orWhereHas('entityDetails', function ($q2) use ($request) {
                        $q2->where('establishment_name', 'like', "%{$request->search}%");
                    })
                    ->orWhereHas('authorizedPersons', function ($q2) use ($request) {
                        $q2->where('name', 'like', "%{$request->search}%");
                    });
            });
        }

        // Report-specific filters and data scope
        switch ($reportType) {
            case 'summary':
                // Show all applications with basic info
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
                if ($request->filled('territory')) {
                    $query->where('territory', $request->territory);
                }
                $query->with(['createdBy', 'vertical', 'regionDetail', 'entityDetails', 'authorizedPersons'])
                    ->orderBy('created_at', 'desc');
                break;

            case 'approval':
                // Show applications that are in approval process or have approval logs
                if ($request->filled('approval_level')) {
                    $query->where('approval_level', $request->approval_level);
                }
                // Only show applications that have some approval activity
                $query->whereHas('approvalLogs')
                    ->orWhereNotNull('approval_level')
                    ->with(['currentApprover', 'approvalLogs', 'createdBy', 'entityDetails', 'authorizedPersons'])
                    ->orderBy('updated_at', 'desc');
                break;

            case 'verification':
                // Show applications in MIS processing/document verification stage
                if ($request->filled('doc_status')) {
                    $query->where('doc_verification_status', $request->doc_status);
                }
                // Applications in MIS processing workflow
                $query->whereIn('status', [
                    'mis_processing',
                    'documents_pending',
                    'documents_resubmitted',
                    'documents_verified',
                    'physical_docs_pending',
                    'physical_docs_redispatched',
                    'physical_docs_verified',
                    'agreement_created'
                ])
                    ->with(['createdBy', 'entityDetails', 'authorizedPersons'])
                    ->orderBy('mis_verified_at', 'desc');
                break;

            case 'dispatch':
                // Show ONLY applications that have physical dispatch records
                if ($request->filled('dispatch_mode')) {
                    $query->whereHas('physicalDispatch', function ($q) use ($request) {
                        $q->where('mode', $request->dispatch_mode);
                    });
                }
                // Only show applications with dispatch records
                $query->whereHas('physicalDispatch')
                    ->with(['physicalDispatch', 'authorizedPersons', 'entityDetails'])
                    ->orderBy('created_at', 'desc');
                break;

            case 'lifecycle':
                // Show applications with some progress in lifecycle
                $query->where(function ($q) {
                    $q->whereHas('approvalLogs')
                        ->orWhereNotNull('mis_verified_at')
                        ->orWhereNotNull('doc_verification_status')
                        ->orWhereHas('physicalDispatch');
                })
                    ->with(['approvalLogs', 'authorizedPersons', 'entityDetails'])
                    ->orderBy('created_at', 'desc');
                break;

            case 'pending':
                // Show ONLY pending applications
                $query->where('status', 'documents_pending')
                    ->when($request->pending_step, fn($q) => $q->where('current_progress_step', $request->pending_step))
                    ->with(['createdBy', 'authorizedPersons', 'entityDetails'])
                    ->orderBy('created_at', 'desc');
                break;

            case 'rejected':
                // Show ONLY rejected applications
                $query->where('status', 'rejected')
                    ->with(['approvalLogs.user', 'authorizedPersons', 'entityDetails'])
                    ->orderBy('updated_at', 'desc');
                break;

            case 'pending_documents':
                // Show ONLY applications with pending documents
                $query->where('status', 'documents_pending')
                    ->with(['createdBy', 'authorizedPersons', 'entityDetails'])
                    ->orderBy('created_at', 'desc');
                break;
        }

        return $query;
    }

    // 1. Distributor Summary - Show ALL applications
    public function distributorSummary(Request $request)
    {
        $query = $this->buildQuery($request, 'summary');

        if ($request->export === 'excel') {
            $distributors = $query->get();
            return Excel::download(new DistributorReportExport($distributors, 'summary'), 'distributor_summary.xlsx');
        }

        $distributors = $query->paginate(20);
        return view('distributor.reports.summary', compact('distributors'));
    }

    // 2. Approval Status - Show applications with approval activity
    public function approvalStatus(Request $request)
    {
        $query = $this->buildQuery($request, 'approval');

        if ($request->export === 'excel') {
            $distributors = $query->get();
            return Excel::download(new DistributorReportExport($distributors, 'approval'), 'approval_status.xlsx');
        }

        $distributors = $query->paginate(20);
        return view('distributor.reports.approval-status', compact('distributors'));
    }

    // 3. Verification Status - Show applications with verification activity
    public function verificationStatus(Request $request)
    {
        $query = $this->buildQuery($request, 'verification');

        if ($request->export === 'excel') {
            $distributors = $query->get();
            return Excel::download(new DistributorReportExport($distributors, 'verification'), 'verification_status.xlsx');
        }

        $distributors = $query->paginate(20);
        return view('distributor.reports.verification-status', compact('distributors'));
    }

    // 4. Dispatch Status - Show ONLY applications with dispatch records
    public function dispatchStatus(Request $request)
    {
        $query = $this->buildQuery($request, 'dispatch');

        if ($request->export === 'excel') {
            $distributors = $query->get();
            return Excel::download(new DistributorReportExport($distributors, 'dispatch'), 'dispatch_status.xlsx');
        }

        $distributors = $query->paginate(20);
        return view('distributor.reports.dispatch-status', compact('distributors'));
    }

    // 5. Lifecycle - Show applications with some progress
    public function lifecycle(Request $request)
    {
        $query = $this->buildQuery($request, 'lifecycle');

        if ($request->export === 'excel') {
            $distributors = $query->get();
            return Excel::download(new DistributorReportExport($distributors, 'lifecycle'), 'lifecycle.xlsx');
        }

        $distributors = $query->paginate(20);
        return view('distributor.reports.lifecycle', compact('distributors'));
    }

    // 6. Pending Work - Show ONLY pending applications
    public function pending(Request $request)
    {
        $query = $this->buildQuery($request, 'pending');

        if ($request->export === 'excel') {
            $distributors = $query->get();
            return Excel::download(new DistributorReportExport($distributors, 'pending'), 'pending_work.xlsx');
        }

        $distributors = $query->paginate(20);
        return view('distributor.reports.pending', compact('distributors'));
    }

    // 7. Rejected - Show ONLY rejected applications
    public function rejected(Request $request)
    {
        $query = $this->buildQuery($request, 'rejected');

        if ($request->export === 'excel') {
            $distributors = $query->get();
            return Excel::download(new DistributorReportExport($distributors, 'rejected'), 'rejected.xlsx');
        }

        $distributors = $query->paginate(20);
        return view('distributor.reports.rejected', compact('distributors'));
    }

    // 8. Pending Documents - Show ONLY applications with pending documents
    public function pendingDocuments(Request $request)
    {
        $query = $this->buildQuery($request, 'pending_documents');

        if ($request->export === 'excel') {
            $distributors = $query->get();
            return Excel::download(new DistributorReportExport($distributors, 'pending_documents'), 'pending_documents.xlsx');
        }

        $distributors = $query->paginate(20);
        return view('distributor.reports.pending-documents', compact('distributors'));
    }

    // 8. Show TAT

    public function tatReport(Request $request)
    {
        $query = Onboarding::query();

        // Common search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('application_code', 'like', "%{$request->search}%")
                    ->orWhereHas('entityDetails', function ($q2) use ($request) {
                        $q2->where('establishment_name', 'like', "%{$request->search}%");
                    })
                    ->orWhereHas('authorizedPersons', function ($q2) use ($request) {
                        $q2->where('name', 'like', "%{$request->search}%");
                    });
            });
        }

        // Date range filter
        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $startDate = \Carbon\Carbon::parse($dates[0])->startOfDay();
                $endDate = \Carbon\Carbon::parse($dates[1])->endOfDay();
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Vertical filter
        if ($request->filled('vertical')) {
            $query->where('vertical_id', $request->vertical);
        }

        // Get applications with their timeline data
        $query->with([
            'createdBy',
            'vertical',
            'regionDetail',
            'entityDetails',
            'authorizedPersons',
            'approvalLogs' => function ($q) {
                $q->orderBy('created_at', 'asc');
            },
            'documentVerifications',
            'physicalDispatch'
            // Removed agreements relationship as it's not needed
        ])->orderBy('created_at', 'desc');

        if ($request->export === 'excel') {
            $distributors = $query->get();
            return Excel::download(new DistributorReportExport($distributors, 'tat'), 'tat_report.xlsx');
        }

        $distributors = $query->paginate(20);
        return view('distributor.reports.tat-report', compact('distributors'));
    }
}
