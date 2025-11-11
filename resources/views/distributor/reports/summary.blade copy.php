@extends('layouts.app')

@section('title', 'Distributor Summary Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="page-title mb-0" style="font-size: 16px;">
                    {{ ucwords(str_replace('-', ' ', $reportType ?? 'summary')) }} Report
                </h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-2">
                    <!-- Compact Filters -->
                    <form method="GET" class="mb-2">
                        <input type="hidden" name="report_type" value="{{ $reportType }}">
                        
                        <div class="row g-1 align-items-end">
                            <!-- Search -->
                            <div class="col-md-2">
                                <label class="form-label small mb-0">Search</label>
                                <input type="text" name="search" class="form-control form-control-sm" 
                                       placeholder="Code or Name" 
                                       value="{{ request('search') }}">
                            </div>
                            
                            <!-- Business Unit -->
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

                            <!-- Zone -->
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

                            <!-- Region -->
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

                            <!-- Territory -->
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

                            <!-- Status -->
                            <div class="col-md-2">
                                <label class="form-label small mb-0">Status</label>
                                <select name="status" class="form-control form-control-sm">
                                    <option value="All">All Status</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="sales_approval" {{ request('status') == 'sales_approval' ? 'selected' : '' }}>Sales Approval</option>
                                    <option value="mis_verification" {{ request('status') == 'mis_verification' ? 'selected' : '' }}>MIS Verification</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->status_name }}" {{ request('status') == $status->status_name ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $status->status_name)) }}
                                        </option>
                                    @endforeach
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
                                <a href="{{ route('applications.distributor-summary', ['report_type' => $reportType]) }}" 
                                   class="btn btn-secondary btn-sm mt-3">
                                    <i class="ri-refresh-line me-1"></i> Reset
                                </a>
                                
                                <!-- Export Button -->
                                <a href="{{ route('applications.distributor-summary', array_merge(request()->all(), ['export' => 'excel'])) }}" 
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
                    <div class="table-responsive" style="font-size: 11px;">
                        <table class="table table-bordered table-striped table-sm mb-1">
                            <thead class="small">
                                <tr>
                                    <th>App Code</th>
                                    <th>Dist. Code</th>
                                    <th>Establishment</th>
                                    <th>Authorized Person</th>
                                    <th>Vertical</th>
                                    <th>Appointment Date</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created Date</th>
                                    @if($reportType == 'approval')
                                        <th>Approver</th>
                                        <th>Level</th>
                                    @endif
                                    @if($reportType == 'verification')
                                        <th>Doc Status</th>
                                        <th>Physical Docs</th>
                                    @endif
                                    @if($reportType == 'tat')
                                        <th>TAT Days</th>
                                        <th>TAT Status</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($distributors as $distributor)
                                <tr>
                                    <td>{{ $distributor->application_code ?? 'N/A' }}</td>
                                    <td>{{ $distributor->distributor_code ?? 'N/A' }}</td>
                                    <td>{{ $distributor->entityDetails->establishment_name ?? 'N/A' }}</td>
                                    <td>{{ $distributor->getAuthorizedOrEntityName() ?? 'N/A' }}</td>
                                    <td>{{ $distributor->vertical?->vertical_name ?? 'N/A' }}</td>
                                    <td>{{ $distributor->date_of_appointment?->format('d-m-Y') ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ \App\Helpers\Helpers::getStatusBadgeColor($distributor->status) }}" style="font-size: 9px;">
                                            {{ ucfirst(str_replace('_', ' ', $distributor->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $distributor->createdBy?->emp_name ?? 'N/A' }}</td>
                                    <td>{{ $distributor->created_at?->format('d-m-Y') ?? 'N/A' }}</td>
                                    @if($reportType == 'approval')
                                        <td>{{ $distributor->currentApprover?->emp_name ?? 'N/A' }}</td>
                                        <td>{{ ucfirst($distributor->approval_level) }}</td>
                                    @endif
                                    @if($reportType == 'verification')
                                        <td>{{ $distributor->doc_verification_status ?? 'N/A' }}</td>
                                        <td>{{ $distributor->physical_docs_status ?? 'N/A' }}</td>
                                    @endif
                                    @if($reportType == 'tat')
                                        @php
                                            $endDate = $distributor->status === 'distributorship_created' ? 
                                                ($distributor->physicalDispatch?->dispatch_date ?? $distributor->updated_at) : 
                                                now();
                                            $tatDays = $distributor->created_at->diffInDays($endDate);
                                            
                                            if ($tatDays <= 7) {
                                                $tatStatus = 'On Time';
                                                $badgeColor = 'success';
                                            } elseif ($tatDays <= 14) {
                                                $tatStatus = 'Delayed';
                                                $badgeColor = 'warning';
                                            } else {
                                                $tatStatus = 'Overdue';
                                                $badgeColor = 'danger';
                                            }
                                        @endphp
                                        <td>{{ $tatDays }}</td>
                                        <td>
                                            <span class="badge bg-{{ $badgeColor }}" style="font-size: 9px;">
                                                {{ $tatStatus }}
                                            </span>
                                        </td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ 
                                        $reportType == 'approval' ? 11 : 
                                        ($reportType == 'verification' ? 11 : 
                                        ($reportType == 'tat' ? 11 : 9)) 
                                    }}" class="text-center py-2">No data found</td>
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

<!-- Add JavaScript for cascade filtering using your existing functions -->
<script>
// Global variables needed for your functions
let isInitializingFromUrl = false;
let isUpdating = false;
const loader = $('#loader'); // You might need to add a loader element or adjust this

// Your existing functions
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

// Update Select2 function (if you're using Select2)
function updateSelect2(selector) {
    if ($(selector).hasClass('select2-hidden-accessible')) {
        $(selector).select2('destroy');
    }
    $(selector).select2({
        width: '100%',
        theme: 'bootstrap-5'
    });
}

// Initialize cascade filtering
$(document).ready(function() {
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

    // Initialize Select2 if needed
    if (typeof $.fn.select2 !== 'undefined') {
        $('#bu, #zone, #region, #territory').select2({
            width: '100%',
            theme: 'bootstrap-5'
        });
    }
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

// If you need to initialize from URL parameters on page load
$(document).ready(function() {
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

<style>
.page-title { font-size: 16px !important; font-weight: 600; }
.form-control-sm { font-size: 11px; height: 28px; }
.btn-sm { font-size: 11px; padding: 4px 8px; }
.table th, .table td { padding: 4px 6px; font-size: 11px; }
.badge { font-size: 9px; }
.form-label { font-size: 10px; font-weight: 500; margin-bottom: 2px; }
.card-body { padding: 8px !important; }
.small { font-size: 10px !important; }
</style>
@endsection