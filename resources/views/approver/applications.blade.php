@extends('layouts.app')

@push('styles')
<style>
    .container-fluid {
        padding: 0.5rem;
    }

    .card {
        margin-bottom: 0.5rem;
        border-radius: 0.2rem;
    }

    .card-body {
        padding: 0.5rem;
    }

    .form-label {
        font-size: 0.65rem;
        margin-bottom: 0.1rem;
        font-weight: 500;
    }

    .form-select-sm,
    .form-control-sm,
    .btn-sm {
        font-size: 0.65rem;
        padding: 0.15rem 0.3rem;
        height: 1.6rem;
    }

    .compact-table th,
    .compact-table td {
        font-size: 0.6rem;
        padding: 0.3rem;
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

    .no-data-message {
        font-size: 0.7rem;
        color: #6c757d;
        text-align: center;
        padding: 0.5rem;
    }

    .toggle-timeline {
        cursor: pointer;
        background: none;
        border: none;
        color: #6c757d;
        font-size: 0.9rem;
        padding: 2px;
        transition: all 0.2s ease;
    }

    .toggle-timeline:hover {
        color: #007bff;
        transform: scale(1.2);
    }

    .toggle-timeline.active {
        color: #007bff;
    }

    .timeline-row {
        display: none;
        background-color: #f8f9fa;
        border-top: none;
    }

    .app-id-with-toggle {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .timeline-container {
        background-color: #f8f9fa;
        border-radius: 5px;
        padding: 0.5rem;
        overflow-x: auto;
    }

    .timeline-item {
        text-align: center;
        min-width: 110px;
    }

    .header-title {
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 0;
    }

    .filter-col {
        flex: 0 0 auto;
    }

    /* Modal styles */
    .modal-content {
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .modal-body .card {
        background-color: #f8f9fa;
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

    .modal-footer .btn {
        font-size: 0.8rem;
        padding: 0.3rem 0.75rem;
    }

    @media (max-width: 576px) {
        .container-fluid {
            padding-left: 0.2rem;
            padding-right: 0.2rem;
        }

        .compact-table th,
        .compact-table td {
            font-size: 0.55rem;
            padding: 0.15rem;
        }

        .compact-table .btn-sm {
            padding: 0.15rem 0.3rem;
            font-size: 0.65rem;
        }

        .form-select-sm,
        .form-control-sm,
        .btn-sm {
            font-size: 0.55rem;
            padding: 0.1rem 0.2rem;
            height: 1.4rem;
        }

        .timeline-item {
            min-width: 90px;
        }

        .modal-body .card-body {
            padding: 0.5rem;
        }

        .modal-body .card-header {
            font-size: 0.75rem;
        }
    }

    @media (min-width: 577px) and (max-width: 768px) {
        .filter-col {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    @media (min-width: 769px) {
        .filter-col {
            flex: 0 0 25%;
            max-width: 25%;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Compact Status Filter --}}
    <div class="row mb-1">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-1 px-2">
                    <h6 class="header-title mb-0">
                        Applications Management
                        @if($filters['kpi_filter'])
                            <span class="text-muted">- {{ ucfirst(str_replace('_', ' ', $filters['kpi_filter'])) }}</span>
                        @endif
                    </h6>
                </div>
                <div class="card-body p-1">
                    <form id="approver-filter-form" method="GET" class="row g-1 align-items-end">
                        {{-- Hidden fields to preserve KPI filter data --}}
                        <input type="hidden" name="kpi_filter" value="{{ $filters['kpi_filter'] }}">
                        
                        {{-- Status Filter (Dynamic from Status Model) --}}
                        <div class="col-6 col-md-3 col-lg-2 filter-col">
                            <label for="approver_status_filter" class="form-label small mb-0">Status</label>
                            <select name="status" id="approver_status_filter" class="form-select form-select-sm">
                                <option value="">All</option>
                                @foreach($statusGroups as $groupKey => $group)
                                    <option value="{{ $group['slugs'] }}" {{ $filters['status'] == $group['slugs'] ? 'selected' : '' }}>
                                        {{ $group['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        

							<div class="col-auto d-flex gap-1">
    <button type="submit" class="btn btn-primary btn-sm py-1">Apply</button>
    <a href="{{ route('approver.applications') }}" class="btn btn-outline-secondary btn-sm py-1">Reset</a>
</div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Applications Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-1 px-2">
                    <h6 class="header-title mb-0">
                        @php
                            $currentGroup = collect($statusGroups)->firstWhere('slugs', $filters['status'] ?? '');
                            $title = $currentGroup ? $currentGroup['label'] : 'Applications List';
                        @endphp
                        {{ $title }}
                        <span class="badge bg-primary ms-1">{{ $applications->total() }}</span>
                    </h6>
                </div>
                <div class="card-body p-1">
                    @if($applications->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm compact-table table-hover mb-1">
                            <thead class="table-light">
                                <tr>
                                    @php
                                        $isPendingView = ($filters['status'] ?? '') === ($statusGroups['pending']['slugs'] ?? 'pending');
                                    @endphp
                                    @if($isPendingView)
                                        {{-- Pending View --}}
                                        <th>Sr. No</th>
                                        <th>Date</th>
                                        <th>Distributor</th>
                                        <th>Initiator</th>
                                        <th>Action</th>
                                    @else
                                        {{-- Regular View --}}
                                        <th>App ID</th>
                                        <th>Distributor</th>
                                        <th>Territory</th>
                                        <th>Region</th>
                                        <th>Initiator</th>
                                        <th>Stage</th>
                                        <th>Status</th>
                                        <th>Submit Date</th>
                                        <th>Approve Date</th>
                                        <th>Final Date</th>
                                        <th>TAT Days</th>
                                        <th>Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($applications as $index => $application)
                                <tr>
                                    @if($isPendingView)
                                        {{-- Pending View --}}
                                        <td>{{ $applications->firstItem() + $index }}</td>
                                        <td class="small">{{ $application->created_at->format('d M y') }}</td>
                                        <td class="small">{{ Str::limit($application->entityDetails->establishment_name ?? 'N/A', 20) }}</td>
                                        <td class="small">
                                            {{ Str::limit($application->createdBy->emp_name ?? 'N/A', 15) }}<br>
                                            <small class="text-muted">{{ $application->createdBy->emp_designation ?? 'N/A' }}</small>
                                        </td>
                                        <td>
    <div class="d-flex align-items-center flex-wrap gap-1">
        {{-- View Button --}}
        <a href="{{ route('approvals.show', $application->id) }}"
           class="btn btn-sm btn-primary"
           data-bs-toggle="tooltip"
           title="View">
            <i class="ri-eye-line"></i>
        </a>

        {{-- Take Action Button --}}
       @php 
$currentUserId = Auth::user()->emp_id;
$isApprover = $application->current_approver_id === $currentUserId;
$hasCurrentApprover = !empty($application->current_approver_id);
$isCreator = $application->created_by === $currentUserId;

// Check approval logs for this user and application
$userApprovalLogs = $application->approvalLogs->where('user_id', $currentUserId);
$userAlreadyApproved = $userApprovalLogs->where('action', 'approved')->isNotEmpty();
$userAlreadyRejected = $userApprovalLogs->where('action', 'rejected')->isNotEmpty();
$userAlreadyActed = $userAlreadyApproved || $userAlreadyRejected;

// Define actionable statuses based on business logic
$actionableStatusesArray = ['under_level1_review', 'under_level2_review', 'under_level3_review', 'reverted', 'on_hold'];
$revertedStatusesArray = ['reverted'];
$completionStatusesArray = ['distributorship_created'];
@endphp
       @if($hasCurrentApprover && $isApprover && !$userAlreadyActed && in_array($application->status, $actionableStatusesArray))
    {{-- Current user is the assigned approver, hasn't acted yet, and application is actionable --}}
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
@elseif($hasCurrentApprover && !$isApprover)
    {{-- There is an approver but it's not the current user --}}
    <span class="badge bg-secondary">
        Awaiting {{ Str::limit($application->currentApprover->emp_name ?? 'Approval', 15) }}
    </span>
@elseif($userAlreadyApproved)
    {{-- User has already approved this application --}}
    <span class="badge bg-info">
        <i class="ri-check-line"></i> Approved by You
    </span>
@elseif($userAlreadyRejected)
    {{-- User has already rejected this application --}}
    <span class="badge bg-danger">
        <i class="ri-close-line"></i> Rejected by You
    </span>
@elseif(in_array($application->status, $revertedStatusesArray) && $isCreator)
    {{-- Application is reverted and current user is the creator --}}
    <a href="{{ route('applications.edit', $application->id) }}"
       class="btn btn-sm btn-warning"
       data-bs-toggle="tooltip"
       title="Edit Reverted Application">
        <i class="ri-edit-line"></i>
    </a>
@elseif(!$hasCurrentApprover && in_array($application->status, $actionableStatusesArray))
    {{-- No approver assigned but application is actionable --}}
    <span class="badge bg-warning">
        Awaiting Assignee
    </span>
@elseif(in_array($application->status, $completionStatusesArray))
    {{-- Application is completed --}}
    <span class="badge bg-success">
        <i class="ri-checkbox-circle-line"></i> Completed
    </span>
@else
    {{-- Default case --}}
    <span class="badge bg-light text-dark">
        In Process
    </span>
@endif

        {{-- Finalized Badge --}}
        @php
        $completionStatusesArray = explode(',', $statusGroups['completed']['slugs'] ?? '');
        @endphp
        @if(in_array($application->status, $completionStatusesArray))
            <span class="badge bg-success d-flex align-items-center gap-1">
                <i class="ri-checkbox-circle-fill"></i> Finalized
            </span>
        @endif
    </div>
</td>
                                    @else
                                        {{-- Regular View --}}
                                        <td>
                                            <div class="app-id-with-toggle">
                                                <button class="toggle-timeline" 
                                                        data-application-id="{{ $application->id }}"
                                                        title="Show Approval Timeline">
                                                    <i class="ri-add-circle-line"></i>
                                                </button>
                                                <span class="small">{{ $application->id }}</span>
                                            </div>
                                        </td>
                                        <td class="small">{{ Str::limit($application->entityDetails->establishment_name ?? 'N/A', 15) }}</td>
                                        <td class="small">{{ Str::limit($application->territoryDetail->territory_name ?? 'N/A', 12) }}</td>
                                        <td class="small">{{ Str::limit($application->regionDetail->region_name ?? 'N/A', 12) }}</td>
                                        <td class="small">{{ Str::limit($application->createdBy->emp_name ?? 'Unknown', 12) }}</td>
                                        <td class="small">{{ ucfirst($application->approval_level ?? 'N/A') }}</td>
                                        <td>
                                            @php
                                            $badgeColor = \App\Models\Status::getBadgeColorForStatus($application->status);
                                            $displayStatus = \App\Models\Status::getDisplayName($application->status);
                                            @endphp
                                            <span class="badge bg-{{ $badgeColor }}">
                                                {{ Str::limit($displayStatus, 12) }}
                                            </span>
                                        </td>
                                        <td class="small">{{ $application->created_at->format('d M Y') }}</td>
                                        <td class="small">
                                            @php
                                                $approvalLog = $application->approvalLogs->where('action', 'approved')->sortByDesc('created_at')->first();
                                            @endphp
                                            {{ $approvalLog ? $approvalLog->created_at->format('d M Y') : 'N/A' }}
                                        </td>
                                        <td class="small">
                                            @php
                                            $completionStatuses = explode(',', $statusGroups['completed']['slugs'] ?? '');
                                            @endphp
                                            {{ in_array($application->status, $completionStatuses) ? $application->updated_at->format('d M Y') : 'N/A' }}
                                        </td>
                                        <td class="small">
                                            @php
                                            $completionStatuses = explode(',', $statusGroups['completed']['slugs'] ?? '');
                                            @endphp
                                            {{ in_array($application->status, $completionStatuses) ? $application->created_at->diffInDays($application->updated_at) : 'N/A' }}
                                        </td>
                                        <td>
                                            <a href="{{ route('approvals.show', $application->id) }}" class="btn btn-sm btn-primary p-1" title="View Details">
                                                <i class="ri-eye-line" style="font-size: 0.7rem;"></i>
                                            </a>
                                        </td>
                                    @endif
                                </tr>
                                @if(!$isPendingView)
                                <tr class="timeline-row" id="timeline-{{ $application->id }}">
                                    <td colspan="12" class="p-2">
                                        {{-- Timeline content --}}
                                        @include('partials.approval_timeline', ['application' => $application])
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Pagination --}}
                    <div class="d-flex justify-content-center mt-2">
                        {{ $applications->links('pagination::bootstrap-4') }}
                    </div>
                    @else
                    <div class="no-data-message py-2">
                        <i class="ri-inbox-line" style="font-size: 1.5rem;"></i>
                        <p class="mt-1 mb-0 small">
                            @php
                                $currentGroup = collect($statusGroups)->firstWhere('slugs', $filters['status'] ?? '');
                                $title = $currentGroup ? $currentGroup['label'] : 'Applications List';
                            @endphp
                            No {{ strtolower($title) }} found based on current filters.
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Modal -->
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
                                @foreach($actionableStatuses as $status)
                                    <option value="{{ $status->name }}">{{ $status->display_name }}</option>
                                @endforeach
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
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize timeline toggles
        initializeTimelineToggles();

        // Auto-submit form when status changes
        $('#approver_status_filter').change(function() {
            $('#approver-filter-form').submit();
        });

        // Initialize modal handlers
        initializeModalListeners();
    });

    function initializeTimelineToggles() {
        $('.toggle-timeline').off('click').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const applicationId = $(this).data('application-id');
            const timelineRow = $(`#timeline-${applicationId}`);
            const icon = $(this).find('i');
            
            if (timelineRow.is(':visible')) {
                timelineRow.hide();
                icon.removeClass('ri-indeterminate-circle-line').addClass('ri-add-circle-line');
                $(this).removeClass('active');
            } else {
                // Close all other timelines
                $('.timeline-row').hide();
                $('.toggle-timeline i').removeClass('ri-indeterminate-circle-line').addClass('ri-add-circle-line');
                $('.toggle-timeline').removeClass('active');
                
                // Open current timeline
                timelineRow.show();
                icon.removeClass('ri-add-circle-line').addClass('ri-indeterminate-circle-line');
                $(this).addClass('active');
            }
        });
    }

    function initializeModalListeners() {
        // Remove any existing listeners to prevent duplicates
        $(document).off('click', '.take-action-btn');
        $(document).off('change', '#actionType');
        $(document).off('submit', '#action-form');
        $(document).off('hidden.bs.modal', '#actionModal');

        // Take Action Button Click
        $(document).on('click', '.take-action-btn', function() {
            const applicationId = $(this).data('application-id');
            const distributorName = $(this).data('distributor-name') || 'N/A';
            const submissionDate = $(this).data('submission-date') || 'N/A';
            const initiator = $(this).data('initiator') || 'N/A';
            const status = $(this).data('status') || '';

            $('#modal-distributor-name').text(distributorName);
            $('#modal-submission-date').text(submissionDate);
            $('#modal-initiator').text(initiator);
            $('#application_id').val(applicationId);
            $('#action-form').attr('action', `/approvals/${applicationId}/${status === 'reverted' ? 'edit' : 'approve'}`);
            $('#actionType').val('');
            $('#remarks').val('');
            $('#modal-action-date').val(new Date().toISOString().split('T')[0]);
            $('#followUpSection').addClass('d-none');
            $('#follow_up_date').val('').prop('required', false);

            // Handle non-actionable statuses
            const nonActionableStatuses = ['distributorship_created', 'rejected', 'mis_rejected', 'agreement_created', 'documents_verified', 'documents_received', 'mis_processing'];
            const submitBtn = $('#action-submit-btn');
            
            if (nonActionableStatuses.includes(status)) {
                submitBtn.prop('disabled', true).addClass('disabled');
                $('#actionType').prop('disabled', true);
                $('#remarks').prop('disabled', true);
                $('#actionType').html('<option value="" selected>Action not allowed for this status</option>');
            } else {
                submitBtn.prop('disabled', false).removeClass('disabled');
                $('#actionType').prop('disabled', false);
                $('#remarks').prop('disabled', false);
                $('#actionType').html(`
                    <option value="" disabled selected>Choose action...</option>
                    <option value="approve">Approve</option>
                    <option value="revert">Revert</option>
                    <option value="hold">Hold</option>
                    <option value="reject">Reject</option>
                `);
            }

            $('#actionModal').modal('show');
        });

        // Action Type Change
        $(document).on('change', '#actionType', function() {
            const action = $(this).val();
            const applicationId = $('#application_id').val();
            
            if (action && applicationId) {
                const url = `/approvals/${applicationId}/${action}`;
                $('#action-form').attr('action', url);
            } else {
                $('#action-form').attr('action', '');
            }

            // Show/hide follow-up date for Hold action
            if (action === 'hold') {
                $('#followUpSection').removeClass('d-none');
                $('#follow_up_date').prop('required', true);
                const defaultFollowUp = new Date();
                defaultFollowUp.setDate(defaultFollowUp.getDate() + 7);
                $('#follow_up_date').val(defaultFollowUp.toISOString().split('T')[0]);
            } else {
                $('#followUpSection').addClass('d-none');
                $('#follow_up_date').prop('required', false).val('');
            }
        });

        // Form Submission
        $(document).on('submit', '#action-form', function(e) {
            e.preventDefault();
            const form = $(this);
            const action = $('#actionType').val();
            const remarks = $('#remarks').val().trim();
            const followUpDate = $('#follow_up_date').val();
            const submitBtn = $('#action-submit-btn');
            const spinner = submitBtn.find('.spinner-border');
            const submitText = submitBtn.find('.submit-text');

            if (!action) {
                showToast('error', 'Please select an action.', 'Validation Error');
                return;
            }
            if (!remarks) {
                showToast('error', 'Remarks are required.', 'Validation Error');
                return;
            }
            if (action === 'hold' && !followUpDate) {
                showToast('error', 'Follow-up date is required for Hold action.', 'Validation Error');
                return;
            }

            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');
            submitText.text('Processing...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('#actionModal').modal('hide');
                    const actionMessages = {
                        'approve': 'Application approved successfully!',
                        'reject': 'Application rejected successfully!',
                        'revert': 'Application reverted successfully!',
                        'hold': 'Application put on hold successfully!'
                    };
                    showToast('success', actionMessages[action] || 'Action completed successfully!', getActionTitle(action));
                    setTimeout(() => {
                        location.reload(); // Reload the page to reflect changes
                    }, 1500);
                },
                error: function(xhr) {
                    let errorMsg = 'Error performing action: ';
                    let title = 'Action Failed';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg += xhr.responseJSON.message;
                    } else if (xhr.status === 403) {
                        errorMsg = 'You are not authorized to perform this action.';
                        title = 'Unauthorized';
                    } else if (xhr.status === 422) {
                        errorMsg = 'Please correct the form errors and try again.';
                        title = 'Validation Error';
                    } else {
                        errorMsg += 'Something went wrong. Please try again.';
                    }
                    showToast('error', errorMsg, title);
                    submitBtn.prop('disabled', false);
                    spinner.addClass('d-none');
                    submitText.text('Submit');
                }
            });
        });

        // Modal cleanup
        $(document).on('hidden.bs.modal', '#actionModal', function() {
            $('#action-form').attr('action', '');
            $('#actionType').val('');
            $('#remarks').val('');
            $('#follow_up_date').val('').prop('required', false);
            $('#followUpSection').addClass('d-none');
            $('#action-submit-btn').prop('disabled', false);
            $('#action-submit-btn .spinner-border').addClass('d-none');
            $('#action-submit-btn .submit-text').text('Submit');
        });
    }

    // Helper functions for toast notifications and action titles
    function showToast(type, message, title = '') {
        // Use your existing toast implementation or create a simple one
        const toast = {
            success: () => alert('✅ ' + message),
            error: () => alert('❌ ' + message),
            warning: () => alert('⚠️ ' + message),
            info: () => alert('ℹ️ ' + message)
        };
        
        if (toast[type]) {
            toast[type]();
        } else {
            alert(message);
        }
        
        // If you have a proper toast library, use it instead:
        // Toastify({ text: message, duration: 3000, gravity: "top", position: "right" }).showToast();
    }

    function getActionTitle(action) {
        const titles = {
            'approve': 'Approval Successful',
            'reject': 'Rejection Successful', 
            'revert': 'Revert Successful',
            'hold': 'Hold Successful'
        };
        return titles[action] || 'Action Completed';
    }
</script>
@endpush