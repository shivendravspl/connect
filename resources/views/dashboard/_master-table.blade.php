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
                    <a href="{{ route('approvals.show', $application->id) }}" class="btn btn-sm btn-primary" title="View">
                        <i class="ri-eye-line"></i>
                    </a>
                </td>
            </tr>
            <!-- Timeline Row -->
            <tr class="timeline-row" id="timeline-{{ $application->id }}">
                <td colspan="12" class="p-3">
                    {{--<div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 text-muted">
                            <i class="ri-history-line me-1"></i> Approval Timeline - {{ $application->entityDetails->establishment_name ?? 'N/A' }}
                        </h6>
                        <small class="text-muted">Application ID: {{ $application->id }}</small>
                    </div>--}}
                    
                    @php
                        // Use the same timeline logic as other views
                        $approvalLevels = [
                            'Draft/Initiated' => null,
                            'Area Coordinator' => null,
                            'Regional Business Manager' => null,
                            'Zonal Business Manager' => null,
                            'General Manager' => null,
                            'MIS' => null,
                        ];

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
                                'status' => in_array($application->status, ['draft', 'initiated']) ? 'pending' : (in_array($application->status, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'distributorship_created', 'mis_rejected', 'physical_docs_verified']) ? 'approved' : 'not-started'),
                                'date' => in_array($application->status, ['draft', 'initiated']) ? $application->created_at->format('d M Y') : (in_array($application->status, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'distributorship_created', 'mis_rejected', 'physical_docs_verified']) ? $application->created_at->format('d M Y') : '-'),
                                'remarks' => $approvalLevels['Draft/Initiated'] ? $approvalLevels['Draft/Initiated']->remarks : '-',
                                'icon' => 'ri-draft-fill'
                            ],
                            [
                                'label' => 'ABM',
                                'log' => $approvalLevels['Area Coordinator'],
                                'status' => $approvalLevels['Area Coordinator'] ? $approvalLevels['Area Coordinator']->action : (in_array($application->status, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'distributorship_created', 'mis_rejected', 'physical_docs_verified']) || in_array($application->approval_level, ['Regional Business Manager', 'Zonal Business Manager', 'General Manager']) ? 'approved' : 'not-started'),
                                'date' => $approvalLevels['Area Coordinator'] ? $approvalLevels['Area Coordinator']->created_at->format('d M Y') : '-',
                                'remarks' => $approvalLevels['Area Coordinator'] ? $approvalLevels['Area Coordinator']->remarks : '-',
                                'icon' => 'ri-user-2-fill'
                            ],
                            [
                                'label' => 'RBM',
                                'log' => $approvalLevels['Regional Business Manager'],
                                'status' => $approvalLevels['Regional Business Manager'] ? $approvalLevels['Regional Business Manager']->action : (in_array($application->status, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'distributorship_created', 'mis_rejected', 'physical_docs_verified']) || in_array($application->approval_level, ['Zonal Business Manager', 'General Manager']) ? 'approved' : 'not-started'),
                                'date' => $approvalLevels['Regional Business Manager'] ? $approvalLevels['Regional Business Manager']->created_at->format('d M Y') : '-',
                                'remarks' => $approvalLevels['Regional Business Manager'] ? $approvalLevels['Regional Business Manager']->remarks : '-',
                                'icon' => 'ri-user-3-fill'
                            ],
                            [
                                'label' => 'ZBM',
                                'log' => $approvalLevels['Zonal Business Manager'],
                                'status' => $approvalLevels['Zonal Business Manager'] ? $approvalLevels['Zonal Business Manager']->action : (in_array($application->status, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'distributorship_created', 'mis_rejected', 'physical_docs_verified']) || $application->approval_level == 'General Manager' ? 'approved' : 'not-started'),
                                'date' => $approvalLevels['Zonal Business Manager'] ? $approvalLevels['Zonal Business Manager']->created_at->format('d M Y') : '-',
                                'remarks' => $approvalLevels['Zonal Business Manager'] ? $approvalLevels['Zonal Business Manager']->remarks : '-',
                                'icon' => 'ri-user-4-fill'
                            ],
                            [
                                'label' => 'GM',
                                'log' => $approvalLevels['General Manager'],
                                'status' => $approvalLevels['General Manager'] ? $approvalLevels['General Manager']->action : (in_array($application->status, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'distributorship_created', 'mis_rejected', 'physical_docs_verified']) ? 'approved' : 'not-started'),
                                'date' => $approvalLevels['General Manager'] ? $approvalLevels['General Manager']->created_at->format('d M Y') : '-',
                                'remarks' => $approvalLevels['General Manager'] ? $approvalLevels['General Manager']->remarks : '-',
                                'icon' => 'ri-user-5-fill'
                            ],
                            [
                                'label' => 'MIS',
                                'log' => $approvalLevels['MIS'],
                                'status' => in_array($application->status, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'physical_docs_verified']) ? 'pending' : ($application->status == 'distributorship_created' ? 'approved' : ($application->status == 'mis_rejected' ? 'rejected' : 'not-started')),
                                'date' => in_array($application->status, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'distributorship_created', 'mis_rejected', 'physical_docs_verified']) ? $application->updated_at->format('d M Y') : '-',
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
                    <div class="timeline-container d-flex flex-row align-items-center justify-content-start p-3 overflow-x-auto overflow-y-hidden bg-light rounded">
                        @foreach($stages as $index => $stage)
                        <div class="timeline-item text-center px-2">
                            <i class="{{ $stage['icon'] }} mb-2 {{ $stage['status'] == 'approved' ? 'text-success' : ($stage['status'] == 'rejected' ? 'text-danger' : ($stage['status'] == 'pending' ? 'text-warning' : 'text-muted')) }}"
                                title="{{ $stage['label'] }} - {{ ucfirst($stage['status']) }}"
                                style="font-size: 1.2rem;"></i>
                            <div class="timeline-stage-info">
                                <strong class="small">{{ $stage['label'] }}</strong><br>
                                <span class="badge bg-{{ $stage['status'] == 'approved' ? 'success' : ($stage['status'] == 'rejected' ? 'danger' : ($stage['status'] == 'pending' ? 'warning' : 'secondary')) }}">
                                    {{ ucfirst($stage['status']) }}
                                </span><br>
                                <small class="text-muted"><strong>Date:</strong> {{ $stage['date'] }}</small><br>
                                <small class="text-muted"><strong>Remarks:</strong> {{ $stage['remarks'] }}</small>
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
                <td colspan="12" class="text-center no-data-message">No applications found based on current filters.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    {{ $masterReportApplications->links() }}
</div>
