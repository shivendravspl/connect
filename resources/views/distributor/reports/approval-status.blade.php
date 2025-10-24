@extends('layouts.app')

@section('title', 'Approval Status Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="page-title mb-0" style="font-size: 16px;">Approval Status Report</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <!-- Filters and Export in same row -->
                    <form method="GET" class="row g-2 align-items-center mb-3">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control form-control-sm" 
                                   placeholder="Search by Application Code / Establishment Name" 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="approval_level" class="form-control form-control-sm">
                                <option value="">Select Approval Level</option>
                                <option value="level1" {{ request('approval_level') == 'level1' ? 'selected' : '' }}>Level 1 (RBM)</option>
                                <option value="level2" {{ request('approval_level') == 'level2' ? 'selected' : '' }}>Level 2 (ZBM)</option>
                                <option value="level3" {{ request('approval_level') == 'level3' ? 'selected' : '' }}>Level 3 (GM Sales)</option>
                                <option value="approved" {{ request('approval_level') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('approval_level') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-search-line me-1"></i> Filter
                            </button>
                            <a href="{{ route('applications.approval-status') }}" class="btn btn-secondary btn-sm">
                                <i class="ri-refresh-line me-1"></i> Reset
                            </a>
                            <a href="{{ route('applications.approval-status', array_merge(request()->all(), ['export' => 'excel'])) }}" 
                               class="btn btn-success btn-sm">
                                <i class="ri-file-excel-line me-1"></i> Export Excel
                            </a>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive" style="font-size: 12px;">
                        <table class="table table-bordered table-striped table-sm mb-2">
                            <thead>
                                <tr>
                                    <th style="font-size: 11px; padding: 6px;">Application Code</th>
                                    <th style="font-size: 11px; padding: 6px;">Establishment Name</th>
                                    <th style="font-size: 11px; padding: 6px;">Authorized Person</th>
                                    <th style="font-size: 11px; padding: 6px;">Current Approval Level</th>
                                    <th style="font-size: 11px; padding: 6px;">Current Approver</th>
                                    <th style="font-size: 11px; padding: 6px;">Status</th>
                                    <th style="font-size: 11px; padding: 6px;">Last Updated</th>
                                    <th style="font-size: 11px; padding: 6px;">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($distributors as $distributor)
                                <tr>
                                    <td style="padding: 6px;">{{ $distributor->application_code ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">{{ $distributor->entityDetails->establishment_name ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">{{ $distributor->getAuthorizedOrEntityName() ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">{{ ucfirst($distributor->approval_level) }}</td>
                                    <td style="padding: 6px;">{{ $distributor->currentApprover?->name ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">
                                        <span class="badge bg-{{ \App\Helpers\Helpers::getStatusBadgeColor($distributor->status) }}" style="font-size: 10px;">
                                            {{ ucfirst(str_replace('_', ' ', $distributor->status)) }}
                                        </span>
                                    </td>
                                    <td style="padding: 6px;">{{ $distributor->updated_at?->format('d-m-Y H:i') ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">{{ $distributor->approvalLogs->last()?->remarks ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-2" style="font-size: 12px;">No distributors found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-2" style="font-size: 12px;">
                        <div>
                            Showing {{ $distributors->firstItem() }} to {{ $distributors->lastItem() }} of {{ $distributors->total() }} entries
                        </div>
                        <div>
                            {{ $distributors->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.page-title {
    font-size: 16px !important;
    font-weight: 600;
}
.form-control-sm {
    font-size: 12px;
    height: calc(1.5em + 0.5rem + 2px);
}
.btn-sm {
    font-size: 12px;
    padding: 0.25rem 0.5rem;
}
.table-sm th,
.table-sm td {
    padding: 6px;
    font-size: 12px;
}
.badge {
    font-size: 10px;
    padding: 0.25em 0.4em;
}
.pagination {
    font-size: 12px;
    margin-bottom: 0;
}
.pagination .page-link {
    padding: 0.25rem 0.5rem;
    font-size: 12px;
}
.card-body {
    padding: 1rem;
}
</style>
@endsection