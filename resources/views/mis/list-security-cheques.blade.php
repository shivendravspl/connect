@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center border-bottom">
        <div>
            <h6 class="mb-0 fw-bold text-dark">
                <i class="ri-shield-check-line me-2 text-primary"></i>
                Security Cheques Management - All Distributors
            </h6>
            <small class="text-muted">Eligible Distributors: <strong>{{ $paginatedCheques->total() }}</strong> records</small>
        </div>

        <div>
            <a href="{{ route('mis.list-security-cheques', ['search' => request('search'), 'export' => 'excel']) }}" 
            class="btn btn-success btn-sm shadow-sm d-flex align-items-center gap-2">
                <i class="ri-file-excel-2-fill fs-5"></i>
                <span class="d-none d-md-inline">Export to Excel</span>
            </a>
        </div>
    </div>

    <!-- Search Form -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('mis.list-security-cheques') }}">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search by establishment name or distributor code..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="ri-search-line me-1"></i> Search
                        </button>
                        @if(request('search'))
                            <a href="{{ route('mis.list-security-cheques') }}" class="btn btn-outline-secondary ms-2">
                                Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="ri-list-unordered me-2"></i> Eligible Distributors ({{ $paginatedCheques->total() }})</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Establishment Name</th>
                        <th>Distributor Code</th>
                        <th>Cheque No</th>
                        <th>Date Obtained</th>
                        <th>Purpose</th>
                        <th>Date of Use</th>
                        <th>Date Return</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>App Status</th>
                        <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginatedCheques as $item)
                            @php
                                $application = $item['application'];
                                $cheque = $item['cheque'];
                            @endphp
                            <tr>
                                <td>{{ $application->entityDetails->establishment_name ?? 'N/A' }}</td>
                                <td><strong>{{ $application->distributor_code ?? 'Not Assigned' }}</strong></td>
                                <td>{{ $cheque?->cheque_no ?? '—' }}</td>
                                <td>{{ $cheque?->date_obtained?->format('d-m-Y') ?? '—' }}</td>
                                <td>{{ $cheque?->purpose ?? '—' }}</td>
                                <td>
                                    @if($cheque?->date_use)
                                        {{ $cheque->date_use->format('d-m-Y') }}
                                    @else
                                        <span class="text-muted">Not Used</span>
                                    @endif
                                </td>
                                <td>
                                    @if($cheque?->date_return)
                                        {{ $cheque->date_return->format('d-m-Y') }}
                                    @else
                                        <span class="text-muted">Not Returned</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$cheque)
                                        <span class="badge bg-secondary">No Cheque</span>
                                    @elseif($cheque->date_return)
                                        <span class="badge bg-success">Returned</span>
                                    @elseif($cheque->date_use)
                                        <span class="badge bg-warning">In Use</span>
                                    @else
                                        <span class="badge bg-info">Held</span>
                                    @endif
                                </td>
                                <td>{{ $application->createdBy->emp_name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $application->status === 'distributorship_created' ? 'info' : 'success' }}">
                                        {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('mis.manage-security-cheques', $application) }}" 
                                    class="btn btn-sm btn-primary">
                                        <i class="ri-edit-line"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    No security cheques found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer py-2">
                {{ $paginatedCheques->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<style>
.cheque-detail {
    background-color: #f8f9fa;
    font-size: 0.85rem;
}
.cheque-detail:last-child {
    margin-bottom: 0 !important;
}
.security-cheques-summary {
    max-height: 200px;
    overflow-y: auto;
}
</style>
@endsection