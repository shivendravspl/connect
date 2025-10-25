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
                                <td colspan="5" class="text-center text-muted py-4">No eligible distributors found.</td>
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
@endsection