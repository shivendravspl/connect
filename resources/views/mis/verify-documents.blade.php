@extends('layouts.app')

@push('styles')
<style>
    .form-section {
        margin-bottom: 1rem;
        padding: 1rem;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }
    .table th, .table td {
        vertical-align: middle;
        font-size: 0.8rem;
        padding: 0.5rem;
    }
    .modal-body .document-preview {
        max-height: 400px;
        overflow-y: auto;
    }
    .modal-body embed, .modal-body img {
        max-width: 100%;
        height: auto;
        margin-bottom: 0.5rem;
    }
    .card {
        margin-bottom: 1rem;
    }
    .btn-sm {
        font-size: 0.75rem;
        padding: 0.2rem 0.5rem;
    }
    .badge {
        font-size: 0.75rem;
    }
    .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .form-select-sm, .form-control-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    .alert-dismissible {
        font-size: 0.8rem;
        padding: 0.5rem;
    }
    @media (max-width: 768px) {
        .container-fluid {
            padding: 0.3rem;
        }
        .card {
            margin: 0;
            border-radius: 0;
        }
        .table th, .table td {
            font-size: 0.7rem;
            padding: 0.3rem;
        }
        .modal-content {
            margin: 0.3rem;
        }
        .modal-body {
            padding: 0.5rem;
        }
        .btn-sm {
            font-size: 0.7rem;
            padding: 0.15rem 0.4rem;
        }
        h4, h5, h6 {
            font-size: 0.9rem;
        }
        .form-select-sm, .form-control-sm {
            font-size: 0.7rem;
            padding: 0.2rem 0.4rem;
        }
        .alert-dismissible {
            font-size: 0.7rem;
        }
    }
    @media (max-width: 576px) {
        .form-section {
            padding: 0.5rem;
        }
        .col-12 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <!-- Page Title -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Verify Documents for {{ $application->application_code ?? 'N/A' }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">Verify Documents</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Application Status -->
                    <div class="form-section">
                        <h5 class="mb-2">Application Status</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <tbody>
                                    <tr>
                                        <th>Status</th>
                                        <td><span class="badge bg-{{ $application->status_badge ?? 'secondary' }}">{{ ucfirst($application->status ?? 'N/A') }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Submitted On</th>
                                        <td>{{ $application->created_at ? $application->created_at->format('d-M-Y H:i') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Last Updated</th>
                                        <td>{{ $application->updated_at ? $application->updated_at->format('d-M-Y H:i') : 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Basic Details -->
                    <div id="basic-details" class="form-section">
                        <h5 class="mb-2">Basic Details</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <tbody>
                                    <tr>
                                        <th>Territory</th>
                                        <td>{{ isset($application->territory) ? DB::table('core_territory')->where('id', $application->territory)->value('territory_name') ?? 'N/A' : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Region</th>
                                        <td>{{ isset($application->region) ? DB::table('core_region')->where('id', $application->region)->value('region_name') ?? 'N/A' : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Zone</th>
                                        <td>{{ isset($application->zone) ? DB::table('core_zone')->where('id', $application->zone)->value('zone_name') ?? 'N/A' : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Business Unit</th>
                                        <td>{{ isset($application->business_unit) ? DB::table('core_business_unit')->where('id', $application->business_unit)->value('business_unit_name') ?? 'N/A' : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Crop Vertical</th>
                                        <td>{{ isset($application->crop_vertical) && $application->crop_vertical === '1' ? 'Field Crop' : 'Veg Crop' }}</td>
                                    </tr>
                                    <tr>
                                        <th>State</th>
                                        <td>{{ isset($application->state) ? DB::table('core_state')->where('id', $application->state)->value('state_name') ?? 'N/A' : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>District</th>
                                        <td>{{ isset($application->district) ? DB::table('core_district')->where('id', $application->district)->value('district_name') ?? 'N/A' : 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Entity Details -->
                    @if(isset($application->entityDetails))
                        <div id="entity-details" class="form-section">
                            <h5 class="mb-2">Entity Details</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <tbody>
                                        <tr>
                                            <th>Type of Firm</th>
                                            <td>{{ $application->entityDetails->entity_type ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Firm Name</th>
                                            <td>{{ $application->entityDetails->establishment_name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Business Address</th>
                                            <td>{{ $application->entityDetails->business_address ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>House No</th>
                                            <td>{{ $application->entityDetails->house_no ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Landmark</th>
                                            <td>{{ $application->entityDetails->landmark ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>City</th>
                                            <td>{{ $application->entityDetails->city ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Pincode</th>
                                            <td>{{ $application->entityDetails->pincode ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>State</th>
                                            <td>{{ $application->entityDetails->state->state_name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>District</th>
                                            <td>{{ $application->entityDetails->district->district_name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Country</th>
                                            <td>{{ $application->entityDetails->district->country ?? 'N/A' }}</td>
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
                                            <td>{{ $application->entityDetails->pan_number ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>GST Applicable</th>
                                            <td>{{ $application->entityDetails->gst_applicable ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>GST Number</th>
                                            <td>{{ $application->entityDetails->gst_number ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Seed License</th>
                                            <td>{{ $application->entityDetails->seed_license ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>TAN Number</th>
                                            <td>{{ $application->entityDetails->additional_data['tan_number'] ?? 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Documents Section -->
                            <h6 class="mb-2">Documents</h6>
                            @php
                                $documents = [];
                                if (!empty($application->entityDetails->documents_data)) {
                                    $raw = $application->entityDetails->documents_data;
                                    if (is_string($raw)) {
                                        $decoded = json_decode($raw, true);
                                        $documents = is_array($decoded) ? $decoded : [];
                                    } elseif (is_array($raw)) {
                                        $documents = $raw;
                                    }
                                }
                            @endphp
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Details</th>
                                            <th>File</th>
                                            <th>Status</th>
                                            <th>Verified</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(empty($documents))
                                            <tr>
                                                <td colspan="5" class="text-muted">No documents available.</td>
                                            </tr>
                                        @else
                                            @foreach($documents as $index => $doc)
                                                <tr>
                                                    <td>{{ ucfirst($doc['type'] ?? 'N/A') }}</td>
                                                    <td>
                                                        @if(is_array($doc['details'] ?? []))
                                                            @foreach($doc['details'] as $key => $value)
                                                                {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}<br>
                                                            @endforeach
                                                        @else
                                                            {{ $doc['details'] ?? 'N/A' }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($doc['path'])
                                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#documentModal-{{ $index }}">View</button>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>{{ $doc['status'] ?? 'Pending' }}</td>
                                                    <td>{{ isset($doc['verified']) && $doc['verified'] ? 'Yes' : 'No' }}</td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <!-- Document Preview Modals -->
                            @foreach($documents as $index => $doc)
                                @if($doc['path'])
                                    <div class="modal fade" id="documentModal-{{ $index }}" tabindex="-1" aria-labelledby="documentModalLabel-{{ $index }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="documentModalLabel-{{ $index }}">{{ $doc['details']['name'] ?? ucfirst($doc['type'] ?? 'Document') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body document-preview">
                                                    @if(in_array(strtolower(pathinfo($doc['path'], PATHINFO_EXTENSION)), ['pdf']))
                                                        <embed src="{{ Storage::url($doc['path']) }}" type="application/pdf" width="100%" height="300px">
                                                    @elseif(in_array(strtolower(pathinfo($doc['path'], PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg']))
                                                        <img src="{{ Storage::url($doc['path']) }}" alt="{{ $doc['details']['name'] ?? 'Document' }}">
                                                    @else
                                                        <a href="{{ Storage::url($doc['path']) }}" target="_blank" class="btn btn-sm btn-primary">Download {{ $doc['details']['name'] ?? 'Document' }}</a>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach

                            <!-- Entity-Specific Details -->
                            @if($application->entityDetails->entity_type === 'sole_proprietorship')
                                <h6 class="mb-2">Proprietor Details</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <tr>
                                                <th>Name</th>
                                                <td>{{ $application->entityDetails->additional_data['proprietor']['name'] ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Date of Birth</th>
                                                <td>{{ $application->entityDetails->additional_data['proprietor']['dob'] ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Father's Name</th>
                                                <td>{{ $application->entityDetails->additional_data['proprietor']['father_name'] ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Address</th>
                                                <td>{{ $application->entityDetails->additional_data['proprietor']['address'] ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Pincode</th>
                                                <td>{{ $application->entityDetails->additional_data['proprietor']['pincode'] ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Country</th>
                                                <td>{{ $application->entityDetails->additional_data['proprietor']['country'] ?? 'N/A' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @elseif(in_array($application->entityDetails->entity_type, ['partnership', 'llp', 'private_company', 'public_company', 'cooperative_society', 'trust']))
                                <h6 class="mb-2">{{ ucfirst(str_replace('_', ' ', $application->entityDetails->entity_type)) }} Details</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            @if($application->entityDetails->entity_type === 'llp')
                                                <tr>
                                                    <th>LLPIN Number</th>
                                                    <td>{{ $application->entityDetails->additional_data['llp']['llpin_number'] ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Incorporation Date</th>
                                                    <td>{{ $application->entityDetails->additional_data['llp']['incorporation_date'] ?? 'N/A' }}</td>
                                                </tr>
                                            @elseif(in_array($application->entityDetails->entity_type, ['private_company', 'public_company']))
                                                <tr>
                                                    <th>CIN Number</th>
                                                    <td>{{ $application->entityDetails->additional_data['company']['cin_number'] ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Incorporation Date</th>
                                                    <td>{{ $application->entityDetails->additional_data['company']['incorporation_date'] ?? 'N/A' }}</td>
                                                </tr>
                                            @elseif($application->entityDetails->entity_type === 'cooperative_society')
                                                <tr>
                                                    <th>Registration Number</th>
                                                    <td>{{ $application->entityDetails->additional_data['cooperative']['reg_number'] ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Registration Date</th>
                                                    <td>{{ $application->entityDetails->additional_data['cooperative']['reg_date'] ?? 'N/A' }}</td>
                                                </tr>
                                            @elseif($application->entityDetails->entity_type === 'trust')
                                                <tr>
                                                    <th>Registration Number</th>
                                                    <td>{{ $application->entityDetails->additional_data['trust']['reg_number'] ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Registration Date</th>
                                                    <td>{{ $application->entityDetails->additional_data['trust']['reg_date'] ?? 'N/A' }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                <h6 class="mb-2">{{ $application->entityDetails->entity_type === 'partnership' ? 'Partners' : ($application->entityDetails->entity_type === 'llp' ? 'Designated Partners' : ($application->entityDetails->entity_type === 'cooperative_society' ? 'Committee Members' : ($application->entityDetails->entity_type === 'trust' ? 'Trustees' : 'Directors'))) }}</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                @if($application->entityDetails->entity_type === 'partnership')
                                                    <th>Father's Name</th>
                                                @elseif($application->entityDetails->entity_type === 'llp')
                                                    <th>DPIN Number</th>
                                                @elseif(in_array($application->entityDetails->entity_type, ['private_company', 'public_company']))
                                                    <th>DIN Number</th>
                                                @elseif(in_array($application->entityDetails->entity_type, ['cooperative_society', 'trust']))
                                                    <th>Designation</th>
                                                @endif
                                                <th>Contact</th>
                                                <th>Address</th>
                                                @if($application->entityDetails->entity_type === 'partnership')
                                                    <th>Email</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($application->entityDetails->additional_data['partners'] ?? [] as $partner)
                                                <tr>
                                                    <td>{{ $partner['name'] ?? 'N/A' }}</td>
                                                    @if($application->entityDetails->entity_type === 'partnership')
                                                        <td>{{ $partner['father_name'] ?? 'N/A' }}</td>
                                                    @elseif($application->entityDetails->entity_type === 'llp')
                                                        <td>{{ $partner['dpin_number'] ?? 'N/A' }}</td>
                                                    @elseif(in_array($application->entityDetails->entity_type, ['private_company', 'public_company']))
                                                        <td>{{ $partner['din_number'] ?? 'N/A' }}</td>
                                                    @elseif(in_array($application->entityDetails->entity_type, ['cooperative_society', 'trust']))
                                                        <td>{{ $partner['designation'] ?? 'N/A' }}</td>
                                                    @endif
                                                    <td>{{ $partner['contact'] ?? 'N/A' }}</td>
                                                    <td>{{ $partner['address'] ?? 'N/A' }}</td>
                                                    @if($application->entityDetails->entity_type === 'partnership')
                                                        <td>{{ $partner['email'] ?? 'N/A' }}</td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <!-- Authorized Persons -->
                            @if(!empty($application->entityDetails->additional_data['authorized_persons']))
                                <h6 class="mb-2">Authorized Persons</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Contact</th>
                                                <th>Email</th>
                                                <th>Address</th>
                                                <th>Relation</th>
                                                <th>Letter</th>
                                                <th>Aadhar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($application->entityDetails->additional_data['authorized_persons'] as $index => $person)
                                                <tr>
                                                    <td>{{ $person['name'] ?? 'N/A' }}</td>
                                                    <td>{{ $person['contact'] ?? 'N/A' }}</td>
                                                    <td>{{ $person['email'] ?? 'N/A' }}</td>
                                                    <td>{{ $person['address'] ?? 'N/A' }}</td>
                                                    <td>{{ $person['relation'] ?? 'N/A' }}</td>
                                                    <td>
                                                        @if(!empty($person['letter']))
                                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#authPersonLetterModal-{{ $index }}">View</button>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!empty($person['aadhar']))
                                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#authPersonAadharModal-{{ $index }}">View</button>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Modals for Authorized Persons Documents -->
                                @foreach($application->entityDetails->additional_data['authorized_persons'] as $index => $person)
                                    @if(!empty($person['letter']))
                                        <div class="modal fade" id="authPersonLetterModal-{{ $index }}" tabindex="-1" aria-labelledby="authPersonLetterModalLabel-{{ $index }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="authPersonLetterModalLabel-{{ $index }}">Authorization Letter - {{ $person['name'] ?? 'Document' }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body document-preview">
                                                        @if(in_array(strtolower(pathinfo($person['letter'], PATHINFO_EXTENSION)), ['pdf']))
                                                            <embed src="{{ asset('storage/' . $person['letter']) }}" type="application/pdf" width="100%" height="300px">
                                                        @elseif(in_array(strtolower(pathinfo($person['letter'], PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg']))
                                                            <img src="{{ asset('storage/' . $person['letter']) }}" alt="Authorization Letter">
                                                        @else
                                                            <a href="{{ asset('storage/' . $person['letter']) }}" target="_blank" class="btn btn-sm btn-primary">Download Authorization Letter</a>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if(!empty($person['aadhar']))
                                        <div class="modal fade" id="authPersonAadharModal-{{ $index }}" tabindex="-1" aria-labelledby="authPersonAadharModalLabel-{{ $index }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="authPersonAadharModalLabel-{{ $index }}">Aadhar - {{ $person['name'] ?? 'Document' }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body document-preview">
                                                        @if(in_array(strtolower(pathinfo($person['aadhar'], PATHINFO_EXTENSION)), ['pdf']))
                                                            <embed src="{{ asset('storage/' . $person['aadhar']) }}" type="application/pdf" width="100%" height="300px">
                                                        @elseif(in_array(strtolower(pathinfo($person['aadhar'], PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg']))
                                                            <img src="{{ asset('storage/' . $person['aadhar']) }}" alt="Aadhar Document">
                                                        @else
                                                            <a href="{{ asset('storage/' . $person['aadhar']) }}" target="_blank" class="btn btn-sm btn-primary">Download Aadhar</a>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    @else
                        <div class="alert alert-warning">
                            Entity Details not available. Please check the controller to ensure <code>$application->entityDetails</code> is passed correctly.
                        </div>
                    @endif

                    <!-- Distribution Details -->
                    @if(isset($application->distributionDetail))
                        <div id="distribution-details" class="form-section">
                            <h5 class="mb-2">Distribution Details</h5>
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
                                            <th>Area Covered</th>
                                            <td>{{ !empty($areaCovered) ? implode(', ', $areaCovered) : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Appointment Type</th>
                                            <td>{{ $application->distributionDetail->appointment_type ?? 'N/A' }}</td>
                                        </tr>
                                        @if($application->distributionDetail && $application->distributionDetail->appointment_type === 'replacement')
                                            <tr>
                                                <th>Reason for Replacement</th>
                                                <td>{{ $application->distributionDetail->replacement_reason ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Commitment to Recover Outstanding</th>
                                                <td>{{ $application->distributionDetail->outstanding_recovery ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Name of Previous Firm</th>
                                                <td>{{ $application->distributionDetail->previous_firm_name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
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
                    @endif

                    <!-- Business Plan -->
                    @if(isset($application->businessPlans))
                        <div id="business-plan" class="form-section">
                            <h5 class="mb-2">Business Plan (Next Two Years)</h5>
                            @php
                                $year2025 = \App\Models\Year::where('period', '2025-26')->first();
                                $year2026 = \App\Models\Year::where('period', '2026-27')->first();
                            @endphp
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Crop</th>
                                            <th>FY 2025-26 (MT)</th>
                                            <th>FY 2026-27 (MT)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($application->businessPlans as $plan)
                                            @php
                                                $targets = is_string($plan->yearly_targets) ? json_decode($plan->yearly_targets, true) : ($plan->yearly_targets ?? []);
                                            @endphp
                                            <tr>
                                                <td>{{ $plan->crop ?? 'N/A' }}</td>
                                                <td>{{ isset($year2025->id) && isset($targets[$year2025->id]) ? $targets[$year2025->id] : 'N/A' }}</td>
                                                <td>{{ isset($year2026->id) && isset($targets[$year2026->id]) ? $targets[$year2026->id] : 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Financial & Operational Information -->
                    @if(isset($application->financialInfo))
                        <div id="financial-info" class="form-section">
                            <h5 class="mb-2">Financial & Operational Information</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <tbody>
                                        <tr>
                                            <th>Net Worth (Previous FY)</th>
                                            <td>{{ $application->financialInfo->net_worth ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Shop Ownership</th>
                                            <td>{{ $application->financialInfo->shop_ownership ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Godown Area & Ownership</th>
                                            <td>{{ $application->financialInfo->godown_area ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Years in Business</th>
                                            <td>{{ $application->financialInfo->years_in_business ?? 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <h6 class="mb-2">Annual Turnover</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Financial Year</th>
                                            <th>Net Turnover (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $turnover = isset($application->financialInfo->annual_turnover) 
                                                ? (is_string($application->financialInfo->annual_turnover) 
                                                    ? json_decode($application->financialInfo->annual_turnover, true) 
                                                    : ($application->financialInfo->annual_turnover ?? [])) 
                                                : [];
                                            $defaultYears = ['2022-23', '2023-24', '2024-25'];
                                        @endphp
                                        @foreach($defaultYears as $year)
                                            <tr>
                                                <td>FY {{ $year }}</td>
                                                <td>{{ isset($turnover[$year]) ? $turnover[$year] : 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Bank Details -->
                    @if(isset($application->bankDetail))
                        <div id="bank-details" class="form-section">
                            <h5 class="mb-2">Bank Details</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <tbody>
                                        <tr>
                                            <th>Financial Status</th>
                                            <td>{{ $application->bankDetail->financial_status ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>No. of Retailers Dealt With</th>
                                            <td>{{ $application->bankDetail->retailer_count ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Bank Name</th>
                                            <td>{{ $application->bankDetail->bank_name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Account Holder Name</th>
                                            <td>{{ $application->bankDetail->account_holder ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Account Number</th>
                                            <td>{{ $application->bankDetail->account_number ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>IFSC Code</th>
                                            <td>{{ $application->bankDetail->ifsc_code ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Account Type</th>
                                            <td>{{ $application->bankDetail->account_type ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Relationship Duration (Years)</th>
                                            <td>{{ $application->bankDetail->relationship_duration ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>OD Limit (if any)</th>
                                            <td>{{ $application->bankDetail->od_limit ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>OD Security</th>
                                            <td>{{ $application->bankDetail->od_security ?? 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Declarations -->
                    @if(isset($application->declarations))
                        <div id="declarations" class="form-section">
                            <h5 class="mb-2">Declarations</h5>
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
                                <div class="card mb-1">
                                    <div class="card-body p-1">
                                        <h6 class="mb-1" style="font-size: 0.85rem;">{{ $config['label'] }}</h6>
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
                                </div>
                            @endforeach
                            <div class="card">
                                <div class="card-body p-1">
                                    <h6 class="mb-1" style="font-size: 0.85rem;">Declaration</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm">
                                            <tbody>
                                                @php
                                                    $truthful = $application->declarations->where('question_key', 'declaration_truthful')->first();
                                                @endphp
                                                <tr>
                                                    <th>I hereby solemnly affirm and declare that the information furnished in this form is true, correct, and complete to the best of my knowledge and belief.</th>
                                                    <td>{{ $truthful && $truthful->has_issue ? 'Affirmed' : 'Not Affirmed' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Approval Logs -->
                    @if(isset($application->approvalLogs))
                        <div id="approval-logs" class="form-section">
                            <h5 class="mb-2">Approval Logs</h5>
                            @if($application->approvalLogs->isEmpty())
                                <p class="text-muted">No approval logs available.</p>
                            @else
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
                                                    <td>{{ $log->created_at ? $log->created_at->format('d-M-Y H:i') : 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Document Verification Section -->
                    <div id="document-verification" class="form-section">
                        <h4 class="card-title mb-2">Document Verification</h4>
                        <div id="verification-message" class="mt-2" style="display: none;"></div>
                        @php
                            $checkpoints = [
                                'business_entity_proofs' => [
                                    'label' => 'Business Entity Proofs',
                                    'document_types' => ['pan_file', 'gst_file', 'seed_license_file'],
                                ],
                                'ownership_confirmation' => [
                                    'label' => 'Ownership Confirmation',
                                    'document_types' => ['pan_file'],
                                ],
                                'all_required_documents' => [
                                    'label' => 'All Required Documents',
                                    'document_types' => ['pan_file', 'gst_file', 'seed_license_file', 'bank_file'],
                                ],
                            ];
                        @endphp
                        <form id="verification-form" action="{{ route('approvals.submit-verification', $application) }}" method="POST">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Document Type</th>
                                            <th>Status</th>
                                            <th>Reason/Requirement</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($checkpoints as $checkpoint => $config)
                                            @php
                                                $verification = $application->documentVerifications->where('document_type', $checkpoint)->first();
                                            @endphp
                                            <tr>
                                                <td>{{ $config['label'] }}</td>
                                                <td>
                                                    <select class="form-select form-select-sm" name="checkpoints[{{ $checkpoint }}][status]">
                                                        <option value="verified" {{ optional($verification)->status === 'verified' ? 'selected' : '' }}>
                                                            Available & Verified
                                                        </option>
                                                        <option value="rejected" {{ optional($verification)->status === 'rejected' ? 'selected' : '' }}>
                                                            Not Available
                                                        </option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <textarea class="form-control form-control-sm" name="checkpoints[{{ $checkpoint }}][remarks]"
                                                        placeholder="Reason for rejection / Additional Requirement" rows="2">{{ optional($verification)->remarks }}</textarea>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Additional Requirements Section -->
                            <div class="form-section mt-3">
                                <h5 class="mb-2">Additional Requirements</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm" id="additional-requirements-table">
                                        <thead>
                                            <tr>
                                                <th>Name of Document</th>
                                                <th>Remark</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $additionalRequirements = $application->documentVerifications->where('document_type', 'additional_requirements')->first()->remarks ?? [];
                                                if (is_string($additionalRequirements)) {
                                                    $additionalRequirements = json_decode($additionalRequirements, true) ?? [];
                                                }
                                            @endphp
                                            @foreach($additionalRequirements as $index => $req)
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm" name="additional_requirements[{{ $index }}][name]"
                                                            value="{{ $req['name'] ?? '' }}" placeholder="e.g., GST Certificate">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm" name="additional_requirements[{{ $index }}][remark]"
                                                            value="{{ $req['remark'] ?? '' }}" placeholder="e.g., Missing Page 2 – Request Reupload">
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-danger remove-row">Remove</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <button type="button" class="btn btn-sm btn-primary mt-1" id="add-requirement">Add Requirement</button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success btn-sm mt-2">Submit Verification</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        let rowIndex = {{ count($additionalRequirements) }};
        $('#add-requirement').click(function() {
            const newRow = `
                <tr>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="additional_requirements[${rowIndex}][name]"
                            placeholder="e.g., GST Certificate">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="additional_requirements[${rowIndex}][remark]"
                            placeholder="e.g., Missing Page 2 – Request Reupload">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-row">Remove</button>
                    </td>
                </tr>`;
            $('#additional-requirements-table tbody').append(newRow);
            rowIndex++;
        });

        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });

        $('#verification-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            const data = form.serialize();

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function(response) {
                    $('#verification-message').html(`
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${response.message || 'Document verification submitted successfully.'}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `).show();
                    setTimeout(function() {
                        window.location.href = response.next_step?.url || '{{ route("dashboard") }}';
                    }, 1500);
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors || { message: 'An error occurred while submitting the verification.' };
                    const errorMessage = Object.values(errors).flat().join('<br>');
                    $('#verification-message').html(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${errorMessage}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `).show();
                }
            });
        });
    });
</script>
@endpush
@endsection