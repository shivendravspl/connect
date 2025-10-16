@extends('layouts.app')

@push('styles')
<style>
    /* General site-wide responsive adjustments */
    body {
        font-size: 0.85rem;
    }

    /* Card padding and margin for compactness */
    .card {
        margin-bottom: 0.5rem;
        border-radius: 0.2rem;
    }

    .card-body {
        padding: 0.5rem;
    }

    /* Filter section adjustments */
    .form-label {
        font-size: 0.65rem;
        margin-bottom: 0.1rem;
    }

    .form-select-sm,
    .form-control-sm,
    .btn-sm {
        font-size: 0.65rem;
        padding: 0.15rem 0.3rem;
        height: 1.6rem;
    }

    .btn.btn-sm {
        font-size: 0.65rem;
        padding: 0.2rem 0.4rem;
    }

    /* KPI Cards - Made clickable with hover effects */
    .kpi-card {
        border-left: 2px solid #007bff;
        transition: transform 0.2s;
        background-color: #ffffff;
        box-shadow: 0 0.1rem 0.2rem rgba(0, 0, 0, 0.05);
        height: 100%;
        display: flex;
        flex-direction: column;
        cursor: pointer; /* Default cursor for non-clickable */
    }

    .kpi-card.clickable {
        cursor: pointer;
    }

    .kpi-card.clickable:hover {
        transform: translateY(-1px);
        box-shadow: 0 0.2rem 0.4rem rgba(0, 0, 0, 0.1);
        background-color: #f8f9fa;
    }

    .kpi-card .card-body {
        padding: 0.4rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex-grow: 1;
    }

    .kpi-card h6.small {
        font-size: 0.7rem;
        margin-bottom: 0.1rem;
        color: #6c757d;
        line-height: 1.1;
    }

    .kpi-value {
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: 0.1rem;
        flex-grow: 1;
        display: flex;
        align-items: center;
    }

    .kpi-trend-up,
    .kpi-trend-down,
    .kpi-trend-neutral {
        font-size: 0.6rem;
        line-height: 1;
        display: block;
        margin-top: auto;
        text-align: right;
        font-weight: 600;
    }

    .kpi-trend-up {
        color: #28a745;
    }

    .kpi-trend-down {
        color: #dc3545;
    }

    .kpi-trend-neutral {
        color: #6c757d;
    }

    /* Tables (General and TAT) */
    .tat-table th,
    .tat-table td,
    .compact-table th,
    .compact-table td {
        font-size: 0.6rem;
        padding: 0.3rem;
        vertical-align: middle;
    }

    .badge {
        font-size: 0.6rem;
        padding: 0.2rem 0.4rem;
    }

    .no-data-message {
        font-size: 0.7rem;
        color: #6c757d;
        text-align: center;
        padding: 0.5rem;
    }

    /* Loader Styles */
    .loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        opacity: 1;
        transition: opacity 0.3s ease-in-out;
    }

    .loader-overlay.d-none {
        opacity: 0;
        pointer-events: none;
    }

    .loader-overlay .spinner-border {
        width: 2.5rem;
        height: 2.5rem;
    }

    /* Mobile-Specific Overrides */
    @media (max-width: 576px) {
        .container-fluid {
            padding-left: 0.2rem;
            padding-right: 0.2rem;
        }

        .col-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }

        .header-title {
            font-size: 0.75rem;
            margin-bottom: 0.3rem;
        }

        .form-select-sm,
        .form-control-sm,
        .btn-sm {
            font-size: 0.55rem;
            padding: 0.1rem 0.2rem;
            height: 1.4rem;
        }

        .kpi-card .card-body {
            padding: 0.2rem;
        }

        .kpi-card h6.small {
            font-size: 0.6rem;
        }

        .kpi-value {
            font-size: 0.8rem;
        }

        .kpi-trend-up,
        .kpi-trend-down,
        .kpi-trend-neutral {
            font-size: 0.5rem;
        }

        .tat-table th,
        .tat-table td,
        .compact-table th,
        .compact-table td {
            font-size: 0.55rem;
            padding: 0.15rem;
        }

        .badge {
            font-size: 0.5rem;
            padding: 0.1rem 0.2rem;
        }

        .loader-overlay .spinner-border {
            width: 1.5rem;
            height: 1.5rem;
        }
    }

    /* Tablets and horizontal phones */
    @media (min-width: 577px) and (max-width: 768px) {
        .col-sm-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }

        .kpi-card .card-body {
            padding: 0.3rem;
        }

        .kpi-value {
            font-size: 0.9rem;
        }

        .kpi-card h6.small {
            font-size: 0.65rem;
        }

        .form-select-sm,
        .form-control-sm,
        .btn-sm {
            font-size: 0.6rem;
            height: 1.5rem;
        }

        .tat-table th,
        .tat-table td,
        .compact-table th,
        .compact-table td {
            font-size: 0.6rem;
        }
    }

    /* Ensure filters fit in one line on large screens */
    @media (min-width: 992px) {
        .filter-col {
            flex: 0 0 10%;
            max-width: 10%;
        }
    }

    /* Enhanced toggle button styles */
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

    .toggle-timeline.active {
        color: #007bff;
    }

    /* Ensure timeline rows are properly styled */
    .timeline-row {
        display: none;
        background-color: #f8f9fa;
        border-top: none;
    }

    .timeline-row td {
        padding: 0.5rem;
    }

    /* Compact table styles for pending */
    .compact-table th, .compact-table td {
        padding: 0.4rem 0.6rem;
        font-size: 0.75rem;
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

    /* Modal styles */
    .modal-content {
        border-radius: 0.5rem;
        box-shadow: var(--card-shadow);
    }

    .modal-body .card {
        background-color: var(--bg-light);
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

    .form-label {
        font-size: 0.75rem;
        font-weight: 500;
    }

    .form-control, .form-select {
        font-size: 0.8rem;
        padding: 0.3rem 0.5rem;
    }

    .modal-footer .btn {
        font-size: 0.8rem;
        padding: 0.3rem 0.75rem;
    }

    /* Responsive adjustments */
    @media (max-width: 576px) {
        .compact-table th, .compact-table td {
            padding: 0.3rem 0.4rem;
            font-size: 0.65rem;
        }

        .compact-table .btn-sm {
            padding: 0.15rem 0.3rem;
            font-size: 0.65rem;
        }

        .modal-body .card-body {
            padding: 0.5rem;
        }

        .modal-body .card-header {
            font-size: 0.75rem;
        }
    }
     .chart-card {
      border: 1px solid #dce3f0;
      border-radius: 10px;
      padding: 15px;
      background: #fff;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      width: 100%;
      max-width: 500px;
      margin: 30px auto;
    }
    .chart-title {
      font-weight: 600;
      font-size: 16px;
      margin-bottom: 10px;
      text-align: center;
    }
       #formsChart1 {
  height: 320px !important; /* or 200px */
}
 #formsChart4 {
  height: 320px !important; /* or 200px */
}
</style>
@endpush

@section('content')
<div id="elmLoader" class="loader-overlay d-none">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

@php
    $isAdminUser = Auth::check() && (Auth::user()->hasAnyRole(['Super Admin', 'Admin']) || Auth::user()->hasPermissionTo('distributor_approval'));
    $isMisUser = Auth::check() && Auth::user()->hasAnyRole(['Mis Admin', 'Mis User']);
    $approverDesignations = ['Area Coordinator', 'Regional Business Manager', 'Zonal Business Manager', 'General Manager']; // Adjust as needed
    $employee = Auth::user()->employee;
    $isApprover = !$isAdminUser && !$isMisUser && $employee && in_array($employee->emp_designation ?? '', $approverDesignations);
    $showAdminDashboard = $isAdminUser;
    $showMisDashboard = $isMisUser;
    $showApproverDashboard = $isApprover;
    $showSalesDashboard = Auth::check() && !$showAdminDashboard && !$showMisDashboard && !$showApproverDashboard;
    $showKpiCards = $showAdminDashboard || $showMisDashboard || $showSalesDashboard || $showApproverDashboard;
    $viewMode = $filters['view_mode'] ?? 'pending';
@endphp

@if($showAdminDashboard || $showMisDashboard || $showApproverDashboard || $showSalesDashboard)
    {{-- Unified Filters Section --}}
    <div class="row mb-1">
        <div class="col-12">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1 header-title">
                        @if($showMisDashboard) MIS @elseif($showAdminDashboard) Approval @elseif($showApproverDashboard) Approver’s @elseif($showSalesDashboard) My @endif Dashboard Management
                    </h4>

                    <div class="flex-shrink-0">
                        <a href="{{ url('applications/create') }}" class="btn btn-sm bg-primary text-white">
                            Appoint Distributor Form
                        </a>
                        <a class="ms-3" href="">Guidance Manual <i class="ri-file-unknow-line"></i></a>
                          <button type="button" class="btn btn-soft-info btn-sm ms-2" id="showDocumentChecklistBtn">
                            <i class="ri-attachment-line me-1"></i> List of Required Documents
                        </button>
                        <button type="button" class="btn btn-soft-primary material-shadow-none btn-sm" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                            <i class="ri-filter-2-line"></i> Filters
                        </button>
                    </div>
                </div>
                <div class="card-body p-1">
                    <form id="{{ $showMisDashboard ? 'mis-filter-form' : ($showApproverDashboard ? 'approver-filter-form' : ($showSalesDashboard ? 'sales-filter-form' : 'filter-form')) }}" method="GET">
                        <div class="collapse show" id="filterCollapse">
                            <div class="row g-1">
                                {{-- Common filters: BU, Zone, Region, Territory - Always show --}}
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                    <label for="bu" class="form-label">BU</label>
                                    <select name="bu" id="bu" class="form-select form-select-sm">
                                        <option value="All">All BU</option>
                                        @foreach ($bu_list as $key => $value)
                                            <option value="{{ $key }}" {{ $filters['bu'] == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                    <label for="zone" class="form-label">Zone</label>
                                    <select name="zone" id="zone" class="form-select form-select-sm">
                                        <option value="All">All Zone</option>
                                        @foreach ($zone_list as $key => $value)
                                            <option value="{{ $key }}" {{ $filters['zone'] == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                    <label for="region" class="form-label">Region</label>
                                    <select name="region" id="region" class="form-select form-select-sm">
                                        <option value="All">All Region</option>
                                        @foreach ($region_list as $key => $value)
                                            <option value="{{ $key }}" {{ $filters['region'] == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                    <label for="territory" class="form-label">Territory</label>
                                    <select name="territory" id="territory" class="form-select form-select-sm">
                                        <option value="All">All Territory</option>
                                        @foreach ($territory_list as $key => $value)
                                            <option value="{{ $key }}" {{ $filters['territory'] == $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Date Range - Common --}}
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                    <label class="form-label">From</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] }}">
                                </div>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                    <label class="form-label">To</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] }}">
                                </div>

                                @if($showAdminDashboard)
                                    <input type="hidden" name="view_mode" id="view_mode" value="{{ $viewMode }}">
                                @endif
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col mt-1 d-flex gap-1 mt-lg-4">
                                    <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards Section --}}
    @if($showKpiCards)
        @if($showAdminDashboard || $showMisDashboard || $showSalesDashboard)
            {{-- Existing KPI Cards for Admin/MIS/Sales --}}
            <div class="row mb-3">
                <div class="col-12">
                    <div class="crm-widget">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1 header-title">Key Indicators</h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="row g-1" id="kpi-container">
                                @if (isset($data['counts']['total']) && $data['counts']['total'] == 0 && !$showSalesDashboard)
                                    <div class="col-12 no-data-message">No applications found based on current filters.</div>
                                @else
                                    @php
                                        $queryString = http_build_query($filters);
                                        $applicationsUrl = route('applications.index') . ($queryString ? '?' . $queryString : '');
                                    @endphp
                                    @if($showAdminDashboard)
                                        {{-- Admin KPIs --}}
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <a href="#" class="text-decoration-none kpi-link" data-kpi="total">
                                                <div class="card kpi-card clickable" data-kpi="total" data-count="{{ $data['counts']['total'] }}">
                                                    <div class="card-body">
                                                        <h6 class="small">Total Forms</h6>
                                                        <div class="kpi-value" id="kpi-total-submitted">{{ $data['counts']['total'] }}</div>
                                                        <span class="kpi-trend-up" id="kpi-trend-total-submitted">🔼 {{ $data['kpi_trends']['total_submitted'] ?? 0 }}%</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <a href="#" class="text-decoration-none kpi-link" data-kpi="appointments">
                                                <div class="card kpi-card clickable" data-kpi="appointments" data-count="{{ $data['counts']['distributorship_created'] }}">
                                                    <div class="card-body">
                                                        <h6 class="small">Appointments</h6>
                                                        <div class="kpi-value" id="kpi-appointments-completed">{{ $data['counts']['distributorship_created'] ?? 0 }}%</div>
                                                        <span class="kpi-trend-up" id="kpi-trend-appointments-completed">🔼 {{ $data['kpi_trends']['appointments_completed'] ?? 0 }}%</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <a href="#" class="text-decoration-none kpi-link" data-kpi="in_process_admin">
                                                <div class="card kpi-card clickable" data-kpi="in_process_admin" data-count="{{ $data['counts']['in_process'] }}">
                                                    <div class="card-body">
                                                        <h6 class="small">In Process</h6>
                                                        <div class="kpi-value" id="kpi-in-process">{{ $data['counts']['in_process'] ?? 0 }}</div>
                                                        <span class="kpi-trend-neutral" id="kpi-trend-in-process">—</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <a href="#" class="text-decoration-none kpi-link" data-kpi="reverted">
                                                <div class="card kpi-card clickable" data-kpi="reverted" data-count="{{ $data['counts']['reverted'] }}">
                                                    <div class="card-body">
                                                        <h6 class="small">Reverted</h6>
                                                        <div class="kpi-value" id="kpi-reverted">{{ $data['counts']['reverted'] ?? 0 }}</div>
                                                        <span class="kpi-trend-up" id="kpi-trend-reverted">🔼 {{ $data['kpi_trends']['reverted'] ?? 0 }}</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <a href="#" class="text-decoration-none kpi-link" data-kpi="rejected_admin">
                                                <div class="card kpi-card clickable" data-kpi="rejected_admin" data-count="{{ $data['counts']['rejected'] }}">
                                                    <div class="card-body">
                                                        <h6 class="small">Rejected</h6>
                                                        <div class="kpi-value" id="kpi-rejected">{{ $data['counts']['rejected'] ?? 0 }}</div>
                                                        <span class="kpi-trend-down" id="kpi-trend-rejected">🔽 {{ $data['kpi_trends']['rejected'] ?? 0 }}</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <a href="#" class="text-decoration-none kpi-link" data-kpi="to_mis_admin">
                                                <div class="card kpi-card clickable" data-kpi="to_mis_admin" data-count="{{ $data['counts']['forwarded_to_mis'] }}">
                                                    <div class="card-body">
                                                        <h6 class="small">To MIS</h6>
                                                        <div class="kpi-value" id="kpi-forwarded-to-mis">{{ $data['counts']['forwarded_to_mis'] ?? 0 }}</div>
                                                        <span class="kpi-trend-up" id="kpi-trend-forwarded-to-mis">🔼 {{ $data['kpi_trends']['forwarded_to_mis'] ?? 0 }}%</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @elseif($showSalesDashboard)
                                        {{-- Sales KPIs --}}
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <a href="#" class="text-decoration-none kpi-link" data-kpi="total_created">
                                                <div class="card kpi-card clickable" data-kpi="total_created" data-count="{{ $data['sales_counts']['total_created'] ?? 0 }}">
                                                    <div class="card-body">
                                                        <h6 class="small">Total Created</h6>
                                                        <div class="kpi-value" id="kpi-total-created">{{ $data['sales_counts']['total_created'] ?? 0 }}</div>
                                                        <span class="kpi-trend-up" id="kpi-trend-total-created">🔼 {{ $data['sales_kpi_trends']['total_created'] ?? 0 }}%</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <a href="#" class="text-decoration-none kpi-link" data-kpi="in_approval">
                                                <div class="card kpi-card clickable" data-kpi="in_approval" data-count="{{ $data['sales_counts']['in_approval'] ?? 0 }}">
                                                    <div class="card-body">
                                                        <h6 class="small">In Approval</h6>
                                                        <div class="kpi-value" id="kpi-in-approval">{{ $data['sales_counts']['in_approval'] ?? 0 }}</div>
                                                        <span class="kpi-trend-neutral" id="kpi-trend-in-approval">—</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <a href="#" class="text-decoration-none kpi-link" data-kpi="to_mis">
                                                <div class="card kpi-card clickable" data-kpi="to_mis" data-count="{{ $data['sales_counts']['to_mis'] ?? 0 }}">
                                                    <div class="card-body">
                                                        <h6 class="small">In MIS</h6>
                                                        <div class="kpi-value" id="kpi-to-mis">{{ $data['sales_counts']['to_mis'] ?? 0 }}</div>
                                                        <span class="kpi-trend-up" id="kpi-trend-to-mis">🔼 {{ $data['sales_kpi_trends']['to_mis'] ?? 0 }}%</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <a href="#" class="text-decoration-none kpi-link" data-kpi="completed">
                                                <div class="card kpi-card clickable" data-kpi="completed" data-count="{{ $data['sales_counts']['completed'] ?? 0 }}">
                                                    <div class="card-body">
                                                        <h6 class="small">Completed</h6>
                                                        <div class="kpi-value" id="kpi-completed">{{ $data['sales_counts']['completed'] ?? 0 }}</div>
                                                        <span class="kpi-trend-up" id="kpi-trend-completed">🔼 {{ $data['sales_kpi_trends']['completed'] ?? 0 }}%</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <a href="#" class="text-decoration-none kpi-link" data-kpi="rejected">
                                                <div class="card kpi-card clickable" data-kpi="rejected" data-count="{{ $data['sales_counts']['rejected'] ?? 0 }}">
                                                    <div class="card-body">
                                                        <h6 class="small">Rejected</h6>
                                                        <div class="kpi-value" id="kpi-rejected">{{ $data['sales_counts']['rejected'] ?? 0 }}</div>
                                                        <span class="kpi-trend-down" id="kpi-trend-rejected">🔽 {{ $data['sales_kpi_trends']['rejected'] ?? 0 }}%</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @else
                                        {{-- MIS KPIs --}}
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <a href="#" class="text-decoration-none kpi-link" data-kpi="total">
                                                <div class="card kpi-card clickable" data-kpi="total" data-count="{{ $data['counts']['total'] }}">
                                                    <div class="card-body">
                                                        <h6 class="small">Total Forms</h6>
                                                        <div class="kpi-value" id="kpi-total-submitted">{{ $data['counts']['total'] ?? 0 }}</div>
                                                        <span class="kpi-trend-up" id="kpi-trend-total-submitted">🔼 {{ $data['kpi_trends']['total_submitted'] ?? 0 }}%</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        {{--<div class="col-6 col-md-3 col-lg-2">
                                            <a href="#" class="text-decoration-none kpi-link" data-kpi="document_verified">
                                                <div class="card kpi-card clickable" data-kpi="document_verified" data-count="{{ $data['counts']['document_verified'] ?? 0 }}">
                                                    <div class="card-body">
                                                        <h6 class="small">Docs Verified</h6>
                                                        <div class="kpi-value" id="kpi-document-verified">{{ $data['counts']['document_verified'] ?? 0 }}</div>
                                                        <span class="kpi-trend-up" id="kpi-trend-document-verified">🔼 {{ $data['kpi_trends']['document_verified'] ?? 0 }}%</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>--}}
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <a href="#" class="text-decoration-none kpi-link" data-kpi="agreement_created">
                                                <div class="card kpi-card clickable" data-kpi="agreement_created" data-count="{{ $data['counts']['agreement_created'] ?? 0 }}">
                                                    <div class="card-body">
                                                        <h6 class="small">Agreements</h6>
                                                        <div class="kpi-value" id="kpi-agreement-created">{{ $data['counts']['agreement_created'] ?? 0 }}</div>
                                                        <span class="kpi-trend-up" id="kpi-trend-agreement-created">🔼 {{ $data['kpi_trends']['agreement_created'] ?? 0 }}%</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <a href="#" class="text-decoration-none kpi-link" data-kpi="physical_docs_verified">
                                                <div class="card kpi-card clickable" data-kpi="physical_docs_verified" data-count="{{ $data['counts']['physical_docs_verified'] ?? 0 }}">
                                                    <div class="card-body">
                                                        <h6 class="small">Physical Docs</h6>
                                                        <div class="kpi-value" id="kpi-physical-docs-verified">{{ $data['counts']['physical_docs_verified'] ?? 0 }}</div>
                                                        <span class="kpi-trend-up" id="kpi-trend-physical-docs-verified">🔼 {{ $data['kpi_trends']['physical_docs_verified'] ?? 0 }}%</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <a href="#" class="text-decoration-none kpi-link" data-kpi="distributorship_created">
                                                <div class="card kpi-card clickable" data-kpi="distributorship_created" data-count="{{ $data['counts']['distributorship_created'] ?? 0 }}">
                                                    <div class="card-body">
                                                        <h6 class="small">Completed</h6>
                                                        <div class="kpi-value" id="kpi-distributors-created">{{ $data['counts']['distributorship_created'] ?? 0 }}</div>
                                                        <span class="kpi-trend-up" id="kpi-trend-distributors-created">🔼 {{ $data['kpi_trends']['distributorship_created'] ?? 0 }}%</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <a href="#" class="text-decoration-none kpi-link" data-kpi="mis_rejected">
                                                <div class="card kpi-card clickable" data-kpi="mis_rejected" data-count="{{ $data['counts']['mis_rejected'] ?? 0 }}">
                                                    <div class="card-body">
                                                        <h6 class="small">Rejected</h6>
                                                        <div class="kpi-value" id="kpi-mis-rejected">{{ $data['counts']['mis_rejected'] ?? 0 }}</div>
                                                        <span class="kpi-trend-down" id="kpi-trend-mis-rejected">🔽 {{ $data['kpi_trends']['mis_rejected'] ?? 0 }}%</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-6 col-md-3 col-lg-2">
                                            <a href="#" class="text-decoration-none kpi-link" data-kpi="in_process">
                                                <div class="card kpi-card clickable" data-kpi="in_process" data-count="{{ $data['counts']['in_process'] ?? 0 }}">
                                                    <div class="card-body">
                                                        <h6 class="small">In Process</h6>
                                                        <div class="kpi-value" id="kpi-in-process">{{ $data['counts']['in_process'] ?? 0 }}</div>
                                                        <span class="kpi-trend-neutral" id="kpi-trend-in-process">—</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endif
                                @endif {{-- end of data check --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($showApproverDashboard)
            {{-- Approver KPI Cards --}}
            <div class="row mb-3">
                <div class="col-12">
                    <div class="crm-widget">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1 header-title">Key Indicators</h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="row g-1" id="approver-kpi-container">
                                @php
                                    $queryString = http_build_query($filters);
                                    $approverUrl = route('approver.applications') . ($queryString ? '?' . $queryString : '');
                                @endphp
                                <div class="col-6 col-md-3 col-lg-2">
                                    <a href="#" class="text-decoration-none kpi-link" data-kpi="pending_your_approval">
                                        <div class="card kpi-card clickable" data-kpi="pending_your_approval" data-count="{{ $data['counts']['pending_your_approval'] }}">
                                            <div class="card-body">
                                                <h6 class="small">🔍 Forms Pending Your Approval</h6>
                                                <div class="kpi-value">{{ $data['counts']['pending_your_approval'] ?? 0 }}</div>
                                                <span class="kpi-trend-neutral">—</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <a href="#" class="text-decoration-none kpi-link" data-kpi="on_hold_by_you">
                                        <div class="card kpi-card clickable" data-kpi="on_hold_by_you" data-count="{{ $data['counts']['on_hold_by_you'] }}">
                                            <div class="card-body">
                                                <h6 class="small">🕓 On Hold by You</h6>
                                                <div class="kpi-value">{{ $data['counts']['on_hold_by_you'] ?? 0  }}</div>
                                                <span class="kpi-trend-neutral">—</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <a href="#" class="text-decoration-none kpi-link" data-kpi="approved_by_you">
                                        <div class="card kpi-card clickable" data-kpi="approved_by_you" data-count="{{ $data['counts']['approved_by_you'] }}">
                                            <div class="card-body">
                                                <h6 class="small">✅ Approved by You</h6>
                                                <div class="kpi-value">{{ $data['counts']['approved_by_you'] ?? 0 }}</div>
                                                <span class="kpi-trend-up">🔼 {{ $data['kpi_trends']['approved_by_you'] ?? 0 }}%</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <a href="#" class="text-decoration-none kpi-link" data-kpi="rejected_by_you">
                                        <div class="card kpi-card clickable" data-kpi="rejected_by_you" data-count="{{ $data['counts']['rejected_by_you'] }}">
                                            <div class="card-body">
                                                <h6 class="small">❌ Rejected by You</h6>
                                                <div class="kpi-value">{{ $data['counts']['rejected_by_you'] ?? 0 }}</div>
                                                <span class="kpi-trend-down">🔽 {{ $data['kpi_trends']['rejected_by_you'] ?? 0 }}%</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <a href="#" class="text-decoration-none kpi-link" data-kpi="reverted_by_you">
                                        <div class="card kpi-card clickable" data-kpi="reverted_by_you" data-count="{{ $data['counts']['reverted_by_you'] }}">
                                            <div class="card-body">
                                                <h6 class="small">🔁 Reverted by You</h6>
                                                <div class="kpi-value">{{ $data['counts']['reverted_by_you'] ?? 0 }}</div>
                                                <span class="kpi-trend-up">🔼 {{ $data['kpi_trends']['reverted_by_you'] ?? 0 }}%</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    {{-- Main Content --}}
    <div class="row mb-1">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($showAdminDashboard)
                        {{-- Admin Table --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="header-title mb-0" id="table-title">
                                @if($viewMode == 'all')
                                    All Applications (<span id="table-count">{{ $data['counts']['total'] ?? 0 }}</span>)
                                @else
                                    Pending Approvals (<span id="table-count">{{ $data['counts']['pending'] ?? 0 }}</span>)
                                @endif
                            </h5>
                        </div>
                        <div id="main-container">
                            @if($viewMode == 'all')
                                @if (isset($masterReportApplications) && $masterReportApplications->isEmpty())
                                    <div class="no-data-message">No applications found.</div>
                                @else
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
                                                            $approvalLog = $application->approvalLogs->where('action', 'approved')->sortByDesc('created_at')->first();
                                                        @endphp
                                                        {{ $approvalLog ? $approvalLog->created_at->format('d M Y') : 'N/A' }}
                                                    </td>
                                                    <td>
                                                        {{ in_array($application->status, explode(',', $statusGroups['completed']['slugs'] ?? '')) ? $application->updated_at->format('d M Y') : 'N/A' }}
                                                    </td>
                                                    <td>
                                                        {{ in_array($application->status, explode(',', $statusGroups['completed']['slugs'] ?? '')) ? $application->created_at->diffInDays($application->updated_at) : 'N/A' }}
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('approvals.show', $application->id) }}" class="btn btn-sm btn-primary" title="View">
                                                            <i class="ri-eye-line"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <tr class="timeline-row" id="timeline-{{ $application->id }}">
                                                    <td colspan="12" class="p-3">
                                                        @php
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
                                                                    'status' => in_array($application->status, ['draft', 'initiated']) ? 'pending' : (in_array($application->status, explode(',', $statusGroups['mis']['slugs'] ?? '')) ? 'approved' : 'not-started'),
                                                                    'date' => in_array($application->status, ['draft', 'initiated']) ? $application->created_at->format('d M Y') : (in_array($application->status, explode(',', $statusGroups['mis']['slugs'] ?? '')) ? $application->created_at->format('d M Y') : '-'),
                                                                    'remarks' => $approvalLevels['Draft/Initiated'] ? $approvalLevels['Draft/Initiated']->remarks : '-',
                                                                    'icon' => 'ri-draft-fill'
                                                                ],
                                                                [
                                                                    'label' => 'ABM',
                                                                    'log' => $approvalLevels['Area Coordinator'],
                                                                    'status' => $approvalLevels['Area Coordinator'] ? $approvalLevels['Area Coordinator']->action : (in_array($application->status, explode(',', $statusGroups['mis']['slugs'] ?? '')) || in_array($application->approval_level, ['Regional Business Manager', 'Zonal Business Manager', 'General Manager']) ? 'approved' : 'not-started'),
                                                                    'date' => $approvalLevels['Area Coordinator'] ? $approvalLevels['Area Coordinator']->created_at->format('d M Y') : '-',
                                                                    'remarks' => $approvalLevels['Area Coordinator'] ? $approvalLevels['Area Coordinator']->remarks : '-',
                                                                    'icon' => 'ri-user-2-fill'
                                                                ],
                                                                [
                                                                    'label' => 'RBM',
                                                                    'log' => $approvalLevels['Regional Business Manager'],
                                                                    'status' => $approvalLevels['Regional Business Manager'] ? $approvalLevels['Regional Business Manager']->action : (in_array($application->status, explode(',', $statusGroups['mis']['slugs'] ?? '')) || in_array($application->approval_level, ['Zonal Business Manager', 'General Manager']) ? 'approved' : 'not-started'),
                                                                    'date' => $approvalLevels['Regional Business Manager'] ? $approvalLevels['Regional Business Manager']->created_at->format('d M Y') : '-',
                                                                    'remarks' => $approvalLevels['Regional Business Manager'] ? $approvalLevels['Regional Business Manager']->remarks : '-',
                                                                    'icon' => 'ri-user-3-fill'
                                                                ],
                                                                [
                                                                    'label' => 'ZBM',
                                                                    'log' => $approvalLevels['Zonal Business Manager'],
                                                                    'status' => $approvalLevels['Zonal Business Manager'] ? $approvalLevels['Zonal Business Manager']->action : (in_array($application->status, explode(',', $statusGroups['mis']['slugs'] ?? '')) || $application->approval_level == 'General Manager' ? 'approved' : 'not-started'),
                                                                    'date' => $approvalLevels['Zonal Business Manager'] ? $approvalLevels['Zonal Business Manager']->created_at->format('d M Y') : '-',
                                                                    'remarks' => $approvalLevels['Zonal Business Manager'] ? $approvalLevels['Zonal Business Manager']->remarks : '-',
                                                                    'icon' => 'ri-user-4-fill'
                                                                ],
                                                                [
                                                                    'label' => 'GM',
                                                                    'log' => $approvalLevels['General Manager'],
                                                                    'status' => $approvalLevels['General Manager'] ? $approvalLevels['General Manager']->action : (in_array($application->status, explode(',', $statusGroups['mis']['slugs'] ?? '')) ? 'approved' : 'not-started'),
                                                                    'date' => $approvalLevels['General Manager'] ? $approvalLevels['General Manager']->created_at->format('d M Y') : '-',
                                                                    'remarks' => $approvalLevels['General Manager'] ? $approvalLevels['General Manager']->remarks : '-',
                                                                    'icon' => 'ri-user-5-fill'
                                                                ],
                                                                [
                                                                    'label' => 'MIS',
                                                                    'log' => $approvalLevels['MIS'],
                                                                    'status' => in_array($application->status, $misSlugs) ? 'pending' : (in_array($application->status, $completionSlugs) ? 'approved' : (in_array($application->status, $rejectionSlugs) ? 'rejected' : 'not-started')),
                                                                    'date' => in_array($application->status, array_merge($misSlugs, $completionSlugs, $rejectionSlugs)) ? $application->updated_at->format('d M Y') : '-',
                                                                    'remarks' => $approvalLevels['MIS'] ? $approvalLevels['MIS']->remarks : '-',
                                                                    'icon' => 'ri-file-text-fill'
                                                                ],
                                                                [
                                                                    'label' => 'Final',
                                                                    'log' => null,
                                                                    'status' => in_array($application->status, $completionSlugs) ? 'approved' : 'not-started',
                                                                    'date' => in_array($application->status, $completionSlugs) ? $application->updated_at->format('d M Y') : '-',
                                                                    'remarks' => '-',
                                                                    'icon' => 'ri-checkbox-circle-fill'
                                                                ]
                                                            ];
                                                        @endphp
                                                        <div class="timeline-container d-flex flex-row align-items-center justify-content-start p-3 overflow-x-auto overflow-y-hidden bg-light rounded">
                                                            @foreach($stages as $stage)
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
                                @endif
                            @else
                                @if (isset($pendingApplications) && $pendingApplications->isEmpty())
                                    <div class="no-data-message">No applications pending your approval.</div>
                                @else
                                    @include('dashboard._approver-table', ['pendingApplications' => $pendingApplications ?? collect()])
                                @endif
                            @endif
                        </div>
                    @elseif($showApproverDashboard)
                        {{-- Approver Table --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="header-title mb-0" id="approver-table-title">
                                📂 Pending Forms Assigned to You (<span id="approver-table-count">{{ $data['counts']['pending_your_approval'] ?? 0 }}</span>)
                            </h5>
                        </div>
                        <div id="approver-pending-container">
                            @if (isset($approverPendingApplications) && $approverPendingApplications->isEmpty())
                                <div class="no-data-message">No pending forms assigned to you.</div>
                            @else
                                @include('dashboard._approver-pending-table', ['approverPendingApplications' => $approverPendingApplications ?? collect()])
                            @endif
                        </div>
                    @elseif($showMisDashboard)
                        {{-- MIS Table --}}
                        <div id="mis-table-container">
                            @if (isset($misApplications) && $misApplications->isEmpty())
                                <div class="no-data-message">No applications found.</div>
                            @else
                                @include('dashboard._mis-table', ['misApplications' => $misApplications ?? collect()])
                            @endif
                        </div>
                    @elseif($showSalesDashboard)
                        {{-- Sales Table --}}
                        <div id="sales-table-container">
                            @if (isset($myApplications) && $myApplications->isEmpty())
                                <div class="no-data-message">No applications created by you.</div>
                            @else
                                @include('dashboard._sales-table', ['myApplications' => $myApplications ?? collect()])
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@else
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Welcome Home</div>
                    <div class="card-body">
                        <p>Now you are logged in!!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif


<!-- Document Checklist Canvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="documentChecklistCanvas" aria-labelledby="documentChecklistCanvasLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="documentChecklistCanvasLabel">
            <i class="ri-file-list-check-line me-2"></i> Checklist of Required Documents
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        {{-- Loading Spinner --}}
        <div id="checklistLoading" class="d-flex justify-content-center align-items-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        
        {{-- Content Area --}}
        <div id="checklistContent" class="d-none">
            {{-- Filters --}}
            <div class="p-3 border-bottom bg-light">
                <div class="row g-2">
                    <div class="col-12">
                        <label for="canvas_entity_type" class="form-label small fw-semibold">Entity Type</label>
                        <select name="entity_type" id="canvas_entity_type" class="form-select form-select-sm">
                            <option value="sole_proprietorship">Sole Proprietorship</option>
                            <option value="partnership">Partnership</option>
                            <option value="llp">LLP</option>
                            <option value="company" selected>Company</option>
                            <option value="cooperative_society">Cooperative Society</option>
                            <option value="trust">Trust</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Documents List --}}
            <div id="canvasDocumentsContainer" class="p-3">
                {{-- Documents will be loaded here via AJAX --}}
            </div>
        </div>
        
        {{-- Error State --}}
        <div id="checklistError" class="d-none text-center py-5">
            <i class="ri-error-warning-line display-4 text-muted"></i>
            <p class="text-muted mt-3">Unable to load document checklist. Please try again.</p>
            <button class="btn btn-sm btn-primary mt-2" onclick="loadDocumentChecklist()">Retry</button>
        </div>
    </div>
</div>
<!-- End Canvas -->

<!-- Toast Container for Notifications -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
    <div id="actionToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                <strong id="toast-title"></strong>
                <div id="toast-message"></div>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const loader = $("#elmLoader");
    let isUpdating = false;
    const isMisUser = {{ $showMisDashboard ? 'true' : 'false' }};
    const isSalesUser = {{ $showSalesDashboard ? 'true' : 'false' }};
    const isApproverUser = {{ $showApproverDashboard ? 'true' : 'false' }};
    const formId = isMisUser ? '#mis-filter-form' : (isApproverUser ? '#approver-filter-form' : (isSalesUser ? '#sales-filter-form' : '#filter-form'));
    const updateFunction = isMisUser ? updateMISDashboard : (isApproverUser ? updateApproverDashboard : (isSalesUser ? updateSalesDashboard : updateDashboard));
    const hasTabs = false; // No tabs anymore

    // Dynamic status data
    let statusGroups = @json($statusGroups ?? []);
    let kpiStatusMappings = @json($kpiStatusMappings ?? []);

    $(document).ready(function() {
        // Initialize Select2 for all filter dropdowns
        $(`${formId} select`).select2({
            width: '100%',
            minimumResultsForSearch: 10,
            placeholder: "Select an option",
            allowClear: true
        });

        // Handle filter changes with debounce
        let debounceTimer;
        $(`${formId} select, ${formId} input`).on('change', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                updateFunction();
            }, 300);
        });

        // Prevent form submission on Enter key
        $(`${formId}`).on('submit', function(e) {
            e.preventDefault();
            if (!isUpdating) updateFunction();
        });

        // Initial load
        updateFunction();

        // Cascade handlers (common for both)
        $("#region").on("change", function() {
            var region = $(this).val();
            getTerritoryByRegion(region);
        });

        $("#zone").on("change", function() {
            var zone = $(this).val();
            getRegionByZone(zone);
        });

        $("#bu").on("change", function() {
            var bu = $(this).val();
            getZoneByBU(bu);
        });

        // Additional cascade for approver filters if needed
        @if($showApproverDashboard)
        $("#initiated_by_role").on("change", function() {
            updateApproverDashboard();
        });
        $("#status").on("change", function() {
            updateApproverDashboard();
        });
        @endif

        // Expandable panel for tables
        $(document).on('click', '.expandable-panel', function() {
            $(this).next('.panel-content').toggle();
        });

        // Handle Select2 open event
        $('.select2-container').on('select2:open', function() {
            $('.select2-dropdown').css('z-index', 10000);
        });

        // Initialize modal, toast, and KPI clicks
        initializeModalListeners();
        initializeToast();
        initializeKpiClicks();
        initializeAllFeatures();
        initializeDocumentChecklist();
    });

    
    // Unified update function wrapper
    function unifiedUpdate(dashboardType = isMisUser ? 'mis' : (isApproverUser ? 'approver' : (isSalesUser ? 'sales' : 'regular'))) {
        if (isUpdating) return;
        isUpdating = true;
        let formData = $(formId).serialize();
        if (!isSalesUser && !isMisUser && !isApproverUser) {
            const viewMode = $('#view_mode').val() || 'pending';
            formData += '&view_mode=' + viewMode;
        }
        $.ajax({
            url: "{{ route('dashboard.dynamic-data') }}",
            type: 'GET',
            data: formData + (dashboardType === 'approver' ? '&dashboard_type=approver' : (dashboardType === 'mis' ? '&dashboard_type=mis' : (dashboardType === 'sales' ? '&dashboard_type=sales' : ''))),
            dataType: 'json',
            beforeSend: function() {
                loader.removeClass('d-none');
            },
            success: function(data) {
                loader.addClass('d-none');
                console.log(`Received ${dashboardType} data:`, data);

                if (!data) {
                    console.error('Invalid response structure:', data);
                    const containerId = isApproverUser ? '#approver-pending-container' : (isMisUser ? '#mis-table-container' : (isSalesUser ? '#sales-table-container' : '#main-container'));
                    $(containerId).html('<div class="col-12 no-data-message">Error: Incomplete data structure from server.</div>');
                    isUpdating = false;
                    return;
                }

                // Update dynamic status data if available
                if (data.statusGroups) {
                    statusGroups = data.statusGroups;
                }
                if (data.kpiStatusMappings) {
                    kpiStatusMappings = data.kpiStatusMappings;
                }

                // Update KPIs if applicable
                updateKpiContainer(data, dashboardType);

                if (isApproverUser) {
                    // Approver-specific update
                    if (data.approver_pending_table_html) {
                        $('#approver-pending-container').html(data.approver_pending_table_html);
                        setTimeout(initializeAllFeatures, 100);
                    }
                    // Update table count
                    $('#approver-table-count').text(data.counts.pending_your_approval || 0);
                    setTimeout(() => {
                        initializeTimelineToggles();
                    }, 100);
                } else if (isMisUser) {
                    // MIS-specific update
                    if (data.mis_table_html) {
                        $('#mis-table-container').html(data.mis_table_html);
                    }
                } else if (isSalesUser) {
                    // Sales update
                    let tableHtml = data.my_table_html || '<div class="no-data-message">No applications created by you.</div>';
                    const salesTotalCreated = data.sales_counts ? data.sales_counts.total_created || 0 : 0;
                    $('#sales-table-container').html('<h5 class="header-title mb-0">My Applications (<span id="sales-table-count">' + salesTotalCreated + '</span>)</h5>' + tableHtml);
                    // Update sales table count
                    $('#sales-table-count').text(salesTotalCreated);
                } else {
                    // Admin updates
                    let tableHtml;
                    const viewMode = $('#view_mode').val() || 'pending';
                    tableHtml = viewMode === 'all' ? (data.master_table_html || '<div class="no-data-message">No applications found.</div>') : (data.pending_table_html || '<div class="no-data-message">No applications pending your approval.</div>');
                    $('#main-container').html(tableHtml);
                    // Update table title and count
                    updateTableTitle($('#view_mode').val() || 'pending', data.counts);
                }

                // Re-initialize
                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                $('[data-bs-toggle="tooltip"]').tooltip();
                initializeModalListeners();
                initializeKpiClicks(); // Re-bind KPI clicks after update
                initializeAllFeatures();
                isUpdating = false;
            },
            error: function(xhr) {
                loader.addClass('d-none');
                console.error(`Error fetching ${dashboardType} data:`, xhr.responseText);
                const containerId = isApproverUser ? '#approver-pending-container' : (isMisUser ? '#mis-table-container' : (isSalesUser ? '#sales-table-container' : '#main-container'));
                $(containerId).html('<div class="col-12 no-data-message">Error loading data. Please try again.</div>');
                isUpdating = false;
            }
        });
    }

    function updateDashboard() {
        unifiedUpdate('regular');
    }

    function updateApproverDashboard() {
        unifiedUpdate('approver');
    }

    function updateMISDashboard() {
        unifiedUpdate('mis');
    }

    function updateSalesDashboard() {
        unifiedUpdate('sales');
    }

    // Update table title for admin
    function updateTableTitle(viewMode, counts) {
        let titleText = '';
        let count = 0;
        if (viewMode === 'all') {
            titleText = 'All Applications';
            count = counts.total || 0;
        } else {
            titleText = 'Pending Approvals';
            count = counts.pending || 0;
        }
        $('#table-title').html(`${titleText} (<span id="table-count">${count}</span>)`);
    }

    // Update KPI container
    function updateKpiContainer(data, dashboardType) {
        let kpiContainerId = 'kpi-container';
    if (dashboardType === 'approver') {
        kpiContainerId = 'approver-kpi-container';
    }
    const kpiContainer = $('#' + kpiContainerId);
    let counts = {};
    let trends = {};

    if (dashboardType === 'approver') {
        counts = data.counts || {};
        trends = data.kpi_trends || {};
        
        // FIX: Don't return early for approver - check if ALL counts are zero
        const hasAnyData = Object.values(counts).some(count => count > 0);
        if (!hasAnyData) {
            kpiContainer.html('<div class="col-12 no-data-message">No applications found based on current filters.</div>');
            return;
        }
    } else if (dashboardType === 'regular') {
        counts = data.counts || {};
        trends = data.kpi_trends || {};
        if (counts.total === 0) {
            kpiContainer.html('<div class="col-12 no-data-message">No applications found based on current filters.</div>');
            return;
        }
    } else if (dashboardType === 'mis') {
        counts = data.counts || {};
        trends = data.kpi_trends || {};
        if (counts.total === 0) {
            kpiContainer.html('<div class="col-12 no-data-message">No applications found based on current filters.</div>');
            return;
        }
    } else if (dashboardType === 'sales') {
        counts = data.sales_counts || data.counts || {};
        trends = data.sales_kpi_trends || data.kpi_trends || {};
        if (counts.total_created === 0) {
            kpiContainer.html('<div class="col-12 no-data-message">No applications found based on current filters.</div>');
            return;
        }
    }

        let kpiHtml = '';
        if (dashboardType === 'approver') {
        console.log('Rendering Approved KPI with value:', counts.approved_by_you);
        // Approver KPIs
        kpiHtml = `
            <div class="col-6 col-md-3 col-lg-2">
                <a href="#" class="text-decoration-none">
                    <div class="card kpi-card clickable" data-kpi="pending_your_approval" data-count="${counts.pending_your_approval || 0}">
                        <div class="card-body">
                            <h6 class="small">🔍 Forms Pending Your Approval</h6>
                            <div class="kpi-value">${counts.pending_your_approval || 0}</div>
                            <span class="kpi-trend-neutral">—</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="#" class="text-decoration-none">
                    <div class="card kpi-card clickable" data-kpi="on_hold_by_you" data-count="${counts.on_hold_by_you || 0}">
                        <div class="card-body">
                            <h6 class="small">🕓 On Hold by You</h6>
                            <div class="kpi-value">${counts.on_hold_by_you || 0}</div>
                            <span class="kpi-trend-neutral">—</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="#" class="text-decoration-none">
                    <div class="card kpi-card clickable" data-kpi="approved_by_you" data-count="${counts.approved_by_you || 0}">
                        <div class="card-body">
                            <h6 class="small">✅ Approved by You</h6>
                            <div class="kpi-value">${counts.approved_by_you || 0}</div>
                            <span class="kpi-trend-up">🔼 ${trends.approved_by_you || 0}%</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="#" class="text-decoration-none">
                    <div class="card kpi-card clickable" data-kpi="rejected_by_you" data-count="${counts.rejected_by_you || 0}">
                        <div class="card-body">
                            <h6 class="small">❌ Rejected by You</h6>
                            <div class="kpi-value">${counts.rejected_by_you || 0}</div>
                            <span class="kpi-trend-down">🔽 ${trends.rejected_by_you || 0}%</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="#" class="text-decoration-none">
                    <div class="card kpi-card clickable" data-kpi="reverted_by_you" data-count="${counts.reverted_by_you || 0}">
                        <div class="card-body">
                            <h6 class="small">🔁 Reverted by You</h6>
                            <div class="kpi-value">${counts.reverted_by_you || 0}</div>
                            <span class="kpi-trend-up">🔼 ${trends.reverted_by_you || 0}%</span>
                        </div>
                    </div>
                </a>
            </div>
        `;
    } else if (dashboardType === 'regular') {
             kpiHtml = `
            <div class="col-6 col-md-3 col-lg-2">
                <a href="#" class="text-decoration-none">
                    <div class="card kpi-card clickable" data-kpi="total" data-count="${counts.total || 0}">
                        <div class="card-body">
                            <h6 class="small">Total Forms</h6>
                            <div class="kpi-value" id="kpi-total-submitted">${counts.total || 0}</div>
                            <span class="kpi-trend-up" id="kpi-trend-total-submitted">🔼 ${trends.total_submitted || 0}%</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="#" class="text-decoration-none">
                    <div class="card kpi-card clickable" data-kpi="appointments" data-count="${counts.distributorship_created || 0}">
                        <div class="card-body">
                            <h6 class="small">Appointments</h6>
                            <div class="kpi-value" id="kpi-appointments-completed">${counts.distributorship_created || 0}</div>
                            <span class="kpi-trend-up" id="kpi-trend-appointments-completed">🔼 ${trends.appointments_completed || 0}%</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="#" class="text-decoration-none">
                    <div class="card kpi-card clickable" data-kpi="in_process" data-count="${counts.in_process || 0}">
                        <div class="card-body">
                            <h6 class="small">In Process</h6>
                            <div class="kpi-value" id="kpi-in-process">${counts.in_process || 0}</div>
                            <span class="kpi-trend-neutral" id="kpi-trend-in-process">—</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="#" class="text-decoration-none">
                    <div class="card kpi-card clickable" data-kpi="reverted" data-count="${counts.reverted || 0}">
                        <div class="card-body">
                            <h6 class="small">Reverted</h6>
                            <div class="kpi-value" id="kpi-reverted">${counts.reverted || 0}</div>
                            <span class="kpi-trend-up" id="kpi-trend-reverted">🔼 ${trends.reverted || 0}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="#" class="text-decoration-none">
                    <div class="card kpi-card clickable" data-kpi="rejected" data-count="${counts.rejected || 0}">
                        <div class="card-body">
                            <h6 class="small">Rejected</h6>
                            <div class="kpi-value" id="kpi-rejected">${counts.rejected || 0}</div>
                            <span class="kpi-trend-down" id="kpi-trend-rejected">🔽 ${trends.rejected || 0}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="#" class="text-decoration-none">
                    <div class="card kpi-card clickable" data-kpi="to_mis" data-count="${counts.forwarded_to_mis || 0}">
                        <div class="card-body">
                            <h6 class="small">To MIS</h6>
                            <div class="kpi-value" id="kpi-forwarded-to-mis">${counts.forwarded_to_mis || 0}</div>
                            <span class="kpi-trend-up" id="kpi-trend-forwarded-to-mis">🔼 ${trends.forwarded_to_mis || 0}%</span>
                        </div>
                    </div>
                </a>
            </div>
        `;
           
        } else if (dashboardType === 'sales') {
            // Sales KPIs
            kpiHtml = `
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="#" class="text-decoration-none">
                        <div class="card kpi-card clickable" data-kpi="total_created" data-count="${counts.total_created || 0}">
                            <div class="card-body">
                                <h6 class="small">Total Created</h6>
                                <div class="kpi-value" id="kpi-total-created">${counts.total_created || 0}</div>
                                <span class="kpi-trend-up" id="kpi-trend-total-created">🔼 ${trends.total_created || 0}%</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="#" class="text-decoration-none">
                        <div class="card kpi-card clickable" data-kpi="in_approval" data-count="${counts.in_approval || 0}">
                            <div class="card-body">
                                <h6 class="small">In Approval</h6>
                                <div class="kpi-value" id="kpi-in-approval">${counts.in_approval || 0}</div>
                                <span class="kpi-trend-neutral" id="kpi-trend-in-approval">—</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="#" class="text-decoration-none">
                        <div class="card kpi-card clickable" data-kpi="to_mis" data-count="${counts.to_mis || 0}">
                            <div class="card-body">
                                <h6 class="small">In MIS</h6>
                                <div class="kpi-value" id="kpi-to-mis">${counts.to_mis || 0}</div>
                                <span class="kpi-trend-up" id="kpi-trend-to-mis">🔼 ${trends.to_mis || 0}%</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="#" class="text-decoration-none">
                        <div class="card kpi-card clickable" data-kpi="completed" data-count="${counts.completed || 0}">
                            <div class="card-body">
                                <h6 class="small">Completed</h6>
                                <div class="kpi-value" id="kpi-completed">${counts.completed || 0}</div>
                                <span class="kpi-trend-up" id="kpi-trend-completed">🔼 ${trends.completed || 0}%</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="#" class="text-decoration-none">
                        <div class="card kpi-card clickable" data-kpi="rejected" data-count="${counts.rejected || 0}">
                            <div class="card-body">
                                <h6 class="small">Rejected</h6>
                                <div class="kpi-value" id="kpi-rejected">${counts.rejected || 0}</div>
                                <span class="kpi-trend-down" id="kpi-trend-rejected">🔽 ${trends.rejected || 0}%</span>
                            </div>
                        </div>
                    </a>
                </div>
            `;
        } else {
            // MIS KPIs
            kpiHtml = `
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="#" class="text-decoration-none">
                        <div class="card kpi-card clickable" data-kpi="total" data-count="${counts.total || 0}">
                            <div class="card-body">
                                <h6 class="small">Total Forms</h6>
                                <div class="kpi-value" id="kpi-total-submitted">${counts.total || 0}</div>
                                <span class="kpi-trend-up" id="kpi-trend-total-submitted">🔼 ${trends.total_submitted || 0}%</span>
                            </div>
                        </div>
                    </a>
                </div>
                <!--<div class="col-6 col-md-3 col-lg-2">
                    <a href="#" class="text-decoration-none">
                        <div class="card kpi-card clickable" data-kpi="document_verified" data-count="${counts.document_verified || 0}">
                            <div class="card-body">
                                <h6 class="small">Docs Verified</h6>
                                <div class="kpi-value" id="kpi-document-verified">${counts.document_verified || 0}</div>
                                <span class="kpi-trend-up" id="kpi-trend-document-verified">🔼 ${trends.document_verified || 0}%</span>
                            </div>
                        </div>
                    </a>
                </div>-->
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="#" class="text-decoration-none">
                        <div class="card kpi-card clickable" data-kpi="agreement_created" data-count="${counts.agreement_created || 0}">
                            <div class="card-body">
                                <h6 class="small">Agreements</h6>
                                <div class="kpi-value" id="kpi-agreement-created">${counts.agreement_created || 0}</div>
                                <span class="kpi-trend-up" id="kpi-trend-agreement-created">🔼 ${trends.agreement_created || 0}%</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="#" class="text-decoration-none">
                        <div class="card kpi-card clickable" data-kpi="physical_docs_verified" data-count="${counts.physical_docs_verified || 0}">
                            <div class="card-body">
                                <h6 class="small">Physical Docs</h6>
                                <div class="kpi-value" id="kpi-physical-docs-verified">${counts.physical_docs_verified || 0}</div>
                                <span class="kpi-trend-up" id="kpi-trend-physical-docs-verified">🔼 ${trends.physical_docs_verified || 0}%</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="#" class="text-decoration-none">
                        <div class="card kpi-card clickable" data-kpi="distributorship_created" data-count="${counts.distributorship_created || 0}">
                            <div class="card-body">
                                <h6 class="small">Completed</h6>
                                <div class="kpi-value" id="kpi-distributors-created">${counts.distributorship_created || 0}</div>
                                <span class="kpi-trend-up" id="kpi-trend-distributors-created">🔼 ${trends.distributorship_created || 0}%</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="#" class="text-decoration-none">
                        <div class="card kpi-card clickable" data-kpi="mis_rejected" data-count="${counts.mis_rejected || 0}">
                            <div class="card-body">
                                <h6 class="small">Rejected</h6>
                                <div class="kpi-value" id="kpi-mis-rejected">${counts.mis_rejected || 0}</div>
                                <span class="kpi-trend-down" id="kpi-trend-mis-rejected">🔽 ${trends.mis_rejected || 0}%</span>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-6 col-md-3 col-lg-2">
                    <a href="#" class="text-decoration-none">
                        <div class="card kpi-card clickable" data-kpi="in_process" data-count="${counts.in_process || 0}">
                            <div class="card-body">
                                <h6 class="small">In Process</h6>
                                <div class="kpi-value" id="kpi-in-process">${counts.in_process || 0}</div>
                                <span class="kpi-trend-neutral" id="kpi-trend-in-process">—</span>
                            </div>
                        </div>
                    </a>
                </div>
            `;
        }
        kpiContainer.html(kpiHtml);
        initializeKpiClicks();
    }

    // KPI Click Handlers - Enhanced for all user types including approver
    function initializeKpiClicks() {
        $('.kpi-card.clickable').off('click').on('click', function(e) {
            e.preventDefault();
            const kpi = $(this).data('kpi');
            const count = $(this).data('count');
            
            console.log('KPI clicked:', kpi, 'Count:', count, 'User type:', isApproverUser ? 'approver' : (isSalesUser ? 'sales' : (isMisUser ? 'mis' : 'admin')));
            
            // If count is 0, don't do anything
            if (count === 0) {
                console.log('Count is 0, skipping navigation');
                return;
            }

            // Get current filters
            const formData = $(formId).serialize();
            
            // Define KPI configurations for different user types
            const kpiConfigs = {
                // Approver-specific KPIs
                'pending_your_approval': {
                    approver: { status: statusGroups.pending ? statusGroups.pending.slugs : '', label: 'Forms Pending Your Approval' }
                },
                'on_hold_by_you': {
                    approver: { status: statusGroups.hold ? statusGroups.hold.slugs : '', label: 'On Hold by You' }
                },
                'approved_by_you': {
                    approver: { status: statusGroups.approved ? statusGroups.approved.slugs : '', label: 'Approved by You' }
                },
                'rejected_by_you': {
                    approver: { status: statusGroups.rejected ? statusGroups.rejected.slugs : '', label: 'Rejected by You' }
                },
                'reverted_by_you': {
                    approver: { status: statusGroups.reverted ? statusGroups.reverted.slugs : '', label: 'Reverted by You' }
                },
                // Sales user specific KPIs
                'total_created': {
                    sales: { status: '', label: 'Total Created' }
                },
                'in_approval': {
                    sales: { status: statusGroups.pending ? statusGroups.pending.slugs : '', label: 'In Approval' }
                },
                'to_mis': {
                    sales: { status: statusGroups.mis ? statusGroups.mis.slugs : '', label: 'In MIS' }
                },
                'completed': {
                    sales: { status: statusGroups.completed ? statusGroups.completed.slugs : '', label: 'Completed' }
                },
                'rejected': {
                    sales: { status: statusGroups.rejected ? statusGroups.rejected.slugs : '', label: 'Rejected' }
                },
                // MIS user specific KPIs
                'document_verified': {
                    mis: { status: kpiStatusMappings.document_verified || 'documents_verified', label: 'Docs Verified' }
                },
                'agreement_created': {
                    mis: { status: kpiStatusMappings.agreement_created || 'agreement_created', label: 'Agreements' }
                },
                'physical_docs_verified': {
                    mis: { status: kpiStatusMappings.physical_docs_verified || 'physical_docs_verified', label: 'Physical Docs' }
                },
                'distributorship_created': {
                    mis: { status: kpiStatusMappings.distributorship_created || 'distributorship_created', label: 'Completed' }
                },
                'mis_rejected': {
                    mis: { status: kpiStatusMappings.mis_rejected || statusGroups.rejected.slugs, label: 'Rejected' }
                },
                'in_process': {
                    mis: { status: kpiStatusMappings.in_process || statusGroups.mis.slugs, label: 'In Process' }
                },
                // Admin user specific KPIs
                'total': {
                    admin: { status: '', label: 'Total Forms' }
                },
                'appointments': {
                    admin: { status: statusGroups.completed ? statusGroups.completed.slugs : '', label: 'Appointments' }
                },
                'in_process_admin': {
                    admin: { status: statusGroups.pending ? statusGroups.pending.slugs : '', label: 'In Process' }
                },
                'reverted': {
                    admin: { status: statusGroups.reverted ? statusGroups.reverted.slugs : '', label: 'Reverted' }
                },
                'rejected_admin': {
                    admin: { status: statusGroups.rejected ? statusGroups.rejected.slugs : '', label: 'Rejected' }
                },
                'to_mis_admin': {
                    admin: { 
                        status: statusGroups.mis ? statusGroups.mis.slugs : '', 
                        label: 'To MIS' 
                    }
                },
                // Fallback mappings for inconsistent KPI names
                'in_process': {
                    admin: { status: statusGroups.mis ? statusGroups.mis.slugs : '', label: 'In Process' }
                },
                'to_mis': {
                    admin: { status: statusGroups.mis ? statusGroups.mis.slugs : '', label: 'To MIS' }
                }
            };

            // Determine user type
            let userType;
            if (isApproverUser) {
                userType = 'approver';
            } else if (isSalesUser) {
                userType = 'sales';
            } else if (isMisUser) {
                userType = 'mis';
            } else {
                userType = 'admin';
            }

            // Find configuration
            let config = kpiConfigs[kpi]?.[userType];
            if (!config) {
                config = { status: '', label: kpi };
            }

            console.log('Using configuration:', config);

            // Build URL with filters
            let baseUrl;
            if (isApproverUser) {
                baseUrl = "{{ route('approver.applications') }}";
            } else if (isSalesUser) {
                baseUrl = "{{ route('applications.index') }}";
            } else if (isMisUser) {
                baseUrl = "{{ route('mis.applications') }}";
            } else {
                baseUrl = "{{ route('approver.applications') }}";
            }

            // Build query parameters
            let queryParams = new URLSearchParams(formData);
            
            // Add status filter if specified
            if (config.status) {
                queryParams.set('status', config.status);
            }
            
            // Add mode for admin users
            if (!isSalesUser && !isMisUser && !isApproverUser) {
                queryParams.set('view_mode', 'all');
            }

            // Add KPI label for reference
            queryParams.set('kpi_filter', kpi);
            
            const url = `${baseUrl}?${queryParams.toString()}`;
            
            console.log(`Redirecting to: ${url}`);
            window.location.href = url;
        });
    }

    // Rest of the script (toast, modal, cascade, timeline, dropdowns unchanged)
    function initializeToast() {
        const toastEl = document.getElementById('actionToast');
        return new bootstrap.Toast(toastEl);
    }

    function showToast(type, message, title = '') {
        const toastEl = document.getElementById('actionToast');
        const toastTitle = document.getElementById('toast-title');
        const toastMessage = document.getElementById('toast-message');

        toastEl.className = `toast align-items-center text-bg-${type === 'error' ? 'danger' : 'success'} border-0`;

        toastTitle.textContent = title || (type === 'error' ? 'Error' : 'Success');
        toastMessage.textContent = message;

        const toast = initializeToast();
        toast.show();

        setTimeout(() => {
            toast.hide();
        }, 5000);
    }

    function getActionTitle(action) {
        const titles = {
            'approve': 'Application Approved',
            'reject': 'Application Rejected',
            'revert': 'Application Reverted',
            'hold': 'Application On Hold'
        };
        return titles[action] || 'Action Completed';
    }

    function initializeModalListeners() {
        $(document).off('click', '.take-action-btn');
        $(document).off('change', '#actionType');
        $(document).off('submit', '#action-form');
        $(document).off('hidden.bs.modal', '#actionModal');

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

            const nonActionableStatuses = ['distributorship_created', 'rejected', 'mis_rejected', 'agreement_created', 'document_verified', 'documents_received', 'mis_processing'];
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

        $(document).on('change', '#actionType', function() {
            const action = $(this).val();
            const applicationId = $('#application_id').val();
            if (action && applicationId) {
                const url = `{{ url('approvals') }}/${applicationId}/${action}`;
                $('#action-form').attr('action', url);
            } else {
                $('#action-form').attr('action', '');
            }

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
                        updateFunction();
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

    function getZoneByBU(bu) {
        const zoneSelect = $('#zone');
        $.ajax({
            url: "{{ route('get_zone_by_bu') }}",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { bu: bu },
            type: 'POST',
            dataType: 'json',
            beforeSend: function() { if (!isUpdating) loader.removeClass('d-none'); },
            success: function(data) {
                loader.addClass('d-none');
                const zoneList = data.zoneList || [];
                zoneSelect.empty().append('<option value="All">All Zone</option>');
                $.each(zoneList, function(index, zone) {
                    zoneSelect.append(`<option value="${zone.id}">${zone.zone_name}</option>`);
                });
                if (bu !== 'All' && zoneList.length > 0 && !isUpdating) {
                    updateFunction();
                }
                isUpdating = false;
            },
            error: function(xhr) {
                loader.addClass('d-none');
                console.error('Error fetching zones:', xhr.responseText);
                isUpdating = false;
            }
        });
    }

    function getRegionByZone(zone) {
        const regionSelect = $('#region');
        $.ajax({
            url: "{{ route('get_region_by_zone') }}",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { zone: zone },
            type: 'POST',
            dataType: 'json',
            beforeSend: function() { if (!isUpdating) loader.removeClass('d-none'); },
            success: function(data) {
                loader.addClass('d-none');
                const regionList = data.regionList || [];
                regionSelect.empty().append('<option value="All">All Region</option>');
                $.each(regionList, function(index, region) {
                    regionSelect.append(`<option value="${region.id}">${region.region_name}</option>`);
                });
                if (zone !== 'All' && regionList.length > 0 && !isUpdating) {
                    updateFunction();
                }
                isUpdating = false;
            },
            error: function(xhr) {
                loader.addClass('d-none');
                console.error('Error fetching regions:', xhr.responseText);
                isUpdating = false;
            }
        });
    }

    function getTerritoryByRegion(region) {
        const territorySelect = $('#territory');
        $.ajax({
            url: "{{ route('get_territory_by_region') }}",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { region: region },
            type: 'POST',
            dataType: 'json',
            beforeSend: function() { if (!isUpdating) loader.removeClass('d-none'); },
            success: function(data) {
                loader.addClass('d-none');
                const territoryList = data.territoryList || [];
                territorySelect.empty().append('<option value="All">All Territory</option>');
                $.each(territoryList, function(index, territory) {
                    territorySelect.append(`<option value="${territory.id}">${territory.territory_name}</option>`);
                });
                if (region !== 'All' && territoryList.length > 0 && !isUpdating) {
                    updateFunction();
                }
                isUpdating = false;
            },
            error: function(xhr) {
                loader.addClass('d-none');
                console.error('Error fetching territories:', xhr.responseText);
                isUpdating = false;
            }
        });
    }

  

    function initializeAllFeatures() {
        console.log('Initializing all features...');
        initializeTimelineToggles();
        initializeTableDropdowns();
        
        setupTabReinitialization();
    }

    function setupTabReinitialization() {
        console.log('Reinitializing table features on load...');
        setTimeout(() => {
            initializeTimelineToggles();
        }, 300);
    }

    function initializeTimelineToggles() {
        console.log('Initializing timeline toggles...');
        
        const allButtons = document.querySelectorAll('.toggle-timeline');
        //console.log('Found toggle buttons:', allButtons.length);
        
        if (allButtons.length === 0) {
            return;
            //console.warn('No toggle buttons found! Checking if tables are loaded...');
            const tables = document.querySelectorAll('table');
            //console.log('Available tables:', tables.length);
            tables.forEach((table, index) => {
                console.log(`Table ${index + 1}:`, table.innerHTML.substring(0, 200) + '...');
            });
            //return;
        }
        
        allButtons.forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
        });
        
        const freshButtons = document.querySelectorAll('.toggle-timeline');
        
        freshButtons.forEach(button => {
            button.__clickListenerAdded = true;
            
            button.addEventListener('click', function(e) {
                console.log('Toggle button clicked - Event fired!', this);
                e.preventDefault();
                e.stopPropagation();
                handleTimelineToggle(this);
            });
            
            button.style.transition = 'all 0.2s ease';
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.2)';
                this.style.color = '#007bff';
            });
            button.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
                if (!this.classList.contains('active')) {
                    this.style.color = '#6c757d';
                }
            });
        });
        
        console.log('Timeline toggles initialized. Buttons processed:', freshButtons.length);
    }

    function handleTimelineToggle(button) {
        const applicationId = button.getAttribute('data-application-id');
        console.log('Handling toggle for application:', applicationId);
        
        if (!applicationId) {
            console.error('No application ID found on button:', button);
            return;
        }
        
        const table = button.closest('table');
        if (!table) {
            console.error('No table found for button:', button);
            return;
        }
        
        const timelineRowId = `timeline-${applicationId}`;
        const timelineRow = table.querySelector(`#${timelineRowId}`);
        const icon = button.querySelector('i');
        
        console.log('Looking for timeline row:', timelineRowId, 'Found:', !!timelineRow);
        
        if (!timelineRow) {
            console.error('Timeline row not found for application:', applicationId);
            console.log('Available timeline rows in table:', table.querySelectorAll('.timeline-row').length);
            
            const allTimelineRows = table.querySelectorAll('.timeline-row');
            allTimelineRows.forEach(row => {
                console.log('Available timeline row ID:', row.id);
            });
            return;
        }
        
        if (timelineRow.style.display === 'table-row') {
            timelineRow.style.display = 'none';
            icon.className = 'ri-add-circle-line';
            button.setAttribute('title', 'Show Approval Timeline');
            button.classList.remove('active');
            console.log('Timeline closed for:', applicationId);
        } else {
            const allTimelineRows = table.querySelectorAll('.timeline-row');
            const allToggleButtons = table.querySelectorAll('.toggle-timeline');
            
            console.log('Closing other timelines in table. Total found:', allTimelineRows.length);
            
            allTimelineRows.forEach(row => {
                row.style.display = 'none';
            });
            allToggleButtons.forEach(btn => {
                const btnIcon = btn.querySelector('i');
                btnIcon.className = 'ri-add-circle-line';
                btn.setAttribute('title', 'Show Approval Timeline');
                btn.classList.remove('active');
            });
            
            timelineRow.style.display = 'table-row';
            icon.className = 'ri-indeterminate-circle-line';
            button.setAttribute('title', 'Hide Approval Timeline');
            button.classList.add('active');
            console.log('Timeline opened for:', applicationId);
        }
    }

    function initializeTableDropdowns() {
    console.log('Initializing table dropdowns...');
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function closeAllTableDropdowns() {
        $('.table-dropdown-menu').removeClass('show').css('display', 'none');
        $('.table-dropdown-btn').attr('aria-expanded', 'false');
        console.log('All table dropdowns closed');
    }

    // Remove existing event handlers first to prevent duplicates
    $(document).off('click', '.table-dropdown-btn');
    $(document).off('click', '.view-doc-btn');
    $(document).off('click', '.view-physical-doc-btn');
    $(document).off('click', '.confirm-distributor-btn');
    $(document).off('click', '#confirm-distributor-submit');
    $(document).off('click', '.mis-action-btn'); // Add this for MIS action buttons

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
        } else {
            $dropdown.removeClass('show').css('display', 'none');
            $button.attr('aria-expanded', 'false');
            console.log('Table dropdown closed for application ID:', $button.closest('tr').data('application-id'));
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

    // MIS Action buttons (Verify Checklist, Manage Physical Documents)
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

    // View Document Verification buttons
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

    // View Physical Document Verification buttons
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

    // Update the confirm distributor modal handler
$(document).on('click', '.confirm-distributor-btn', function(e) {
    e.preventDefault();
    e.stopPropagation();
    closeAllTableDropdowns();
    
    var url = $(this).data('url');
    var applicationId = $(this).data('application-id');
    var distributorName = $(this).data('distributor-name');
    
    // Set the application details
    $('#confirm-application-id').text(applicationId);
    $('#confirm-distributor-name').text(distributorName);
    
    // Reset and initialize the form
    $('#confirm-distributor-form')[0].reset();
    $('#confirm-distributor-form').removeClass('was-validated');
    
    // Set today's date as default for appointment date
    var today = new Date().toISOString().split('T')[0];
    $('#date-of-appointment').val(today);
    
    // Store the submission URL in the form
    $('#confirm-distributor-form').data('url', url);
    
    $('#confirmDistributorModal').modal('show');
});

 
let isConfirmSubmitting = false;

$(document).on('click', '.confirm-distributor-btn', function(e) {
    e.preventDefault();
    e.stopPropagation();
    closeAllTableDropdowns();
    
    var url = $(this).data('url');
    var applicationId = $(this).data('application-id');
    var distributorName = $(this).data('distributor-name');
    var currentStatus = $(this).data('status');
    
    // Quick check - if already confirmed, don't open modal
    if (currentStatus === 'distributorship_created') {
        showToast('info', 'This distributor has already been confirmed.', 'Info');
        return;
    }
    
    // Set the application details
    $('#confirm-application-id').text(applicationId);
    $('#confirm-distributor-name').text(distributorName);
    
    // Reset and initialize the form
    $('#confirm-distributor-form')[0].reset();
    $('#confirm-distributor-form').removeClass('was-validated');
    
    // Set today's date as default for appointment date
    var today = new Date().toISOString().split('T')[0];
    $('#date-of-appointment').val(today);
    
    // Store the submission URL in the form
    $('#confirm-distributor-form').data('url', url);
    
    $('#confirmDistributorModal').modal('show');
});

$(document).on('submit', '#confirm-distributor-form', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    if (isConfirmSubmitting) {
        return;
    }
    
    var form = $(this)[0];
    var url = $(this).data('url');
    
    if (!form.checkValidity()) {
        $(this).addClass('was-validated');
        return;
    }
    
    isConfirmSubmitting = true;
    var formData = $(this).serialize();
    var submitBtn = $('#confirm-distributor-submit');
    
    console.log('Submitting form data:', formData);
    console.log('URL:', url);
    
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Processing...');
    
    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Success response:', response);
            if (response.success) {
                showToast('success', response.message, 'Success');
                $('#confirmDistributorModal').modal('hide');
                
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showToast('error', response.message, 'Error');
                submitBtn.prop('disabled', false).html('Confirm Distributor');
                isConfirmSubmitting = false;
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log('Error response:', jqXHR);
            
            // Silent handling for 403 errors (already confirmed)
            if (jqXHR.status === 403) {
                $('#confirmDistributorModal').modal('hide');
                setTimeout(() => {
                    window.location.reload();
                }, 500);
                isConfirmSubmitting = false;
                return;
            }
            
            var errorMessage = 'An error occurred while processing your request.';
            
            if (jqXHR.responseJSON && jqXHR.responseJSON.errors) {
                var errors = jqXHR.responseJSON.errors;
                errorMessage = Object.values(errors).flat().join('<br>');
                $('#confirm-distributor-form').addClass('was-validated');
            } else if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                errorMessage = jqXHR.responseJSON.message;
            } else if (jqXHR.status === 500) {
                errorMessage = 'Internal server error. Please try again later.';
            }
            
            showToast('error', errorMessage, 'Error');
            submitBtn.prop('disabled', false).html('Confirm Distributor');
            isConfirmSubmitting = false;
        }
    });
});

$('#confirmDistributorModal').on('hidden.bs.modal', function() {
    isConfirmSubmitting = false;
    $('#confirm-distributor-form')[0].reset();
    $('#confirm-distributor-form').removeClass('was-validated');
    $('#confirm-distributor-submit').prop('disabled', false).html('Confirm Distributor');
    $('#date-of-appointment, #distributor-code').removeClass('is-valid is-invalid');
});



    // Modal cleanup handlers
    $('#docVerificationModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
        $('#doc-verification-content').html('Loading...');
    });
    $('#physicalDocVerificationModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
        $('#physical-doc-verification-content').html('Loading...');
    });

    // Window resize handler
    $(window).off('resize').on('resize', function() {
        closeAllTableDropdowns();
    });

    console.log('Table dropdowns initialized successfully');
}

    window.initializeTimelineToggles = initializeTimelineToggles;
    window.initializeAllFeatures = initializeAllFeatures;


    function initializeDocumentChecklist() {
    // Show canvas button handler
    $('#showDocumentChecklistBtn').on('click', function() {
        const canvas = new bootstrap.Offcanvas('#documentChecklistCanvas');
        canvas.show();
        loadDocumentChecklist();
    });

    // Only entity type change handler now
    $('#canvas_entity_type').on('change', function() {
        loadDocumentChecklist();
    });

    // Canvas shown event - reload data when opened
    $('#documentChecklistCanvas').on('shown.bs.offcanvas', function() {
        loadDocumentChecklist();
    });
}

function loadDocumentChecklist() {
    const entityType = $('#canvas_entity_type').val();
    
    // Show loading, hide content and error
    $('#checklistLoading').removeClass('d-none');
    $('#checklistContent').addClass('d-none');
    $('#checklistError').addClass('d-none');
    
    $.ajax({
        url: "{{ route('document-checklist.canvas-data') }}",
        type: 'GET',
        data: {
            entity_type: entityType
        },
        success: function(response) {
            $('#checklistLoading').addClass('d-none');
            
            if (response.success && response.html) {
                $('#canvasDocumentsContainer').html(response.html);
                $('#checklistContent').removeClass('d-none');
                // No need to initialize checkboxes anymore
                initializeCanvasTooltips();
            } else {
                showChecklistError();
            }
        },
        error: function(xhr) {
            $('#checklistLoading').addClass('d-none');
            showChecklistError();
            console.error('Error loading document checklist:', xhr);
        }
    });
}

function showChecklistError() {
    $('#checklistError').removeClass('d-none');
    $('#checklistContent').addClass('d-none');
}

function initializeCanvasTooltips() {
    // Initialize tooltips only
    $('[data-bs-toggle="tooltip"]').tooltip();
}
</script>
@endpush