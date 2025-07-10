@php
    use Illuminate\Support\Str;
@endphp

<div class="form-section">
    <h3>Basic Information</h3>
    <table class="table">
        <tr>
            <td class="label">Territory:</td>
            <td class="value">{{ $application->territoryDetail->territory_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Crop Vertical:</td>
            <td class="value">
                @if($application->crop_vertical == '1')
                    Field Crop
                @elseif($application->crop_vertical == '2')
                    Veg Crop
                @else
                    N/A
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Region:</td>
            <td class="value">{{ $application->regionDetail->region_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Zone:</td>
            <td class="value">{{ $application->zoneDetail->zone_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">District:</td>
            <td class="value">{{ $application->district && isset($districts[$application->district]) ? $districts[$application->district]->district_name : 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">State:</td>
            <td class="value">{{ $application->state && isset($states[$application->state]) ? $states[$application->state]->state_name : 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Business Unit:</td>
            <td class="value">{{ $application->businessUnit->business_unit_name ?? 'Not Assigned' }}</td>
        </tr>
    </table>
</div>

<div class="form-section">
    <h3>Entity Details</h3>
    @if($application->entityDetails)
        <table class="table">
            <tr>
                <td class="label">Establishment Name:</td>
                <td class="value">{{ $application->entityDetails->establishment_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Entity Type:</td>
                <td class="value">{{ $application->entityDetails->entity_type ? Str::title(str_replace('_', ' ', $application->entityDetails->entity_type)) : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Business Place/Shop Address:</td>
                <td class="value">
                    {{ $application->entityDetails->house_no ?? 'N/A' }},
                    {{ $application->entityDetails->landmark ?? 'N/A' }},
                    {{ $application->entityDetails->city ?? 'N/A' }},
                    {{ $application->entityDetails->district_id && isset($districts[$application->entityDetails->district_id]) ? $districts[$application->entityDetails->district_id]->district_name : 'N/A' }},
                    {{ $application->entityDetails->state_id && isset($states[$application->entityDetails->state_id]) ? $states[$application->entityDetails->state_id]->state_name : 'N/A' }},
                    {{ $application->entityDetails->pincode ?? 'N/A' }},
                    {{ $application->entityDetails->country_id && isset($countries[$application->entityDetails->country_id]) ? $countries[$application->entityDetails->country_id]->country_name : 'N/A' }}
                </td>
            </tr>
            <tr>
                <td class="label">Mobile Number:</td>
                <td class="value">{{ $application->entityDetails->mobile ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">E-mail Address:</td>
                <td class="value">{{ $application->entityDetails->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">PAN Number:</td>
                <td class="value">{{ $application->entityDetails->pan_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">GST (Applicable or Not):</td>
                <td class="value">{{ $application->entityDetails->gst_applicable ? Str::title($application->entityDetails->gst_applicable) : 'N/A' }}</td>
            </tr>
            @if($application->entityDetails->gst_applicable == 'yes')
                <tr>
                    <td class="label">GST Number:</td>
                    <td class="value">{{ $application->entityDetails->gst_number ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">GST Validity:</td>
                    <td class="value">
                        @php
                            $additionalData = is_array($application->entityDetails->additional_data) ? $application->entityDetails->additional_data : json_decode($application->entityDetails->additional_data, true) ?? [];
                            $gstValidity = $additionalData['gst_validity'] ?? 'N/A';
                        @endphp
                        {{ $gstValidity }}
                    </td>
                </tr>
            @endif
            <tr>
                <td class="label">Seed License Number:</td>
                <td class="value">{{ $application->entityDetails->seed_license ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Seed License Validity:</td>
                <td class="value">
                    @php
                        $additionalData = is_array($application->entityDetails->additional_data) ? $application->entityDetails->additional_data : json_decode($application->entityDetails->additional_data, true) ?? [];
                        $seedLicenseValidity = $additionalData['seed_license_validity'] ?? 'N/A';
                    @endphp
                    {{ $seedLicenseValidity }}
                </td>
            </tr>
            <tr>
                <td class="label">TAN Number:</td>
                <td class="value">
                    @php
                        $additionalData = is_array($application->entityDetails->additional_data) ? $application->entityDetails->additional_data : json_decode($application->entityDetails->additional_data, true) ?? [];
                        $tanNumber = $additionalData['tan_number'] ?? 'N/A';
                    @endphp
                    {{ $tanNumber }}
                </td>
            </tr>
        </table>

        <h4>Ownership Details</h4>
        @php
            $entityType = $application->entityDetails->entity_type;
            $additionalData = is_array($application->entityDetails->additional_data) ? $application->entityDetails->additional_data : json_decode($application->entityDetails->additional_data, true) ?? [];
        @endphp
        @if($entityType === 'sole_proprietorship' && isset($additionalData['proprietor']) && is_array($additionalData['proprietor']))
            <h5>In Case of Sole Proprietorship</h5>
            <table class="table">
                <tr>
                    <td class="label">Name of Proprietor:</td>
                    <td class="value">{{ $additionalData['proprietor']['name'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Date of Birth:</td>
                    <td class="value">{{ $additionalData['proprietor']['dob'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Father's/Husband's Name:</td>
                    <td class="value">{{ $additionalData['proprietor']['father_name'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Permanent Address of Proprietor:</td>
                    <td class="value">
                        {{ $additionalData['proprietor']['address'] ?? 'N/A' }},
                        {{ $additionalData['proprietor']['landmark'] ?? 'N/A' }},
                        {{ $additionalData['proprietor']['city'] ?? 'N/A' }},
                        {{ $additionalData['proprietor']['district'] ?? 'N/A' }},
                        {{ $additionalData['proprietor']['state'] ?? 'N/A' }},
                        {{ $additionalData['proprietor']['pincode'] ?? 'N/A' }},
                        {{ $additionalData['proprietor']['country'] ?? 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Mobile Number:</td>
                    <td class="value">{{ $additionalData['proprietor']['contact'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">E-mail Address:</td>
                    <td class="value">{{ $additionalData['proprietor']['email'] ?? 'N/A' }}</td>
                </tr>
            </table>
        @elseif(in_array($entityType, ['partnership', 'llp', 'private_company', 'public_company', 'cooperative_society', 'trust']) && isset($additionalData['partners']) && is_array($additionalData['partners']))
            <h5>In Case of {{ Str::title($entityType) }}</h5>
            @foreach($additionalData['partners'] as $index => $partner)
                <p><strong>Partner/Director/Trustee {{ $index + 1 }}:</strong></p>
                <table class="table">
                    <tr>
                        <td class="label">Name:</td>
                        <td class="value">{{ $partner['name'] ?? 'N/A' }}</td>
                    </tr>
                    @if(isset($partner['father_name']))
                        <tr>
                            <td class="label">Father's/Husband's Name:</td>
                            <td class="value">{{ $partner['father_name'] }}</td>
                        </tr>
                    @endif
                    @if(isset($partner['dpin_number']))
                        <tr>
                            <td class="label">DPIN Number:</td>
                            <td class="value">{{ $partner['dpin_number'] }}</td>
                        </tr>
                    @endif
                    @if(isset($partner['cin_number']))
                        <tr>
                            <td class="label">CIN Number:</td>
                            <td class="value">{{ $partner['cin_number'] }}</td>
                        </tr>
                    @endif
                    @if(isset($partner['designation']))
                        <tr>
                            <td class="label">Designation:</td>
                            <td class="value">{{ $partner['designation'] }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="label">Contact:</td>
                        <td class="value">{{ $partner['contact'] ?? 'N/A' }}</td>
                    </tr>
                    @if(isset($partner['email']))
                        <tr>
                            <td class="label">Email:</td>
                            <td class="value">{{ $partner['email'] }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="label">Full Address:</td>
                        <td class="value">{{ $partner['address'] ?? 'N/A' }}</td>
                    </tr>
                </table>
            @endforeach
        @else
            <table class="table">
                <tr>
                    <td class="label">Ownership Details:</td>
                    <td class="value">No specific owner/partner/directors details available.</td>
                </tr>
            </table>
        @endif

        @if(isset($additionalData['authorized_persons']) && is_array($additionalData['authorized_persons']) && !empty(array_filter($additionalData['authorized_persons'])))
            <h5>Authorized Persons</h5>
            @foreach($additionalData['authorized_persons'] as $index => $person)
                <p><strong>Authorized Person {{ $index + 1 }}:</strong></p>
                <table class="table">
                    <tr>
                        <td class="label">Name:</td>
                        <td class="value">{{ $person['name'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Contact:</td>
                        <td class="value">{{ $person['contact'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Email:</td>
                        <td class="value">{{ $person['email'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Relation with Owner/Entity:</td>
                        <td class="value">{{ $person['relation'] ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Full Address:</td>
                        <td class="value">{{ $person['address'] ?? 'N/A' }}</td>
                    </tr>
                </table>
            @endforeach
        @else
            <table class="table">
                <tr>
                    <td class="label">Authorized Persons:</td>
                    <td class="value">No authorized persons provided.</td>
                </tr>
            </table>
        @endif
    @else
        <table class="table">
            <tr>
                <td class="label">Entity Details:</td>
                <td class="value">No entity details provided.</td>
            </tr>
        </table>
    @endif
</div>

<div class="form-section">
    <h3>Bank Details</h3>
    @if($application->bankDetail)
        <table class="table">
            <tr>
                <td class="label">Name of the Bank:</td>
                <td class="value">{{ $application->bankDetail->bank_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Name of Bank Account Holder:</td>
                <td class="value">{{ $application->bankDetail->account_holder ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Account Number:</td>
                <td class="value">{{ $application->bankDetail->account_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">IFSC Code of Bank:</td>
                <td class="value">{{ $application->bankDetail->ifsc_code ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Account Type:</td>
                <td class="value">{{ $application->bankDetail->account_type ? Str::title($application->bankDetail->account_type) : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Relationship Duration:</td>
                <td class="value">{{ $application->bankDetail->relationship_duration ?? 'N/A' }} years</td>
            </tr>
            <tr>
                <td class="label">OD Limit:</td>
                <td class="value">{{ $application->bankDetail->od_limit ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">OD Security:</td>
                <td class="value">{{ $application->bankDetail->od_security ?? 'N/A' }}</td>
            </tr>
        </table>
    @else
        <table class="table">
            <tr>
                <td class="label">Bank Details:</td>
                <td class="value">No bank details provided.</td>
            </tr>
        </table>
    @endif
</div>

<div class="form-section">
    <h3>Distribution Details</h3>
    @if($application->distributionDetail)
        <table class="table">
            <tr>
                <td class="label">Area to be Covered:</td>
                <td class="value">
                    {{ $application->distributionDetail->area_covered ? implode(', ', is_array($application->distributionDetail->area_covered) ? $application->distributionDetail->area_covered : json_decode($application->distributionDetail->area_covered, true) ?? []) : 'N/A' }}
                </td>
            </tr>
            <tr>
                <td class="label">Appointment Type:</td>
                <td class="value">{{ $application->distributionDetail->appointment_type ? Str::title(str_replace('_', ' ', $application->distributionDetail->appointment_type)) : 'N/A' }}</td>
            </tr>
            @if($application->distributionDetail->appointment_type === 'replacement')
                <tr>
                    <td class="label">Reason:</td>
                    <td class="value">{{ $application->distributionDetail->replacement_reason ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Commitment to Recover Outstanding:</td>
                    <td class="value">{{ $application->distributionDetail->outstanding_recovery ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Name of Previous Firm:</td>
                    <td class="value">{{ $application->distributionDetail->previous_firm_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Code of Previous Firm:</td>
                    <td class="value">{{ $application->distributionDetail->previous_firm_code ?? 'N/A' }}</td>
                </tr>
            @elseif($application->distributionDetail->appointment_type === 'new_area')
                <tr>
                    <td class="label">Earlier Distributor:</td>
                    <td class="value">{{ $application->distributionDetail->earlier_distributor ?? 'N/A' }}</td>
                </tr>
            @endif
        </table>
    @else
        <table class="table">
            <tr>
                <td class="label">Distribution Details:</td>
                <td class="value">No distribution details provided.</td>
            </tr>
        </table>
    @endif
</div>

<div class="form-section">
    <h3>Business Plan (Next Two Years)</h3>
    @if($application->businessPlans->isNotEmpty())
        <table class="table">
            <tr>
                <th>Crop</th>
                <th>FY 2025-26 (in MT)</th>
                <th>FY 2026-27 (in MT)</th>
            </tr>
            @foreach($application->businessPlans as $plan)
                <tr>
                    <td>{{ $plan->crop ?? 'N/A' }}</td>
                    <td>
                        @php
                            $yearlyTargets = is_array($plan->yearly_targets) ? $plan->yearly_targets : json_decode($plan->yearly_targets, true) ?? [];
                            $year2025 = $years->where('period', '2025-26')->first()->id ?? '';
                        @endphp
                        {{ isset($yearlyTargets[$year2025]) ? $yearlyTargets[$year2025] : 'N/A' }}
                    </td>
                    <td>
                        @php
                            $year2026 = $years->where('period', '2026-27')->first()->id ?? '';
                        @endphp
                        {{ isset($yearlyTargets[$year2026]) ? $yearlyTargets[$year2026] : 'N/A' }}
                    </td>
                </tr>
            @endforeach
        </table>
    @else
        <table class="table">
            <tr>
                <td class="label">Business Plan:</td>
                <td class="value">No business plan provided.</td>
            </tr>
        </table>
    @endif
</div>

<div class="form-section">
    <h3>Financial & Operational Information</h3>
    @if($application->financialInfo)
        <table class="table">
            <tr>
                <td class="label">Net Worth (Previous FY):</td>
                <td class="value">{{ $application->financialInfo->net_worth ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Shop Ownership:</td>
                <td class="value">{{ $application->financialInfo->shop_ownership ? Str::title($application->financialInfo->shop_ownership) : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Godown Area & Ownership:</td>
                <td class="value">{{ $application->financialInfo->godown_area ? $application->financialInfo->godown_area . ' – ' . Str::title($application->financialInfo->shop_ownership) : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Years in Business:</td>
                <td class="value">{{ $application->financialInfo->years_in_business ?? 'N/A' }}</td>
            </tr>
        </table>
        <h4>Annual Turnover</h4>
        @if($application->financialInfo->annual_turnover)
            <table class="table">
                <tr>
                    <th>Financial Year</th>
                    <th>Net Turnover</th>
                </tr>
                @foreach(is_array($application->financialInfo->annual_turnover) ? $application->financialInfo->annual_turnover : json_decode($application->financialInfo->annual_turnover, true) ?? [] as $year => $amount)
                    <tr>
                        <td>{{ $year }}</td>
                        <td>{{ $amount }}</td>
                    </tr>
                @endforeach
            </table>
        @else
            <table class="table">
                <tr>
                    <td class="label">Annual Turnover:</td>
                    <td class="value">No annual turnover data provided.</td>
                </tr>
            </table>
        @endif
    @else
        <table class="table">
            <tr>
                <td class="label">Financial Information:</td>
                <td class="value">No financial information provided.</td>
            </tr>
        </table>
    @endif
</div>

<div class="form-section">
    <h3>Existing Distributorships (Agro Inputs)</h3>
    @if($application->existingDistributorships->isNotEmpty())
        <table class="table">
            <tr>
                <th>Sr. No</th>
                <th>Company Name</th>
            </tr>
            @foreach($application->existingDistributorships as $index => $distributorship)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $distributorship->company_name ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </table>
    @else
        <table class="table">
            <tr>
                <td class="label">Existing Distributorships:</td>
                <td class="value">No existing distributorships provided.</td>
            </tr>
        </table>
    @endif
</div>

<div class="form-section">
    <h3>Financial Status & Banking Information (Additional)</h3>
    @if($application->bankDetail)
        <table class="table">
            <tr>
                <td class="label">Financial Status:</td>
                <td class="value">{{ $application->bankDetail->financial_status ? Str::title($application->bankDetail->financial_status) : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">No. of Retailers Dealt With :</td>
                <td class="value">{{ $application->bankDetail->retailer_count ?? 'N/A' }}</td>
            </tr>
             <tr>
                <td class="label">Bank Name :</td>
                <td class="value">{{ $application->bankDetail->bank_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Account Holder :</td>
                <td class="value">{{ $application->bankDetail->account_holder ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Account Number :</td>
                <td class="value">{{ $application->bankDetail->account_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">IFSC Code :</td>
                <td class="value">{{ $application->bankDetail->ifsc_code ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Account Type :</td>
                <td class="value">{{ $application->bankDetail->account_type ?? 'N/A' }}</td>
            </tr>
             <tr>
                <td class="label">Relationship Duration :</td>
                <td class="value">{{ $application->bankDetail->relationship_duration ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">OD Limit :</td>
                <td class="value">{{ $application->bankDetail->od_limit ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">OD Security :</td>
                <td class="value">{{ $application->bankDetail->od_security ?? 'N/A' }}</td>
            </tr>
        </table>
    @else
        <table class="table">
            <tr>
                <td class="label">Financial Status & Banking Information :</td>
                <td class="value">No additional banking information provided.</td>
            </tr>
        </table>
    @endif
</div>

<div class="form-section">
    <h3>Declarations </h3>
    @if($application->declarations->isNotEmpty())
        @php
            $declarationQuestions = [
                'is_other_distributor' => 'Whether the Distributor is an Agent/Distributor of any other Company?',
                'has_sister_concern' => 'Whether the Distributor has any sister concern or affiliated entity other than the one applying for this distributorship?',
                'has_question_c' => 'Whether the Distributor is acting as an Agent/Distributor for any other entities in the distribution of similar crops?',
                'has_question_d' => 'Whether the Distributor is a partner, relative, or otherwise associated with any entity engaged in the business of agro inputs?',
                'has_question_e' => 'Whether the Distributor has previously acted as an Agent/Distributor of VNR Seeds and is again applying for a Distributorship?',
                'has_disputed_dues' => 'Whether any disputed dues are payable by the Distributor to the other Company/Bank/Financial Institution?',
                'has_question_g' => 'Whether the Distributor has ceased to be Agent/Distributor of any other company in the last twelve months?',
                'has_question_h' => 'Whether the Distributor’s relative is connected in any way with VNR Seeds and any other Seed Company?',
                'has_question_i' => 'Whether the Distributor is involved in any other capacity with the Company apart from this application?',
                'has_question_j' => 'Whether the Distributor has been referred by any Distributors or other parties associated with the Company?',
                'has_question_k' => 'Whether the Distributor is currently marketing or selling products under its own brand name?',
                'has_question_l' => 'Whether the Distributor has been employed in the agro-input industry at any point during the past 5 years?',
                'declaration_truthful' => 'I/We hereby solemnly affirm the truthfulness and completeness of the foregoing information and agree to be bound by all terms and conditions of the appointment/agreement with the Company.',
                'declaration_update' => 'I/We undertake to inform the company of any changes to the information provided herein within a period of 7 days, accompanied by relevant documentation.'
            ];
        @endphp
        @foreach($application->declarations as $declaration)
            @php
                $questionText = $declarationQuestions[$declaration->question_key] ?? Str::title(str_replace('_', ' ', $declaration->question_key));
                $hasIssue = (bool) $declaration->has_issue;
                $details = is_array($declaration->details) ? $declaration->details : json_decode($declaration->details, true) ?? [];
            @endphp
            <table class="table">
                <tr>
                    <td class="q_label">{{ $questionText }}</td>
                    <td class="q_value">{{ $hasIssue ? 'Yes' : 'No' }}</td>
                </tr>
                @if($hasIssue && !empty($details))
                    @if($declaration->question_key === 'has_disputed_dues')
                        <tr>
                            <td class="q_label">Amount:</td>
                            <td class="q_value">{{ $details['amount'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="q_label">Nature of the Dispute:</td>
                            <td class="q_value">{{ $details['nature'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="q_label">Year of Dispute:</td>
                            <td class="q_value">{{ $details['year'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="q_label">Present Position:</td>
                            <td class="q_value">{{ $details['present_position'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="q_label">Reason for Default:</td>
                            <td class="q_value">{{ $details['reason'] ?? 'N/A' }}</td>
                        </tr>
                    @else
                        @foreach($details as $detailKey => $detailValue)
                            <tr>
                                <td class="q_label">{{ Str::title(str_replace('_', ' ', $detailKey)) }}:</td>
                                <td class="q_value">{{ $detailValue }}</td>
                            </tr>
                        @endforeach
                    @endif
                @endif
            </table>
        @endforeach
    @else
        <table class="table">
            <tr>
                <td class="label">Declarations:</td>
                <td class="value">No declarations provided.</td>
            </tr>
        </table>
    @endif
</div>

{{--<div class="form-section">
    <h3>Declaration </h3>
    <div class="declaration-text">
        <p>I/We hereby solemnly affirm the truthfulness and completeness of the foregoing information and agree to be bound by all terms and conditions of the appointment/agreement with the Company. </p>
        <p>I/We undertake to inform the company of any changes to the information provided herein within a period of 7 days, accompanied by relevant documentation.</p>
    </div>
    <table class="table">
        <tr>
            <td class="label">Form Filled By:</td>
            <td class="value">{{ $application->entityDetails->establishment_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Date:</td>
            <td class="value">{{ now()->format('d-m-Y') }}</td>
        </tr>
    </table>
</div>--}}