@extends('layouts.app')

@section('content')
<div class="container-fluid px-2 px-md-3 py-4 bg-light">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8">
            
            <!-- Show previous dispatch history if exists -->
            @if($previousDispatches->count() > 0 || $latestDispatch)
            <div class="card shadow-sm rounded-3 mb-3">
                <div class="card-header text-white py-2 px-3 small" style="background-color: #6c757d;">
                    <h6 class="mb-0">
                        <i class="ri-history-line me-1"></i> 
                        Dispatch History
                    </h6>
                </div>
                <div class="card-body p-3 small">
                    @if($latestDispatch)
                    <div class="border-bottom pb-2 mb-2">
                        <h6 class="fw-bold text-primary">Latest Dispatch</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Dispatch Date:</strong> {{ $latestDispatch->dispatch_date }}<br>
                                <strong>Mode:</strong> {{ ucfirst($latestDispatch->mode) }}<br>
                                @if($latestDispatch->receive_date)
                                <strong>Received Date:</strong> {{ $latestDispatch->receive_date }}
                                @else
                                <span class="badge bg-warning">Not Received</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong>Created At:</strong> {{ $latestDispatch->created_at->format('d-M-Y H:i') }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($previousDispatches->count() > 0)
                    <h6 class="fw-bold mt-3">Previous Dispatches</h6>
                    @foreach($previousDispatches as $previous)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Dispatch Date:</strong> {{ $previous->dispatch_date }}<br>
                                <strong>Mode:</strong> {{ ucfirst($previous->mode) }}<br>
                                @if($previous->receive_date)
                                <strong>Received Date:</strong> {{ $previous->receive_date }}
                                @else
                                <span class="badge bg-warning">Not Received</span>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <strong>Created By:</strong> {{ $previous->creator->name ?? 'Unknown' }}<br>
                                <strong>Created At:</strong> {{ $previous->created_at->format('d-M-Y H:i') }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
            @endif

            <!-- Dispatch Form Card -->
            <div class="card shadow-sm rounded-3">
                <div class="card-header text-white py-2 px-3 small" style="background-color: #72e7d3;">
                    <h6 class="mb-0">
                        <i class="ri-truck-line me-1"></i> 
                        @if($latestDispatch)
                            Redispatch Physical Documents for Application #{{ $application->application_code ?? $application->id }}
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

                    @if($canRedispatch)
                        @if($latestDispatch)
                        <div class="alert alert-warning small">
                            <i class="ri-information-line me-1"></i>
                            <strong>Note:</strong> This is a re-dispatch. Previous dispatch records are preserved above.
                        </div>
                        @endif

                        <form id="dispatch-form" method="POST" action="{{ route('dispatch.store', $application->id) }}" class="small">
                            @csrf

                            <!-- Dispatch Mode -->
                            <div class="mb-3">
                                <label for="mode" class="form-label fw-semibold small">Dispatch Mode</label>
                                <select name="mode" id="mode" class="form-select form-select-sm @error('mode') is-invalid @enderror" required>
                                    <option value="" disabled {{ old('mode') ? '' : 'selected' }}>Choose mode...</option>
                                    <option value="transport" {{ old('mode') == 'transport' ? 'selected' : '' }}>Transport</option>
                                    <option value="courier" {{ old('mode') == 'courier' ? 'selected' : '' }}>Courier</option>
                                    <option value="by_hand" {{ old('mode') == 'by_hand' ? 'selected' : '' }}>By Hand</option>
                                </select>
                                @error('mode')
                                <div class="invalid-feedback small">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Transport -->
                            <div id="transport-fields" class="dispatch-fields {{ old('mode') == 'transport' ? '' : 'd-none' }}">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label for="transport_name" class="form-label small">Transport Name</label>
                                        <input type="text" name="transport_name" id="transport_name" 
                                            class="form-control form-control-sm @error('transport_name') is-invalid @enderror" 
                                            value="{{ old('transport_name') }}">
                                        @error('transport_name')
                                        <div class="invalid-feedback small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="driver_name" class="form-label small">Driver Name</label>
                                        <input type="text" name="driver_name" id="driver_name" 
                                            class="form-control form-control-sm @error('driver_name') is-invalid @enderror" 
                                            value="{{ old('driver_name') }}">
                                        @error('driver_name')
                                        <div class="invalid-feedback small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="driver_contact" class="form-label small">Driver Contact</label>
                                        <input type="text" name="driver_contact" id="driver_contact" 
                                            class="form-control form-control-sm @error('driver_contact') is-invalid @enderror" 
                                            value="{{ old('driver_contact') }}">
                                        @error('driver_contact')
                                        <div class="invalid-feedback small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Courier -->
                            <div id="courier-fields" class="dispatch-fields {{ old('mode') == 'courier' ? '' : 'd-none' }}">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label for="docket_number" class="form-label small">Docket Number</label>
                                        <input type="text" name="docket_number" id="docket_number" 
                                            class="form-control form-control-sm @error('docket_number') is-invalid @enderror" 
                                            value="{{ old('docket_number') }}">
                                        @error('docket_number')
                                        <div class="invalid-feedback small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="courier_company_name" class="form-label small">Courier Company Name</label>
                                        <input type="text" name="courier_company_name" id="courier_company_name" 
                                            class="form-control form-control-sm @error('courier_company_name') is-invalid @enderror" 
                                            value="{{ old('courier_company_name') }}">
                                        @error('courier_company_name')
                                        <div class="invalid-feedback small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- By Hand -->
                            <div id="by-hand-fields" class="dispatch-fields {{ old('mode') == 'by_hand' ? '' : 'd-none' }}">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label for="person_name" class="form-label small">Person Name</label>
                                        <input type="text" name="person_name" id="person_name" 
                                            class="form-control form-control-sm @error('person_name') is-invalid @enderror" 
                                            value="{{ old('person_name') }}">
                                        @error('person_name')
                                        <div class="invalid-feedback small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label for="person_contact" class="form-label small">Person Contact</label>
                                        <input type="text" name="person_contact" id="person_contact" 
                                            class="form-control form-control-sm @error('person_contact') is-invalid @enderror" 
                                            value="{{ old('person_contact') }}">
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
                                    value="{{ old('dispatch_date', date('Y-m-d')) }}" 
                                    required 
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
                                
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="ri-send-plane-line me-1"></i> 
                                    {{ $latestDispatch ? 'Redispatch Documents' : 'Dispatch Documents' }}
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-info">
                            <i class="ri-information-line me-1"></i>
                            Dispatch is not available in the current application status.
                        </div>
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('applications.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="ri-arrow-left-line me-1"></i> Back to Applications
                            </a>
                        </div>
                    @endif                  
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
                .prop('required', false)
                .prop('disabled', true);

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
                    .prop('required', true)
                    .prop('disabled', false);
            }
        }

        // Initialize fields based on current mode
        const initialMode = $('#mode').val();
        console.log('Initial mode: ' + initialMode);
        toggleFields(initialMode);

        // Handle mode change
        $('#mode').on('change', function() {
            const mode = $(this).val();
            console.log('Mode changed to: ' + mode);
            toggleFields(mode);
        });

        // Debug form submission
        $('#dispatch-form').on('submit', function(e) {
            console.log('Form submitting via POST to: ' + $(this).attr('action'));
            console.log('Form data: ', $(this).serializeArray());
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