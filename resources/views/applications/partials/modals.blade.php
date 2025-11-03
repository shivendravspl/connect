
{{-- Document Verification Modal --}}
<div class="modal fade" id="docVerificationModal" tabindex="-1" aria-labelledby="docVerificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="docVerificationModalLabel">Document Verification Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="doc-verification-content">Loading...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Physical Document Verification Modal --}}
<div class="modal fade" id="physicalDocVerificationModal" tabindex="-1" aria-labelledby="physicalDocVerificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="physicalDocVerificationModalLabel">Physical Document Verification Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="physical-doc-verification-content">Loading...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Action Modal (for Approver) --}}
   <!-- Common Action Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="action-form" method="POST" action="">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="actionModalLabel">Take Action on Application</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Basic Details -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Application Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Distributor:</strong> <span id="modal-distributor-name">N/A</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Submitted:</strong> <span id="modal-submission-date">N/A</span><br>
                                        <strong>Initiator:</strong> <span id="modal-initiator">N/A</span><br>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Selection -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="actionType" class="form-label">Action <span class="text-danger">*</span></label>
                                <select id="actionType" name="action" class="form-select" required>
                                    <option value="" disabled selected>Choose action...</option>
                                    <option value="approve">Approve</option>
                                    <option value="revert">Revert</option>
                                    <option value="hold">Hold</option>
                                    <option value="reject">Reject</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="modal-action-date" class="form-label">Action Date</label>
                                <input type="date" id="modal-action-date" name="action_date" class="form-control" readonly value="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks <span class="text-danger">*</span></label>
                            <textarea id="remarks" name="remarks" class="form-control" rows="3" required placeholder="Enter your remarks/comments..."></textarea>
                        </div>

                        <!-- Follow-up Date (for Hold) -->
                        <div class="mb-3 d-none" id="followUpSection">
                            <label for="follow_up_date" class="form-label">Follow-up Date <span class="text-danger">*</span></label>
                            <input type="date" id="follow_up_date" name="follow_up_date" class="form-control" min="{{ now()->addDay()->format('Y-m-d') }}">
                            <div class="form-text">Application will be followed up on this date</div>
                        </div>

                        <input type="hidden" id="application_id" name="application_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="action-submit-btn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span class="submit-text">Submit</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@include('mis.partials.modals')