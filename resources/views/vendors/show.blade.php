@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h3>Vendor Details: {{ $vendor->company_name }}</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4>Company Information</h4>
                            <p><strong>Name:</strong> {{ $vendor->company_name }}</p>
                            <p><strong>Nature of Business:</strong> {{ $vendor->nature_of_business }}</p>
                            <p><strong>Purpose:</strong> {{ $vendor->purpose_of_transaction }}</p>
                            <p><strong>Address:</strong> {{ $vendor->company_address }}, {{ $vendor->company_city }}, 
                                {{ $vendor->state->state_name ?? '' }} - {{ $vendor->pincode }}</p>
                        </div>
                        <div class="col-md-6">
                            <h4>Contact Information</h4>
                            <p><strong>Email:</strong> {{ $vendor->vendor_email }}</p>
                            <p><strong>Contact Person:</strong> {{ $vendor->contact_person_name }}</p>
                            <p><strong>Phone:</strong> {{ $vendor->contact_number }}</p>
                            <p><strong>VNR Contact:</strong> {{ $vendor->vnrContactPerson->emp_name ?? '' }}</p>
                            <p><strong>Payment Terms:</strong> {{ $vendor->payment_terms }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h4>Legal Information</h4>
                            <p><strong>Legal Status:</strong> {{ $vendor->legal_status }}</p>
                            <p><strong>PAN:</strong> {{ $vendor->pan_number }}</p>
                            @if($vendor->pan_card_copy_path)
                                <p><a href="{{ Storage::url($vendor->pan_card_copy_path) }}" target="_blank">View PAN Card</a></p>
                            @endif
                            <p><strong>Aadhar:</strong> {{ $vendor->aadhar_number }}</p>
                            @if($vendor->aadhar_card_copy_path)
                                <p><a href="{{ Storage::url($vendor->aadhar_card_copy_path) }}" target="_blank">View Aadhar Card</a></p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h4>Banking Information</h4>
                            <p><strong>Account Holder:</strong> {{ $vendor->bank_account_holder_name }}</p>
                            <p><strong>Account Number:</strong> {{ $vendor->bank_account_number }}</p>
                            <p><strong>IFSC:</strong> {{ $vendor->ifsc_code }}</p>
                            <p><strong>Branch:</strong> {{ $vendor->bank_branch }}</p>
                            @if($vendor->cancelled_cheque_copy_path)
                                <p><a href="{{ Storage::url($vendor->cancelled_cheque_copy_path) }}" target="_blank">View Cancelled Cheque</a></p>
                            @endif
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="{{ route('vendors.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection