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
                    if (response.application_id) {
                        applicationId = response.application_id; // Update global applicationId
                        if (!$('#application_id').length) {
                            form.append(`<input type="hidden" id="application_id" name="application_id" value="${response.application_id}">`);
                        } else {
                            $('#application_id').val(response.application_id);
                        }
                        console.log('Updated application_id:', applicationId);
                    }
                    currentStep = response.current_step;
                    showStep(currentStep);
                    updateButtons();
                    scrollToTop(); // Scroll to the top for the new step
                })
                .catch((error) => {
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


    // --- Data Saving Function (AJAX Calls to Backend) ---
    // This function handles sending form data for a specific step to the Laravel backend.
    function saveStep(step, isFinalSubmission = false) {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            // Find all form fields (inputs, selects, textareas) within the current step's content div
            const currentFields = $(`.step-content[data-step="${step}"]`).find('input:not(:disabled), select:not(:disabled), textarea:not(:disabled)');

            // Special handling for Step 1 fields due to specific ID/Name mapping needs
            if (step === 1) {
                formData.append('territory', $('#territory').val());
                formData.append('region', $('#region_id').val());      
                formData.append('zone', $('#zone_id').val());          
                formData.append('business_unit', $('#bu_id').val());    
                formData.append('crop_vertical', $('#crop_vertical').val());
                formData.append('state', $('#dis_state').val());       
                formData.append('district', $('#district').val());     
            } else {
                // Generic handling for all other steps (Step 2 through 9)
                currentFields.each(function() {
                    const field = $(this);
                    const name = field.attr('name');

                    if (field.attr('type') === 'file' && field[0].files.length > 0) {
                        formData.append(name, field[0].files[0]); // Append file
                    } else if (name === 'area_covered[]') {
                        // Special handling for multi-selects like area_covered[]
                        (field.val() || []).forEach((val, i) => formData.append(`area_covered[${i}]`, val));
                    } else if (field.attr('type') === 'checkbox') {
                        formData.append(name, field.is(':checked') ? '1' : '0'); // Checkbox value as 1 or 0
                    } else {
                        formData.append(name, field.val()); // Standard input value
                    }
                });
            }

            // Always append the current step number and application ID
            formData.append('current_step', step);
            formData.append('application_id', applicationId || $('#application_id').val() || '');
            const button = isFinalSubmission ? submitBtn : nextBtn;
            button.prop('disabled', true).html(
                `<i class="fas fa-spinner fa-spin"></i> ${isFinalSubmission ? 'Submitting...' : 'Saving...'}`
            );

            // AJAX call to the Laravel backend
            $.ajax({
                url: `/applications/save-step/${step}`, // Route to your saveStep method
                method: 'POST',
                data: formData,
                processData: false,     // Required for FormData
                contentType: false,     // Required for FormData
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, // CSRF token for Laravel security
                success(response) {
                    if (response.success) {
                        // If it's the first step and a new application_id is returned, add it to the form
                        if (step === 1 && response.application_id) {
                            form.append(`<input type="hidden" id="application_id" name="application_id" value="${response.application_id}">`);
                        }
                        showSidebarNotification(response.message, 'success'); // Show success notification
                        // This ensures the UI reflects the backend's state immediately after save.
                        updateStepper(response.current_step);
                        // If it's the final submission (step 9) and a redirect URL is provided, navigate
                        if (step === 8 && response.redirect) {
                            window.location.href = response.redirect;
                            return; // Exit early as we are redirecting
                        }
                        resolve(response); // Resolve the Promise with the server response
                    } else {
                        // Handle server-side validation errors or other failures
                        showSidebarNotification(response.error || 'Failed to save data', 'error');
                        reject(); // Reject the Promise
                    }
                },
                error(xhr) {
                const response = xhr.responseJSON || {};
                let errorMessage = 'Failed to save data.';

                // Handle validation errors or other server errors
                if (response.error) {
                    if (typeof response.error === 'object') {
                        // Flatten validation errors for notification
                        errorMessage = Object.values(response.error).flat().join(' ');
                        // Display field-specific errors
                        $('.form-group').find('.invalid-feedback').remove();
                        $('.form-control, .form-select').removeClass('is-invalid');
                        for (const key in response.error) {
                            // Handle nested fields (e.g., business_plans[0][crop], annual_turnover[2022-23])
                            let selector = `[name="${key}"]`;
                            if (key.includes('.')) {
                                const parts = key.split('.');
                                selector = `[name^="${parts[0]}["]`;
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


    // --- Frontend Validation Function ---
    // This function validates the required fields for the *current* step.
    function validateStep(step) {
        const section = $(`.step-content[data-step="${step}"]`);
        let isValid = true;
        const errors = []; // Collect error messages for the sidebar notification

        // Clear any previously displayed validation errors
        section.find('.is-invalid').removeClass('is-invalid');
        section.find('.invalid-feedback').remove();

        // Validate all visible, required fields within the current step's section
        section.find('[required]:visible').each(function() {
            const $el = $(this);
            let valid = true;
            let msg = '';

            if ($el.is(':checkbox') && !$el.is(':checked')) {
                // Handle required checkboxes (e.g., confirmations)
                valid = false;
                msg = $el.attr('id')?.includes('confirm') ? 'Please confirm the entered details' : 'Required';
            } else if ($el.is('select') && !$el.val()) {
                // Handle required select (dropdown) fields
                valid = false;
                msg = 'Please select an option';
            } else if ($el.is('input[type="file"]')) {
                // Handle required file input fields
                const file = $el[0].files[0];
                const preview = $el.siblings('.file-preview'); // Assuming a file preview element exists
                const hasFile = preview.find('a').length > 0; // Check if an existing file is linked

                // Check for 'removedDocuments' if you have logic to mark files as removed but still required
                const type = $el.attr('name').replace('documents[', '').replace(']', '');
                const removed = removedDocuments[type];

                if (!file && !hasFile && !removed) {
                    valid = false;
                    msg = 'Upload required';
                } else if (file && file.size > 2 * 1024 * 1024) {
                    valid = false;
                    msg = 'Max file size: 2MB';
                } else if (file && !['application/pdf', 'image/jpeg', 'image/png'].includes(file.type)) {
                    valid = false;
                    msg = 'Allowed: PDF, JPG, PNG';
                }
            } else if (!$el.val() || ($el.is(':radio') && !$(`input[name="${$el.attr('name')}"]:checked`).length)) {
                // Handle general required text/number inputs or radio buttons
                valid = false;
                msg = 'Required';
            }

            if (!valid) {
                $el.addClass('is-invalid'); // Add Bootstrap's invalid class
                // Append validation feedback message
                $el.closest('.form-group, .file-upload-wrapper').append(`<div class="invalid-feedback text-danger">${msg}</div>`);
                isValid = false; // Mark step as invalid
                errors.push(`${$el.attr('name') || 'Field'}: ${msg}`); // Add to collected errors
            }
        });

        // Special validation for dynamic tables, like authorized persons
        const authTable = section.find('#authorized_persons_table');
        if (authTable.length) {
            let atLeastOneRowFilled = false;

            authTable.find('.authorized-person-entry').each(function() {
                const $row = $(this);
                let rowIsFilled = false;

                // Check if any non-file field in the row has data
                $row.find('input:not([type="file"]), textarea').each(function() {
                    if ($(this).val().trim() !== '') {
                        rowIsFilled = true;
                        atLeastOneRowFilled = true;
                    }
                });

                // Get existing file references for conditional validation
                const $existingLetter = $row.find('input[name="existing_auth_person_letter[]"]');
                const $existingAadhar = $row.find('input[name="existing_auth_person_aadhar[]"]');
                const hasExistingLetter = $existingLetter.length > 0 && $existingLetter.val() !== '';
                const hasExistingAadhar = $existingAadhar.length > 0 && $existingAadhar.val() !== '';

                if (hasExistingLetter || hasExistingAadhar) {
                    rowIsFilled = true;
                    atLeastOneRowFilled = true;
                }

                // If this row has some data, validate all its required fields
                if (rowIsFilled) {
                    // Validate Letter of Authorization file
                    const $letterInput = $row.find('input[name="auth_person_letter[]"]');
                    const hasNewLetter = $letterInput[0].files.length > 0;
                    if (!hasNewLetter && !hasExistingLetter) {
                        $letterInput.addClass('is-invalid');
                        $letterInput.after('<div class="invalid-feedback">Either upload a new file or keep the existing one</div>');
                        isValid = false;
                        errors.push('auth_person_letter[]: File required');
                    } else if (hasNewLetter) {
                        const file = $letterInput[0].files[0];
                        const validTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                        if (file.size > 2 * 1024 * 1024) {
                            $letterInput.addClass('is-invalid'); $letterInput.after('<div class="invalid-feedback">Max file size: 2MB</div>'); isValid = false;
                        } else if (!validTypes.includes(file.type)) {
                            $letterInput.addClass('is-invalid'); $letterInput.after('<div class="invalid-feedback">Allowed formats: PDF, DOC, DOCX</div>'); isValid = false;
                        }
                    }

                    // Validate Aadhar document file
                    const $aadharInput = $row.find('input[name="auth_person_aadhar[]"]');
                    const hasNewAadhar = $aadharInput[0].files.length > 0;
                    if (!hasNewAadhar && !hasExistingAadhar) {
                        $aadharInput.addClass('is-invalid');
                        $aadharInput.after('<div class="invalid-feedback">Either upload a new file or keep the existing one</div>');
                        isValid = false;
                        errors.push('auth_person_aadhar[]: File required');
                    } else if (hasNewAadhar) {
                        const file = $aadharInput[0].files[0];
                        const validTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                        if (file.size > 2 * 1024 * 1024) {
                            $aadharInput.addClass('is-invalid'); $aadharInput.after('<div class="invalid-feedback">Max file size: 2MB</div>'); isValid = false;
                        } else if (!validTypes.includes(file.type)) {
                            $aadharInput.addClass('is-invalid'); $aadharInput.after('<div class="invalid-feedback">Allowed formats: PDF, JPG, PNG</div>'); isValid = false;
                        }
                    }

                    // Validate other required fields in the row (e.g., name, contact)
                    $row.find('[required]:not([type="file"])').each(function() {
                        const $input = $(this);
                        if (!$input.val().trim()) {
                            $input.addClass('is-invalid');
                            $input.after('<div class="invalid-feedback">This field is required</div>');
                            isValid = false;
                            errors.push(`${$input.attr('name')}: Required`);
                        }
                    });
                }
            });

            if (atLeastOneRowFilled && !isValid) {
                // If some data was entered in the table but not all required fields are valid
                showSidebarNotification('Please complete all required fields in authorized persons table', 'error');
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
        if (!isValid) {
            // Only show consolidated error if `errors` array has content from general validation
            if (errors.length > 0) {
                 showSidebarNotification('Complete all required fields:<br>' + errors.join('<br>'), 'error');
            }
            section.find('.is-invalid').first().focus();
        }

        return isValid; // Return true if all validations pass, false otherwise
    }


    // --- Form Submission Handler ---
    // Intercepts the form's natural submit event to use our AJAX saveStep function.
    form.on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        if (validateStep(currentStep)) {
            const isFinal = currentStep === totalSteps; // Check if this is the final step

            saveStep(currentStep, isFinal) // Call saveStep (which handles AJAX and UI updates)
                .then((response) => {
                    if (!isFinal) {
                        // For non-final steps, currentStep has already been updated
                        // by `saveStep`'s success handler (`currentStep = response.current_step;`).
                        // `showStep(currentStep)` and `updateButtons()` also correctly use this updated `currentStep`.
                        // `updateStepper` is called directly by `saveStep`.
                        // So, no additional UI updates are strictly necessary here.
                    }
                })
                .catch((error) => {
                    // Error notifications are handled within the `saveStep`'s error callback.
                    // If it's a final submission and it failed, re-enable the submit button.
                    if (isFinal) {
                        submitBtn.prop('disabled', false).html('Submit Application');
                    }
                });
        }
    });

});