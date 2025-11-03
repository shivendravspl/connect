
 @php
 $authorizedEmployee = \App\Models\User::where('emp_id', 1971)->first()?->employee;
@endphp
<!-- Confirm Distributor Modal -->
<div class="modal fade" id="confirmDistributorModal" tabindex="-1" aria-labelledby="confirmDistributorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDistributorModalLabel">Finalize distributorship appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="confirm-distributor-form">
                @csrf
                  <input type="hidden" name="authorized_person_name" id="authorized-person-name-input" value="{{ $authorizedEmployee->emp_name ?? '' }}">
    <input type="hidden" name="authorized_person_designation" id="authorized-person-designation-input" value="{{ $authorizedEmployee->emp_designation ?? '' }}">
                <div class="modal-body">
                    <p class="mb-4">Are you sure you want to confirm the distributor for Application ID <strong><span id="confirm-application-id"></span></strong> (<strong><span id="confirm-distributor-name"></span></strong>)?</p>

                    <!-- Verification and Approval Structure -->
                    <div class="verification-approval-structure">
                        <!-- Details of appointment -->
                        <div class="appointment-details-section">
                            <h6 class="section-title mb-3">Details of appointment (Details Filled by Accounts receivable and MIS Team)</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="date-of-appointment" class="form-label"><strong>Date of Appointment</strong></label>
                                        <input type="date" class="form-control" id="date-of-appointment" name="date_of_appointment" required>
                                        <div class="invalid-feedback">Please select appointment date.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="distributor-code" class="form-label"><strong>Code of Distributor</strong></label>
                                        <input type="text" class="form-control" id="distributor-code" name="distributor_code" placeholder="Enter distributor code" required>
                                        <div class="invalid-feedback">Please enter distributor code.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks Section -->
                        <div class="remarks-section mt-4">
                            <div class="form-group">
                                <label for="confirm-remarks" class="form-label"><strong>Remarks (optional)</strong></label>
                                <textarea class="form-control" id="confirm-remarks" name="remarks" rows="3" placeholder="Enter any additional remarks"></textarea>
                            </div>
                        </div>

                        <!-- Checked & Verified by -->
                        <div class="verification-section mb-4">
                            <div class="verification-row mb-3">
                                <div class="row">
                                    <!-- Verified by -->
                                    <div class="col-md-6">
                                        <h6 class="subsection-title">Verified by</h6>
                                        <div class="signature-line mb-2"></div>
                                        <div class="verification-details">
                                            <div class="detail-item">
                                                <strong>Name:</strong>
                                                <span id="verified-by-name">{{ Auth::user()->employee->emp_name ?? 'N/A' }}</span>
                                            </div>
                                            <div class="detail-item">
                                                <strong>Designation:</strong>
                                                <span id="verified-by-designation">{{ Auth::user()->employee->emp_designation ?? 'N/A' }}</span>
                                            </div>
                                            <div class="detail-item">
                                                <strong>Date:</strong>
                                                <span id="verified-by-date">{{ date('Y-m-d') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Authorized Responsible Person (Dept) (MIS) -->
                       

                        <!-- Authorized Responsible Person (Dept) (MIS) -->
                        <div class="authorized-person-section mb-4">
                            <h6 class="section-title mb-3">Authorized Responsible Person (Dept) (MIS)</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="detail-item">
                                        <strong>Name:</strong>
                                        <span id="authorized-person-name">{{ $authorizedEmployee->emp_name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="detail-item">
                                        <strong>Designation:</strong>
                                        <span id="authorized-person-designation">{{ $authorizedEmployee->emp_designation ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="confirm-distributor-submit">Confirm Distributor</button>
                </div>
            </form>
        </div>
    </div>
</div>