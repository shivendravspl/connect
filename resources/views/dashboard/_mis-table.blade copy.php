<div class="table-responsive">
    <table class="table table-sm compact-table table-hover table-bordered" id="misApplicationsTable">
        <thead>
            <tr>
                <th>Application ID</th>
                <th>Distributor Name</th>
                <th>Zone</th>
                <th>Submission Date</th>
                <th>Initiator</th>
                <th>Current Stage</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($misApplications as $application)
                <tr class="main-row" data-application-id="{{ $application->id }}">
                    <td>
                        <a href="javascript:void(0);" class="toggle-timeline" data-bs-toggle="tooltip" title="Toggle Approval Timeline">
                            <i class="ri-arrow-right-circle-fill me-1"></i> {{ $application->id }}
                        </a>
                    </td>
                    <td>{{ $application->entityDetails->establishment_name ?? 'N/A' }}</td>
                    <td class="align-middle small">
                        {{ $application->zoneDetail->zone_name ?? 'N/A' }}
                    </td>
                    <td>{{ $application->created_at->format('Y-m-d') }}</td>
                    <td>{{ $application->createdBy->emp_name ?? 'N/A' }}</td>
                    <td>
                        @php
                            $stage = '';
                            if ($application->status == 'distributorship_created') {
                                $stage = 'Completed';
                            } elseif (in_array($application->status, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'mis_rejected','physical_docs_verified'])) {
                                $stage = 'With MIS Team';
                            } elseif ($application->status == 'initiated') {
                                $stage = 'Initiated';
                            } elseif ($application->approval_level == 'Area Coordinator') {
                                $stage = 'ABM';
                            } elseif ($application->approval_level == 'Regional Business Manager') {
                                $stage = 'RBM';
                            } elseif ($application->approval_level == 'Zonal Business Manager') {
                                $stage = 'ZBM';
                            } elseif ($application->approval_level == 'General Manager') {
                                $stage = 'GM';
                            } elseif ($application->status == 'draft') {
                                $stage = 'Draft';
                            } else {
                                $stage = 'N/A';
                            }
                        @endphp
                        {{ $stage }}
                    </td>
                    <td>
                        <span class="badge bg-{{ $application->status == 'mis_processing' ? 'warning' : ($application->status == 'distributorship_created' ? 'success' : ($application->status == 'mis_rejected' ? 'danger' : 'info')) }}">
                            {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            <a href="{{ route('approvals.show', $application->id) }}" 
                               class="btn btn-primary btn-sm" 
                               data-bs-toggle="tooltip" 
                               title="View">
                                <i class="ri-eye-line"></i> View
                            </a>
                            @if ($application->status == 'mis_processing')
                                <a href="{{ route('approvals.verify-documents', $application->id) }}" 
                                   class="btn btn-info btn-sm mis-action-btn" 
                                   data-bs-toggle="tooltip" 
                                   title="Verify Checklist"
                                   data-application-id="{{ $application->id }}"
                                   data-distributor-name="{{ $application->entityDetails->establishment_name ?? 'N/A' }}"
                                   data-submission-date="{{ $application->created_at->format('Y-m-d') }}"
                                   data-initiator="{{ $application->createdBy->emp_name ?? 'N/A' }}"
                                   data-status="{{ $application->status }}">
                                    <i class="ri-check-line"></i> Verify
                                </a>
                            @endif
                            @if ($application->status == 'document_verified')
                                {{--<a href="{{ route('approvals.create-agreement', $application->id) }}" 
                                   class="btn btn-primary btn-sm mis-action-btn" 
                                   data-bs-toggle="tooltip" 
                                   title="Create Agreement"
                                   data-application-id="{{ $application->id }}"
                                   data-distributor-name="{{ $application->entityDetails->establishment_name ?? 'N/A' }}"
                                   data-submission-date="{{ $application->created_at->format('Y-m-d') }}"
                                   data-initiator="{{ $application->createdBy->emp_name ?? 'N/A' }}"
                                   data-status="{{ $application->status }}">
                                    <i class="ri-file-text-line"></i> Create Agreement
                                </a>--}}
                            @endif
                            @if (in_array($application->status, ['agreement_created', 'documents_received']))
                                <a href="{{ route('approvals.confirm-distributor', $application->id) }}" 
                                   class="btn btn-success btn-sm mis-action-btn" 
                                   data-bs-toggle="tooltip" 
                                   title="Confirm Distributor"
                                   data-application-id="{{ $application->id }}"
                                   data-distributor-name="{{ $application->entityDetails->establishment_name ?? 'N/A' }}"
                                   data-submission-date="{{ $application->created_at->format('Y-m-d') }}"
                                   data-initiator="{{ $application->createdBy->emp_name ?? 'N/A' }}"
                                   data-status="{{ $application->status }}">
                                    <i class="ri-user-add-line"></i> Confirm Distributor
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
                <tr class="timeline-row" id="timeline-{{ $application->id }}" style="display: none;">
                    <td colspan="7">
                        <div class="timeline-container d-flex flex-row align-items-center justify-content-start p-3 overflow-auto">
                            @php
                                $approvalLevels = [
                                    'Draft/Initiated' => null,
                                    'Area Coordinator' => null,
                                    'Regional Business Manager' => null,
                                    'Zonal Business Manager' => null,
                                    'General Manager' => null,
                                    'MIS' => null,
                                ];

                                // Map approval_logs roles to timeline stages
                                foreach ($application->approvalLogs as $log) {
                                    if ($log->role == 'Executive' && is_null($approvalLevels['Area Coordinator'])) {
                                        $approvalLevels['Area Coordinator'] = $log;
                                    } elseif ($log->role == 'Assistant Manager' && is_null($approvalLevels['Regional Business Manager'])) {
                                        $approvalLevels['Regional Business Manager'] = $log;
                                    } elseif ($log->role == 'Manager' && is_null($approvalLevels['Zonal Business Manager'])) {
                                        $approvalLevels['Zonal Business Manager'] = $log;
                                    } elseif ($log->role == 'General Manager' && is_null($approvalLevels['General Manager'])) {
                                        $approvalLevels['General Manager'] = $log;
                                    } elseif ($log->role == 'MIS' && is_null($approvalLevels['MIS'])) {
                                        $approvalLevels['MIS'] = $log;
                                    }
                                }

                                $stages = [
                                    [
                                        'label' => 'Draft/Initiated',
                                        'log' => $approvalLevels['Draft/Initiated'],
                                        'status' => in_array($application->status, ['draft', 'initiated']) ? 'pending' : (in_array($application->status, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'distributorship_created', 'mis_rejected']) ? 'approved' : 'not-started'),
                                        'date' => in_array($application->status, ['draft', 'initiated']) ? $application->created_at->format('d M Y') : (in_array($application->status, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'distributorship_created', 'mis_rejected']) ? $application->created_at->format('d M Y') : '-'),
                                        'remarks' => $approvalLevels['Draft/Initiated'] ? $approvalLevels['Draft/Initiated']->remarks : '-',
                                        'icon' => 'ri-draft-fill'
                                    ],
                                    [
                                        'label' => 'ABM',
                                        'log' => $approvalLevels['Area Coordinator'],
                                        'status' => $approvalLevels['Area Coordinator'] ? $approvalLevels['Area Coordinator']->action : (in_array($application->status, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'distributorship_created', 'mis_rejected']) || in_array($application->approval_level, ['Regional Business Manager', 'Zonal Business Manager', 'General Manager']) ? 'approved' : 'not-started'),
                                        'date' => $approvalLevels['Area Coordinator'] ? $approvalLevels['Area Coordinator']->created_at->format('d M Y') : '-',
                                        'remarks' => $approvalLevels['Area Coordinator'] ? $approvalLevels['Area Coordinator']->remarks : '-',
                                        'icon' => 'ri-user-2-fill'
                                    ],
                                    [
                                        'label' => 'RBM',
                                        'log' => $approvalLevels['Regional Business Manager'],
                                        'status' => $approvalLevels['Regional Business Manager'] ? $approvalLevels['Regional Business Manager']->action : (in_array($application->status, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'distributorship_created', 'mis_rejected']) || in_array($application->approval_level, ['Zonal Business Manager', 'General Manager']) ? 'approved' : 'not-started'),
                                        'date' => $approvalLevels['Regional Business Manager'] ? $approvalLevels['Regional Business Manager']->created_at->format('d M Y') : '-',
                                        'remarks' => $approvalLevels['Regional Business Manager'] ? $approvalLevels['Regional Business Manager']->remarks : '-',
                                        'icon' => 'ri-user-3-fill'
                                    ],
                                    [
                                        'label' => 'ZBM',
                                        'log' => $approvalLevels['Zonal Business Manager'],
                                        'status' => $approvalLevels['Zonal Business Manager'] ? $approvalLevels['Zonal Business Manager']->action : (in_array($application->status, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'distributorship_created', 'mis_rejected']) || $application->approval_level == 'General Manager' ? 'approved' : 'not-started'),
                                        'date' => $approvalLevels['Zonal Business Manager'] ? $approvalLevels['Zonal Business Manager']->created_at->format('d M Y') : '-',
                                        'remarks' => $approvalLevels['Zonal Business Manager'] ? $approvalLevels['Zonal Business Manager']->remarks : '-',
                                        'icon' => 'ri-user-4-fill'
                                    ],
                                    [
                                        'label' => 'GM',
                                        'log' => $approvalLevels['General Manager'],
                                        'status' => $approvalLevels['General Manager'] ? $approvalLevels['General Manager']->action : (in_array($application->status, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'distributorship_created', 'mis_rejected','physical_docs_verified']) ? 'approved' : 'not-started'),
                                        'date' => $approvalLevels['General Manager'] ? $approvalLevels['General Manager']->created_at->format('d M Y') : '-',
                                        'remarks' => $approvalLevels['General Manager'] ? $approvalLevels['General Manager']->remarks : '-',
                                        'icon' => 'ri-user-5-fill'
                                    ],
                                    [
                                        'label' => 'MIS',
                                        'log' => $approvalLevels['MIS'],
                                        'status' => in_array($application->status, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received','physical_docs_verified']) ? 'pending' : ($application->status == 'distributorship_created' ? 'approved' : ($application->status == 'mis_rejected' ? 'rejected' : 'not-started')),
                                        'date' => in_array($application->status, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'distributorship_created', 'mis_rejected']) ? $application->updated_at->format('d M Y') : '-',
                                        'remarks' => $approvalLevels['MIS'] ? $approvalLevels['MIS']->remarks : '-',
                                        'icon' => 'ri-file-text-fill'
                                    ],
                                    [
                                        'label' => 'Final',
                                        'log' => null,
                                        'status' => $application->status == 'distributorship_created' ? 'approved' : 'not-started',
                                        'date' => $application->status == 'distributorship_created' ? $application->updated_at->format('d M Y') : '-',
                                        'remarks' => '-',
                                        'icon' => 'ri-checkbox-circle-fill'
                                    ]
                                ];
                            @endphp
                            @foreach($stages as $index => $stage)
                                <div class="timeline-item text-center px-2">
                                    <i class="{{ $stage['icon'] }} mb-2 {{ $stage['status'] == 'approved' ? 'text-success' : ($stage['status'] == 'rejected' ? 'text-danger' : ($stage['status'] == 'pending' ? 'text-warning' : 'text-muted')) }}"
                                       data-bs-toggle="tooltip"
                                       data-bs-title="{{ $stage['label'] }} - {{ ucfirst($stage['status']) }}"
                                       style="font-size: 1.2rem;"></i>
                                    <div>
                                        <strong>{{ $stage['label'] }}</strong><br>
                                        <span class="badge bg-{{ $stage['status'] == 'approved' ? 'success' : ($stage['status'] == 'rejected' ? 'danger' : ($stage['status'] == 'pending' ? 'warning' : 'secondary')) }}">
                                            {{ ucfirst($stage['status']) }}
                                        </span><br>
                                        <small><strong>Date:</strong> {{ $stage['date'] }}</small><br>
                                        <small><strong>Remarks:</strong> {{ $stage['remarks'] }}</small>
                                    </div>
                                </div>
                                @if(!$loop->last)
                                    <div class="arrow px-2 d-flex align-items-center">
                                        <span style="font-size: 1.2rem; color: #6c757d;">→</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="no-data-message">No applications found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center mt-2">
    {{ $misApplications->appends(request()->query())->links() }}
</div>

<style>
    .timeline-container {
        background-color: #f8f9fa;
        border-radius: 5px;
        max-height: 150px; /* Limit height for compact display */
        overflow-x: auto; /* Horizontal scrollbar for wide timelines */
        white-space: nowrap; /* Prevent wrapping of items */
    }
    .timeline-item {
        display: inline-block;
        min-width: 120px; /* Ensure each stage has enough space */
        vertical-align: top;
        padding: 10px;
    }
    .arrow {
        display: inline-block;
        vertical-align: middle;
    }
    .timeline-row {
        display: none;
        background-color: #f8f9fa; /* Match timeline container */
        border-top: none; /* Seamless integration with main row */
    }
    .timeline-row td {
        padding: 0.5rem;
    }
    .toggle-timeline {
        cursor: pointer;
        text-decoration: none;
        color: #007bff;
    }
    .toggle-timeline:hover {
        text-decoration: underline;
    }
    .table-sm th, .table-sm td {
        vertical-align: middle;
    }
</style>

<script>
    $(document).ready(function() {
        // Toggle timeline row on Application ID click
        $(document).on('click', '.toggle-timeline', function() {
            var applicationId = $(this).closest('tr').data('application-id');
            var timelineRow = $('#timeline-' + applicationId);
            
            // Toggle visibility
            if (timelineRow.is(':visible')) {
                timelineRow.hide();
                $(this).find('i').removeClass('ri-arrow-down-circle-fill').addClass('ri-arrow-right-circle-fill');
            } else {
                $('.timeline-row').hide(); // Hide other timeline rows
                $('.toggle-timeline i').removeClass('ri-arrow-down-circle-fill').addClass('ri-arrow-right-circle-fill');
                timelineRow.show();
                $(this).find('i').removeClass('ri-arrow-right-circle-fill').addClass('ri-arrow-down-circle-fill');
            }
        });

        // Reinitialize tooltips after table update
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>