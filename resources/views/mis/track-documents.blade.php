@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Track Physical Documents for {{ $application->application_code }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Track Documents</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Physical Document Tracking</h4>
                    <form action="{{ route('approvals.submit-documents', $application) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Agreement</label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="agreement_received"
                                    id="agreement_received" {{ $application->physicalDocuments->first()->agreement_received ?? false ? 'checked' : '' }}>
                                <label class="form-check-label" for="agreement_received">Received</label>
                            </div>
                            <input type="date" class="form-control mt-2" name="agreement_received_date"
                                value="{{ $application->physicalDocuments->first()->agreement_received_date ?? '' }}">
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" name="agreement_verified"
                                    id="agreement_verified" {{ $application->physicalDocuments->first()->agreement_verified ?? false ? 'checked' : '' }}>
                                <label class="form-check-label" for="agreement_verified">Verified</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Security Cheque</label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="security_cheque_received"
                                    id="security_cheque_received" {{ $application->physicalDocuments->first()->security_cheque_received ?? false ? 'checked' : '' }}>
                                <label class="form-check-label" for="security_cheque_received">Received</label>
                            </div>
                            <input type="date" class="form-control mt-2" name="security_cheque_received_date"
                                value="{{ $application->physicalDocuments->first()->security_cheque_received_date ?? '' }}">
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" name="security_cheque_verified"
                                    id="security_cheque_verified" {{ $application->physicalDocuments->first()->security_cheque_verified ?? false ? 'checked' : '' }}>
                                <label class="form-check-label" for="security_cheque_verified">Verified</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Security Deposit</label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="security_deposit_received"
                                    id="security_deposit_received" {{ $application->physicalDocuments->first()->security_deposit_received ?? false ? 'checked' : '' }}>
                                <label class="form-check-label" for="security_deposit_received">Received</label>
                            </div>
                            <input type="date" class="form-control mt-2" name="security_deposit_received_date"
                                value="{{ $application->physicalDocuments->first()->security_deposit_received_date ?? '' }}">
                            <div class="form-check mt-2">
                                <input type="checkbox" class="form-check-input" name="security_deposit_verified"
                                    id="security_deposit_verified" {{ $application->physicalDocuments->first()->security_deposit_verified ?? false ? 'checked' : '' }}>
                                <label class="form-check-label" for="security_deposit_verified">Verified</label>
                            </div>
                            <input type="number" class="form-control mt-2" name="security_deposit_amount"
                                value="{{ $application->physicalDocuments->first()->security_deposit_amount ?? '' }}"
                                placeholder="Deposit Amount" step="0.01">
                        </div>
                        <button type="submit" class="btn btn-success">Update Documents</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection