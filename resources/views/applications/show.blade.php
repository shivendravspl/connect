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
        font-size: 0.8rem;
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
    <h2 class="mb-3">Distributor Application - View (ID: {{ $application->application_code }})</h2>
    <div class="card">
        <div class="card-body p-2">
            <!-- Application Status -->
            <div class="mb-3">
                <h5 class="mb-2">Application Status: <span class="badge bg-{{ $application->status_badge }}">{{ ucfirst($application->status) }}</span></h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <tbody>
                            <tr>
                                <th>Submitted On</th>
                                <td>{{ $application->created_at->format('d-M-Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Last Updated</th>
                                <td>{{ $application->updated_at->format('d-M-Y H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Step 1: Basic Details -->
            <div id="basic-details" class="form-section">
                <h5 class="mb-3">Basic Details</h5>
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

            <!-- Step 2: Entity Details -->
            @if(isset($application->entityDetails))
            <div id="entity-details" class="form-section">
                <h5 class="mb-3">Entity Details</h5>
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

                <!-- Documents -->
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
                @if(!empty($documents))
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
                                <td class="document-link">
                                    @if($doc['path'])
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#documentModal{{ $index }}">View</button>
                                    @else
                                    N/A
                                    @endif
                                </td>
                                <td>{{ $doc['status'] ?? 'Pending' }}</td>
                                <td>{{ isset($doc['verified']) && $doc['verified'] ? 'Yes' : 'No' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Document Modals -->
                @foreach($documents as $index => $doc)
                @if($doc['path'])
                <div class="modal fade" id="documentModal{{ $index }}" tabindex="-1" aria-labelledby="documentModalLabel{{ $index }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header p-2">
                                <h5 class="modal-title" id="documentModalLabel{{ $index }}">{{ ucfirst($doc['type'] ?? 'Document') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-2">
                                <iframe src="{{ Storage::url($doc['path']) }}" title="{{ $doc['type'] ?? 'Document' }}"></iframe>
                            </div>
                            <div class="modal-footer p-2">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
                @endif

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
            </div>
            @endif

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
                                @if (!empty($person['letter']))
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#authLetterModal{{ $index }}">View</button>
                                @else
                                N/A
                                @endif
                            </td>
                            <td>
                                @if (!empty($person['aadhar']))
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#authAadharModal{{ $index }}">View</button>
                                @else
                                N/A
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Authorized Persons Modals -->
            @foreach($application->entityDetails->additional_data['authorized_persons'] as $index => $person)
            @if(!empty($person['letter']))
            <div class="modal fade" id="authLetterModal{{ $index }}" tabindex="-1" aria-labelledby="authLetterModalLabel{{ $index }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header p-2">
                            <h5 class="modal-title" id="authLetterModalLabel{{ $index }}">Authorization Letter</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-2">
                            <iframe src="{{ asset('storage/' . $person['letter']) }}" title="Authorization Letter"></iframe>
                        </div>
                        <div class="modal-footer p-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if(!empty($person['aadhar']))
            <div class="modal fade" id="authAadharModal{{ $index }}" tabindex="-1" aria-labelledby="authAadharModalLabel{{ $index }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header p-2">
                            <h5 class="modal-title" id="authAadharModalLabel{{ $index }}">Aadhar Document</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-2">
                            <iframe src="{{ asset('storage/' . $person['aadhar']) }}" title="Aadhar Document"></iframe>
                        </div>
                        <div class="modal-footer p-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
            @endif

            <!-- Step 3: Distribution Details -->
            @if(isset($application->distributionDetail))
            <div id="distribution-details" class="form-section">
                <h5 class="mb-3">Distribution Details</h5>
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

            <!-- Step 4: Business Plan -->
            @if(isset($application->businessPlans) && !$application->businessPlans->isEmpty())
            <div id="business-plan" class="form-section">
                <h5 class="mb-3">Business Plan (Next Two Years)</h5>
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
            @if(isset($application->financialInfo))
            <div id="financial-info" class="form-section">
                <h5 class="mb-3">Financial & Operational Information</h5>
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

            <!-- Step 6: Existing Distributorships -->
            @if(isset($application->existingDistributorships) && !$application->existingDistributorships->isEmpty())
            <div id="existing-distributorships" class="form-section">
                <h5 class="mb-3">Existing Distributorships (Agro Inputs)</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
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
            </div>
            @endif

            <!-- Step 7: Bank Details -->
            @if(isset($application->bankDetail))
            <div id="bank-details" class="form-section">
                <h5 class="mb-3">Bank Details</h5>
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

            <!-- Step 8: Declarations -->
            @if(isset($application->declarations) && !$application->declarations->isEmpty())
            <div id="declarations" class="form-section">
                <h5 class="mb-3">Declarations</h5>
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
                <div class="card mb-2">
                    <div class="card-body p-2">
                        <h6 class="mb-2" style="font-size: 0.9rem;">{{ $config['label'] }}</h6>
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
                    <div class="card-body p-2">
                        <h6 class="mb-2" style="font-size: 0.9rem;">Declaration</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <tbody>
                                    @php
                                        $truthful = $application->declarations->where('question_key', 'declaration_truthful')->first();
                                        $update = $application->declarations->where('question_key', 'declaration_update')->first();
                                    @endphp
                                    <tr>
                                        <th>a. Truthfulness and Completeness</th>
                                        <td>{{ $truthful && $truthful->has_issue ? 'Affirmed' : 'Not Affirmed' }}</td>
                                    </tr>
                                    <tr>
                                        <th>b. Update Commitment</th>
                                        <td>{{ $update && $update->has_issue ? 'Agreed' : 'Not Agreed' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Approval Logs -->
            @if(isset($application->approvalLogs) && !$application->approvalLogs->isEmpty())
            <div id="approval-logs" class="form-section">
                <h5 class="mb-3">Approval Logs</h5>
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
            @endif
        </div>
    </div>
    @if(auth()->user()->emp_id === $application->current_approver_id && !auth()->user()->hasRole('Mis User'))
    <div class="card mt-3">
        <div class="card-header p-2">
            <h5 class="mb-0">Take Action</h5>
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
                        <h5 class="modal-title" id="revertModalLabel">Revert Application</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-2">
                        <div class="mb-2">
                            <label for="revertRemarks" class="form-label">Reason for Revert *</label>
                            <textarea name="remarks" id="revertRemarks" class="form-control" rows="3" required style="font-size: 0.8rem;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer p-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning btn-sm">Confirm Revert</button>
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
                    <div class="modal-header p-2">
                        <h5 class="modal-title" id="holdModalLabel">Put Application On Hold</h5>
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
                        <h5 class="modal-title" id="rejectModalLabel">Reject Application</h5>
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
    </div>
    @endif
</div>
@endsection
