@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2">
                    <h5 class="mb-0">Edit Distributor Application ({{ $application->application_code }})</h5>
                </div>

                <div class="card-body p-2">
                    <div class="stepper-wrapper" data-current-step="{{ $initialFrontendStep }}">
                        <div class="stepper d-flex flex-wrap justify-content-between mb-3">
                            @php
                            $steps = ['Basic Details', 'Entity Details', 'Distribution Details', 'Business Plan',
                            'Financial & Distributorships', 'Bank Details', 'Declarations', 'Review & Submit'];

                            $completedStepsData = [
                            1 => !empty($application->territory) && !empty($application->crop_vertical) && !empty($application->region) && !empty($application->zone) && !empty($application->business_unit) && !empty($application->district) && !empty($application->state),
                            2 => $application->entityDetails && !empty($application->entityDetails->establishment_name) && !empty($application->entityDetails->pan_number),
                            3 => $application->distributionDetail && !empty($application->distributionDetail->area_covered),
                            4 => $application->businessPlans->isNotEmpty() && $application->businessPlans->first(), // Use a real key field
                            5 => $application->financialInfo && !empty($application->financialInfo->net_worth), // Use a real key field
                            6 => $application->bankDetail && !empty($application->bankDetail->bank_name) && !empty($application->bankDetail->account_number), // Use a real key field
                            7 => $application->declarations->isNotEmpty(),
                            8 => $application->status === 'submitted',
                            ];

                            $currentStep = $initialFrontendStep;
                            @endphp

                            @foreach($steps as $index => $stepName)
                            @php
                            $stepNumber = $index + 1;
                            $isActive = ($currentStep == $stepNumber);
                            $isCompleted = $completedStepsData[$stepNumber] ?? false;
                            $isClickable = $isCompleted || ($stepNumber < $currentStep);
                                @endphp

                                <div class="step {{ $isActive ? 'active' : '' }} {{ $isCompleted ? 'completed' : '' }} {{ $isClickable ? 'clickable' : '' }} mb-2"
                                data-step="{{ $stepNumber }}">
                                <div class="step-circle" style="width:24px;height:24px;font-size:12px;line-height:24px;">{{ $stepNumber }}</div>
                                <div class="step-label" style="font-size:11px;">{{ $stepName }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <form id="distributorForm" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="application_id" name="application_id" value="{{ $application->id }}">

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

                            @if($application->id)
                            <iframe src="{{ route('application.preview', $application->id) }}"
                                style="width:100%; height:70vh; border:1px solid #ccc;"></iframe>
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

                    <div class="form-navigation mt-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-sm btn-secondary previous" style="display:none; min-width:80px;">
                            <i class="fas fa-arrow-left d-sm-none"></i>
                            <span class="d-none d-sm-inline">Previous</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-primary next" style="min-width:80px;">
                            <span class="d-none d-sm-inline">Next</span>
                            <i class="fas fa-arrow-right d-sm-none"></i>
                        </button>
                        <button type="submit" class="btn btn-sm btn-success submit" style="display:none; min-width:80px;" name="submit" id="submit-application">
                            <i class="fas fa-check d-sm-none"></i>
                            <span class="d-none d-sm-inline">Submit</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/application-form.js') }}"></script>
@endpush