<!-- resources/views/approvals/partials/take-action.blade.php -->
@if(auth()->user()->emp_id === $application->current_approver_id)
    {{--<div id="take-action" class="form-section mb-3">
        <h5 class="mb-2">Take Action</h5>
        <form id="approve-form" action="{{ route('approvals.approve', $application) }}" method="POST" class="d-inline">
            @csrf
            <div class="mb-3">
                <label for="approveRemarks" class="form-label">Remarks (Optional)</label>
                <textarea name="remarks" id="approveRemarks" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" id="approve-button" class="btn btn-sm btn-success">Approve</button>
        </form>
        <button type="button" class="btn btn-sm btn-warning ms-2" data-bs-toggle="modal" data-bs-target="#revertModal">Revert</button>
        <button type="button" class="btn btn-sm btn-secondary ms-2" data-bs-toggle="modal" data-bs-target="#holdModal">Hold</button>
        <button type="button" class="btn btn-sm btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>

        <!-- Revert Modal -->
        <div class="modal fade" id="revertModal" tabindex="-1" aria-labelledby="revertModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('approvals.revert', $application) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="revertModalLabel">Revert Application</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="revertRemarks" class="form-label">Reason for Revert *</label>
                                <textarea name="remarks" id="revertRemarks" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-warning">Confirm Revert</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Hold Modal -->
        <div class="modal fade" id="holdModal" tabindex="-1" aria-labelledby="holdModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('approvals.hold', $application) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="holdModalLabel">Put Application On Hold</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="holdRemarks" class="form-label">Reason for Hold *</label>
                                <textarea name="remarks" id="holdRemarks" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="followUpDate" class="form-label">Follow-up Date *</label>
                                <input type="date" name="follow_up_date" id="followUpDate" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-secondary">Confirm Hold</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('approvals.reject', $application) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="rejectModalLabel">Reject Application</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="rejectRemarks" class="form-label">Reason for Rejection *</label>
                                <textarea name="remarks" id="rejectRemarks" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-sm btn-danger">Confirm Rejection</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>--}}
@endif