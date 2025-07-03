@extends('layouts.app')

@push('breadcrumb')
    <li class="breadcrumb-item">Form</li>
    <li class="breadcrumb-item active">Legal Status</li>
@endpush

@push('page-title')
    Legal Status
@endpush

@push('page-back-button')
    <a href="{{ route('legal_status.index') }}" class="btn btn-outline-secondary me-2">
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
                <h4 class="card-title mb-0">{{ isset($data->id) ? 'Update' : 'Add New' }} Legal Status</h4>
                @can('delete-LegalStatus')
                    @if(isset($data->id))
                        <a href="javascript:void(0)" data-link="{{ route('legal_status.destroy', $data->id) }}" data-id="{{ $data->id }}" class="btn btn-sm btn-outline-danger js-ak-delete-link">
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
                <form method="POST" action="@isset($data->id){{ route('legal_status.update', $data->id) }}@else{{ route('legal_status.store') }}@endisset" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @isset($data->id)
                        @method('PUT')
                    @endisset
                    @csrf
                    
                    <div class="row">
                        <div class="col-12 mb-3">
    <div class="form-group">
        <label for="legal_status" class="form-label">Legal Status </label>
        <input type="text" class="form-control  " 
               id="legal_status" name="legal_status" 
               placeholder="Legal Status"   
               value="{{ old('legal_status', $data->legal_status ?? '') }}">
        @if($errors->has('legal_status'))
            <div class="invalid-feedback d-block">
                {{ $errors->first('legal_status') }}
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
                        <a href="{{ $cancel_route ?? route('legal_status.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection