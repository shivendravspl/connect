@php
use App\Models\Status;
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
                                       placeholder="Code or Name" 
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
                    <div class="table-responsive" style="font-size: 11px;">
                        <table class="table table-bordered table-striped table-sm mb-1">
                            <thead class="small">
                                <tr>
                                    <th>App Code</th>
                                    <th>Focus Code</th>
                                    <th>Establishment</th>
                                    <th>Authorized Person</th>
                                    <th>Vertical</th>
                                    <th>App Date</th>
                                    <th>Appointment Date</th>
                                    <th>Status</th>
                                    <th>RBM TAT</th>
                                    <th>GM TAT</th>
                                    <th>SE TAT</th>
                                    <th>MIS TAT</th>
                                    <th>Physical TAT</th>
                                    <th>Total TAT</th>
                                    <th>TAT Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($distributors as $distributor)
                                <tr>
                                    <td>{{ $distributor->application_code ?? 'N/A' }}</td>
                                    <td>{{ $distributor->distributor_code ?? 'N/A' }}</td>
                                    <td>{{ $distributor->establishment_name ?? 'N/A' }}</td>
                                    <td>{{ $distributor->getAuthorizedOrEntityName() ?? 'N/A' }}</td>
                                    <td>{{ $distributor->vertical?->vertical_name ?? 'N/A' }}</td>
                                    <td>{{ $distributor->created_at?->format('d-m-Y') ?? 'N/A' }}</td>
                                    <td>
                                        @if($distributor->date_of_appointment)
                                            {{ \Carbon\Carbon::parse($distributor->date_of_appointment)->format('d-m-Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ \App\Helpers\Helpers::getStatusBadgeColor($distributor->status) }}" style="font-size: 9px;">
                                            {{ ucfirst(str_replace('_', ' ', $distributor->status)) }}
                                        </span>
                                    </td>

                                    <!-- TAT Columns -->
                                    <td>
                                        @php
                                        $rbmLog = $distributor->approvalLogs->where('role', 'Regional Business Manager')->first();
                                        $rbmTat = $rbmLog ? ceil($distributor->created_at->diffInDays($rbmLog->created_at)) : 'Pending';
                                        $rbmTat = is_numeric($rbmTat) ? $rbmTat . ($rbmTat != 1 ? '' : '') : $rbmTat;
                                        @endphp
                                        {{ $rbmTat }}
                                    </td>
                                    <td>
                                        @php
                                        $gmLog = $distributor->approvalLogs->where('role', 'General Manager')->first();
                                        if ($gmLog) {
                                            $prev = $distributor->approvalLogs->where('created_at', '<', $gmLog->created_at)->sortByDesc('created_at')->first();
                                            $gmTat = $prev ? ceil($prev->created_at->diffInDays($gmLog->created_at)) : 'N/A';
                                        } else {
                                            $gmTat = 'Pending';
                                        }
                                        $gmTat = is_numeric($gmTat) ? $gmTat . ($gmTat != 1 ? '' : '') : $gmTat;
                                        @endphp
                                        {{ $gmTat }}
                                    </td>
                                    <td>
                                        @php
                                        $seLog = $distributor->approvalLogs->where('role', 'Senior Executive')->first();
                                        if ($seLog) {
                                            $prev = $distributor->approvalLogs->where('created_at', '<', $seLog->created_at)->sortByDesc('created_at')->first();
                                            $seTat = $prev ? ceil($prev->created_at->diffInDays($seLog->created_at)) : 'N/A';
                                        } else {
                                            $seTat = 'Pending';
                                        }
                                        $seTat = is_numeric($seTat) ? $seTat . ($seTat != 1 ? '' : '') : $seTat;
                                        @endphp
                                        {{ $seTat }}
                                    </td>
                                    <td>
                                        @if($distributor->mis_verified_at)
                                        @php
                                        $finalApp = $distributor->approvalLogs->where('action', 'approved')->last();
                                        $misTat = $finalApp ? ceil($finalApp->created_at->diffInDays($distributor->mis_verified_at)) : 'N/A';
                                        $misTat = is_numeric($misTat) ? $misTat . ($misTat != 1 ? '' : '') : $misTat;
                                        @endphp
                                        {{ $misTat }}
                                        @else
                                        Pending
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                        $dispatch = $distributor->physicalDispatch;
                                        if ($dispatch && $dispatch->dispatch_date && $distributor->mis_verified_at) {
                                            $physTat = ceil($distributor->mis_verified_at->diffInDays($dispatch->dispatch_date));
                                            $physTat = $physTat . ($physTat != 1 ? '' : '');
                                        } else {
                                            $physTat = 'Pending';
                                        }
                                        @endphp
                                        {{ $physTat }}
                                    </td>
                                    <td>
                                        @php
                                        $endDate = null;
                                        if (in_array($distributor->status, ['completed', 'distributorship_created'])) {
                                            $endDate = $distributor->physicalDispatch?->dispatch_date ?? $distributor->updated_at;
                                        } elseif ($distributor->mis_verified_at) {
                                            $endDate = $distributor->mis_verified_at;
                                        } elseif ($distributor->approvalLogs->isNotEmpty()) {
                                            $endDate = $distributor->approvalLogs->last()->created_at;
                                        } else {
                                            $endDate = now();
                                        }
                                        $totalTatDays = ceil($distributor->created_at->diffInDays($endDate));
                                        $totalTat = $totalTatDays . ($totalTatDays != 1 ? '' : '');
                                        @endphp
                                        {{ $totalTat }}
                                    </td>
                                    <td>
                                        @php
                                        $tatDays = ceil($distributor->created_at->diffInDays($endDate ?? now()));

                                        if ($tatDays <= 7) {
                                            $status='On Time' ;
                                            $badgeColor='success' ;
                                        } elseif ($tatDays <=14) {
                                            $status='Delayed' ;
                                            $badgeColor='warning' ;
                                        } else {
                                            $status='Overdue' ;
                                            $badgeColor='danger' ;
                                        }
                                        @endphp

                                        <span class="badge bg-{{ $badgeColor }}" style="font-size: 9px;">
                                            {{ $status }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="15" class="text-center py-2">No data found</td>
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

<!-- Add JavaScript for cascade filtering using your existing functions -->
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
.table th, .table td { padding: 4px 6px; font-size: 11px; }
.badge { font-size: 9px; }
.form-label { font-size: 10px; font-weight: 500; margin-bottom: 2px; }
.card-body { padding: 8px !important; }
.small { font-size: 10px !important; }
</style>
@endpush