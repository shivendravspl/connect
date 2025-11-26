@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">
            <i class="ri-file-list-line me-2"></i> Security Cheques Management - All Distributors
        </h6>
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
                        <button type="submit" class="btn btn-primary">
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
            <h6 class="mb-0"><i class="ri-list-unordered me-2"></i> Eligible Distributors ({{ $applications->total() }})</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Establishment Name</th>
                            <th>Distributor Code</th>
                            <th>Security Cheque Details</th>
                            <th>Created By</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $application)
                            <tr>
                                <td>{{ $application->entityDetails->establishment_name ?? 'N/A' }}</td>
                                <td>
                                    <strong>{{ $application->distributor_code ?? 'Not Assigned' }}</strong>
                                </td>
                                <td>
                                    @php
                                        $securityCheques = $application->physicalDocumentChecks->flatMap->securityChequeDetails;
                                    @endphp
                                    
                                    @if($securityCheques->count() > 0)
                                        <div class="security-cheques-summary">
                                            @foreach($securityCheques as $cheque)
                                                <div class="cheque-detail mb-2 p-2 border rounded">
                                                    <small>
                                                        <strong>Cheque No:</strong> {{ $cheque->cheque_no ?? 'N/A' }}<br>
                                                        <strong>Date Obtained:</strong> {{ $cheque->date_obtained ? $cheque->date_obtained->format('d-m-Y') : 'N/A' }}<br>
                                                        <strong>Purpose:</strong> {{ $cheque->purpose ?? 'N/A' }}<br>
                                                        <strong>Date of Use:</strong> {{ $cheque->date_use ? $cheque->date_use->format('d-m-Y') : 'Not Used' }}<br>
                                                        <strong>Date Return:</strong> {{ $cheque->date_return ? $cheque->date_return->format('d-m-Y') : 'Not Returned' }}<br>
                                                        <strong>Status:</strong> 
                                                        @if($cheque->date_return)
                                                            <span class="badge bg-success">Returned</span>
                                                        @elseif($cheque->date_use)
                                                            <span class="badge bg-warning">In Use</span>
                                                        @else
                                                            <span class="badge bg-info">Held</span>
                                                        @endif
                                                    </small>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">No security cheques recorded</span>
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
                                        <i class="ri-edit-line me-1"></i> Manage Cheques
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No eligible distributors found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer py-2">
                {{ $applications->appends(request()->query())->links() }}
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