<div class="table-responsive">
    <table class="table table-sm compact-table table-hover">
        <thead>
            <tr>
                <th>Application ID</th>
                <th>Distributor Name</th>
                <th>Territory</th>
                <th>Region</th>
                <th>Initiator</th>
                <th>Stage</th>
                <th>Status</th>
                <th>Submission Date</th>
                <th>Approval Date</th>
                <th>Final Appointment Date</th>
                <th>TAT (Days)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($masterReportApplications as $application)
            <tr>
                <td>{{ $application->id }}</td>
                <td>{{ $application->entityDetails->legal_name ?? 'N/A' }}</td>
                <td>{{ $application->territoryDetail->territory_name ?? 'N/A' }}</td>
                <td>{{ $application->regionDetail->region_name ?? 'N/A' }}</td>
                <td>{{ $application->createdBy->emp_name ?? 'Unknown' }} ({{ $application->createdBy->emp_designation ?? 'N/A' }})</td>
                <td>{{ ucfirst($application->approval_level ?? 'N/A') }}</td>
                <td>
                    <span class="badge bg-{{ $application->status_badge ?? 'secondary' }}">
                        {{ ucwords(str_replace('_', ' ', $application->status ?? 'N/A')) }}
                    </span>
                </td>
                <td>{{ $application->created_at->format('d M Y') }}</td>
                <td>
                    @php
                        // Find the last 'approved' log for any stage
                        $approvalLog = $application->approvalLogs->where('action', 'approved')->sortByDesc('created_at')->first();
                    @endphp
                    {{ $approvalLog ? $approvalLog->created_at->format('d M Y') : 'N/A' }}
                </td>
                <td>
                    {{ $application->status == 'distributorship_created' ? $application->updated_at->format('d M Y') : 'N/A' }}
                </td>
                <td>
                    {{ $application->status == 'distributorship_created' ? $application->created_at->diffInDays($application->updated_at) : 'N/A' }}
                </td>
                <td>
                    <a href="{{ route('approvals.show', $application->id) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="View"><i class="ri-eye-line"></i></a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center no-data-message">No applications found based on current filters.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    {{ $masterReportApplications->links() }}
</div>