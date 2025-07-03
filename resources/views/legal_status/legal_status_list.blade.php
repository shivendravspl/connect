@extends('layouts.app')

@push('breadcrumb')
    <li class="breadcrumb-item active">Legal Status</li>
@endpush

@push('page-title')
    Legal Status
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteLinks = document.querySelectorAll('.js-ak-delete-link');
            deleteLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'You won\'t be able to revert this!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="ri-delete-bin-line"></i> Delete',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = this.getAttribute('data-link') || '#';
                            form.innerHTML = `
                                @csrf
                                @method('DELETE')
                            `;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Legal Status List</h4>
                <div class="d-flex align-items-center">
                    <form class="d-flex me-3" role="search">
                        <input type="hidden" name="legal_status_source" value="legal_status">
                        <input type="hidden" name="legal_status_length" value="{{ Request()->query('legal_status_length') }}">
                        <div class="input-group">
                            <input type="text" class="form-control" name="legal_status_search" placeholder="Search" value="{{ Request()->query('legal_status_source') == 'legal_status' ? Request()->query('legal_status_search') ?? '' : '' }}" autocomplete="off">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="ri-search-line"></i>
                            </button>
                            @if(Request()->query('legal_status_source') == 'legal_status' && Request()->query('legal_status_search'))
                                <button class="btn btn-outline-secondary js-ak-reset-search" type="button">
                                    <i class="ri-close-line"></i>
                                </button>
                            @endif
                        </div>
                    </form>
                    @can('add-LegalStatus')
                        <a href="{{ route('legal_status.create') }}" class="btn btn-primary">
                            <i class="ri-add-line"></i> Add New
                        </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered js-ak-content">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                   <th>Legal Status</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($legal_status_list as $data)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $data->legal_status }}</td>

                                    <td class="text-center">
                                        @can('edit-LegalStatus')
                                            <a href="{{ route('legal_status.edit', $data->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                        @endcan
                                        @can('delete-LegalStatus')
                                            <a href="javascript:void(0)" data-link="{{ route('legal_status.destroy', $data->id) }}" class="btn btn-sm btn-outline-danger js-ak-delete-link">
                                                <i class="ri-delete-bin-line"></i>
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div>
                    <select class="form-select js-ak-table-length-DataTable" style="width: auto;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination mb-0 js-ak-pagination-box">
                            <li class="page-item"><a class="page-link" href="#">Previous</a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">Next</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection