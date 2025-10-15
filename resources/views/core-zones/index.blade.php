@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0">Zones</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Pages</a></li>
                        <li class="breadcrumb-item active">Zone List</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Zone Records</h5>
                    <div>
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="offcanvas" href="#FilterCanvas">
                            <i class="ri-filter-3-line align-bottom me-1"></i> Filters
                        </button>
                        <a href="{{ route('zones.export') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i> Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-nowrap mb-0" id="zone-table">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Zone Name</th>
                                    <th>Zone Code</th>
                                    <th>Numeric Code</th>
                                    <th>Effective Date</th>
                                    <th>Status</th>
                                    <th>Vertical Name</th>
                                    <th>Business Type</th>
                                </tr>
                            </thead>
                            <tbody></tbody> <!-- AJAX data -->
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="FilterCanvas" aria-labelledby="FilterCanvasLabel">
        <div class="offcanvas-header bg-light">
            <h5 class="offcanvas-title" id="FilterCanvasLabel">Zone Filters</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <form id="filter-form" class="d-flex flex-column justify-content-end h-100">
            <div class="offcanvas-body">
                <div class="col-lg-12">
                    <label for="status_filter" class="form-label">Status:</label>
                    <select name="status" id="status_filter" class="form-control form-select" onchange="filter_data();">
                        <option value="">Select Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-lg-12 mt-3">
                    <label for="vertical_filter" class="form-label">Vertical:</label>
                    <select name="vertical_id" id="vertical_filter" class="form-control form-select" onchange="filter_data();">
                        <option value="">Select Vertical</option>
                        @foreach ($verticals as $vertical)
                        <option value="{{ $vertical->id }}">{{ $vertical->vertical_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-12 mt-3">
                    <label for="business_type_filter" class="form-label">Business Type:</label>
                    <select name="business_type" id="business_type_filter" class="form-control form-select" onchange="filter_data();">
                        <option value="">Select Business Type</option>
                        @foreach ($business_types as $business_type)
                        <option value="{{ $business_type->id }}">{{ $business_type->business_type }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="offcanvas-footer border-top p-3 text-center">
                <div class="row">
                    <div class="col-6">
                        <button type="button" class="btn btn-primary w-100" id="apply-btn">Apply Filters</button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-light w-100" id="reset-btn">Reset</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    #zone-table.dataTable {
        font-size: 0.82rem;
    }

    #zone-table.dataTable thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background-color: #f3f6f9;
        color: #333;
    }

    #zone-table.dataTable tbody td {
        font-size: 0.82rem;
        vertical-align: middle;
    }

    #zone-table .badge {
        font-size: 0.68rem;
        padding: 0.3em 0.6em;
    }

    .card {
        border: none;
        box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, 0.03);
        border-radius: 0.5rem;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, .125);
        padding: 1rem 1.5rem;
    }

    #zone-table.dataTable tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    #zone-table.dataTable tbody tr {
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
    }

    #zone-table.dataTable tbody tr:hover {
        background-color: rgba(70, 127, 207, 0.05);
        border-left-color: #467fcf;
    }

    .btn-sm {
        padding: 0.4rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1;
        min-width: 32px;
    }

    .btn-sm i {
        font-size: 0.8rem;
    }

    .form-select-sm {
        font-size: 0.82rem;
    }
</style>
@endpush

@push('scripts')
<script>
    function filter_data() {
        $('#zone-table').DataTable().ajax.reload();
    }

    $(document).ready(function() {
        console.log('Document ready: Initializing DataTable');

        // Initialize DataTable
        const table = $('#zone-table').DataTable({
            processing: true,
            serverSide: true,
            searching: true,
            destroy: true,
            ajax: {
                url: "{{ route('zones.getZoneList') }}",
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: function(d) {
                    d.status = $('#status_filter').val();
                    d.vertical_id = $('#vertical_filter').val();
                    d.business_type = $('#business_type_filter').val();
                },
                error: function(xhr, error, thrown) {
                    if (xhr.status === 419) {
                        alert('Session expired or CSRF token mismatch. Please refresh the page.');
                        window.location.reload();
                    } else {
                        console.error('DataTable error:', error, thrown);
                    }
                }
            },
            columns: [{
                    data: 'id',
                    name: 'id'
                },
                {
                    data: 'zone_name',
                    name: 'zone_name'
                },
                {
                    data: 'zone_code',
                    name: 'zone_code'
                },
                {
                    data: 'numeric_code',
                    name: 'numeric_code'
                },
                {
                    data: 'effective_date',
                    name: 'effective_date'
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    render: function(data) {
                        let badgeClass = data === 'Active' ? 'success' : 'danger';
                        return `<span class="badge bg-${badgeClass}">${data}</span>`;
                    }
                },
                {
                    data: 'vertical_name',
                    name: 'core_vertical.vertical_name'
                },
                {
                    data: 'business_type',
                    name: 'core_business_type.business_type'
                }
            ]
        });

        // Apply filters on button click
        $('#apply-btn').on('click', function() {
            filter_data();
            $('#FilterCanvas').offcanvas('hide');
        });

        // Reset filters
        $('#reset-btn').on('click', function() {
            $('#status_filter, #vertical_filter, #business_type_filter').val('');
            filter_data();
            $('#FilterCanvas').offcanvas('hide');
        });
    });
</script>
@endpush