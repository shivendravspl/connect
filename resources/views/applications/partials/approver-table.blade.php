@if($applications->isEmpty())
    <div class="no-data-message">No pending forms found based on current filters.</div>
@else
    <div class="table-responsive">
        <table class="table table-sm compact-table table-hover">
            <thead>
                <tr>
                    <th>Sr. No</th>
                    <th>Date Submitted</th>
                    <th>Distributor Name</th>
                    <th>Initiated By</th>
                    <th>Status</th>
                    <th>Days Pending</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($applications as $index => $application)
                    @php
                        $daysPending = floor($application->created_at->diffInDays(\Carbon\Carbon::now()));
                        $initiatorRole = $application->createdBy->emp_designation ?? 'N/A';
                        $statusBadge = $application->status_badge ?? 'secondary';
                        $statusLabel = ucwords(str_replace('_', ' ', $application->status ?? 'N/A'));
                    @endphp
                    <tr>
                        <td>{{ $applications->firstItem() + $index }}</td>
                        <td>{{ $application->created_at->format('d-M-Y') }}</td>
                        <td>{{ $application->entityDetails->establishment_name ?? 'N/A' }}</td>
                        <td>{{ $application->createdBy->emp_name ?? 'Unknown' }} ({{ $initiatorRole }})</td>
                        <td>
                            <span class="badge bg-{{ $statusBadge }}">{{ $statusLabel }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $daysPending > 3 ? 'warning' : 'info' }}">{{ $daysPending }} Days</span>
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
                @endforeach
            </tbody>
        </table>
        {{ $applications->appends(request()->query())->links('pagination::simple-bootstrap-5') }}
    </div>
@endif