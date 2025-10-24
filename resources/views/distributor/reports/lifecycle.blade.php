@extends('layouts.app')

@section('title', 'Distributor Lifecycle Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="page-title mb-0" style="font-size: 16px;">Distributor Lifecycle Report</h4>
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
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-search-line me-1"></i> Filter
                            </button>
                            <a href="{{ route('applications.lifecycle') }}" class="btn btn-secondary btn-sm">
                                <i class="ri-refresh-line me-1"></i> Reset
                            </a>
                            <a href="{{ route('applications.lifecycle', array_merge(request()->all(), ['export' => 'excel'])) }}" 
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
                                    <th style="font-size: 11px; padding: 6px;">Application Created</th>
                                    <th style="font-size: 11px; padding: 6px;">Approval Level 1 (RBM)</th>
                                    <th style="font-size: 11px; padding: 6px;">Approval Level 2 (GM)</th>
                                    <th style="font-size: 11px; padding: 6px;">Approval Level 3</th>
                                    <th style="font-size: 11px; padding: 6px;">MIS Verification</th>
                                    <th style="font-size: 11px; padding: 6px;">Physical Docs Status</th>
                                    <th style="font-size: 11px; padding: 6px;">Agreement Status</th>
                                    <th style="font-size: 11px; padding: 6px;">Final Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($distributors as $distributor)
                                @php
                                    // Get approval logs by role
                                    $rbmApproval = $distributor->approvalLogs->where('role', 'Regional Business Manager')->first();
                                    $gmApproval = $distributor->approvalLogs->where('role', 'General Manager')->first();
                                    $seApproval = $distributor->approvalLogs->where('role', 'Senior Executive')->first();
                                    
                                    // Get the latest action for each level
                                    $level1 = $distributor->approvalLogs->where('role', 'Regional Business Manager')->last();
                                    $level2 = $distributor->approvalLogs->where('role', 'General Manager')->last();
                                    $level3 = $distributor->approvalLogs->where('role', 'Senior Executive')->last();
                                @endphp
                                <tr>
                                    <td style="padding: 6px;">{{ $distributor->application_code ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">{{ $distributor->entityDetails->establishment_name ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">{{ $distributor->getAuthorizedOrEntityName() ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">{{ $distributor->created_at?->format('d-m-Y') ?? 'N/A' }}</td>
                                    
                                    <!-- Approval Level 1 - Regional Business Manager -->
                                    <td style="padding: 6px;">
                                        @if($level1)
                                            <span class="badge bg-{{ $level1->action == 'approved' ? 'success' : ($level1->action == 'rejected' ? 'danger' : 'warning') }}" style="font-size: 10px;">
                                                {{ ucfirst($level1->action) }}
                                            </span>
                                            <br>
                                            <small>{{ $level1->created_at?->format('d-m-Y') }}</small>
                                        @else
                                            <span class="badge bg-secondary" style="font-size: 10px;">Pending</span>
                                        @endif
                                    </td>
                                    
                                    <!-- Approval Level 2 - General Manager -->
                                    <td style="padding: 6px;">
                                        @if($level2)
                                            <span class="badge bg-{{ $level2->action == 'approved' ? 'success' : ($level2->action == 'rejected' ? 'danger' : 'warning') }}" style="font-size: 10px;">
                                                {{ ucfirst($level2->action) }}
                                            </span>
                                            <br>
                                            <small>{{ $level2->created_at?->format('d-m-Y') }}</small>
                                        @else
                                            <span class="badge bg-secondary" style="font-size: 10px;">Pending</span>
                                        @endif
                                    </td>
                                    
                                    <!-- Approval Level 3 - Senior Executive -->
                                    <td style="padding: 6px;">
                                        @if($level3)
                                            <span class="badge bg-{{ $level3->action == 'approved' ? 'success' : ($level3->action == 'rejected' ? 'danger' : ($level3->action == 'documents_verified' ? 'info' : 'warning')) }}" style="font-size: 10px;">
                                                {{ ucfirst(str_replace('_', ' ', $level3->action)) }}
                                            </span>
                                            <br>
                                            <small>{{ $level3->created_at?->format('d-m-Y') }}</small>
                                        @else
                                            <span class="badge bg-secondary" style="font-size: 10px;">Pending</span>
                                        @endif
                                    </td>
                                    
                                    <td style="padding: 6px;">{{ $distributor->mis_verified_at?->format('d-m-Y') ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">
                                        @if($distributor->physical_docs_status)
                                            <span class="badge bg-{{ $distributor->physical_docs_status == 'verified' ? 'success' : 'warning' }}" style="font-size: 10px;">
                                                {{ ucfirst($distributor->physical_docs_status) }}
                                            </span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td style="padding: 6px;">
                                        @if($distributor->agreement_status)
                                            <span class="badge bg-info" style="font-size: 10px;">
                                                {{ ucfirst($distributor->agreement_status) }}
                                            </span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td style="padding: 6px;">
                                        @if($distributor->final_status)
                                            <span class="badge bg-success" style="font-size: 10px;">
                                                {{ ucfirst($distributor->final_status) }}
                                            </span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center py-2" style="font-size: 12px;">No distributors found</td>
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