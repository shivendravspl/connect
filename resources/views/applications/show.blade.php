@extends('layouts.app')

@push('styles')
<style>
    .form-section {
        margin-bottom: 1rem;
        padding: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }

    .table th,
    .table td {
        vertical-align: middle;
        font-size: 0.6rem !important;
        padding: 0.5rem;
    }

    .document-link a {
        color: #007bff;
        text-decoration: none;
        font-size: 0.8rem;
    }

    .document-link a:hover {
        text-decoration: underline;
    }

    .btn-sm {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
    }

    .modal-content {
        font-size: 0.8rem;
    }

    .modal-body iframe {
        width: 100%;
        height: 400px;
        border: none;
    }

    /* Mobile-specific styles */
    @media (max-width: 768px) {
        .container {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        h2 {
            font-size: 1.4rem;
        }

        h5 {
            font-size: 1.1rem;
        }

        .card {
            margin-left: -0.5rem;
            margin-right: -0.5rem;
            border-radius: 0;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .modal-content {
            margin: 0.5rem;
        }

        .modal-body {
            padding: 0.75rem;
        }

        .modal-footer .btn {
            margin-bottom: 0.5rem;
        }

        .modal-body iframe {
            height: 300px;
        }
    }
</style>
@endpush

@section('content')
<div class="container mt-3">

    <!-- Application Status -->
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-cent">
            <h6 class="mb-0">Distributor Application Status: <span class="badge bg-{{ $application->status_badge }}">{{ ucfirst($application->status) }}</span></h6>
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i> Back
            </a>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <tbody>
                        <tr>
                            <td><b>Submitted On:</b> {{ $application->created_at->format('d-M-Y H:i') }}</td>
                            <td><b>Last Updated:</b> {{ $application->updated_at->format('d-M-Y H:i') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Step 1: Basic Details -->
    <div id="basic-details" class="card mb-3">
        <div class="card-header">
            <h6 class="mb-1">Basic Details</h6>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <tbody>
                        <tr>
                            <th>Territory</th>
                            <td>{{ isset($application->territory) ? DB::table('core_territory')->where('id', $application->territory)->value('territory_name') ?? 'N/A' : 'N/A' }}</td>
                            <th>Region</th>
                            <td>{{ isset($application->region) ? DB::table('core_region')->where('id', $application->region)->value('region_name') ?? 'N/A' : 'N/A' }}</td>
                            <th>Zone</th>
                            <td>{{ isset($application->zone) ? DB::table('core_zone')->where('id', $application->zone)->value('zone_name') ?? 'N/A' : 'N/A' }}</td>
                            <th>Business Unit</th>
                            <td>{{ isset($application->business_unit) ? DB::table('core_business_unit')->where('id', $application->business_unit)->value('business_unit_name') ?? 'N/A' : 'N/A' }}</td>
                            <th>Crop Vertical</th>
                            <td>{{ isset($application->crop_vertical) && $application->crop_vertical === '1' ? 'Field Crop' : 'Veg Crop' }}</td>
                        </tr>
                        {{--<tr>
                            <th>State</th>
                            <td>{{ isset($application->state) ? DB::table('core_state')->where('id', $application->state)->value('state_name') ?? 'N/A' : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>District</th>
                            <td>{{ isset($application->district) ? DB::table('core_district')->where('id', $application->district)->value('district_name') ?? 'N/A' : 'N/A' }}</td>
                        </tr>--}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Step 2: Entity Details --}}
    @if($application->entityDetails)
    <div id="entity-details" class="card mb-3">
        <div class="card-header">
            <h6 class="mb-1">
                {{ $entityTypeLabels[$application->entityDetails->entity_type] ?? 'Entity Details' }}
            </h6>
        </div>
        <div class="card-body p-2">
            <div class="row">
                <div class="col-md-6">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <tr>
                                <th class="w-40">Establishment Name</th>
                                <td class="w-60">{{ $application->entityDetails->establishment_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Entity Type</th>
                                <td>{{ $entityTypeLabels[$application->entityDetails->entity_type] ?? $application->entityDetails->entity_type ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Mobile</th>
                                <td>{{ $application->entityDetails->mobile ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $application->entityDetails->email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>PAN Number</th>
                                <td class="d-flex justify-content-between align-items-center">
                                    <div>
                                        @if($application->entityDetails->pan_number)
                                        <span class="fw-bold">{{ $application->entityDetails->pan_number }}</span>
                                        @if($application->entityDetails->pan_verified)
                                        <span class="badge bg-success ms-1">Verified</span>
                                        @endif
                                        @else
                                        N/A
                                        @endif
                                    </div>
                                    <div>
                                        @if($application->entityDetails->pan_path)
                                        <a href="#" class="document-link ms-1" data-bs-toggle="modal" data-bs-target="#panModal">
                                            <i class="ri-eye-line"></i> View
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>GST Applicable</th>
                                <td class="d-flex justify-content-between align-items-center">
                                    {{ $application->entityDetails->gst_applicable ? Str::title($application->entityDetails->gst_applicable) : 'N/A' }}
                                    @if($application->entityDetails->gst_applicable === 'yes' && $application->entityDetails->gst_number)
                                    <br><small class="text-muted">
                                        <strong>GST No:</strong> {{ $application->entityDetails->gst_number }}
                                        @if($application->entityDetails->gst_verified)
                                        <span class="badge bg-success ms-1">Verified</span>
                                        @endif
                                        @if($application->entityDetails->gst_path)
                                        <a href="#" class="document-link ms-1" data-bs-toggle="modal" data-bs-target="#gstModal">
                                            <i class="ri-eye-line"></i> View
                                        </a>
                                        @endif
                                    </small>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <tr>
                                <th class="w-40">Seed License No</th>
                                <td class="w-60 d-flex justify-content-between align-items-center">
                                    <div>
                                        @if($application->entityDetails->seed_license)
                                        <span class="fw-bold">{{ $application->entityDetails->seed_license }}</span>
                                        @if($application->entityDetails->seed_license_verified)
                                        <span class="badge bg-success ms-1">Verified</span>
                                        @endif
                                    </div>
                                    <div>
                                        @if($application->entityDetails->seed_license_path)
                                        <a href="#" class="document-link ms-1" data-bs-toggle="modal" data-bs-target="#seedLicenseModal">
                                            <i class="ri-eye-line"></i> View
                                        </a>
                                        @endif
                                        @else
                                        N/A
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>TAN Number</th>
                                <td>{{ $application->entityDetails->tan_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Has Authorized Persons</th>
                                <td>
                                    {{ $application->entityDetails->has_authorized_persons === 'yes' ? 'Yes' : 'No' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Business Address</th>
                                <td>
                                    @php
                                    $addressParts = [];
                                    if ($application->entityDetails->house_no) $addressParts[] = $application->entityDetails->house_no;
                                    if ($application->entityDetails->landmark) $addressParts[] = $application->entityDetails->landmark;
                                    if ($application->entityDetails->city) $addressParts[] = $application->entityDetails->city;
                                    if ($application->entityDetails->district_id && isset($districts[$application->entityDetails->district_id])) {
                                    $addressParts[] = $districts[$application->entityDetails->district_id]->district_name;
                                    }
                                    if ($application->entityDetails->state_id && isset($states[$application->entityDetails->state_id])) {
                                    $addressParts[] = $states[$application->entityDetails->state_id]->state_name;
                                    }
                                    if ($application->entityDetails->pincode) $addressParts[] = $application->entityDetails->pincode;
                                    @endphp
                                    {{ !empty($addressParts) ? implode(', ', $addressParts) : 'N/A' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- **UPDATED: Bank Details Section - Direct from entityDetails ** --}}
            @if($application->entityDetails->bank_name || $application->bankDetail)
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-2">Bank Account Details</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <tr>
                            <th class="w-25">Bank Name</th>
                            <td class="w-25">{{ $application->entityDetails->bank_name ?? $application->bankDetail->bank_name ?? 'N/A' }}</td>
                            <th>Account Holder</th>
                            <td class="w-25">{{ $application->entityDetails->account_holder_name ?? $application->bankDetail->account_holder ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Account Number</th>
                            <td>{{ $application->entityDetails->account_number ?? $application->bankDetail->account_number ?? 'N/A' }}</td>
                            <th>IFSC Code</th>
                            <td>{{ $application->entityDetails->ifsc_code ?? $application->bankDetail->ifsc_code ?? 'N/A' }}</td>
                        </tr>
                        @if($application->entityDetails->bank_document_path)
                        <tr>
                            <th>Bank Document</th>
                            <td colspan="3">
                                <span class="badge bg-success">Uploaded</span>
                                <a href="#" class="document-link ms-2" data-bs-toggle="modal" data-bs-target="#bankDocModal">
                                    <i class="ri-eye-line"></i> View Document
                                </a>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>

            </div>
            @endif


            <!-- Step 9: Documents -->
            <div id="documents" class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-1">Supporting Documents</h6>
                </div>
                <div class="card-body p-2">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <tbody>
                                @php
                                $entityType = $application->entityDetails->entity_type ?? null;

                                // Define document labels based on entity type
                                $entityProofLabel = 'Entity Proof Document';
                                $ownershipInfoLabel = 'Ownership Information';
                                $bankStatementLabel = 'Bank Statement (6 Months)';
                                $itrAcknowledgementLabel = 'Income Tax Return Acknowledgement';
                                $balanceSheetLabel = 'Balancesheet of Latest FY';

                                // Set labels based on entity type
                                if ($entityType) {
                                switch ($entityType) {
                                case 'partnership':
                                $entityProofLabel = 'Partnership Agreement';
                                $ownershipInfoLabel = 'Certified List of Partners';
                                break;
                                case 'llp':
                                $entityProofLabel = 'Certificate of Incorporation';
                                $ownershipInfoLabel = 'Certified List of Partners';
                                break;
                                case 'private_company':
                                case 'public_company':
                                $entityProofLabel = 'Certificate of Incorporation';
                                $ownershipInfoLabel = 'Certified List of Directors';
                                break;
                                case 'cooperative_society':
                                $entityProofLabel = 'Certificate of Registration';
                                $ownershipInfoLabel = 'Certified List of Directors';
                                break;
                                case 'trust':
                                $entityProofLabel = 'Certificate of Registration';
                                $ownershipInfoLabel = 'Certified List of Trustees';
                                break;
                                case 'sole_proprietorship':
                                $ownershipInfoLabel = 'Aadhar Card of Proprietor';
                                break;
                                }

                                if ($entityType === 'sole_proprietorship') {
                                $itrAcknowledgementLabel = 'Income Tax Return Acknowledgement';
                                $balanceSheetLabel = 'Balancesheet of Latest FY';
                                } else {
                                $itrAcknowledgementLabel = 'Income Tax Return Acknowledgement (Entity)';
                                $balanceSheetLabel = 'Balancesheet of Latest FY (Entity)';
                                }
                                }
                                @endphp

                                <!-- Entity Proof Document -->
                                @if(in_array($entityType, ['partnership', 'llp', 'private_company', 'public_company', 'cooperative_society', 'trust']) && $application->entityDetails->entity_proof_path)
                                <tr>
                                    <th class="w-30">{{ $entityProofLabel }}</th>
                                    <td class="w-70">
                                        <span class="badge bg-success">Uploaded</span>
                                        <a href="#" class="document-link ms-2" data-bs-toggle="modal" data-bs-target="#entityProofModal">
                                            <i class="ri-eye-line"></i> View Document
                                        </a>
                                    </td>
                                </tr>
                                @endif

                                <!-- Ownership Information -->
                                @if(in_array($entityType, ['sole_proprietorship', 'llp', 'private_company', 'public_company', 'cooperative_society', 'trust']))
                                @if(($entityType === 'sole_proprietorship' && $application->proprietorDetails && $application->proprietorDetails->ownership_info_path) ||
                                ($entityType !== 'sole_proprietorship' && $application->entityDetails->ownership_info_path))
                                <tr>
                                    <th class="w-30">{{ $ownershipInfoLabel }}</th>
                                    <td class="w-70">
                                        <span class="badge bg-success">Uploaded</span>
                                        @if($entityType === 'sole_proprietorship' && $application->proprietorDetails && $application->proprietorDetails->ownership_info_path)
                                        <a href="#" class="document-link ms-2" data-bs-toggle="modal" data-bs-target="#proprietorAadharModal">
                                            <i class="ri-eye-line"></i> View Aadhar
                                        </a>
                                        @elseif($application->entityDetails->ownership_info_path)
                                        <a href="#" class="document-link ms-2" data-bs-toggle="modal" data-bs-target="#ownershipInfoModal">
                                            <i class="ri-eye-line"></i> View Document
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @endif

                                <!-- Bank Statement (Always shown) -->
                                @if($application->entityDetails->bank_statement_path)
                                <tr>
                                    <th class="w-30">{{ $bankStatementLabel }}</th>
                                    <td class="w-70">
                                        <span class="badge bg-success">Uploaded</span>
                                        <a href="#" class="document-link ms-2" data-bs-toggle="modal" data-bs-target="#bankStatementModal">
                                            <i class="ri-eye-line"></i> View Document
                                        </a>
                                    </td>
                                </tr>
                                @endif

                                <!-- Credit Worthiness Documents (Only shown when entity type is set) -->
                                @if($entityType)
                                <!-- ITR Acknowledgement -->
                                @if($application->entityDetails->itr_acknowledgement_path)
                                <tr>
                                    <th class="w-30">{{ $itrAcknowledgementLabel }}</th>
                                    <td class="w-70">
                                        <span class="badge bg-success">Uploaded</span>
                                        <a href="#" class="document-link ms-2" data-bs-toggle="modal" data-bs-target="#itrAcknowledgementModal">
                                            <i class="ri-eye-line"></i> View Document
                                        </a>
                                    </td>
                                </tr>
                                @endif

                                <!-- Balance Sheet (Optional) -->
                                @if($application->entityDetails->balance_sheet_path)
                                <tr>
                                    <th class="w-30">{{ $balanceSheetLabel }}</th>
                                    <td class="w-70">
                                        <span class="badge bg-success">Uploaded</span>
                                        <a href="#" class="document-link ms-2" data-bs-toggle="modal" data-bs-target="#balanceSheetModal">
                                            <i class="ri-eye-line"></i> View Document
                                        </a>
                                    </td>
                                </tr>
                                @endif
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


            {{-- **UPDATED: Entity-Specific Details ** --}}
            @php
            $entityType = $application->entityDetails->entity_type;
            @endphp

            @switch($entityType)
            @case('individual_person')
            @if($application->individualDetails)
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="mb-2">Individual Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <tr>
                                <th class="w-30">Full Name</th>
                                <td class="w-30">{{ $application->individualDetails->name ?? 'N/A' }}</td>
                                <th>Father's/Spouse's Name</th>
                                <td class="w-40">{{ $application->individualDetails->father_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Date of Birth</th>
                                <td>{{ $application->individualDetails->dob ? \Carbon\Carbon::parse($application->individualDetails->dob)->format('d-m-Y') : 'N/A' }}</td>
                                <th>Age</th>
                                <td>{{ $application->individualDetails->age ?? 'N/A' }} years</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            @break

            @case('sole_proprietorship')
            @if($application->proprietorDetails)
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="mb-2">Proprietor Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <tr>
                                <th class="w-30">Proprietor Name</th>
                                <td class="w-30">{{ $application->proprietorDetails->name ?? 'N/A' }}</td>
                                <th>Father's/Spouse's Name</th>
                                <td class="w-40">{{ $application->proprietorDetails->father_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Date of Birth</th>
                                <td>{{ $application->proprietorDetails->dob ? \Carbon\Carbon::parse($application->proprietorDetails->dob)->format('d-m-Y') : 'N/A' }}</td>
                                <th>Age</th>
                                <td>{{ $application->proprietorDetails->age ?? 'N/A' }} years</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            @break

            @case('partnership')
            @if($application->partnershipPartners->isNotEmpty())
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="mb-2">Partners Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>PAN</th>
                                    <th>Contact</th>
                                    <th>Aadhar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($application->partnershipPartners as $index => $partner)
                                <tr>
                                    <td>{{ $partner->name ?? 'N/A' }}</td>
                                    <td>{{ $partner->pan ?? 'N/A' }}</td>
                                    <td>{{ $partner->contact ?? 'N/A' }}</td>
                                    <td> @if($partner->aadhar_path)
                                        <button type="button"
                                            class="btn btn-sm btn-outline-success"
                                            data-bs-toggle="modal"
                                            data-bs-target="#partnerAadharModal{{ $index }}">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            @foreach($application->partnershipPartners as $index => $person)
            @if($person->aadhar_path)
            <div class="modal fade" id="partnerAadharModal{{ $index }}" tabindex="-1"
                aria-labelledby="partnerAadharModalLabel{{ $index }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title" id="partnerAadharModalLabel{{ $index }}">
                                Aadhar Document - {{ $person->name }}
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-2">
                            <iframe src="{{ Storage::disk('s3')->url('Connect/Distributor/partner_aadhar/' . $person->aadhar_path) }}"
                                title="Aadhar Document"
                                style="width: 100%; height: 500px; border: none;"></iframe>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @endforeach

            @if($application->partnershipSignatories->isNotEmpty())
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="mb-2">Signatory Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Contact</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($application->partnershipSignatories as $signatory)
                                <tr>
                                    <td>{{ $signatory->name ?? 'N/A' }}</td>
                                    <td>{{ $signatory->designation ?? 'N/A' }}</td>
                                    <td>{{ $signatory->contact ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            @break

            @case('llp')
            @if($application->llpDetails)
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="mb-2">LLP Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <tr>
                                <th class="w-30">LLPIN Number</th>
                                <td class="w-70">{{ $application->llpDetails->llpin_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Date of Incorporation</th>
                                <td>{{ $application->llpDetails->incorporation_date ? \Carbon\Carbon::parse($application->llpDetails->incorporation_date)->format('d-m-Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            @if($application->llpPartners->isNotEmpty())
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="mb-2">Designated Partners</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>DPIN Number</th>
                                    <th>Contact</th>
                                    <th>Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($application->llpPartners as $partner)
                                <tr>
                                    <td>{{ $partner->name ?? 'N/A' }}</td>
                                    <td>{{ $partner->dpin_number ?? 'N/A' }}</td>
                                    <td>{{ $partner->contact ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($partner->address ?? 'N/A', 50) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            @break

            @case('private_company')
            @case('public_company')
            @if($application->companyDetails)
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="mb-2">Company Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <tr>
                                <th class="w-30">CIN Number</th>
                                <td class="w-70">{{ $application->companyDetails->cin_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Date of Incorporation</th>
                                <td>{{ $application->companyDetails->incorporation_date ? \Carbon\Carbon::parse($application->companyDetails->incorporation_date)->format('d-m-Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            @if($application->directors->isNotEmpty())
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="mb-2">Directors Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>DIN Number</th>
                                    <th>Contact</th>
                                    <th>Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($application->directors as $director)
                                <tr>
                                    <td>{{ $director->name ?? 'N/A' }}</td>
                                    <td>{{ $director->din_number ?? 'N/A' }}</td>
                                    <td>{{ $director->contact ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($director->address ?? 'N/A', 50) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            @break

            @case('cooperative_society')
            @if($application->cooperativeDetails)
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="mb-2">Cooperative Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <tr>
                                <th class="w-30">Registration Number</th>
                                <td class="w-70">{{ $application->cooperativeDetails->reg_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Registration Date</th>
                                <td>{{ $application->cooperativeDetails->reg_date ? \Carbon\Carbon::parse($application->cooperativeDetails->reg_date)->format('d-m-Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            @if($application->committeeMembers->isNotEmpty())
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="mb-2">Committee Members</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Contact</th>
                                    <th>Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($application->committeeMembers as $member)
                                <tr>
                                    <td>{{ $member->name ?? 'N/A' }}</td>
                                    <td>{{ $member->designation ?? 'N/A' }}</td>
                                    <td>{{ $member->contact ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($member->address ?? 'N/A', 50) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            @break

            @case('trust')
            @if($application->trustDetails)
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="mb-2">Trust Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <tr>
                                <th class="w-30">Registration Number</th>
                                <td class="w-70">{{ $application->trustDetails->reg_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Registration Date</th>
                                <td>{{ $application->trustDetails->reg_date ? \Carbon\Carbon::parse($application->trustDetails->reg_date)->format('d-m-Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            @if($application->trustees->isNotEmpty())
            <div class="row mt-3">
                <div class="col-12">
                    <h6 class="mb-2">Trustees Details</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Contact</th>
                                    <th>Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($application->trustees as $trustee)
                                <tr>
                                    <td>{{ $trustee->name ?? 'N/A' }}</td>
                                    <td>{{ $trustee->designation ?? 'N/A' }}</td>
                                    <td>{{ $trustee->contact ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($trustee->address ?? 'N/A', 50) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            @break

            @default
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        Ownership details for "{{ $entityTypeLabels[$entityType] ?? $entityType }}" not available or not configured.
                    </div>
                </div>
            </div>
            @endswitch

            {{-- **UPDATED: Authorized Persons Section ** --}}
            @if($application->entityDetails->has_authorized_persons === 'yes')
            <div class="row mt-3">
                <div class="col-12">
                    <div class="card authorized-person-card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="ri-user-star-line me-2"></i>Authorized Persons
                                <span class="badge bg-primary ms-1">{{ $application->authorizedPersons->count() }}</span>
                            </h6>
                        </div>
                        <div class="card-body p-2">
                            @if($application->authorizedPersons->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Contact</th>
                                            <th>Email</th>
                                            <th>Relation</th>
                                            <th>Aadhar</th>
                                            <th>Documents</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($application->authorizedPersons as $index => $person)
                                        <tr>
                                            <td><strong>{{ $person->name ?? 'N/A' }}</strong></td>
                                            <td>{{ $person->contact ?? 'N/A' }}</td>
                                            <td>{{ $person->email ?? 'N/A' }}</td>
                                            <td>{{ $person->relation ?? 'N/A' }}</td>
                                            <td>{{ $person->aadhar_number ?? 'N/A' }}</td>
                                            <td>
                                                @if($person->letter_path || $person->aadhar_path)
                                                <div class="btn-group btn-group-sm" role="group">
                                                    @if($person->letter_path)
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#authLetterModal{{ $index }}">
                                                        <i class="ri-file-text-line"></i> Letter
                                                    </button>
                                                    @endif
                                                    @if($person->aadhar_path)
                                                    <button type="button"
                                                        class="btn  btn-sm  btn-outline-success"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#authAadharModal{{ $index }}">
                                                        <i class="ri-id-card-line"></i> Aadhar
                                                    </button>
                                                    @endif
                                                </div>
                                                @else
                                                <span class="text-muted">No documents</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="alert alert-warning">
                                <i class="ri-alert-line me-2"></i>
                                No authorized persons details provided.
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Authorized Persons Document Modals --}}
            @foreach($application->authorizedPersons as $index => $person)
            @if($person->letter_path)
            <div class="modal fade" id="authLetterModal{{ $index }}" tabindex="-1" aria-labelledby="authLetterModalLabel{{ $index }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title" id="authLetterModalLabel{{ $index }}">Authorization Letter - {{ $person->name }}</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-2">
                            <iframe src="{{ Storage::disk('s3')->url('Connect/Distributor/authorized_persons/' . $person->letter_path) }}"
                                title="Authorization Letter"
                                style="width: 100%; height: 500px; border: none;"></iframe>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($person->aadhar_path)
            <div class="modal fade" id="authAadharModal{{ $index }}" tabindex="-1" aria-labelledby="authAadharModalLabel{{ $index }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title" id="authAadharModalLabel{{ $index }}">Aadhar Document - {{ $person->name }}</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-2">
                            <iframe src="{{ Storage::disk('s3')->url('Connect/Distributor/authorized_persons/' . $person->aadhar_path) }}"
                                title="Aadhar Document"
                                style="width: 100%; height: 500px; border: none;"></iframe>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
            @endif
        </div>
    </div>

    @endif

    <!-- Step 3: Distribution Details -->
    @if(isset($application->distributionDetail))
    <div id="distribution-details" class="card mb-3">
        <div class="card-header">
            <h6 class="mb-1">Distribution Details</h6>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <tbody>
                        @php
                        $areaCovered = $application->distributionDetail->area_covered ?? [];
                        if (is_string($areaCovered)) {
                        $decoded = json_decode($areaCovered, true);
                        if (is_array($decoded)) {
                        $areaCovered = count($decoded) === 1 && str_contains($decoded[0], ',')
                        ? array_map('trim', explode(',', $decoded[0]))
                        : $decoded;
                        } else {
                        $areaCovered = array_map('trim', explode(',', $areaCovered));
                        }
                        } elseif (!is_array($areaCovered)) {
                        $areaCovered = [];
                        }
                        @endphp
                        <tr>
                            <th colspan="2">Area Covered</th>
                            <td colspan="2">{{ !empty($areaCovered) ? implode(', ', $areaCovered) : 'N/A' }}</td>

                            <th colspan="2">Appointment Type</th>
                            <td colspan="2">{{ $application->distributionDetail->appointment_type ?? 'N/A' }}</td>
                        </tr>
                        @if($application->distributionDetail && $application->distributionDetail->appointment_type === 'replacement')
                        <tr>
                            <th>Reason for Replacement</th>
                            <td>{{ $application->distributionDetail->replacement_reason ?? 'N/A' }}</td>

                            <th>Commitment to Recover Outstanding</th>
                            <td>{{ $application->distributionDetail->outstanding_recovery ?? 'N/A' }}</td>

                            <th>Name of Previous Firm</th>
                            <td>{{ $application->distributionDetail->previous_firm_name ?? 'N/A' }}</td>

                            <th>Code of Previous Firm</th>
                            <td>{{ $application->distributionDetail->previous_firm_code ?? 'N/A' }}</td>
                        </tr>
                        @elseif($application->distributionDetail && $application->distributionDetail->appointment_type === 'new_area')
                        <tr>
                            <th>Earlier Distributor</th>
                            <td>{{ $application->distributionDetail->earlier_distributor ?? 'N/A' }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Step 4: Business Plan -->
    @if(isset($application->businessPlans) && !$application->businessPlans->isEmpty())
    <div id="business-plan" class="card mb-3">
        <div class="card-header">
            <h6 class="mb-1">Business Plan (Next Two Years)</h6>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Crop</th>
                            <th colspan="2">{{ $application->businessPlans->first()->current_financial_year ?? 'FY 1' }}</th>
                            <th colspan="2">{{ $application->businessPlans->first()->next_financial_year ?? 'FY 2' }}</th>
                        </tr>
                        <tr>
                            <th></th>
                            <!-- Current FY Sub-headers -->
                            <th class="form-label fw-normal sub-header" style="width: 15%;">MT *</th>
                            <th class="form-label fw-normal sub-header" style="width: 15%;">Amount *</th>
                            <!-- Next FY Sub-headers -->
                            <th class="form-label fw-normal sub-header" style="width: 15%;">MT *</th>
                            <th class="form-label fw-normal sub-header" style="width: 15%;">Amount *</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($application->businessPlans as $plan)
                        <tr>
                            <td>{{ $plan->crop ?? 'N/A' }}</td>
                            <td>{{ $plan->current_financial_year_mt ?? 'N/A' }}</td>
                            <td>{{ $plan->current_financial_year_amount ?? 'N/A' }}</td>
                            <td>{{ $plan->next_financial_year_mt ?? 'N/A' }}</td>
                            <td>{{ $plan->next_financial_year_amount ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif


    <!-- Step 5: Financial & Operational Information -->
    @if(isset($application->financialInfo))
    <div id="financial-info" class="card mb-3">
        <div class="card-header">
            <h6 class="mb-1">Financial & Operational Information</h6>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <tbody>
                        <tr>
                            <th>Net Worth (Previous FY)</th>
                            <td>{{ $application->financialInfo->net_worth ?? 'N/A' }}</td>

                            <th>Shop Ownership</th>
                            <td>{{ $application->financialInfo->shop_ownership ?? 'N/A' }}</td>

                            <th>Godown Area & Ownership</th>
                            <td>{{ $application->financialInfo->godown_area ?? 'N/A' }}</td>

                            <th>Years in Business</th>
                            <td>{{ $application->financialInfo->years_in_business ?? 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <h6 class="mb-2 mt-1">Annual Turnover</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Financial Year</th>
                            <th>Net Turnover (₹)</th>
                        </tr>
                    </thead>
                    @php
                    // Get the annual turnover JSON from the financialInfo relation
                    $turnover = optional($application->financialInfo)->annual_turnover;

                    // Decode it into an array if it's a string, or use empty array if null
                    $turnover = is_string($turnover) ? json_decode($turnover, true) : ($turnover ?? []);

                    // Get all the years present in the turnover
                    $turnoverYears = array_keys($turnover);
                    @endphp

                    <tbody>
                        @foreach($turnoverYears as $year)
                        <tr>
                            <td>FY {{ $year }}</td>
                            <td>{{ $turnover[$year] }}</td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
    </div>
    @endif  

    <!-- Step 7: Bank Details -->
    @if(isset($application->bankDetail))
    <div id="bank-details" class="card mb-3">
        <div class="card-header">
            <h6 class="mb-1">Bank Details</h6>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <tbody>
                        <tr>
                            <th>Financial Status</th>
                            <td>{{ $application->bankDetail->financial_status ?? 'N/A' }}</td>

                            <th>No. of Retailers Dealt With</th>
                            <td>{{ $application->bankDetail->retailer_count ?? 'N/A' }}</td>

                            <th>Bank Name</th>
                            <td>{{ $application->bankDetail->bank_name ?? 'N/A' }}</td>

                            <th>Account Holder Name</th>
                            <td>{{ $application->bankDetail->account_holder ?? 'N/A' }}</td>

                            <th>Account Number</th>
                            <td>{{ $application->bankDetail->account_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>IFSC Code</th>
                            <td>{{ $application->bankDetail->ifsc_code ?? 'N/A' }}</td>

                            <th>Account Type</th>
                            <td>{{ $application->bankDetail->account_type ?? 'N/A' }}</td>

                            <th>Relationship Duration (Years)</th>
                            <td>{{ $application->bankDetail->relationship_duration ?? 'N/A' }}</td>

                            <th>OD Limit (if any)</th>
                            <td>{{ $application->bankDetail->od_limit ?? 'N/A' }}</td>

                            <th>OD Security</th>
                            <td>{{ $application->bankDetail->od_security ?? 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Step 8: Declarations -->
    @if(isset($application->declarations) && !$application->declarations->isEmpty())
    <div id="declarations" class="card mb-3">
        <div class="card-header">
            <h6 class="mb-1">Declarations</h6>
        </div>
        <div class="card-body p-2">
            @php
            $questions = [
            'is_other_distributor' => [
            'label' => 'a. Whether the Distributor is an Agent/Distributor of any other Company?',
            'details_field' => 'other_distributor_details',
            ],
            'has_sister_concern' => [
            'label' => 'b. Whether the Distributor has any sister concern or affiliated entity other than the one applying for this distributorship?',
            'details_field' => 'sister_concern_details',
            ],
            'has_question_c' => [
            'label' => 'c. Whether the Distributor is acting as an Agent/Distributor for any other entities in the distribution of similar crops?',
            'details_field' => 'question_c_details',
            ],
            'has_question_d' => [
            'label' => 'd. Whether the Distributor is a partner, relative, or otherwise associated with any entity engaged in the business of agro inputs?',
            'details_field' => 'question_d_details',
            ],
            'has_question_e' => [
            'label' => 'e. Whether the Distributor has previously acted as an Agent/Distributor of VNR Seeds and is again applying for a Distributorship?',
            'details_field' => 'question_e_details',
            ],
            'has_disputed_dues' => [
            'label' => 'f. Whether any disputed dues are payable by the Distributor to the other Company/Bank/Financial Institution?',
            'details_fields' => [
            'disputed_amount' => 'Disputed Amount',
            'dispute_nature' => 'Nature of Dispute',
            'dispute_year' => 'Year of Dispute',
            'dispute_status' => 'Present Position',
            'dispute_reason' => 'Reason for Default',
            ],
            ],
            'has_question_g' => [
            'label' => 'g. Whether the Distributor has ceased to be Agent/Distributor of any other company in the last twelve months?',
            'details_field' => 'question_g_details',
            ],
            'has_question_h' => [
            'label' => 'h. Whether the Distributor`s relative is connected in any way with VNR Seeds and any other Seed Company?',
            'details_field' => 'question_h_details',
            ],
            'has_question_i' => [
            'label' => 'i. Whether the Distributor is involved in any other capacity with the Company apart from this application?',
            'details_field' => 'question_i_details',
            ],
            'has_question_j' => [
            'label' => 'j. Whether the Distributor has been referred by any Distributors or other parties associated with the Company?',
            'details_fields' => [
            'referrer_1' => 'Referrer I',
            'referrer_2' => 'Referrer II',
            'referrer_3' => 'Referrer III',
            'referrer_4' => 'Referrer IV',
            ],
            ],
            'has_question_k' => [
            'label' => 'k. Whether the Distributor is currently marketing or selling products under its own brand name?',
            'details_field' => 'question_k_details',
            ],
            'has_question_l' => [
            'label' => 'l. Whether the Distributor has been employed in the agro-input industry at any point during the past 5 years?',
            'details_field' => 'question_l_details',
            ],
            ];
            @endphp
            @foreach($questions as $questionKey => $config)
            @php
            $declaration = $application->declarations->where('question_key', $questionKey)->first();
            $hasIssue = $declaration ? $declaration->has_issue : false;
            $details = [];
            if ($declaration && $declaration->details) {
            $details = is_array($declaration->details)
            ? $declaration->details
            : json_decode($declaration->details, true);
            }
            @endphp
            <div class="mb-2">
                <h6 class="mb-1">{{ $config['label'] }}</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tbody>
                            <tr>
                                <th>Answer</th>
                                <td>{{ $hasIssue ? 'Yes' : 'No' }}</td>
                            </tr>
                            @if($hasIssue && !empty($details))
                            @if(isset($config['details_field']))
                            <tr>
                                <th>Details</th>
                                <td>{{ $details[$config['details_field']] ?? 'N/A' }}</td>
                            </tr>
                            @elseif(isset($config['details_fields']))
                            @foreach($config['details_fields'] as $field => $label)
                            <tr>
                                <th>{{ $label }}</th>
                                <td>{{ $details[$field] ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                            @endif
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
            <div class="mt-3">
                <h6 class="mb-1">Declaration</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tbody>
                            @php
                            $truthful = $application->declarations->where('question_key', 'declaration_truthful')->first();
                            @endphp
                            <tr>
                                <th>I hereby solemnly affirm and declare that the information furnished in this form is true, correct, and complete to the best of my knowledge and belief</th>
                                <td>{{ $truthful && $truthful->has_issue ? 'Affirmed' : 'Not Affirmed' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

  {{-- ... previous code remains the same ... --}}

<!-- Audit Trail -->
@if(isset($createdBy) || $application->approvalLogs->isNotEmpty() || $application->distributor_code || $application->date_of_appointment)
<div id="audit-trail" class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Audit Trail</h6>
    </div>
    <div class="card-body p-2">
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 20%">Action</th>
                        <th style="width: 20%">Name</th>
                        <th style="width: 15%">Role</th>
                        <th style="width: 15%">Date</th>
                        <th style="width: 30%">Remarks/Details</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Filled By --}}
                    @if(isset($createdBy))
                    <tr>
                        <td><strong>Application Created</strong></td>
                        <td>{{ $createdBy->emp_name ?? 'N/A' }}</td>
                        <td>{{ $createdBy->emp_designation ?? 'N/A' }}</td>
                        <td>{{ $application->created_at->format('d-M-Y H:i') }}</td>
                        <td>
                            <span class="badge bg-primary">Submitted</span>
                        </td>
                    </tr>
                    @endif

                    {{-- Approval Logs --}}
                    @if($application->approvalLogs->isNotEmpty())
                        @foreach($application->approvalLogs->sortBy('created_at') as $log)
                        <tr>
                            <td>
                                <strong>
                                    @switch($log->action)
                                        @case('approved')
                                            Approved
                                            @break
                                        @case('reverted')
                                            Reverted
                                            @break
                                        @case('rejected')
                                            Rejected
                                            @break
                                        @case('hold')
                                            Put on Hold
                                            @break
                                        @case('documents_verified')
                                            Documents Verified
                                            @break
                                        @case('distributor_confirmed')
                                            Distributor Confirmed
                                            @break
                                        @case('security_cheque_updated')
                                            Security Cheque Updated
                                            @break
                                        @default
                                            {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                    @endswitch
                                </strong>
                            </td>
                            <td>{{ $log->user->name ?? 'N/A' }}</td>
                            <td>{{ $log->role ?? 'N/A' }}</td>
                            <td>{{ $log->created_at->format('d-M-Y H:i') }}</td>
                            <td>
                                @if($log->remarks)
                                    <div class="small">{{ $log->remarks }}</div>
                                @endif
                                @if($log->follow_up_date)
                                    <div class="small text-muted">
                                        <strong>Follow-up:</strong> {{ \Carbon\Carbon::parse($log->follow_up_date)->format('d-M-Y') }}
                                    </div>
                                @endif
                                <span class="badge bg-{{ 
                                    $log->action === 'approved' ? 'success' : 
                                    ($log->action === 'reverted' ? 'warning' : 
                                    ($log->action === 'rejected' ? 'danger' : 
                                    ($log->action === 'hold' ? 'secondary' : 
                                    ($log->action === 'documents_verified' ? 'info' : 
                                    ($log->action === 'distributor_confirmed' ? 'success' : 
                                    ($log->action === 'security_cheque_updated' ? 'primary' : 'info')))))) 
                                }}">
                                    @switch($log->action)
                                        @case('approved')
                                            Approved
                                            @break
                                        @case('reverted')
                                            Reverted
                                            @break
                                        @case('rejected')
                                            Rejected
                                            @break
                                        @case('hold')
                                            On Hold
                                            @break
                                        @case('documents_verified')
                                            Documents Verified
                                            @break
                                        @case('distributor_confirmed')
                                            Distributor Confirmed
                                            @break
                                        @case('security_cheque_updated')
                                            Cheque Updated
                                            @break
                                        @default
                                            {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                    @endswitch
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    @endif

                    {{-- Distributor Onboarding Information --}}
                    @if($application->distributor_code || $application->date_of_appointment)
                    <tr class="table-success">
                        <td><strong>Distributor Onboarded</strong></td>
                        <td colspan="2">
                            @if($application->distributor_code)
                                <strong>Focus Code:</strong> {{ $application->distributor_code }}
                            @else
                                Distributor Created
                            @endif
                        </td>
                        <td>
                            @if($application->date_of_appointment)
                                {{ \Carbon\Carbon::parse($application->date_of_appointment)->format('d-M-Y') }}
                            @else
                                {{ $application->created_at->format('d-M-Y') }}
                            @endif
                        </td>
                        <td>
                            @if($application->date_of_appointment)
                                <div class="small">
                                    <strong>Date of Appointment:</strong> {{ \Carbon\Carbon::parse($application->date_of_appointment)->format('d-M-Y') }}
                                </div>
                            @endif
                            @if($application->distributor_code)
                                <div class="small">
                                    <strong>Focus Code:</strong> {{ $application->distributor_code }}
                                </div>
                            @endif
                            <span class="badge bg-success">Completed</span>
                        </td>
                    </tr>
                    @endif

                    {{-- If no audit trail data exists --}}
                    @if(!isset($createdBy) && $application->approvalLogs->isEmpty() && !$application->distributor_code && !$application->date_of_appointment)
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            <i class="ri-information-line me-1"></i>
                            No audit trail data available
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Distributor Information Summary --}}
        @if($application->distributor_code || $application->date_of_appointment)
        <div class="mt-3 p-2 border rounded bg-light">
            <div class="row">
                @if($application->distributor_code)
                <div class="col-md-6">
                    <strong>Focus Code:</strong> 
                    <span class="badge bg-success fs-6">{{ $application->distributor_code }}</span>
                </div>
                @endif
                @if($application->date_of_appointment)
                <div class="col-md-6">
                    <strong>Date of Appointment:</strong> 
                    <span class="fw-bold">
                        {{ \Carbon\Carbon::parse($application->date_of_appointment)->format('d-M-Y') }}
                    </span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endif

{{-- ... rest of the code remains the same ... --}}

    <!-- Approval Logs -->
    {{--@if(isset($application->approvalLogs) && !$application->approvalLogs->isEmpty())
    <div id="approval-logs" class="card mb-3">
        <div class="card-header">
            <h6 class="mb-1">Approval Logs</h6>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Remarks</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($application->approvalLogs as $log)
                        <tr>
                            <td>{{ $log->user->name ?? 'N/A' }}</td>
    <td>{{ $log->action ?? 'N/A' }}</td>
    <td>{{ $log->remarks ?? 'N/A' }}</td>
    <td>{{ $log->created_at->format('d-M-Y H:i') }}</td>
    </tr>
    @endforeach
    </tbody>
    </table>
</div>
</div>
</div>
@endif--}}

<!-- Take Action -->
@if(auth()->user()->emp_id === $application->current_approver_id && !auth()->user()->hasRole('Mis User'))
{{--<div class="card mb-3">
        <div class="card-header p-2">
            <h6 class="mb-0">Take Action</h6>
        </div>
        <div class="card-body p-2">
            <form action="{{ route('approvals.approve', $application) }}" method="POST" class="d-inline">
@csrf
<div class="mb-2">
    <label for="approveRemarks" class="form-label">Remarks (Optional)</label>
    <textarea name="remarks" id="approveRemarks" class="form-control" rows="2" style="font-size: 0.8rem;"></textarea>
</div>
<button type="submit" class="btn btn-success btn-sm">Approve</button>
</form>
<button type="button" class="btn btn-warning btn-sm ms-1" data-bs-toggle="modal" data-bs-target="#revertModal">Revert</button>
<button type="button" class="btn btn-secondary btn-sm ms-1" data-bs-toggle="modal" data-bs-target="#holdModal">Hold</button>
<button type="button" class="btn btn-danger btn-sm ms-1" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
</div>
</div>

<!-- Revert Modal -->
<div class="modal fade" id="revertModal" tabindex="-1" aria-labelledby="revertModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('approvals.revert', $application) }}" method="POST">
                @csrf
                <div class="modal-header p-2">
                    <h6 class="modal-title" id="revertModalLabel">Revert Application</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2">
                    <div class=" Geschlecht mb-2">
                        <label for="revertRemarks" class="form-label">Reason for Revert *</label>
                        <textarea name="remarks" id="revertRemarks" class="form-control" rows="3" required style="font-size: 0.8rem;"></textarea>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm">Confirm Revert</button>
                    </ivaldiv>
            </form>
        </div>
    </div>
</div>

<!-- Hold Modal -->
<div class="modal fade" id="holdModal" tabindex="-1" aria-labelledby="holdModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('approvals.hold', $application) }}" method="POST">
                @csrf
                <div class="modal-header p-2">
                    <h6 class="modal-title" id="holdModalLabel">Put Application On Hold</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2">
                    <div class="mb-2">
                        <label for="holdRemarks" class="form-label">Reason for Hold *</label>
                        <textarea name="remarks" id="holdRemarks" class="form-control" rows="3" required style="font-size: 0.8rem;"></textarea>
                    </div>
                    <div class="mb-2">
                        <label for="followUpDate" class="form-label">Follow-up Date *</label>
                        <input type="date" name="follow_up_date" id="followUpDate" class="form-control" required style="font-size: 0.8rem;">
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary btn-sm">Confirm Hold</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('approvals.reject', $application) }}" method="POST">
                @csrf
                <div class="modal-header p-2">
                    <h6 class="modal-title" id="rejectModalLabel">Reject Application</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2">
                    <div class="mb-2">
                        <label for="rejectRemarks" class="form-label">Reason for Rejection *</label>
                        <textarea name="remarks" id="rejectRemarks" class="form-control" rows="3" required style="font-size: 0.8rem;"></textarea>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div> --}}
@endif
</div>

{{-- **UPDATED: Document Modals ** --}}
@if($application->entityDetails)
{{-- PAN Document Modal --}}
@if($application->entityDetails->pan_path)
<div class="modal fade" id="panModal" tabindex="-1" aria-labelledby="panModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="panModalLabel">PAN Document</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <iframe src="{{ Storage::disk('s3')->url('Connect/Distributor/pan/' . $application->entityDetails->pan_path) }}"
                    title="PAN Document"
                    style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- GST Document Modal --}}
@if($application->entityDetails->gst_applicable === 'yes' && $application->entityDetails->gst_path)
<div class="modal fade" id="gstModal" tabindex="-1" aria-labelledby="gstModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="gstModalLabel">GST Document</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <iframe src="{{ Storage::disk('s3')->url('Connect/Distributor/gst/' . $application->entityDetails->gst_path) }}"
                    title="GST Document"
                    style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

@if($application->entityDetails->bank_document_path)
<div class="modal fade" id="bankDocModal" tabindex="-1" aria-labelledby="bankDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="bankDocModalLabel">BANK Document</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <iframe src="{{ Storage::disk('s3')->url('Connect/Distributor/bank/' . $application->entityDetails->bank_document_path) }}"
                    title="PAN Document"
                    style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Seed License Document Modal --}}
@if($application->entityDetails->seed_license_path)
<div class="modal fade" id="seedLicenseModal" tabindex="-1" aria-labelledby="seedLicenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="seedLicenseModalLabel">Seed License Document</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <iframe src="{{ Storage::disk('s3')->url('Connect/Distributor/seed_license/' . $application->entityDetails->seed_license_path) }}"
                    title="Seed License Document"
                    style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Bank Document Modal --}}
@if($application->entityDetails->bank_document_path)
<div class="modal fade" id="bankDocumentModal" tabindex="-1" aria-labelledby="bankDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="bankDocumentModalLabel">Bank Document</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <iframe src="{{ Storage::disk('s3')->url('Connect/Distributor/bank/' . $application->entityDetails->bank_document_path) }}"
                    title="Bank Document"
                    style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Bank Statement Modal (if additional statement exists) --}}
@if($application->bankDetail && $application->bankDetail->bank_statement_path)
<div class="modal fade" id="bankStatementModal" tabindex="-1" aria-labelledby="bankStatementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="bankStatementModalLabel">Bank Statement</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <iframe src="{{ Storage::disk('s3')->url('Connect/Distributor/bank_statements/' . $application->bankDetail->bank_statement_path) }}"
                    title="Bank Statement"
                    style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif
@endif

{{-- Document Modals --}}
@if($application->entityDetails)
<!-- Entity Proof Modal -->
@if($application->entityDetails->entity_proof_path)
<div class="modal fade" id="entityProofModal" tabindex="-1" aria-labelledby="entityProofModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="entityProofModalLabel">{{ $entityProofLabel }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <iframe src="{{ Storage::disk('s3')->url('Connect/Distributor/entity_proof/' . $application->entityDetails->entity_proof_path) }}"
                    title="Entity Proof Document"
                    style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Ownership Information Modal -->
@if($application->entityDetails->ownership_info_path)
<div class="modal fade" id="ownershipInfoModal" tabindex="-1" aria-labelledby="ownershipInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="ownershipInfoModalLabel">{{ $ownershipInfoLabel }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <iframe src="{{ Storage::disk('s3')->url('Connect/Distributor/ownership_info/' . $application->entityDetails->ownership_info_path) }}"
                    title="Ownership Information Document"
                    style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Proprietor Aadhar Modal -->
{{--@if($application->proprietorDetails && $application->proprietorDetails->aadhar_path)
    <div class="modal fade" id="proprietorAadharModal" tabindex="-1" aria-labelledby="proprietorAadharModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="proprietorAadharModalLabel">Aadhar Card of Proprietor</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2">
                    <iframe src="{{ Storage::disk('s3')->url('Connect/Distributor/proprietor_aadhar/' . $application->proprietorDetails->aadhar_path) }}"
title="Proprietor Aadhar Card"
style="width: 100%; height: 500px; border: none;"></iframe>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>
</div>
</div>
</div>
@endif--}}

<!-- Bank Statement Modal -->
@if($application->entityDetails->bank_statement_path)
<div class="modal fade" id="bankStatementModal" tabindex="-1" aria-labelledby="bankStatementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="bankStatementModalLabel">{{ $bankStatementLabel }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <iframe src="{{ Storage::disk('s3')->url('Connect/Distributor/bank_statement/' . $application->entityDetails->bank_statement_path) }}"
                    title="Bank Statement"
                    style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- ITR Acknowledgement Modal -->
@if($application->entityDetails->itr_acknowledgement_path)
<div class="modal fade" id="itrAcknowledgementModal" tabindex="-1" aria-labelledby="itrAcknowledgementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="itrAcknowledgementModalLabel">{{ $itrAcknowledgementLabel }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <iframe src="{{ Storage::disk('s3')->url('Connect/Distributor/itr_acknowledgement/' . $application->entityDetails->itr_acknowledgement_path) }}"
                    title="ITR Acknowledgement"
                    style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Balance Sheet Modal -->
@if($application->entityDetails->balance_sheet_path)
<div class="modal fade" id="balanceSheetModal" tabindex="-1" aria-labelledby="balanceSheetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="balanceSheetModalLabel">{{ $balanceSheetLabel }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <iframe src="{{ Storage::disk('s3')->url('Connect/Distributor/balance_sheet/' . $application->entityDetails->balance_sheet_path) }}"
                    title="Balance Sheet"
                    style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endif
@endif


@endsection