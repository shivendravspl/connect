@extends('layouts.app')

@push('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('page-builder.index') }}">Page Builder</a></li>
    <li class="breadcrumb-item active">{{ isset($data->id) ? 'Edit' : 'Create' }} Page</li>
@endpush

@push('page-title')
    {{ isset($data->id) ? 'Edit Page' : 'Create New Page' }}
@endpush

@push('styles')
<style>
    .form-container {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 25px;
        margin-top: 20px;
    }
    
    .form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    
    .form-header h3 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }
    
    .form-delete-record a {
        color: #dc3545;
        font-size: 1.2rem;
        transition: all 0.3s;
    }
    
    .form-delete-record a:hover {
        color: #c82333;
    }
    
    .form-content {
        margin-top: 20px;
    }
    
    .input-container {
        margin-bottom: 20px;
    }
    
    .input-label label {
        font-weight: 500;
        margin-bottom: 8px;
        display: block;
        color: #495057;
    }
    
    .input-data {
        position: relative;
    }
    
    .form-input {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 1rem;
        transition: border-color 0.15s;
    }
    
    .form-input:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }
    
    .error-message {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 5px;
        display: none;
    }
    
    .error-message.show {
        display: block;
    }
    
    .text-muted {
        color: #6c757d;
        font-size: 0.875rem;
        margin-top: 5px;
    }
    
    .form-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    
    .btn {
        padding: 8px 20px;
        border-radius: 4px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .btn-primary {
        background: #3b7ddd;
        color: white;
        border: 1px solid #3b7ddd;
    }
    
    .btn-primary:hover {
        background: #326abc;
        border-color: #2e63b1;
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
        border: 1px solid #6c757d;
    }
    
    .btn-secondary:hover {
        background: #5a6268;
        border-color: #545b62;
    }
</style>
@endpush

@section('content')
    <div class="form-container">
        <form method="POST"
              action="{{ isset($data->id) ? route('page-builder.update', $data->id) : route('page-builder.store') }}"
              enctype="multipart/form-data" class="validate-form">
            @csrf
            @isset($data->id)
                @method('PUT')
            @endisset
            
            <div class="form-header">
                <h3>{{ isset($data->id) ? 'Edit Page' : 'Create New Page' }}</h3>
                @isset($data->id)
                <div class="form-delete-record">
                    <a href="#" data-link="{{ route('page-builder.destroy', $data->id) }}" 
                       data-id="{{ $data->id }}" class="delete-link js-ak-delete-link">
                        <i class="fas fa-trash-alt"></i> Delete Page
                    </a>
                </div>
                @endisset
            </div>
            
            @include('layouts.errors')
            
            <div class="form-content">
                <div class="input-container">
                    <div class="input-label">
                        <label for="page_name">Page Name <span class="text-danger">*</span></label>
                    </div>
                    <div class="input-data">
                        <input type="text" class="form-input" id="page_name" name="page_name" 
                               placeholder="Enter page name" required
                               value="{{ old('page_name', $data->page_name ?? '') }}">
                        <div class="error-message @error('page_name') show @enderror">
                            @error('page_name') {{ $message }} @else Required! @enderror
                        </div>
                        <div class="text-muted">Enter a descriptive name for your page</div>
                    </div>
                </div>
                
                <!-- Add more form fields here as needed -->
                
                <div class="form-footer">
                    <a href="{{ route('page-builder.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> {{ isset($data->id) ? 'Update' : 'Save' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
    
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('.validate-form');
    const inputs = form.querySelectorAll('input[required]');
    
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.nextElementSibling.classList.add('show');
                isValid = false;
            } else {
                input.nextElementSibling.classList.remove('show');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Delete confirmation
    document.querySelectorAll('.js-ak-delete-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this page?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = this.dataset.link;
                
                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = document.querySelector('meta[name="csrf-token"]').content;
                
                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';
                
                form.appendChild(csrf);
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush