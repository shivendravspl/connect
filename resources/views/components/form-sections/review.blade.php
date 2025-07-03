<div id="review" class="form-section">
    <h5 class="mb-4">Review & Submit</h5>
    
    <div class="alert alert-warning">
        <strong>Important:</strong> Please review all the information carefully before submitting. Once submitted, you won't be able to edit the application unless it's reverted by the approver.
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0">Application Summary</h6>
        </div>
        <div class="card-body">
            <div id="review-summary">
                <p class="text-muted">Reviewing your application details...</p>
            </div>
        </div>
    </div>
    
    <div class="form-group mb-4 form-check">
        <input type="checkbox" class="form-check-input" id="confirm_accuracy" required>
        <label class="form-check-label" for="confirm_accuracy">
            I confirm that all the information provided in this application is accurate to the best of my knowledge.
        </label>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Generate dynamic review summary
    function generateReviewSummary() {
        // Helper functions
        const getValue = (selector, defaultValue = 'Not provided') => {
            const val = $(selector).val();
            return val ? val : defaultValue;
        };
        
        const getSelectedText = (selector, defaultValue = 'Not provided') => {
            const text = $(selector).find('option:selected').text();
            return text ? text : defaultValue;
        };

        // Get current date in Indian format
        const today = new Date().toLocaleDateString('en-IN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });

        // Get verification data (replace with your actual data source)
        const verificationData = {
            filledBy: getValue('#applicant_name'),
            verifiers: [
                {
                    name: "Amir Khan", // Should come from your system
                    designation: "Area Business Manager"
                },
                {
                    name: "Surendra", // Should come from your system
                    designation: "Regional Business Manager"
                }
            ]
        };

        // Build business plans HTML
        let businessPlansHtml = '<ul>';
        $('[name^="business_plans["]').each(function() {
            if ($(this).attr('name').includes('[crop]') && $(this).val()) {
                const index = $(this).attr('name').match(/\[(\d+)\]/)[1];
                const crop = $(this).val();
                const fy1 = getValue(`[name="business_plans[${index}][fy2025_26_MT]"]`, '0');
                const fy2 = getValue(`[name="business_plans[${index}][fy2026_27_MT]"]`, '0');
                
                businessPlansHtml += `<li>${crop}: ${fy1} MT (2025-26), ${fy2} MT (2026-27)</li>`;
            }
        });
        businessPlansHtml += businessPlansHtml === '<ul>' ? '<li>No business plans added</li></ul>' : '</ul>';

        // Build verification HTML
        let verificationHtml = `
        <div class="verification-section mt-4">
            <h6>Verification Details</h6>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Form Filled by:</strong> ${verificationData.filledBy}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Date:</strong> ${today}</p>
                </div>
            </div>
            
            <div class="mt-3">
                <p><strong>Verified by VNR Seeds Pvt. Ltd.</strong></p>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>`;
        
        verificationData.verifiers.forEach(verifier => {
            verificationHtml += `
                            <tr>
                                <td>${verifier.name}</td>
                                <td>${verifier.designation}</td>
                                <td>${today}</td>
                            </tr>`;
        });
        
        verificationHtml += `
                        </tbody>
                    </table>
                </div>
            </div>
        </div>`;

        // Build complete summary HTML
        const summaryHtml = `
        <div class="row">
            <div class="col-md-6">
                <h6>Entity Details</h6>
                <p><strong>Name:</strong> ${getValue('#establishment_name')}</p>
                <p><strong>Type:</strong> ${getSelectedText('#entity_type')}</p>
                <p><strong>Address:</strong> ${getValue('#business_address')}</p>
            </div>
            <div class="col-md-6">
                <h6>Contact Information</h6>
                <p><strong>Mobile:</strong> ${getValue('#mobile')}</p>
                <p><strong>Email:</strong> ${getValue('#email')}</p>
                <p><strong>PAN:</strong> ${getValue('#pan_number')}</p>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <h6>Bank Details</h6>
                <p><strong>Bank Name:</strong> ${getValue('#bank_name')}</p>
                <p><strong>Account Number:</strong> ${getValue('#account_number')}</p>
            </div>
            <div class="col-md-6">
                <h6>Financial Information</h6>
                <p><strong>Net Worth:</strong> ₹${getValue('#net_worth', '0')}</p>
                <p><strong>Years in Business:</strong> ${getValue('#years_in_business', '0')}</p>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <h6>Business Plan</h6>
                ${businessPlansHtml}
            </div>
        </div>
        ${verificationHtml}`;

        $('#review-summary').html(summaryHtml);
    }

    // Generate summary immediately when page loads (for testing)
    generateReviewSummary();
    
    // Also generate when review section is shown via navigation
    $(document).on('click', '.next', function() {
        if ($(this).closest('.form-navigation').prev().attr('id') === 'review') {
            generateReviewSummary();
        }
    });
});
</script>
@endpush