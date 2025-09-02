@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h3 class="mb-0">Vendor Submission Received</h3>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-clock fa-5x text-info"></i>
                    </div>
                    <h4 class="mb-3">Your vendor application is pending approval</h4>
                    <p class="lead">Thank you for submitting your vendor information.</p>
                    <p>Your application is currently under review by our administration team. You will be notified once it's approved.</p>
                    
                    <div class="mt-4">
                        <a href="{{ route('vendors.index') }}" class="btn btn-primary">
                            <i class="fas fa-home mr-2"></i> Return to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection