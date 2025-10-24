@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title small">TAT Report - Distributor Onboarding</h5>
                    <div class="card-tools">
                        <form action="{{ route('applications.reports.tat') }}" method="GET" class="form-inline">
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                                <input type="text" name="date_range" class="form-control form-control-sm ms-2 daterange" placeholder="Date Range" value="{{ request('date_range') }}">
                                <select name="status" class="form-control form-control-sm ms-2 small">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                                    <a href="{{ route('applications.reports.tat') }}" class="btn btn-secondary btn-sm ms-1">Reset</a>
                                    <button type="submit" name="export" value="excel" class="btn btn-success btn-sm ms-1">Export Excel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="small">
                                <tr>
                                    <th class="small">Application Code</th>
                                    <th class="small">Establishment Name</th>
                                    <th class="small">Authorized Person</th>
                                    <th class="small">Vertical</th>
                                    <th class="small">Region</th>
                                    <th class="small">Application Date</th>
                                    <th class="small">Current Status</th>
                                    <th class="small">RBM TAT</th>
                                    <th class="small">GM TAT</th>
                                    <th class="small">SE TAT</th>
                                    <th class="small">MIS TAT</th>
                                    <th class="small">Physical Docs TAT</th>
                                    <th class="small">Total TAT</th>
                                    <th class="small">Status</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                @forelse($distributors as $distributor)
                                @php
                                    // Calculate TAT values for display
                                    $rbmTat = \App\Helpers\helpers::calculateApprovalTat($distributor, 'Regional Business Manager');
									$gmTat = \App\Helpers\helpers::calculateApprovalTat($distributor, 'General Manager');
									$seTat = \App\Helpers\helpers::calculateApprovalTat($distributor, 'Senior Executive');
									$misTat = \App\Helpers\helpers::calculateMisTat($distributor);
									$physicalDocsTat = \App\Helpers\helpers::calculatePhysicalDocsTat($distributor);
									$totalTat = \App\Helpers\helpers::calculateTotalTat($distributor);
									$tatStatus = \App\Helpers\helpers::getTatStatus($totalTat);
                                @endphp
                                <tr>
                                    <td class="small">{{ $distributor->application_code }}</td>
                                    <td class="small">{{ $distributor->entityDetails->establishment_name ?? 'N/A' }}</td>
                                    <td class="small">{{ $distributor->getAuthorizedOrEntityName() ?? 'N/A' }}</td>
                                    <td class="small">{{ $distributor->vertical->vertical_name ?? 'N/A' }}</td>
                                    <td class="small">{{ $distributor->regionDetail->region_name ?? 'N/A' }}</td>
                                    <td class="small">{{ $distributor->created_at->format('d-m-Y') }}</td>
                                    <td class="small">
                                        <span class="badge bg-{{ \App\Helpers\helpers::getStatusBadgeColor($distributor->status) }}">{{ ucfirst(str_replace('_', ' ', $distributor->status)) }}
                                        </span>
                                    </td>
                                    <td class="small">{!! $rbmTat !!}</td>
                                    <td class="small">{!! $gmTat !!}</td>
                                    <td class="small">{!! $seTat !!}</td>
                                    <td class="small">{!! $misTat !!}</td>
                                    <td class="small">{!! $physicalDocsTat !!}</td>
                                    <td class="small">
                                        <span class="badge bg-{{ $tatStatus == 'Within SLA' ? 'success' : ($tatStatus == 'Moderate Delay' ? 'warning' : 'danger') }}">
                                            {{ $totalTat }}
                                        </span>
                                    </td>
                                    <td class="small">
                                        <span class="badge bg-{{ $tatStatus == 'Within SLA' ? 'success' : ($tatStatus == 'Moderate Delay' ? 'warning' : 'danger') }}">
                                            {{ $tatStatus }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="14" class="text-center small">No records found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $distributors->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.daterange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                format: 'DD-MM-YYYY'
            }
        });

        $('.daterange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' to ' + picker.endDate.format('DD-MM-YYYY'));
        });

        $('.daterange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });
</script>
@endpush