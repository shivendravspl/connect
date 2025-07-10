@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>New Distributor Application</h4>
                </div>
                <div class="card-body">
                    @include('components.stepper', ['steps' => [
                        'Basic Details', 
                        'Entity Details',
                        'Distribution Details',
                        'Business Plan',
                        'Financial Info',
                        'Existing Distributorships',
                        'Bank Details',
                        'Declarations',
                        'Review & Submit'
                    ]])
                    
                    <form id="distributorForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                         <!-- Step 1: Basic Details -->
                        <div class="step-content" data-step="1">
                            @include('components.form-sections.step1', [
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
                            @include('components.form-sections.entity-details')
                        </div>
                        
                        <!-- Step 3: Distribution Details -->
                        <div class="step-content" data-step="3" style="display:none;">
                            @include('components.form-sections.distribution-details')
                        </div>
                        
                        <!-- Step 4: Business Plan -->
                        <div class="step-content" data-step="4" style="display:none;">
                            @include('components.form-sections.business-plan')
                        </div>
                        
                        <!-- Step 5: Financial Info -->
                        <div class="step-content" data-step="5" style="display:none;">
                            @include('components.form-sections.financial-info')
                        </div>
                        
                        <!-- Step 6: Existing Distributorships -->
                        <div class="step-content" data-step="6" style="display:none;">
                            @include('components.form-sections.existing-distributorships')
                        </div>

                         <!-- Step 7: Bank Details -->
                        <div class="step-content" data-step="7" style="display:none;">
                            @include('components.form-sections.bank-details')
                        </div>
                        
                        
                        <!-- Step 8: Declarations -->
                        <div class="step-content" data-step="8" style="display:none;">
                            @include('components.form-sections.declarations')
                        </div>
                        
                        <!-- Step 9: Documents -->
                        {{-- <div class="step-content" data-step="9" style="display:none;">
                            @include('components.form-sections.documents')
                        </div> --}}
                        
                        <!-- Step 10: Review -->
                        {{--<div class="step-content" data-step="9" style="display:none;">
                            @include('components.form-sections.review')
                        </div>--}}
                        
                        <div class="form-navigation mt-4">
                            <button type="button" class="btn btn-secondary previous" style="display:none;">Previous</button>
                            <button type="button" class="btn btn-primary next">Next</button>
                            <button  id="submit-application" type="submit" class="btn btn-success submit" style="display:none;">Submit Application</button>
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