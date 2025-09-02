@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3>Vendor Registrattion Form</h3>
                </div>
                <div class="card-body">
                    <!-- Stepper -->
                    <div class="steps">
                        @foreach([1 => 'Company Information', 2 => 'Legal Information', 3 => 'Banking Information'] as $step => $title)
                            <div class="step 
                                @if($current_step == $step) active @endif
                                @if($step < $current_step || ($step == 1 && $current_step > 1)) clickable completed @endif"
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
                                <button type="button" class="btn btn-secondary prev-step me-2">Previous</button>
                            @endif
                            
                            <button type="submit" class="btn btn-primary">
                                @if($current_step < 3) Continue @else Submit Application @endif
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Modal -->
<div class="modal fade" id="documentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">Document Viewer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="documentFrame" style="height: 70vh;"></div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

    .step.active:not(:last-child):after,
    .step.completed:not(:last-child):after {
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

    .step.completed .step-number {
        background: #28a745;
        color: white;
    }

    .step-title {
        font-size: 14px;
        color: #6c757d;
    }

    .step.active .step-title,
    .step.completed .step-title {
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

    select.is-invalid {
        border: 1px solid #dc3545;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize form with current step
        updateStepperUI({{ $current_step }});
        
        // Clear errors when user interacts with fields
        $('input, select, textarea').on('input change', function() {
            clearFieldError($(this));
        });

        function clearFieldError(fieldElement) {
            if (!fieldElement || !fieldElement.length) return;
            fieldElement.removeClass('is-invalid');
            fieldElement.closest('.form-group').find('.invalid-feedback').remove();
        }

        // Department change handler
        $('#vnr_contact_department_id').change(function() {
            const departmentId = $(this).val();
            const employeeSelect = $('#vnr_contact_person_id');
            const currentEmployeeId = employeeSelect.val();

            employeeSelect.empty().append('<option value="">Loading...</option>');

            if (departmentId) {
                $.ajax({
                    url: '/vendors/employees/by-department/' + departmentId,
                    type: 'GET',
                    success: function(data) {
                        employeeSelect.empty();
                        employeeSelect.append('<option value="">Select Employee</option>');

                        $.each(data, function(key, employee) {
                            const option = new Option(
                                employee.text,
                                employee.id,
                                false,
                                employee.id == currentEmployeeId
                            );
                            employeeSelect.append(option);
                        });

                        if (currentEmployeeId && employeeSelect.find('option[value="' + currentEmployeeId + '"]').length) {
                            employeeSelect.val(currentEmployeeId);
                        }
                    },
                    error: function() {
                        employeeSelect.empty().append('<option value="">Error loading employees</option>');
                    }
                });
            } else {
                employeeSelect.empty().append('<option value="">Select Department first</option>');
            }
        });

        @if(isset($vendor) && $vendor->id)
        // Set department value if exists
        const departmentId = '{{ $vendor->vnr_contact_department_id ?? '' }}';
        if (departmentId) {
            $('#vnr_contact_department_id').val(departmentId);
            setTimeout(function() {
                $('#vnr_contact_department_id').trigger('change');
                
                const employeeId = '{{ $vendor->vnr_contact_person_id ?? '' }}';
                if (employeeId) {
                    setTimeout(function() {
                        $('#vnr_contact_person_id').val(employeeId);
                    }, 200);
                }
            }, 100);
        }
        @endif

        // Handle form submission
        $('#vendorForm').on('submit', function(e) {
            e.preventDefault();
            submitForm();
        });

        // Handle previous step button
        $('.prev-step').click(function() {
            const currentStep = parseInt($('input[name="current_step"]').val());
            if (currentStep > 1) {
                navigateToStep(currentStep - 1);
            }
        });

        // Stepper navigation - handle clicking on steps
     $(document).on('click', '.step.clickable', function() {
        const targetStep = $(this).data('step');
        const currentStep = parseInt($('input[name="current_step"]').val());

        if (targetStep < currentStep) {
            if (confirm('Are you sure you want to go back to this step? Unsaved changes may be lost.')) {
                navigateToStep(targetStep);
            }
        }
    });

        function navigateToStep(step) {
            // Save current form data before navigating
            const formData = $('#vendorForm').serializeArray();
            
            // Update the current step
            $('input[name="current_step"]').val(step);
            
            // Load the step content
            loadStepContent(step);
            
            // Update the stepper UI
            updateStepperUI(step);
            
            // Restore form data after a short delay to allow DOM to update
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
            }, 100);
        }

        function loadStepContent(step) {
            // Hide all step content
            $('.step-content').addClass('d-none');
            
            // Show the current step content
            $(`#step${step}`).removeClass('d-none');
            
            // Clear any validation errors
            clearErrors();
            
            // Update button visibility
            $('.prev-step').toggle(step > 1);
            
            // Update submit button text
            $('.btn-primary').text(step < 3 ? 'Continue' : 'Submit Application');
        }

         function updateStepperUI(currentStep) {
    // Get the maximum step completed so far
    let maxStep = parseInt($('input[name="max_step_completed"]').val()) || currentStep;

    $('.step').removeClass('active completed clickable');

    $('.step').each(function() {
        const step = $(this).data('step');

        if (step == currentStep) {
            $(this).addClass('active');
        } 
        else if (step < currentStep) {
            $(this).addClass('completed clickable');
        } 

        // Only make steps up to maxStep clickable
        if (step <= maxStep) {
            $(this).addClass('clickable');
        }
    });
}


        function submitForm() {
            clearErrors();

            const form = $('#vendorForm')[0];
            const formData = new FormData(form);
            const isFinalStep = $('input[name="current_step"]').val() == 3;

            $.ajax({
                url: "{{ route('vendors.store') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        if (isFinalStep) {
                            // Redirect to success page or dashboard
                            window.location.href = response.redirect || "{{ route('vendors.index') }}";
                        } else {
                            // Update hidden fields and move to next step
                            $('input[name="current_step"]').val(response.next_step);
                            $('input[name="vendor_id"]').val(response.vendor_id);
                            navigateToStep(response.next_step);
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
                input.addClass('is-invalid');

                const errorElement = $('<span class="invalid-feedback d-block">')
                    .append('<strong>' + messages[0] + '</strong>');

                if (input.is('select')) {
                    input.closest('.form-group').append(errorElement);
                } else {
                    input.after(errorElement);
                }
            });

            // Scroll to first error
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 500);
        }
    });

    // Document viewer functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Handle document viewing
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('view-document')) {
                e.preventDefault();
                const url = e.target.dataset.url;
                const frame = document.getElementById('documentFrame');
                frame.innerHTML = `<iframe src="${url}" style="width:100%; height:100%; border:none;"></iframe>`;
                
                const modal = new bootstrap.Modal(document.getElementById('documentModal'));
                modal.show();
            }
        });

        // Clean up when modal closes
        document.getElementById('documentModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('documentFrame').innerHTML = '';
        });
    });
</script>
@endpush