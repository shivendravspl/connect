@extends('layouts.app')

@section('title', 'Distributor Summary Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="page-title mb-0" style="font-size: 16px;">
                    {{ ucwords(str_replace('-', ' ', $reportType ?? 'summary')) }} Report
                </h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <!-- Report Type Selector -->
                    <form method="GET" class="row g-2 align-items-center mb-3">
                        <div class="col-md-3">
                            <select name="report_type" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="summary" {{ ($reportType ?? 'summary') == 'summary' ? 'selected' : '' }}>Summary Report</option>
                                <option value="approval" {{ $reportType == 'approval' ? 'selected' : '' }}>Approval Status</option>
                                <option value="verification" {{ $reportType == 'verification' ? 'selected' : '' }}>Verification Status</option>
                                <option value="pending" {{ $reportType == 'pending' ? 'selected' : '' }}>Pending Documents</option>
                                <option value="completed" {{ $reportType == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="rejected" {{ $reportType == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                    </form>

                    <!-- Simplified Filters -->
                    <form method="GET" class="row g-2 align-items-center mb-3">
                        <input type="hidden" name="report_type" value="{{ $reportType }}">
                        
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control form-control-sm" 
                                   placeholder="Application Code or Establishment Name" 
                                   value="{{ request('search') }}">
                        </div>
                        
                        <div class="col-md-3">
                            <select name="status" class="form-control form-control-sm">
                                <option value="All">All Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="sales_approval" {{ request('status') == 'sales_approval' ? 'selected' : '' }}>Sales Approval</option>
                                <option value="mis_verification" {{ request('status') == 'mis_verification' ? 'selected' : '' }}>MIS Verification</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-search-line me-1"></i> Filter
                            </button>
                            <a href="{{ route('applications.distributor-summary', ['report_type' => $reportType]) }}" 
                               class="btn btn-secondary btn-sm">
                                <i class="ri-refresh-line me-1"></i> Reset
                            </a>
                        </div>
                    </form>

                    <!-- Export Button -->
                    <div class="mb-3">
                        @if(request()->anyFilled(['search', 'status']))
                        <div class="alert alert-info py-2" style="font-size: 12px;">
                            <i class="ri-information-line me-1"></i>
                            Showing filtered results ({{ $distributors->total() }} records). Export will include only the filtered data.
                        </div>
                        @endif
                        
                        <a href="{{ route('applications.distributor-summary', array_merge(request()->all(), ['export' => 'excel'])) }}" 
                           class="btn btn-success btn-sm">
                            <i class="ri-file-excel-line me-1"></i> Export Excel ({{ $distributors->total() }} records)
                        </a>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive" style="font-size: 12px;">
                        <table class="table table-bordered table-striped table-sm mb-2">
                            <thead>
                                <tr>
                                    <th>Application Code</th>
                                    <th>Establishment Name</th>
                                    <th>Authorized Person</th>
                                    <th>Vertical</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created Date</th>
                                    @if($reportType == 'approval')
                                        <th>Current Approver</th>
                                        <th>Approval Level</th>
                                    @endif
                                    @if($reportType == 'verification')
                                        <th>Doc Verification</th>
                                        <th>Physical Docs</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($distributors as $distributor)
                                <tr>
                                    <td>{{ $distributor->application_code ?? 'N/A' }}</td>
                                    <td>{{ $distributor->entityDetails->establishment_name ?? 'N/A' }}</td>
                                    <td>{{ $distributor->getAuthorizedOrEntityName() ?? 'N/A' }}</td>
                                    <td>{{ $distributor->vertical?->vertical_name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ \App\Helpers\Helpers::getStatusBadgeColor($distributor->status) }}">
                                            {{ ucfirst(str_replace('_', ' ', $distributor->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $distributor->createdBy?->emp_name ?? 'N/A' }}</td>
                                    <td>{{ $distributor->created_at?->format('d-m-Y') ?? 'N/A' }}</td>
                                    @if($reportType == 'approval')
                                        <td>{{ $distributor->currentApprover?->emp_name ?? 'N/A' }}</td>
                                        <td>{{ ucfirst($distributor->approval_level) }}</td>
                                    @endif
                                    @if($reportType == 'verification')
                                        <td>{{ $distributor->doc_verification_status ?? 'N/A' }}</td>
                                        <td>{{ $distributor->physical_docs_status ?? 'N/A' }}</td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ $reportType == 'approval' ? 9 : ($reportType == 'verification' ? 9 : 7) }}" 
                                        class="text-center py-2">No data found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div style="font-size: 12px;">
                            Showing {{ $distributors->firstItem() }} to {{ $distributors->lastItem() }} of {{ $distributors->total() }} entries
                        </div>
                        <div>
                            {{ $distributors->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.page-title { font-size: 16px !important; font-weight: 600; }
.form-control-sm { font-size: 12px; }
.btn-sm { font-size: 12px; }
.table th, .table td { padding: 6px; font-size: 12px; }
.badge { font-size: 10px; }
</style>
@endsection