@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>Vendor Onboarding</h3>
                </div>
                <div class="card-body">
                    <!-- Stepper -->
                    <div class="steps">
                        @php
                        // Define completed steps differently
                        $completedSteps = isset($vendor) && $vendor->id ? range(1, max($vendor->current_step, 3)) : [];
                        @endphp

                        @foreach([1 => 'Company Information', 2 => 'Legal Information', 3 => 'Banking Information'] as $step => $title)
                        <div class="step 
        @if($current_step >= $step) active @endif 
        @if(in_array($step, $completedSteps)) clickable @endif
        @if($step <= $vendor->current_step ?? 0) completed @endif"
                            data-step="{{ $step }}">
                            <div class="step-number">{{ $step }}</div>
                            <div class="step-title">{{ $title }}</div>
                        </div>
                        @endforeach
                    </div>

                    <form id="vendorForm" enctype="multipart/form-data" novalidate>
                        @csrf
                        <input type="hidden" name="current_step" value="{{ $current_step }}">
                        <input type="hidden" name="vendor_id" value="{{ $vendor->id ?? '' }}">

                        <!-- Step 1: Company Information -->
                        <div class="step-content @if($current_step != 1) d-none @endif" id="step1">
                            @include('vendors.steps.company_info', [
                            'vendor' => $vendor ?? null,
                            'states' => $states,
                            'departments' => $departments,
                            'employees' => $employees ?? []
                            ])
                        </div>

                        <!-- Step 2: Legal Information -->
                        <div class="step-content @if($current_step != 2) d-none @endif" id="step2">
                            @include('vendors.steps.legal_info', ['vendor' => $vendor ?? null])
                        </div>

                        <!-- Step 3: Banking Information -->
                        <div class="step-content @if($current_step != 3) d-none @endif" id="step3">
                            @include('vendors.steps.banking_info', ['vendor' => $vendor ?? null])
                        </div>

                        <div class="form-footer mt-4">
                            @if($current_step > 1)
                            <button type="button" class="btn btn-secondary prev-step">Previous</button>
                            @endif

                            @if($current_step < 3)
                                <button type="submit" class="btn btn-primary next-step">Next</button>
                                @else
                                <button type="submit" class="btn btn-success submit-form">Submit</button>
                                @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .steps {
        display: flex;
        justify-content: space-between;
        margin-bottom: 30px;
    }

    .step {
        flex: 1;
        text-align: center;
        position: relative;
    }

    .step:not(:last-child):after {
        content: '';
        position: absolute;
        top: 15px;
        left: 50%;
        right: -50%;
        height: 2px;
        background: #dee2e6;
        z-index: 0;
    }

    .step.active:not(:last-child):after {
        background: #007bff;
    }

    .step-number {
        width: 30px;
        height: 30px;
        line-height: 30px;
        border-radius: 50%;
        background: #dee2e6;
        display: inline-block;
        margin-bottom: 5px;
        color: #6c757d;
    }

    .step.active .step-number {
        background: #007bff;
        color: white;
    }

    .step-title {
        font-size: 14px;
        color: #6c757d;
    }

    .step.active .step-title {
        color: #007bff;
        font-weight: bold;
    }

    .step.clickable {
        cursor: pointer;
    }

    .step.clickable:hover .step-number {
        background: #0056b3;
        transition: background 0.3s ease;
    }

    .step.clickable:hover .step-title {
        color: #0056b3;
        transition: color 0.3s ease;
    }

    .step.completed .step-number {
        background: #28a745 !important;
    }

    .step.completed.clickable:hover .step-number {
        background: #218838 !important;
    }

    .radio-group.is-invalid {
        border: 1px solid #dc3545;
        padding: 10px;
        border-radius: 4px;
    }

    /* Add this to highlight just the invalid radio button */
    .radio-group.is-invalid .form-check-input.is-invalid {
        border-color: #dc3545;
    }

    /* Style for invalid select2 elements */
    .select2-container--default .select2-selection--single.is-invalid {
        border: 1px solid #dc3545 !important;
    }

    /* Style for invalid regular select elements */
    select.is-invalid {
        border: 1px solid #dc3545;
    }

    /* Ensure error messages appear below select2 containers */
    .select2-container~.invalid-feedback {
        display: block;
        margin-top: -5px;
        margin-bottom: 10px;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {

        // Clear errors when user interacts with fields
        $('input[type="text"], input[type="email"], textarea').on('input', function() {
            clearFieldError($(this)); // Pass the jQuery-wrapped element
        });

        // For regular select elements
        $('select:not(.select2-hidden-accessible)').on('change', function() {
            clearFieldError($(this));
        });

        $(document).on('change', '.select2', function() {
            const elementId = $(this).attr('id');
            if (!elementId) return; // Exit if no ID exists
            const selectId = elementId.replace('-container', '');
            if (selectId) {
                clearFieldError($('#' + selectId));
            }
        });
        $('input[type="radio"], input[type="checkbox"]').on('change', function() {
            clearFieldError($(this));
        });

        function clearFieldError(fieldElement) { // Renamed parameter to be more specific
            // Check if the element exists
            if (!fieldElement || !fieldElement.length) return;

            // For Select2 elements
            if (fieldElement.hasClass('select2-hidden-accessible')) {
                const select2Container = fieldElement.next('.select2-container');
                if (select2Container.length) {
                    select2Container.find('.select2-selection').removeClass('is-invalid');
                    select2Container.next('.invalid-feedback').remove();
                }
            }
            // For radio buttons
            else if (fieldElement.is(':radio')) {
                fieldElement.closest('.radio-group').removeClass('is-invalid');
                fieldElement.closest('.form-group').find('.invalid-feedback').remove();
            }
            // For regular fields
            else {
                fieldElement.removeClass('is-invalid');
                fieldElement.closest('.form-group').find('.invalid-feedback').remove();
            }
        }

        // Department change handler - modified to preserve existing value
        $('#vnr_contact_department_id').change(function() {
            const departmentId = $(this).val();
            const employeeSelect = $('#vnr_contact_person_id');
            const currentEmployeeId = employeeSelect.val(); // Get current value before emptying

            employeeSelect.empty().append('<option value="">Loading...</option>');

            if (departmentId) {
                $.ajax({
                    url: '/vendors/employees/by-department/' + departmentId,
                    type: 'GET',
                    success: function(data) {
                        employeeSelect.empty();

                        // Add default option
                        employeeSelect.append(
                            $('<option>', {
                                value: '',
                                text: 'Select Employee'
                            })
                        );

                        // Add options from AJAX
                        $.each(data, function(key, employee) {
                            const option = new Option(
                                employee.text,
                                employee.id,
                                false,
                                employee.id == currentEmployeeId // Preselect if matches
                            );
                            employeeSelect.append(option);
                        });

                        // Reinitialize Select2
                        employeeSelect.select2({
                            placeholder: 'Select Employee',
                            allowClear: true
                        });

                        // If we had a selected value and it exists in new options, keep it selected
                        if (currentEmployeeId && employeeSelect.find('option[value="' + currentEmployeeId + '"]').length) {
                            employeeSelect.val(currentEmployeeId).trigger('change');
                        }
                    },
                    error: function() {
                        employeeSelect.empty().append(
                            $('<option>', {
                                value: '',
                                text: 'Error loading employees'
                            })
                        );
                    }
                });
            } else {
                employeeSelect.empty().append(
                    $('<option>', {
                        value: '',
                        text: 'Select Department first'
                    })
                );
            }
        });



        // Initialize select2
        $('.select2').select2({
            placeholder: 'Select an option',
            allowClear: true
        });

        @if(isset($vendor) && $vendor->id)
        // Set department value if exists
        const departmentId = '{{ $vendor->vnr_contact_department_id ?? '
        ' }}';
        if (departmentId) {
            // First set the value in the select element
            $('#vnr_contact_department_id').val(departmentId);

            // Then trigger change after a small delay to ensure Select2 is ready
            setTimeout(function() {
                $('#vnr_contact_department_id').trigger('change');

                // Load employees for this department with the selected employee
                const employeeId = '{{ $vendor->vnr_contact_person_id ?? '
                ' }}';
                if (employeeId) {
                    // Small delay to ensure department change is processed
                    setTimeout(function() {
                        $('#vnr_contact_person_id').val(employeeId).trigger('change');
                    }, 200);
                }
            }, 100);
        }
        @endif

        // Handle next/previous step navigation
        $('.next-step').click(function(e) {
            e.preventDefault();
            submitForm(false);
        });

        $('.prev-step').click(function() {
            const currentStep = parseInt($('input[name="current_step"]').val());
            $('input[name="current_step"]').val(currentStep - 1);
            loadStep(currentStep - 1);
        });

        $('.submit-form').click(function(e) {
            e.preventDefault();
            submitForm(true);
        });

        // Add click handler for stepper navigation
        // Replace your current step click handler with this:
        $(document).on('click', '.step.clickable', function() {
            const step = $(this).data('step');
            const currentStep = parseInt($('input[name="current_step"]').val());
            const maxCompletedStep = {{ $vendor->current_step ?? 1 }};


            // Only allow navigation to completed steps
            if (step <= maxCompletedStep) {
                // Save current form data before switching steps
                const formData = $('#vendorForm').serializeArray();

                // Update UI
                $('input[name="current_step"]').val(step);
                loadStep(step);
                updateStepperUI(step);

                // Ensure form data is preserved
                setTimeout(() => {
                    formData.forEach(field => {
                        if (field.name !== 'current_step') {
                            const $field = $(`[name="${field.name}"]`);
                            if ($field.is(':checkbox, :radio')) {
                                $field.filter(`[value="${field.value}"]`).prop('checked', true);
                            } else {
                                $field.val(field.value);
                            }
                        }
                    });

                    // Reinitialize Select2 if needed
                    $('.select2').trigger('change');
                }, 100);
            }
        });

        function loadStep(step) {
            $('.step-content').addClass('d-none');
            $(`#step${step}`).removeClass('d-none');
            clearErrors();

            // Update button visibility
            const maxCompletedStep = {{ $vendor->current_step ?? 1 }};

            $('.prev-step').toggle(step > 1);
            $('.next-step').toggle(step < maxCompletedStep);
            $('.submit-form').toggle(step === maxCompletedStep && step === 3);
        }


        function updateStepperUI(currentStep) {
            const maxCompletedStep = {{ $vendor->current_step ?? 1 }};


            $('.step').removeClass('active');
            for (let i = 1; i <= currentStep; i++) {
                $(`.step[data-step="${i}"]`).addClass('active');
            }

            // Update completed steps styling
            $('.step').removeClass('completed');
            for (let i = 1; i <= maxCompletedStep; i++) {
                $(`.step[data-step="${i}"]`).addClass('completed');
            }
        }

        function submitForm(isFinalSubmit) {
            clearErrors();

            const form = $('#vendorForm')[0];
            const formData = new FormData(form);

            $.ajax({
                url: "{{ route('vendors.store') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            $('input[name="current_step"]').val(response.next_step);
                            $('input[name="vendor_id"]').val(response.vendor_id);
                            loadStep(response.next_step);
                            updateStepperUI(response.next_step);
                        }
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        displayErrors(errors);
                    } else {
                        alert('An error occurred. Please try again.');
                    }
                }
            });
        }

        function clearErrors() {
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
        }

        function displayErrors(errors) {
            $.each(errors, function(field, messages) {
                const input = $('[name="' + field + '"]');

                // Special handling for radio buttons
                if (input.is(':radio')) {
                    const radioGroup = input.closest('.radio-group');
                    radioGroup.addClass('is-invalid');

                    const errorElement = $('<span class="invalid-feedback d-block">')
                        .append('<strong>' + messages[0] + '</strong>');

                    radioGroup.after(errorElement);
                }
                // Special handling for Select2 elements
                else if (input.hasClass('select2-hidden-accessible')) {
                    const select2Container = input.next('.select2-container');
                    select2Container.find('.select2-selection').addClass('is-invalid');

                    const errorElement = $('<span class="invalid-feedback d-block">')
                        .append('<strong>' + messages[0] + '</strong>');

                    select2Container.after(errorElement);
                }
                // Normal handling for other fields
                else {
                    input.addClass('is-invalid');

                    const errorElement = $('<span class="invalid-feedback d-block">')
                        .append('<strong>' + messages[0] + '</strong>');

                    if (input.is('select')) {
                        input.closest('.form-group').append(errorElement);
                    } else {
                        input.after(errorElement);
                    }
                }
            });

            // Scroll to first error
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 500);
        }
    });
</script>
@endpush