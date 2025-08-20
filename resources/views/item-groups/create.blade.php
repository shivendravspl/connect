@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Create New Item Group</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('item-groups.store') }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label for="name">Group Name *</label>
                            <input type="text" class="form-control  form-control-sm @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="description">Description</label>
                            <textarea class="form-control form-control-sm @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-save"></i> Create Group
                            </button>
                            <a href="{{ route('item-groups.index') }}" class="btn btn-sm btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection