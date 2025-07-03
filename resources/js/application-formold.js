$(document).ready(function() {
    const form = $('#distributorForm');
    const steps = $('.step');
    const stepContents = $('.step-content');
    const prevBtn = $('.previous');
    const nextBtn = $('.next');
    const submitBtn = $('.submit');
    
    let currentStep = 1;
    const totalSteps = steps.length;
    
    // Initialize form
    showStep(currentStep);
    updateButtons();
    
    // Next button click
    nextBtn.click(function() {
    if (validateStep(currentStep)) {
        // Save current step data before proceeding
            saveStep(currentStep).then(() => {
                currentStep++;
                showStep(currentStep);
                updateButtons();
                updateStepper();
                scrollToTop();
            }).catch(error => {
                console.error('Error saving step:', error);
                alert('Failed to save current step. Please try again.');
            });
        }
    });


    // Function to save step data via AJAX
function saveStep(stepNumber) {
    return new Promise((resolve, reject) => {
        // Get all form data
        const formData = new FormData(document.getElementById('distributorForm'));
        
        // Add the current step to the form data
        formData.append('current_step', stepNumber);

        // Show loading indicator
        nextBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

        $.ajax({
            url: '/applications/save-step/' + stepNumber,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                nextBtn.prop('disabled', false).html('Next');
                if (response.success) {
                    resolve(response);
                } else {
                    reject(response.error || 'Unknown error');
                }
            },
            error: function(xhr) {
                nextBtn.prop('disabled', false).html('Next');
                reject(xhr.responseJSON?.error || 'Server error');
            }
        });
    });
}
    
    // Previous button click
    prevBtn.click(function() {
        currentStep--;
        showStep(currentStep);
        updateButtons();
        updateStepper();
        scrollToTop();
    });
    
    // Show the current step and hide others
    function showStep(stepNumber) {
        stepContents.hide();
        $(`.step-content[data-step="${stepNumber}"]`).show();
    }
    
    // Update button visibility
    function updateButtons() {
        prevBtn.toggle(currentStep > 1);
        nextBtn.toggle(currentStep < totalSteps);
        submitBtn.toggle(currentStep === totalSteps);
    }
    
    // Update stepper UI
    function updateStepper() {
        steps.removeClass('active completed');
        steps.each(function(index) {
            if (index + 1 < currentStep) {
                $(this).addClass('completed');
            } else if (index + 1 === currentStep) {
                $(this).addClass('active');
            }
        });
    }
    
    // Validate current step
    function validateStep(stepNumber) {
        const currentSection = $(`.step-content[data-step="${stepNumber}"]`);
        const inputs = currentSection.find('input[required], select[required], textarea[required]');
        let isValid = true;
        
        inputs.each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        alert(stepNumber);
        // Special validation for entity type specific fields
        const entityType = $('select[name="entity_type"]').val();
        if ((entityType === 'individual' || entityType === 'sole_proprietorship') && 
            stepNumber === 2 && // ownership details step
            (!$('#owner_name').val() || !$('#dob').val() || !$('#permanent_address').val())) {
            isValid = false;
            alert('Please fill all required ownership details before proceeding.');
        }
        
        if (!isValid && !(entityType === 'individual' || entityType === 'sole_proprietorship' && stepNumber === 2)) {
            currentSection.find('.is-invalid').first().focus();
            alert('Please fill all required fields before proceeding.');
        }
        
        return isValid;
    }
    
    function scrollToTop() {
        $('html, body').animate({
            scrollTop: $('.stepper-wrapper').offset().top - 20
        }, 300);
    }
    
    // Form submission handling
    form.on('submit', function(e) {
        if (!validateStep(currentStep)) {
            e.preventDefault();
            return false;
        }
        
        // Additional validation for declarations
        if (currentStep >= 7 && (!$('#declaration_truthful').is(':checked') || !$('#declaration_update').is(':checked'))) {
            alert('Please accept all declarations before submitting.');
            e.preventDefault();
            return false;
        }
        
        if (currentStep === totalSteps && !$('#confirm_accuracy').is(':checked')) {
            alert('Please confirm the accuracy of your information before submitting.');
            e.preventDefault();
            return false;
        }
        
        return true;
    });
    
    // Dynamic ownership section handling
    $('select[name="entity_type"]').change(function() {
        const type = $(this).val();
        $('.ownership-section').hide();
        
        if (type === 'individual' || type === 'sole_proprietorship') {
            $('#ownership-individual').show();
            // Make individual fields required
            $('#owner_name, #dob, #permanent_address').prop('required', true);
        } else if (type === 'partnership' || type === 'llp') {
            $('#ownership-partnership').show();
        }
    }).trigger('change');
    
    // Business plans dynamic rows
    $(document).on('click', '.add-business-plan', function() {
        const container = $(this).closest('.form-section').find('#business-plan-container');
        const index = container.find('.business-plan-row').length;
        const newRow = $(`
            <div class="business-plan-row mb-4 border p-3">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Crop *</label>
                            <input type="text" class="form-control" name="business_plans[${index}][crop]" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="form-label">FY 2025-26 (MT) *</label>
                            <input type="number" class="form-control" name="business_plans[${index}][fy2025_26_MT]" min="0" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label class="form-label">FY 2026-27 (MT) *</label>
                            <input type="number" class="form-control" name="business_plans[${index}][fy2026_27_MT]" min="0" step="0.01" required>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-danger remove-business-plan">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
        `);
        
        container.append(newRow);
    });
    
    $(document).on('click', '.remove-business-plan', function() {
        if ($('.business-plan-row').length > 1) {
            $(this).closest('.business-plan-row').remove();
        } else {
            alert('At least one business plan is required.');
        }
    });
    
    // Existing distributorships dynamic rows
    $(document).on('click', '.add-distributorship', function() {
        const container = $(this).closest('.form-section').find('#distributorship-container');
        const index = container.find('.distributorship-row').length;
        const newRow = $(`
            <div class="distributorship-row mb-3">
                <div class="row">
                    <div class="col-md-10">
                        <div class="form-group mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" class="form-control" name="existing_distributorships[${index}][company_name]">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-danger remove-distributorship" style="margin-top: 30px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `);
        
        container.append(newRow);
    });
    
    $(document).on('click', '.remove-distributorship', function() {
        $(this).closest('.distributorship-row').remove();
    });
    
    // File upload preview
    $(document).on('change', 'input[type="file"]', function() {
        const input = $(this);
        const preview = input.siblings('.file-preview');
        const fileName = input[0].files[0]?.name || 'No file chosen';
        preview.text(fileName);
    });
    
    // Generate review summary when reaching the review step
    $(document).on('click', '.next', function() {
        if (currentStep === totalSteps - 1) { // About to show review step
            generateReviewSummary();
        }
    });
    
    function generateReviewSummary() {
        let summaryHtml = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Entity Details</h6>
                    <p><strong>Name:</strong> <span id="review-establishment-name">${$('#establishment_name').val()}</span></p>
                    <p><strong>Type:</strong> <span id="review-entity-type">${$('#entity_type option:selected').text()}</span></p>
                    <p><strong>Address:</strong> <span id="review-business-address">${$('#business_address').val()}</span></p>
                </div>
                <div class="col-md-6">
                    <h6>Contact Information</h6>
                    <p><strong>Mobile:</strong> <span id="review-mobile">${$('#mobile').val()}</span></p>
                    <p><strong>Email:</strong> <span id="review-email">${$('#email').val()}</span></p>
                    <p><strong>PAN:</strong> <span id="review-pan">${$('#pan_number').val()}</span></p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <h6>Bank Details</h6>
                    <p><strong>Bank Name:</strong> <span id="review-bank-name">${$('#bank_name').val()}</span></p>
                    <p><strong>Account Number:</strong> <span id="review-account-number">${$('#account_number').val()}</span></p>
                </div>
                <div class="col-md-6">
                    <h6>Financial Information</h6>
                    <p><strong>Net Worth:</strong> ₹<span id="review-net-worth">${$('#net_worth').val()}</span></p>
                    <p><strong>Years in Business:</strong> <span id="review-years-business">${$('#years_in_business').val()}</span></p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <h6>Business Plan</h6>
                    <div id="review-business-plans"></div>
                </div>
            </div>`;
        
        $('#review-summary').html(summaryHtml);
        
        // Business plans
        let businessPlansHtml = '<ul>';
        $('[name^="business_plans["]').each(function() {
            if ($(this).attr('name').includes('[crop]') && $(this).val()) {
                const index = $(this).attr('name').match(/\[(\d+)\]/)[1];
                const crop = $(this).val();
                const fy1 = $(`[name="business_plans[${index}][fy2025_26_MT]"]`).val();
                const fy2 = $(`[name="business_plans[${index}][fy2026_27_MT]"]`).val();
                
                businessPlansHtml += `<li>${crop}: ${fy1} MT (2025-26), ${fy2} MT (2026-27)</li>`;
            }
        });
        businessPlansHtml += '</ul>';
        $('#review-business-plans').html(businessPlansHtml);
    }
});