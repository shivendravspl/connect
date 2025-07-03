@extends('layouts.app')

@push('breadcrumb')
    <li class="breadcrumb-item active">gender</li>
@endpush

@push('page-title')
    gender
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
                <h4 class="card-title mb-0">gender List</h4>
                <div class="d-flex align-items-center">
                    <form class="d-flex me-3" role="search">
                        <input type="hidden" name="page_source" value="page">
                        <input type="hidden" name="page_length" value="{{ Request()->query('page_length') }}">
                        <div class="input-group">
                            <input type="text" class="form-control" name="page_search" placeholder="Search" value="{{ Request()->query('page_source') == 'page' ? Request()->query('page_search') ?? '' : '' }}" autocomplete="off">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="ri-search-line"></i>
                            </button>
                            @if(Request()->query('page_source') == 'page' && Request()->query('page_search'))
                                <button class="btn btn-outline-secondary js-ak-reset-search" type="button">
                                    <i class="ri-close-line"></i>
                                </button>
                            @endif
                        </div>
                    </form>
                    <a href="{{ route('gender.create') }}" class="btn btn-primary">
                        <i class="ri-add-line"></i> Add New
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered js-ak-content">
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td class="text-center">
                                    <a href="{{ route('gender.edit', 1) }}" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="ri-edit-line"></i>
                                    </a>
                                    <a href="javascript:void(0)" data-link="{{ route('gender.destroy', 1) }}" class="btn btn-sm btn-outline-danger js-ak-delete-link">
                                        <i class="ri-delete-bin-line"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td class="text-center">
                                    <a href="{{ route('gender.edit', 2) }}" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="ri-edit-line"></i>
                                    </a>
                                    <a href="javascript:void(0)" data-link="{{ route('gender.destroy', 2) }}" class="btn btn-sm btn-outline-danger js-ak-delete-link">
                                        <i class="ri-delete-bin-line"></i>
                                    </a>
                                </td>
                            </tr>
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