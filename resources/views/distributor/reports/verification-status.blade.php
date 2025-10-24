@extends('layouts.app')

@section('title', 'Verification Status Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="page-title mb-0" style="font-size: 16px;">Verification Status Report</h4>
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
                            <select name="doc_status" class="form-control form-control-sm">
                                <option value="">Select Document Status</option>
                                <option value="documents_pending" {{ request('doc_status') == 'documents_pending' ? 'selected' : '' }}>Documents Pending</option>
                                <option value="documents_verified" {{ request('doc_status') == 'documents_verified' ? 'selected' : '' }}>Documents Verified</option>
                                <option value="physical_docs_pending" {{ request('doc_status') == 'physical_docs_pending' ? 'selected' : '' }}>Physical Docs Pending</option>
                                <option value="physical_docs_verified" {{ request('doc_status') == 'physical_docs_verified' ? 'selected' : '' }}>Physical Docs Verified</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-search-line me-1"></i> Filter
                            </button>
                            <a href="{{ route('applications.verification-status') }}" class="btn btn-secondary btn-sm">
                                <i class="ri-refresh-line me-1"></i> Reset
                            </a>
                            <a href="{{ route('applications.verification-status', array_merge(request()->all(), ['export' => 'excel'])) }}" 
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
                                    <th style="font-size: 11px; padding: 6px;">Document Verification Status</th>
                                    <th style="font-size: 11px; padding: 6px;">MIS Verified At</th>
                                    <th style="font-size: 11px; padding: 6px;">Physical Document Status</th>
                                    <th style="font-size: 11px; padding: 6px;">Verified By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($distributors as $distributor)
                                <tr>
                                    <td style="padding: 6px;">{{ $distributor->application_code ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">{{ $distributor->entityDetails->establishment_name ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">{{ $distributor->getAuthorizedOrEntityName() ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">
                                        <span class="badge bg-{{ \App\Helpers\Helpers::getVerificationBadgeColor($distributor->doc_verification_status) }}" style="font-size: 10px;">
                                            {{ ucfirst(str_replace('_', ' ', $distributor->doc_verification_status)) }}
                                        </span>
                                    </td>
                                    <td style="padding: 6px;">{{ $distributor->mis_verified_at?->format('d-m-Y') ?? 'N/A' }}</td>
                                    <td style="padding: 6px;">
                                        <span class="badge bg-{{ \App\Helpers\Helpers::getVerificationBadgeColor($distributor->physical_docs_status) }}" style="font-size: 10px;">
                                            {{ ucfirst(str_replace('_', ' ', $distributor->physical_docs_status)) }}
                                        </span>
                                    </td>
                                    <td style="padding: 6px;">{{ $distributor->documentVerifications->last()?->user?->name ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-2" style="font-size: 12px;">No distributors found</td>
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