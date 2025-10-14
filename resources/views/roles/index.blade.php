@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0">Master</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Pages</a></li>
                        <li class="breadcrumb-item active">Roles</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <!-- end page title -->
    @can('list-role')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                @can('add-role')
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Role & Permission</h5>
                    <div>
                        <button type="button" class="btn btn-success add-btn btn-sm" data-bs-toggle="modal"
                            data-bs-target="#AddNewRoleModal"><i class="ri-add-line align-bottom me-1"></i> Add Role
                        </button>
                        <a class="btn btn-primary btn-sm" href="{{ route('permission') }}" target="_blank">Permission List</a>
                    </div>
                </div>

                @endcan
                <div class="card-body">
                    <div class="table-data">
                        <table class="table table-bordered table-striped" id="data-table">
                            <thead class="text-center">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Permission</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                <tr style="vertical-align: middle;">
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">{{ $role->name }}</td>
                                    <td class="tags" style="white-space: normal;width: 60%;">
                                        @foreach ($role->permissions as $permission)
                                        <span
                                            class="badge bg-primary-subtle text-primary">{{ $permission->name }}</span>
                                        @endforeach
                                    </td>
                                    <td class="text-center">
                                        @if ($role->status == 'active')
                                        Active
                                        @else
                                        In Active
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @can('edit-role')
                                        <a href="{{ route('roles.edit', $role->id) }}"
                                            class="btn btn-soft-info btn-sm edit-role"
                                            data-id="{{ $role->id }}"><i class="bx bx-pencil"></i></a>
                                        @endcan

                                        @can('delete-role')
                                        @if ($role->can_delete === 'Y')
                                        <a href="javascript:void(0);" class="btn btn-soft-danger btn-sm"
                                            onclick="deleteData(this)"
                                            data-url="{{ route('roles.destroy', $role->id) }}"><i
                                                class="bx bx-trash"></i></a>
                                        @endif
                                        @endcan

                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>

    </div>
    @endcan
</div>

<div class="modal fade" id="AddNewRoleModal" tabindex="-1" aria-modal="true" role="dialog" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('roles.store') }}" method="POST" id="addRoleForm">
                @csrf
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <div class="row mb-3">
                        <!-- Role Name -->
                        <div class="col-md-4">
                            <label for="role_name">Role Name :</label> <span class="text-danger">*</span>
                            <input type="text" name="role_name" id="role_name" class="form-control">
                            <span class="text-danger error-text role_name_error"></span>
                        </div>
                    </div>

                    <!-- Permissions Accordion -->
                    <div class="accordion" id="permissionsAccordion">
                        @foreach($permissions as $module => $groups)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-{{ $module }}">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $module }}" aria-expanded="true" aria-controls="collapse-{{ $module }}">
                                    {{ strtoupper($module) }}
                                </button>
                            </h2>
                            <div id="collapse-{{ $module }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $module }}" data-bs-parent="#permissionsAccordion">
                                <div class="accordion-body">
                                    @foreach($groups as $group => $permissions)
                                    <div class="mb-3">
                                        <!-- Group Header -->
                                        <div class="d-flex align-items-center">
                                            <input type="checkbox" class="form-check-input me-2" onclick="checkAllPermissions('{{Str::slug($group)}}');" />
                                            <h6 class="mb-0 text-primary">{{ strtoupper($group) }}</h6>
                                        </div>

                                        <!-- Group Permissions -->
                                        <div class="row mt-2">
                                            @foreach($permissions as $permission)
                                            <div class="col-6 col-md-4">
                                                <div class="form-check">
                                                    <input type="checkbox" name="permission[]" class="form-check-input {{Str::slug($group)}}" id="{{$permission['id']}}" value="{{$permission['name']}}">
                                                    <label class="form-check-label" for="{{$permission['id']}}">{{ $permission['name'] }}</label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <hr>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>


        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

@endsection
@push('scripts')
<script>
    function checkAllPermissions(group) {
        const checkboxes = document.querySelectorAll(`.${group}`);
        const masterCheckbox = event.target;
        checkboxes.forEach((checkbox) => {
            checkbox.checked = masterCheckbox.checked;
        });
    }


    $('#AddNewRoleModal').on('hidden.bs.modal', function() {
        $('#addRoleForm')[0].reset();
        $('.error-text').text('');
    });
</script>
@endpush