@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Vendor List</h5>
                    <a href="{{ route('vendors.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New Vendor
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Company Name</th>
                                    <th>Contact Person</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vendor_list as $vendor)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $vendor->company_name }}</td>
                                    <td>{{ $vendor->contact_person_name }}</td>
                                    <td>{{ $vendor->vendor_email }}</td>
                                    <td>{{ $vendor->contact_number }}</td>
                                    <td>
                                        @if($vendor->is_completed)
                                            <span class="badge badge-success">Completed</span>
                                        @else
                                            <span class="badge badge-warning">In Progress (Step {{ $vendor->current_step }})</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('vendors.show', $vendor->id) }}" class="btn btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('vendors.edit', $vendor->id) }}" class="btn btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('vendors.destroy', $vendor->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this vendor?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No vendors found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($vendor_list->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $vendor_list->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection