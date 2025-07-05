<!-- Review Section -->
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

    {{--<button type="button" class="btn btn-primary" id="submit-application">Submit Application</button>--}}
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
        </div>`;

        $('#review-summary').html(summaryHtml);
    }

    // Generate summary when review section is shown
    $(document).on('click', '.next', function() {
        if ($(this).closest('.form-navigation').prev().attr('id') === 'review') {
            generateReviewSummary();
        }
    });

    // Handle final submission
    $('#submit-application').on('click', function() {
        if (!$('#confirm_accuracy').is(':checked')) {
            alert('Please confirm the accuracy of the information before submitting.');
            return;
        }

        const applicationId = $('#application_id').val(); // Ensure application_id is stored in a hidden input
        if (!applicationId) {
            alert('Error: Application ID is missing.');
            return;
        }

        $.ajax({
            url: `/distributor-applications/${applicationId}/submit`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                confirm_accuracy: true
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    window.location.href = '/dashboard'; // Redirect to dashboard or success page
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON.error || 'Failed to submit application.'));
            }
        });
    });

    // Generate summary immediately when page loads
    generateReviewSummary();
});
</script>
@endpush