{{-- resources/views/dashboard/_approver-pending-table.blade.php --}}
@if($approverPendingApplications->isEmpty())
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
                @foreach($approverPendingApplications as $index => $application)
                    @php
                        $daysPending = $application->created_at->diffInDays(\Carbon\Carbon::now());
                        $initiatorRole = $application->createdBy->emp_designation ?? 'N/A';
                        $statusBadge = $application->status_badge ?? 'secondary';
                        $statusLabel = ucwords(str_replace('_', ' ', $application->status ?? 'N/A'));
                    @endphp
                    <tr>
                        <td>{{ $approverPendingApplications->firstItem() + $index }}</td>
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
                            <div class="table-dropdown-container">
                                <button class="btn btn-soft-secondary btn-sm table-dropdown-btn" type="button" aria-expanded="false">
                                    <i class="ri-more-fill"></i>
                                </button>
                                <ul class="table-dropdown-menu table-dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('applications.show', $application->id) }}">
                                            <i class="ri-eye-fill align-bottom me-2 text-muted"></i> View
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item take-action-btn" href="#" 
                                           data-application-id="{{ $application->id }}" 
                                           data-distributor-name="{{ $application->entityDetails->establishment_name ?? 'N/A' }}" 
                                           data-submission-date="{{ $application->created_at->format('d-M-Y') }}" 
                                           data-initiator="{{ $application->createdBy->emp_name ?? 'Unknown' }}" 
                                           data-status="{{ $application->status }}"
                                           data-action="approve">
                                            <i class="ri-check-line align-bottom me-2 text-success"></i> Approve
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item take-action-btn" href="#" 
                                           data-application-id="{{ $application->id }}" 
                                           data-distributor-name="{{ $application->entityDetails->establishment_name ?? 'N/A' }}" 
                                           data-submission-date="{{ $application->created_at->format('d-M-Y') }}" 
                                           data-initiator="{{ $application->createdBy->emp_name ?? 'Unknown' }}" 
                                           data-status="{{ $application->status }}"
                                           data-action="reject">
                                            <i class="ri-close-line align-bottom me-2 text-danger"></i> Reject
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item take-action-btn" href="#" 
                                           data-application-id="{{ $application->id }}" 
                                           data-distributor-name="{{ $application->entityDetails->establishment_name ?? 'N/A' }}" 
                                           data-submission-date="{{ $application->created_at->format('d-M-Y') }}" 
                                           data-initiator="{{ $application->createdBy->emp_name ?? 'Unknown' }}" 
                                           data-status="{{ $application->status }}"
                                           data-action="revert">
                                            <i class="ri-arrow-go-back-line align-bottom me-2 text-warning"></i> Revert
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item take-action-btn" href="#" 
                                           data-application-id="{{ $application->id }}" 
                                           data-distributor-name="{{ $application->entityDetails->establishment_name ?? 'N/A' }}" 
                                           data-submission-date="{{ $application->created_at->format('d-M-Y') }}" 
                                           data-initiator="{{ $application->createdBy->emp_name ?? 'Unknown' }}" 
                                           data-status="{{ $application->status }}"
                                           data-action="hold">
                                            <i class="ri-time-line align-bottom me-2 text-info"></i> Hold
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $approverPendingApplications->links() }}
    </div>
@endif

{{-- Common Action Modal (shared across dashboards) --}}
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
                    {{-- Basic Details --}}
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
                            <input type="hidden" id="application_id" name="application_id">
                        </div>
                    </div>

                    {{-- Action Selection --}}
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

                    {{-- Remarks --}}
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks <span class="text-danger">*</span></label>
                        <textarea id="remarks" name="remarks" class="form-control" rows="3" required placeholder="Enter your remarks/comments..."></textarea>
                    </div>

                    {{-- Follow-up Date (for Hold) --}}
                    <div class="mb-3 d-none" id="followUpSection">
                        <div class="col-12">
                            <label for="follow_up_date" class="form-label">Follow-up Date <span class="text-danger">*</span></label>
                            <input type="date" id="follow_up_date" name="follow_up_date" class="form-control" min="{{ now()->addDay()->format('Y-m-d') }}">
                            <div class="form-text">Application will be followed up on this date</div>
                        </div>
                    </div>
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

<style>
.table-dropdown-container {
    position: static !important;
}

.table-dropdown-menu {
    list-style: none;
    padding-left: 0;
    margin: 0;     
    min-width: 200px;
    z-index: 10000 !important;
    max-height: 300px;
    overflow-y: auto;
    position: fixed !important;
    display: none;
    background-color: #fff;
    border: 1px solid rgba(0, 0, 0, 0.15);
    border-radius: 0.25rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transition: opacity 0.15s ease-in-out, transform 0.15s ease-in-out;
}

.table-dropdown-menu.show {
    display: block;
    opacity: 1;
    transform: translateY(0);
}

.dropdown-item {
    font-size: 0.85rem;
    padding: 0.25rem 1rem;
    color: #212529;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

.dropdown-item i {
    width: 20px;
    text-align: center;
}

.btn-soft-secondary {
    background-color: #f3f3f9;
    border-color: #f3f3f9;
    color: #6c757d;
}

.btn-soft-secondary:hover {
    background-color: #e0e0e0;
    border-color: #e0e0e0;
    color: #495057;
}

.table-dropdown-menu-end {
    right: 0;
    left: auto;
}
</style>