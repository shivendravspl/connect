@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Pending Vendor Edit Approvals</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Vendor</th>
                                <th>Submitted By</th>
                                <th>Section</th>
                                <th>Submitted At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tempEdits as $tempEdit)
                                <tr>
                                    <td>{{ $tempEdit->vendor->company_name }}</td>
                                    <td>{{ $tempEdit->submittedBy->name }}</td>
                                    <td>{{ ucfirst($tempEdit->section ?? 'N/A') }}</td>
                                    <td>{{ $tempEdit->created_at->format('d M Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('temp-edits.show', $tempEdit->id) }}" class="btn btn-sm btn-primary">
                                            <i class="ri-eye-line"></i> View
                                        </a>
                                        <form action="{{ route('temp-edits.approve', $tempEdit->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this edit?')">
                                                <i class="ri-check-line"></i> Approve
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $tempEdit->id }}">
                                            <i class="ri-close-line"></i> Reject
                                        </button>
                                    </td>
                                </tr>
                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal{{ $tempEdit->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $tempEdit->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="rejectModalLabel{{ $tempEdit->id }}">Reject Vendor Edit</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('temp-edits.reject', $tempEdit->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="rejection_reason" class="form-label">Rejection Reason</label>
                                                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Reject</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>

                    {{ $tempEdits->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .app-menu.navbar-menu {
        display: block !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.querySelector('.app-menu.navbar-menu');
        if (sidebar) {
            sidebar.classList.add('show');
        }
    });
</script>
@endpush
