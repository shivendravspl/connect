{{-- resources/views/dashboard/partials/approval-section.blade.php --}}
<!-- Pending Approvals -->
<h5 class="mb-3">Applications Pending Your Approval</h5>
@if($pendingApplications->isEmpty())
    <div class="alert alert-info">No applications pending your approval.</div>
@else
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th>Application ID</th>
                    <th>Territory</th>
                    <th>Region</th>
                    <th>Zone</th>
                    <th>Business Unit</th>
                    <th>Submitted On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingApplications as $application)
                <tr>
                    <td>{{ $application->application_code }}</td>
                    <td>{{ $application->territoryDetail->territory_name ?? 'N/A' }}</td>
                    <td>{{ $application->regionDetail->region_name ?? 'N/A' }}</td>
                    <td>{{ $application->zoneDetail->zone_name ?? 'N/A' }}</td>
                    <td>{{ $application->businessUnit->business_unit_name ?? 'N/A' }}</td>
                    <td>{{ $application->created_at->format('d-M-Y H:i') }}</td>
                    <td>
                        <a href="{{ route('approvals.show', $application) }}" class="btn btn-sm btn-primary">Review</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<!-- My Applications -->
<h5 class="mb-3 mt-5">My Applications</h5>
@if($myApplications->isEmpty())
    <div class="alert alert-info">You haven't submitted any applications yet.</div>
@else
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th>Application ID</th>
                    <th>Territory</th>
                    <th>Status</th>
                    <th>Current Approver</th>
                    <th>Submitted On</th>
                    <th>Last Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($myApplications as $application)
                <tr>
                    <td>{{ $application->application_code }}</td>
                    <td>{{ $application->territory->territory_name ?? 'N/A' }}</td>
                    <td>
                        <span class="badge bg-{{ $application->status_badge }}">
                            {{ ucfirst($application->status) }}
                        </span>
                    </td>
                    <td>
                        @if($application->current_approver)
                            {{ $application->current_approver->name }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $application->created_at->format('d-M-Y H:i') }}</td>
                    <td>{{ $application->updated_at->format('d-M-Y H:i') }}</td>
                    <td>
                        <a href="{{ route('approvals.show', $application) }}" class="btn btn-sm btn-primary">View</a>
                        @if($application->status === 'reverted')
                            <a href="{{ route('applications.edit', $application) }}" class="btn btn-sm btn-warning">Edit</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif