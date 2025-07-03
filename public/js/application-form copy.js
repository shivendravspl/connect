$(document).ready(function() {
    const form = $('#distributorForm');
    const steps = $('.step');
    const stepContents = $('.step-content');
    const prevBtn = $('.previous');
    const nextBtn = $('.next');
    const submitBtn = $('.submit');
    
    let currentStep = 1;
    const totalSteps = steps.length;
    
    // Initialize removedDocuments globally
    let removedDocuments = {};

    // Initialize form
    showStep(currentStep);
    updateButtons();
    
    // File upload preview (for step 9)
    $(document).on('change', 'input[type="file"]', function() {
        const input = $(this);
        const preview = input.siblings('.file-preview');
        const type = input.attr('name').replace('documents[', '').replace(']', '');
        const fileName = input[0].files[0]?.name || 'No file chosen';

        if (fileName !== 'No file chosen') {
            preview.html(fileName);
            // Clear removal flag if a new file is uploaded
            delete removedDocuments[type];
            // Re-apply required attribute for required documents
            if (input.hasClass('required-field')) {
                input.prop('required', true);
            }
        } else {
            preview.html('No file chosen');
            // Re-apply required attribute for required documents
            if (input.hasClass('required-field')) {
                input.prop('required', true);
            }
            delete removedDocuments[type];
        }
    });

    // Handle document removal (for step 9)
    $(document).on('click', '.remove-document', function() {
        const $button = $(this);
        const type = $button.data('type');
        const $fileInput = $button.closest('.file-upload-wrapper').find('input[type="file"]');
        const $preview = $button.siblings('.file-preview');
        const originalPreviewHtml = $preview.html(); // Store original HTML for reversion

        // Temporarily update UI
        $fileInput.val('');
        $preview.html('No file chosen');
        // Re-apply required attribute for required documents
        if ($fileInput.hasClass('required-field')) {
            $fileInput.prop('required', true);
        }

        Swal.fire({
            title: 'Are you sure?',
            text: 'This will remove the uploaded document.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove it!',
        }).then((result) => {
            if (result.isConfirmed) {
                // Send removal request
                $.ajax({
                    url: `/applications/remove-document/${$('#application_id').val()}`,
                    type: 'POST',
                    data: {
                        type: type,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            // Confirm removal in UI and state
                            removedDocuments[type] = 1;
                            showSidebarNotification('Document removed.', 'success');
                            // Ensure required attribute persists
                            if ($fileInput.hasClass('required-field')) {
                                $fileInput.prop('required', true);
                            }
                        } else {
                            // Revert UI on failure
                            $preview.html(originalPreviewHtml);
                            $fileInput.prop('required', false);
                            showErrorAlert(response.error || 'Failed to remove document');
                        }
                    },
                    error: function(xhr) {
                        // Revert UI on error
                        $preview.html(originalPreviewHtml);
                        $fileInput.prop('required', false);
                        Swal.fire('Error', xhr.responseJSON?.error || 'Failed to remove document', 'error');
                    }
                });
            } else {
                // Revert UI if user cancels
                $preview.html(originalPreviewHtml);
                $fileInput.prop('required', false);
            }
        });
    });

    // Clear file selection (for step 9)
    $(document).on('click', '.clear-file', function() {
        const wrapper = $(this).closest('.file-upload-wrapper');
        const $fileInput = wrapper.find('input[type="file"]');
        const type = $fileInput.attr('name').replace('documents[', '').replace(']', '');
        wrapper.find('.file-preview').html('No file chosen');
        $fileInput.val('');
        // Re-apply required attribute for required documents
        if ($fileInput.hasClass('required-field')) {
            $fileInput.prop('required', true);
        }
        // Clear removal flag
        delete removedDocuments[type];
    });

    // Next button click
    nextBtn.click(function() {
        if (validateStep(currentStep)) {
            saveStep(currentStep).then(() => {
                currentStep++;
                showStep(currentStep);
                updateButtons();
                updateStepper();
                scrollToTop();
            }).catch(error => {
                console.error('Error saving step:', error);
                showErrorAlert(error || 'Failed to save current step. Please try again.');
            });
        }
    });

    // Previous button click
    prevBtn.click(function() {
        currentStep--;
        showStep(currentStep);
        updateButtons();
        updateStepper();
        scrollToTop();
    });

    // Function to save step data via AJAX
    function saveStep(stepNumber) {
        return new Promise((resolve, reject) => {
            const formElement = document.getElementById('distributorForm');
            const formData = new FormData();

            if (stepNumber === 1) {
                formData.append('territory', $('#territory').val());
                formData.append('region', $('#region_id').val());
                formData.append('zone', $('#zone_id').val());
                formData.append('crop_vertical', $('#crop_vertical').val());
                formData.append('dis_state', $('#dis_state').val());
                formData.append('district', $('#district').val());
            } else {
                const currentStepFields = $(`.step-content[data-step="${stepNumber}"]`).find('input:not(:disabled), select:not(:disabled), textarea:not(:disabled)');
                currentStepFields.each(function() {
                    const field = $(this);
                    if (field.attr('type') === 'file') {
                        if (field[0].files.length > 0) {
                            formData.append(field.attr('name'), field[0].files[0]);
                        } else if (stepNumber === 9 && !removedDocuments[field.attr('name').replace('documents[', '').replace(']', '')]) {
                            // Only append _existing for step 9 if not removed
                            const preview = field.siblings('.file-preview');
                            const fileLink = preview.find('a').attr('href');
                            if (fileLink) {
                                formData.append(`${field.attr('name')}_existing`, fileLink);
                            }
                        }
                    } else if (field.attr('type') === 'checkbox') {
                        formData.append(field.attr('name'), field.is(':checked') ? '1' : '0');
                    } else {
                        formData.append(field.attr('name'), field.val());
                    }
                });
            }

            // Add removed documents only for step 9
            if (stepNumber === 9) {
                for (const type in removedDocuments) {
                    if (removedDocuments[type]) {
                        formData.append(`remove_documents[${type}]`, '1');
                    }
                }
            }

            // Add metadata
            formData.append('current_step', stepNumber);
            formData.append('application_id', $('#application_id').val() || '');

            // Log FormData for debugging
            for (let pair of formData.entries()) {
                console.log(`${pair[0]}: ${pair[1]}`);
            }

            nextBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: `/applications/save-step/${stepNumber}`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        if (stepNumber === 1 && response.application_id) {
                            $('<input>').attr({
                                type: 'hidden',
                                id: 'application_id',
                                name: 'application_id',
                                value: response.application_id
                            }).appendTo(formElement);
                        }
                        // Reset removedDocuments after successful step 9 submission
                        if (stepNumber === 9) {
                            removedDocuments = {};
                        }
                        resolve(response);
                        showSidebarNotification(response.message, 'success');
                    } else {
                        showErrorAlert(response.error || 'Failed to save data');
                        reject(response.error);
                    }
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.error || xhr.statusText || 'Failed to save data. Please try again.';
                    if (typeof errorMsg === 'object') {
                        $('.form-group').find('.invalid-feedback').remove();
                        $('.form-control').removeClass('is-invalid');
                        for (let field in errorMsg) {
                            const input = $(`[name="${field}"]`);
                            if (input.length) {
                                input.closest('.form-group').append(
                                    `<div class="invalid-feedback">${errorMsg[field][0]}</div>`
                                );
                                input.addClass('is-invalid');
                            }
                        }
                    } else {
                        showErrorAlert(errorMsg);
                    }
                    reject(errorMsg);
                },
                complete: function() {
                    nextBtn.prop('disabled', false).html('Next');
                }
            });
        });
    }

    // Helper function to show error alerts consistently
    function showErrorAlert(message) {
        showSidebarNotification(message, 'error');
    }

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
        let isValid = true;
        const errors = [];

        // Reset all invalid markers first
        currentSection.find('.is-invalid').removeClass('is-invalid');
        currentSection.find('.invalid-feedback').remove();

        // Validate only visible required fields in this step
        currentSection.find('[required]:visible').each(function() {
            const $input = $(this);
            let inputValid = true;
            let errorMessage = '';

            if ($input.is(':hidden')) {
                return true;
            }

            if ($input.is(':checkbox') && !$input.is(':checked')) {
                inputValid = false;
                errorMessage = 'This checkbox must be checked';
            } else if ($input.is('select') && !$input.val()) {
                inputValid = false;
                errorMessage = 'Please select an option';
            } else if ($input.is('input[type="text"], input[type="email"], input[type="number"], textarea') && !$.trim($input.val())) {
                inputValid = false;
                errorMessage = 'This field is required';
            } else if ($input.is('input[type="number"]') && isNaN($input.val())) {
                inputValid = false;
                errorMessage = 'Please enter a valid number';
            } else if ($input.is(':radio') && !$(`input[name="${$input.attr('name')}"]:checked`).length) {
                inputValid = false;
                errorMessage = 'Please select Yes or No';
            } else if ($input.is('input[type="file"]')) {
                const file = $input[0]?.files?.[0];
                const preview = $input.siblings('.file-preview');
                const hasExistingFile = preview.find('a').length > 0;
                const type = $input.attr('name').replace('documents[', '').replace(']', '');
                const isRemoved = removedDocuments[type];

                if (!file && !hasExistingFile && !isRemoved) {
                    inputValid = false;
                    errorMessage = 'Please upload a file';
                } else if (file) {
                    const maxSize = 2 * 1024 * 1024;
                    if (file.size > maxSize) {
                        inputValid = false;
                        errorMessage = 'File size must not exceed 2MB';
                    }
                    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                    if (!allowedTypes.includes(file.type)) {
                        inputValid = false;
                        errorMessage = 'Only PDF, JPG, JPEG, or PNG files are allowed';
                    }
                }
            }

            if (!inputValid) {
                $input.addClass('is-invalid');
                $input.after(`<div class="invalid-feedback text-danger">${errorMessage}</div>`);
                isValid = false;
                const fieldName = $input.attr('placeholder') || 
                                 $input.closest('.form-group').find('label').text().replace('*', '').trim() ||
                                 $input.attr('name');
                errors.push(`${fieldName}: ${errorMessage}`);
            }
        });

        // Validate conditionally required fields for Yes/No questions in step 8
        if (stepNumber === 8) {
            const yesNoQuestions = [
                { radioName: 'is_other_distributor', containerId: '#other_distributor_details_container', fieldId: '#other_distributor_details', label: 'Other Distributor Details' },
                { radioName: 'has_sister_concern', containerId: '#sister_concern_details_container', fieldId: '#sister_concern_details', label: 'Sister Concern Details' },
                { radioName: 'has_question_c', containerId: '#question_c_details_container', fieldId: '#question_c_details', label: 'Similar Crops Distributor Details' },
                { radioName: 'has_question_d', containerId: '#question_d_details_container', fieldId: '#question_d_details', label: 'Agro Inputs Association Details' },
                { radioName: 'has_question_e', containerId: '#question_e_details_container', fieldId: '#question_e_details', label: 'Previous VNR Seeds Distributorship Details' },
                { radioName: 'has_disputed_dues', containerId: '#disputed_dues_details_container', fields: [
                    { id: '#disputed_amount', label: 'Disputed Amount' },
                    { id: '#dispute_nature', label: 'Nature of Dispute' },
                    { id: '#dispute_year', label: 'Year of Dispute' },
                    { id: '#dispute_status', label: 'Present Position' },
                    { id: '#dispute_reason', label: 'Reason for Default' }
                ], label: 'Disputed Dues Details' },
                { radioName: 'has_question_g', containerId: '#question_g_details_container', fieldId: '#question_g_details', label: 'Ceased Agent/Distributor Details' },
                { radioName: 'has_question_h', containerId: '#question_h_details_container', fieldId: '#question_h_details', label: 'Relative Connection Details' },
                { radioName: 'has_question_i', containerId: '#question_i_details_container', fieldId: '#question_i_details', label: 'Other Company Involvement Details' },
                { radioName: 'has_question_j', containerId: '#question_j_details_container', fields: [
                    { id: '#referrer_1', label: 'Referrer I' },
                    { id: '#referrer_2', label: 'Referrer II' },
                    { id: '#referrer_3', label: 'Referrer III' },
                    { id: '#referrer_4', label: 'Referrer IV' }
                ], label: 'Referrer Details' },
                { radioName: 'has_question_k', containerId: '#question_k_details_container', fieldId: '#question_k_details', label: 'Own Brand Marketing Details' },
                { radioName: 'has_question_l', containerId: '#question_l_details_container', fieldId: '#question_l_details', label: 'Agro-Input Industry Employment Details' }
            ];

            yesNoQuestions.forEach(question => {
                const radioValue = $(`input[name="${question.radioName}"]:checked`).val();
                if (radioValue === '1') {
                    if (question.fieldId) {
                        const $field = $(question.fieldId);
                        if (!$.trim($field.val())) {
                            $field.addClass('is-invalid');
                            $field.after(`<div class="invalid-feedback text-danger">This field is required when "Yes" is selected.</div>`);
                            isValid = false;
                            errors.push(`${question.label} is required`);
                        }
                    } else if (question.fields) {
                        question.fields.forEach(field => {
                            const $field = $(field.id);
                            if (!$.trim($field.val()) && question.radioName !== 'has_question_j') {
                                $field.addClass('is-invalid');
                                $field.after(`<div class="invalid-feedback text-danger">This field is required when "Yes" is selected.</div>`);
                                isValid = false;
                                errors.push(`${field.label} is required`);
                            }
                        });
                    }
                }
            });

            // Validate mandatory checkboxes
            if (!$('#declaration_truthful').is(':checked')) {
                $('#declaration_truthful').addClass('is-invalid');
                $('#declaration_truthful').after('<div class="invalid-feedback text-danger">You must affirm the truthfulness of the information.</div>');
                isValid = false;
                errors.push('Declaration of truthfulness is required');
            }
            if (!$('#declaration_update').is(':checked')) {
                $('#declaration_update').addClass('is-invalid');
                $('#declaration_update').after('<div class="invalid-feedback text-danger">You must agree to inform the company of changes.</div>');
                isValid = false;
                errors.push('Declaration of updates is required');
            }
        }

        // Validate optional file inputs for step 9
        if (stepNumber === 9) {
            const optionalFileInputs = [
                { id: '#gst_certificate', label: 'GST Certificate' },
                { id: '#seed_license', label: 'Seed License' },
                { id: '#other_document', label: 'Other Relevant Document' }
            ];

            optionalFileInputs.forEach(input => {
                const $input = $(input.id);
                if ($input.length === 0) {
                    console.error(`Input element with ID ${input.id} not found in the DOM for step ${stepNumber}`);
                    errors.push(`${input.label} input field is missing`);
                    return;
                }
                const file = $input[0]?.files?.[0];
                if (file) {
                    const maxSize = 2 * 1024 * 1024;
                    if (file.size > maxSize) {
                        $input.addClass('is-invalid');
                        $input.closest('.file-upload-wrapper').after(`<div class="invalid-feedback text-danger">File size must not exceed 2MB</div>`);
                        isValid = false;
                        errors.push(`${input.label}: File size must not exceed 2MB`);
                    }
                    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                    if (!allowedTypes.includes(file.type)) {
                        $input.addClass('is-invalid');
                        $input.closest('.file-upload-wrapper').after(`<div class="invalid-feedback text-danger">Only PDF, JPG, JPEG, or PNG files are allowed</div>`);
                        isValid = false;
                        errors.push(`${input.label}: Only PDF, JPG, JPEG, or PNG files are allowed`);
                    }
                }
            });
        }

        if (!isValid) {
            showSidebarNotification('Please complete all required fields:<br>' + errors.join('<br>'), 'error');
            currentSection.find('.is-invalid').first().focus();
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

        if (currentStep >= 7 && (!$('#declaration_truthful').is(':checked') || !$('#declaration_update').is(':checked'))) {
            showSidebarNotification('Please accept all declarations before submitting.', 'error');
            e.preventDefault();
            return false;
        }

        if (currentStep === totalSteps && !$('#confirm_accuracy').is(':checked')) {
            showSidebarNotification('Please confirm the accuracy of your information before submitting.', 'error');
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
            //Swal.fire('Error', 'At least one business plan is required.', 'error');
            showSidebarNotification('At least one business plan is required.', 'error');

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

    // Generate review summary
    $(document).on('click', '.next', function() {
        if (currentStep === totalSteps - 1) {
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



    function showSidebarNotification(message, type = 'success') {
  const notificationId = `notification-${Date.now()}`;
  const notificationHtml = `
    <div id="${notificationId}" class="notification ${type}">
      <div class="message">${message}</div>
      <span class="close-btn">&times;</span>
    </div>
  `;
  
  $('#notification-container').append(notificationHtml);
  $('#notification-sidebar').addClass('active');

  // Auto-dismiss after 5 seconds
  setTimeout(() => {
    $(`#${notificationId}`).fadeOut(300, function() {
      $(this).remove();
      if ($('#notification-container').children().length === 0) {
        $('#notification-sidebar').removeClass('active');
      }
    });
  }, 1000);

  // Allow manual dismissal
  $(`#${notificationId} .close-btn`).on('click', function() {
    $(`#${notificationId}`).fadeOut(300, function() {
      $(this).remove();
      if ($('#notification-container').children().length === 0) {
        $('#notification-sidebar').removeClass('active');
      }
    });
  });
}
});