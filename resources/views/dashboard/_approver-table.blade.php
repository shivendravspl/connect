<!-- resources/views/dashboard/_approver-table.blade.php -->
@if($pendingApplications->isEmpty())
<div class="alert alert-info no-data-message">No applications pending your approval.</div>
@else
<div class="table-responsive">
    <table class="table table-hover compact-table">
        <thead class="table-light">
            <tr>
                <th>Sr. No</th>
                <th>Date Submitted</th>
                <th>Distributor Name</th>
                <th>Initiated By</th>
                <th>Days Pending</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pendingApplications as $index => $application)
            <tr class="status-card {{ $application->status_badge }}">
                <td>{{ $pendingApplications->firstItem() + $index }}</td>
                <td>{{ $application->updated_at->format('d M Y') }}</td>
                <td>{{ $application->entityDetails->establishment_name ?? 'N/A' }}</td>
                <td>{{ $application->createdBy->emp_name ?? 'N/A' }} ({{ $application->createdBy->emp_designation ?? 'N/A' }})</td>
                <td>{{ $application->updated_at->diffInDays(now()) }} Days</td>
                <td>
                    @php
                    $isApprover = $application->current_approver_id === Auth::user()->emp_id;
                    @endphp
                    @if($isApprover)
                        <div class="btn-group" role="group">
                            <a href="{{ route('approvals.show', $application->id) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="View"><i class="ri-eye-line">View</i></a>
                            <button type="button" class="btn btn-sm btn-success action-modal" data-bs-toggle="modal" data-bs-target="#approveModal" data-application-id="{{ $application->id }}" data-bs-toggle="tooltip" title="Approve"><i class="ri-checkbox-fill">Approve</i></button>
                            <button type="button" class="btn btn-sm btn-warning action-modal" data-bs-toggle="modal" data-bs-target="#revertModal" data-application-id="{{ $application->id }}" data-bs-toggle="tooltip" title="Revert"><i class="ri-checkbox-fill">Revert</i></button>
                            <button type="button" class="btn btn-sm btn-secondary action-modal" data-bs-toggle="modal" data-bs-target="#holdModal" data-application-id="{{ $application->id }}" data-bs-toggle="tooltip" title="Hold"><i class="ri-pause-circle-line">Hold</i></button>
                            <button type="button" class="btn btn-sm btn-danger action-modal" data-bs-toggle="modal" data-bs-target="#rejectModal" data-application-id="{{ $application->id }}" data-bs-toggle="tooltip" title="Reject"><i class="ri-delete-bin-line">Reject</i></button>
                        </div>
                    @else
                        <span class="badge bg-secondary">Awaiting {{ $application->currentApprover->emp_name ?? 'Approval' }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $pendingApplications->links() }}
</div>
@include('dashboard.partials.approval_modals')
@endif