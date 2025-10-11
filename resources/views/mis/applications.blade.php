@extends('layouts.app')

@push('styles')
<style>
	.container-fluid {
		padding: 1rem;
	}

	.card {
		margin-bottom: 1rem;
		border-radius: 0.375rem;
	}

	.card-body {
		padding: 1rem;
	}

	.form-label {
		font-size: 0.875rem;
		margin-bottom: 0.25rem;
		font-weight: 500;
	}

	.form-select {
		font-size: 0.875rem;
	}

	.table-responsive {
		position: relative;
		overflow-x: auto;
	}

	.table th,
	.table td {
		vertical-align: middle;
		font-size: 0.8rem;
	}

	.badge {
		font-size: 0.75rem;
		padding: 0.3rem 0.6rem;
	}

	.no-data-message {
		font-size: 0.9rem;
		color: #6c757d;
		text-align: center;
		padding: 2rem;
	}

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

	.toggle-timeline {
		cursor: pointer;
		background: none;
		border: none;
		color: #6c757d;
		font-size: 1.1rem;
		padding: 4px;
		transition: all 0.2s ease;
	}

	.toggle-timeline:hover {
		color: #007bff;
		transform: scale(1.2);
	}

	.timeline-row {
		display: none;
		background-color: #f8f9fa;
		border-top: none;
	}

	.app-id-with-toggle {
		display: flex;
		align-items: center;
		gap: 8px;
	}

	.timeline-container {
		background-color: #f8f9fa;
		border-radius: 5px;
		max-height: 150px;
		overflow-x: auto;
		overflow-y: hidden;
		white-space: nowrap;
	}

	.timeline-item {
		display: inline-block;
		min-width: 120px;
		vertical-align: top;
		padding: 10px;
	}

	.arrow {
		display: inline-block;
		vertical-align: middle;
	}

	.status-filter-container {
		max-width: 300px;
	}

	#misApplicationsTable {
		width: 100%;
		min-width: 800px;
	}

	.table-sm th,
	.table-sm td {
		vertical-align: middle;
		font-size: 0.85rem;
	}

	@media (max-width: 768px) {
		.container-fluid {
			padding: 0.5rem;
		}

		.table th,
		.table td {
			font-size: 0.75rem;
		}

		.status-filter-container {
			max-width: 100%;
		}
	}
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4">
	{{-- Simple Status Filter --}}
	<div class="row mb-1">
		<div class="col-12">
			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center py-1 px-2">
					<h6 class="card-title mb-0 small font-weight-bold">
						MIS Applications Management
						@if($filters['kpi_filter'])
						<span class="text-muted">- {{ ucfirst(str_replace('_', ' ', $filters['kpi_filter'])) }}</span>
						@endif
					</h6>
				</div>
				<div class="card-body p-1">
					<form id="mis-filter-form" method="GET" class="row g-1 align-items-end">
						{{-- Hidden fields to preserve KPI filter data --}}
						<input type="hidden" name="kpi_filter" value="{{ $filters['kpi_filter'] }}">

						{{-- Dynamic Status Filter --}}
						<div class="col-6 col-md-4 status-filter-container">
							<label for="mis_status_filter" class="form-label small mb-1">Status</label>
							<select name="status" id="mis_status_filter" class="form-select form-select-sm">
								<option value="">All Applications</option>
								@foreach($statusGroups as $key => $group)
								@if($key !== '')
								<option value="{{ $group['slugs'] }}"
									{{ $filters['status'] == $group['slugs'] ? 'selected' : '' }}>
									{{ $group['label'] }}
								</option>
								@endif
								@endforeach
							</select>
						</div>

						<div class="col-auto d-flex gap-1">
    <button type="submit" class="btn btn-primary btn-sm px-3 py-1">Apply</button>
    <a href="{{ route('mis.applications') }}" class="btn btn-outline-secondary btn-sm px-3 py-1">Reset</a>
</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	{{-- MIS Applications Table --}}
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-header">
					<h5 class="card-title mb-0">
						MIS Applications List
						<span class="badge bg-primary">{{ $applications->total() }}</span>
					</h5>
				</div>
				<div class="card-body p-0">
					@if($applications->count() > 0)
					<div class="table-responsive">
						<table class="table table-sm table-hover table-bordered" id="misApplicationsTable">
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
								@foreach ($applications as $application)
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
										// Dynamic stage determination based on status
										$statusModel = $allStatuses->where('name', $application->status)->first();

										if ($statusModel) {
										$stageDisplay = $statusModel->display_name;
										} else {
										// Fallback logic based on approval_level
										$stageMap = [
										'Area Coordinator' => 'ABM',
										'Regional Business Manager' => 'RBM',
										'Zonal Business Manager' => 'ZBM',
										'General Manager' => 'GM',
										];
										$stageDisplay = $stageMap[$application->approval_level] ?? 'N/A';
										}
										@endphp
										{{ $stageDisplay }}
									</td>
									<td>
										@php
										$badgeColor = \App\Models\Status::getBadgeColorForStatus($application->status);
										$displayStatus = \App\Models\Status::getDisplayName($application->status);
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

												{{-- MIS Processing Actions --}}
												@php
												$misProcessingStatuses = explode(',', $statusGroups['mis']['slugs']);
												// Define which statuses actually need verification (not including documents_verified)
												$verificationRequiredStatuses = array_diff($misProcessingStatuses, ['documents_verified']);
												@endphp

												@if (in_array($application->status, $verificationRequiredStatuses))
												<li>
													<a class="dropdown-item mis-action-btn"
														href="{{ route('approvals.verify-documents', $application->id) }}"
														data-application-id="{{ $application->id }}"
														data-distributor-name="{{ $application->entityDetails->establishment_name ?? 'N/A' }}"
														data-submission-date="{{ $application->created_at->format('Y-m-d') }}"
														data-initiator="{{ $application->createdBy->emp_name ?? 'N/A' }}"
														data-status="{{ $application->status }}">
														<i class="ri-check-line align-bottom me-2 text-muted"></i>
														@if($application->status === 'mis_processing')
														Verify Checklist
														@elseif($application->status === 'documents_pending')
														Verify Pending Documents
														@elseif($application->status === 'documents_resubmitted')
														Verify Resubmitted Documents
														@else
														Verify Documents
														@endif
													</a>
												</li>
												@endif

												{{-- Document Verified Actions --}}
												@if ($application->status == 'documents_verified')
												<li>
													<a class="dropdown-item mis-action-btn"
														href="{{ route('approvals.physical-documents', $application->id) }}"
														data-application-id="{{ $application->id }}"
														data-distributor-name="{{ $application->entityDetails->establishment_name ?? 'N/A' }}"
														data-submission-date="{{ $application->created_at->format('Y-m-d') }}"
														data-initiator="{{ $application->createdBy->emp_name ?? 'N/A' }}"
														data-status="{{ $application->status }}">
														<i class="ri-file-text-line align-bottom me-2 text-muted"></i> Manage Physical Documents
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
														<i class="ri-user-add-line align-bottom me-2 text-muted"></i> Confirm Distributor
													</a>
												</li>
												@endif

												{{-- Document Verification View --}}
												@php
												$docViewStatuses = array_merge(
												explode(',', $statusGroups['mis']['slugs']),
												['agreement_created', 'physical_docs_verified']
												);
												@endphp
												@if (in_array($application->status, $docViewStatuses))
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
								<tr class="timeline-row" id="timeline-{{ $application->id }}" style="display: none;">
    <td colspan="8" class="p-0">
        {{-- Timeline content --}}
        @include('partials.approval_timeline', ['application' => $application])
    </td>
</tr>
								@endforeach
							</tbody>
						</table>
					</div>

					{{-- Pagination --}}
					<div class="d-flex justify-content-center mt-3 p-3">
						{{ $applications->appends(request()->query())->links() }}
					</div>
					@else
					<div class="no-data-message p-4">
						<i class="ri-inbox-line fs-2 text-muted"></i>
						<p class="mt-2 mb-0">No applications found based on current filters.</p>
					</div>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Include Modals -->
@include('mis.partials.modals')

@endsection

@push('scripts')
<script>
	$(document).ready(function() {
		// Initialize timeline toggles
		initializeTimelineToggles();

		// Auto-submit form when status changes
		$('#mis_status_filter').change(function() {
			$('#mis-filter-form').submit();
		});

		// Initialize dropdown menus
		initializeTableDropdowns();

		// Initialize modal handlers
		initializeModalHandlers();
	});

	// Move closeAllTableDropdowns to global scope
	function closeAllTableDropdowns() {
		$('.table-dropdown-menu').removeClass('show').css('display', 'none');
		$('.table-dropdown-btn').attr('aria-expanded', 'false');
		console.log('All table dropdowns closed');
	}

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
			} else {
				// Close all other timelines
				$('.timeline-row').hide();
				$('.toggle-timeline i').removeClass('ri-indeterminate-circle-line').addClass('ri-add-circle-line');

				// Open current timeline
				timelineRow.show();
				icon.removeClass('ri-add-circle-line').addClass('ri-indeterminate-circle-line');
			}
		});
	}

	function initializeTableDropdowns() {
		$(document).on('click', '.table-dropdown-btn', function(e) {
			e.preventDefault();
			e.stopPropagation();
			var $button = $(this);
			var $dropdown = $button.next('.table-dropdown-menu');
			var isVisible = $dropdown.hasClass('show');

			closeAllTableDropdowns();

			if (!isVisible) {
				var buttonRect = $button[0].getBoundingClientRect();
				var dropdownWidth = $dropdown.outerWidth();
				var dropdownHeight = $dropdown.outerHeight();

				var leftPosition = buttonRect.left;
				var topPosition = buttonRect.bottom;

				if (leftPosition + dropdownWidth > window.innerWidth) {
					leftPosition = window.innerWidth - dropdownWidth - 10;
				}

				if (topPosition + dropdownHeight > window.innerHeight) {
					topPosition = buttonRect.top - dropdownHeight;
				}

				$dropdown.css({
					'position': 'fixed',
					'top': topPosition + 'px',
					'left': leftPosition + 'px',
					'display': 'block'
				}).addClass('show');

				$button.attr('aria-expanded', 'true');
				console.log('Table dropdown opened for application ID:', $button.closest('tr').data('application-id'));
			} else {
				$dropdown.removeClass('show').css('display', 'none');
				$button.attr('aria-expanded', 'false');
				console.log('Table dropdown closed for application ID:', $button.closest('tr').data('application-id'));
			}
		});

		$(document).on('click', function(e) {
			if (!$(e.target).closest('.table-dropdown-container').length &&
				!$(e.target).closest('#page-topbar').length) {
				closeAllTableDropdowns();
			}
		});

		$(document).on('click', '.dropdown-item', function(e) {
			e.stopPropagation();
		});
	}

	function initializeModalHandlers() {
		// Document verification modal
		$(document).on('click', '.view-doc-btn', function(e) {
			e.preventDefault();
			e.stopPropagation();
			closeAllTableDropdowns();
			var url = $(this).data('url');
			var applicationId = $(this).data('application-id');
			$('#docVerificationModalLabel').text('Document Verification Details for Application ' + applicationId);
			$('#doc-verification-content').html('Loading...');

			$('#docVerificationModal').modal('show');

			$.get(url, function(response) {
				$('#doc-verification-content').html(response);
			}).fail(function(jqXHR, textStatus, errorThrown) {
				$('#doc-verification-content').html('<p>Error loading document verification details: ' + textStatus + '</p>');
			});
		});

		// Physical document verification modal
		$(document).on('click', '.view-physical-doc-btn', function(e) {
			e.preventDefault();
			e.stopPropagation();
			closeAllTableDropdowns();
			var url = $(this).data('url');
			var applicationId = $(this).data('application-id');
			$('#physicalDocVerificationModalLabel').text('Physical Document Verification Details for Application ' + applicationId);
			$('#physical-doc-verification-content').html('Loading...');

			$('#physicalDocVerificationModal').modal('show');

			$.get(url, function(response) {
				$('#physical-doc-verification-content').html(response);
			}).fail(function(jqXHR, textStatus, errorThrown) {
				$('#physical-doc-verification-content').html('<p>Error loading physical document verification details: ' + textStatus + '</p>');
			});
		});

		// Confirm distributor modal
		$(document).on('click', '.confirm-distributor-btn', function(e) {
			e.preventDefault();
			e.stopPropagation();
			closeAllTableDropdowns();
			var url = $(this).data('url');
			var applicationId = $(this).data('application-id');
			var distributorName = $(this).data('distributor-name');
			$('#confirm-application-id').text(applicationId);
			$('#confirm-distributor-name').text(distributorName);
			$('#confirm-remarks').val('');
			$('#confirmDistributorModal').modal('show');
			$('#confirm-distributor-submit').data('url', url);
		});

		// MIS action buttons (Verify Checklist, Manage Physical Documents)
		$(document).on('click', '.mis-action-btn', function(e) {
			e.preventDefault();
			e.stopPropagation();
			closeAllTableDropdowns();
			var url = $(this).attr('href');
			// Redirect to the action page
			window.location.href = url;
		});

		$(document).on('click', '#confirm-distributor-submit', function() {
			var url = $(this).data('url');
			var remarks = $('#confirm-remarks').val();
			$.ajax({
				url: url,
				method: 'POST',
				data: {
					remarks: remarks
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function(response) {
					if (response.success) {
						alert(response.message);
						$('#confirmDistributorModal').modal('hide');
						if (response.redirect) {
							window.location.href = response.redirect;
						} else {
							location.reload();
						}
					} else {
						alert(response.message);
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					alert('Error: ' + (jqXHR.responseJSON ? jqXHR.responseJSON.message : textStatus));
				}
			});
		});

		// Modal cleanup
		$('#docVerificationModal').on('hidden.bs.modal', function() {
			$('#doc-verification-content').html('Loading...');
		});
		$('#physicalDocVerificationModal').on('hidden.bs.modal', function() {
			$('#physical-doc-verification-content').html('Loading...');
		});
	}

	// Make functions available globally for debugging
	window.closeAllTableDropdowns = closeAllTableDropdowns;
	window.initializeTableDropdowns = initializeTableDropdowns;
</script>
@endpush