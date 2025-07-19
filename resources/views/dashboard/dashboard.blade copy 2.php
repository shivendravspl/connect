@extends('layouts.app')

@push('styles')
<style>
    /* Status badge colors */
    .bg-approved,
    .bg-distributorship_created {
        background-color: #28a745;
    }

    .bg-rejected,
    .bg-mis_rejected {
        background-color: #dc3545;
    }

    .bg-pending,
    .bg-submitted,
    .bg-under_review,
    .bg-mis_processing,
    .bg-agreement_created,
    .bg-documents_received {
        background-color: #ffc107;
        color: #212529;
    }

    .bg-reverted {
        background-color: #fd7e14;
    }

    .bg-on_hold {
        background-color: #17a2b8;
    }

    /* Status card borders */
    .approved,
    .distributorship_created {
        border-left-color: #28a745;
    }

    .rejected,
    .mis_rejected {
        border-left-color: #dc3545;
    }

    .pending,
    .submitted,
    .under_review,
    .mis_processing,
    .agreement_created,
    .documents_received {
        border-left-color: #ffc107;
    }

    .reverted {
        border-left-color: #fd7e14;
    }

    .on_hold {
        border-left-color: #17a2b8;
    }

    /* MIS process indicators */
    .doc-status-verified {
        color: #28a745;
    }

    .doc-status-pending {
        color: #ffc107;
    }

    .doc-status-rejected,
    .doc-status-not_verified {
        color: #dc3545;
    }

    /* Compact table styling */
    .compact-table td,
    .compact-table th {
        padding: 0.5rem;
        vertical-align: middle;
    }

    /* Document verification and physical document lists */
    .doc-verification-list,
    .physical-doc-list {
        list-style: none;
        padding-left: 0;
        margin-bottom: 0;
    }

    .doc-verification-list li,
    .physical-doc-list li {
        margin-bottom: 0.25rem;
    }

    /* General styling */
    .container-fluid {
        padding: 1rem;
    }

    .page-title {
        font-size: 1.25rem;
    }

    .header-title {
        font-size: 1rem;
    }

    .card-title {
        font-size: 0.9rem;
    }

    .btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.25em 0.5em;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.03);
    }

    .pagination {
        margin-top: 1rem;
        font-size: 0.8rem;
    }

    .card {
        margin-bottom: 1rem;
    }

    .card-body {
        padding: 1rem;
    }

    .nav-tabs .nav-link {
        font-size: 0.8rem;
        padding: 0.5rem 0.75rem;
    }

    @media (max-width: 768px) {
        body {
            font-size: 13px;
        }

        .container-fluid {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        .card-body {
            padding: 0.75rem;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .compact-table th,
        .compact-table td {
            padding: 0.3rem;
            white-space: nowrap;
        }

        .nav-tabs {
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .nav-tabs .nav-item {
            white-space: nowrap;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Dashboard</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title">Welcome, {{ Auth::user()->emp_name }}!</h4>
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-muted mb-2">Your Role</p>
                            <h5 class="mb-0">
                                <span class="badge bg-primary">
                                    {{ Auth::user()->emp_designation }}
                                    @if(Auth::user()->employee->isMisTeam())
                                    (MIS Team)
                                    @endif
                                </span>
                            </h5>
                        </div>
                        @if(isset($data['counts']['pending']) && $data['counts']['pending'] > 0)
                        <div class="flex-shrink-0">
                            <span class="badge bg-danger rounded-pill">
                                {{ $data['counts']['pending'] }} Pending Approval{{ $data['counts']['pending'] > 1 ? 's' : '' }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Dashboard Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="header-title">Application Dashboard</h4>
                </div>

                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom mb-3">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#pending-tab">
                                Pending Approvals ({{ $data['counts']['pending'] ?? 0 }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#my-tab">
                                My Applications ({{ $data['counts']['my'] ?? 0 }})
                            </a>
                        </li>
                        @if(Auth::user()->employee->isMisTeam())
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#mis-tab">
                                MIS Processing ({{ $data['counts']['mis'] ?? 0 }})
                            </a>
                        </li>
                        @endif
                    </ul>

                    <div class="tab-content">
                        <!-- Pending Approvals Tab -->
                        <div class="tab-pane fade show active" id="pending-tab">
                            @if($pendingApplications->isEmpty())
                            <div class="alert alert-info">No applications pending your approval.</div>
                            @else
                            <div class="table-responsive">
                                <table class="table table-hover compact-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>App ID</th>
                                            <th>Territory</th>
                                            <th>Current Stage</th>
                                            <th>Status</th>
                                            <th>Submitted</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pendingApplications as $application)
                                        <tr class="status-card {{ $application->status_badge }}">
                                            <td>{{ $application->application_code ?? 'N/A' }}</td>
                                            <td>{{ $application->territoryDetail->territory_name ?? 'N/A' }}</td>
                                            <td>{{ ucfirst($application->approval_level ?? 'N/A') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $application->status_badge }}">
                                                    {{ ucwords(str_replace('_', ' ', $application->status)) }}
                                                </span>
                                                @if($application->status === 'on_hold' && $application->follow_up_date)
                                                <small class="text-muted d-block">
                                                    Until: {{ \Carbon\Carbon::parse($application->follow_up_date)->format('d M Y') }}
                                                </small>
                                                @endif
                                            </td>
                                            <td>{{ $application->created_at->format('d M Y') }}</td>
                                            <td>
                                                @php
                                                $isApprover = $application->current_approver_id === Auth::user()->emp_id;
                                                $isManager = $application->createdBy && $application->createdBy->emp_reporting === Auth::user()->emp_id;
                                                @endphp
                                                @if($isApprover || $isManager)
                                                <a href="{{ route('approvals.show', $application) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> Review
                                                </a>
                                                @else
                                                <span class="badge bg-secondary">
                                                    Awaiting {{ $application->currentApprover->emp_name ?? 'Approval' }}
                                                </span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{ $pendingApplications->links() }}
                            </div>
                            @endif
                        </div>

                        <!-- My Applications Tab -->
                        <div class="tab-pane fade" id="my-tab">
                            @if($myApplications->isEmpty())
                            <div class="alert alert-info">You haven't submitted or acted on any applications yet.</div>
                            @else
                            <div class="table-responsive">
                                <table class="table table-hover compact-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>App ID</th>
                                            <th>Territory</th>
                                            <th>Current Stage</th>
                                            <th>Status</th>
                                            <th>Last Action</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($myApplications as $application)
                                        <tr class="status-card {{ $application->status_badge }}">
                                            <td>{{ $application->application_code ?? 'N/A' }}</td>
                                            <td>{{ $application->territoryDetail->territory_name ?? 'N/A' }}</td>
                                            <td>{{ ucfirst($application->approval_level ?? 'N/A') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $application->status_badge }}">
                                                    {{ ucwords(str_replace('_', ' ', $application->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($log = $application->approvalLogs->last())
                                                {{ ucfirst($log->action) }} by {{ $log->user->name ?? 'Unknown' }}
                                                <small class="text-muted d-block">
                                                    {{ $log->created_at->format('d M Y H:i') }}
                                                </small>
                                                @else
                                                <span class="text-muted">No actions</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('approvals.show', $application) }}"
                                                        class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    @if($application->status === 'reverted' && $application->created_by === Auth::user()->emp_id)
                                                    <a href="{{ route('applications.edit', $application) }}"
                                                        class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    @endif
                                                    @if($application->status === 'distributorship_created')
                                                    <span class="badge bg-success p-2">
                                                        <i class="fas fa-check-circle"></i> Finalized
                                                    </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{ $myApplications->links() }}
                            </div>
                            @endif
                        </div>

                        <!-- MIS Tasks Tab -->
                        @if(Auth::user()->employee->isMisTeam())
                        <div class="tab-pane fade" id="mis-tab">
                            @if($misApplications->isEmpty())
                            <div class="alert alert-info">No applications pending MIS processing.</div>
                            @else
                            <div class="table-responsive">
                                <table class="table table-hover compact-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>App ID</th>
                                            <th>Territory</th>
                                            <th>Status</th>
                                            <th>Document Verification</th>
                                            <th>Physical Documents</th>
                                            <th>Next Step</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($misApplications as $application)
                                        <tr class="status-card {{ $application->status_badge }}">
                                            <td>{{ $application->application_code ?? 'N/A' }}</td>
                                            <td>{{ $application->territoryDetail->territory_name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $application->status_badge }}">
                                                    {{ ucwords(str_replace('_', ' ', $application->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($application->documentVerifications->isNotEmpty())
                                                <ul class="doc-verification-list">
                                                    @foreach($application->documentVerifications as $doc)
                                                    <li>
                                                        <i class="fas fa-file-alt"></i>
                                                        {{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}
                                                        <span class="doc-status-{{ $doc->status }}">
                                                            ({{ ucfirst($doc->status) }})
                                                        </span>
                                                        @if($doc->remarks)
                                                        <small class="text-muted d-block">{{ $doc->remarks }}</small>
                                                        @endif
                                                    </li>
                                                    @endforeach
                                                </ul>
                                                @else
                                                <span class="text-muted">Not started</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($application->physicalDocuments->isNotEmpty())
                                                <ul class="physical-doc-list">
                                                    @foreach($application->physicalDocuments as $doc)
                                                    <li>
                                                        <i class="fas fa-file"></i>
                                                        {{ ucfirst(str_replace('_', ' ', $doc->type)) }}:
                                                        <span class="doc-status-{{ $doc->received ? ($doc->verified ? 'verified' : 'pending') : 'not_verified' }}">
                                                            {{ $doc->received ? ($doc->verified ? 'Verified' : 'Received') : 'Not Received' }}
                                                        </span>
                                                    </li>
                                                    @endforeach
                                                </ul>
                                                @else
                                                <span class="text-muted">Not started</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    {{--<a href="{{ route('approvals.show', $application) }}"
                                                        class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>--}}
                                                    @if($application->status === 'mis_processing')
                                                    <a href="{{ route('approvals.verify-documents', $application) }}"
                                                        class="btn btn-sm btn-success">
                                                        <i class="fas fa-check"></i> Verify Documents
                                                    </a>
                                                    @elseif($application->status === 'document_verified')
                                                    <a href="{{ route('approvals.upload-agreement', $application) }}"
                                                        class="btn btn-sm btn-info">
                                                        <i class="fas fa-upload"></i> Upload Agreement
                                                    </a>
                                                    @elseif($application->status === 'agreement_created')
                                                    <a href="{{ route('approvals.track-documents', $application) }}"
                                                        class="btn btn-sm btn-info">
                                                        <i class="fas fa-file"></i> Update Physical Docs
                                                    </a>
                                                    @elseif($application->status === 'documents_received')
                                                    <a href="{{ route('approvals.track-documents', $application) }}"
                                                        class="btn btn-sm btn-info">
                                                        <i class="fas fa-file"></i> Update Physical Docs
                                                    </a>
                                                    @elseif($application->status === 'distributorship_created')
                                                    <span class="badge bg-success p-2">
                                                        <i class="fas fa-check-circle"></i> Finalized
                                                    </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                {{ $misApplications->links() }}
                            </div>
                            @endif
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Summary Cards -->
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Applications</h5>
                    <h2 class="mb-0">{{ $data['counts']['total'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Approved</h5>
                    <h2 class="mb-0">{{ $data['counts']['approved'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">In Process</h5>
                    <h2 class="mb-0">{{ $data['counts']['in_process'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Rejected/Reverted</h5>
                    <h2 class="mb-0">{{ $data['counts']['rejected'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
     $(document).ready(function() {
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Auto-refresh counts every 2 minutes
        setInterval(function() {
            $.get('{{ route("dashboard.status-counts") }}', function(data) {
                $('.nav-tabs a[href="#pending-tab"]').text('Pending Approvals (' + (data.pending || 0) + ')');
                $('.nav-tabs a[href="#my-tab"]').text('My Applications (' + (data.my || 0) + ')');
                @if(Auth::user()->employee-> isMisTeam())
                $('.nav-tabs a[href="#mis-tab"]').text('MIS Processing (' + (data.mis || 0) + ')');
                @endif
            }).fail(function(xhr) {
                console.error('Error fetching status counts:', xhr.responseText);
            });
        }, 120000);
    });
</script>
@endpush