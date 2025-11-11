@extends('layouts.app')

@section('content')
<div class="container-fluid" style="padding: 1.5rem;">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2">
                    <h5 class="mb-0">Edit Distributor Application ({{ $application->application_code }})</h5>
                </div>

                <div class="card-body p-2">
                    <div class="stepper-wrapper" data-current-step="{{ $currentStep  }}">
                        <div class="stepper d-flex flex-wrap justify-content-between mb-3">
                        @php
$steps = ['Basic Details', 'Entity Details', 'Distribution Details', 'Business Plan', 'Financial & Distributorships', 'Financial Status & Banking Information', 'Declarations', 'Review & Submit'];

$completedStepsData = [
    1 => !empty($application->territory) && !empty($application->crop_vertical) && !empty($application->region) && !empty($application->zone) && !empty($application->business_unit), // Removed district and state
    2 => !empty($application->entityDetails) && !empty($application->entityDetails->establishment_name) && !empty($application->entityDetails->pan_number),
    3 => !empty($application->distributionDetail) && !empty($application->distributionDetail->area_covered) && is_array(json_decode($application->distributionDetail->area_covered, true)) && count(json_decode($application->distributionDetail->area_covered, true)) > 0,
    4 => $application->businessPlans->isNotEmpty(),
    5 => !empty($application->financialInfo) && !empty($application->financialInfo->net_worth),
    6 => !empty($application->bankDetail) && !empty($application->bankDetail->bank_name) && !empty($application->bankDetail->account_number),
    7 => $application->declarations->isNotEmpty(),
    8 => in_array($application->status, ['initiated', 'approved']),
];

// Debug completedStepsData
Log::info('completedStepsData for application_id: ' . ($application->id ?? 'unknown'), [
    'step1' => [
        'territory' => !empty($application->territory),
        'crop_vertical' => !empty($application->crop_vertical),
        'region' => !empty($application->region),
        'zone' => !empty($application->zone),
        'business_unit' => !empty($application->business_unit),
        'district' => !empty($application->district),
        'state' => !empty($application->state),
        'completed' => $completedStepsData[1]
    ],
    'step2' => $completedStepsData[2],
    'step3' => $completedStepsData[3],
    'step4' => $completedStepsData[4],
    'step5' => $completedStepsData[5],
    'step6' => $completedStepsData[6],
    'step7' => $completedStepsData[7],
    'step8' => $completedStepsData[8]
]);
@endphp
                            @foreach($steps as $index => $stepName)
                            @php
                            $stepNumber = $index + 1;
                            $isActive = ($currentStep == $stepNumber);
                            $isCompleted = $completedStepsData[$stepNumber] ?? false; // Use the derived completion status
                            $isClickable = $isCompleted || ($stepNumber < $currentStep); // A step is clickable if completed or if it's a previous step
                            @endphp

                            <div class="step {{ $isActive ? 'active' : '' }} {{ $isCompleted ? 'completed' : '' }} {{ $isClickable ? 'clickable' : '' }} mb-2"
                                data-step="{{ $stepNumber }}">
                                <div class="step-circle" style="width:24px;height:24px;font-size:12px;line-height:24px;">{{ $stepNumber }}</div>
                                <div class="step-label" style="font-size:11px;">{{ $stepName }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <form id="distributorForm" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="application_id" name="application_id" value="{{ $application->id }}">
                    <input type="hidden" id="initial_frontend_step" value="{{ $initialFrontendStep }}">


                    <div class="step-content" data-step="1">
                        @include('components.form-sections.step1', [
                        'application' => $application,
                        'territory_list' => $territory_list,
                        'zone_list' => $zone_list,
                        'region_list' => $region_list,
                        'preselected' => $preselected,
                        'crop_type' => $crop_type,
                        'states' => $states
                        ])
                    </div>

                    <div class="step-content" data-step="2" style="display:none;">
                        @include('components.form-sections.entity-details', ['application' => $application,'states' => $states])
                    </div>

                    <div class="step-content" data-step="3" style="display:none;">
                        @include('components.form-sections.distribution-details', ['application' => $application])
                    </div>

                    <div class="step-content" data-step="4" style="display:none;">
                        @include('components.form-sections.business-plan', ['application' => $application])
                    </div>

                    <div class="step-content" data-step="5" style="display:none;">
                        @include('components.form-sections.financial-info', ['application' => $application])
                    </div>

                    <div class="step-content" data-step="6" style="display:none;">
                        @include('components.form-sections.bank-details', ['application' => $application])
                    </div>

                    <div class="step-content" data-step="7" style="display:none;">
                        @include('components.form-sections.declarations', ['application' => $application])
                    </div>

                    <div class="step-content" data-step="8" style="display:none;">
                        <div class="card p-3">
                            <h5 class="mb-3">Final Review</h5>
                            
                            {{-- Remove the condition or ensure iframe is always present in edit mode --}}
                            @if($application->id)
                            <iframe src="{{ route('application.preview', $application->id) }}"
                                    style="width:100%; height:70vh; border:1px solid #ccc;"
                                    id="preview-iframe"></iframe>
                            <div class="mt-3">
                                <a href="{{ route('application.download', $application->id) }}"
                                class="btn btn-sm btn-primary" download>
                                    <i class="fas fa-download"></i> Download PDF
                                </a>
                            </div>
                            @else
                            <p class="text-danger">Please save the application before reviewing.</p>
                            @endif
                            <div class="mt-4 form-check">
                                <input type="checkbox" class="form-check-input" id="confirm_accuracy" required>
                                <label class="form-check-label" for="confirm_accuracy">
                                    I confirm that all the information provided above is accurate and complete to the best of my knowledge.
                                </label>
                                <div class="invalid-feedback text-danger">Please confirm the accuracy before submitting.</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-navigation mt-3 d-flex justify-content-between align-items-center sticky-bottom bg-white p-2 border-top">
    
                        <div class="d-flex">
                            <button type="button" class="btn btn-sm btn-secondary previous" style="display:none; min-width:80px;">
                                <i class="fas fa-arrow-left d-sm-none"></i>
                                <span class="d-none d-sm-inline">Previous</span>
                            </button>
                        </div>

                        <div class="ms-auto d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-primary next" style="min-width:80px;">
                                <span class="d-none d-sm-inline">Next</span>
                                <i class="fas fa-arrow-right d-sm-none"></i>
                            </button>
                            <button type="submit" class="btn btn-sm btn-success submit" style="display:none; min-width:80px;" name="submit" id="submit-application">
                                <i class="fas fa-check d-sm-none"></i>
                                <span class="d-none d-sm-inline">Submit</span>
                            </button>
                        </div>

                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize completedStepsData with fallback
     // Initialize completedStepsData with fallback
    const completedStepsData = @json($completedStepsData) || {
        1: false, 2: false, 3: false, 4: false, 5: false, 6: false, 7: false, 8: false
    };
    const isEditMode = true; // Since this is the edit page
    const currentApplicationId = {{ $application->id }};
    console.log('completedStepsData:', completedStepsData);
</script>
<script src="{{ asset('js/application-form.js') }}"></script>
@endpush