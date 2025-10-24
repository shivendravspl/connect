@extends('layouts.app')

@section('title', 'Rejected Applications Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="page-title mb-0" style="font-size: 16px;">Rejected Applications</h4>
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
                            <a href="{{ route('applications.rejected') }}" class="btn btn-secondary btn-sm">
                                <i class="ri-refresh-line me-1"></i> Reset
                            </a>
                            <a href="{{ route('applications.rejected', array_merge(request()->all(), ['export' => 'excel'])) }}" 
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
                                    <th style="font-size: 11px; padding: 6px;">Rejected By</th>
                                    <th style="font-size: 11px; padding: 6px;">Rejection Reason</th>
                                    <th style="font-size: 11px; padding: 6px;">Rejection Date</th>
                                    <th style="font-size: 11px; padding: 6px;">Follow Up Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($distributors as $distributor)
                                @php
                                    $lastRejection = $distributor->approvalLogs->where('action', 'rejected')->last();
                                @endphp
                                <tr>
                                    <td style="padding: 6px;">{{ $distributor->application_code ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">{{ $distributor->entityDetails->establishment_name ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">{{ $distributor->getAuthorizedOrEntityName() ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">{{ $lastRejection?->user?->name ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">{{ $lastRejection?->remarks ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">{{ $lastRejection?->created_at?->format('d-m-Y') ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">{{ $lastRejection?->follow_up_date?->format('d-m-Y') ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-2" style="font-size: 12px;">No rejected distributors found</td>
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