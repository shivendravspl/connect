<!-- resources/views/applications/edit.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Distributor Application ({{ $application->application_code }})</h4>
                </div>
                <div class="card-body">
                    @include('components.stepper', ['steps' => [
                        'Basic Details',
                        'Entity Details',
                        'Distribution Details',
                        'Business Plan',
                        'Financial Info',
                        'Existing Distributors',
                        'Bank Details',
                        'Declarations',
                        'Review & Submit'
                    ]])

                    <form id="distributorForm" method="POST" action="{{ route('applications.update', $application) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="application_id" name="application_id" value="{{ $application->id }}">

                        <!-- Step 1: Basic Details -->
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

                        <!-- Step 2: Entity Details -->
                        <div class="step-content" data-step="2" style="display:none;">
                            @include('components.form-sections.entity-details', ['application' => $application,'states' => $states])
                        </div>

                        <!-- Other steps -->
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
                            @include('components.form-sections.existing-distributorships', ['application' => $application])
                        </div>
                         <div class="step-content" data-step="7" style="display:none;">
                            @include('components.form-sections.bank-details', ['application' => $application])
                        </div>
                        <div class="step-content" data-step="8" style="display:none;">
                            @include('components.form-sections.declarations', ['application' => $application])
                        </div>
                        {{--<div class="step-content" data-step="9" style="display:none;">
                            @include('components.form-sections.documents', ['application' => $application])
                        </div>--}}
                        <div class="step-content" data-step="9" style="display:none;">
                            @include('components.form-sections.review', ['application' => $application])
                        </div>

                        <div class="form-navigation mt-4">
                            <button type="button" class="btn btn-secondary previous" style="display:none;">Previous</button>
                            <button type="button" class="btn btn-primary next">Next</button>
                            <button type="submit" class="btn btn-success submit" style="display:none;" name="submit">Submit Application</button>
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