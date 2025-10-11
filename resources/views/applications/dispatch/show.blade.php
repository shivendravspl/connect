@extends('layouts.app')

@section('content')
<div class="container-fluid px-2 px-md-3 py-4 bg-light">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8">
            <div class="card shadow-sm rounded-3">
                <div class="card-header text-white py-2 px-3 small" style="background-color: #72e7d3;">
                    <h6 class="mb-0">
                        <i class="ri-truck-line me-1"></i> 
                        @if($dispatch->exists)
                            View Dispatch Details for Application #{{ $application->application_code ?? $application->id }}
                        @else
                            Dispatch Physical Documents for Application #{{ $application->application_code ?? $application->id }}
                        @endif
                    </h6>
                </div>
                <div class="card-body p-3 small">
                    @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                    @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show small" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <form id="dispatch-form" method="POST" action="{{ route('dispatch.store', $application->id) }}" class="small">
                        @csrf

                        <!-- Dispatch Mode -->
                        <div class="mb-3">
                            <label for="mode" class="form-label fw-semibold small">Dispatch Mode</label>
                            <select name="mode" id="mode" class="form-select form-select-sm @error('mode') is-invalid @enderror" 
                                {{ $dispatch->exists ? 'disabled' : 'required' }}>
                                <option value="" disabled {{ old('mode', $dispatch->mode) ? '' : 'selected' }}>Choose mode...</option>
                                <option value="transport" {{ old('mode', $dispatch->mode) == 'transport' ? 'selected' : '' }}>Transport</option>
                                <option value="courier" {{ old('mode', $dispatch->mode) == 'courier' ? 'selected' : '' }}>Courier</option>
                                <option value="by_hand" {{ old('mode', $dispatch->mode) == 'by_hand' ? 'selected' : '' }}>By Hand</option>
                            </select>
                            @error('mode')
                            <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Transport -->
                        <div id="transport-fields" class="dispatch-fields {{ old('mode', $dispatch->mode) == 'transport' ? '' : 'd-none' }}">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="transport_name" class="form-label small">Transport Name</label>
                                    <input type="text" name="transport_name" id="transport_name" 
                                        class="form-control form-control-sm @error('transport_name') is-invalid @enderror" 
                                        value="{{ old('transport_name', $dispatch->transport_name) }}"
                                        {{ $dispatch->exists ? 'disabled' : '' }}>
                                    @error('transport_name')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="driver_name" class="form-label small">Driver Name</label>
                                    <input type="text" name="driver_name" id="driver_name" 
                                        class="form-control form-control-sm @error('driver_name') is-invalid @enderror" 
                                        value="{{ old('driver_name', $dispatch->driver_name) }}"
                                        {{ $dispatch->exists ? 'disabled' : '' }}>
                                    @error('driver_name')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="driver_contact" class="form-label small">Driver Contact</label>
                                    <input type="text" name="driver_contact" id="driver_contact" 
                                        class="form-control form-control-sm @error('driver_contact') is-invalid @enderror" 
                                        value="{{ old('driver_contact', $dispatch->driver_contact) }}"
                                        {{ $dispatch->exists ? 'disabled' : '' }}>
                                    @error('driver_contact')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Courier -->
                        <div id="courier-fields" class="dispatch-fields {{ old('mode', $dispatch->mode) == 'courier' ? '' : 'd-none' }}">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="docket_number" class="form-label small">Docket Number</label>
                                    <input type="text" name="docket_number" id="docket_number" 
                                        class="form-control form-control-sm @error('docket_number') is-invalid @enderror" 
                                        value="{{ old('docket_number', $dispatch->docket_number) }}"
                                        {{ $dispatch->exists ? 'disabled' : '' }}>
                                    @error('docket_number')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="courier_company_name" class="form-label small">Courier Company Name</label>
                                    <input type="text" name="courier_company_name" id="courier_company_name" 
                                        class="form-control form-control-sm @error('courier_company_name') is-invalid @enderror" 
                                        value="{{ old('courier_company_name', $dispatch->courier_company_name) }}"
                                        {{ $dispatch->exists ? 'disabled' : '' }}>
                                    @error('courier_company_name')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- By Hand -->
                        <div id="by-hand-fields" class="dispatch-fields {{ old('mode', $dispatch->mode) == 'by_hand' ? '' : 'd-none' }}">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label for="person_name" class="form-label small">Person Name</label>
                                    <input type="text" name="person_name" id="person_name" 
                                        class="form-control form-control-sm @error('person_name') is-invalid @enderror" 
                                        value="{{ old('person_name', $dispatch->person_name) }}"
                                        {{ $dispatch->exists ? 'disabled' : '' }}>
                                    @error('person_name')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="person_contact" class="form-label small">Person Contact</label>
                                    <input type="text" name="person_contact" id="person_contact" 
                                        class="form-control form-control-sm @error('person_contact') is-invalid @enderror" 
                                        value="{{ old('person_contact', $dispatch->person_contact) }}"
                                        {{ $dispatch->exists ? 'disabled' : '' }}>
                                    @error('person_contact')
                                    <div class="invalid-feedback small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="mb-3">
                            <label for="dispatch_date" class="form-label fw-semibold small">Dispatch Date</label>
                            <input type="date" name="dispatch_date" id="dispatch_date" 
                                class="form-control form-control-sm @error('dispatch_date') is-invalid @enderror" 
                                value="{{ old('dispatch_date', $dispatch->dispatch_date) }}" 
                                {{ $dispatch->exists ? 'disabled' : 'required' }} 
                                max="{{ date('Y-m-d') }}">
                            @error('dispatch_date')
                            <div class="invalid-feedback small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('applications.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="ri-arrow-left-line me-1"></i> Back to Applications
                            </a>
                            
                            @if (!$dispatch->exists)
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-save-line me-1"></i> Submit Dispatch Details
                            </button>
                            @else
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="window.print()">
                                <i class="ri-printer-line me-1"></i> Print Details
                            </button>
                            @endif
                        </div>
                    </form>                  
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        console.log('Dispatch form script initialized');

        // Function to toggle fields based on mode
        function toggleFields(mode) {
            console.log('Toggling fields for mode: ' + mode);

            // Hide all
            $('.dispatch-fields')
                .addClass('d-none')
                .find('input')
                .prop('required', false);

            // Only enable/disable if form is not in view mode
            const isViewMode = {{ $dispatch->exists ? 'true' : 'false' }};
            if (!isViewMode) {
                $('.dispatch-fields').find('input').prop('disabled', true);
            }

            // Map mode to actual container id
            const fieldMap = {
                transport: '#transport-fields',
                courier: '#courier-fields',
                by_hand: '#by-hand-fields'
            };

            if (mode && fieldMap[mode]) {
                $(fieldMap[mode])
                    .removeClass('d-none')
                    .find('input')
                    .prop('required', true);

                // Only enable if form is not in view mode
                if (!isViewMode) {
                    $(fieldMap[mode]).find('input').prop('disabled', false);
                }
            }
        }

        // Initialize fields based on current mode
        const initialMode = $('#mode').val();
        console.log('Initial mode: ' + initialMode);
        toggleFields(initialMode);

        // Handle mode change only if not in view mode
        @if(!$dispatch->exists)
        $('#mode').on('change', function() {
            const mode = $(this).val();
            console.log('Mode changed to: ' + mode);
            toggleFields(mode);
        });
        @endif

        // Debug form submission
        $('#dispatch-form').on('submit', function(e) {
            console.log('Form submitting via POST to: ' + $(this).attr('action'));
            console.log('Form data: ', $(this).serializeArray());
            
            // Clear disabled fields to prevent them from being sent
            $(this).find('input:disabled').val('');
        });

        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endpush

@push('styles')
<style>
    .form-label {
        margin-bottom: 0.25rem;
    }

    .form-control-sm,
    .form-select-sm {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }

    .card-header h6 {
        font-size: 0.9rem;
    }

    /* Style for disabled fields in view mode */
    .form-control:disabled,
    .form-select:disabled {
        background-color: #f8f9fa;
        border-color: #e9ecef;
        color: #6c757d;
        opacity: 1;
    }

    .border-top {
        border-top: 1px solid #dee2e6 !important;
    }
</style>
@endpush
@endsection