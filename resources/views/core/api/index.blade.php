@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div
                class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0">Core APIs</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Pages</a></li>
                        <li class="breadcrumb-item active">API List</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <!-- end page title -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-0">
                    <div class="row g-4 align-items-center">
                        <div class="col-sm-3">
                            <div class="search-box">
                                <input type="text" class="form-control search" placeholder="Search for..."
                                    id="customSearchBox">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>
                        <div class="col-sm-auto ms-auto">
                            <div class="hstack gap-2">
                                <button class="btn btn-soft-success" id="import-actions"><i class=" ri-download-cloud-2-line"></i> Import</button>
                                <button type="button" class="btn btn-success add-btn btn-sm" id="syncAPI"><i
                                        class="ri-restart-line align-bottom me-1"></i> Sync APIs
                                </button>

                            </div>
                        </div>
                    </div>
                </div>

                <!--end card-body-->
                <div class="card-body border border-dashed border-end-0 border-start-0">
                    <div id="elmLoader" class="d-none">
                        <div class="spinner-border text-primary avatar-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <div class="table-responsive table-card mb-4">
                        <table class="table align-middle table-nowrap mb-0" id="data-table">
                            <thead class="table-light text-muted">
                                <tr>
                                    <th></th>
                                    <th>S.No</th>
                                    <th>API Name</th>
                                    <th>API End Point</th>
                                    <th>Parameter</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($api_list as $api)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="apis" id="apis_{{$loop->iteration}}"
                                            value="{{$api->api_end_point}}">
                                    </td>
                                    <td class="text-center">{{$loop->iteration}}</td>
                                    <td>{{$api->api_name}}</td>
                                    <td>{{$api->api_end_point}}</td>
                                    <td>{{$api->parameters}}</td>
                                    <td>{{$api->description}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                    <!--end table-->

                    <!-- No Results Message -->
                    <div class="noresult">
                        <div class="text-center">
                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                colors="primary:#121331,secondary:#08a88a"
                                style="width:75px;height:75px"></lord-icon>
                            <h5 class="mt-2">Sorry! No Result Found</h5>

                        </div>
                    </div>

                    <!-- Custom Pagination -->
                    <div class="d-flex justify-content-end mt-3">
                        <!-- Page Length Selector -->
                        <select id="pageLengthSelector" class="form-select form-select-sm me-3"
                            style="width: auto;">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <div class="pagination-wrap hstack gap-2" style="display: flex;">
                            <ul class="pagination listjs-pagination mb-0"></ul>
                        </div>
                    </div>
                </div>
                <!--end card-body-->
            </div>
            <!--end card-->
        </div>
        <!--end col-->
    </div>

</div>

@endsection
@push('styles')
<style>
    #data-table.dataTable {
        font-size: 0.82rem;
    }

    #data-table.dataTable thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background-color: #f3f6f9;
        color: #333;
    }

    #data-table.dataTable tbody td {
        font-size: 0.82rem;
        vertical-align: middle;
    }

    #data-table .badge {
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

    #data-table.dataTable tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    #data-table.dataTable tbody tr {
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
    }

    #data-table.dataTable tbody tr:hover {
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
<script src="{{ asset('assets/js/data_table_init.js') }}"></script>
<script>
    $(document).on('click', '#syncAPI', function() {
        $.ajax({
            url: "{{route('core_api_sync')}}",
            method: 'GET',
            processData: false,
            dataType: 'json',
            contentType: false,
            beforeSend: function() {
                $("#elmLoader").removeClass('d-none')
            },
            success: function(data) {
                if (data.status == 200) {
                    $("#elmLoader").addClass('d-none');
                    toastr.success(data.msg);
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    $("#elmLoader").addClass('d-none');
                    toastr.error(data.msg);
                }
            }
        });

    });
    // Event listener for checkboxes to show/hide import button
    $('#data-table').on('change', 'input[name="apis"]', function() {
        // Check if any checkbox is checked
        var anyChecked = $('#data-table input[name="apis"]:checked').length > 0;

        // Toggle import button based on checkbox selection
        $('#import-actions').toggle(anyChecked);
    });
    // Initially hide the import button
    $('#import-actions').hide();

    $(document).on('click', '#import-actions', function() {
        var api_end_points = [];

        $("input[name='apis']").each(function() {
            if ($(this).prop("checked") === true) {
                var value = $(this).val();
                api_end_points.push(value);
            }
        });
        if (api_end_points.length > 0) {
            if (confirm('Are you sure to import selected api data?')) {
                $.ajax({
                    url: "{{route('importAPISData')}}",
                    type: 'POST',
                    data: {
                        api_end_points: api_end_points,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(data) {
                        if (data.status === 400) {
                            alert("Something went wrong...!!");
                        } else {
                            alert("Data imported successfully...!!");
                        }
                    }
                });
            }

        } else {
            alert('No API Selected!\nPlease select atleast one api to proceed.');
        }

    });
</script>
@endpush