@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Vendor List</h5>
                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Mis User']))
                        <a href="{{ route('vendors.create') }}" class="btn btn-primary btn-sm">
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
                                    <th width="120px">Actions</th>
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
                                        <div>
                                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-fill"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('vendors.show', $vendor->id) }}">
                                                        <i class="ri-eye-fill align-bottom me-2 text-muted"></i> View
                                                    </a>
                                                </li>
                                                @if(!$vendor->is_completed)
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('vendors.edit', $vendor->id) }}">
                                                            <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                                        </a>
                                                    </li>
                                                @endif
                                                @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin']) && $vendor->is_completed && $vendor->approval_status == 'pending')
                                                    <li>
                                                        <form action="{{ route('approve', $vendor->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item" onclick="return confirm('Are you sure you want to approve this vendor?')">
                                                                <i class="ri-edit-circle-line align-bottom me-2 text-muted"></i> Approve
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $vendor->id }}">
                                                            <i class="ri-chat-delete-fill align-bottom me-2 text-muted"></i> Reject
                                                        </button>
                                                    </li>
                                                @endif
                                                @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin']) && in_array($vendor->approval_status, ['approved', 'rejected']))
                                                    <li>
                                                        <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#toggleActiveModal{{ $vendor->id }}">
                                                            <i class="ri-{{ $vendor->is_active ? 'check-line' : 'close-line' }} align-bottom me-2 text-muted"></i>
                                                            {{ $vendor->is_active ? 'Deactivate' : 'Activate' }}
                                                        </button>
                                                    </li>
                                                @endif
                                                @if(auth()->user()->hasAnyRole(['Super Admin', 'Admin']))
                                                    <li class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('vendors.destroy', $vendor->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item" onclick="return confirm('Are you sure you want to delete this vendor?')">
                                                                <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                            </ul>
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
                                            <form action="{{ route('reject', $vendor->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="rejection_reason_{{ $vendor->id }}">Reason for Rejection</label>
                                                        <textarea class="form-control" name="rejection_reason" id="rejection_reason_{{ $vendor->id }}" rows="4" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-sm btn-danger">Reject Vendor</button>
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
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('vendors.toggle-active', $vendor->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <p>
                                                        Are you sure you want to {{ $vendor->is_active ? 'deactivate' : 'activate' }} the vendor "{{ $vendor->company_name }}"?
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-sm {{ $vendor->is_active ? 'btn-danger' : 'btn-success' }}">
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
        font-size: 0.6rem;
        padding: 0.2rem 0.4rem;
        line-height: 1.2;
        background-color: var(--vz-btn-hover-bg);
    }

    .btn.show {   
        color: var(--vz-btn-active-color);
        background-color: var(--vz-btn-active-bg);
        border-color: var(--vz-btn-active-border-color);
    }
    
    
    .btn-soft-secondary {
        background-color: rgba(108, 117, 125, 0.1);
        border-color: rgba(108, 117, 125, 0.2);
        color: #6c757d;
    }
    
    .btn-soft-secondary:hover, .btn-soft-secondary:focus {
        background-color: rgba(108, 117, 125, 0.2);
        border-color: rgba(108, 117, 125, 0.3);
        color: #6c757d;
    }

    .btn-success {
        background-color: #28a745;
        border-color: #28a745;
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .dropdown-menu {
        font-size: 0.85rem;
        z-index: 1050; /* Ensure dropdown appears above other elements */
    }

    .dropdown-item {
        padding: 0.25rem 1rem;
    }

    .dropdown-item i {
        font-size: 1rem;
        vertical-align: middle;
    }

    .modal {
        z-index: 1060; /* Higher than dropdown to ensure modal is visible */
    }

    .modal-content {
        opacity: 1 !important; /* Ensure modal content is fully visible */
        background-color: #fff; /* Explicit background color */
    }

    .modal-backdrop {
        z-index: 1055; /* Ensure backdrop is below modal but above dropdown */
        opacity: 0.5 !important; /* Standard backdrop opacity */
    }

    .modal-title, .modal-body, .modal-footer {
        color: #333 !important; /* Ensure text is visible */
    }
</style>
@endpush