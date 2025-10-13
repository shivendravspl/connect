@extends('layouts.app')

@section('content')
<div class="container-fluid px-2 px-sm-3">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0">Onboarding</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Pages</a></li>
                        <li class="breadcrumb-item active">My Applications</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row justify-content-center">
        <div class="col-12 px-0 px-sm-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-1 px-2">
                    <h5 class="card-title mb-0">My Applications</h5>
                    <div class="d-flex align-items-center" style="gap: 0.5rem;">
                        <!-- Filters -->
                        <div class="d-flex align-items-center" style="gap: 0.5rem;">
                            <div class="filter-container">
                                <select id="territory_filter" class="select2-filter">
                                    <option value="">All Territories</option>
                                    @foreach($territories as $territory)
                                    <option value="{{ $territory->id }}">{{ $territory->territory_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="filter-container">
                                <select id="status_filter" class="select2-filter">
                                    <option value="">All Statuses</option>
                                    @foreach($statuses as $status)
                                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- New Button -->
                        @if((auth()->user()->emp_id && auth()->user()->hasAnyRole(['Admin', 'Super Admin', 'Mis User'])) || auth()->user()->hasPermissionTo('add-distributor'))
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

<script>
$(document).ready(function() {
    // Initialize Select2 for filters
    $('.select2-filter').select2({
        theme: 'bootstrap4',
        width: '100%', // Full width of container
        minimumResultsForSearch: 10,
        placeholder: function() {
            return $(this).find('option:first').text();
        },
        allowClear: true,
        dropdownCssClass: 'custom-select2-dropdown',
        escapeMarkup: function (markup) { return markup; }
    });

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
                d.territory = $('#territory_filter').val() || '';
                d.status = $('#status_filter').val() || '';
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
        order: [[5, 'desc']],
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
                targets: 5,
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

    // Ensure Select2 dropdowns stay within card boundaries and scroll internally
    $('.select2-filter').on('select2:open', function() {
        setTimeout(() => {
            const dropdown = $('.custom-select2-dropdown');
            dropdown.css({
                'z-index': 1050, // Above card
                'max-width': $(this).parent().width(),
                'max-height': '300px', // Limit height to prevent full page scroll
                'overflow-y': 'auto' // Enable internal scrolling
            });

            // Also ensure the results container scrolls if needed
            dropdown.find('.select2-results__options').css({
                'max-height': '250px',
                'overflow-y': 'auto'
            });
        }, 0);
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
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .card-header {
        padding: 0.5rem 0.75rem;
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .filter-container {
        min-width: 120px;
        max-width: 200px;
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-container--bootstrap4 .select2-selection--single {
        height: 28px !important;
        line-height: 1.5 !important;
        padding: 0.25rem 0.5rem !important;
        font-size: 0.75rem !important;
        border: 1px solid #ced4da;
        border-radius: 0.2rem;
        background-color: #fff;
        display: flex;
        align-items: center;
    }

    .select2-container--bootstrap4 .select2-selection__rendered {
        color: #495057;
        padding-left: 0;
    }

    .select2-container--bootstrap4 .select2-selection__arrow {
        height: 28px !important;
        right: 5px;
    }

    .select2-container--bootstrap4 .select2-selection__clear {
        margin-right: 10px;
        color: #6c757d;
    }

    .select2-container--bootstrap4 .select2-selection--single:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }

    .custom-select2-dropdown .select2-results__option {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .custom-select2-dropdown .select2-results__option--highlighted {
        background-color: #007bff !important;
        color: #fff !important;
    }

    /* Ensure dropdown scrolls internally and doesn't affect page scroll */
    .custom-select2-dropdown {
        max-height: 300px !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
    }

    .custom-select2-dropdown .select2-results__options {
        max-height: 250px !important;
        overflow-y: auto !important;
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

        .filter-container {
            min-width: 100px;
            max-width: 150px;
        }

        .select2-container--bootstrap4 .select2-selection--single {
            font-size: 0.7rem !important;
            height: 26px !important;
            padding: 0.2rem 0.4rem !important;
        }

        .select2-container--bootstrap4 .select2-selection__arrow {
            height: 26px !important;
        }

        .custom-select2-dropdown .select2-results__option {
            font-size: 0.7rem;
        }

        /* Adjust dropdown height for mobile */
        .custom-select2-dropdown {
            max-height: 250px !important;
        }

        .custom-select2-dropdown .select2-results__options {
            max-height: 200px !important;
        }
    }
</style>
@endpush
@endsection