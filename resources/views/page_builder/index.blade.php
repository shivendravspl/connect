@extends('layouts.app')

@push('breadcrumb')
<li class="breadcrumb-item active">Page Builder</li>
@endpush

@push('page-title')
Page Builder
@endpush

@push('styles')
<style>
    .btn-sm {
        padding: 0.25rem 0.5rem !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 32px;
        width: 32px;
    }

    .btn-sm i {
        font-size: 16px;
    }

    .action-icons .btn {
        border-radius: 4px;
    }

    .action-icons {
        display: flex;
        gap: 8px;
    }
    
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .search-container {
        width: 300px;
    }
    
    .search-wrapper {
        position: relative;
    }
    
    .search-input {
        padding-right: 40px;
    }
    
    .search-icon {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
    }
    
    .reset-search {
        position: absolute;
        right: 35px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #6c757d;
    }
    
    @media (max-width: 576px) {
        .header-actions {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .search-container {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('.js-search-input');
        const searchForm = document.querySelector('.search-form');
        let searchTimer;
        
        // Debounced search on typing
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                searchForm.submit();
            }, 500);
        });
        
        // Reset search
        document.querySelector('.js-reset-search')?.addEventListener('click', function() {
            window.location.href = "{{ route('page-builder.index') }}";
        });
    });
</script>
@endpush

@section('content')
<div class="container my-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="header-actions">
                <h3 class="card-title mb-0">Page List</h3>
                
                <div class="d-flex align-items-center gap-3">
                    <form class="search-form search-container" method="GET">
                        <input name="page_source" value="page" type="hidden"/>
                        <div class="search-wrapper">
                            <input type="text" 
                                   autocomplete="off" 
                                   placeholder="Search pages..." 
                                   name="page_search"
                                   value="{{ request()->query('page_search') }}"
                                   class="form-control js-search-input search-input">
                                   
                            @if(request()->query('page_search'))
                                <span class="reset-search js-reset-search">
                                    <i class="bx bx-x"></i>
                                </span>
                            @endif
                            
                            <span class="search-icon">
                                <i class="bx bx-search"></i>
                            </span>
                        </div>
                    </form>
                    
                    <a href="{{ route('page-builder.create') }}" class="btn btn-success">Add New Page</a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="10%">ID</th>
                            <th>Page Name</th>
                            <th width="25%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($page_list as $data)
                        <tr>
                            <td>{{ $data->id }}</td>
                            <td>{{ $data->page_name }}</td>
                            <td>
                                @if($data->is_editable == 'Y')
                                <div class="action-icons">
                                    <a href="{{ route('page-builder.edit', $data->id) }}"
                                        class="btn btn-sm btn-info" title="Edit">
                                        <i class="bx bx-pencil"></i>
                                    </a>

                                    <form action="{{ route('page-builder.destroy', $data->id) }}"
                                        method="POST" onsubmit="return confirm('Are you sure you want to delete this page?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>

                                    <a href="{{ route('page-builder.page', ['page' => base64_encode($data->id)]) }}"
                                        class="btn btn-sm btn-secondary" title="View">
                                        <i class="bx bxs-magic-wand"></i>
                                    </a>
                                </div>
                                @else
                                <span class="text-muted">Not editable</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-4">No pages found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($page_list->hasPages())
            <div class="d-flex flex-wrap justify-content-between align-items-center pt-3 border-top">
                <div>
                    Showing {{ $page_list->firstItem() }} to {{ $page_list->lastItem() }} of {{ $page_list->total() }} entries
                </div>
                <div>
                    {{ $page_list->appends(request()->query())->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection