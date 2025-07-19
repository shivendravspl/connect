// application-form.js

$(document).ready(function() {

    function showSidebarNotification(message, type = 'success') {
        const id = `notification-${Date.now()}`;
        const html = `
            <div id="${id}" class="notification ${type}">
                <div class="message">${message}</div>
                <span class="close-btn">&times;</span>
            </div>
        `;
        notificationContainer.append(html);
        notificationSidebar.addClass('active');
        setTimeout(() => removeNotification(id), 5000);
        $(`#${id} .close-btn`).on('click', () => removeNotification(id));
    }

    function removeNotification(id) {
        $(`#${id}`).fadeOut(300, function() {
            $(this).remove();
            if (notificationContainer.children().length === 0) {
                notificationSidebar.removeClass('active');
            }
        });
    }
    // --- End Notification Functions ---
    const form = $('#distributorForm');
    const stepContents = $('.step-content');
    const steps = $('.step');
    const totalSteps = steps.length;

    // Initialize currentStep from the data attribute on page load.
    // This is the source of truth for the initial state coming from Laravel.
    let currentStep = parseInt($('.stepper-wrapper').data('current-step')) || 1;

    const prevBtn = $('.previous');
    const nextBtn = $('.next');
    const submitBtn = $('.submit');

    const notificationContainer = $('#notification-container');
    const notificationSidebar = $('#notification-sidebar');

    let removedDocuments = {}; // To track removed files if needed for backend processing
    let applicationId = $('#application_id').val() || '';

    // --- Initialization on Load ---
    showStep(currentStep);
    updateButtons();
    updateStepper(currentStep);


    console.log('Initial application_id:', applicationId);
    // --- Event Listener for Stepper Clicks ---
    $(document).on('click', '.step', function() {
        const stepNumber = parseInt($(this).data('step'));
        console.log('Clicked step:', stepNumber, 'Current step:', currentStep);
        // Only attempt navigation if the clicked step is different from the currently active step
        if (stepNumber !== currentStep) {
            if (stepNumber < currentStep || $(this).hasClass('completed')) {
                console.log('Navigating to step:', stepNumber);
                currentStep = stepNumber; // Update the global currentStep variable
                showStep(currentStep);    // Display the content for the new step
                updateButtons();          // Adjust navigation buttons for the new step
                updateStepper(currentStep); // Update the visual stepper UI
                scrollToTop();            // Scroll to the top of the form
            } else {
                // If trying to jump to a future, uncompleted step
                showSidebarNotification('Please complete the current step first', 'error');
            }
        }
    });


    // --- Navigation Handlers for "Next" and "Previous" Buttons ---

    nextBtn.click(function() {
        // Validate fields for the current step before proceeding
        if (validateStep(currentStep)) {
            // Save the current step's data via AJAX
            saveStep(currentStep)
                .then((response) => {
                    currentStep = response.current_step;
                    showStep(currentStep);
                    updateButtons();
                    scrollToTop(); // Scroll to the top for the new step
                })
                .catch(() => {
                    // Error notification is already handled within `saveStep`'s error block.
                    // Re-enable the "Next" button if it was disabled and the save failed.
                    if (nextBtn.is(':disabled')) {
                        nextBtn.prop('disabled', false).html('Next');
                    }
                });
        }
    });

    prevBtn.click(function() {
        currentStep--; // Decrement the global currentStep to go back
        showStep(currentStep); // Display the content for the previous step
        updateButtons();       // Adjust navigation buttons
        updateStepper(currentStep); // Update the visual stepper UI
        scrollToTop();         // Scroll to the top
    });


    // --- Form Step Management Functions ---

    // Hides all step content and shows only the content for the specified step
    function showStep(step) {
        stepContents.hide();
        $(`.step-content[data-step="${step}"]`).show();
    }

    // Toggles the visibility and text of the Previous, Next, and Submit buttons
    function updateButtons() {
        prevBtn.toggle(currentStep > 1); // Show "Previous" if not on the first step
        nextBtn.toggle(currentStep < totalSteps); // Show "Next" if not on the last step
        submitBtn.toggle(currentStep === totalSteps); // Show "Submit" only on the last step
    }

    // --- CRITICAL: Updates the visual state of the stepper steps (active, completed, clickable) ---
    // This function takes the currently active step number and updates the UI accordingly.
      function updateStepper(activeStepNumber) {
    // First reset all dynamic classes
    steps.removeClass('active clickable');
    
    steps.each(function() {
        const stepNumber = parseInt($(this).data('step'));
        const $step = $(this);
        
        // Preserve the server-rendered completed state
        const isCompleted = $step.hasClass('completed') || stepNumber < activeStepNumber;
        
        if (isCompleted) {
            $step.addClass('completed');
            $step.addClass('clickable');
        }
        
        if (stepNumber === activeStepNumber) {
            $step.addClass('active');
            $step.addClass('clickable');
        }
        
        console.log(`Step ${stepNumber}: 
            active: ${stepNumber === activeStepNumber}, 
            completed: ${isCompleted}, 
            clickable: ${isCompleted || stepNumber === activeStepNumber}`);
    });
}

    // Scrolls the window to bring the stepper into view
    function scrollToTop() {
        $('html, body').animate({ scrollTop: $('.stepper-wrapper').offset().top - 20 }, 300);
    }

    // --- Frontend Validation Function ---
function validateStep(step) {
    const section = $(`.step-content[data-step="${step}"]`);
    let isValid = true;
    const errors = [];
    // Clear any previously displayed validation errors
    section.find('.is-invalid').removeClass('is-invalid');
    section.find('.invalid-feedback').remove();

    // Validate all visible, required fields within the current step's section, excluding auth_person fields in Step 2
    section.find('[required]:visible').each(function() {
        const $el = $(this);
        const name = $el.attr('name');
        // Skip auth_person_letter[] and auth_person_aadhar[] in Step 2, as they are handled separately
        if (step === 2 && (name === 'auth_person_letter[]' || name === 'auth_person_aadhar[]')) {
            return true; // Continue to next element
        }

        let valid = true;
        let msg = '';

        if ($el.is(':checkbox') && !$el.is(':checked')) {
            valid = false;
            msg = $el.attr('id')?.includes('confirm') ? 'Please confirm the entered details' : 'Required';
        } else if ($el.is('select') && !$el.val()) {
            valid = false;
            msg = 'Please select an option';
        } else if ($el.is('input[type="file"]')) {
            const file = $el[0].files[0];
            const preview = $el.siblings('.file-preview, .small.text-muted');
            const hasFile = preview.find('a').length > 0;
            const type = name;
            const removed = removedDocuments[type];

             if (['bank_file', 'seed_license_file', 'pan_file'].includes(name) && !file && !hasFile && !removed) {
                valid = false;
                 msg = `A ${name.replace('_file', '').replace('_', ' ')} document is required`;
            } else if (name === 'gst_file' && $('#gst_applicable').val() === 'yes' && !file && !hasFile && !removed) {
                valid = false;
                msg = 'A GST document is required when GST is applicable';
            } else if (file && file.size > 2 * 1024 * 1024) {
                valid = false;
                msg = 'Max file size: 2MB';
            } else if (file && !['application/pdf', 'image/jpeg', 'image/png'].includes(file.type)) {
                valid = false;
                msg = 'Allowed: PDF, JPG, PNG';
            }
        } else if (!$el.val() || ($el.is(':radio') && !$(`input[name="${name}"]:checked`).length)) {
            valid = false;
            msg = 'Required';
        }

        if (!valid) {
            $el.addClass('is-invalid');
            $el.closest('.form-group, .file-upload-wrapper').append(`<div class="invalid-feedback text-danger">${msg}</div>`);
            isValid = false;
            errors.push(`${name || 'Field'}: ${msg}`);
        }
    });

    // Special validation for dynamic tables, like authorized persons (Step 2)
    if (step === 2) {
        const authTable = section.find('#authorized_persons_table');
        const hasAuthorizedPersons = $('#has_authorized_persons').length && $('#has_authorized_persons').val() === 'yes';
        let atLeastOneRowFilled = false;

        if (authTable.length) {
            authTable.find('.authorized-person-entry').each(function(index) {
                const $row = $(this);
                let rowIsFilled = false;

                // Check if any non-file field in the row has data
                $row.find('input:not([type="file"]), textarea').each(function() {
                    if ($(this).val().trim() !== '') {
                        rowIsFilled = true;
                        atLeastOneRowFilled = true;
                    }
                });

                // Get existing file references using correct indexed selector
                const $existingLetter = $row.find(`input[name="existing_auth_person_letter[${index}]"]`);
                const $existingAadhar = $row.find(`input[name="existing_auth_person_aadhar[${index}]"]`);
                const hasExistingLetter = $existingLetter.length > 0 && $existingLetter.val().trim() !== '';
                const hasExistingAadhar = $existingAadhar.length > 0 && $existingAadhar.val().trim() !== '';
                const isLetterRemoved = removedDocuments[`auth_person_letter[${index}]`] || false;
                const isAadharRemoved = removedDocuments[`auth_person_aadhar[${index}]`] || false;

                if (hasExistingLetter || hasExistingAadhar) {
                    rowIsFilled = true;
                    atLeastOneRowFilled = true;
                }

                // Only validate if authorized persons are required and the row has data
                if (hasAuthorizedPersons && rowIsFilled) {
                    // Validate Letter of Authorization file
                    const $letterInput = $row.find('input[name="auth_person_letter[]"]');
                    const hasNewLetter = $letterInput[0].files.length > 0;
                    if (!hasNewLetter && !hasExistingLetter && !isLetterRemoved) {
                        $letterInput.addClass('is-invalid');
                        $letterInput.after('<div class="invalid-feedback">Please upload a Letter of Authorization or keep the existing one</div>');
                        isValid = false;
                        errors.push(`auth_person_letter[${index}]: File required`);
                    } else if (hasNewLetter) {
                        const file = $letterInput[0].files[0];
                        const validTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                        if (file.size > 2 * 1024 * 1024) {
                            $letterInput.addClass('is-invalid');
                            $letterInput.after('<div class="invalid-feedback">Max file size: 2MB</div>');
                            isValid = false;
                            errors.push(`auth_person_letter[${index}]: Max file size: 2MB`);
                        } else if (!validTypes.includes(file.type)) {
                            $letterInput.addClass('is-invalid');
                            $letterInput.after('<div class="invalid-feedback">Allowed formats: PDF, DOC, DOCX</div>');
                            isValid = false;
                            errors.push(`auth_person_letter[${index}]: Allowed formats: PDF, DOC, DOCX`);
                        }
                    }

                    // Validate Aadhar document file
                    const $aadharInput = $row.find('input[name="auth_person_aadhar[]"]');
                    const hasNewAadhar = $aadharInput[0].files.length > 0;
                    if (!hasNewAadhar && !hasExistingAadhar && !isAadharRemoved) {
                        $aadharInput.addClass('is-invalid');
                        $aadharInput.after('<div class="invalid-feedback">Please upload an Aadhar document or keep the existing one</div>');
                        isValid = false;
                        errors.push(`auth_person_aadhar[${index}]: File required`);
                    } else if (hasNewAadhar) {
                        const file = $aadharInput[0].files[0];
                        const validTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                        if (file.size > 2 * 1024 * 1024) {
                            $aadharInput.addClass('is-invalid');
                            $aadharInput.after('<div class="invalid-feedback">Max file size: 2MB</div>');
                            isValid = false;
                            errors.push(`auth_person_aadhar[${index}]: Max file size: 2MB`);
                        } else if (!validTypes.includes(file.type)) {
                            $aadharInput.addClass('is-invalid');
                            $aadharInput.after('<div class="invalid-feedback">Allowed formats: PDF, JPG, PNG</div>');
                            isValid = false;
                            errors.push(`auth_person_aadhar[${index}]: Allowed formats: PDF, JPG, PNG`);
                        }
                    }

                    // Validate other required fields in the row (name, contact, address, relation)
                    $row.find('[required]:not([type="file"])').each(function() {
                        const $input = $(this);
                        if (!$input.val().trim()) {
                            $input.addClass('is-invalid');
                            $input.after('<div class="invalid-feedback">This field is required</div>');
                            isValid = false;
                            errors.push(`${$input.attr('name')}[${index}]: Required`);
                        }
                    });
                }
            });

            // Ensure at least one authorized person is filled if required
            if (hasAuthorizedPersons && !atLeastOneRowFilled) {
                showSidebarNotification('At least one authorized person must be added with all required fields', 'error');
                isValid = false;
                errors.push('auth_person_name: At least one authorized person required');
            } else if (atLeastOneRowFilled && !isValid) {
                showSidebarNotification('Please complete all required fields in authorized persons table', 'error');
            }
        } else if (hasAuthorizedPersons) {
            // If table is missing but authorized persons are required
            showSidebarNotification('Authorized persons table is missing', 'error');
            isValid = false;
            errors.push('auth_person_name: Table missing');
        }
    }

    // Special validation for declarations (Step 7)
    if (step === 7 && (!$('#declaration_truthful').is(':checked') || !$('#declaration_update').is(':checked'))) {
        showSidebarNotification('Please accept all declarations to proceed', 'error');
        isValid = false;
    }

    // Special validation for the review step (Step 8)
    if (step === 8 && !$('#confirm_accuracy').is(':checked')) {
        showSidebarNotification('Please confirm the accuracy of your application before submitting', 'error');
        isValid = false;
    }

    // If any validation errors occurred, show a consolidated notification and focus the first invalid field
    if (!isValid && errors.length > 0) {
        showSidebarNotification('Complete all required fields:<br>' + errors.join('<br>'), 'error');
        section.find('.is-invalid').first().focus();
    }

    return isValid;
}

    // --- Data Saving Function (AJAX Calls to Backend) ---
  // --- Data Saving Function (AJAX Calls to Backend) ---
function saveStep(step, isFinalSubmission = false) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        // Find all form fields (inputs, selects, textareas) within the current step's content div
        const currentFields = $(`.step-content[data-step="${step}"]`).find('input:not(:disabled), select:not(:disabled), textarea:not(:disabled)');

        // Special handling for Step 1 fields
        if (step === 1) {
            formData.append('territory', $('#territory').val() || '');
            formData.append('region', $('#region_id').val() || '');
            formData.append('zone', $('#zone_id').val() || '');
            formData.append('business_unit', $('#bu_id').val() || '');
            formData.append('crop_vertical', $('#crop_vertical').val() || '');
            formData.append('state', $('#dis_state').val() || '');
            formData.append('district', $('#district').val() || '');
        } else if (step === 2) {
            const fileFields = ['bank_file', 'seed_license_file', 'pan_file', 'gst_file'];
            // Special handling for Step 2 (Authorized Persons)
            currentFields.each(function() {
                const field = $(this);
                const name = field.attr('name');

                if (field.attr('type') === 'file' && name.endsWith('[]') && field[0].files.length > 0) {
                    // Skip file fields here; handle them in row-specific loop below
                    return;
                } else if (field.attr('type') === 'file' && fileFields.includes(name) && field[0].files.length > 0) {
                    formData.append(name, field[0].files[0]);
                } else if (name === 'area_covered[]') {
                    (field.val() || []).forEach((val, i) => formData.append(`area_covered[${i}]`, val));
                } else if (field.attr('type') === 'checkbox') {
                    formData.append(name, field.is(':checked') ? '1' : '0');
                } else {
                    formData.append(name, field.val() || '');
                }
            });

            // Handle authorized persons table rows explicitly
            const authTable = $(`.step-content[data-step="2"]`).find('#authorized_persons_table');
            if (authTable.length) {
                authTable.find('.authorized-person-entry').each(function(index) {
                    const $row = $(this);
                    const letterInput = $row.find('input[name="auth_person_letter[]"]')[0];
                    const aadharInput = $row.find('input[name="auth_person_aadhar[]"]')[0];

                    if (letterInput && letterInput.files.length > 0) {
                        formData.append(`auth_person_letter[${index}]`, letterInput.files[0]);
                    }
                    if (aadharInput && aadharInput.files.length > 0) {
                        formData.append(`auth_person_aadhar[${index}]`, aadharInput.files[0]);
                    }
                });
            }

            // Append removed files
            for (const [key, value] of Object.entries(removedDocuments)) {
                if (fileFields.includes(key) || key.startsWith('auth_person_letter[') || key.startsWith('auth_person_aadhar[')) {
                    formData.append(`removed_${key}`, value ? '1' : '0');
                }
            }
        } else {
            // Generic handling for other steps (Step 3 through 8)
            currentFields.each(function() {
                const field = $(this);
                const name = field.attr('name');

                if (field.attr('type') === 'file' && field[0].files.length > 0) {
                    formData.append(name, field[0].files[0]);
                } else if (name === 'area_covered[]') {
                    (field.val() || []).forEach((val, i) => formData.append(`area_covered[${i}]`, val));
                } else if (field.attr('type') === 'checkbox') {
                    formData.append(name, field.is(':checked') ? '1' : '0');
                } else {
                    formData.append(name, field.val() || '');
                }
            });
        }

        // Always append the current step number and application ID
        formData.append('current_step', step);
        formData.append('application_id', applicationId || $('#application_id').val() || '');

        // Log FormData for debugging
        console.log('FormData for Step', step);
        for (let pair of formData.entries()) {
            console.log(`${pair[0]}: ${pair[1]}`);
        }

        const button = isFinalSubmission ? submitBtn : nextBtn;
        button.prop('disabled', true).html(
            `<i class="fas fa-spinner fa-spin"></i> ${isFinalSubmission ? 'Submitting...' : 'Saving...'}`
        );

        // AJAX call to the Laravel backend
        $.ajax({
            url: `/applications/save-step/${step}`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success(response) {
                if (response.success) {
                    if (response.application_id) {
                        applicationId = response.application_id;
                        if (!$('#application_id').length) {
                            form.append(`<input type="hidden" id="application_id" name="application_id" value="${response.application_id}">`);
                        } else {
                            $('#application_id').val(response.application_id);
                        }
                        console.log('Updated application_id:', applicationId);
                    }

                    showSidebarNotification(response.message, 'success');
                    updateStepper(response.current_step);

                    if (step === 8 && response.redirect) {
                        window.location.href = response.redirect;
                        return;
                    }
                    resolve(response);
                } else {
                    showSidebarNotification(response.error || 'Failed to save data', 'error');
                    reject();
                }
            },
            error(xhr) {
                const response = xhr.responseJSON || {};
                let errorMessage = 'Failed to save data.';

                if (response.error) {
                    if (typeof response.error === 'object') {
                        errorMessage = Object.values(response.error).flat().join(' ');
                        $('.form-group').find('.invalid-feedback').remove();
                        $('.form-control, .form-select').removeClass('is-invalid');
                        for (const key in response.error) {
                            let selector = `[name="${key}"]`;
                            if (key.includes('.')) {
                                const parts = key.split('.');
                                selector = `[name="${parts[0]}[${parts[1]}]"]`;
                            }
                            const input = $(selector);
                            if (input.length) {
                                input.addClass('is-invalid');
                                input.closest('.form-group, .file-upload-wrapper').append(
                                    `<div class="invalid-feedback text-danger">${response.error[key][0]}</div>`
                                );
                            }
                        }
                    } else {
                        errorMessage = response.error;
                    }
                } else {
                    errorMessage = xhr.statusText || 'An unexpected error occurred.';
                }

                showSidebarNotification(errorMessage, 'error');
                reject(response);
            },
            complete() {
                button.prop('disabled', false).html(isFinalSubmission ? 'Submit Application' : 'Next');
            }
        });
    });
}

   



    // --- Form Submission Handler ---
    // Intercepts the form's natural submit event to use our AJAX saveStep function.
    form.on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        // The submit event only fires on the last step, so we know it's the final submission.
        if (validateStep(currentStep)) {
            saveStep(currentStep, true) // Call saveStep, flagging it as the final submission.
                .then(() => {
                    // On successful final submission, the `saveStep` function handles the redirect.
                    // No further action is needed here in the success case.
                })
                .catch(() => {
                    // Error notifications are handled within `saveStep`.
                    // If the submission fails, re-enable the submit button.
                    submitBtn.prop('disabled', false).html('Submit Application');
                });
        }
    });

});