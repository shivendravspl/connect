@extends('layouts.app')

@section('title', 'TAT Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="page-title mb-0" style="font-size: 16px;">TAT Report</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-3">
                    <!-- Simplified Filters -->
                    <form method="GET" class="row g-2 align-items-center mb-3">
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
                            <a href="{{ route('applications.reports.tat') }}" class="btn btn-secondary btn-sm">
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
                        
                        <a href="{{ route('applications.reports.tat', array_merge(request()->all(), ['export' => 'excel'])) }}" 
                           class="btn btn-success btn-sm">
                            <i class="ri-file-excel-line me-1"></i> Export Excel ({{ $distributors->total() }} records)
                        </a>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive" style="font-size: 12px;">
                        <table class="table table-bordered table-striped table-sm mb-2">
                            <thead>
                                <tr>
                                    <th>App Code</th>
                                    <th>Establishment Name</th>
                                    <th>Authorized Person</th>
                                    <th>Vertical</th>
                                    <th>App Date</th>
                                    <th>Status</th>
                                    <th>RBM TAT</th>
                                    <th>GM TAT</th>
                                    <th>SE TAT</th>
                                    <th>MIS TAT</th>
                                    <th>Physical TAT</th>
                                    <th>Total TAT</th>
                                    <th>TAT Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($distributors as $distributor)
                                <tr>
                                    <td>{{ $distributor->application_code ?? 'N/A' }}</td>
                                    <td>{{ $distributor->entityDetails->establishment_name ?? 'N/A' }}</td>
                                    <td>{{ $distributor->getAuthorizedOrEntityName() ?? 'N/A' }}</td>
                                    <td>{{ $distributor->vertical?->vertical_name ?? 'N/A' }}</td>
                                    <td>{{ $distributor->created_at?->format('d-m-Y') ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ \App\Helpers\Helpers::getStatusBadgeColor($distributor->status) }}" style="font-size: 10px;">
                                            {{ ucfirst(str_replace('_', ' ', $distributor->status)) }}
                                        </span>
                                    </td>
                                    
                                    <!-- TAT Columns -->
                                    <td>
                                        @php
                                            $rbmLog = $distributor->approvalLogs->where('role', 'Regional Business Manager')->first();
                                            $rbmTat = $rbmLog ? ($distributor->created_at->diffInDays($rbmLog->created_at) > 0 ? $distributor->created_at->diffInDays($rbmLog->created_at) . ' days' : 'Same day') : 'Pending';
                                        @endphp
                                        {{ $rbmTat }}
                                    </td>
                                    <td>
                                        @php
                                            $gmLog = $distributor->approvalLogs->where('role', 'General Manager')->first();
                                            $gmTat = $gmLog ? (($prev = $distributor->approvalLogs->where('created_at', '<', $gmLog->created_at)->first()) ? ($prev->created_at->diffInDays($gmLog->created_at) > 0 ? $prev->created_at->diffInDays($gmLog->created_at) . ' days' : 'Same day') : 'N/A') : 'Pending';
                                        @endphp
                                        {{ $gmTat }}
                                    </td>
                                    <td>
                                        @php
                                            $seLog = $distributor->approvalLogs->where('role', 'Senior Executive')->first();
                                            $seTat = $seLog ? (($prev = $distributor->approvalLogs->where('created_at', '<', $seLog->created_at)->first()) ? ($prev->created_at->diffInDays($seLog->created_at) > 0 ? $prev->created_at->diffInDays($seLog->created_at) . ' days' : 'Same day') : 'N/A') : 'Pending';
                                        @endphp
                                        {{ $seTat }}
                                    </td>
                                    <td>
                                        @if($distributor->mis_verified_at)
                                            @php
                                                $finalApp = $distributor->approvalLogs->where('action', 'approved')->last();
                                                $misTat = $finalApp ? ($finalApp->created_at->diffInDays($distributor->mis_verified_at) > 0 ? $finalApp->created_at->diffInDays($distributor->mis_verified_at) . ' days' : 'Same day') : 'N/A';
                                            @endphp
                                            {{ $misTat }}
                                        @else
                                            Pending
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $dispatch = $distributor->physicalDispatch;
                                            $physTat = ($dispatch && $dispatch->dispatch_date && $distributor->mis_verified_at) ? ($distributor->mis_verified_at->diffInDays($dispatch->dispatch_date) > 0 ? $distributor->mis_verified_at->diffInDays($dispatch->dispatch_date) . ' days' : 'Same day') : 'Pending';
                                        @endphp
                                        {{ $physTat }}
                                    </td>
                                    <td>
                                        @php
                                            $endDate = null;
                                            if (in_array($distributor->status, ['completed', 'distributorships_created'])) {
                                                $endDate = $distributor->physicalDispatch?->dispatch_date ?? $distributor->updated_at;
                                            } elseif ($distributor->mis_verified_at) {
                                                $endDate = $distributor->mis_verified_at;
                                            } elseif ($distributor->approvalLogs->isNotEmpty()) {
                                                $endDate = $distributor->approvalLogs->last()->created_at;
                                            } else {
                                                $endDate = now();
                                            }
                                            $totalTat = $distributor->created_at->diffInDays($endDate) . ' days';
                                        @endphp
                                        {{ $totalTat }}
                                    </td>
                                    <td>
                                        @php
                                            $tatDays = $distributor->created_at->diffInDays($endDate ?? now());
                                            $status = $tatDays <= 7 ? 'Within SLA' : ($tatDays <= 14 ? 'Moderate Delay' : 'Critical Delay');
                                        @endphp
                                        <span class="badge bg-{{ $tatDays <= 7 ? 'success' : ($tatDays <= 14 ? 'warning' : 'danger') }}" style="font-size: 10px;">
                                            {{ $status }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="13" class="text-center py-2" style="font-size: 12px;">No data found</td>
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
.form-control-sm { font-size: 12px; height: calc(1.5em + 0.5rem + 2px); }
.btn-sm { font-size: 12px; padding: 0.25rem 0.5rem; }
.table th, .table td { padding: 6px; font-size: 12px; }
.badge { font-size: 10px; padding: 0.25em 0.4em; }
.pagination { font-size: 12px; margin-bottom: 0; }
.pagination .page-link { padding: 0.25rem 0.5rem; font-size: 12px; }
.card-body { padding: 1rem; }
</style>
@endsection