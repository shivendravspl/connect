@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0">Users</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0)">User Management</a></li>
                        <li class="breadcrumb-item active">User List</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- End page title -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-end">
                        <!-- Filters -->
                        <div class="col-md-2">
                            <label for="bu" class="form-label">Business Unit</label>
                            <select name="bu" id="bu" class="form-select form-select-sm">
                                @foreach ($bu_list as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="filter_zone" class="form-label">Zone</label>
                            <select class="form-control form-control-sm" id="filter_zone">
                                <option value="">All Zones</option>
                                @foreach($zone_list as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="filter_region" class="form-label">Region</label>
                            <select class="form-control form-control-sm" id="filter_region">
                                <option value="">All Regions</option>
                                @foreach($region_list as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="filter_territory" class="form-label">Territory</label>
                            <select class="form-control form-control-sm" id="filter_territory">
                                <option value="">All Territories</option>
                                @foreach($territory_list as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="filter_crop_vertical" class="form-label">Crop Vertical</label>
                            <select class="form-control form-control-sm" id="filter_crop_vertical">
                                <option value="">All Crop Verticals</option>
                                @foreach($crop_type as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 text-end">
                            <button class="btn btn-success btn-sm export-users" title="Export to Excel">
                                <i class="fas fa-file-excel me-1"></i> Export
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="user-table" class="table table-bordered table-hover w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Roles</th>
                                    <th>Territory</th>
                                    <th>Region</th>
                                    <th>Zone</th>
                                    <th>Crop Vertical</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light p-2">
                    <h5 class="modal-title" id="userModalLabel">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="user-form" action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" value="POST">
                    <div class="modal-body p-3">
                        <div class="mb-2">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control form-control-sm" id="name" name="name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control form-control-sm" id="email" name="email">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control form-control-sm" id="phone" name="phone">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control form-control-sm" id="password" name="password">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control form-control-sm" id="password_confirmation" name="password_confirmation">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label for="roles" class="form-label">Roles</label>
                            <select class="form-select select2 form-control-sm" id="roles" name="roles[]" multiple required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer p-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header p-2">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    Are you sure you want to delete this user? This action cannot be undone.
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-sm" id="confirm-delete">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    #user-table.dataTable {
        font-size: 0.82rem;
        width: 100% !important; /* Ensure table takes full width */
    }

    #user-table.dataTable thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background-color: #f3f6f9;
        color: #333;
        white-space: nowrap; /* Prevent header text wrapping */
    }

    #user-table.dataTable tbody td {
        font-size: 0.82rem;
        vertical-align: middle;
        white-space: nowrap; /* Prevent cell content wrapping */
        overflow: hidden;
        text-overflow: ellipsis; /* Handle overflow gracefully */
    }

    #user-table .badge {
        font-size: 0.68rem;
        padding: 0.3em 0.6em;
    }

    .dataTables_filter input {
        font-size: 0.82rem;
        padding: 0.5rem;
        border-radius: 0.25rem;
        border: 1px solid #ced4da;
    }

    .dataTables_filter label {
        font-size: 0.82rem;
        color: #333;
    }

    .dataTables_info,
    .dataTables_paginate .pagination {
        font-size: 0.82rem;
    }

    .dataTables_length select {
        font-size: 0.82rem;
        padding: 0.3rem;
    }

    .card {
        border: none;
        box-shadow: 0 0.75rem 1.5rem rgba(18, 38, 63, 0.03);
        border-radius: 0.25rem;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, .125);
        padding: 1rem 1.5rem;
    }

    .table-responsive {
        overflow-x: auto; /* Enable horizontal scrolling if needed */
    }

    #user-table.dataTable tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    #user-table.dataTable tbody tr {
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
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

    .gap-2 {
        gap: 0.5rem;
    }

    .is-invalid {
        border-color: #dc3545 !important;
    }

    .invalid-feedback {
        display: none;
        color: #dc3545;
        font-size: 0.75rem;
    }

    .is-invalid~.invalid-feedback {
        display: block;
    }

    .select2-container .select2-selection--multiple {
        min-height: 32px;
    }

    .select2-container .select2-selection--multiple .select2-selection__choice {
        margin-top: 3px;
        background-color: #467fcf;
        border-color: #467fcf;
        color: white;
        font-size: 0.75rem;
    }

    .select2-container .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
    }

    .modal-sm {
        max-width: 400px;
    }

    .modal-content {
        max-height: 90vh;
        overflow: hidden;
    }

    .modal-body {
        padding: 1rem !important;
        overflow-y: hidden;
    }

    .modal-header,
    .modal-footer {
        padding: 0.5rem 1rem !important;
    }

    .form-control-sm,
    .form-select-sm {
        font-size: 0.8rem;
        padding: 0.5rem 1rem;
        height: 32px;
    }

    .form-label {
        font-size: 0.85rem;
        margin-bottom: 0.2rem;
    }

    .modal-title {
        font-size: 1.1rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: 'Select roles',
            allowClear: true,
            dropdownParent: $('#userModal'),
            width: '100%'
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        // Initialize DataTable with responsive option
        const table = $('#user-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true, // Enable responsive behavior
            autoWidth: false, // Disable auto-width to prevent misalignment
            ajax: {
                url: "{{ route('getUserList') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: function(d) {
                    if ($('#bu').val() !== 'All') {
                        d.bu_id = $('#bu').val();
                    }
                    d.territory_id = $('#filter_territory').val();
                    d.region_id = $('#filter_region').val();
                    d.zone_id = $('#filter_zone').val();
                    d.crop_vertical = $('#filter_crop_vertical').val();
                }
            },
            columns: [
                { data: 'id', name: 'id', width: '5%' },
                { data: 'name', name: 'name', width: '15%' },
                { data: 'email', name: 'email', width: '15%' },
                { data: 'phone', name: 'phone', width: '10%' },
                { 
                    data: 'roles',
                    name: 'roles',
                    width: '10%',
                    render: function(data) {
                        return data ? data.map(role => `<span class="badge bg-primary">${role}</span>`).join(' ') : '-';
                    }
                },
                { data: 'territory_name', name: 'core_territory.territory_name', width: '10%' },
                { data: 'region_name', name: 'core_region.region_name', width: '10%' },
                { data: 'zone_name', name: 'core_zone.zone_name', width: '10%' },
                { data: 'crop_vertical_name', name: 'core_employee.emp_vertical', width: '10%' },
                { data: 'created_at', name: 'created_at', width: '10%' },
                { data: 'action', name: 'action', orderable: false, searchable: false, width: '10%' }
            ]
        });

        // Hierarchical filter functions
        function getZoneByBU(bu) {
            const zoneSelect = $('#filter_zone');
            $.ajax({
                url: "{{ route('get_zone_by_bu') }}",
                data: { bu: bu },
                type: 'POST',
                dataType: 'json',
                beforeSend: function() {
                    zoneSelect.prop('disabled', true);
                },
                success: function(data) {
                    zoneSelect.empty().append('<option value="">All Zones</option>');
                    $.each(data.zoneList, function(index, zone) {
                        zoneSelect.append(
                            `<option value="${zone.id}">${zone.zone_name}</option>`
                        );
                    });
                    zoneSelect.prop('disabled', false);
                    $('#filter_region').empty().append('<option value="">All Regions</option>');
                    $('#filter_territory').empty().append('<option value="">All Territories</option>');
                    table.ajax.reload();
                }
            });
        }

        function getRegionByZone(zone) {
            const regionSelect = $('#filter_region');
            $.ajax({
                url: "{{ route('get_region_by_zone') }}",
                data: { zone: zone },
                type: 'POST',
                dataType: 'json',
                beforeSend: function() {
                    regionSelect.prop('disabled', true);
                },
                success: function(data) {
                    regionSelect.empty().append('<option value="">All Regions</option>');
                    $.each(data.regionList, function(index, region) {
                        regionSelect.append(
                            `<option value="${region.id}">${region.region_name}</option>`
                        );
                    });
                    regionSelect.prop('disabled', false);
                    $('#filter_territory').empty().append('<option value="">All Territories</option>');
                    table.ajax.reload();
                }
            });
        }

        function getTerritoryByRegion(region) {
            const territorySelect = $('#filter_territory');
            $.ajax({
                url: "{{ route('get_territory_by_region') }}",
                data: { region: region },
                type: 'POST',
                dataType: 'json',
                beforeSend: function() {
                    territorySelect.prop('disabled', true);
                },
                success: function(data) {
                    territorySelect.empty().append('<option value="">All Territories</option>');
                    $.each(data.territoryList, function(index, territory) {
                        territorySelect.append(
                            `<option value="${territory.id}">${territory.territory_name}</option>`
                        );
                    });
                    territorySelect.prop('disabled', false);
                    table.ajax.reload();
                }
            });
        }

        // Filter change handlers
        $(document).on("change", "#bu", function() {
            var bu = $(this).val();
            getZoneByBU(bu);
        });

        $(document).on("change", "#filter_zone", function() {
            var zone = $(this).val();
            getRegionByZone(zone);
        });

        $(document).on("change", "#filter_region", function() {
            var region = $(this).val();
            getTerritoryByRegion(region);
        });

        $('#filter_zone, #filter_crop_vertical, #filter_territory, #filter_region').change(function() {
            table.ajax.reload();
        });

        // Add user button click handler
        $('.add-user').click(function() {
            const form = $('#user-form');
            form[0].reset();
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('').hide();
            $('#roles').val(null).trigger('change');
            $('#userModalLabel').text('Add User');
            form.attr('action', '{{ route("users.store") }}');
            form.find('input[name="_method"]').val('POST');
            $('#email, #phone').removeAttr('required');
            $('#password, #password_confirmation').attr('required', 'required');
            $('#userModal').modal('show');
        });

        // Edit user button click handler
        $(document).on('click', '.edit-user', function() {
            const userId = $(this).data('id');
            const form = $('#user-form');

            form[0].reset();
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('').hide();

            $('#userModalLabel').text('Edit User');
            form.attr('action', '{{ url("users") }}/' + userId);
            form.find('input[name="_method"]').val('PUT');
            $('#email, #phone').removeAttr('required');
            $('#password, #password_confirmation').removeAttr('required');

            $.ajax({
                url: "{{ url('users') }}/" + userId + "/edit",
                type: 'GET',
                success: function(response) {
                    const userData = response.data || response;
                    if (userData && (userData.user || userData.id)) {
                        const user = userData.user || userData;
                        $('#name').val(user.name || '');
                        $('#email').val(user.email || '');
                        $('#phone').val(user.phone || '');
                        $('#password, #password_confirmation').val('');
                        if (userData.userRoles) {
                            $('#roles').val(userData.userRoles).trigger('change');
                        }
                        $('#userModal').modal('show');
                    } else {
                        console.error('Invalid user data structure:', userData);
                        alert('Invalid user data received');
                    }
                },
                error: function(xhr) {
                    console.error('Edit AJAX error:', xhr.responseText, xhr.status);
                    alert('Error loading user data: ' + (xhr.responseJSON?.message || 'Unknown error'));
                }
            });
        });

        // Form submission handler
        $('#user-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            const method = form.find('input[name="_method"]').val() || 'POST';

            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('').hide();

            $.ajax({
                url: url,
                type: method,
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#userModal').modal('hide');
                        table.ajax.reload(null, false);
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    console.error('Submit AJAX error:', xhr.responseJSON);
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            const input = form.find(`[name="${field}"], [name="${field}[]"]`);
                            input.addClass('is-invalid');
                            input.siblings('.invalid-feedback').text(messages[0]).show();
                        });
                    } else {
                        alert('An error occurred. Please try again.');
                    }
                }
            });
        });

        // Delete user button click handler
        let deleteUserId;
        $(document).on('click', '.delete-user', function() {
            deleteUserId = $(this).data('id');
            $('#deleteModal').modal('show');
        });

        // Confirm delete button click handler
        $('#confirm-delete').click(function() {
            $.ajax({
                url: "{{ url('users') }}/" + deleteUserId,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#deleteModal').modal('hide');
                        table.ajax.reload(null, false);
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    console.error('Delete AJAX error:', xhr.responseText);
                    alert('Error deleting user');
                }
            });
        });

        // Export button click handler
        $(document).on('click', '.export-users', function() {
            const filters = {
                bu_id: $('#bu').val(),
                territory_id: $('#filter_territory').val(),
                region_id: $('#filter_region').val(),
                zone_id: $('#filter_zone').val(),
                crop_vertical: $('#filter_crop_vertical').val(),
                search: $('#user-table_filter input').val() || ''
            };

            const form = $('<form>', {
                action: "{{ route('users.export') }}",
                method: 'POST',
                target: '_blank'
            }).appendTo('body');

            form.append($('<input>', {
                type: 'hidden',
                name: '_token',
                value: $('meta[name="csrf-token"]').attr('content')
            }));

            $.each(filters, function(key, value) {
                form.append($('<input>', {
                    type: 'hidden',
                    name: key,
                    value: value
                }));
            });

            form.submit();
            form.remove();
        });
    });
</script>
@endpush