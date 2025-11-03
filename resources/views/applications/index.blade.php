@extends('layouts.app')

@php
use App\Models\Status;
@endphp

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Page Title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Onboarding Applications</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">
                            @if($user_type === 'admin_approver') Admin & Pending Approvals
                            @elseif($user_type === 'admin') All Applications
                            @elseif($user_type === 'mis') MIS Applications
                            @elseif($user_type === 'approver') My Approvals & Applications
                            @else My Applications
                            @endif
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Compact Filters Form -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body py-2">
                <form method="GET" action="{{ route('applications.index') }}" class="row g-2 align-items-center" id="filterForm">
                    
                    <!-- BU Filter -->
                    @if ($userCapabilities['access_level'] == 'bu' || $userCapabilities['access_level'] == 'all')
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                        <label for="bu" class="form-label small fw-semibold mb-1">BU</label>
                        <select name="bu" id="bu" class="form-select form-select-sm select2-filter">
                            @foreach ($bu_list as $key => $value)
                            <option value="{{ $key }}" {{ ($filters['bu'] ?? '') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Zone Filter -->
                    @if ($userCapabilities['access_level'] == 'bu' || $userCapabilities['access_level'] == 'zone' || $userCapabilities['access_level'] == 'all')
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                        <label for="zone" class="form-label small fw-semibold mb-1">Zone</label>
                        <select name="zone" id="zone" class="form-select form-select-sm select2-filter">
                            @foreach ($zone_list as $key => $value)
                            <option value="{{ $key }}" {{ ($filters['zone'] ?? '') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Region Filter -->
                    @if ($userCapabilities['access_level'] == 'bu' || $userCapabilities['access_level'] == 'zone' || $userCapabilities['access_level'] == 'region' || $userCapabilities['access_level'] == 'all')
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                        <label for="region" class="form-label small fw-semibold mb-1">Region</label>
                        <select name="region" id="region" class="form-select form-select-sm select2-filter">
                            @foreach ($region_list as $key => $value)
                            <option value="{{ $key }}" {{ ($filters['region'] ?? '') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Territory Filter -->
                    @if ($userCapabilities['access_level'] == 'bu' || $userCapabilities['access_level'] == 'zone' || $userCapabilities['access_level'] == 'region' || $userCapabilities['access_level'] == 'territory' || $userCapabilities['access_level'] == 'all')
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                        <label for="territory" class="form-label small fw-semibold mb-1">Territory</label>
                        <select name="territory" id="territory" class="form-select form-select-sm select2-filter">
                            @foreach ($territory_list as $key => $value)
                            <option value="{{ $key }}" {{ ($filters['territory'] ?? '') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Date filters -->
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                        <label class="form-label small fw-semibold mb-1">From Date</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] ?? '' }}">
                    </div>

                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                        <label class="form-label small fw-semibold mb-1">To Date</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] ?? '' }}">
                    </div>

                    <!-- Status filter -->
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                        <label class="form-label small fw-semibold mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm select2-filter" data-placeholder="All Statuses">
                            <option value="All" {{ $filters['status'] == 'All' ? 'selected' : '' }}>All Statuses</option>
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
                            </optgroup>
                        </select>
                    </div>

                    <!-- Action buttons -->
                    <div class="col-12 col-sm-auto mt-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="ri-filter-line me-1"></i>Apply
                            </button>
                            <a href="{{ route('applications.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="ri-refresh-line me-1"></i>Clear
                            </a>
                        </div>
                    </div>
                </form>
                <div id="filter-loader" class="d-none position-absolute top-50 start-50 translate-middle z-3">
                    <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
                        <span class="visually-hidden">Loading filters...</span>
                    </div>
                    <div class="mt-2 text-center">
                        <small class="text-muted">Loading...</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Rest of your content remains the same -->
    <!-- Role-Based Navigation -->
    @if(in_array($user_type, ['admin_approver', 'approver']))
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-2">
                    <ul class="nav nav-pills nav-justified" id="applicationsTab" role="tablist">
                        @if($has_pending_approvals)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-tab-pane" type="button" role="tab" aria-controls="pending-tab-pane" aria-selected="true">
                                <i class="ri-time-line me-1"></i>
                                Pending Approvals
                                <span class="badge bg-danger ms-1">{{ $pending_approvals->total() }}</span>
                            </button>
                        </li>
                        @endif

                        @if($user_type === 'admin_approver')
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ !$has_pending_approvals ? 'active' : '' }}" id="master-tab" data-bs-toggle="tab" data-bs-target="#master-tab-pane" type="button" role="tab" aria-controls="master-tab-pane" aria-selected="false">
                                <i class="ri-dashboard-line me-1"></i>
                                All Applications
                                <span class="badge bg-primary ms-1">{{ $all_applications->total() }}</span>
                            </button>
                        </li>
                        @elseif($user_type === 'approver')
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ !$has_pending_approvals ? 'active' : '' }}" id="my-apps-tab" data-bs-toggle="tab" data-bs-target="#my-apps-tab-pane" type="button" role="tab" aria-controls="my-apps-tab-pane" aria-selected="false">
                                <i class="ri-file-list-line me-1"></i>
                                My Applications
                                <span class="badge bg-info ms-1">{{ $my_applications->total() }}</span>
                            </button>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Table Section -->
    <div class="row">
        <div class="col-12">

            <!-- Admin Approver -->
            @if($user_type === 'admin_approver')
            <div class="tab-content" id="applicationsTabContent">
                @if($has_pending_approvals)
                <div class="tab-pane fade show active" id="pending-tab-pane" role="tabpanel" aria-labelledby="pending-tab" tabindex="0">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-time-line me-1"></i>
                                Pending Approvals
                                <span class="badge bg-danger ms-1">{{ $pending_approvals->total() }}</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @include('applications.partials.approver-table', ['applications' => $pending_approvals])
                        </div>
                    </div>
                </div>
                @endif

                <div class="tab-pane fade {{ !$has_pending_approvals ? 'show active' : '' }}" id="master-tab-pane" role="tabpanel" aria-labelledby="master-tab" tabindex="0">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-dashboard-line me-1"></i>
                                All Applications
                                <span class="badge bg-primary ms-1">{{ $all_applications->total() }}</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @include('applications.partials.admin-table', ['applications' => $all_applications])
                        </div>
                    </div>
                </div>
            </div>

            <!-- Regular Admin -->
            @elseif($user_type === 'admin')
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-dashboard-line me-1"></i>
                        All Applications
                        <span class="badge bg-primary ms-1">{{ $all_applications->total() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @include('applications.partials.admin-table', ['applications' => $all_applications])
                </div>
            </div>

            <!-- MIS User -->
            @elseif($user_type === 'mis')
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-file-text-line me-1"></i>
                        MIS Applications
                        <span class="badge bg-warning ms-1">{{ $mis_applications->total() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @include('applications.partials.mis-table', ['applications' => $mis_applications])
                </div>
            </div>

            <!-- Approver -->
            @elseif($user_type === 'approver')
            <div class="tab-content" id="applicationsTabContent">
                @if($has_pending_approvals)
                <div class="tab-pane fade show active" id="pending-tab-pane" role="tabpanel" aria-labelledby="pending-tab" tabindex="0">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-time-line me-1"></i>
                                Pending Approvals
                                <span class="badge bg-danger ms-1">{{ $pending_approvals->total() }}</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @include('applications.partials.approver-table', ['applications' => $pending_approvals])
                        </div>
                    </div>
                </div>
                @endif

                <div class="tab-pane fade {{ !$has_pending_approvals ? 'show active' : '' }}" id="my-apps-tab-pane" role="tabpanel" aria-labelledby="my-apps-tab" tabindex="0">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-file-list-line me-1"></i>
                                My Applications
                                <span class="badge bg-info ms-1">{{ $my_applications->total() }}</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            @include('applications.partials.sales-table', ['applications' => $my_applications])
                        </div>
                    </div>
                </div>
            </div>

            <!-- Creator (Sales Person) -->
            @else
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-file-list-line me-1"></i>
                        My Applications
                        <span class="badge bg-info ms-1">{{ $my_applications->total() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @include('applications.partials.sales-table', ['applications' => $my_applications])
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- Shared Modals --}}
    @include('applications.partials.modals')
</div>
@endsection

@push('styles')

<style>
    /* Compact filter styles */
    .card-body.py-2 {
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
    }

    .form-label.small {
        font-size: 0.75rem;
        margin-bottom: 0.25rem;
    }

    .form-control-sm,
    .form-select-sm {
        font-size: 0.775rem;
        height: calc(1.8em + 0.5rem + 2px);
    }

    .btn-sm {
        font-size: 0.775rem;
        padding: 0.25rem 0.5rem;
    }

    /* Select2 small size */
    
    /* Ensure all filter elements align properly */
    .row.g-2.align-items-center {
        align-items: end !important;
    }

    /* Table styles remain the same */
    .table-responsive {
        position: relative;
        overflow-x: auto;
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

    .table-dropdown-menu.table-dropdown-menu-end {
        right: auto;
        left: auto;
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

    .timeline-row {
        display: none;
        background-color: #f8f9fa;
        border-top: none;
    }

    .timeline-row td {
        padding: 0.5rem;
    }

    .toggle-timeline {
        cursor: pointer;
        background: none;
        border: none;
        color: #6c757d;
        font-size: 1.1rem;
        padding: 4px;
    }

    .toggle-timeline:hover {
        color: #007bff;
    }

    .app-id-with-toggle {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .table-sm th,
    .table-sm td {
        vertical-align: middle;
        font-size: 0.85rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        console.log('DOM loaded - initializing application features...');
        initializeSelect2();
        
        // Initialize all features first
        initializeAllFeatures();
        
        // Then initialize filter state (this will handle URL parameters)
        setTimeout(() => {
            initializeFilterState();
        }, 500);
        
        setupTabReinitialization();
    });

    // Add this variable to track if we're initializing from URL
    let isInitializingFromUrl = false;

    // Function to update Select2 on a specific select element
    function updateSelect2(selectId) {
        const $select = $(selectId);
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }
        $select.select2({
            width: '100%',
            dropdownAutoWidth: true,
            placeholder: function() {
                return $select.data('placeholder') || 'Select...';
            },
            allowClear: false,
            minimumResultsForSearch: 5
        });
        // $select.next('.select2-container').find('.select2-selection').addClass('form-select-sm');
    }

    // Initialize filter state from URL parameters
    function initializeFilterState() {
        console.log('Initializing filter state from URL parameters...');
        isInitializingFromUrl = true;
        
        const urlParams = new URLSearchParams(window.location.search);
        const bu = urlParams.get('bu');
        const zone = urlParams.get('zone');
        const region = urlParams.get('region');
        const territory = urlParams.get('territory');
        
        console.log('URL params:', { bu, zone, region, territory });
        
        // If we have specific filters in URL, load the cascade
        if (bu && bu !== 'All' && territory && territory !== 'All') {
            console.log('Loading cascade for territory:', territory);
            
            // Load the full cascade chain
            loadFullCascade(bu, zone, region, territory);
        } else {
            isInitializingFromUrl = false;
        }
    }

    // Load the full cascade chain
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

    // Initialize Select2 for filter dropdowns
    function initializeSelect2() {
        console.log('Initializing Select2 for filters...');

        $('.select2-filter').select2({
            width: '100%',
            dropdownAutoWidth: true,
            placeholder: function() {
                return $(this).data('placeholder') || 'Select...';
            },
            allowClear: false,
            minimumResultsForSearch: 5
        });

        // Adjust Select2 container width for small form controls
        // $('.select2-filter').next('.select2-container').find('.select2-selection').addClass('form-select-sm');

        console.log('Select2 initialized');
    }

    // Reinitialize when tab becomes visible or on page load
    function setupTabReinitialization() {
        console.log('Setting up tab reinitialization...');

        // Reinitialize when tab becomes visible
        $(document).on('visibilitychange', function() {
            if (!document.hidden) {
                console.log('Tab became visible - reinitializing features...');
                setTimeout(() => {
                    initializeAllFeatures();
                }, 100);
            }
        });

        // Reinitialize on tab change
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
            console.log('Tab changed - reinitializing features...');
            setTimeout(() => {
                initializeAllFeatures();
                initializeSelect2(); // Reinitialize Select2 on tab change
            }, 300);
        });
    }

    // Helper functions (same as before)
    function closeAllTableDropdowns() {
        $('.table-dropdown-menu').removeClass('show').css('display', 'none');
        $('.table-dropdown-btn').attr('aria-expanded', 'false');
        console.log('All table dropdowns closed');
    }

    function showToast(type, message, title = '') {
        const toastContainer = $('.toast-container').length ? $('.toast-container') : createToastContainer();

        const toast = $(`
            <div class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${title ? `<strong>${title}:</strong> ` : ''}${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);

        toastContainer.append(toast);

        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();

        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }

    function createToastContainer() {
        const container = $('<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999"></div>');
        $('body').append(container);
        return container;
    }

    function cleanupAllModals() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css({
            'overflow': '',
            'padding-right': ''
        });
        $('.modal').hide().removeClass('show');

        showToast('success', 'All modals cleaned up!', 'Cleanup Complete');
    }

    // Main initialization function
    function initializeAllFeatures() {
        console.log('Initializing all application features...');
        initializeTableDropdowns();
        initializeTimelineToggles();
        initializeActionButtons();
        initializeConfirmDistributor();
        initializeAutoCloseAlerts();
        initializeEmergencyFix();
        initializeModalListeners();
        initializeCascadingFilters(); 
        console.log('All application features initialized');
    }

    // Enhanced table dropdowns using jQuery approach
    function initializeTableDropdowns() {
        console.log('Initializing table dropdowns...');

        // Set up CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Remove existing event handlers first to prevent duplicates
        $(document).off('click', '.table-dropdown-btn');
        $(document).off('click', '.view-doc-btn');
        $(document).off('click', '.view-physical-doc-btn');
        $(document).off('click', '.confirm-distributor-btn');
        $(document).off('click', '.mis-action-btn');
        $(document).off('click', '.toggle-timeline');

        // Table dropdown button click handler
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
            }
        });

        // Close dropdowns when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.table-dropdown-container').length &&
                !$(e.target).closest('#page-topbar').length) {
                closeAllTableDropdowns();
            }
        });

        // Prevent dropdown close when clicking inside dropdown items
        $(document).on('click', '.dropdown-item', function(e) {
            e.stopPropagation();
        });

        console.log('Table dropdowns initialized');
    }

    // Timeline toggles
    function initializeTimelineToggles() {
        console.log('Initializing timeline toggles...');

        $(document).on('click', '.toggle-timeline', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeAllTableDropdowns();

            var $button = $(this);
            var applicationId = $button.data('application-id');
            console.log('Timeline toggle clicked for application:', applicationId);

            var $table = $button.closest('table');
            var $timelineRow = $('#timeline-' + applicationId);
            var $icon = $button.find('i');

            if ($timelineRow.length === 0) {
                console.error('Timeline row not found for application:', applicationId);
                return;
            }

            if ($timelineRow.is(':visible')) {
                // Close this timeline
                $timelineRow.hide();
                $icon.removeClass('ri-indeterminate-circle-line').addClass('ri-add-circle-line');
                $button.attr('title', 'Show Approval Timeline').removeClass('active');
                console.log('Timeline closed for:', applicationId);
            } else {
                // Close all other timelines in this table first
                $table.find('.timeline-row').hide();
                $table.find('.toggle-timeline').each(function() {
                    $(this).find('i').removeClass('ri-indeterminate-circle-line').addClass('ri-add-circle-line');
                    $(this).attr('title', 'Show Approval Timeline').removeClass('active');
                });

                // Open this timeline
                $timelineRow.show();
                $icon.removeClass('ri-add-circle-line').addClass('ri-indeterminate-circle-line');
                $button.attr('title', 'Hide Approval Timeline').addClass('active');
                console.log('Timeline opened for:', applicationId);
            }
        });

        console.log('Timeline toggles initialized');
    }

    // Action buttons for modals
    function initializeActionButtons() {
        console.log('Initializing action buttons...');

        // Document verification modal
        $(document).on('click', '.view-doc-btn', function(e) {
            e.preventDefault();
            closeAllTableDropdowns();

            var url = $(this).data('url') || $(this).attr('href');
            console.log('Opening document verification modal for URL:', url);

            if (url && url !== 'javascript:void(0);') {
                $('#doc-verification-content').load(url, function(response, status, xhr) {
                    if (status === "error") {
                        console.error('Error loading document verification content');
                        $('#doc-verification-content').html('<div class="alert alert-danger">Error loading content</div>');
                    }
                });
                $('#docVerificationModal').modal('show');
            }
        });

        // Physical document verification modal
        $(document).on('click', '.view-physical-doc-btn', function(e) {
            e.preventDefault();
            closeAllTableDropdowns();

            var url = $(this).data('url') || $(this).attr('href');
            console.log('Opening physical document verification modal for URL:', url);

            if (url && url !== 'javascript:void(0);') {
                $('#physical-doc-verification-content').load(url, function(response, status, xhr) {
                    if (status === "error") {
                        console.error('Error loading physical document verification content');
                        $('#physical-doc-verification-content').html('<div class="alert alert-danger">Error loading content</div>');
                    }
                });
                $('#physicalDocVerificationModal').modal('show');
            }
        });

        // MIS action buttons
         $(document).on('click', '.mis-action-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeAllTableDropdowns();
                const url = $(this).attr('href');
                const applicationId = $(this).data('application-id');
                const distributorName = $(this).data('distributor-name');
                
                console.log('MIS action clicked:', url, 'for application:', applicationId, 'Distributor:', distributorName);
                
                // Redirect to the MIS action page
                if (url) {
                    window.location.href = url;
                }
            });

        console.log('Action buttons initialized');
    }

    // Confirm distributor functionality
    function initializeConfirmDistributor() {
        console.log('Initializing confirm distributor functionality...');

        let isConfirmSubmitting = false;

        // Remove existing handlers
        $(document).off('click', '.confirm-distributor-btn');
        $(document).off('submit', '#confirm-distributor-form');

        // Confirm distributor button click
        $(document).on('click', '.confirm-distributor-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeAllTableDropdowns();

            var $button = $(this);
            var url = $button.data('url');
            var applicationId = $button.data('application-id');
            var distributorName = $button.data('distributor-name');
            var currentStatus = $button.data('status');

            // Quick check - if already confirmed, don't open modal
            if (currentStatus === 'distributorship_created') {
                showToast('info', 'This distributor has already been confirmed.', 'Info');
                return;
            }

            // Set the application details
            $('#confirm-application-id').text(applicationId);
            $('#confirm-distributor-name').text(distributorName);

            // Reset and initialize the form
            var $form = $('#confirm-distributor-form');
            $form[0].reset();
            $form.removeClass('was-validated');

            // Set today's date as default for appointment date
            var today = new Date().toISOString().split('T')[0];
            $('#date-of-appointment').val(today);

            // Store the submission URL in the form
            $form.data('url', url);

            // Show the modal
            $('#confirmDistributorModal').modal('show');
        });

        // Form submission
        $(document).on('submit', '#confirm-distributor-form', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (isConfirmSubmitting) {
                return;
            }

            var $form = $(this);
            var url = $form.data('url');

            if (!$form[0].checkValidity()) {
                $form.addClass('was-validated');
                return;
            }

            isConfirmSubmitting = true;
            var formData = new FormData($form[0]);
            var $submitBtn = $('#confirm-distributor-submit');

            console.log('Submitting form data:', Object.fromEntries(formData));
            console.log('URL:', url);

            $submitBtn.prop('disabled', true);
            $submitBtn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Processing...');

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    console.log('Success response:', data);
                    if (data.success) {
                        showToast('success', data.message, 'Success');
                        $('#confirmDistributorModal').modal('hide');

                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showToast('error', data.message, 'Error');
                        $submitBtn.prop('disabled', false);
                        $submitBtn.html('Confirm Distributor');
                        isConfirmSubmitting = false;
                    }
                },
                error: function(xhr, status, error) {
                    console.log('Error response:', xhr);

                    // Silent handling for 403 errors (already confirmed)
                    if (xhr.status === 403) {
                        $('#confirmDistributorModal').modal('hide');
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                        isConfirmSubmitting = false;
                        return;
                    }

                    var errorMessage = 'An error occurred while processing your request.';

                    try {
                        var errorData = JSON.parse(xhr.responseText);
                        if (errorData.errors) {
                            var errors = errorData.errors;
                            errorMessage = Object.values(errors).flat().join('<br>');
                            $form.addClass('was-validated');
                        } else if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (e) {
                        if (xhr.status === 500) {
                            errorMessage = 'Internal server error. Please try again later.';
                        }
                    }

                    showToast('error', errorMessage, 'Error');
                    $submitBtn.prop('disabled', false);
                    $submitBtn.html('Confirm Distributor');
                    isConfirmSubmitting = false;
                }
            });
        });

        // Reset form when modal is hidden
        $('#confirmDistributorModal').on('hidden.bs.modal', function() {
            isConfirmSubmitting = false;
            var $form = $('#confirm-distributor-form');
            $form[0].reset();
            $form.removeClass('was-validated');
            var $submitBtn = $('#confirm-distributor-submit');
            $submitBtn.prop('disabled', false);
            $submitBtn.html('Confirm Distributor');

            // Clear validation styles
            $('#date-of-appointment, #distributor-code').removeClass('is-valid is-invalid');
        });

        console.log('Confirm distributor functionality initialized');
    }

    // Auto-close alerts
    function initializeAutoCloseAlerts() {
        console.log('Initializing auto-close alerts...');

        setTimeout(() => {
            $('.alert:not(.alert-permanent)').fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Emergency fix for stuck modals
    function initializeEmergencyFix() {
        console.log('Initializing emergency fix...');

        // Create emergency button if it doesn't exist
        if ($('#emergencyModalFix').length === 0) {
            $('body').append(`
                <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 99999">
                    <button id="emergencyModalFix" class="btn btn-warning btn-sm shadow" title="Fix stuck modals" style="display: none;">
                        <i class="ri-refresh-line me-1"></i> Fix Modals
                    </button>
                </div>
            `);
        }

        $('#emergencyModalFix').off('click').on('click', function() {
            cleanupAllModals();
            $(this).hide();
        });

        function checkForStuckModal() {
            var hasBackdrop = $('.modal-backdrop').length > 0;
            var hasModalOpen = $('body').hasClass('modal-open');
            var hasVisibleModal = $('.modal.show').length > 0;

            if ((hasBackdrop || hasModalOpen) && !hasVisibleModal) {
                $('#emergencyModalFix').show();
            } else {
                $('#emergencyModalFix').hide();
            }
        }
        setInterval(checkForStuckModal, 1000);
        $(document).on('shown.bs.modal hidden.bs.modal', checkForStuckModal);
        console.log('Emergency fix initialized');
    }


    // Add the modal listeners function
    function initializeModalListeners() {
        console.log('Initializing modal listeners for approval actions...');

        $(document).off('click', '.take-action-btn');
        $(document).off('change', '#actionType');
        $(document).off('submit', '#action-form');
        $(document).off('hidden.bs.modal', '#actionModal');

        // Take action button click
        $(document).on('click', '.take-action-btn', function() {
            closeAllTableDropdowns();

            const applicationId = $(this).data('application-id');
            const distributorName = $(this).data('distributor-name') || 'N/A';
            const submissionDate = $(this).data('submission-date') || 'N/A';
            const initiator = $(this).data('initiator') || 'N/A';
            const status = $(this).data('status') || '';

            console.log('Opening action modal for application:', applicationId);

            $('#modal-distributor-name').text(distributorName);
            $('#modal-submission-date').text(submissionDate);
            $('#modal-initiator').text(initiator);
            $('#application_id').val(applicationId);

            // Set the form action based on current status
            const baseUrl = '{{ url("approvals") }}';
            if (status === 'reverted') {
                $('#action-form').attr('action', `${baseUrl}/${applicationId}/edit`);
            } else {
                $('#action-form').attr('action', `${baseUrl}/${applicationId}/approve`);
            }

            $('#actionType').val('');
            $('#remarks').val('');
            $('#modal-action-date').val(new Date().toISOString().split('T')[0]);
            $('#followUpSection').addClass('d-none');
            $('#follow_up_date').val('').prop('required', false);

            // Check if action is allowed for current status
            const nonActionableStatuses = ['distributorship_created', 'rejected', 'agreement_created', 'document_verified', 'documents_received', 'mis_processing', 'completed'];
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

        // Action type change handler
        $(document).on('change', '#actionType', function() {
            const action = $(this).val();
            const applicationId = $('#application_id').val();

            if (action && applicationId) {
                const baseUrl = '{{ url("approvals") }}';
                const url = `${baseUrl}/${applicationId}/${action}`;
                $('#action-form').attr('action', url);
            } else {
                $('#action-form').attr('action', '');
            }

            // Show/hide follow-up date for hold action
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

        // Form submission handler
        $(document).on('submit', '#action-form', function(e) {
            e.preventDefault();
            const form = $(this);
            const action = $('#actionType').val();
            const remarks = $('#remarks').val().trim();
            const followUpDate = $('#follow_up_date').val();
            const submitBtn = $('#action-submit-btn');
            const spinner = submitBtn.find('.spinner-border');
            const submitText = submitBtn.find('.submit-text');

            // Validation
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

                    // Reload the page after successful action
                    setTimeout(() => {
                        window.location.reload();
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

        // Modal hidden event
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

    // Helper function to get action title
    function getActionTitle(action) {
        const titles = {
            'approve': 'Application Approved',
            'reject': 'Application Rejected',
            'revert': 'Application Reverted',
            'hold': 'Application On Hold'
        };
        return titles[action] || 'Action Completed';
    }

    // Add these variables at the top of your script
    let isUpdating = false;
    let loader = $('#filter-loader'); // Make sure you have this element or create it

    // Initialize cascading filters
    function initializeCascadingFilters() {
        console.log('Initializing cascading filters...');

        // Modified cascade handlers with initialization check
        $("#region").on("change", function() {
            if (isInitializingFromUrl) return;
            
            var region = $(this).val();
            getTerritoryByRegion(region);
        });

        $("#zone").on("change", function() {
            if (isInitializingFromUrl) return;
            
            var zone = $(this).val();
            getRegionByZone(zone);
        });

        $("#bu").on("change", function() {
            if (isInitializingFromUrl) return;
            
            var bu = $(this).val();
            getZoneByBU(bu);
        });
    }

    // Update your cascade functions to accept callbacks
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

    // Export functions for global access
    window.reinitializeApplicationFeatures = initializeAllFeatures;
    window.setupTabReinitialization = setupTabReinitialization;
    window.cleanupAllModals = cleanupAllModals;
    window.closeAllTableDropdowns = closeAllTableDropdowns;
    console.log('All application features initialization complete');
</script>
@endpush