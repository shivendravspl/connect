@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Vendor Edit: {{ $tempEdit->vendor->company_name }}</h3>
                    <div class="badge bg-{{ $tempEdit->approval_status == 'pending' ? 'warning' : ($tempEdit->approval_status == 'approved' ? 'success' : 'danger') }} text-white">
                        {{ ucfirst($tempEdit->approval_status) }}
                    </div>
                </div>
                <div class="card-body">
                    @if($tempEdit->rejection_reason)
                        <div class="alert alert-danger">Rejection Reason: {{ $tempEdit->rejection_reason }}</div>
                    @endif
                    <h5>Proposed Changes</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Current Value</th>
                                <th>Proposed Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $fileFields = [
                                    'pan_card_copy_path' => 'PAN Card',
                                    'aadhar_card_copy_path' => 'Aadhar Card',
                                    'gst_certificate_copy_path' => 'GST Certificate',
                                    'msme_certificate_copy_path' => 'MSME Certificate',
                                    'cancelled_cheque_copy_path' => 'Cancelled Cheque',
                                    'agreement_copy_path' => 'Agreement',
                                    'other_documents_path' => 'Other Documents',
                                ];
                            @endphp
                            @foreach($tempEdit->getAttributes() as $key => $value)
                                @if(!in_array($key, ['id', 'vendor_id', 'submitted_by', 'approval_status', 'is_active', 'is_completed', 'current_step', 'rejection_reason', 'approved_by', 'approved_at', 'created_at', 'updated_at']) && $value !== null && $value !== $tempEdit->vendor->$key)
                                    <tr>
                                        <td>{{ isset($fileFields[$key]) ? $fileFields[$key] : str_replace('_', ' ', ucfirst($key)) }}</td>
                                        <td>
                                            @if(isset($fileFields[$key]))
                                                @if($tempEdit->vendor->$key)
                                                    <a href="{{ route('vendors.documents.show', ['id' => $tempEdit->vendor->id, 'type' => str_replace('_copy_path', '', $key)]) }}" target="_blank">View Current Document</a>
                                                @else
                                                    N/A
                                                @endif
                                            @else
                                                {{ $tempEdit->vendor->$key ?? 'N/A' }}
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($fileFields[$key]))
                                                <a href="{{ route('temp-document', ['id' => $tempEdit->id, 'type' => str_replace('_copy_path', '', $key)]) }}" target="_blank">View Proposed Document</a>
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between mt-3">
                        <a href="{{ route('temp-edits') }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Back to List
                        </a>
                        @if($tempEdit->approval_status == 'pending')
                            <div>
                                <form action="{{ route('temp-edits.approve', $tempEdit->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this edit?')">
                                        <i class="ri-check-line"></i> Approve
                                    </button>
                                </form>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i class="ri-close-line"></i> Reject
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Vendor Edit</h5>
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
@endsection

@push('styles')
<style>
    .table td, .table th {
        vertical-align: middle;
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