@extends('layouts.app')

@push('breadcrumb')
    <li class="breadcrumb-item"><a href="#">Settings</a></li>
    <li class="breadcrumb-item active">Menu Builder</li>
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Menu Builder</h5>
                        <div class="flex-shrink-0">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#menuModal">
                                <i class="ri-add-line align-bottom me-1"></i> Add Menu
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <button class="btn btn-soft-primary me-2" id="nestable-button" data-action="expand-all">
                            <i class="ri-expand-left-right-line align-bottom me-1"></i> Expand All
                        </button>
                        <button class="btn btn-success" onclick="set_menu_position()">
                            <i class="ri-save-line align-bottom me-1"></i> Save Order
                        </button>
                    </div>
                    
                    <div class="dd" id="nestable">
                        @php
                            $menu = displayNestableMenu(0, 1, 1, 1, 1);
                        @endphp
                        @if(empty($menu))
                            <div class="alert alert-info">No menu items found. Add your first menu item.</div>
                        @else
                            {!! $menu !!}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Modal -->
    <div class="modal fade" id="menuModal" tabindex="-1" aria-labelledby="menuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="menuModalLabel">
                        <i class="ri-menu-line align-bottom me-1"></i> <span id="modalTitle">Add Menu</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="menu_form" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent Menu</label>
                            <select name="parent_id" id="parent_id" class="form-select">
                                <option value="0">Set as Parent Menu</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="menu_name" class="form-label">Menu Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="menu_name" id="menu_name" placeholder="Enter menu name" required>
                            <input type="hidden" name="id" id="id">
                        </div>
                        <div class="mb-3">
                            <label for="menu_url" class="form-label">URL</label>
                            <input type="text" class="form-control" name="menu_url" id="menu_url" placeholder="Enter URL">
                        </div>
                        <div class="mb-3">
                            <label for="menu_position" class="form-label">Position <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control" name="menu_position" id="menu_position" placeholder="Enter position" required>
                        </div>
                        <div class="mb-3">
                            <label for="menu_status" class="form-label">Status</label>
                            <select class="form-select" name="menu_status" id="menu_status">
                                <option value="A">Active</option>
                                <option value="D">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="menu_modal_button">
                            <i class="ri-save-line align-bottom me-1"></i> <span id="buttonText">Save</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/jquery-nestable.js') }}"></script>
    <script>
        $(document).ready(function() {
              // Setup CSRF token for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            // Initialize Nestable
            $('#nestable').nestable({
                group: 1,
                maxDepth: 10
            }).nestable('collapseAll');

            // Toggle expand/collapse
            $('#nestable-button').on('click', function() {
                const action = $(this).data('action');
                if (action === 'expand-all') {
                    $('#nestable').nestable('expandAll');
                    $(this).html('<i class="ri-collapse-left-right-line align-bottom me-1"></i> Collapse All')
                           .data('action', 'collapse-all');
                } else {
                    $('#nestable').nestable('collapseAll');
                    $(this).html('<i class="ri-expand-left-right-line align-bottom me-1"></i> Expand All')
                           .data('action', 'expand-all');
                }
            });

            // Form submission
            $('#menu_form').on('submit', function(e) {
                e.preventDefault();
                const button = $('#menu_modal_button');
                const originalText = button.html();
                button.html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Processing...')
                      .prop('disabled', true);

                $.ajax({
                    url: "{{ route('menu-builder.store') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.status) {
                            $('#menuModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Something went wrong!'
                        });
                    },
                    complete: function() {
                        button.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Modal cleanup
            $('#menuModal').on('hidden.bs.modal', function() {
                $('#menu_form').trigger('reset');
                $('#id').val('');
                $('#modalTitle').text('Add Menu');
                $('#buttonText').text('Save');
            });
        });

        function set_menu_position() {
            const menu = $('#nestable').nestable('serialize');
            if (menu.length) {
                const button = $('button[onclick="set_menu_position()"]');
                const originalText = button.html();
                button.html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...')
                      .prop('disabled', true);

                $.ajax({
                    url: "{{ route('menu-builder.setPosition') }}",
                    method: 'POST',
                    data: { menu: menu },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Menu order saved successfully!',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to save menu order'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Something went wrong while saving menu order'
                        });
                    },
                    complete: function() {
                        button.html(originalText).prop('disabled', false);
                    }
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'No menu items to save!'
                });
            }
        }

        function get_form(id = 0) {
            $('#menu_form').trigger('reset');
            $('#id').val(id);

            if (id) {
                $('#modalTitle').text('Edit Menu');
                $('#buttonText').text('Update');
                
                $.ajax({
                    url: "{{ route('menu-builder.show_menu') }}",
                    method: 'POST',
                    data: { id: id },
                    success: function(data) {
                        if (data.status) {
                            $('#menu_name').val(data.menu_name);
                            $('#menu_url').val(data.menu_url);
                            $('#menu_position').val(data.menu_position);
                            $('#menu_status').val(data.status).trigger('change');
                            get_menu_parent_list(id, data.parent_id);
                            $('#menuModal').modal('show');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load menu data'
                        });
                    }
                });
            } else {
                $('#modalTitle').text('Add Menu');
                $('#buttonText').text('Save');
                get_menu_parent_list(id);
                $('#menuModal').modal('show');
            }
        }

        function get_menu_parent_list(id, parent_id = 0) {
            $.ajax({
                url: "{{ route('menu-builder.getParentMenus') }}",
                method: 'POST',
                data: { id: id },
                success: function(response) {
                    let options = '<option value="0">Set as Parent Menu</option>';
                    if (response.status && response.data) {
                        response.data.forEach(item => {
                            const selected = parent_id == item.id ? 'selected' : '';
                            options += `<option value="${item.id}" ${selected}>${item.menu_name}</option>`;
                        });
                    }
                    $('#parent_id').html(options).trigger('change');
                },
                error: function() {
                    $('#parent_id').html('<option value="0">Set as Parent Menu</option>');
                }
            });
        }
    </script>
@endpush