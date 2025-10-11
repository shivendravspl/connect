@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            <i class="ri-check-line me-2"></i> MIS Verification List
        </h5>
    </div>

    <div class="card shadow-sm rounded-3">
        <div class="card-header bg-light py-2">
            <h6 class="mb-0"><i class="ri-file-list-line me-2"></i> Applications Awaiting Verification</h6>
        </div>
        <div class="card-body p-0">
            @if($misApplications->isNotEmpty())
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:5%">#</th>
                            <th style="width:10%">Distributor Name</th>
                            <th style="width:5%">Zone</th>
                            <th style="width:20%">Date Received</th>
                            <th style="width:10%">Doc Verification</th>
                            <th style="width:10%">Agreement Status</th>
                            <th style="width:10%">Physical Docs</th>
                            <th style="width:10%">Final Status</th>
                            <th style="width:20%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($misApplications as $index => $application)
                        <tr>
                            <td class="align-middle small">{{ $index + 1 }}</td>
                            <td class="align-middle small">
                                {{ $application->entityDetails->establishment_name ?? 'N/A' }}
                            </td>
                             <td class="align-middle small">
                                {{ $application->zoneDetail->zone_name ?? 'N/A' }}
                            </td>
                            <td class="align-middle small">
                                {{ $application->created_at ? $application->created_at->format('d-m-Y') : 'N/A' }}
                            </td>
                            <td class="align-middle small">
                                {{ $application->doc_verification_status ?? 'pending' }}
                            </td>
                             <td class="align-middle small">
                                {{ $application->agreement_status ?? 'pending' }}
                            </td>
                            <td class="align-middle small">
                                {{ $application->physical_docs_status ?? 'pending' }}
                            </td>

                            <td class="align-middle small">
                                <span class="badge {{ $application->status === 'mis_processing' ? 'bg-info' : ($application->status === 'documents_pending' ? 'bg-warning' : ($application->status === 'documents_resubmitted' ? 'bg-primary' : 'bg-success')) }} fs-sm">
                                    {{ ucwords(str_replace('_', ' ', $application->status)) }}
                                </span>
                            </td>
                            <td class="text-center align-middle">
                                @if($application->status === 'document_verified' && $application->doc_verification_status === 'document_verified')
                                    <button class="btn btn-success btn-sm" disabled title="Verification Completed">
                                        <i class="ri-check-line"></i> Verified
                                    </button>
                                    <a href="{{ route('approvals.physical-documents', $application) }}" 
                                       class="btn btn-outline-primary btn-sm" 
                                       title="Manage Physical Documents">
                                        <i class="ri-file-text-line"></i> Physical Documents
                                    </a>
                                    @elseif($application->status == 'physical_docs_verified')
                                     <button class="btn btn-success btn-sm" disabled title="Verification Completed">
                                        <i class="ri-check-line"></i> Physical Docs Verified
                                    </button>
                                @else
                                    <a href="{{ route('approvals.show', $application->id) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="View"><i class="ri-eye-line" style="margin-bottom: 1px;"></i></a>
                                    @if (in_array($application->status, ['mis_processing', 'documents_pending', 'documents_resubmitted']))
    @php
        $verifyLabels = [
            'mis_processing' => 'Verify',
            'documents_pending' => 'Verify/Reverted',
            'documents_resubmitted' => 'Reverify',
        ];

        $verifyTitles = [
            'mis_processing' => 'Verify Documents',
            'documents_pending' => 'Verify or Reverted Documents',
            'documents_resubmitted' => 'Reverify Documents',
        ];

        $label = $verifyLabels[$application->status] ?? 'Verify';
        $title = $verifyTitles[$application->status] ?? 'Verify Documents';
    @endphp

    <a class="dropdown-item mis-action-btn"
       href="{{ route('approvals.verify-documents', $application->id) }}"
       title="{{ $title }}"
       data-application-id="{{ $application->id }}"
       data-distributor-name="{{ $application->entityDetails->establishment_name ?? 'N/A' }}"
       data-submission-date="{{ $application->created_at->format('Y-m-d') }}"
       data-initiator="{{ $application->createdBy->emp_name ?? 'N/A' }}"
       data-status="{{ $application->status }}">
        <i class="ri-check-line align-bottom me-2 text-muted"></i> {{ $label }} Checklist
    </a>
@endif

                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light py-2">
                {{ $misApplications->links() }}
            </div>
            @else
            <div class="p-3 text-center text-muted">
                <i class="ri-file-list-line fs-3 mb-2 d-block"></i>
                <p class="mb-0 small">No applications awaiting verification</p>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.card {
    border-radius: 0.75rem !important;
    transition: all 0.2s ease;
}
.card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}
.table-sm th, .table-sm td {
    padding: 0.4rem 0.5rem;
    font-size: 0.85rem;
}
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
}
.fs-sm {
    font-size: 0.8rem !important;
}
.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
@media (max-width: 576px) {
    .table-responsive {
        font-size: 0.75rem;
    }
}
@media print {
    .btn, .card-header, .card-footer {
        display: none !important;
    }
    .table {
        font-size: 0.8rem;
    }
}
</style>
@endsection