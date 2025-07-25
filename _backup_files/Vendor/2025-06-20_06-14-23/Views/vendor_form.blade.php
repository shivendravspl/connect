@extends('layouts.app')

@push('breadcrumb')
    <li class="breadcrumb-item">Form</li>
    <li class="breadcrumb-item active">vendor</li>
@endpush

@push('page-title')
    vendor
@endpush

@push('page-back-button')
    <a href="{{ route('vendor.index') }}" class="btn btn-outline-secondary me-2">
        <i class="ri-arrow-left-line"></i> Back
    </a>
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
                <h4 class="card-title mb-0">{{ isset($data->id) ? 'Update' : 'Add New' }} vendor</h4>
                @can('delete-Vendor')
                    @if(isset($data->id))
                        <a href="javascript:void(0)" data-link="{{ route('vendor.destroy', $data->id) }}" data-id="{{ $data->id }}" class="btn btn-sm btn-outline-danger js-ak-delete-link">
                            <i class="ri-delete-bin-line"></i>
                        </a>
                    @endif
                @endcan
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger mb-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="@isset($data->id){{ route('vendor.update', $data->id) }}@else{{ route('vendor.store') }}@endisset" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @isset($data->id)
                        @method('PUT')
                    @endisset
                    @csrf
                    
                    <div class="row">
                        <div class="col-12 mb-3">
    <div class="form-group">
        <label for="email" class="form-label">email <span class="text-danger">*</span></label>
        <input type="email" class="form-control  required" 
               id="email" name="email" 
               placeholder="" required  
               value="{{ old('email', $data->email ?? '') }}">
        @if($errors->has('email'))
            <div class="invalid-feedback d-block">
                {{ $errors->first('email') }}
            </div>
        @endif
        @if(!empty('') || !empty(''))
            <small class="form-text text-muted">
                 
            </small>
        @endif
    </div>
</div><div class="col-12 mb-3">
    <div class="form-group">
        <label for="company_name" class="form-label">Company name <span class="text-danger">*</span></label>
        <input type="text" class="form-control  required" 
               id="company_name" name="company_name" 
               placeholder="" required  
               value="{{ old('company_name', $data->company_name ?? '') }}">
        @if($errors->has('company_name'))
            <div class="invalid-feedback d-block">
                {{ $errors->first('company_name') }}
            </div>
        @endif
        @if(!empty('') || !empty(''))
            <small class="form-text text-muted">
                 
            </small>
        @endif
    </div>
</div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line"></i> Save
                        </button>
                        <a href="{{ $cancel_route ?? route('vendor.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection