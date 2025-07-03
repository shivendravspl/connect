@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0">Distributors</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                        <li class="breadcrumb-item active">Distributor List</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Card with Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Distributor Records</h5>
                    <div class="hstack gap-2">
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="offcanvas" href="#FilterCanvas">
                            <i class="ri-filter-3-line align-bottom me-1"></i> Filters
                        </button>
                        <a href="{{ route('distributor.export') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel me-1"></i> Export Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle table-nowrap mb-0" id="distributor-table">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>VC Territory</th>
                                    <th>FC Territory</th>
                                    <th>Bulk Territory</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="noresult" style="display: none;">
                        <div class="text-center">
                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop" colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px"></lord-icon>
                            <h5 class="mt-2">Sorry! No Result Found</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="FilterCanvas" aria-labelledby="FilterCanvasLabel">
        <div class="offcanvas-header bg-light">
            <h5 class="offcanvas-title" id="FilterCanvasLabel">Distributor Filters</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <form id="filter-form" class="d-flex flex-column justify-content-end h-100">
            <div class="offcanvas-body">
                <div class="col-12">
                    <label for="status_filter" class="form-label">Status:</label>
                    <select name="status" id="status_filter" class="form-control form-select form-select-sm" onchange="filter_data();">
                        <option value="">Select Status</option>
                        <option value="A">Active</option>
                        <option value="D">Deactive</option>
                    </select>
                </div>
                <div class="col-12 mt-3">
                    <label for="business_type_filter" class="form-label">Business Type:</label>
                    <select name="business_type" id="business_type_filter" class="form-control form-select form-select-sm" onchange="filter_data();">
                        <option value="">Select</option>
                        @foreach ($business_type as $row)
                        <option value="{{ $row->id }}">{{ $row->business_type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 mt-3">
                    <label for="vc_territory_filter" class="form-label">VC Territory:</label>
                    <select name="vc_territory" id="vc_territory_filter" class="form-control form-select form-select-sm" onchange="filter_data();">
                        <option value="">Select</option>
                        @foreach($vc_territory_list as $row)
                        <option value="{{ $row->id }}">{{ $row->territory_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 mt-3">
                    <label for="fc_territory_filter" class="form-label">FC Territory:</label>
                    <select name="fc_territory" id="fc_territory_filter" class="form-control form-select form-select-sm" onchange="filter_data();">
                        <option value="">Select</option>
                        @foreach($fc_territory_list as $row)
                        <option value="{{ $row->id }}">{{ $row->territory_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="offcanvas-footer border-top p-3 text-center">
                <div class="row">
                    <div class="col-6">
                        <button type="button" class="btn btn-primary btn-sm w-100" id="apply-btn">Apply Filters</button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-light btn-sm w-100" id="reset-btn">Reset</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    #distributor-table.dataTable {
        font-size: 0.82rem;
    }

    #distributor-table.dataTable thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background-color: #f3f6f9;
        color: #333;
    }

    #distributor-table.dataTable tbody td {
        font-size: 0.82rem;
        vertical-align: middle;
    }

    #distributor-table .badge {
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

    #distributor-table.dataTable tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    #distributor-table.dataTable tbody tr {
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
    }

    #distributor-table.dataTable tbody tr:hover {
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded');
    } else {
        console.log('jQuery loaded, version:', jQuery.fn.jquery);
    }

    $(document).ready(function($) {
        console.log('Document ready: Initializing DataTable');

        // Initialize DataTable
        let table;
        try {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            table = $('#distributor-table').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                responsive: true,
                pageLength: 10,
                destroy: true,
                lengthMenu: [
                    [5, 10, 25, 50, 100],
                    [5, 10, 25, 50, 100]
                ],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                ajax: {
                    url: "{{ route('getDistributorList') }}",
                    type: 'POST',
                    data: function(d) {
                        d.status = $('#status_filter').val();
                        d.business_type = $('#business_type_filter').val();
                        d.vc_territory = $('#vc_territory_filter').val();
                        d.fc_territory = $('#fc_territory_filter').val();
                        console.log('AJAX data:', d);
                    },
                    beforeSend: function() {
                        console.log('Sending AJAX request to getDistributorList');
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTable AJAX error:', xhr.status, error, thrown, xhr.responseText);
                        let message = 'Failed to load distributors. Check console for details.';
                        if (xhr.status === 403) message = 'You do not have permission to view distributors.';
                        else if (xhr.status === 419) {
                            message = 'Session expired. Reloading page...';
                            setTimeout(() => window.location.reload(), 2000);
                        } else if (xhr.status === 500) message = 'Server error. Please contact support.';
                        toastr.error(message);
                    }
                },
                columns: [{
                        data: 'id',
                        name: 'core_distributor.id',
                        className: 'text-center'
                    },
                    {
                        data: 'name',
                        name: 'core_distributor.name'
                    },
                    {
                        data: 'phone',
                        name: 'core_distributor.phone',
                        className: 'text-center'
                    },
                    {
                        data: 'vc_territory_name',
                        name: 'vc.territory_name'
                    },
                    {
                        data: 'fc_territory_name',
                        name: 'fc.territory_name'
                    },
                    {
                        data: 'bulk_territory_name',
                        name: 'bc.territory_name'
                    },
                    {
                        data: 'status',
                        name: 'core_distributor.status',
                        className: 'text-center',
                        render: function(data) {
                            let badgeClass = data === 'A' ? 'success' : 'danger';
                            let displayText = data === 'A' ? 'Active' : 'Deactive';
                            return `<span class="badge bg-${badgeClass}">${displayText}</span>`;
                        }
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                drawCallback: function() {
                    console.log('DataTable drawn');
                    $('.noresult').toggle(table.rows({
                        search: 'applied'
                    }).count() === 0);
                }
            });
            console.log('DataTable initialized');
        } catch (e) {
            console.error('DataTable initialization error:', e);
            toastr.error('Failed to initialize table. Check console.');
        }

        // Apply filters
        $('#apply-btn').on('click', function() {
            table.ajax.reload();
            $('#FilterCanvas').offcanvas('hide');
        });



        // Reset filters
        $('#reset-btn').on('click', function() {
            $('#filter-form')[0].reset();
            table.ajax.reload();
            $('#FilterCanvas').offcanvas('hide');
        });
    });

    function filter_data() {
        $("#distributor-table").DataTable().ajax.reload();
    }
</script>
@endpush