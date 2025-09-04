@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Vendor List</h5>
                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Mis User']))
                        <a href="{{ route('vendors.create') }}" class="btn btn-primary btn-xs">
                            <i class="ri-add-box-fill"></i>
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('info'))
                        <div class="alert alert-info">{{ session('info') }}</div>
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
                                    <th width="200px">Actions</th>
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
                                        <div class="d-flex justify-content-around gap-2">
                                            <a href="{{ route('vendors.show', $vendor->id) }}" class="btn btn-info btn-xs" title="View">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            @if(!$vendor->is_completed)
                                                <a href="{{ route('vendors.edit', $vendor->id) }}" class="btn btn-primary btn-xs" title="Edit">
                                                    <i class="ri-pencil-fill"></i>
                                                </a>
                                            @endif
                                            @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin']) && $vendor->is_completed && $vendor->approval_status == 'pending')
                                                <form action="{{ route('approve', $vendor->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-xs" title="Approve" onclick="return confirm('Are you sure you want to approve this vendor?')">
                                                        <i class="ri-edit-circle-line"></i>
                                                    </button>
                                                </form>
                                                <button type="button" class="btn btn-danger btn-xs" title="Reject" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $vendor->id }}">
                                                    <i class="ri-chat-delete-fill"></i>
                                                </button>
                                            @endif
                                            @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin']) && in_array($vendor->approval_status, ['approved', 'rejected']))
                                                <button type="button" class="btn btn-xs {{ $vendor->is_active ? 'btn-success' : 'btn-secondary' }}" title="{{ $vendor->is_active ? 'Active (Click to Deactivate)' : 'Inactive (Click to Activate)' }}" data-bs-toggle="modal" data-bs-target="#toggleActiveModal{{ $vendor->id }}">
                                                    <i class="ri-{{ $vendor->is_active ? 'check-line' : 'close-line' }}"></i>
                                                    {{ $vendor->is_active ? 'Active' : 'Inactive' }}
                                                </button>
                                            @endif
                                            @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
                                                <form action="{{ route('vendors.destroy', $vendor->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs" title="Delete" onclick="return confirm('Are you sure you want to delete this vendor?')">
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
                                                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('reject', $vendor->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="rejection_reason">Reason for Rejection</label>
                                                        <textarea class="form-control" name="rejection_reason" id="rejection_reason" rows="4" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-xs btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-xs btn-danger">Reject Vendor</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <!-- Toggle Active/Inactive Modal -->
                                <div class="modal fade" id="toggleActiveModal{{ $vendor->id }}" tabindex="-1" aria-labelledby="toggleActiveModalLabel{{ $vendor->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="toggleActiveModalLabel{{ $vendor->id }}">
                                                    {{ $vendor->is_active ? 'Deactivate' : 'Activate' }} Vendor
                                                </h5>
                                                <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('vendors.toggle-active', $vendor->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <p>
                                                        Are you sure you want to {{ $vendor->is_active ? 'deactivate' : 'activate' }} the vendor "{{ $vendor->company_name }}"?
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-xs btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-xs {{ $vendor->is_active ? 'btn-danger' : 'btn-success' }}">
                                                        {{ $vendor->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
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
        font-size: 0.65rem;
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
        font-size: 0.640rem;
        padding: 0.25rem 0.5rem;
    }
    
    .btn-xs {   
        font-size: 0.6rem;
        padding: 0.2rem 0.4rem;
        line-height: 1.2;
    }
    
    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .d-flex.gap-2 {
        gap: 0.3rem; /* Reduced gap for smaller buttons */
    }
</style>
@endpush