@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0">Vendor Onboarding Successful</h3>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle fa-5x text-success"></i>
                    </div>
                    <h4 class="mb-3">Thank you for submitting your vendor information!</h4>
                    <p class="lead">Your vendor onboarding request has been successfully submitted.</p>
                    <p>Our team will review your information and contact you if any additional details are required.</p>
                    
                    <div class="mt-4">
                        <a href="{{ route('vendors.index') }}" class="btn btn-primary">
                            <i class="fas fa-list mr-2"></i> Return to Vendors List
                        </a>
                        <a href="{{ route('vendors.edit', $vendor->id) }}" class="btn btn-outline-secondary ml-2">
                            <i class="fas fa-edit mr-2"></i> Edit Information
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection