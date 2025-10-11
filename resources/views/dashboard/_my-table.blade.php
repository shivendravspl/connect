<div class="table-responsive">
    <table class="table table-sm compact-table table-hover">
        <thead>
            <tr>
                <th>Application ID</th>
                <th>Distributor Name</th>
                <th>Territory</th>
                <th>Region</th>
                <th>Status</th>
                <th>Submitted Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($myApplications as $application)
            <tr>
                <td>{{ $application->id }}</td>
                <td>{{ $application->entityDetails->establishment_name ?? 'N/A' }}</td>
                <td>{{ $application->territoryDetail->territory_name ?? 'N/A' }}</td>
                <td>{{ $application->regionDetail->region_name ?? 'N/A' }}</td>
                <td>
                    <span class="badge bg-{{ $application->status_badge ?? 'secondary' }}">
                        {{ ucwords(str_replace('_', ' ', $application->status ?? 'N/A')) }}
                    </span>
                </td>
                <td>{{ $application->created_at->format('d M Y') }}</td>
                <td>
                    <a href="{{ route('approvals.show', $application->id) }}" class="btn btn-sm btn-primary" title="View">
                        <i class="ri-eye-line"></i>
                    </a>
                    @if($application->status == 'draft')
                        <a href="{{ route('applications.edit', $application->id) }}" class="btn btn-sm btn-outline-primary ms-1" title="Edit">
                            <i class="ri-edit-line"></i>
                        </a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center no-data-message">No applications found based on current filters.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    {{ $myApplications->links() }}
</div>