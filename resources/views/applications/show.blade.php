@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Application: {{ $application->application_code }}</h4>
                    <div>
                        <span class="badge bg-{{ $application->status_badge }}">
                            {{ ucfirst($application->status) }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <ul class="nav nav-tabs" id="applicationTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button">Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button">Documents</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="approvals-tab" data-bs-toggle="tab" data-bs-target="#approvals" type="button">Approval History</button>
                        </li>
                        @if($application->agreement)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="agreement-tab" data-bs-toggle="tab" data-bs-target="#agreement" type="button">Agreement</button>
                        </li>
                        @endif
                    </ul>

                    <div class="tab-content py-3" id="applicationTabsContent">
                        <div class="tab-pane fade show active" id="details" role="tabpanel">
                            @include('applications.show-sections.details')
                        </div>
                        
                        <div class="tab-pane fade" id="documents" role="tabpanel">
                            @include('applications.show-sections.documents')
                        </div>
                        
                        <div class="tab-pane fade" id="approvals" role="tabpanel">
                            @include('applications.show-sections.approvals')
                        </div>
                        
                        @if($application->agreement)
                        <div class="tab-pane fade" id="agreement" role="tabpanel">
                            @include('applications.show-sections.agreement')
                        </div>
                        @endif
                    </div>
                </div>
                
                @if($application->canBeApprovedBy(auth()->user()))
                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times"></i> Reject
                        </button>
                        <button class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#revertModal">
                            <i class="fas fa-undo"></i> Revert
                        </button>
                        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#holdModal">
                            <i class="fas fa-pause"></i> Hold
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($application->canBeApprovedBy(auth()->user()))
<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('applications.approve', $application) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Approve Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="approveRemarks" class="form-label">Remarks (Optional)</label>
                        <textarea class="form-control" id="approveRemarks" name="remarks" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Other action modals (Reject, Revert, Hold) would be similar -->
@endif
@endsection

@push('scripts')
<script>
    // Activate Bootstrap tabs
    const tabElms = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabElms.forEach(tabEl => {
        tabEl.addEventListener('click', event => {
            event.preventDefault();
            const tab = new bootstrap.Tab(event.target);
            tab.show();
        });
    });
</script>
@endpush