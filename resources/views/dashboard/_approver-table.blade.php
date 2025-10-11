<style>
    /* Compact table styles */
    .compact-table th, .compact-table td {
        padding: 0.4rem 0.6rem;
        font-size: 0.75rem;
        vertical-align: middle;
    }

    .compact-table .btn-sm {
        padding: 0.2rem 0.4rem;
        font-size: 0.7rem;
        line-height: 1.2;
    }

    .compact-table .badge {
        font-size: 0.65rem;
        padding: 0.25rem 0.5rem;
    }

    /* Modal styles */
    .modal-content {
        border-radius: 0.5rem;
        box-shadow: var(--card-shadow);
    }

    .modal-body .card {
        background-color: var(--bg-light);
        border: none;
    }

    .modal-body .card-header {
        padding: 0.5rem;
        font-size: 0.85rem;
    }

    .modal-body .card-body {
        padding: 0.75rem;
        font-size: 0.8rem;
    }

    .form-label {
        font-size: 0.75rem;
        font-weight: 500;
    }

    .form-control, .form-select {
        font-size: 0.8rem;
        padding: 0.3rem 0.5rem;
    }

    .modal-footer .btn {
        font-size: 0.8rem;
        padding: 0.3rem 0.75rem;
    }

    /* Responsive adjustments */
    @media (max-width: 576px) {
        .compact-table th, .compact-table td {
            padding: 0.3rem 0.4rem;
            font-size: 0.65rem;
        }

        .compact-table .btn-sm {
            padding: 0.15rem 0.3rem;
            font-size: 0.65rem;
        }

        .modal-body .card-body {
            padding: 0.5rem;
        }

        .modal-body .card-header {
            font-size: 0.75rem;
        }
    }
</style>

@if($pendingApplications->isEmpty())
<div class="alert alert-info no-data-message">No applications pending your approval.</div>
@else
<div class="table-responsive">
    <table class="table table-hover compact-table">
        <thead class="table-light">
            <tr>
                <th>Sr. No</th>
                <th>Date</th>
                <th>Distributor</th>
                <th>Initiator</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pendingApplications as $index => $application)
            <tr class="status-card {{ $application->status_badge }}" data-application-id="{{ $application->id }}">
                <td>{{ $pendingApplications->firstItem() + $index }}</td>
                <td>{{ $application->created_at->format('d M y') }}</td>
                <td>{{ Str::limit($application->entityDetails->establishment_name ?? 'N/A', 20) }}</td>
                <td>{{ Str::limit($application->createdBy->emp_name ?? 'N/A', 15) }}<br><small>{{ $application->createdBy->emp_designation ?? 'N/A' }}</small></td>
                <td>
                    <div class="d-flex align-items-center flex-wrap gap-1">
                        {{-- View Button --}}
                        <a href="{{ route('approvals.show', $application->id) }}"
                           class="btn btn-sm btn-primary"
                           data-bs-toggle="tooltip"
                           title="View">
                            <i class="ri-eye-line"></i>
                        </a>

                        {{-- Edit Button --}}
                        @if($application->status === 'reverted' && $application->created_by === Auth::user()->emp_id)
                            <a href="{{ route('applications.edit', $application->id) }}"
                               class="btn btn-sm btn-warning"
                               data-bs-toggle="tooltip"
                               title="Edit">
                                <i class="ri-edit-line"></i>
                            </a>
                        @endif

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

                        {{-- Finalized Badge --}}
                        @if($application->status === 'distributorship_created')
                            <span class="badge bg-success d-flex align-items-center gap-1">
                                <i class="ri-checkbox-circle-fill"></i> Finalized
                            </span>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $pendingApplications->links() }}
</div>

    <!-- Common Action Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="action-form" method="POST" action="">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="actionModalLabel">Take Action on Application</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Basic Details -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Application Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Distributor:</strong> <span id="modal-distributor-name">N/A</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Submitted:</strong> <span id="modal-submission-date">N/A</span><br>
                                        <strong>Initiator:</strong> <span id="modal-initiator">N/A</span><br>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Selection -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="actionType" class="form-label">Action <span class="text-danger">*</span></label>
                                <select id="actionType" name="action" class="form-select" required>
                                    <option value="" disabled selected>Choose action...</option>
                                    <option value="approve">Approve</option>
                                    <option value="revert">Revert</option>
                                    <option value="hold">Hold</option>
                                    <option value="reject">Reject</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="modal-action-date" class="form-label">Action Date</label>
                                <input type="date" id="modal-action-date" name="action_date" class="form-control" readonly value="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks <span class="text-danger">*</span></label>
                            <textarea id="remarks" name="remarks" class="form-control" rows="3" required placeholder="Enter your remarks/comments..."></textarea>
                        </div>

                        <!-- Follow-up Date (for Hold) -->
                        <div class="mb-3 d-none" id="followUpSection">
                            <label for="follow_up_date" class="form-label">Follow-up Date <span class="text-danger">*</span></label>
                            <input type="date" id="follow_up_date" name="follow_up_date" class="form-control" min="{{ now()->addDay()->format('Y-m-d') }}">
                            <div class="form-text">Application will be followed up on this date</div>
                        </div>

                        <input type="hidden" id="application_id" name="application_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="action-submit-btn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span class="submit-text">Submit</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif