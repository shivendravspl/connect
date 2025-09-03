@extends('layouts.app')

@section('content')
<div class="container-fluid px-2 px-sm-3">
    <div class="row justify-content-center">
        <div class="col-12 px-0 px-sm-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-1 px-2">
                    <h5 class="mb-0 small font-weight-bold">My Applications</h5>
                    <div class="d-flex align-items-center" style="gap: 0.5rem;">
                        <!-- Filters -->
                        <div class="d-flex align-items-center" style="gap: 0.5rem;">
                            <div>
                                <select id="territory_filter" class="form-select form-select-sm">
                                    <option value="">All Territories</option>
                                    @foreach($territories as $territory)
                                    <option value="{{ $territory->id }}">{{ $territory->territory_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <select id="status_filter" class="form-select form-select-sm">
                                    <option value="">All Statuses</option>
                                    @foreach($statuses as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- New Button -->
                        @if(auth()->user()->emp_id && auth()->user()->hasAnyRole(['Admin', 'Super Admin', 'Mis User']))
                        <a href="{{ route('applications.create') }}" class="btn btn-sm btn-primary py-1 px-3 fs-6">
                            <i class="fas fa-plus fa-sm"></i> <span class="d-none d-sm-inline">New</span>
                        </a>
                        @endif
                    </div>
                </div>

                <div class="card-body p-1 p-sm-2">
                    <!-- DataTable -->
                    <div class="table-responsive">
                        <table id="applicationsTable" class="table table-sm table-hover mb-1 small" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="py-1 px-1 text-center">S.No</th>
                                    <th class="py-1 px-1">App ID</th>
                                    <th class="py-1 px-1">Distributor</th>
                                    <th class="py-1 px-1 d-none d-sm-table-cell">Territory</th>
                                    <th class="py-1 px-1">Status</th>
                                    <th class="py-1 px-1 d-none d-md-table-cell">Submitted</th>
                                    <th class="py-1 px-1 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- DataTables CSS and JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    // Set CSRF token for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    });

    var table = $('#applicationsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("applications.datatable") }}',
            type: 'POST',
            data: function(d) {
                // Ensure empty strings instead of null
                d.territory = $('#territory_filter').val() ? $('#territory_filter').val() : '';
                d.status = $('#status_filter').val() ? $('#status_filter').val() : '';
                console.log('Sending AJAX Data:', {
                    territory: d.territory,
                    status: d.status,
                    draw: d.draw,
                    start: d.start,
                    length: d.length,
                    search: d.search.value
                });
                return d;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX Error:', {
                    status: xhr.status,
                    error: error,
                    response: xhr.responseText
                });
                alert('Error loading data: ' + (xhr.responseJSON?.error || error));
            },
            dataSrc: function(json) {
                console.log('Received AJAX Response:', json);
                if (json.error) {
                    alert('Server Error: ' + json.error);
                    return [];
                }
                return json.data;
            }
        },
        columns: [
            { data: 's_no', name: 's_no', searchable: false, orderable: false },
            { data: 'application_code', name: 'application_code' },
            { data: 'distributor', name: 'distributor' },
            { data: 'territory', name: 'territory', visible: window.innerWidth >= 576 },
            { data: 'status', name: 'status' },
            { data: 'created_at', name: 'created_at', visible: window.innerWidth >= 768 },
            { data: 'actions', name: 'actions', searchable: false, orderable: false }
        ],
        order: [[5, 'desc']], // Default sort by created_at (Submitted) descending
        pageLength: 10,
        responsive: true,
        language: {
            emptyTable: "You haven't submitted any applications yet.",
            zeroRecords: "No matching records found"
        },
        columnDefs: [
            { className: 'py-1 px-1 text-center', targets: [0, 6] },
            { className: 'py-1 px-1', targets: '_all' },
            {
                targets: 5, // created_at column
                render: function(data, type, row) {
                    return type === 'sort' ? new Date(data).getTime() : data;
                }
            }
        ]
    });

    // Refresh table on filter change
    $('#territory_filter, #status_filter').on('change', function() {
        console.log('Filter Changed:', {
            territory: $('#territory_filter').val() || '',
            status: $('#status_filter').val() || ''
        });
        table.draw();
    });
});
</script>

<style>
    .btn-action {
        width: 22px;
        height: 22px;
        border-radius: 3px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    
    .btn-action:hover {
        transform: scale(1.1);
        opacity: 0.9;
    }

    .fs-10 {
        font-size: 10px;
    }

    .small {
        font-size: 0.75rem;
    }

    .table-sm td,
    .table-sm th {
        padding: 0.3rem;
    }

    .card {
        border-radius: 0.25rem;
    }

    .card-header {
        padding: 0.5rem 0.75rem;
    }

    .form-select-sm {
        padding: 0.2rem 0.5rem;
        font-size: 0.75rem;
        height: 28px;
    }

    @media (max-width: 576px) {
        .container-fluid {
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }

        .card-body {
            padding: 0.25rem;
        }

        .table td,
        .table th {
            white-space: nowrap;
        }
        
        .btn-action {
            width: 20px;
            height: 20px;
        }
        
        .table-responsive th:nth-child(1),
        .table-responsive td:nth-child(1) {
            display: none;
        }
        
        .table-responsive th:nth-child(2),
        .table-responsive td:nth-child(2) {
            padding-left: 0.5rem;
        }

        .form-select-sm {
            font-size: 0.7rem;
            height: 26px;
        }
    }
</style>
@endpush
@endsection