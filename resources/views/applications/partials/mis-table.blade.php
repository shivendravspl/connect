@php
use App\Models\Status;
@endphp

<div class="table-responsive">
    <table class="table table-sm table-hover table-bordered" id="misApplicationsTable">
        <thead>
            <tr>
                <th>Application ID</th>
                <th>Distributor Name</th>
                <th>Zone</th>
                <th>Submission Date</th>
                <th>Initiator</th>
                <th>Status</th>
                <th>Actions</th>
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
                <td class="align-middle small">
                    {{ $application->zoneDetail->zone_name ?? 'N/A' }}
                </td>
                <td>{{ $application->created_at->format('Y-m-d') }}</td>
                <td>{{ $application->createdBy->emp_name ?? 'N/A' }}</td>
                <td>
                    @php
                    $badgeColor = Status::getBadgeColorForStatus($application->status);
                    $displayStatus = Status::getDisplayName($application->status);
                    @endphp
                    <span class="badge bg-{{ $badgeColor }}">
                        {{ $displayStatus }}
                    </span>
                </td>
                <td>
                    <div class="table-dropdown-container">
                        <button class="btn btn-soft-secondary btn-sm table-dropdown-btn" type="button" aria-expanded="false">
                            <i class="ri-more-fill"></i>
                        </button>
                        <ul class="table-dropdown-menu table-dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('approvals.show', $application->id) }}">
                                    <i class="ri-eye-fill align-bottom me-2 text-muted"></i> View
                                </a>
                            </li>
                             @if(($application->status !== 'draft') && $application->created_by === Auth::user()->emp_id)
                                <li>
                                    <a href="{{ route('dispatch.show', $application->id) }}" 
                                        class="dropdown-item"
                                        title="Fill Dispatch Details">
                                            <i class="ri-truck-line align-bottom me-2 text-muted"></i> Dispatch
                                    </a>
                                </li>
                            @endif
                            @if(($application->status === 'reverted' || $application->status === 'draft') && $application->created_by === Auth::user()->emp_id)
                               <li> <a href="{{ route('applications.edit', $application->id) }}" class="dropdown-item" title="Edit">
                                    <i class="ri-edit-line align-bottom me-2 text-muted"></i>
                                </a></li>
                            @endif
                            {{-- MIS Processing Actions --}}
                            @if (in_array($application->status, ['mis_processing', 'documents_pending', 'documents_resubmitted']))
                            <li>
                               @php
                                   $verifyLabels = [
                                       'mis_processing' => 'Verify',
                                       'documents_pending' => 'Verify/Reverted',
                                       'documents_resubmitted' => 'Reverify',
                                   ];

                                   $verifyTitles = [
                                       'mis_processing' => 'Verify Documents',
                                       'documents_pending' => 'Verify or Reverted Documents',
                                       'documents_resubmitted' => 'Reverify Documents',
                                   ];

                                   $label = $verifyLabels[$application->status] ?? 'Verify';
                                   $title = $verifyTitles[$application->status] ?? 'Verify Documents';
                               @endphp

                               <a class="dropdown-item mis-action-btn"
                                  href="{{ route('approvals.verify-documents', $application->id) }}"
                                  title="{{ $title }}"
                                  data-application-id="{{ $application->id }}"
                                  data-distributor-name="{{ $application->entityDetails->establishment_name ?? 'N/A' }}"
                                  data-submission-date="{{ $application->created_at->format('Y-m-d') }}"
                                  data-initiator="{{ $application->createdBy->emp_name ?? 'N/A' }}"
                                  data-status="{{ $application->status }}">
                                   <i class="ri-check-line align-bottom me-2 text-muted"></i> {{ $label }} Checklist
                               </a>
                            </li>
                            @endif

                            {{-- Document Verified Actions --}}
                            @if (($application->status == 'documents_verified' || 
                                $application->status == 'physical_docs_pending' || 
                                $application->status == 'physical_docs_redispatched' || $application->status === 'security_deposit_not_received') && 
                                $application->physicalDispatch)
                            <li>
                                <a class="dropdown-item mis-action-btn"
                                    href="{{ route('approvals.physical-documents', $application->id) }}"
                                    data-application-id="{{ $application->id }}"
                                    data-distributor-name="{{ $application->entityDetails->establishment_name ?? 'N/A' }}"
                                    data-submission-date="{{ $application->created_at->format('Y-m-d') }}"
                                    data-initiator="{{ $application->createdBy->emp_name ?? 'N/A' }}"
                                    data-status="{{ $application->status }}">
                                    <i class="ri-file-text-line align-bottom me-2 text-muted"></i> 
                                    @if($application->status == 'physical_docs_redispatched')
                                        Manage Redispatched Documents
                                    @else
                                        Manage Physical Documents
                                    @endif
                                </a>
                            </li>
                            @endif

                           {{-- Draft Agreement Button --}}
                            @if ($application->status == 'documents_verified' || $application->status == 'physical_docs_verified' || $application->status == 'physical_docs_pending' || $application->status == 'physical_docs_redispatched')
                            <li>
                                <a class="dropdown-item"
                                href="{{ route('approvals.draft-agreement', $application->id) }}"
                                target="_blank">
                                    <i class="ri-file-copy-line align-bottom me-2 text-muted"></i> Draft Agreement
                                </a>
                            </li>
                            @endif

                            {{-- Agreement and Physical Docs Verified Actions --}}
                            @if (in_array($application->status, ['agreement_created', 'physical_docs_verified']))
                            <li>
                                <a class="dropdown-item confirm-distributor-btn"
                                        href="javascript:void(0);"
                                        data-url="{{ route('approvals.confirm-distributor', $application->id) }}"
                                        data-application-id="{{ $application->id }}"
                                        data-distributor-name="{{ $application->entityDetails->establishment_name ?? 'N/A' }}"
                                        data-submission-date="{{ $application->created_at->format('Y-m-d') }}"
                                        data-initiator="{{ $application->createdBy->emp_name ?? 'N/A' }}"
                                        data-status="{{ $application->status }}">
                                        <i class="ri-user-add-line align-bottom me-2 text-muted"></i> Finalize distributorship appointment
                                </a>
                            </li>
                            @endif

                            {{-- Document Verification View --}}
                            @if (in_array($application->status, ['documents_verified', 'documents_pending', 'documents_resubmitted', 'agreement_created', 'physical_docs_verified']))
                            <li>
                                <a class="dropdown-item view-doc-btn"
                                    href="javascript:void(0);"
                                    data-url="{{ route('approvals.view-doc-verification', $application->id) }}"
                                    data-application-id="{{ $application->id }}">
                                    <i class="ri-file-list-3-line align-bottom me-2 text-muted"></i> View Document Verification
                                </a>
                            </li>
                            @endif

                            {{-- Physical Document Verification View --}}
                            @if (in_array($application->status, ['physical_docs_verified', 'agreement_created']))
                            <li>
                                <a class="dropdown-item view-physical-doc-btn"
                                    href="javascript:void(0);"
                                    data-url="{{ route('approvals.view-physical-doc-verification', $application->id) }}"
                                    data-application-id="{{ $application->id }}">
                                    <i class="ri-file-shield-2-line align-bottom me-2 text-muted"></i> View Physical Document Verification
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </td>
            </tr>
            {{-- Timeline row (same as before) --}}
              <tr class="timeline-row" id="timeline-{{ $application->id }}" style="display: none;">
				<td colspan="8" class="p-3">
					@php
						// Get the latest log for each role
						$latestLogs = [];
						
						foreach ($application->approvalLogs as $log) {
							$role = $log->role;
							// If we don't have a log for this role, or if this log is newer, use it
							if (!isset($latestLogs[$role]) || $log->created_at > $latestLogs[$role]->created_at) {
								$latestLogs[$role] = $log;
							}
						}

						// Map roles to approval levels
						$approvalLevels = [
							'Regional Business Manager' => $latestLogs['Regional Business Manager'] ?? null,
							'Zonal Business Manager' => $latestLogs['Zonal Business Manager'] ?? null,
							'General Manager' => $latestLogs['General Manager'] ?? null,
							'MIS' => $latestLogs['MIS'] ?? $latestLogs['Senior Executive'] ?? null,
						];

						$currentStatus = $application->status;
						$currentApprovalLevel = $application->approval_level;

						// Build stages
						$stages = [];

						// Stage 1: Draft/Initiated
						$stages[] = [
							'label' => 'Draft/Initiated',
							'log' => null,
							'status' => in_array($currentStatus, ['draft', 'initiated']) ? 'pending' : 'approved',
							'date' => $application->created_at->format('d M Y'),
							'remarks' => 'Application submitted',
							'icon' => 'ri-draft-fill'
						];

						// Stage 2: Regional Business Manager
						$rbmLog = $approvalLevels['Regional Business Manager'];
						$rbmStatus = $rbmLog ? $rbmLog->action : 'not-started';
						$rbmDate = $rbmLog ? $rbmLog->created_at->format('d M Y') : '-';
						$rbmRemarks = $rbmLog ? $rbmLog->remarks : '-';

						$stages[] = [
							'label' => 'RBM',
							'log' => $rbmLog,
							'status' => $rbmStatus,
							'date' => $rbmDate,
							'remarks' => $rbmRemarks,
							'icon' => 'ri-user-3-fill'
						];

						// Stage 3: Zonal Business Manager (only if exists)
						$zbmLog = $approvalLevels['Zonal Business Manager'];
						if ($zbmLog) {
							$stages[] = [
								'label' => 'ZBM',
								'log' => $zbmLog,
								'status' => $zbmLog->action,
								'date' => $zbmLog->created_at->format('d M Y'),
								'remarks' => $zbmLog->remarks,
								'icon' => 'ri-user-4-fill'
							];
						}

						// Stage 4: General Manager
						$gmLog = $approvalLevels['General Manager'];
						$gmStatus = 'not-started';
						$gmDate = '-';
						$gmRemarks = '-';

						if ($gmLog) {
							$gmStatus = $gmLog->action;
							$gmDate = $gmLog->created_at->format('d M Y');
							$gmRemarks = $gmLog->remarks;
						} elseif ($rbmStatus == 'approved' && !$zbmLog) {
							// If RBM approved and no ZBM, GM should be pending
							$gmStatus = 'pending';
						} elseif ($zbmLog && $zbmLog->action == 'approved') {
							// If ZBM approved, GM should be pending
							$gmStatus = 'pending';
						}

						$stages[] = [
							'label' => 'GM',
							'log' => $gmLog,
							'status' => $gmStatus,
							'date' => $gmDate,
							'remarks' => $gmRemarks,
							'icon' => 'ri-user-5-fill'
						];

						// Stage 5: MIS
						$misLog = $approvalLevels['MIS'];
						$misStatus = 'not-started';
						$misDate = '-';
						$misRemarks = '-';

						if ($misLog) {
							$misStatus = $misLog->action;
							$misDate = $misLog->created_at->format('d M Y');
							$misRemarks = $misLog->remarks;
						} elseif ($gmStatus == 'approved' && in_array($currentStatus, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'physical_docs_verified'])) {
							$misStatus = 'pending';
							$misDate = $application->updated_at->format('d M Y');
						} elseif ($currentStatus == 'distributorship_created') {
							$misStatus = 'approved';
							$misDate = $application->updated_at->format('d M Y');
						} elseif ($currentStatus == 'mis_rejected') {
							$misStatus = 'rejected';
							$misDate = $application->updated_at->format('d M Y');
						}

						$stages[] = [
							'label' => 'MIS',
							'log' => $misLog,
							'status' => $misStatus,
							'date' => $misDate,
							'remarks' => $misRemarks,
							'icon' => 'ri-file-text-fill'
						];

						// Final Stage
						$finalStatus = $currentStatus == 'distributorship_created' ? 'approved' : 'not-started';
						$finalDate = $currentStatus == 'distributorship_created' ? $application->updated_at->format('d M Y') : '-';

						$stages[] = [
							'label' => 'Final',
							'log' => null,
							'status' => $finalStatus,
							'date' => $finalDate,
							'remarks' => $currentStatus == 'distributorship_created' ? 'Distributorship created successfully' : '-',
							'icon' => 'ri-checkbox-circle-fill'
						];

						// Handle special statuses
						if ($currentStatus == 'reverted') {
							foreach ($stages as &$stage) {
								if ($stage['label'] == $currentApprovalLevel) {
									$stage['status'] = 'reverted';
									$stage['remarks'] = 'Application reverted for corrections';
								}
							}
						}
					@endphp

					<div class="timeline-container d-flex flex-row align-items-center justify-content-start p-3 overflow-x-auto overflow-y-hidden bg-light rounded">
						@foreach($stages as $index => $stage)
						<div class="timeline-item text-center px-2" style="min-width: 140px;">
							<i class="{{ $stage['icon'] }} mb-2 {{ $stage['status'] == 'approved' ? 'text-success' : ($stage['status'] == 'rejected' ? 'text-danger' : ($stage['status'] == 'pending' ? 'text-warning' : ($stage['status'] == 'hold' ? 'text-info' : ($stage['status'] == 'reverted' ? 'text-info' : 'text-muted')))) }}"
								title="{{ $stage['label'] }} - {{ ucfirst($stage['status']) }}"
								style="font-size: 1.2rem;"></i>
							<div class="timeline-stage-info">
								<strong class="small">{{ $stage['label'] }}</strong><br>
								<span class="badge bg-{{ $stage['status'] == 'approved' ? 'success' : ($stage['status'] == 'rejected' ? 'danger' : ($stage['status'] == 'pending' ? 'warning' : ($stage['status'] == 'hold' ? 'info' : ($stage['status'] == 'reverted' ? 'info' : 'secondary')))) }} small">
									{{ ucfirst($stage['status']) }}
								</span><br>
								<small class="text-muted d-block"><strong>Date:</strong> {{ $stage['date'] }}</small>
								<small class="text-muted d-block"><strong>Remarks:</strong> {{ \Illuminate\Support\Str::limit($stage['remarks'], 30) }}</small>
							</div>
						</div>
						@if(!$loop->last)
						<div class="arrow px-1 d-flex align-items-center">
							<span style="font-size: 1.2rem; color: #6c757d;">→</span>
						</div>
						@endif
						@endforeach
					</div>
				</td>
			</tr>
            @empty
            <tr>
                <td colspan="8" class="no-data-message">No applications found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-center mt-2">
    {{ $applications->appends(request()->query())->links('pagination::simple-bootstrap-5') }}
</div>