<!-- resources/views/dashboard/partials/approval_modals.blade.php -->
<!-- Approve Modal -->
<div class="modal fade action-modal" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approve-form" method="POST" action="{{ url('approvals/0/approve') }}">
                @csrf
                <input type="hidden" name="application_id" id="approve_application_id">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="approve-remarks" class="form-label">Remarks (Optional)</label>
                        <textarea class="form-control" id="approve-remarks" name="remarks" rows="3"></textarea>
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

<!-- Revert Modal -->
<div class="modal fade action-modal" id="revertModal" tabindex="-1" aria-labelledby="revertModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="revert-form" method="POST" action="{{ url('approvals/0/revert') }}">
                @csrf
                <input type="hidden" name="application_id" id="revert_application_id">
                <div class="modal-header p-2">
                    <h5 class="modal-title" id="revertModalLabel">Revert Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2">
                    <div class="mb-2">
                        <label for="revertRemarks" class="form-label">Reason for Revert *</label>
                        <textarea name="remarks" id="revertRemarks" class="form-control form-control-sm" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-warning">Confirm Revert</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hold Modal -->
<div class="modal fade action-modal" id="holdModal" tabindex="-1" aria-labelledby="holdModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="hold-form" method="POST" action="{{ url('approvals/0/hold') }}">
                @csrf
                <input type="hidden" name="application_id" id="hold_application_id">
                <div class="modal-header p-2">
                    <h5 class="modal-title" id="holdModalLabel">Put Application On Hold</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2">
                    <div class="mb-2">
                        <label for="holdRemarks" class="form-label">Reason for Hold *</label>
                        <textarea name="remarks" id="holdRemarks" class="form-control form-control-sm" rows="3" required></textarea>
                    </div>
                    <div class="mb-2">
                        <label for="followUpDate" class="form-label">Follow-up Date *</label>
                        <input type="date" name="follow_up_date" id="followUpDate" class="form-control form-control-sm" required>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-secondary">Confirm Hold</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade action-modal" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="reject-form" method="POST" action="{{ url('approvals/0/reject') }}">
                @csrf
                <input type="hidden" name="application_id" id="reject_application_id">
                <div class="modal-header p-2">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2">
                    <div class="mb-2">
                        <label for="rejectRemarks" class="form-label">Reason for Rejection *</label>
                        <textarea name="remarks" id="rejectRemarks" class="form-control form-control-sm" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-danger">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>