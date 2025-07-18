@extends('layouts.app')

@push('styles')
<style>
    .compact-table {
        font-size: 0.85rem;
        line-height: 1.2;
    }
    .compact-table th, .compact-table td {
        padding: 0.5rem;
        vertical-align: middle;
    }
    .compact-table .btn-sm {
        font-size: 0.75rem;
        padding: 0.2rem 0.4rem;
    }
    .compact-table .badge {
        font-size: 0.7rem;
        padding: 0.3rem 0.5rem;
    }
    .card {
        margin-bottom: 1rem;
        border-radius: 0.25rem;
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
    @media (max-width: 768px) {
        .compact-table {
            font-size: 0.75rem;
        }
        .compact-table th, .compact-table td {
            padding: 0.3rem;
        }
        .compact-table .btn-sm {
            font-size: 0.65rem;
            padding: 0.15rem 0.3rem;
        }
        .container-fluid {
            padding: 0.5rem;
        }
        .card {
            margin: 0;
            border-radius: 0;
        }
        .modal-content {
            margin: 0.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    <div class="row mb-3">
        <div class="col-12">
            <h2 class="mb-2">Distributor Application - View (ID: {{ $application->application_code ?? 'N/A' }})</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Application Status -->
                    <div class="form-section mb-3">
                        <h5 class="mb-2">Application Status</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered compact-table">
                                <tbody>
                                    <tr>
                                        <td><strong>Status</strong></td>
                                        <td><span class="badge bg-{{ $application->status_badge ?? 'secondary' }}">{{ ucfirst($application->status ?? 'N/A') }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Submitted On</strong></td>
                                        <td>{{ $application->created_at ? $application->created_at->format('d-M-Y H:i') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Last Updated</strong></td>
                                        <td>{{ $application->updated_at ? $application->updated_at->format('d-M-Y H:i') : 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Step 1: Basic Details -->
                    @if($application->current_progress_step >= 1)
                        <div id="basic-details" class="form-section mb-3">
                            <h5 class="mb-2">Basic Details</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered compact-table">
                                    <tbody>
                                        <tr>
                                            <td><strong>Territory</strong></td>
                                            <td>{{ isset($application->territory) ? DB::table('core_territory')->where('id', $application->territory)->value('territory_name') ?? 'N/A' : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Region</strong></td>
                                            <td>{{ isset($application->region) ? DB::table('core_region')->where('id', $application->region)->value('region_name') ?? 'N/A' : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Zone</strong></td>
                                            <td>{{ isset($application->zone) ? DB::table('core_zone')->where('id', $application->zone)->value('zone_name') ?? 'N/A' : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Business Unit</strong></td>
                                            <td>{{ isset($application->business_unit) ? DB::table('core_business_unit')->where('id', $application->business_unit)->value('business_unit_name') ?? 'N/A' : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Crop Vertical</strong></td>
                                            <td>{{ isset($application->crop_vertical) && $application->crop_vertical === '1' ? 'Field Crop' : 'Veg Crop' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>State</strong></td>
                                            <td>{{ isset($application->state) ? DB::table('core_state')->where('id', $application->state)->value('state_name') ?? 'N/A' : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>District</strong></td>
                                            <td>{{ isset($application->district) ? DB::table('core_district')->where('id', $application->district)->value('district_name') ?? 'N/A' : 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Step 2: Entity Details -->
                    @if($application->current_progress_step >= 2 && isset($application->entityDetails))
                        <div id="entity-details" class="form-section mb-3">
                            <h5 class="mb-2">Entity Details</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered compact-table">
                                    <tbody>
                                        <tr>
                                            <td><strong>Type of Firm</strong></td>
                                            <td>{{ $application->entityDetails->entity_type ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Firm Name</strong></td>
                                            <td>{{ $application->entityDetails->establishment_name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Business Address</strong></td>
                                            <td>{{ $application->entityDetails->business_address ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>House No</strong></td>
                                            <td>{{ $application->entityDetails->house_no ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Landmark</strong></td>
                                            <td>{{ $application->entityDetails->landmark ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>City</strong></td>
                                            <td>{{ $application->entityDetails->city ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Pincode</strong></td>
                                            <td>{{ $application->entityDetails->pincode ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>State</strong></td>
                                            <td>{{ $application->entityDetails->state->state_name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>District</strong></td>
                                            <td>{{ $application->entityDetails->district->district_name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Country</strong></td>
                                            <td>{{ $application->entityDetails->district->country ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Mobile</strong></td>
                                            <td>{{ $application->entityDetails->mobile ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email</strong></td>
                                            <td>{{ $application->entityDetails->email ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>PAN Number</strong></td>
                                            <td>{{ $application->entityDetails->pan_number ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>GST Applicable</strong></td>
                                            <td>{{ $application->entityDetails->gst_applicable ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>GST Number</strong></td>
                                            <td>{{ $application->entityDetails->gst_number ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Seed License</strong></td>
                                            <td>{{ $application->entityDetails->seed_license ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>TAN Number</strong></td>
                                            <td>{{ $application->entityDetails->additional_data['tan_number'] ?? 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Documents -->
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
                            <h6 class="mb-2">Documents</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered compact-table">
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

                            <!-- Document Modals -->
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
                                                        <embed src="{{ Storage::url($doc['path']) }}" type="application/pdf" width="100%" height="400px">
                                                    @elseif(in_array(strtolower(pathinfo($doc['path'], PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg']))
                                                        <img src="{{ Storage::url($doc['path']) }}" alt="{{ $doc['details']['name'] ?? 'Document' }}">
                                                    @else
                                                        <a href="{{ Storage::url($doc['path']) }}" target="_blank" class="btn btn-sm btn-primary">Download {{ $doc['details']['name'] ?? 'Document' }}</a>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                                    <table class="table table-bordered compact-table">
                                        <tbody>
                                            <tr>
                                                <td><strong>Name</strong></td>
                                                <td>{{ $application->entityDetails->additional_data['proprietor']['name'] ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Date of Birth</strong></td>
                                                <td>{{ $application->entityDetails->additional_data['proprietor']['dob'] ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Father's Name</strong></td>
                                                <td>{{ $application->entityDetails->additional_data['proprietor']['father_name'] ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Address</strong></td>
                                                <td>{{ $application->entityDetails->additional_data['proprietor']['address'] ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Pincode</strong></td>
                                                <td>{{ $application->entityDetails->additional_data['proprietor']['pincode'] ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Country</strong></td>
                                                <td>{{ $application->entityDetails->additional_data['proprietor']['country'] ?? 'N/A' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @elseif(in_array($application->entityDetails->entity_type, ['partnership', 'llp', 'private_company', 'public_company', 'cooperative_society', 'trust']))
                                <h6 class="mb-2">{{ ucfirst(str_replace('_', ' ', $application->entityDetails->entity_type)) }} Details</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered compact-table">
                                        <tbody>
                                            @if($application->entityDetails->entity_type === 'llp')
                                                <tr>
                                                    <td><strong>LLPIN Number</strong></td>
                                                    <td>{{ $application->entityDetails->additional_data['llp']['llpin_number'] ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Incorporation Date</strong></td>
                                                    <td>{{ $application->entityDetails->additional_data['llp']['incorporation_date'] ?? 'N/A' }}</td>
                                                </tr>
                                            @elseif(in_array($application->entityDetails->entity_type, ['private_company', 'public_company']))
                                                <tr>
                                                    <td><strong>CIN Number</strong></td>
                                                    <td>{{ $application->entityDetails->additional_data['company']['cin_number'] ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Incorporation Date</strong></td>
                                                    <td>{{ $application->entityDetails->additional_data['company']['incorporation_date'] ?? 'N/A' }}</td>
                                                </tr>
                                            @elseif($application->entityDetails->entity_type === 'cooperative_society')
                                                <tr>
                                                    <td><strong>Registration Number</strong></td>
                                                    <td>{{ $application->entityDetails->additional_data['cooperative']['reg_number'] ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Registration Date</strong></td>
                                                    <td>{{ $application->entityDetails->additional_data['cooperative']['reg_date'] ?? 'N/A' }}</td>
                                                </tr>
                                            @elseif($application->entityDetails->entity_type === 'trust')
                                                <tr>
                                                    <td><strong>Registration Number</strong></td>
                                                    <td>{{ $application->entityDetails->additional_data['trust']['reg_number'] ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Registration Date</strong></td>
                                                    <td>{{ $application->entityDetails->additional_data['trust']['reg_date'] ?? 'N/A' }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                <h6 class="mb-2">{{ $application->entityDetails->entity_type === 'partnership' ? 'Partners' : ($application->entityDetails->entity_type === 'llp' ? 'Designated Partners' : ($application->entityDetails->entity_type === 'cooperative_society' ? 'Committee Members' : ($application->entityDetails->entity_type === 'trust' ? 'Trustees' : 'Directors'))) }}</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered compact-table">
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
                                @if(!empty($application->entityDetails->additional_data['authorized_persons']))
                                    <h6 class="mb-2">Authorized Persons</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered compact-table">
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
                                                                <embed src="{{ asset('storage/' . $person['letter']) }}" type="application/pdf" width="100%" height="400px">
                                                            @elseif(in_array(strtolower(pathinfo($person['letter'], PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg']))
                                                                <img src="{{ asset('storage/' . $person['letter']) }}" alt="Authorization Letter">
                                                            @else
                                                                <a href="{{ asset('storage/' . $person['letter']) }}" target="_blank" class="btn btn-sm btn-primary">Download Authorization Letter</a>
                                                            @endif
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                                                                <embed src="{{ asset('storage/' . $person['aadhar']) }}" type="application/pdf" width="100%" height="400px">
                                                            @elseif(in_array(strtolower(pathinfo($person['aadhar'], PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg']))
                                                                <img src="{{ asset('storage/' . $person['aadhar']) }}" alt="Aadhar Document">
                                                            @else
                                                                <a href="{{ asset('storage/' . $person['aadhar']) }}" target="_blank" class="btn btn-sm btn-primary">Download Aadhar</a>
                                                            @endif
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
                            @endif
                        </div>
                    @elseif($application->current_progress_step < 2)
                        <div class="alert alert-warning">
                            Entity Details not available. Form not progressed beyond Step 1.
                        </div>
                    @endif

                    <!-- Step 3: Distribution Details -->
                    @if($application->current_progress_step >= 3 && isset($application->distributionDetail))
                        <div id="distribution-details" class="form-section mb-3">
                            <h5 class="mb-2">Distribution Details</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered compact-table">
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
                                            <td><strong>Area Covered</strong></td>
                                            <td>{{ !empty($areaCovered) ? implode(', ', $areaCovered) : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Appointment Type</strong></td>
                                            <td>{{ $application->distributionDetail->appointment_type ?? 'N/A' }}</td>
                                        </tr>
                                        @if($application->distributionDetail && $application->distributionDetail->appointment_type === 'replacement')
                                            <tr>
                                                <td><strong>Reason for Replacement</strong></td>
                                                <td>{{ $application->distributionDetail->replacement_reason ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Commitment to Recover Outstanding</strong></td>
                                                <td>{{ $application->distributionDetail->outstanding_recovery ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Name of Previous Firm</strong></td>
                                                <td>{{ $application->distributionDetail->previous_firm_name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Code of Previous Firm</strong></td>
                                                <td>{{ $application->distributionDetail->previous_firm_code ?? 'N/A' }}</td>
                                            </tr>
                                        @elseif($application->distributionDetail && $application->distributionDetail->appointment_type === 'new_area')
                                            <tr>
                                                <td><strong>Earlier Distributor</strong></td>
                                                <td>{{ $application->distributionDetail->earlier_distributor ?? 'N/A' }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Step 4: Business Plan -->
                    @if($application->current_progress_step >= 4 && isset($application->businessPlans) && $application->businessPlans->isNotEmpty())
                        <div id="business-plan" class="form-section mb-3">
                            <h5 class="mb-2">Business Plan (Next Two Years)</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered compact-table">
                                    <thead>
                                        <tr>
                                            <th>Crop</th>
                                            <th>FY 2025-26 (MT)</th>
                                            <th>FY 2026-27 (MT)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $year2025 = \App\Models\Year::where('period', '2025-26')->first();
                                            $year2026 = \App\Models\Year::where('period', '2026-27')->first();
                                        @endphp
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

                    <!-- Step 5: Financial & Operational Information -->
                    @if($application->current_progress_step >= 5 && isset($application->financialInfo))
                        <div id="financial-info" class="form-section mb-3">
                            <h5 class="mb-2">Financial & Operational Information</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered compact-table">
                                    <tbody>
                                        <tr>
                                            <td><strong>Net Worth (Previous FY)</strong></td>
                                            <td>{{ $application->financialInfo->net_worth ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Shop Ownership</strong></td>
                                            <td>{{ $application->financialInfo->shop_ownership ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Godown Area & Ownership</strong></td>
                                            <td>{{ $application->financialInfo->godown_area ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Years in Business</strong></td>
                                            <td>{{ $application->financialInfo->years_in_business ?? 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <h6 class="mb-2">Annual Turnover</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered compact-table">
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

                    <!-- Step 6: Existing Distributorships -->
                    @if($application->current_progress_step >= 6 && isset($application->existingDistributorships))
                        <div id="existing-distributorships" class="form-section mb-3">
                            <h5 class="mb-2">Existing Distributorships (Agro Inputs)</h5>
                            @if($application->existingDistributorships->isEmpty())
                                <p class="text-muted">No existing distributorships provided.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-bordered compact-table">
                                        <thead>
                                            <tr>
                                                <th>Company Name</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($application->existingDistributorships as $distributorship)
                                                <tr>
                                                    <td>{{ $distributorship->company_name ?? 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Step 7: Bank Details -->
                    @if($application->current_progress_step >= 7 && isset($application->bankDetail))
                        <div id="bank-details" class="form-section mb-3">
                            <h5 class="mb-2">Bank Details</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered compact-table">
                                    <tbody>
                                        <tr>
                                            <td><strong>Financial Status</strong></td>
                                            <td>{{ $application->bankDetail->financial_status ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>No. of Retailers Dealt With</strong></td>
                                            <td>{{ $application->bankDetail->retailer_count ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Bank Name</strong></td>
                                            <td>{{ $application->bankDetail->bank_name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Account Holder Name</strong></td>
                                            <td>{{ $application->bankDetail->account_holder ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Account Number</strong></td>
                                            <td>{{ $application->bankDetail->account_number ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>IFSC Code</strong></td>
                                            <td>{{ $application->bankDetail->ifsc_code ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Account Type</strong></td>
                                            <td>{{ $application->bankDetail->account_type ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Relationship Duration (Years)</strong></td>
                                            <td>{{ $application->bankDetail->relationship_duration ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>OD Limit (if any)</strong></td>
                                            <td>{{ $application->bankDetail->od_limit ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>OD Security</strong></td>
                                            <td>{{ $application->bankDetail->od_security ?? 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Step 8: Declarations -->
                    @if($application->current_progress_step >= 8 && isset($application->declarations))
                        <div id="declarations" class="form-section mb-3">
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
                            <div class="table-responsive">
                                <table class="table table-bordered compact-table">
                                    <thead>
                                        <tr>
                                            <th>Question</th>
                                            <th>Answer</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
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
                                            <tr>
                                                <td>{{ $config['label'] }}</td>
                                                <td>{{ $hasIssue ? 'Yes' : 'No' }}</td>
                                                <td>
                                                    @if($hasIssue && !empty($details))
                                                        @if(isset($config['details_field']))
                                                            {{ $details[$config['details_field']] ?? 'N/A' }}
                                                        @elseif(isset($config['details_fields']))
                                                            @foreach($config['details_fields'] as $field => $label)
                                                                {{ $label }}: {{ $details[$field] ?? 'N/A' }}<br>
                                                            @endforeach
                                                        @endif
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        @php
                                            $truthful = $application->declarations->where('question_key', 'declaration_truthful')->first();
                                            $update = $application->declarations->where('question_key', 'declaration_update')->first();
                                        @endphp
                                        <tr>
                                            <td>a. I/We hereby solemnly affirm the truthfulness and completeness of the foregoing information and agree to be bound by all terms and conditions of the appointment/agreement with the Company.</td>
                                            <td>{{ $truthful && $truthful->has_issue ? 'Affirmed' : 'Not Affirmed' }}</td>
                                            <td>N/A</td>
                                        </tr>
                                        <tr>
                                            <td>b. I/We undertake to inform the company of any changes to the information provided herein within a period of 7 days, accompanied by relevant documentation.</td>
                                            <td>{{ $update && $update->has_issue ? 'Agreed' : 'Not Agreed' }}</td>
                                            <td>N/A</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- Approval Logs -->
                    @if($application->status !== 'draft' && isset($application->approvalLogs))
                        <div id="approval-logs" class="form-section mb-3">
                            <h5 class="mb-2">Approval Logs</h5>
                            @if($application->approvalLogs->isEmpty())
                                <p class="text-muted">No approval logs available.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-bordered compact-table">
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

                    <!-- Take Action Section -->
                    @if(auth()->user()->emp_id === $application->current_approver_id)
                        {{--<div id="take-action" class="form-section mb-3">
                            <h5 class="mb-2">Take Action</h5>
                            <form id="approve-form" action="{{ route('approvals.approve', $application) }}" method="POST" class="d-inline">
                                @csrf
                                <div class="mb-3">
                                    <label for="approveRemarks" class="form-label">Remarks (Optional)</label>
                                    <textarea name="remarks" id="approveRemarks" class="form-control" rows="2"></textarea>
                                </div>
                                <button type="submit" id="approve-button" class="btn btn-sm btn-success">Approve</button>
                            </form>
                            <button type="button" class="btn btn-sm btn-warning ms-2" data-bs-toggle="modal" data-bs-target="#revertModal">Revert</button>
                            <button type="button" class="btn btn-sm btn-secondary ms-2" data-bs-toggle="modal" data-bs-target="#holdModal">Hold</button>
                            <button type="button" class="btn btn-sm btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>

                            <!-- Revert Modal -->
                            <div class="modal fade" id="revertModal" tabindex="-1" aria-labelledby="revertModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('approvals.revert', $application) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="revertModalLabel">Revert Application</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="revertRemarks" class="form-label">Reason for Revert *</label>
                                                    <textarea name="remarks" id="revertRemarks" class="form-control" rows="3" required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-sm btn-warning">Confirm Revert</button>
                                            </div>
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
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="holdModalLabel">Put Application On Hold</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="holdRemarks" class="form-label">Reason for Hold *</label>
                                                    <textarea name="remarks" id="holdRemarks" class="form-control" rows="3" required></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="followUpDate" class="form-label">Follow-up Date *</label>
                                                    <input type="date" name="follow_up_date" id="followUpDate" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-sm btn-secondary">Confirm Hold</button>
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
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="rejectModalLabel">Reject Application</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="rejectRemarks" class="form-label">Reason for Rejection *</label>
                                                    <textarea name="remarks" id="rejectRemarks" class="form-control" rows="3" required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-sm btn-danger">Confirm Rejection</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>--}}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('approve-form')?.addEventListener('submit', function() {
        document.querySelectorAll('.form-section button').forEach(button => {
            button.disabled = true;
        });
    });
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endpush
@endsection