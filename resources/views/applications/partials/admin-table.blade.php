@php
use App\Models\Status;
@endphp
<div class="table-responsive">
    <table class="table table-sm table-hover table-bordered" id="adminApplicationsTable">
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
            @forelse ($applications as $application)
            <tr class="main-row" data-application-id="{{ $application->id }}">
                <td>
                    <div class="app-id-with-toggle">
                        <button class="toggle-timeline" 
                                data-application-id="{{ $application->id }}"
                                title="Show Approval Timeline">
                            <i class="ri-add-circle-line"></i>
                        </button>
                        <span>{{ $application->id }}</span>
                    </div>
                </td>
                <td>{{ $application->entityDetails->establishment_name ?? 'N/A' }}</td>
                <td>{{ $application->territoryDetail->territory_name ?? 'N/A' }}</td>
                <td>{{ $application->regionDetail->region_name ?? 'N/A' }}</td>
                <td>{{ $application->createdBy->emp_name ?? 'Unknown' }} ({{ $application->createdBy->emp_designation ?? 'N/A' }})</td>
                <td>{{ ucfirst($application->approval_level ?? 'N/A') }}</td>
                <td>
                    @php
                    $badgeColor = Status::getBadgeColorForStatus($application->status);
                    $displayStatus = Status::getDisplayName($application->status);
                    @endphp
                    <span class="badge bg-{{ $badgeColor }}">
                        {{ $displayStatus }}
                    </span>
                </td>
                <td>{{ $application->created_at->format('d M Y') }}</td>
                <td>
                    @php
                        $approvalLog = $application->approvalLogs->where('action', 'approved')->sortByDesc('created_at')->first();
                    @endphp
                    {{ $approvalLog ? $approvalLog->created_at->format('d M Y') : 'N/A' }}
                </td>
                <td>
                    {{ in_array($application->status, ['distributorship_created']) ? $application->updated_at->format('d M Y') : 'N/A' }}
                </td>
                <td>
                    {{ in_array($application->status, ['distributorship_created']) ? $application->created_at->diffInDays($application->updated_at) : 'N/A' }}
                </td>
                <td>
                    <a href="{{ route('approvals.show', $application->id) }}" class="btn btn-sm btn-primary" title="View">
                        <i class="ri-eye-line"></i>
                    </a>
                    {{-- Take Action Button --}}
                    @php $isApprover = $application->current_approver_id === Auth::user()->emp_id; @endphp
                    @if($isApprover)
                        <button type="button" class="btn btn-sm btn-secondary take-action-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#actionModal"
                                data-application-id="{{ $application->id }}"
                                data-distributor-name="{{ $application->entityDetails->establishment_name ?? 'N/A' }}"
                                data-submission-date="{{ $application->created_at->format('d M Y') }}"
                                data-initiator="{{ $application->createdBy->emp_name ?? 'N/A' }} ({{ $application->createdBy->emp_designation ?? 'N/A' }})"
                                data-status="{{ $application->status }}"
                                title="Take Action">
                            <i class="ri-edit-box-line"></i>
                        </button>
                    @else
                        <span class="badge bg-secondary">
                            Awaiting {{ Str::limit($application->currentApprover->emp_name ?? 'Approval', 15) }}
                        </span>
                    @endif
                </td>
            </tr>
            <tr class="timeline-row" id="timeline-{{ $application->id }}" style="display: none;">
                <td colspan="12" class="p-3">
					 @include('applications.partials.timeline', ['application' => $application])
				</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center no-data-message">No applications found based on current filters.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center mt-2">
    {{ $applications->appends(request()->query())->links('pagination::simple-bootstrap-5') }}
</div>