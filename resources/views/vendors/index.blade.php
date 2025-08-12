@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Vendor List</h5>
                    <a href="{{ route('vendors.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus"></i> Add New Vendor
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
                                    <th width="250px">Actions</th>
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
                                        @if($vendor->approval_status == 'approved')
                                            <span class="badge bg-success text-white">Approved</span>
                                            <br><small>{{ $vendor->is_active ? 'Active' : 'Inactive' }}</small>
                                        @elseif($vendor->approval_status == 'rejected')
                                            <span class="badge bg-danger text-white">Rejected</span>
                                            @if($vendor->rejection_reason)
                                                <br><small class="text-danger">Reason: {{ $vendor->rejection_reason }}</small>
                                            @endif
                                        @else
                                            <span class="badge bg-warning text-dark">
                                                {{ $vendor->is_completed ? 'Pending Approval' : 'In Progress (Step ' . $vendor->current_step . ')' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('vendors.show', $vendor->id) }}" class="btn btn-info btn-sm" title="View">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            @if(!$vendor->is_completed)
                                                <a href="{{ route('vendors.edit', $vendor->id) }}" class="btn btn-primary btn-sm" title="Edit">
                                                    <i class="ri-pencil-fill"></i>
                                                </a>
                                            @endif
                                            @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin']) && $vendor->is_completed && $vendor->approval_status == 'pending')
                                                <form action="{{ route('vendors.approve', $vendor->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" title="Approve" onclick="return confirm('Are you sure you want to approve this vendor?')">
                                                        <i class="ri-edit-circle-line"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-danger btn-sm" title="Reject" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $vendor->id }}">
                                                    <i class="ri-chat-delete-fill"></i>
                                                </button>
                                            @endif
                                            @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin']) && in_array($vendor->approval_status, ['approved', 'rejected']))
                                                <form action="{{ route('vendors.toggle-active', $vendor->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-{{ $vendor->is_active ? 'warning' : 'success' }} btn-sm" title="{{ $vendor->is_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="ri-{{ $vendor->is_active ? 'close-line' : 'check-line' }}"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
                                                <form action="{{ route('vendors.destroy', $vendor->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this vendor?')">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                <!-- Rejection Modal -->
                                <div class="modal fade" id="rejectModal{{ $vendor->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $vendor->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="rejectModalLabel{{ $vendor->id }}">Reject Vendor</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('vendors.reject', $vendor->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="rejection_reason">Reason for Rejection</label>
                                                        <textarea class="form-control" name="rejection_reason" id="rejection_reason" rows="4" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Reject Vendor</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
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

@push('styles')
<style>
    .table td, .table th {
        vertical-align: middle;
    }
    
    .badge {
        font-size: 0.85rem;
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
    
    .bg-success {
        background-color: #28a745 !important;
    }
    
    .bg-warning {
        background-color: #ffc107 !important;
    }
    
    .bg-danger {
        background-color: #dc3545 !important;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    .btn i {
        font-size: 0.875rem;
        vertical-align: middle;
    }
</style>
@endpush