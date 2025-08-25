@extends('layouts.app')

@push('styles')
<style>
    /* General site-wide responsive adjustments */
    body {
        font-size: 0.9rem;
        /* Slightly smaller base font for overall compactness */
    }

    /* Card padding and margin for general compactness */
    .card {
        margin-bottom: 0.75rem;
        /* Reduce card vertical spacing */
        border-radius: 0.25rem;
        /* Keep rounded corners */
    }

    .card-body {
        padding: 0.75rem;
        /* Slightly reduced default padding for card bodies */
    }

    /* Filter section adjustments */
    .form-label {
        font-size: 0.7rem;
        /* Smaller labels */
        margin-bottom: 0.2rem;
        /* Reduced margin */
    }

    .form-select-sm,
    .form-control-sm,
    .btn-sm {
        font-size: 0.7rem;
        /* Smaller form controls and buttons */
        padding: 0.2rem 0.4rem;
        /* More compact padding */
        height: 1.8rem;
        /* Set a fixed height for consistency */
    }

    .btn.btn-sm {
        /* Ensure generic btn-sm styles don't get overridden too much */
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    /* --- KPI Cards Specific Styles --- */
    .kpi-card {
        border-left: 3px solid #007bff;
        transition: transform 0.2s;
        background-color: #ffffff;
        /* Explicitly set white background */
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        /* Subtle shadow for depth */
        height: 100%;
        /* Ensure all cards in a row have equal height */
        display: flex;
        flex-direction: column;
        /* Stack content vertically */
    }

    .kpi-card:hover {
        transform: translateY(-2px);
    }

    .kpi-card .card-body {
        padding: 0.6rem;
        /* Slightly increased for better visual balance */
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex-grow: 1;
        /* Allow content to grow and fill space */
    }

    .kpi-card h6.small {
        font-size: 0.75rem;
        /* Slightly larger title for readability */
        margin-bottom: 0.2rem;
        /* Reduced space below title */
        color: #6c757d;
        /* Muted color for titles */
        line-height: 1.2;
    }

    .kpi-value {
        font-size: 1.2rem;
        /* Larger, more prominent value */
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 0.2rem;
        /* Space below value */
        flex-grow: 1;
        /* Allow value to take up more vertical space if needed */
        display: flex;
        /* Use flex to center value if it's short */
        align-items: center;
        /* Center vertically within its flex area */
    }

    .kpi-trend-up,
    .kpi-trend-down,
    .kpi-trend-neutral {
        font-size: 0.65rem;
        /* Slightly increased trend font size */
        line-height: 1;
        display: block;
        margin-top: auto;
        /* Pushes to the bottom of the card body */
        text-align: right;
        /* Align trend to the right */
        font-weight: 600;
        /* Make trend bold */
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

    /* --- Tab Navigation --- */
    .nav-tabs .nav-link {
        font-size: 0.75rem;
        /* Smaller tabs */
        padding: 0.4rem 0.75rem;
    }

    /* --- Tables (General and TAT) --- */
    .tat-table th,
    .tat-table td,
    .compact-table th,
    .compact-table td {
        font-size: 0.7rem;
        /* Make table content smaller */
        padding: 0.4rem;
        /* Reduce cell padding */
        vertical-align: middle;
        /* Ensure content is vertically aligned */
    }

    .badge {
        font-size: 0.65rem;
        /* Slightly larger badges for readability */
        padding: 0.25rem 0.5rem;
    }

    .no-data-message {
        font-size: 0.75rem;
        /* Smaller no data message */
        color: #6c757d;
        text-align: center;
        padding: 0.75rem;
    }

    /* --- Loader Styles --- */
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
        width: 3rem;
        height: 3rem;
    }

    /* --- Mobile-Specific Overrides (max-width: 576px) --- */
    @media (max-width: 576px) {

        /* Adjustments for extra small screens (phones in portrait) */
        .container-fluid {
            padding-left: 0.25rem;
            /* Reduce overall padding on edges */
            padding-right: 0.25rem;
        }

        .col-6 {
            /* This is already set, ensuring 2 columns for KPIs */
            flex: 0 0 50%;
            max-width: 50%;
        }

        .header-title {
            font-size: 0.8rem;
            /* Smaller header titles */
            margin-bottom: 0.5rem;
        }

        .form-select-sm,
        .form-control-sm,
        .btn-sm {
            font-size: 0.6rem;
            /* Even smaller form controls and buttons */
            padding: 0.15rem 0.3rem;
            height: 1.5rem;
            /* Consistent smaller height */
        }

        .kpi-card .card-body {
            padding: 0.3rem;
            /* Even tighter padding for KPI cards */
        }

        .kpi-card h6.small {
            font-size: 0.65rem;
            /* Smallest title size */
        }

        .kpi-value {
            font-size: 0.9rem;
            /* Smallest KPI value size */
        }

        .kpi-trend-up,
        .kpi-trend-down,
        .kpi-trend-neutral {
            font-size: 0.55rem;
            /* Smallest trend text */
        }

        .tat-table th,
        .tat-table td,
        .compact-table th,
        .compact-table td {
            font-size: 0.6rem;
            /* Smallest table content */
            padding: 0.2rem;
            /* Minimal padding */
        }

        .badge {
            font-size: 0.55rem;
            /* Smallest badges */
            padding: 0.15rem 0.3rem;
        }

        .loader-overlay .spinner-border {
            width: 1.8rem;
            /* Smaller spinner on mobile */
            height: 1.8rem;
        }
    }

    /* Larger tablets and horizontal phones */
    @media (min-width: 577px) and (max-width: 768px) {
        .col-sm-4 {
            /* Ensure 3 columns for KPIs on small tablets */
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }

        .kpi-card .card-body {
            padding: 0.4rem;
        }

        .kpi-value {
            font-size: 1.1rem;
        }

        .kpi-card h6.small {
            font-size: 0.7rem;
        }

        .form-select-sm,
        .form-control-sm,
        .btn-sm {
            font-size: 0.7rem;
            height: 1.7rem;
        }

        .tat-table th,
        .tat-table td,
        .compact-table th,
        .compact-table td {
            font-size: 0.65rem;
        }
    }
</style>
@endpush

@section('content')
<div id="elmLoader" class="loader-overlay d-none">
    <div class="spinner-border text-primary" role="status"> {{-- Removed avatar-sm as we're defining size in CSS --}}
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
@if(Auth::check() && Auth::user()->hasAnyRole(['Super Admin', 'Admin', 'Mis User']))

<div class="min-vh-100 d-flex align-items-center"
    <div class="container py-5">

        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                                    <i class="fas fa-filter"></i> Filters
                                </button>
                            </div>
                            <form id="filter-form" method="GET">
                                <div class="collapse show" id="filterCollapse">
                                    <div class="row g-1">
                                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                            <label for="date_range_type" class="form-label">Date Type</label>
                                            <select name="date_range_type" id="date_range_type" class="form-select form-select-sm">
                                                <option value="submission" {{ $filters['date_range_type'] == 'submission' ? 'selected' : '' }}>Submission</option>
                                                <option value="approval" {{ $filters['date_range_type'] == 'approval' ? 'selected' : '' }}>Approval</option>
                                                <option value="appointment" {{ $filters['date_range_type'] == 'appointment' ? 'selected' : '' }}>Appointment</option>
                                            </select>
                                        </div>
                                        @if ($access_level == 'bu' || $access_level == 'all')
                                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                            <label for="bu" class="form-label">Business Unit</label>
                                            <select name="bu" id="bu" class="form-select form-select-sm">
                                                <option value="All">All BU</option>
                                                @foreach ($bu_list as $key => $value)
                                                <option value="{{ $key }}" {{ $filters['bu'] == $key ? 'selected' : '' }}>{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif
                                        @if ($access_level == 'bu' || $access_level == 'zone' || $access_level == 'all')
                                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
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
                                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
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
                                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                            <label for="territory" class="form-label">Territory</label>
                                            <select name="territory" id="territory" class="form-select form-select-sm">
                                                <option value="All">All Territory</option>
                                                @foreach ($territory_list as $key => $value)
                                                <option value="{{ $key }}" {{ $filters['territory'] == $key ? 'selected' : '' }}>{{ $value }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif
                                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                            <label class="form-label">Initiator</label>
                                            <select name="initiator_role" id="initiator_role" class="form-select form-select-sm">
                                                <option value="">All</option>
                                                <option value="TM" {{ $filters['initiator_role'] == 'TM' ? 'selected' : '' }}>TM</option>
                                                <option value="Area Coordinator" {{ $filters['initiator_role'] == 'Area Coordinator' ? 'selected' : '' }}>Area Coordinator</option>
                                                <option value="Regional Business Manager" {{ $filters['initiator_role'] == 'Regional Business Manager' ? 'selected' : '' }}>Regional Business Manager</option>
                                                <option value="Zonal Business Manager" {{ $filters['initiator_role'] == 'Zonal Business Manager' ? 'selected' : '' }}>Zonal Business Manager</option>
                                            </select>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                            <label class="form-label">Stage</label>
                                            <select name="approval_stage" id="approval_stage" class="form-select form-select-sm">
                                                <option value="">All</option>
                                                <option value="initiated" {{ $filters['approval_stage'] == 'initiated' ? 'selected' : '' }}>Initiated</option>
                                                <option value="Regional Business Manager" {{ $filters['approval_stage'] == 'Regional Business Manager' ? 'selected' : '' }}>Regional Business Manager</option>
                                                <option value="Zonal Business Manager" {{ $filters['approval_stage'] == 'Zonal Business Manager' ? 'selected' : '' }}>Zonal Business Manager</option>
                                                <option value="General Manager" {{ $filters['approval_stage'] == 'General Manager' ? 'selected' : '' }}>General Manager</option>
                                                <option value="mis" {{ $filters['approval_stage'] == 'mis' ? 'selected' : '' }}>MIS</option>
                                                <option value="completed" {{ $filters['approval_stage'] == 'completed' ? 'selected' : '' }}>Final</option>
                                            </select>
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
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
                                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                            <label class="form-label">From</label>
                                            <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] }}">
                                        </div>
                                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                            <label class="form-label">To</label>
                                            <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] }}">
                                        </div>
                                        <div class="col-12 mt-1 d-flex justify-content-end gap-1">
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

            <div class="row mb-2">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title">Key Performance Indicators</h4>
                            <div class="row g-2" id="kpi-container">
                                @if ($data['counts']['total'] == 0)
                                <div class="col-12 no-data-message">No applications found based on current filters.</div>
                                @else
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    <div class="card kpi-card">
                                        <div class="card-body">
                                            <h6 class="small">Total Forms</h6>
                                            <div class="kpi-value" id="kpi-total-submitted">{{ $data['counts']['total'] }}</div>
                                            <span class="kpi-trend-up" id="kpi-trend-total-submitted">🔼 {{ $data['kpi_trends']['total_submitted'] }}%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    <div class="card kpi-card">
                                        <div class="card-body">
                                            <h6 class="small">Avg. TAT</h6>
                                            <div class="kpi-value" id="kpi-avg-tat">{{ $tatData['total']['avg_tat'] }} Days</div>
                                            <span class="kpi-trend-down" id="kpi-trend-avg-tat">⏬ {{ $data['kpi_trends']['avg_tat'] }} Days</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    <div class="card kpi-card">
                                        <div class="card-body">
                                            <h6 class="small">Appointments</h6>
                                            <div class="kpi-value" id="kpi-appointments-completed">{{ $data['counts']['distributors_created'] }}</div>
                                            <span class="kpi-trend-up" id="kpi-trend-appointments-completed">🔼 {{ $data['kpi_trends']['appointments_completed'] }}%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    <div class="card kpi-card">
                                        <div class="card-body">
                                            <h6 class="small">In Process</h6>
                                            <div class="kpi-value" id="kpi-in-process">{{ $data['counts']['in_process'] }}</div>
                                            <span class="kpi-trend-neutral" id="kpi-trend-in-process">—</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    <div class="card kpi-card">
                                        <div class="card-body">
                                            <h6 class="small">Reverted</h6>
                                            <div class="kpi-value" id="kpi-reverted">{{ $data['counts']['reverted'] }}</div>
                                            <span class="kpi-trend-up" id="kpi-trend-reverted">🔼 {{ $data['kpi_trends']['reverted'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    <div class="card kpi-card">
                                        <div class="card-body">
                                            <h6 class="small">Rejected</h6>
                                            <div class="kpi-value" id="kpi-rejected">{{ $data['counts']['rejected'] }}</div>
                                            <span class="kpi-trend-down" id="kpi-trend-rejected">🔽 {{ $data['kpi_trends']['rejected'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
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
            </div>

            <div class="row mb-2">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-tabs nav-tabs-custom mb-2">
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
                                @if(Auth::check() && Auth::user()->employee && Auth::user()->employee->isMisTeam())
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#mis-tab">
                                        MIS (<span id="tab-mis-count">{{ $data['counts']['mis'] }}</span>)
                                    </a>
                                </li>
                                @endif
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#master-tab">
                                        Master (<span id="tab-master-count">{{ $data['counts']['total'] }}</span>)
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
                                @if(Auth::check() && Auth::user()->employee && Auth::user()->employee->isMisTeam())
                                <div class="tab-pane fade" id="mis-tab">
                                    @if ($misApplications->isEmpty())
                                    <div class="no-data-message">No applications pending MIS processing.</div>
                                    @else
                                    @include('dashboard._mis-table')
                                    @endif
                                </div>
                                @endif
                                <div class="tab-pane fade" id="master-tab">
                                    <div class="row">
                                        <div class="col-12">
                                            <h5 class="header-title">Turnaround Time (TAT) Analysis</h5>
                                            @if ($tatData['total']['avg_tat'] == 0 && $tatData['total']['max_tat'] == 0)
                                            <div class="no-data-message">No TAT data available.</div>
                                            @else
                                            <div class="table-responsive">
                                                <table class="table table-sm tat-table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Stage</th>
                                                            <th>Avg. TAT</th>
                                                            <th>Max TAT</th>
                                                            <th>Exceeding SLA</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tat-table-body">
                                                        <tr>
                                                            <td>Sub → RBM</td>
                                                            <td id="tat-submission-to-rbm-avg">{{ $tatData['submission_to_rbm']['avg_tat'] }} Days</td>
                                                            <td id="tat-submission-to-rbm-max">{{ $tatData['submission_to_rbm']['max_tat'] }} Days</td>
                                                            <td id="tat-submission-to-rbm-exceeding">{{ $tatData['submission_to_rbm']['exceeding_sla'] }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>RBM → ZBM</td>
                                                            <td id="tat-rbm-to-zbm-avg">{{ $tatData['rbm_to_zbm']['avg_tat'] }} Days</td>
                                                            <td id="tat-rbm-to-zbm-max">{{ $tatData['rbm_to_zbm']['max_tat'] }} Days</td>
                                                            <td id="tat-rbm-to-zbm-exceeding">{{ $tatData['rbm_to_zbm']['exceeding_sla'] }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>ZBM → GM</td>
                                                            <td id="tat-zbm-to-gm-avg">{{ $tatData['zbm_to_gm']['avg_tat'] }} Days</td>
                                                            <td id="tat-zbm-to-gm-max">{{ $tatData['zbm_to_gm']['max_tat'] }} Days</td>
                                                            <td id="tat-zbm-to-gm-exceeding">{{ $tatData['zbm_to_gm']['exceeding_sla'] }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>GM → MIS</td>
                                                            <td id="tat-gm-to-mis-avg">{{ $tatData['gm_to_mis']['avg_tat'] }} Days</td>
                                                            <td id="tat-gm-to-mis-max">{{ $tatData['gm_to_mis']['max_tat'] }} Days</td>
                                                            <td id="tat-gm-to-mis-exceeding">{{ $tatData['gm_to_mis']['exceeding_sla'] }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td>MIS → Final</td>
                                                            <td id="tat-mis-to-final-avg">{{ $tatData['mis_to_final']['avg_tat'] }} Days</td>
                                                            <td id="tat-mis-to-final-max">{{ $tatData['mis_to_final']['max_tat'] }} Days</td>
                                                            <td id="tat-mis-to-final-exceeding">{{ $tatData['mis_to_final']['exceeding_sla'] }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Total</strong></td>
                                                            <td id="tat-total-avg"><strong>{{ $tatData['total']['avg_tat'] }} Days</strong></td>
                                                            <td id="tat-total-max"><strong>{{ $tatData['total']['max_tat'] }} Days</strong></td>
                                                            <td id="tat-total-exceeding"><strong>{{ $tatData['total']['exceeding_sla'] }}</strong></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            @endif
                                            <h5 class="header-title mt-2">Applications</h5>
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
</div>
@else
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Welcome Home</div>
                <div class="card-body">
                    <p>Now you can fill your vendor registration form</p>
                    {{--<a href="{{ url('/') }}" class="btn btn-primary">Return Home</a>--}}
                </div>
            </div>
        </div>
    </div>
</div>
@endif


@endsection
@push('scripts')
<script>
    const loader = $("#elmLoader");
    let isUpdating = false; // Flag to prevent concurrent updates

    $(document).ready(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();

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


        // Modal handler for all actions
        const actionMap = {
            revertModal: {
                form: '#revert-form',
                input: '#revert_application_id',
                action: 'revert'
            },
            holdModal: {
                form: '#hold-form',
                input: '#hold_application_id',
                action: 'hold'
            },
            rejectModal: {
                form: '#reject-form',
                input: '#reject_application_id',
                action: 'reject'
            },
            approveModal: {
                form: '#approve-form',
                input: '#approve_application_id',
                action: 'approve'
            }
        };

        $(document).on('show.bs.modal', '.action-modal', function(event) {
            const button = $(event.relatedTarget);
            const applicationId = button.data('application-id');
            const modalId = $(this).attr('id');
            const config = actionMap[modalId];

            console.log('Modal opened:', modalId);
            console.log('Button data:', button.data());
            console.log('Application ID:', applicationId);
            console.log('Config:', config);

            if (config) {
                $(config.input).val(applicationId);
                const url = `{{ url('approvals') }}/${applicationId}/${config.action}`;
                $(config.form).attr('action', url);
                console.log('Form action set to:', url);
                console.log('Application ID input set to:', $(config.input).val());
            } else {
                console.error('No config found for modal:', modalId);
            }
        });

        // Disable submit buttons on form submit
        $('#revert-form, #hold-form, #reject-form, #approve-form').on('submit', function() {
            $(this).find('button[type="submit"]').prop('disabled', true).text('Processing...');
            $(this).find('.btn-close').prop('disabled', true);
        });

        // Expandable panel for MIS table rows
        $(document).on('click', '.expandable-panel', function() {
            $(this).next('.panel-content').toggle();
        });
    });

    function updateDashboard() {
        if (isUpdating) return; // Prevent concurrent calls
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
                console.log("Received data:", data); // Debugging line

                if (!data || !data.counts || !data.tat || !data.kpi_trends) {
                    console.error('Invalid response structure:', data);
                    $('#kpi-container').html('<div class="col-12 no-data-message">Error: Incomplete data structure from server.</div>');
                    $('#tat-table-body').html('<tr><td colspan="4" class="no-data-message">No TAT data available.</td></tr>');
                    $('#master-table-container').html('<div class="no-data-message">No applications found.</div>');
                    isUpdating = false;
                    return;
                }

                // Update KPI values dynamically
                const kpiContainer = $('#kpi-container');
                if (data.counts.total === 0) {
                    kpiContainer.html('<div class="col-12 no-data-message">No applications found based on current filters.</div>');
                } else {
                    kpiContainer.html(`
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                            <div class="card kpi-card">
                                <div class="card-body">
                                    <h6 class="small">Total Forms</h6>
                                    <div class="kpi-value" id="kpi-total-submitted">${data.counts.total || 0}</div>
                                    <span class="kpi-trend-up" id="kpi-trend-total-submitted">🔼 ${data.kpi_trends.total_submitted || 0}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                            <div class="card kpi-card">
                                <div class="card-body">
                                    <h6 class="small">Avg. TAT</h6>
                                    <div class="kpi-value" id="kpi-avg-tat">${data.tat.total?.avg_tat || 0} Days</div>
                                    <span class="kpi-trend-down" id="kpi-trend-avg-tat">⏬ ${data.kpi_trends.avg_tat || 0} Days</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                            <div class="card kpi-card">
                                <div class="card-body">
                                    <h6 class="small">Appointments</h6>
                                    <div class="kpi-value" id="kpi-appointments-completed">${data.counts.distributors_created || 0}</div>
                                    <span class="kpi-trend-up" id="kpi-trend-appointments-completed">🔼 ${data.kpi_trends.appointments_completed || 0}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                            <div class="card kpi-card">
                                <div class="card-body">
                                    <h6 class="small">In Process</h6>
                                    <div class="kpi-value" id="kpi-in-process">${data.counts.in_process || 0}</div>
                                    <span class="kpi-trend-neutral" id="kpi-trend-in-process">—</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                            <div class="card kpi-card">
                                <div class="card-body">
                                    <h6 class="small">Reverted</h6>
                                    <div class="kpi-value" id="kpi-reverted">${data.counts.reverted || 0}</div>
                                    <span class="kpi-trend-up" id="kpi-trend-reverted">🔼 ${data.kpi_trends.reverted || 0}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                            <div class="card kpi-card">
                                <div class="card-body">
                                    <h6 class="small">Rejected</h6>
                                    <div class="kpi-value" id="kpi-rejected">${data.counts.rejected || 0}</div>
                                    <span class="kpi-trend-down" id="kpi-trend-rejected">🔽 ${data.kpi_trends.rejected || 0}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
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
                @if(Auth::check() && Auth::user()->employee && Auth::user()->employee->isMisTeam())
                $('#tab-mis-count').text(data.counts.mis || 0);
                @endif

                // Update TAT table
                const tatTableBody = $('#tat-table-body');
                if (data.tat.total.avg_tat === 0 && data.tat.total.max_tat === 0) {
                    tatTableBody.html('<tr><td colspan="4" class="no-data-message">No TAT data available.</td></tr>');
                } else {
                    tatTableBody.html(`
                        <tr>
                            <td>Sub → RBM</td>
                            <td id="tat-submission-to-rbm-avg">${data.tat.submission_to_rbm?.avg_tat || 0} Days</td>
                            <td id="tat-submission-to-rbm-max">${data.tat.submission_to_rbm?.max_tat || 0} Days</td>
                            <td id="tat-submission-to-rbm-exceeding">${data.tat.submission_to_rbm?.exceeding_sla || 0}</td>
                        </tr>
                        <tr>
                            <td>RBM → ZBM</td>
                            <td id="tat-rbm-to-zbm-avg">${data.tat.rbm_to_zbm?.avg_tat || 0} Days</td>
                            <td id="tat-rbm-to-zbm-max">${data.tat.rbm_to_zbm?.max_tat || 0} Days</td>
                            <td id="tat-rbm-to-zbm-exceeding">${data.tat.rbm_to_zbm?.exceeding_sla || 0}</td>
                        </tr>
                        <tr>
                            <td>ZBM → GM</td>
                            <td id="tat-zbm-to-gm-avg">${data.tat.zbm_to_gm?.avg_tat || 0} Days</td>
                            <td id="tat-zbm-to-gm-max">${data.tat.zbm_to_gm?.max_tat || 0} Days</td>
                            <td id="tat-zbm-to-gm-exceeding">${data.tat.zbm_to_gm?.exceeding_sla || 0}</td>
                        </tr>
                        <tr>
                            <td>GM → MIS</td>
                            <td id="tat-gm-to-mis-avg">${data.tat.gm_to_mis?.avg_tat || 0} Days</td>
                            <td id="tat-gm-to-mis-max">${data.tat.gm_to_mis?.max_tat || 0} Days</td>
                            <td id="tat-gm-to-mis-exceeding">${data.tat.gm_to_mis?.exceeding_sla || 0}</td>
                        </tr>
                        <tr>
                            <td>MIS → Final</td>
                            <td id="tat-mis-to-final-avg">${data.tat.mis_to_final?.avg_tat || 0} Days</td>
                            <td id="tat-mis-to-final-max">${data.tat.mis_to_final?.max_tat || 0} Days</td>
                            <td id="tat-mis-to-final-exceeding">${data.tat.mis_to_final?.exceeding_sla || 0}</td>
                        </tr>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td id="tat-total-avg"><strong>${data.tat.total?.avg_tat || 0} Days</strong></td>
                            <td id="tat-total-max"><strong>${data.tat.total?.max_tat || 0} Days</strong></td>
                            <td id="tat-total-exceeding"><strong>${data.tat.total?.exceeding_sla || 0}</strong></td>
                        </tr>
                    `);
                }

                // Update Master Report table
                $('#master-table-container').html(data.master_table_html || '<div class="no-data-message">No applications found.</div>');

                // Re-initialize tooltips for newly loaded content
                $('[data-bs-toggle="tooltip"]').tooltip('dispose'); // Destroy existing tooltips
                $('[data-bs-toggle="tooltip"]').tooltip(); // Initialize new ones
                isUpdating = false;
            },
            error: function(xhr) {
                loader.addClass('d-none');
                console.error('Error fetching dashboard data:', xhr.responseText);
                $('#kpi-container').html('<div class="col-12 no-data-message">Error loading data. Please try again.</div>');
                $('#tat-table-body').html('<tr><td colspan="4" class="no-data-message">Error loading TAT data.</td></tr>');
                $('#master-table-container').html('<div class="no-data-message">Error loading applications.</div>');
                isUpdating = false;
            }
        });
    }

    function getZoneByBU(bu) {
        const zoneSelect = $('#zone');
        $.ajax({
            url: "{{ route('get_zone_by_bu') }}",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                bu: bu
            },
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
                // Only trigger dashboard update if BU changed and zone list is populated
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
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                zone: zone
            },
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
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                region: region
            },
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