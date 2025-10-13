$(document).ready(function() {
    // --- Notification Functions (Unchanged) ---
    function showSidebarNotification(message, type = 'success') {
        console.log('showSidebarNotification called with:', { message, type });
        
        // Use globals - ensure they're defined
        const container = window.notificationContainer || $('#notification-container');
        const sidebar = window.notificationSidebar || $('#notification-sidebar');
        
        if (container.length === 0 || sidebar.length === 0) {
            console.error('Notification elements not found! Check HTML and selectors.');
            alert('Notification system error: Sidebar not initialized.'); // Fallback alert
            return;
        }
        
        const id = `notification-${Date.now()}`;
        const html = `
            <div id="${id}" class="notification ${type}">
                <div class="message">${message}</div>
                <span class="close-btn">&times;</span>
            </div>
        `;
        
        console.log('Appending HTML to container:', container);
        container.append(html);
        
        console.log('Notification count after append:', container.children().length);
        
        sidebar.addClass('active');
        sidebar.css({
            'display': 'block !important',
            'right': '0 !important',
            'z-index': '99999 !important'
        });
        console.log('Forced CSS overrides applied');        
        setTimeout(() => removeNotification(id), 2000);
        $(`#${id} .close-btn`).on('click', () => removeNotification(id));
        
        console.log('Notification should now be visible!');
    }

    function removeNotification(id) {
        const $notif = $(`#${id}`);
        if ($notif.length === 0) {
            console.warn('Notification not found for removal:', id);
            return;
        }
        $notif.fadeOut(300, function() {
            $(this).remove();
            const container = window.notificationContainer || $('#notification-container');
            if (container.children().length === 0) {
                const sidebar = window.notificationSidebar || $('#notification-sidebar');
                sidebar.removeClass('active');
            }
        });
    }

    // --- Initialization ---
    const form = $('#distributorForm');
    const stepContents = $('.step-content');
    const steps = $('.step');
    const totalSteps = steps.length;

    let currentStep = parseInt($('.stepper-wrapper').data('current-step')) || 1;
    let completedSteps = {};

    // Initialize completedSteps based on completedStepsData
    try {
        for (let step = 1; step <= totalSteps; step++) {
            completedSteps[step] = completedStepsData[step] || false;
        }
    } catch (e) {
        console.error('Error initializing completedSteps:', e);
        // Fallback: assume all steps incomplete except those before currentStep
        for (let step = 1; step < currentStep; step++) {
            completedSteps[step] = true;
        }
    }
    console.log('Initial completedSteps:', completedSteps);
    // Define globals early for notifications
    window.notificationContainer = $('#notification-container');
    window.notificationSidebar = $('#notification-sidebar');
    
    // Verify (remove after testing)
    console.log('Initialized container:', window.notificationContainer.length > 0 ? 'Found!' : 'Missing!');
    console.log('Initialized sidebar:', window.notificationSidebar.length > 0 ? 'Found!' : 'Missing!');
    const prevBtn = $('.previous');
    const nextBtn = $('.next');
    const submitBtn = $('.submit');
   
    let removedDocuments = {};
    let applicationId = $('#application_id').val() || '';

    showStep(currentStep);
    updateButtons();
    updateStepper(currentStep);

    console.log('Initial application_id:', applicationId);

 $(document).on('click', '.step.clickable', function() {
    const stepNumber = parseInt($(this).data('step'));
    console.log('Step clicked:', stepNumber);

    const isEditMode = !!applicationId; // true if editing

    // 🔒 Block navigation only if NEW mode (not edit)
    if (!isEditMode) {
        for (let i = 1; i < stepNumber; i++) {
            if (!completedSteps[i]) {
                console.log(`Step ${i} is incomplete. Blocking jump to step ${stepNumber}.`);
                showSidebarNotification(
                    `Please complete step ${i} before moving to step ${stepNumber}`,
                    'error'
                );
                return;
            }
        }
    }

    // ✅ If navigating within steps
    if (stepNumber < totalSteps) {
        currentStep = stepNumber;
        showStep(currentStep);
        updateButtons();
        updateStepper(currentStep);
        scrollToTop();
    } 
    // ✅ If navigating to final Review & Submit step
    else {
        console.log('Attempting to access Review & Submit, validating all steps...');
        if (validateAllSteps()) {
            currentStep = stepNumber;
            showStep(currentStep);
            updateButtons();
            updateStepper(currentStep);
            scrollToTop();
            if (applicationId) {
                refreshReviewPreview();
            }
        } else {
            const firstIncompleteStep = getFirstIncompleteStep();
            console.log('Redirecting to incomplete step:', firstIncompleteStep);
            currentStep = firstIncompleteStep;
            showStep(currentStep);
            updateButtons();
            updateStepper(currentStep);
            scrollToTop();
            showSidebarNotification(
                `Please complete step ${firstIncompleteStep} before accessing Review & Submit`,
                'error'
            );
        }
    }
});



    nextBtn.click(function() {
        console.log('Next button clicked, validating step:', currentStep);
        if (validateStep(currentStep)) {
            saveStep(currentStep)
                .then((response) => {
                    completedSteps[currentStep] = true;
                    console.log('Step saved, updated completedSteps:', completedSteps);
                    currentStep++;
                    showStep(currentStep);
                    updateButtons();
                    updateStepper(currentStep);
                    scrollToTop();
                    if (currentStep === totalSteps && applicationId) {
                        refreshReviewPreview();
                    }
                })
                .catch((error) => {
                    console.log('Save step failed:', error);
                    nextBtn.prop('disabled', false).html(
                        `<span class="d-none d-sm-inline">Next</span>
                         <i class="fas fa-arrow-right d-sm-none"></i>`
                    );
                });
        } else {
            console.log('Validation failed for step:', currentStep);
        }
    });

    prevBtn.click(function() {
        console.log('Previous button clicked, moving to step:', currentStep - 1);
        currentStep--;
        showStep(currentStep);
        updateButtons();
        updateStepper(currentStep);
        scrollToTop();
    });

    form.on('submit', function(e) {
        e.preventDefault();
        console.log('Form submission initiated for step:', currentStep);
        console.log('validateAllSteps:', validateAllSteps());
        console.log('validateStep(8):', currentStep === totalSteps ? validateStep(totalSteps) : 'N/A');
        console.log('confirm_accuracy checked:', $('#confirm_accuracy').is(':checked'));

        if (currentStep === totalSteps) {
            if (validateAllSteps()) {
                if (validateStep(totalSteps)) {
                    console.log('Submitting step 8...');
                    saveStep(currentStep, true)
                        .then((response) => {
                            console.log('Submission successful:', response);
                            showSidebarNotification('Application submitted successfully!', 'success');
                        })
                        .catch((error) => {
                            console.log('Submission failed:', error);
                            submitBtn.prop('disabled', false).html(
                                `<span class="d-none d-sm-inline">Submit</span>
                                 <i class="fas fa-check d-sm-none"></i>`
                            );
                        });
                } else {
                    console.log('Step 8 validation failed');
                    showSidebarNotification('Please complete all required fields in Step 8', 'error');
                }
            } else {
                const firstIncompleteStep = getFirstIncompleteStep();
                console.log('Redirecting to incomplete step:', firstIncompleteStep);
                currentStep = firstIncompleteStep;
                showStep(currentStep);
                updateButtons();
                updateStepper(currentStep);
                scrollToTop();
                showSidebarNotification(`Please complete step ${firstIncompleteStep} before submitting`, 'error');
            }
        }
    });

    function refreshReviewPreview() {
        const iframe = $('.step-content[data-step="8"] iframe');
        if (iframe.length && applicationId) {
            iframe.attr('src', `/application/${applicationId}/preview`);
            console.log('Refreshing iframe with application_id:', applicationId);
        } else {
            console.warn('Iframe or application_id missing for preview refresh');
        }
    }

    function showStep(step) {
        stepContents.hide();
        $(`.step-content[data-step="${step}"]`).show();
        console.log('Showing step:', step);
    }

    function updateButtons() {
        prevBtn.toggle(currentStep > 1);
        nextBtn.toggle(currentStep < totalSteps);
        submitBtn.toggle(currentStep === totalSteps);
        console.log('Updated buttons: prev=', currentStep > 1, 'next=', currentStep < totalSteps, 'submit=', currentStep === totalSteps);
    }

    function updateStepper(activeStepNumber) {
        steps.removeClass('active completed clickable');
        steps.each(function() {
            const stepNumber = parseInt($(this).data('step'));
            const $step = $(this);
            if (completedSteps[stepNumber]) {
                $step.addClass('completed');
            }
            if (stepNumber === activeStepNumber) {
                $step.addClass('active');
            }
            if (stepNumber < totalSteps || (stepNumber === totalSteps && validateAllSteps())) {
                $step.addClass('clickable');
            }
        });
        console.log('Updated stepper, activeStep:', activeStepNumber, 'completedSteps:', completedSteps);
    }

    function scrollToTop() {
        $('html, body').animate({ scrollTop: $('.stepper-wrapper').offset().top - 20 }, 300);
    }

    function getFirstIncompleteStep() {
        for (let step = 1; step < totalSteps; step++) {
            if (!completedSteps[step]) {
                console.log('First incomplete step:', step);
                return step;
            }
        }
        console.log('No incomplete steps found, defaulting to step 1');
        return 1;
    }

    function validateAllSteps() {
        for (let step = 1; step < totalSteps; step++) {
            if (!completedSteps[step]) {
                console.log('validateAllSteps failed at step:', step);
                return false;
            }
        }
        console.log('validateAllSteps passed');
        return true;
    }

    function validateStep(step) {
        const section = $(`.step-content[data-step="${step}"]`);
        let isValid = true;
        const errors = [];
        section.find('.is-invalid').removeClass('is-invalid');
        section.find('.invalid-feedback').remove();

        section.find('[required]:visible').each(function() {
            const $el = $(this);
            const name = $el.attr('name');

            if (step === 2 && (name === 'auth_person_letter[]' || name === 'auth_person_aadhar[]')) {
                return true;
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

        if (step === 2) {
            const authTable = section.find('#authorized_persons_table');
            const hasAuthorizedPersons = $('#has_authorized_persons').length && $('#has_authorized_persons').val() === 'yes';
            let atLeastOneRowFilled = false;

            if (authTable.length) {
                authTable.find('.authorized-person-entry').each(function(index) {
                    const $row = $(this);
                    let rowIsFilled = false;

                    $row.find('input:not([type="file"]), textarea').each(function() {
                        if ($(this).val().trim() !== '') {
                            rowIsFilled = true;
                            atLeastOneRowFilled = true;
                        }
                    });

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

                    if (hasAuthorizedPersons && rowIsFilled) {
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

                if (hasAuthorizedPersons && !atLeastOneRowFilled) {
                    showSidebarNotification('At least one authorized person must be added with all required fields', 'error');
                    isValid = false;
                    errors.push('auth_person_name: At least one authorized person required');
                } else if (atLeastOneRowFilled && !isValid) {
                    showSidebarNotification('Please complete all required fields in authorized persons table', 'error');
                }
            } else if (hasAuthorizedPersons) {
                showSidebarNotification('Authorized persons table is missing', 'error');
                isValid = false;
                errors.push('auth_person_name: Table missing');
            }
        }

        if (step === 7 && (!$('#declaration_truthful').is(':checked'))) {
            console.log('Step 7 validation failed: declarations not checked');
            showSidebarNotification('Please accept all declarations to proceed', 'error');
            isValid = false;
            errors.push('declarations: Please accept all declarations');
        }

        if (step === 8 && !$('#confirm_accuracy').is(':checked')) {
            console.log('Step 8 validation failed: confirm_accuracy not checked');
            showSidebarNotification('Please confirm the accuracy of your application before submitting', 'error');
            $('#confirm_accuracy').addClass('is-invalid');
            $('#confirm_accuracy').closest('.form-group').append('<div class="invalid-feedback text-danger">Please confirm the accuracy before submitting.</div>');
            isValid = false;
            errors.push('confirm_accuracy: Please confirm the accuracy');
        }

        if (!isValid && errors.length > 0) {
            console.log('Validation errors for step', step, ':', errors);
            showSidebarNotification('Complete all required fields:<br>' + errors.join('<br>'), 'error');
            section.find('.is-invalid').first().focus();
        }

        console.log('validateStep(', step, ') result:', isValid);
        return isValid;
    }

    function saveStep(step, isFinalSubmission = false) {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            const currentFields = $(`.step-content[data-step="${step}"]`).find('input:not(:disabled), select:not(:disabled), textarea:not(:disabled)');

            if (step === 1) {
                formData.append('territory', $('#territory').val() || '');
                formData.append('region', $('#region_id').val() || '');
                formData.append('zone', $('#zone_id').val() || '');
                formData.append('business_unit', $('#bu_id').val() || '');
                formData.append('crop_vertical', $('#crop_vertical').val() || '');
                formData.append('district', $('#district').val() || '');
                formData.append('state', $('#state').val() || '');
            } else if (step === 2) {
                const fileFields = ['bank_file', 'seed_license_file', 'pan_file', 'gst_file'];
                currentFields.each(function() {
                    const field = $(this);
                    const name = field.attr('name');
                    if (field.attr('type') === 'file' && name.endsWith('[]') && field[0].files.length > 0) {
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

                for (const [key, value] of Object.entries(removedDocuments)) {
                    if (fileFields.includes(key) || key.startsWith('auth_person_letter[') || key.startsWith('auth_person_aadhar[')) {
                        formData.append(`removed_${key}`, value ? '1' : '0');
                    }
                }
            } else if (step === 4) {
            // Special handling for Step 4 - Business Plans
            const businessPlanRows = $('.business-plan-row');
            
            businessPlanRows.each(function(index) {
                const $row = $(this);
                
                // Get all the values from the row
                const crop = $row.find('select[name^="business_plans"][name$="[crop]"]').val() || '';
                const currentMt = $row.find('input[name^="business_plans"][name$="[current_financial_year_mt]"]').val() || '';
                const currentAmount = $row.find('input[name^="business_plans"][name$="[current_financial_year_amount]"]').val() || '';
                const nextMt = $row.find('input[name^="business_plans"][name$="[next_financial_year_mt]"]').val() || '';
                const nextAmount = $row.find('input[name^="business_plans"][name$="[next_financial_year_amount]"]').val() || '';
                
                // Append each field with proper array indexing
                formData.append(`business_plans[${index}][crop]`, crop);
                formData.append(`business_plans[${index}][current_financial_year_mt]`, currentMt);
                formData.append(`business_plans[${index}][current_financial_year_amount]`, currentAmount);
                formData.append(`business_plans[${index}][next_financial_year_mt]`, nextMt);
                formData.append(`business_plans[${index}][next_financial_year_amount]`, nextAmount);
            });
            
            console.log('Step 4 - Business Plans data prepared:', {
                rowCount: businessPlanRows.length,
                data: Array.from(formData.entries()).filter(([key]) => key.includes('business_plans'))
            });
        } else {
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

            formData.append('current_step', step);
            formData.append('application_id', applicationId || $('#application_id').val() || '');

            console.log('FormData for Step', step);
            for (let pair of formData.entries()) {
                console.log(`${pair[0]}: ${pair[1]}`);
            }

            const button = isFinalSubmission ? submitBtn : nextBtn;
            button.prop('disabled', true).html(
                `<i class="fas fa-spinner fa-spin"></i> ${isFinalSubmission ? 'Submitting...' : 'Saving...'}`
            );

            $.ajax({
                url: `/applications/save-step/${step}`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success(response) {
                    if (response.success) {
                         if (!window.location.pathname.includes('/edit')) {
                                const newUrl = `/applications/${response.application_id}/edit`;
                                // Update URL without reload
                                window.history.pushState({}, '', newUrl);
                                // Also update hidden application_id field
                                if (!$('#application_id').length) {
                                    form.append(`<input type="hidden" id="application_id" name="application_id" value="${response.application_id}">`);
                                } else {
                                    $('#application_id').val(response.application_id);
                                }
                                console.log('Switched to edit mode, new URL:', newUrl);
                            }

                        if (response.application_id) {
                            applicationId = response.application_id;
                            if (!$('#application_id').length) {
                                form.append(`<input type="hidden" id="application_id" name="application_id" value="${response.application_id}">`);
                            } else {
                                $('#application_id').val(response.application_id);
                            }
                            console.log('Updated application_id:', applicationId);
                        }

                        completedSteps[step] = true;
                        console.log('Step saved, updated completedSteps:', completedSteps);
                        showSidebarNotification(response.message, 'success');
                        updateStepper(response.current_step);

                        if (step === 8 && response.redirect) {
                            $.post('/clear-application-id', {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            });
                            console.log('Redirecting to:', response.redirect);
                            window.location.href = response.redirect;
                            return;
                        }
                        resolve(response);
                    } else {
                        console.log('Save failed for step', step, response);
                        showSidebarNotification(response.error || 'Failed to save data', 'error');
                        if (response.missing_steps) {
                            console.log('Missing steps:', response.missing_steps);
                            showSidebarNotification('Missing data in steps: ' + response.missing_steps.join(', '), 'error');
                        }
                        reject(response);
                    }
                },
                error(xhr) {
                    const response = xhr.responseJSON || {};
                    let errorMessage = 'Failed to save data.';
                    if (response.error && typeof response.error === 'object') {
                        if (step === 5) {
                            // Special handling for step 5
                            // Clear errors
                            $('.step-content[data-step="5"]').find('.form-control, .form-select').removeClass('is-invalid');
                            $('.step-content[data-step="5"]').find('.invalid-feedback').remove();
                            $('#annual-turnover-error').hide().empty();
                            $('.step-content[data-step="5"] tbody tr').removeClass('table-danger');

                            let hasTurnoverError = false;

                            // Handle turnover amount error
                            if (response.error['annual_turnover.amount']) {
                                const message = Array.isArray(response.error['annual_turnover.amount']) ? response.error['annual_turnover.amount'][0] : response.error['annual_turnover.amount'];
                                $('#annual-turnover-error').text(message).show();
                                $('.step-content[data-step="5"] input[name^="annual_turnover[amount]"]').addClass('is-invalid');
                                $('.step-content[data-step="5"] input[name^="annual_turnover[amount]"]').closest('tr').addClass('table-danger');
                                hasTurnoverError = true;
                            }

                            // Handle turnover year error if any
                            if (response.error['annual_turnover.year']) {
                                const messages = Array.isArray(response.error['annual_turnover.year']) ? response.error['annual_turnover.year'] : [response.error['annual_turnover.year']];
                                let message = messages.join(', ');
                                let currentError = $('#annual-turnover-error').text();
                                if (currentError) {
                                    currentError += ' ' + message;
                                } else {
                                    currentError = message;
                                }
                                $('#annual-turnover-error').text(currentError).show();
                                $('.step-content[data-step="5"] .table-responsive table tbody tr').addClass('table-danger');
                                hasTurnoverError = true;
                            }

                            // Handle other fields in step 5
                            const step5Fields = ['net_worth', 'shop_ownership', 'shop_uom', 'shop_area', 'godown_uom', 'godown_area', 'godown_ownership', 'years_in_business'];
                            $.each(response.error, function(field, messages) {
                                if (step5Fields.includes(field)) {
                                    const message = Array.isArray(messages) ? messages[0] : messages;
                                    let $input = $('.step-content[data-step="5"] [name="' + field + '"]');
                                    if ($input.length) {
                                        $input.addClass('is-invalid');
                                        $input.after('<div class="invalid-feedback d-block">' + message + '</div>');
                                    }
                                }
                            });

                            // Scroll to errors
                            if (hasTurnoverError) {
                                $('html, body').animate({
                                    scrollTop: $('.step-content[data-step="5"] .table-responsive').offset().top - 100
                                }, 500);
                            } else {
                                $('html, body').animate({
                                    scrollTop: $('.step-content[data-step="5"]').offset().top - 100
                                }, 500);
                            }

                            errorMessage = Object.values(response.error).flat().join(' ');
                        } else {
                            // General handling for other steps
                            errorMessage = Object.values(response.error).flat().join(' ');
                            $('.form-group').find('.invalid-feedback').remove();
                            $('.form-control, .form-select, .input-group').removeClass('is-invalid');
                            for (const key in response.error) {
                                let selector = `[name="${key}"]`;
                                   if (key.includes('.')) {
                                    const parts = key.split('.');
                                    if (parts[0] === 'business_plans' && parts.length === 3) {
                                        const index = parts[1];
                                        const field = parts[2];
                                        selector = `[name="business_plans[${index}][${field}]"]`;
                                        const pairedFields = {
                                            'current_financial_year_mt': 'current_financial_year_amount',
                                            'current_financial_year_amount': 'current_financial_year_mt',
                                            'next_financial_year_mt': 'next_financial_year_amount',
                                            'next_financial_year_amount': 'next_financial_year_mt'
                                        };
                                        const pairedField = pairedFields[field];
                                        if (pairedField) {
                                            const pairedSelector = `[name="business_plans[${index}][${pairedField}]"]`;
                                            $(pairedSelector).addClass('is-invalid'); // Highlight both for "either/or"
                                        }
                                    } else if (parts.length === 2) {
                                        // Simple array: e.g., "array.0" -> array[0]
                                        selector = `[name="${parts[0]}[${parts[1]}]"]`;
                                    } else {
                                        // Deeper nesting: join all but last with [ ], last as is
                                        const nestedName = parts.slice(0, -1).reduce((acc, part, i) => acc + (i === 0 ? part : `[${part}]`), '') + `[${parts[parts.length - 1]}]`;
                                        selector = `[name="${nestedName}"]`;
                                    }
                                }
                                const input = $(selector);
                                if (input.length) {
                                // Add is-invalid to input and visible parent (e.g., input-group or button)
                                input.addClass('is-invalid');
                                input.closest('.input-group').addClass('is-invalid');  // Highlights the whole upload group
                                
                                // Safer container for feedback: col-md-*, form-group, or fallback to mb-3 section
                                let container = input.closest('.form-group, .file-upload-wrapper, .col-md-*, .col-*, .mb-3');
                                if (container.length === 0) {
                                    container = input.parent().parent();  // Fallback to col-md-4
                                }
                                if (container.length) {
                                    container.append(
                                        `<div class="invalid-feedback text-danger d-block">${response.error[key][0]}</div>`  // d-block forces show
                                    );
                                }
                            }
                        }
                    }
                } else if (response.error) {
                    errorMessage = response.error;
                } else {
                    errorMessage = xhr.statusText || 'An unexpected error occurred.';
                }
                console.log('AJAX error for step', step, response);
                showSidebarNotification(errorMessage, 'error');
                if (response.missing_steps) {
                    console.log('Missing steps:', response.missing_steps);
                    showSidebarNotification('Missing data in steps: ' + response.missing_steps.join(', '), 'error');
                }
                reject(response);
            },
                complete() {
                    button.prop('disabled', false).html(
                        isFinalSubmission ?
                            `<span class="d-none d-sm-inline">Submit</span><i class="fas fa-check d-sm-none"></i>` :
                            `<span class="d-none d-sm-inline">Next</span><i class="fas fa-arrow-right d-sm-none"></i>`
                    );
                }
            });
        });
    }

    // Debug submit button click
    $('#submit-application').on('click', function() {
        console.log('Submit button clicked');
    });
});