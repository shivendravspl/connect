@php
use App\Models\Status;
use App\Http\Controllers\DistributorReportController;
@endphp
@extends('layouts.app')

@section('title', 'TAT Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="page-title mb-0" style="font-size: 16px;">TAT Report</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-2">
                    <!-- Comprehensive Filters -->
                    <form method="GET" class="mb-2">
                        <div class="row g-1 align-items-end">
                            <!-- Search -->
                            <div class="col-md-2">
                                <label class="form-label small mb-0">Search</label>
                                <input type="text" name="search" class="form-control form-control-sm" 
                                       placeholder="Code, Name, or Person" 
                                       value="{{ request('search') }}">
                            </div>
                            
                            <!-- Business Unit -->
                            @if ($userCapabilities['access_level'] == 'bu' || $userCapabilities['access_level'] == 'all')
                            <div class="col-md-2">
                                <label class="form-label small mb-0">BU</label>
                                <select name="bu" class="form-control form-control-sm" id="bu">
                                    <option value="All">All BU</option>
                                    @foreach($bu_list as $id => $name)
                                        @if($id !== 'All')
                                            <option value="{{ $id }}" {{ request('bu') == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <!-- Zone -->
                            @if ($userCapabilities['access_level'] == 'bu' || $userCapabilities['access_level'] == 'zone' || $userCapabilities['access_level'] == 'all')
                            <div class="col-md-2">
                                <label class="form-label small mb-0">Zone</label>
                                <select name="zone" class="form-control form-control-sm" id="zone">
                                    <option value="All">All Zone</option>
                                    @foreach($zone_list as $id => $name)
                                        @if($id !== 'All')
                                            <option value="{{ $id }}" {{ request('zone') == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <!-- Region -->
                            @if ($userCapabilities['access_level'] == 'bu' || $userCapabilities['access_level'] == 'zone' || $userCapabilities['access_level'] == 'region' || $userCapabilities['access_level'] == 'all')
                            <div class="col-md-2">
                                <label class="form-label small mb-0">Region</label>
                                <select name="region" class="form-control form-control-sm" id="region">
                                    <option value="All">All Region</option>
                                    @foreach($region_list as $id => $name)
                                        @if($id !== 'All')
                                            <option value="{{ $id }}" {{ request('region') == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <!-- Territory -->
                            @if ($userCapabilities['access_level'] == 'bu' || $userCapabilities['access_level'] == 'zone' || $userCapabilities['access_level'] == 'region' || $userCapabilities['access_level'] == 'territory' || $userCapabilities['access_level'] == 'all')
                            <div class="col-md-2">
                                <label class="form-label small mb-0">Territory</label>
                                <select name="territory" class="form-control form-control-sm" id="territory">
                                    <option value="All">All Territory</option>
                                    @foreach($territory_list as $id => $name)
                                        @if($id !== 'All')
                                            <option value="{{ $id }}" {{ request('territory') == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <!-- Status -->
                            <div class="col-md-2">
                                <label class="form-label small mb-0">Status</label>
                                <select name="status" class="form-select form-select-sm" data-placeholder="All Statuses">
                                    <option value="All" {{ $filters['status'] == 'All' ? 'selected' : '' }}>All Status</option>
                                    <optgroup label="Main Status Groups">
                                        <option value="draft" {{ $filters['status'] == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="sales_approval" {{ $filters['status'] == 'sales_approval' ? 'selected' : '' }}>Sales Approval</option>
                                        <option value="mis_verification" {{ $filters['status'] == 'mis_verification' ? 'selected' : '' }}>MIS Verification</option>
                                        <option value="completed" {{ $filters['status'] == 'completed' ? 'selected' : '' }}>Completed</option>
                                    </optgroup>
                                    <optgroup label="Individual Statuses">
                                        <option value="reverted" {{ $filters['status'] == 'reverted' ? 'selected' : '' }}>Reverted</option>
                                        <option value="rejected" {{ $filters['status'] == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        <option value="on_hold" {{ $filters['status'] == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                        <option value="security_deposit_not_received" {{ $filters['status'] == 'security_deposit_not_received' ? 'selected' : '' }}>
                                            Security Deposit Not Received
                                        </option>
                                        @foreach($statuses as $status)
                                            @if(in_array($status->status_name, ['mis_processing', 'documents_pending', 'documents_resubmitted', 'documents_verified', 'physical_docs_pending', 'physical_docs_redispatched', 'physical_docs_verified', 'agreement_created', 'distributorship_created']))
                                                <option value="{{ $status->status_name }}" {{ $filters['status'] == $status->status_name ? 'selected' : '' }}>
                                                    {{ ucfirst(str_replace('_', ' ', $status->status_name)) }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                        </div>

                        <div class="row g-1 align-items-end mt-1">
                            <!-- Date Range -->
                            <div class="col-md-2">
                                <label class="form-label small mb-0">From Date</label>
                                <input type="date" name="date_from" class="form-control form-control-sm" 
                                       value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-0">To Date</label>
                                <input type="date" name="date_to" class="form-control form-control-sm" 
                                       value="{{ request('date_to') }}">
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary btn-sm mt-3">
                                    <i class="ri-search-line me-1"></i> Filter
                                </button>
                                <a href="{{ route('applications.reports.tat') }}" 
                                   class="btn btn-secondary btn-sm mt-3">
                                    <i class="ri-refresh-line me-1"></i> Reset
                                </a>
                                
                                <!-- Export Button -->
                                <a href="{{ route('applications.reports.tat', array_merge(request()->all(), ['export' => 'excel'])) }}" 
                                   class="btn btn-success btn-sm mt-3">
                                    <i class="ri-file-excel-line me-1"></i> Export ({{ $distributors->total() }})
                                </a>
                            </div>

                            <!-- Results Info -->
                            <div class="col-md-4 text-end">
                                @if(request()->anyFilled(['search', 'bu', 'zone', 'region', 'territory', 'date_from', 'date_to', 'status']))
                                <div class="small text-muted mt-3">
                                    <i class="ri-information-line me-1"></i>
                                    Filtered: {{ $distributors->total() }} records
                                </div>
                                @endif
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive" style="font-size: 9px;">
                        <table class="table table-bordered table-striped table-sm mb-1">
                            <thead class="small">
                                <tr>
                                    <th>Sr No</th>
                                    <th>App Date</th>
                                    <th>App Code</th>
                                    <th>Initiated By</th>
                                    <th>Establishment Name</th>
                                    <th>Authorized Person</th>
                                    <th>Crop Vertical</th>
                                    <th>Application Status</th>
                                    <th>RBM Approval Date</th>
                                    <th>ZBM Approval Date</th>
                                    <th>GM Approval Date</th>
                                    <th>Revert Date</th>
                                    <th>Reply Date</th>
                                    <th>Dispatch Date</th>
                                    <th>Physical Receive Date</th>
                                    <th>MIS Verification Date</th>
                                    <th>Final Creation Date</th>
                                    <th>RBM TAT</th>
                                    <th>ZBM TAT</th>
                                    <th>GM TAT</th>
                                    <th>MIS Doc Verification</th>
                                    <th>Reply/Revert TAT</th>
                                    <th>Dispatch/Physical TAT</th>
                                    <th>MIS TAT</th>
                                    <th>Physical Doc Pendency</th>
                                    <th>Deposit TAT</th>
                                    <th>Distributor Finalisation</th>
                                    <th>Total TAT</th>
                                    <th>TAT Status</th>
                                    <th>Pending Level</th>
                                    <th>Days Pending</th>
                                    <th>Remarks/Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($distributors as $index => $distributor)
                                @php
                                    $tatData = DistributorReportController::calculateTATData($distributor);
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $distributor->created_at->format('d-m-Y') }}</td>
                                    <td>{{ $distributor->application_code ?? 'N/A' }}</td>
                                    <td>{{ $distributor->created_by_name ?? 'N/A' }}</td>
                                    <td>{{ $distributor->establishment_name ?? 'N/A' }}</td>
                                    <td>{{ $distributor->getAuthorizedOrEntityName() ?? 'N/A' }}</td>
                                    <td>{{ $distributor->vertical_name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ \App\Helpers\Helpers::getStatusBadgeColor($distributor->status) }}" style="font-size: 8px;">
                                            {{ ucfirst(str_replace('_', ' ', $distributor->status)) }}
                                        </span>
                                    </td>
                                    
                                    <!-- Approval Dates -->
                                    <td>{{ $tatData['rbm_approval_date'] ? $tatData['rbm_approval_date']->format('d-m-Y') : 'Pending' }}</td>
                                    <td>{{ $tatData['zbm_approval_date'] ? $tatData['zbm_approval_date']->format('d-m-Y') : 'Pending' }}</td>
                                    <td>{{ $tatData['gm_approval_date'] ? $tatData['gm_approval_date']->format('d-m-Y') : 'Pending' }}</td>
                                    <td>{{ $tatData['revert_date'] ? $tatData['revert_date']->format('d-m-Y') : 'N/A' }}</td>
                                    <td>{{ $tatData['reply_date'] ? $tatData['reply_date']->format('d-m-Y') : 'N/A' }}</td>
                                    <td>{{ $tatData['dispatch_date'] ? \Carbon\Carbon::parse($tatData['dispatch_date'])->format('d-m-Y') : 'N/A' }}</td>
                                    <td>{{ $tatData['physical_receive_date'] ? \Carbon\Carbon::parse($tatData['physical_receive_date'])->format('d-m-Y') : 'N/A' }}</td>
                                    <td>{{ $tatData['mis_verification_date'] ? $tatData['mis_verification_date']->format('d-m-Y') : 'Pending' }}</td>
                                    <td>{{ $tatData['final_creation_date'] ? $tatData['final_creation_date']->format('d-m-Y') : 'Pending' }}</td>
                                    
                                    <!-- TAT Values -->
                                    <td class="text-center">
                                        @if($tatData['rbm_tat'] !== null)
                                            <span class="badge bg-{{ $tatData['rbm_tat'] <= 2 ? 'success' : ($tatData['rbm_tat'] <= 3 ? 'warning' : 'danger') }}">
                                                {{ (int)$tatData['rbm_tat'] }} days
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($tatData['zbm_tat'] !== null)
                                            <span class="badge bg-{{ $tatData['zbm_tat'] <= 2 ? 'success' : ($tatData['zbm_tat'] <= 3 ? 'warning' : 'danger') }}">
                                                {{ (int)$tatData['zbm_tat'] }} days
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($tatData['gm_tat'] !== null)
                                            <span class="badge bg-{{ $tatData['gm_tat'] <= 3 ? 'success' : ($tatData['gm_tat'] <= 5 ? 'warning' : 'danger') }}">
                                                {{ (int)$tatData['gm_tat'] }} days
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($tatData['mis_doc_verification_tat'] !== null)
                                            <span class="badge bg-{{ $tatData['mis_doc_verification_tat'] <= 1 ? 'success' : ($tatData['mis_doc_verification_tat'] <= 2 ? 'warning' : 'danger') }}">
                                                {{ (int)$tatData['mis_doc_verification_tat'] }} days
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($tatData['revert_reply_tat'] !== null)
                                            <span class="badge bg-{{ $tatData['revert_reply_tat'] <= 1 ? 'success' : ($tatData['revert_reply_tat'] <= 2 ? 'warning' : 'danger') }}">
                                                {{ (int)$tatData['revert_reply_tat'] }} days
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($tatData['dispatch_tat'] !== null)
                                            <span class="badge bg-{{ $tatData['dispatch_tat'] <= 7 ? 'success' : ($tatData['dispatch_tat'] <= 11 ? 'warning' : 'danger') }}">
                                                {{ (int)$tatData['dispatch_tat'] }} days
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($tatData['mis_tat'] !== null)
                                            <span class="badge bg-{{ $tatData['mis_tat'] <= 1 ? 'success' : ($tatData['mis_tat'] <= 2 ? 'warning' : 'danger') }}">
                                                {{ (int)$tatData['mis_tat'] }} days
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($tatData['physical_pendency_tat'] !== null)
                                            <span class="badge bg-{{ $tatData['physical_pendency_tat'] <= 1 ? 'success' : ($tatData['physical_pendency_tat'] <= 2 ? 'warning' : 'danger') }}">
                                                {{ (int)$tatData['physical_pendency_tat'] }} days
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($tatData['deposit_tat'] !== null)
                                            <span class="badge bg-{{ $tatData['deposit_tat'] <= 1 ? 'success' : ($tatData['deposit_tat'] <= 2 ? 'warning' : 'danger') }}">
                                                {{ (int)$tatData['deposit_tat'] }} days
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($tatData['distributor_finalisation_tat'] !== null)
                                            <span class="badge bg-{{ $tatData['distributor_finalisation_tat'] <= 2 ? 'success' : ($tatData['distributor_finalisation_tat'] <= 3 ? 'warning' : 'danger') }}">
                                                {{ (int)$tatData['distributor_finalisation_tat'] }} days
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($tatData['total_tat'] !== null)
                                            <span class="badge bg-{{ $tatData['total_tat'] <= 20 ? 'success' : ($tatData['total_tat'] <= 33 ? 'warning' : 'danger') }}">
                                                {{ (int)$tatData['total_tat'] }} days
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $tatData['tat_status'] == 'Within SLA' ? 'success' : ($tatData['tat_status'] == 'Moderate Delay' ? 'warning' : 'danger') }}" style="font-size: 8px;">
                                            {{ $tatData['tat_status'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info" style="font-size: 8px;">
                                            {{ $tatData['pending_level'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $tatData['days_pending'] <= 3 ? 'success' : ($tatData['days_pending'] <= 7 ? 'warning' : 'danger') }}">
                                            {{ (int)$tatData['days_pending'] }} days
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">-</small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="31" class="text-center py-2">No data found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Compact Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <div style="font-size: 11px;">
                            Showing {{ $distributors->firstItem() }} to {{ $distributors->lastItem() }} of {{ $distributors->total() }} entries
                        </div>
                        <div>
                            {{ $distributors->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let isInitializingFromUrl = false;
    let isUpdating = false;
    const loader = $('<div class="d-none">Loading...</div>'); // Create a simple loader

    // Update Select2 function
    function updateSelect2(selector) {
        if ($(selector).hasClass('select2-hidden-accessible')) {
            $(selector).select2('destroy');
        }
        $(selector).select2({
            width: '100%',
            theme: 'bootstrap-5'
        });
    }

    function getZoneByBU(bu, callback = null) {
        const zoneSelect = $('#zone');
        
        if (bu === 'All' || !bu) {
            zoneSelect.empty().append('<option value="All">All Zone</option>');
            // Reset dependent dropdowns only if not initializing
            if (!isInitializingFromUrl) {
                $('#region').empty().append('<option value="All">All Region</option>');
                $('#territory').empty().append('<option value="All">All Territory</option>');
            }
            updateSelect2('#zone');
            if (callback) callback();
            loader.addClass('d-none');
            return;
        }
        
        $.ajax({
            url: "{{ route('get_zone_by_bu') }}",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { bu: bu },
            type: 'POST',
            dataType: 'json',
            beforeSend: function() { 
                if (!isUpdating) loader.removeClass('d-none'); 
            },
            success: function(data) {
                loader.addClass('d-none');
                const zoneList = data.zoneList || [];
                zoneSelect.empty().append('<option value="All">All Zone</option>');
                
                $.each(zoneList, function(index, zone) {
                    zoneSelect.append(`<option value="${zone.id}">${zone.zone_name}</option>`);
                });
                
                updateSelect2('#zone');
                
                if (callback) {
                    callback();
                } else if (bu !== 'All' && zoneList.length > 0 && !isUpdating) {
                    // updateFunction(); // Uncomment if needed
                }
                isUpdating = false;
            },
            error: function(xhr) {
                loader.addClass('d-none');
                console.error('Error fetching zones:', xhr.responseText);
                isUpdating = false;
                if (callback) callback();
            }
        });
    }

    function getRegionByZone(zone, callback = null) {
        const regionSelect = $('#region');
        
        if (zone === 'All' || !zone) {
            regionSelect.empty().append('<option value="All">All Region</option>');
            // Reset dependent dropdown only if not initializing
            if (!isInitializingFromUrl) {
                $('#territory').empty().append('<option value="All">All Territory</option>');
            }
            updateSelect2('#region');
            if (callback) callback();
            loader.addClass('d-none');
            return;
        }
        
        $.ajax({
            url: "{{ route('get_region_by_zone') }}",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { zone: zone },
            type: 'POST',
            dataType: 'json',
            beforeSend: function() { 
                if (!isUpdating) loader.removeClass('d-none'); 
            },
            success: function(data) {
                loader.addClass('d-none');
                const regionList = data.regionList || [];
                regionSelect.empty().append('<option value="All">All Region</option>');
                
                $.each(regionList, function(index, region) {
                    regionSelect.append(`<option value="${region.id}">${region.region_name}</option>`);
                });
                
                updateSelect2('#region');
                
                if (callback) {
                    callback();
                } else if (zone !== 'All' && regionList.length > 0 && !isUpdating) {
                    // updateFunction(); // Uncomment if needed
                }
                isUpdating = false;
            },
            error: function(xhr) {
                loader.addClass('d-none');
                console.error('Error fetching regions:', xhr.responseText);
                isUpdating = false;
                if (callback) callback();
            }
        });
    }

    function getTerritoryByRegion(region, callback = null) {
        const territorySelect = $('#territory');
        
        if (region === 'All' || !region) {
            territorySelect.empty().append('<option value="All">All Territory</option>');
            updateSelect2('#territory');
            if (callback) callback();
            loader.addClass('d-none');
            return;
        }
        
        $.ajax({
            url: "{{ route('get_territory_by_region') }}",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { region: region },
            type: 'POST',
            dataType: 'json',
            beforeSend: function() { 
                if (!isUpdating) loader.removeClass('d-none'); 
            },
            success: function(data) {
                loader.addClass('d-none');
                const territoryList = data.territoryList || [];
                territorySelect.empty().append('<option value="All">All Territory</option>');
                
                $.each(territoryList, function(index, territory) {
                    territorySelect.append(`<option value="${territory.id}">${territory.territory_name}</option>`);
                });
                
                updateSelect2('#territory');
                
                if (callback) {
                    callback();
                } else if (region !== 'All' && territoryList.length > 0 && !isUpdating) {
                    // updateFunction(); // Uncomment if needed
                }
                isUpdating = false;
            },
            error: function(xhr) {
                loader.addClass('d-none');
                console.error('Error fetching territories:', xhr.responseText);
                isUpdating = false;
                if (callback) callback();
            }
        });
    }

    // Initialize cascade filtering
    $(document).ready(function() {
        // Initialize Select2 if available
        if (typeof $.fn.select2 !== 'undefined') {
            $('#bu, #zone, #region, #territory').select2({
                width: '100%',
                theme: 'bootstrap-5'
            });
        }

        // BU change event
        $('#bu').change(function() {
            const buId = $(this).val();
            getZoneByBU(buId);
        });

        // Zone change event
        $('#zone').change(function() {
            const zoneId = $(this).val();
            getRegionByZone(zoneId);
        });

        // Region change event
        $('#region').change(function() {
            const regionId = $(this).val();
            getTerritoryByRegion(regionId);
        });

        // Load full cascade chain if you need to pre-populate based on URL parameters
        function loadFullCascade(bu, zone, region, territory) {
            getZoneByBU(bu, function() {
                // After zones loaded, select the zone
                if (zone && zone !== 'All') {
                    $('#zone').val(zone);
                    updateSelect2('#zone');
                    $('#zone').trigger('change');
                    
                    // Load regions for this zone
                    getRegionByZone(zone, function() {
                        // After regions loaded, select the region
                        if (region && region !== 'All') {
                            $('#region').val(region);
                            updateSelect2('#region');
                            $('#region').trigger('change');
                            
                            // Load territories for this region
                            getTerritoryByRegion(region, function() {
                                // After territories loaded, select the territory
                                if (territory && territory !== 'All') {
                                    setTimeout(() => {
                                        $('#territory').val(territory);
                                        updateSelect2('#territory');
                                        $('#territory').trigger('change');
                                        isInitializingFromUrl = false;
                                    }, 300);
                                } else {
                                    isInitializingFromUrl = false;
                                }
                            });
                        } else {
                            isInitializingFromUrl = false;
                        }
                    });
                } else {
                    isInitializingFromUrl = false;
                }
            });
        }

        // Initialize from URL parameters on page load
        const urlParams = new URLSearchParams(window.location.search);
        const bu = urlParams.get('bu');
        const zone = urlParams.get('zone');
        const region = urlParams.get('region');
        const territory = urlParams.get('territory');
        
        if (bu || zone || region || territory) {
            isInitializingFromUrl = true;
            loadFullCascade(bu || 'All', zone || 'All', region || 'All', territory || 'All');
        }
    });
</script>
@endpush

@push('styles')
<style>
.page-title { font-size: 16px !important; font-weight: 600; }
.form-control-sm { font-size: 11px; height: 28px; }
.btn-sm { font-size: 11px; padding: 4px 8px; }
.table th, .table td { padding: 3px 4px; font-size: 9px; }
.badge { font-size: 8px; }
.form-label { font-size: 10px; font-weight: 500; margin-bottom: 2px; }
.card-body { padding: 8px !important; }
.small { font-size: 9px !important; }
</style>
@endpush