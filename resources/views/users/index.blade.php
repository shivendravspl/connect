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
    <div class="row align-items-center">
        <div class="col-md-6">
            <h5 class="card-title mb-0">User Management</h5>
        </div>
        <div class="col-md-6">
            <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-success btn-sm export-users" title="Export to Excel">
                    <i class="ri-file-excel-2-line me-1"></i>
                </button>
                {{--<button class="btn btn-primary btn-sm add-user" title="Add New User">
                    <i class="fas fa-plus me-1"></i> Add User
                </button>--}}
            </div>
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
                                    <th>Status</th>
                                    <th>Phone</th>
                                    <th>Roles</th>
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
                            <label for="user_status" class="form-label">Status</label>
                            <select class="form-select form-control-sm" id="user_status" name="user_status" required>
                                <option value="A">Active</option>
                                <option value="P">Pending</option>
                                <option value="D">Disabled</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control form-control-sm" id="phone" name="phone">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label for="roles" class="form-label">Roles</label>
                            <select class="form-select select2 form-control-sm" id="roles" name="roles[]" multiple>
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

    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light p-2">
                    <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="change-password-form" method="POST">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <input type="hidden" name="user_id" id="password_user_id">
                    <div class="modal-body p-3">
                        <div class="mb-2">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control form-control-sm" id="new_password" name="password" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-2">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control form-control-sm" id="password_confirmation" name="password_confirmation" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer p-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary btn-sm">Update Password</button>
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
        width: 100% !important;
    }

    #user-table.dataTable thead th {
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background-color: #f3f6f9;
        color: #333;
        white-space: nowrap;
    }

    #user-table.dataTable tbody td {
        font-size: 0.82rem;
        vertical-align: middle;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
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
        overflow-x: auto;
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

    .btn-xs {
    padding: 0.15rem 0.35rem;   /* smaller padding */
    font-size: 0.65rem;         /* smaller text/icons */
    line-height: 1;
    border-radius: 0.25rem;
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
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "{{ route('getUserList') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: function(d) {
                }
            },
            columns: [
                { data: 'id', name: 'id', width: '5%' },
                { data: 'name', name: 'name', width: '15%' },
                { data: 'email', name: 'email', width: '15%' },
                { 
                    data: 'status', 
                    name: 'status', 
                    width: '10%' ,
                    render: function(data) {
                        if (data === 'A') {
                            return `<span class="badge bg-success">Active</span>`;
                        } else if (data === 'P') {
                            return `<span class="badge bg-warning text-dark">Pending</span>`;
                        } else if (data === 'D') {
                            return `<span class="badge bg-danger text-dark">Disabled</span>`;
                        } else {
                            return `<span>-</span>`;
                        }
                    }
                },
                { data: 'phone', name: 'phone', width: '10%' },
                { 
                    data: 'roles',
                    name: 'roles',
                    width: '10%',
                    render: function(data) {
                        return data ? data.map(role => `<span class="badge bg-primary">${role}</span>`).join(' ') : '-';
                    }
                },
                { data: 'created_at', name: 'created_at', width: '10%' },
                { 
                    data: 'action', 
                    name: 'action', 
                    orderable: false, 
                    searchable: false, 
                    width: '15%',
                    render: function(data, type, row) {
                        return `
                            <div class="d-flex gap-2">
                                <button class="btn btn-xs btn-info edit-user" data-id="${row.id}" title="Edit">
                                    <i class="bx bx-pencil fs-8"></i>
                                </button>
                                <button class="btn btn-xs btn-warning change-password" data-id="${row.id}" title="Change Password">
                                    <i class="ri-lock-password-line fs-14"></i>
                                </button>
                                <a href="/user/${row.id}/permission" class="btn btn-xs btn-secondary" title="Manage Permissions">
                                    <i class="ri-shield-keyhole-line align-bottom"></i>
                                </a>
                                <button class="btn btn-xs btn-danger delete-user" data-id="${row.id}" title="Delete">
                                    <i class="bx bx-trash fs-14"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ]
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

            $.ajax({
                url: "{{ url('users') }}/" + userId + "/edit",
                type: 'GET',
                success: function(response) {
                    const userData = response.data || response;
                    if (userData && (userData.user || userData.id)) {
                        const user = userData.user || userData;
                        $('#name').val(user.name || '').prop('readonly', true);
                        $('#email').val(user.email || '').prop('readonly', true);
                        $('#phone').val(user.phone || '').prop('readonly', true);
                        $('#user_status').val(user.status || 'A');
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

        // Change password button click handler
        $(document).on('click', '.change-password', function() {
            const userId = $(this).data('id');
            const form = $('#change-password-form');
            
            form[0].reset();
            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('').hide();
            
            $('#password_user_id').val(userId);
            form.attr('action', '{{ url("users") }}/' + userId + '/password');
            $('#changePasswordModal').modal('show');
        });

        // Form submission handler for user form
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

        // Form submission handler for password change form
        $('#change-password-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            const method = form.find('input[name="_method"]').val() || 'PUT';

            form.find('.is-invalid').removeClass('is-invalid');
            form.find('.invalid-feedback').text('').hide();

            $.ajax({
                url: url,
                type: method,
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#changePasswordModal').modal('hide');
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    console.error('Password change AJAX error:', xhr.responseJSON);
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            const input = form.find(`[name="${field}"]`);
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
                search: $('#user-table_filter input').val() || ''
            };

            const form = $('<form>', {
                action: "{{ route('users.export') }}",
                method: 'POST',
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