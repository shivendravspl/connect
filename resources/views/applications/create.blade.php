@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0">New Distributor Application</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Pages</a></li>
                        <li class="breadcrumb-item active">New Distributor</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2">
                    <h5 class="mb-0">New Distributor Application</h5>
                </div>
                <div class="card-body p-2">
                    <div class="stepper-wrapper" data-current-step="{{ $currentStep ?? 1 }}">
                        <div class="stepper d-flex flex-wrap justify-content-between mb-3">
                            @php
                            $steps = ['Basic Details', 'Entity Details', 'Distribution Details', 'Business Plan', 'Financial & Distributorshipships', 'Financial Status & Banking Information', 'Declarations', 'Review & Submit'];
                            @endphp

                          @foreach($steps as $index => $stepName)
                            @php
                                $stepNumber   = $index + 1;
                                $isActive     = ($currentStep ?? 1) == $stepNumber;
                                $isCompleted  = $completedStepsData[$stepNumber] ?? false;
                                $hasAppId     = $application_id || (isset($application) && $application->id);

                                // Default: not clickable
                                $isClickable = false;

                                // Step 1 is always clickable
                                if ($stepNumber === 1) {
                                    $isClickable = true;
                                }
                                // Steps 2–7 require app_id + Step 1 completed
                                elseif ($stepNumber <= 7 && $hasAppId && ($completedStepsData[1] ?? false)) {
                                    $isClickable = true;
                                }
                                // Step 8 requires app_id + all Steps 1–7 completed
                                elseif ($stepNumber === 8 && $hasAppId && !in_array(false, array_slice($completedStepsData, 1, 7))) {
                                    $isClickable = true;
                                }
                            @endphp

                            <div class="step {{ $isActive ? 'active' : '' }} {{ $isCompleted ? 'completed' : '' }} {{ $isClickable ? 'clickable' : '' }} mb-2"
                                 data-step="{{ $stepNumber }}">
                                <div class="step-circle" style="width:24px;height:24px;font-size:12px;line-height:24px;">{{ $stepNumber }}</div>
                                <div class="step-label" style="font-size:11px;">{{ $stepName }}</div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <form id="distributorForm" method="POST" action="{{ route('applications.save-step', $currentStep ?? 1) }}" enctype="multipart/form-data" novalidate>
                        @csrf
                        <input type="hidden" id="application_id" name="application_id" value="{{ $application_id ?? $application->id ?? '' }}">

                        <div class="step-content" data-step="1">
                            @include('components.form-sections.step1', [
                            'territory_list' => $territory_list,
                            'zone_list' => $zone_list,
                            'region_list' => $region_list,
                            'preselected' => $preselected,
                            'crop_type' => $crop_type,
                            'states' => $states,
                            'application' => $application ?? new \App\Models\Onboarding()
                            ])
                        </div>

                        <div class="step-content" data-step="2" style="display:none;">
                            @include('components.form-sections.entity-details', ['application' => $application ?? new \App\Models\Onboarding(), 'states' => $states])
                        </div>

                        <div class="step-content" data-step="3" style="display:none;">
                            @include('components.form-sections.distribution-details', ['application' => $application ?? new \App\Models\Onboarding()])
                        </div>

                        <div class="step-content" data-step="4" style="display:none;">
                            @include('components.form-sections.business-plan', ['application' => $application ?? new \App\Models\Onboarding()])
                        </div>

                        <div class="step-content" data-step="5" style="display:none;">
                            @include('components.form-sections.financial-info', ['application' => $application ?? new \App\Models\Onboarding()])
                        </div>

                        <div class="step-content" data-step="6" style="display:none;">
                            @include('components.form-sections.bank-details', ['application' => $application ?? new \App\Models\Onboarding()])
                        </div>

                        <div class="step-content" data-step="7" style="display:none;">
                            @include('components.form-sections.declarations', ['application' => $application ?? new \App\Models\Onboarding()])
                        </div>

                        <div class="step-content" data-step="8" style="display:none;">
                            <div class="card p-3">
                                <h5 class="mb-3">Final Review</h5>
                                @if($application_id || (isset($application) && $application->id))
                                    <iframe src="{{ route('application.preview', ($application_id ?? $application->id)) }}"
                                        style="width:100%; height:70vh; border:1px solid #ccc;"></iframe>
                                    <div class="mt-3">
                                        <a href="{{ route('application.download', ($application_id ?? $application->id)) }}"
                                            class="btn btn-sm btn-primary" download>
                                            <i class="fas fa-download"></i> Download PDF
                                        </a>
                                    </div>
                                @else
                                    <p class="text-danger">Please save the application to enable preview.</p>
                                @endif

                                <div class="mt-4 form-check">
                                    <input type="checkbox" class="form-check-input" id="confirm_accuracy" name="confirm_accuracy" required>
                                    <label class="form-check-label" for="confirm_accuracy">
                                        I confirm that all the information provided above is accurate and complete to the best of my knowledge.
                                    </label>
                                    <div class="invalid-feedback text-danger">Please confirm the accuracy before submitting.</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-navigation mt-3 d-flex justify-content-between align-items-center sticky-bottom bg-white p-2 border-top">
                            <button type="button" class="btn btn-sm btn-secondary previous" style="display:none; min-width:80px;">
                                <i class="fas fa-arrow-left d-sm-none"></i>
                                <span class="d-none d-sm-inline">Previous</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary next" style="min-width:80px;">
                                <span class="d-none d-sm-inline">Next</span>
                                <i class="fas fa-arrow-right d-sm-none"></i>
                            </button>
                            <div>
                                @if($currentStep == 8 && !in_array(false, array_slice($completedStepsData, 1, 7)))
                                    <button type="submit" class="btn btn-sm btn-success submit" style="min-width:80px;" id="submit-application">
                                        <i class="fas fa-check d-sm-none"></i>
                                        <span class="d-none d-sm-inline">Submit</span>
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-sm btn-success submit" style="min-width:80px;" id="submit-application" disabled>
                                        <i class="fas fa-check d-sm-none"></i>
                                        <span class="d-none d-sm-inline">Submit</span>
                                    </button>
                                    @if($currentStep == 8)
                                        <p class="text-danger mt-2">Please complete all previous steps before submitting.</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize completedStepsData with fallback
    const completedStepsData = @json($completedStepsData) || {
        1: false, 2: false, 3: false, 4: false, 5: false, 6: false, 7: false, 8: false
    };
    console.log('completedStepsData:', completedStepsData);
    // Log initial application_id
    console.log('Initial blade application_id:', '{{ $application_id ?? $application->id ?? 'none' }}');
</script>
<script src="{{ asset('js/application-form.js') }}"></script>
@endpush