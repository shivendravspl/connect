<!-- Document Verification Modal -->
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

<!-- Physical Document Verification Modal -->
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

<!-- Confirm Distributor Modal -->
<div class="modal fade" id="confirmDistributorModal" tabindex="-1" aria-labelledby="confirmDistributorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDistributorModalLabel">Confirm Distributor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to confirm the distributor for Application ID <span id="confirm-application-id"></span> (<span id="confirm-distributor-name"></span>)?</p>
                <div class="form-group mb-3">
                    <label for="confirm-remarks">Remarks (optional)</label>
                    <textarea class="form-control" id="confirm-remarks" name="remarks" rows="4" placeholder="Enter any remarks"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="confirm-distributor-submit">Confirm</button>
            </div>
        </div>
    </div>
</div>