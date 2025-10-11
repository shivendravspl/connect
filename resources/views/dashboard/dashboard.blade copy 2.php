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

    /* KPI Cards */
    .kpi-card {
        border-left: 2px solid #007bff;
        transition: transform 0.2s;
        background-color: #ffffff;
        box-shadow: 0 0.1rem 0.2rem rgba(0, 0, 0, 0.05);
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .kpi-card:hover {
        transform: translateY(-1px);
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

    /* Tab Navigation */
    .nav-tabs .nav-link {
        font-size: 0.7rem;
        padding: 0.3rem 0.6rem;
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
</style>
@endpush

@section('content')
<div id="elmLoader" class="loader-overlay d-none">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
@if(Auth::check() && (Auth::user()->hasAnyRole(['Super Admin', 'Admin']) || Auth::user()->hasPermissionTo('distributor_approval')))
    <div class="row mb-1">
        <div class="col-12">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1 header-title">Filters</h4>
                    <div class="flex-shrink-0">
                        <button type="button" class="btn btn-soft-primary material-shadow-none btn-sm" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                            <i class="ri-filter-2-line"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-1">
                    <form id="filter-form" method="GET">
                        <div class="collapse show" id="filterCollapse">
                            <div class="row g-1">
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                    <label for="date_range_type" class="form-label">Date Type</label>
                                    <select name="date_range_type" id="date_range_type" class="form-select form-select-sm">
                                        <option value="submission" {{ $filters['date_range_type'] == 'submission' ? 'selected' : '' }}>Submission</option>
                                        <option value="approval" {{ $filters['date_range_type'] == 'approval' ? 'selected' : '' }}>Approval</option>
                                        <option value="appointment" {{ $filters['date_range_type'] == 'appointment' ? 'selected' : '' }}>Appointment</option>
                                    </select>
                                </div>
                                @if ($access_level == 'bu' || $access_level == 'all')
                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                        <label for="bu" class="form-label">BU</label>
                                        <select name="bu" id="bu" class="form-select form-select-sm">
                                            <option value="All">All BU</option>
                                            @foreach ($bu_list as $key => $value)
                                                <option value="{{ $key }}" {{ $filters['bu'] == $key ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                @if ($access_level == 'bu' || $access_level == 'zone' || $access_level == 'all')
                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                        <label for="zone" class="form-label">Zone</label>
                                        <select name="zone" id="zone" class="form-select form-select-sm">
                                            <option value="All">All Zone</option>
                                            @foreach ($zone_list as $key => $value)
                                                <option value="{{ $key }}" {{ $filters['zone'] == $key ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                @if ($access_level == 'bu' || $access_level == 'zone' || $access_level == 'region' || $access_level == 'all')
                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                        <label for="region" class="form-label">Region</label>
                                        <select name="region" id="region" class="form-select form-select-sm">
                                            <option value="All">All Region</option>
                                            @foreach ($region_list as $key => $value)
                                                <option value="{{ $key }}" {{ $filters['region'] == $key ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                @if ($access_level == 'bu' || $access_level == 'zone' || $access_level == 'region' || $access_level == 'territory' || $access_level == 'all')
                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                        <label for="territory" class="form-label">Territory</label>
                                        <select name="territory" id="territory" class="form-select form-select-sm">
                                            <option value="All">All Territory</option>
                                            @foreach ($territory_list as $key => $value)
                                                <option value="{{ $key }}" {{ $filters['territory'] == $key ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                    <label class="form-label">Initiator</label>
                                    <select name="initiator_role" id="initiator_role" class="form-select form-select-sm">
                                        <option value="">All</option>
                                        <option value="TM" {{ $filters['initiator_role'] == 'TM' ? 'selected' : '' }}>TM</option>
                                        <option value="Area Coordinator" {{ $filters['initiator_role'] == 'Area Coordinator' ? 'selected' : '' }}>Area Coordinator</option>
                                        <option value="Regional Business Manager" {{ $filters['initiator_role'] == 'Regional Business Manager' ? 'selected' : '' }}>RBM</option>
                                        <option value="Zonal Business Manager" {{ $filters['initiator_role'] == 'Zonal Business Manager' ? 'selected' : '' }}>ZBM</option>
                                    </select>
                                </div>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                    <label class="form-label">Stage</label>
                                    <select name="approval_stage" id="approval_stage" class="form-select form-select-sm">
                                        <option value="">All</option>
                                        <option value="initiated" {{ $filters['approval_stage'] == 'initiated' ? 'selected' : '' }}>Initiated</option>
                                        <option value="Regional Business Manager" {{ $filters['approval_stage'] == 'Regional Business Manager' ? 'selected' : '' }}>RBM</option>
                                        <option value="Zonal Business Manager" {{ $filters['approval_stage'] == 'Zonal Business Manager' ? 'selected' : '' }}>ZBM</option>
                                        <option value="General Manager" {{ $filters['approval_stage'] == 'General Manager' ? 'selected' : '' }}>GM</option>
                                        <option value="mis" {{ $filters['approval_stage'] == 'mis' ? 'selected' : '' }}>MIS</option>
                                        <option value="completed" {{ $filters['approval_stage'] == 'completed' ? 'selected' : '' }}>Final</option>
                                    </select>
                                </div>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                    <label class="form-label">Status</label>
                                    <select name="status" id="status1" class="form-select form-select-sm">
                                        <option value="">All</option>
                                        <option value="initiated" {{ $filters['status'] == 'initiated' ? 'selected' : '' }}>Initiated</option>
                                        <option value="under_review" {{ $filters['status'] == 'under_review' ? 'selected' : '' }}>Review</option>
                                        <option value="on_hold" {{ $filters['status'] == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                        <option value="approved" {{ $filters['status'] == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ $filters['status'] == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        <option value="reverted" {{ $filters['status'] == 'reverted' ? 'selected' : '' }}>Reverted</option>
                                        <option value="distributorship_created" {{ $filters['status'] == 'distributorship_created' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </div>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                    <label class="form-label">From</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] }}">
                                </div>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                    <label class="form-label">To</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] }}">
                                </div>
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

    {{--<div class="row mb-1">
        <div class="col-12">
            <div class="crm-widget">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1 header-title">Key Indicators</h4>
                    <div class="flex-shrink-0">
                        <div class="dropdown card-header-dropdown">
                            <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="text-muted fs-14"><i class="mdi mdi-dots-vertical align-middle"></i></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Today</a>
                                <a class="dropdown-item" href="#">Last Week</a>
                                <a class="dropdown-item" href="#">Last Month</a>
                                <a class="dropdown-item" href="#">Current Year</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="row g-1" id="kpi-container">
                        @if ($data['counts']['total'] == 0)
                            <div class="col-12 no-data-message">No applications found based on current filters.</div>
                        @else
                            <div class="col-6 col-md-3 col-lg-2">
                                <div class="card kpi-card">
                                    <div class="card-body">
                                        <h6 class="small">Total Forms</h6>
                                        <div class="kpi-value" id="kpi-total-submitted">{{ $data['counts']['total'] }}</div>
                                        <span class="kpi-trend-up" id="kpi-trend-total-submitted">🔼 {{ $data['kpi_trends']['total_submitted'] }}%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-2">
                                <div class="card kpi-card">
                                    <div class="card-body">
                                        <h6 class="small">Avg. TAT</h6>
                                        <div class="kpi-value" id="kpi-avg-tat">{{ $tatData['total']['avg_tat'] }} Days</div>
                                        <span class="kpi-trend-down" id="kpi-trend-avg-tat">⏬ {{ $data['kpi_trends']['avg_tat'] }} Days</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-2">
                                <div class="card kpi-card">
                                    <div class="card-body">
                                        <h6 class="small">Appointments</h6>
                                        <div class="kpi-value" id="kpi-appointments-completed">{{ $data['counts']['distributors_created'] }}</div>
                                        <span class="kpi-trend-up" id="kpi-trend-appointments-completed">🔼 {{ $data['kpi_trends']['appointments_completed'] }}%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-2">
                                <div class="card kpi-card">
                                    <div class="card-body">
                                        <h6 class="small">In Process</h6>
                                        <div class="kpi-value" id="kpi-in-process">{{ $data['counts']['in_process'] }}</div>
                                        <span class="kpi-trend-neutral" id="kpi-trend-in-process">—</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-2">
                                <div class="card kpi-card">
                                    <div class="card-body">
                                        <h6 class="small">Reverted</h6>
                                        <div class="kpi-value" id="kpi-reverted">{{ $data['counts']['reverted'] }}</div>
                                        <span class="kpi-trend-up" id="kpi-trend-reverted">🔼 {{ $data['kpi_trends']['reverted'] }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-2">
                                <div class="card kpi-card">
                                    <div class="card-body">
                                        <h6 class="small">Rejected</h6>
                                        <div class="kpi-value" id="kpi-rejected">{{ $data['counts']['rejected'] }}</div>
                                        <span class="kpi-trend-down" id="kpi-trend-rejected">🔽 {{ $data['kpi_trends']['rejected'] }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3 col-lg-2">
                                <div class="card kpi-card">
                                    <div class="card-body">
                                        <h6 class="small">To MIS</h6>
                                        <div class="kpi-value" id="kpi-forwarded-to-mis">{{ $data['counts']['forwarded_to_mis'] }}</div>
                                        <span class="kpi-trend-up" id="kpi-trend-forwarded-to-mis">🔼 {{ $data['kpi_trends']['forwarded_to_mis'] }}%</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>--}}

    <div class="row mb-1">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs nav-tabs-custom mb-1">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#pending-tab">
                                Submitted (<span id="tab-pending-count">{{ $data['counts']['pending'] }}</span>)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#my-tab">
                                My Apps (<span id="tab-my-count">{{ $data['counts']['my'] }}</span>)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#master-tab">
                                All Applications (<span id="tab-master-count">{{ $data['counts']['total'] }}</span>)
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="pending-tab">
                            @if ($pendingApplications->isEmpty())
                                <div class="no-data-message">No applications pending your approval.</div>
                            @else
                                @include('dashboard._approver-table', ['pendingApplications' => $pendingApplications])
                            @endif
                        </div>
                        <div class="tab-pane fade" id="my-tab">
                            @if ($myApplications->isEmpty())
                                <div class="no-data-message">You haven't submitted or acted on any applications yet.</div>
                            @else
                                @include('dashboard._sales-table')
                            @endif
                        </div>
                        <div class="tab-pane fade" id="master-tab">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="header-title">Applications</h5>
                                        </div>
                                        <div class="card-body">
                                            <div id="master-table-container">
                                                @if ($masterReportApplications->isEmpty())
                                                    <div class="no-data-message">No applications found.</div>
                                                @else
                                                    @include('dashboard._master-table', ['masterReportApplications' => $masterReportApplications])
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@elseif(Auth::check() && (Auth::user()->hasAnyRole(['Mis Admin', 'Mis User'])))

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">MIS Dashboard - Post-Approval Management</div>
                    <div class="card-body">
                        {{--<div class="row mb-1">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header align-items-center d-flex">
                                        <h4 class="card-title mb-0 flex-grow-1 header-title">Filters</h4>
                                        <div class="flex-shrink-0">
                                            <button type="button" class="btn btn-soft-primary material-shadow-none btn-sm" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                                                <i class="ri-filter-2-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body p-1">
                                        <form id="filter-form" method="GET">
                                            <div class="collapse show" id="filterCollapse">
                                                <div class="row g-1">
                                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                                        <label for="date_range_type" class="form-label">Date Type</label>
                                                        <select name="date_range_type" id="date_range_type" class="form-select form-select-sm">
                                                            <option value="submission" {{ $filters['date_range_type'] == 'submission' ? 'selected' : '' }}>Submission</option>
                                                            <option value="approval" {{ $filters['date_range_type'] == 'approval' ? 'selected' : '' }}>Approval</option>
                                                            <option value="appointment" {{ $filters['date_range_type'] == 'appointment' ? 'selected' : '' }}>Appointment</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                                        <label for="status" class="form-label">Status</label>
                                                        <select name="status" id="status1" class="form-select form-select-sm">
                                                            <option value="">All</option>
                                                            <option value="mis_processing" {{ $filters['status'] == 'mis_processing' ? 'selected' : '' }}>MIS Processing</option>
                                                            <option value="document_verified" {{ $filters['status'] == 'document_verified' ? 'selected' : '' }}>Document Verified</option>
                                                            <option value="agreement_created" {{ $filters['status'] == 'agreement_created' ? 'selected' : '' }}>Agreement Created</option>
                                                            <option value="documents_received" {{ $filters['status'] == 'documents_received' ? 'selected' : '' }}>Documents Received</option>
                                                            <option value="distributorship_created" {{ $filters['status'] == 'distributorship_created' ? 'selected' : '' }}>Completed</option>
                                                            <option value="mis_rejected" {{ $filters['status'] == 'mis_rejected' ? 'selected' : '' }}>Rejected</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                                        <label class="form-label">From</label>
                                                        <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] }}">
                                                    </div>
                                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 filter-col">
                                                        <label class="form-label">To</label>
                                                        <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] }}">
                                                    </div>
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
                        </div>--}}
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="header-title">MIS Applications</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="mis-table-container">
                                            @if ($misApplications->isEmpty())
                                                <div class="no-data-message">No applications found.</div>
                                            @else
                                                @include('dashboard._mis-table', ['misApplications' => $misApplications])
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
@include('layouts.script') 
@push('scripts')
<script>
    const loader = $("#elmLoader");
    let isUpdating = false;

    $(document).ready(function() {
        // Initialize Select2 for filter dropdowns
        $('#date_range_type, #bu, #zone, #region, #territory, #initiator_role, #approval_stage, #status1').select2({
            width: '100%',
            minimumResultsForSearch: 10,
            placeholder: "Select an option",
            allowClear: true
        });

        // Handle filter changes with debounce
        let debounceTimer;
        $('#filter-form select, #filter-form input').on('change', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                if (!isUpdating) updateDashboard();
            }, 300);
        });

        // Prevent form submission on Enter key
        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            if (!isUpdating) updateDashboard();
        });

        // Initial load
        updateDashboard();

        // Cascade handlers
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

        // Expandable panel for MIS table rows
        $(document).on('click', '.expandable-panel', function() {
            $(this).next('.panel-content').toggle();
        });

        // Handle Select2 open event to adjust z-index for loader compatibility
        $('.select2-container').on('select2:open', function() {
            $('.select2-dropdown').css('z-index', 10000);
        });

        // Initialize modal and toast
        initializeModalListeners();
        initializeToast();
    });

    function initializeToast() {
        const toastEl = document.getElementById('actionToast');
        return new bootstrap.Toast(toastEl);
    }

    function showToast(type, message, title = '') {
        const toastEl = document.getElementById('actionToast');
        const toastBody = toastEl.querySelector('.toast-body');
        const toastTitle = document.getElementById('toast-title');
        const toastMessage = document.getElementById('toast-message');

        // Set toast type
        toastEl.className = `toast align-items-center text-bg-${type === 'error' ? 'danger' : 'success'} border-0`;

        // Set content
        toastTitle.textContent = title || (type === 'error' ? 'Error' : 'Success');
        toastMessage.textContent = message;

        // Show toast
        const toast = initializeToast();
        toast.show();

        // Auto-hide after 5 seconds
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
        // Remove existing listeners to prevent duplication
        $(document).off('click', '.take-action-btn');
        $(document).off('change', '#actionType');
        $(document).off('submit', '#action-form');
        $(document).off('hidden.bs.modal', '#actionModal');

        // Handle Take Action button click
        $(document).on('click', '.take-action-btn', function() {
            const applicationId = $(this).data('application-id');
            const distributorName = $(this).data('distributor-name') || 'N/A';
            const submissionDate = $(this).data('submission-date') || 'N/A';
            const initiator = $(this).data('initiator') || 'N/A';
            const status = $(this).data('status') || '';

            console.log('Button data:', { applicationId, distributorName, submissionDate, initiator, status });

            // Update modal content
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

            // Disable submit button for non-actionable statuses
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

        // Handle action type change
        $(document).on('change', '#actionType', function() {
            const action = $(this).val();
            const applicationId = $('#application_id').val();
            if (action && applicationId) {
                const url = `{{ url('approvals') }}/${applicationId}/${action}`;
                $('#action-form').attr('action', url);
                console.log('Form action set to:', url);
            } else {
                $('#action-form').attr('action', '');
                console.log('Form action cleared');
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

        // Handle form submission
        $(document).on('submit', '#action-form', function(e) {
            e.preventDefault();
            const form = $(this);
            const action = $('#actionType').val();
            const remarks = $('#remarks').val().trim();
            const followUpDate = $('#follow_up_date').val();
            const submitBtn = $('#action-submit-btn');
            const spinner = submitBtn.find('.spinner-border');
            const submitText = submitBtn.find('.submit-text');

            // Client-side validation
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
                        updateDashboard();
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

        // Reset modal on close
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

    function updateDashboard() {
        if (isUpdating) return;
        isUpdating = true;
        const formData = $('#filter-form').serialize();
        $.ajax({
            url: "{{ route('dashboard.dynamic-data') }}",
            type: 'GET',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                loader.removeClass('d-none');
            },
            success: function(data) {
                loader.addClass('d-none');
                console.log("Received data:", data);

                if (!data || !data.counts || !data.tat || !data.kpi_trends) {
                    console.error('Invalid response structure:', data);
                    $('#kpi-container').html('<div class="col-12 no-data-message">Error: Incomplete data structure from server.</div>');
                    $('#pending-tab').html('<div class="no-data-message">No applications pending your approval.</div>');
                    $('#my-tab').html('<div class="no-data-message">You haven\'t submitted or acted on any applications yet.</div>');
                    $('#master-table-container').html('<div class="no-data-message">No applications found.</div>');
                    isUpdating = false;
                    return;
                }

                // Update KPI values
                const kpiContainer = $('#kpi-container');
                if (data.counts.total === 0) {
                    kpiContainer.html('<div class="col-12 no-data-message">No applications found based on current filters.</div>');
                } else {
                    kpiContainer.html(`
                        <div class="col-6 col-md-3 col-lg-2">
                            <div class="card kpi-card">
                                <div class="card-body">
                                    <h6 class="small">Total Forms</h6>
                                    <div class="kpi-value" id="kpi-total-submitted">${data.counts.total || 0}</div>
                                    <span class="kpi-trend-up" id="kpi-trend-total-submitted">🔼 ${data.kpi_trends.total_submitted || 0}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <div class="card kpi-card">
                                <div class="card-body">
                                    <h6 class="small">Avg. TAT</h6>
                                    <div class="kpi-value" id="kpi-avg-tat">${data.tat.total?.avg_tat || 0} Days</div>
                                    <span class="kpi-trend-down" id="kpi-trend-avg-tat">⏬ ${data.kpi_trends.avg_tat || 0} Days</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <div class="card kpi-card">
                                <div class="card-body">
                                    <h6 class="small">Appointments</h6>
                                    <div class="kpi-value" id="kpi-appointments-completed">${data.counts.distributors_created || 0}</div>
                                    <span class="kpi-trend-up" id="kpi-trend-appointments-completed">🔼 ${data.kpi_trends.appointments_completed || 0}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <div class="card kpi-card">
                                <div class="card-body">
                                    <h6 class="small">In Process</h6>
                                    <div class="kpi-value" id="kpi-in-process">${data.counts.in_process || 0}</div>
                                    <span class="kpi-trend-neutral" id="kpi-trend-in-process">—</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <div class="card kpi-card">
                                <div class="card-body">
                                    <h6 class="small">Reverted</h6>
                                    <div class="kpi-value" id="kpi-reverted">${data.counts.reverted || 0}</div>
                                    <span class="kpi-trend-up" id="kpi-trend-reverted">🔼 ${data.kpi_trends.reverted || 0}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <div class="card kpi-card">
                                <div class="card-body">
                                    <h6 class="small">Rejected</h6>
                                    <div class="kpi-value" id="kpi-rejected">${data.counts.rejected || 0}</div>
                                    <span class="kpi-trend-down" id="kpi-trend-rejected">🔽 ${data.kpi_trends.rejected || 0}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <div class="card kpi-card">
                                <div class="card-body">
                                    <h6 class="small">To MIS</h6>
                                    <div class="kpi-value" id="kpi-forwarded-to-mis">${data.counts.forwarded_to_mis || 0}</div>
                                    <span class="kpi-trend-up" id="kpi-trend-forwarded-to-mis">🔼 ${data.kpi_trends.forwarded_to_mis || 0}%</span>
                                </div>
                            </div>
                        </div>
                    `);
                }

                // Update tab counts
                $('#tab-pending-count').text(data.counts.pending || 0);
                $('#tab-my-count').text(data.counts.my || 0);
                $('#tab-master-count').text(data.counts.total || 0);

                // Update Pending Applications table
                $('#pending-tab').html(data.pending_table_html || '<div class="no-data-message">No applications pending your approval.</div>');

                // Update My Applications table
                $('#my-tab').html(data.my_table_html || '<div class="no-data-message">You haven\'t submitted or acted on any applications yet.</div>');

                // Update Master Report table
                $('#master-table-container').html(data.master_table_html || '<div class="no-data-message">No applications found.</div>');

                // Re-initialize tooltips and modal listeners
                $('[data-bs-toggle="tooltip"]').tooltip('dispose');
                $('[data-bs-toggle="tooltip"]').tooltip();
                initializeModalListeners();
                isUpdating = false;
            },
            error: function(xhr) {
                loader.addClass('d-none');
                console.error('Error fetching dashboard data:', xhr.responseText);
                $('#kpi-container').html('<div class="col-12 no-data-message">Error loading data. Please try again.</div>');
                $('#pending-tab').html('<div class="no-data-message">Error loading pending applications.</div>');
                $('#my-tab').html('<div class="no-data-message">Error loading your applications.</div>');
                $('#master-table-container').html('<div class="no-data-message">Error loading applications.</div>');
                isUpdating = false;
            }
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
                    updateDashboard();
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
                    updateDashboard();
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
                    updateDashboard();
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
</script>
@endpush