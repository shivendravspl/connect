$(document).ready(function() {
    const form = $('#distributorForm');
    const stepContents = $('.step-content');
    const steps = $('.step');
    const totalSteps = steps.length;
    let currentStep = 1;
    const prevBtn = $('.previous');
    const nextBtn = $('.next');
    const submitBtn = $('.submit');
    
    console.log('jQuery loaded:', typeof $ !== 'undefined');
    console.log('Tesseract loaded:', typeof Tesseract !== 'undefined');
    console.log('Next button:', $('.next').length, 'Visible:', $('.next').is(':visible'));
    console.log('Steps found:', $('.step').length, 'Step elements:', $('.step').toArray());
    console.log('Initial currentStep:', currentStep, 'totalSteps:', totalSteps);
    // Initialize form
    showStep(currentStep);
    updateButtons();

    // Initialize removedDocuments globally
    let removedDocuments = {};

    // Sidebar notification function
    function showSidebarNotification(message, type = 'success') {
        const notificationId = `notification-${Date.now()}`;
        const notificationHtml = `
            <div id="${notificationId}" class="notification ${type}">
                <div class="message">${message}</div>
                <span class="close-btn">×</span>
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
        }, 5000);

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

    // File upload preview and OCR processing with auto-fill
    $(document).on('change', 'input[type="file"]', function() {
        const input = $(this);
        const preview = input.siblings('.file-preview');
        const type = input.attr('name').replace('documents[', '').replace(']', '');
        const file = input[0].files[0];
        const fileName = file?.name || 'No file chosen';

        preview.html(fileName);
        delete removedDocuments[type];
        if (input.hasClass('required-field')) {
            input.prop('required', true);
        }

        // Clear related inputs and indicator
        if (type === 'pan_card') {
            $('#pan_number_input').val('');
            $('#pan_validation_indicator').html('');
            $('#pan_confirm').prop('checked', false).prop('required', true);
        } else if (type === 'bank_proof') {
            $('#bank_account_input').val('');
            $('#bank_validation_indicator').html('');
            $('#bank_confirm').prop('checked', false).prop('required', true);
        }

        // Perform OCR for PAN Card and Bank Proof
        if (type === 'pan_card' || type === 'bank_proof') {
            if (file) {
                const maxSize = 2 * 1024 * 1024;
                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    showSidebarNotification('Only PDF, JPG, JPEG, or PNG files are allowed.', 'error');
                    input.val('');
                    preview.html('No file chosen');
                    resetRelatedInputs(type);
                    return;
                }
                if (file.size > maxSize) {
                    showSidebarNotification('File size must not exceed 2MB.', 'error');
                    input.val('');
                    preview.html('No file chosen');
                    resetRelatedInputs(type);
                    return;
                }

                showSidebarNotification(`Processing ${type === 'pan_card' ? 'PAN Card' : 'Bank Proof'}...`, 'info');
                Tesseract.recognize(
                    file,
                    'eng',
                    { logger: (m) => console.log(m) }
                ).then(({ data: { text } }) => {
                    let extractedValue = '';
                    if (type === 'pan_card') {
                        const panRegex = /[A-Z]{5}[0-9]{4}[A-Z]{1}/;
                        const match = text.match(panRegex);
                        extractedValue = match ? match[0] : '';
                    } else if (type === 'bank_proof') {
                        const accountRegex = /\b\d{9,18}\b/;
                        const match = text.match(accountRegex);
                        extractedValue = match ? match[0] : '';
                    }

                    const inputFieldId = type === 'pan_card' ? '#pan_number_input' : '#bank_account_input';
                    const indicatorId = type === 'pan_card' ? '#pan_validation_indicator' : '#bank_validation_indicator';
                    const confirmId = type === 'pan_card' ? '#pan_confirm' : '#bank_confirm';

                    if (extractedValue) {
                        $(inputFieldId).val(extractedValue);
                        $(indicatorId).html('✅').css('color', 'green');
                        showSidebarNotification(`${type === 'pan_card' ? 'PAN Number' : 'Bank Account Number'} extracted and filled. Please verify and check the confirmation box.`, 'success');
                        $(confirmId).prop('required', true);
                    } else {
                        $(inputFieldId).val('');
                        $(indicatorId).html('');
                        showSidebarNotification(`Could not extract ${type === 'pan_card' ? 'PAN Number' : 'Bank Account Number'} from the uploaded file. Please enter manually.`, 'error');
                        $(confirmId).prop('checked', false).prop('required', false);
                    }
                }).catch(err => {
                    console.error('OCR Error:', err);
                    showSidebarNotification(`Failed to process ${type === 'pan_card' ? 'PAN Card' : 'Bank Proof'}. Please enter the number manually.`, 'error');
                    $(type === 'pan_card' ? '#pan_number_input' : '#bank_account_input').val('');
                    $(type === 'pan_card' ? '#pan_validation_indicator' : '#bank_validation_indicator').html('');
                    $(type === 'pan_card' ? '#pan_confirm' : '#bank_confirm').prop('checked', false).prop('required', false);
                });
            } else {
                resetRelatedInputs(type);
            }
        }
    });

    // Helper function to reset related inputs
    function resetRelatedInputs(type) {
        if (type === 'pan_card') {
            $('#pan_number_input').val('');
            $('#pan_validation_indicator').html('');
            $('#pan_confirm').prop('checked', false).prop('required', true);
        } else if (type === 'bank_proof') {
            $('#bank_account_input').val('');
            $('#bank_validation_indicator').html('');
            $('#bank_confirm').prop('checked', false).prop('required', true);
        }
    }

    // Validate text input and checkbox on change
    $(document).on('input', '#pan_number_input, #bank_account_input', function() {
        const input = $(this);
        const type = input.attr('id') === 'pan_number_input' ? 'pan_card' : 'bank_proof';
        const fileInput = $(`#${type}`);
        const file = fileInput[0]?.files[0];
        const indicatorId = type === 'pan_card' ? '#pan_validation_indicator' : '#bank_validation_indicator';
        const confirmId = type === 'pan_card' ? '#pan_confirm' : '#bank_confirm';

        if (file) {
            Tesseract.recognize(
                file,
                'eng',
                { logger: (m) => console.log(m) }
            ).then(({ data: { text } }) => {
                let extractedValue = '';
                if (type === 'pan_card') {
                    const panRegex = /[A-Z]{5}[0-9]{4}[A-Z]{1}/;
                    const match = text.match(panRegex);
                    extractedValue = match ? match[0] : '';
                } else if (type === 'bank_proof') {
                    const accountRegex = /\b\d{9,18}\b/;
                    const match = text.match(accountRegex);
                    extractedValue = match ? match[0] : '';
                }

                const userInput = input.val().trim().toUpperCase();
                if (extractedValue && userInput) {
                    if (userInput === extractedValue) {
                        $(indicatorId).html('✅').css('color', 'green');
                        showSidebarNotification(`${type === 'pan_card' ? 'PAN Number' : 'Bank Account Number'} matches the uploaded document. Please check the confirmation box.`, 'success');
                        $(confirmId).prop('required', true);
                    } else {
                        $(indicatorId).html('❌').css('color', 'red');
                        showSidebarNotification(`${type === 'pan_card' ? 'PAN Number' : 'Bank Account Number'} does not match the uploaded document.`, 'error');
                        $(confirmId).prop('checked', false).prop('required', false);
                    }
                } else if (userInput) {
                    $(indicatorId).html('');
                    showSidebarNotification(`Could not extract ${type === 'pan_card' ? 'PAN Number' : 'Bank Account Number'} from the uploaded file.`, 'error');
                    $(confirmId).prop('checked', false).prop('required', false);
                }
            }).catch(err => {
                console.error('OCR Error:', err);
                showSidebarNotification(`Failed to process ${type === 'pan_card' ? 'PAN Card' : 'Bank Proof'}.`, 'error');
                $(indicatorId).html('');
                $(confirmId).prop('checked', false).prop('required', false);
            });
        } else {
            $(indicatorId).html('');
            $(confirmId).prop('checked', false).prop('required', true);
        }
    });

    // Checkbox change handler
    $(document).on('change', '#pan_confirm, #bank_confirm', function() {
        const checkbox = $(this);
        const type = checkbox.attr('id') === 'pan_confirm' ? 'pan_card' : 'bank_proof';
        const inputFieldId = type === 'pan_card' ? '#pan_number_input' : '#bank_account_input';
        const indicatorId = type === 'pan_card' ? '#pan_validation_indicator' : '#bank_validation_indicator';
        const userInput = $(inputFieldId).val().trim();

        if (checkbox.is(':checked') && $(indicatorId).html() !== '✅') {
            checkbox.prop('checked', false); // Fixed typo: 'checkbox' to 'checked'
            showSidebarNotification(`Please ensure the ${type === 'pan_card' ? 'PAN Number' : 'Bank Account Number'} matches before confirming.`, 'error');
        }
    });

    // Handle document removal
    $(document).on('click', '.remove-document', function() {
        const $button = $(this);
        const type = $button.data('type');
        const $fileInput = $button.closest('.file-upload-wrapper').find('input[type="file"]');
        const $preview = $button.siblings('.file-preview');
        const originalPreviewHtml = $preview.html();

        Swal.fire({
            title: 'Are you sure?',
            text: 'This will remove the uploaded document.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove it!',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/applications/remove-document/${$('#application_id').val()}`,
                    type: 'POST',
                    data: {
                        type: type,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            removedDocuments[type] = 1;
                            showSidebarNotification('Document removed.', 'success');
                            if ($fileInput.hasClass('required-field')) {
                                $fileInput.prop('required', true);
                            }
                            resetRelatedInputs(type);
                        } else {
                            $preview.html(originalPreviewHtml);
                            $fileInput.prop('required', false);
                            showSidebarNotification(response.error || 'Failed to remove document', 'error');
                        }
                    },
                    error: function(xhr) {
                        $preview.html(originalPreviewHtml);
                        $fileInput.prop('required', false);
                        showSidebarNotification(xhr.responseJSON?.error || 'Failed to remove document', 'error');
                    }
                });
            } else {
                $preview.html(originalPreviewHtml);
                $fileInput.prop('required', false);
            }
        });
    });

    // Clear file selection
    $(document).on('click', '.clear-file', function() {
        const wrapper = $(this).closest('.file-upload-wrapper');
        const $fileInput = wrapper.find('input[type="file"]');
        const type = $fileInput.attr('name').replace('documents[', '').replace(']', '');
        wrapper.find('.file-preview').html('No file chosen');
        $fileInput.val('');
        if ($fileInput.hasClass('required-field')) {
            $fileInput.prop('required', true);
        }
        delete removedDocuments[type];
        resetRelatedInputs(type);
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
                showSidebarNotification('Failed to save current step. Please try again.', 'error');
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
                formData.append('business_unit', $('#bu_id').val());
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
                        }
                    } else if (field.attr('id') === 'area_covered') {
                        const values = (field.val() || []).filter(val => val !== null && val !== undefined);
                        values.forEach((value, index) => {
                            formData.append(`area_covered[${index}]`, value);
                        });
                    } else if (field.attr('type') === 'checkbox') {
                        formData.append(field.attr('name'), field.is(':checked') ? '1' : '0');
                    } else {
                        formData.append(field.attr('name'), field.val());
                    }
                });
            }

         

            formData.append('current_step', stepNumber);
            formData.append('application_id', $('#application_id').val() || '');

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
                        
                        showSidebarNotification(response.message, 'success');
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            resolve(response);
                        }
                    } else {
                        showSidebarNotification(response.error || 'Failed to save data', 'error');
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
                        showSidebarNotification(errorMsg, 'error');
                    }
                    reject(errorMsg);
                },
                complete: function() {
                    nextBtn.prop('disabled', false).html('Next');
                }
            });
        });
    }

    // Show the current step and hide others
    function showStep(stepNumber) {
        stepContents.hide();
        $(`.step-content[data-step="${stepNumber}"]`).show();
    }

    // Update button visibility
    function updateButtons() {
        console.log('Updating buttons', { currentStep, totalSteps, nextBtnVisible: nextBtn.is(':visible') });
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

        currentSection.find('.is-invalid').removeClass('is-invalid');
        currentSection.find('.invalid-feedback').remove();

        currentSection.find('[required]:visible').each(function() {
            const $input = $(this);
            let inputValid = true;
            let errorMessage = '';

            if ($input.is(':hidden')) {
                return true;
            }

            if ($input.is(':checkbox') && !$input.is(':checked')) {
                inputValid = false;
                errorMessage = $input.attr('id') === 'pan_confirm' || $input.attr('id') === 'bank_confirm'
                    ? 'Please confirm the entered details'
                    : 'This checkbox must be checked';
            } else if ($input.is('select') && !$input.val()) {
                inputValid = false;
                errorMessage = 'Please select an option';
            } else if ($input.attr('id') === 'area_covered') {
                const selectedValues = $input.val() || [];
                if (selectedValues.length === 0) {
                    inputValid = false;
                    errorMessage = 'Please select at least one area to be covered';
                }
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
            } else if ($input.attr('id') === 'pan_number_input') {
                const value = $input.val().trim();
                const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
                if (!value) {
                    inputValid = false;
                    errorMessage = 'PAN Number is required';
                } else if (!panRegex.test(value)) {
                    inputValid = false;
                    errorMessage = 'Invalid PAN Number format (e.g., ABCDE1234F)';
                } else if ($('#pan_validation_indicator').html() === '✅' && !$('#pan_confirm').is(':checked')) {
                    inputValid = false;
                    errorMessage = 'Please check the confirmation box for PAN Number';
                }
            } else if ($input.attr('id') === 'bank_account_input') {
                const value = $input.val().trim();
                if (!value) {
                    inputValid = false;
                    errorMessage = 'Bank Account Number is required';
                } else if (!/^\d{9,18}$/.test(value)) {
                    inputValid = false;
                    errorMessage = 'Bank Account Number must be 9-18 digits';
                } else if ($('#bank_validation_indicator').html() === '✅' && !$('#bank_confirm').is(':checked')) {
                    inputValid = false;
                    errorMessage = 'Please check the confirmation box for Bank Account Number';
                }
            }

            if (!inputValid) {
                $input.addClass('is-invalid');
                $input.closest('.form-group, .file-upload-wrapper').append(`<div class="invalid-feedback text-danger">${errorMessage}</div>`);
                isValid = false;
                const fieldName = $input.attr('placeholder') || 
                                 $input.closest('.form-group').find('label').text().replace('*', '').trim() ||
                                 $input.attr('name');
                errors.push(`${fieldName}: ${errorMessage}`);
            }
        });

       
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
                    <p><strong>PAN:</strong> <span id="review-pan">${$('#pan_number_input').val()}</span></p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <h6>Bank Details</h6>
                    <p><strong>Bank Name:</strong> <span id="review-bank-name">${$('#bank_name').val()}</span></p>
                    <p><strong>Account Number:</strong> <span id="review-account-number">${$('#bank_account_input').val()}</span></p>
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
});