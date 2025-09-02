<div class="review-section">
    <h3>Review Your Application</h3>
    <p>Please review the details below before submitting your application.</p>

    <div class="card mb-3">
        <div class="card-header">
            <h5>Basic Details</h5>
        </div>
        <div class="card-body">
            <p><strong>Business Unit:</strong> {{ $application->businessUnit->business_unit_name ?? 'N/A' }}</p>
            <p><strong>Zone:</strong> {{ $application->zoneDetail->zone_name ?? 'N/A' }}</p>
            <p><strong>Region:</strong> {{ $application->regionDetail->region_name ?? 'N/A' }}</p>
            <p><strong>Territory:</strong> {{ $application->territoryDetail->territory_name ?? 'N/A' }}</p>
            <p><strong>Crop Vertical:</strong>
                @if($application->crop_vertical == '1')
                    Field Crop
                @elseif($application->crop_vertical == '2')
                    Veg Crop
                @else
                    N/A
                @endif
            </p>
            {{--<p><strong>State:</strong> {{ $application->state ? \DB::table('core_state')->where('id', $application->state)->value('state_name') : 'N/A' }}</p>
            <p><strong>District:</strong> {{ $application->district ? \DB::table('core_district')->where('id', $application->district)->value('district_name') : 'N/A' }}</p>--}}
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5>Entity Details</h5>
        </div>
        <div class="card-body">
            @if(!empty($application->entityDetails))
                <p><strong>Establishment Name:</strong> {{ $application->entityDetails->establishment_name ?? 'N/A' }}</p>
                <p><strong>Entity Type:</strong> {{ str_replace('_', ' ', Str::title($application->entityDetails->entity_type)) ?? 'N/A' }}</p>
                <p><strong>Business Address:</strong> {{ $application->entityDetails->business_address ?? 'N/A' }}</p>
                <p><strong>House No:</strong> {{ $application->entityDetails->house_no ?? 'N/A' }}</p>
                <p><strong>Landmark:</strong> {{ $application->entityDetails->landmark ?? 'N/A' }}</p>
                <p><strong>City:</strong> {{ $application->entityDetails->city ?? 'N/A' }}</p>
                <p><strong>State:</strong> {{ $application->entityDetails->state_id ? \DB::table('core_state')->where('id', $application->entityDetails->state_id)->value('state_name') : 'N/A' }}</p>
                <p><strong>District:</strong> {{ $application->entityDetails->district_id ? \DB::table('core_district')->where('id', $application->entityDetails->district_id)->value('district_name') : 'N/A' }}</p>
                <p><strong>Country:</strong> {{ $application->entityDetails->country_id ? \DB::table('core_country')->where('id', $application->entityDetails->country_id)->value('country_name') : 'N/A' }}</p>
                <p><strong>Pincode:</strong> {{ $application->entityDetails->pincode ?? 'N/A' }}</p>
                <p><strong>Mobile:</strong> {{ $application->entityDetails->mobile ?? 'N/A' }}</p>
                <p><strong>Email:</strong> {{ $application->entityDetails->email ?? 'N/A' }}</p>
                <p><strong>PAN Number:</strong> {{ $application->entityDetails->pan_number ?? 'N/A' }}</p>
                <p><strong>GST Applicable:</strong> {{ Str::title($application->entityDetails->gst_applicable ?? 'N/A') }}</p>
                @if($application->entityDetails->gst_applicable == 'yes')
                    <p><strong>GST Number:</strong> {{ $application->entityDetails->gst_number ?? 'N/A' }}</p>
                    <p><strong>GST Validity:</strong> {{ $application->entityDetails->additional_data['gst_validity'] ?? 'N/A' }}</p>
                @endif
                <p><strong>Seed License:</strong> {{ $application->entityDetails->seed_license ?? 'N/A' }}</p>
                <p><strong>Seed License Validity:</strong> {{ $application->entityDetails->additional_data['seed_license_validity'] ?? 'N/A' }}</p>
                <p><strong>TAN Number:</strong> {{ $application->entityDetails->additional_data['tan_number'] ?? 'N/A' }}</p>

                <h6>Owner/Partner/Director Details:</h6>
                @php
                    $entityType = $application->entityDetails->entity_type;
                    $additionalData = $application->entityDetails->additional_data;
                @endphp

                @if($entityType === 'sole_proprietorship' && isset($additionalData['proprietor']))
                    <p><strong>Proprietor Name:</strong> {{ $additionalData['proprietor']['name'] ?? 'N/A' }}</p>
                    <p><strong>Proprietor DOB:</strong> {{ $additionalData['proprietor']['dob'] ?? 'N/A' }}</p>
                    <p><strong>Proprietor Father's Name:</strong> {{ $additionalData['proprietor']['father_name'] ?? 'N/A' }}</p>
                    <p><strong>Proprietor Address:</strong> {{ $additionalData['proprietor']['address'] ?? 'N/A' }}</p>
                    <p><strong>Proprietor Pincode:</strong> {{ $additionalData['proprietor']['pincode'] ?? 'N/A' }}</p>
                    <p><strong>Proprietor Country:</strong> {{ $additionalData['proprietor']['country'] ?? 'N/A' }}</p>
                @elseif(in_array($entityType, ['partnership', 'llp', 'private_company', 'public_company', 'cooperative_society', 'trust']) && isset($additionalData['partners']) && is_array($additionalData['partners']))
                    @foreach($additionalData['partners'] as $index => $partner)
                        <p><strong>{{ Str::title($entityType) }} Member {{ $index + 1 }}:</strong></p>
                        <ul>
                            <li>Name: {{ $partner['name'] ?? 'N/A' }}</li>
                            @if(isset($partner['father_name'])) <li>Father's Name: {{ $partner['father_name'] }}</li> @endif
                            @if(isset($partner['dpin_number'])) <li>DPIN Number: {{ $partner['dpin_number'] }}</li> @endif
                            @if(isset($partner['cin_number'])) <li>CIN Number: {{ $partner['cin_number'] }}</li> @endif
                            @if(isset($partner['designation'])) <li>Designation: {{ $partner['designation'] }}</li> @endif
                            <li>Contact: {{ $partner['contact'] ?? 'N/A' }}</li>
                            @if(isset($partner['email'])) <li>Email: {{ $partner['email'] }}</li> @endif
                            <li>Address: {{ $partner['address'] ?? 'N/A' }}</li>
                        </ul>
                    @endforeach
                @else
                    <p>No specific owner/partner/director details available for this entity type or not provided.</p>
                @endif

                <h6>Authorized Persons:</h6>
                @if(isset($additionalData['authorized_persons']) && is_array($additionalData['authorized_persons']) && !empty(array_filter($additionalData['authorized_persons'])))
                    @foreach($additionalData['authorized_persons'] as $index => $person)
                        <p><strong>Authorized Person {{ $index + 1 }}:</strong></p>
                        <ul>
                            <li>Name: {{ $person['name'] ?? 'N/A' }}</li>
                            <li>Contact: {{ $person['contact'] ?? 'N/A' }}</li>
                            <li>Email: {{ $person['email'] ?? 'N/A' }}</li>
                            <li>Address: {{ $person['address'] ?? 'N/A' }}</li>
                            <li>Relation: {{ $person['relation'] ?? 'N/A' }}</li>
                            <li>Letter of Authorization:
                                @if(isset($person['letter']))
                                    <a href="{{ asset('storage/' . $person['letter']) }}" target="_blank">View Document</a>
                                @else
                                    N/A
                                @endif
                            </li>
                            <li>Aadhar:
                                @if(isset($person['aadhar']))
                                    <a href="{{ asset('storage/' . $person['aadhar']) }}" target="_blank">View Document</a>
                                @else
                                    N/A
                                @endif
                            </li>
                        </ul>
                    @endforeach
                @else
                    <p>No authorized persons provided.</p>
                @endif

                <h6>Uploaded Documents for Entity Details:</h6>
                @php
                    $entityDocuments = json_decode($application->entityDetails->documents_data, true);
                @endphp
                @if(!empty($entityDocuments))
                    <ul>
                        @foreach($entityDocuments as $doc)
                            <li><strong>{{ Str::title(str_replace('_', ' ', $doc['type'])) }}:</strong>
                                <a href="{{ asset('storage/' . $doc['path']) }}" target="_blank">View Document</a>
                                @if(isset($doc['verified']) && $doc['verified']) (Verified) @endif
                                @if(isset($doc['details']))
                                    (
                                    @foreach($doc['details'] as $key => $value)
                                        {{ Str::title(str_replace('_', ' ', $key)) }}: {{ $value }}@if(!$loop->last), @endif
                                    @endforeach
                                    )
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p>No documents uploaded for Entity Details.</p>
                @endif

            @else
                <p>No entity details provided.</p>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5>Distribution Details</h5>
        </div>
        <div class="card-body">
            @if(!empty($application->distributionDetail))
                <p><strong>Area Covered:</strong>
                    @if($application->distributionDetail->area_covered)
                        {{ implode(', ', json_decode($application->distributionDetail->area_covered, true)) }}
                    @else
                        N/A
                    @endif
                </p>
                <p><strong>Appointment Type:</strong> {{ Str::title(str_replace('_', ' ', $application->distributionDetail->appointment_type)) ?? 'N/A' }}</p>
                @if($application->distributionDetail->appointment_type === 'replacement')
                    <p><strong>Replacement Reason:</strong> {{ $application->distributionDetail->replacement_reason ?? 'N/A' }}</p>
                    <p><strong>Outstanding Recovery:</strong> {{ $application->distributionDetail->outstanding_recovery ?? 'N/A' }}</p>
                    <p><strong>Previous Firm Name:</strong> {{ $application->distributionDetail->previous_firm_name ?? 'N/A' }}</p>
                    <p><strong>Previous Firm Code:</strong> {{ $application->distributionDetail->previous_firm_code ?? 'N/A' }}</p>
                @elseif($application->distributionDetail->appointment_type === 'new_area')
                    <p><strong>Earlier Distributor:</strong> {{ $application->distributionDetail->earlier_distributor ?? 'N/A' }}</p>
                @endif
            @else
                <p>No distribution details provided.</p>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5>Business Plan</h5>
        </div>
        <div class="card-body">
            @if($application->businessPlans->count() > 0)
                @foreach($application->businessPlans as $plan)
                    <p><strong>Crop:</strong> {{ $plan->crop ?? 'N/A' }}</p>
                    @if(!empty($plan->yearly_targets))
                        <p><strong>Yearly Targets:</strong></p>
                        <ul>
                            @foreach($plan->yearly_targets as $yearId => $target)
                                @php
                                    $yearPeriod = $years[$yearId]->period ?? $yearId;
                                @endphp
                                <li>FY {{ $yearPeriod }}: {{ $target }} MT</li>
                            @endforeach
                        </ul>
                    @endif
                    <hr>
                @endforeach
            @else
                <p>No business plan provided.</p>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5>Financial Information</h5>
        </div>
        <div class="card-body">
            @if(!empty($application->financialInfo))
                <p><strong>Net Worth:</strong> {{ $application->financialInfo->net_worth ?? 'N/A' }}</p>
                <p><strong>Shop Ownership:</strong> {{ Str::title($application->financialInfo->shop_ownership) ?? 'N/A' }}</p>
                <p><strong>Godown Area:</strong> {{ $application->financialInfo->godown_area ?? 'N/A' }}</p>
                <p><strong>Years in Business:</strong> {{ $application->financialInfo->years_in_business ?? 'N/A' }}</p>
                <h6>Annual Turnover:</h6>
                @if($application->financialInfo->annual_turnover)
                    <ul>
                        @foreach(json_decode($application->financialInfo->annual_turnover, true) as $year => $amount)
                            <li>{{ $year }}: {{ $amount }}</li>
                        @endforeach
                    </ul>
                @else
                    <p>No annual turnover data provided.</p>
                @endif
            @else
                <p>No financial information provided.</p>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5>Existing Distributorships</h5>
        </div>
        <div class="card-body">
            @if($application->existingDistributorships->count() > 0)
                <ul>
                    @foreach($application->existingDistributorships as $distributorship)
                        <li><strong>Company Name:</strong> {{ $distributorship->company_name ?? 'N/A' }}</li>
                    @endforeach
                </ul>
            @else
                <p>No existing distributorships provided.</p>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5>Bank Details</h5>
        </div>
        <div class="card-body">
            @if(!empty($application->bankDetail))
                <p><strong>Financial Status:</strong> {{ $application->bankDetail->financial_status ?? 'N/A' }}</p>
                <p><strong>Retailer Count:</strong> {{ $application->bankDetail->retailer_count ?? 'N/A' }}</p>
                <p><strong>Bank Name:</strong> {{ $application->bankDetail->bank_name ?? 'N/A' }}</p>
                <p><strong>Account Holder:</strong> {{ $application->bankDetail->account_holder ?? 'N/A' }}</p>
                <p><strong>Account Number:</strong> {{ $application->bankDetail->account_number ?? 'N/A' }}</p>
                <p><strong>IFSC Code:</strong> {{ $application->bankDetail->ifsc_code ?? 'N/A' }}</p>
                <p><strong>Account Type:</strong> {{ Str::title($application->bankDetail->account_type) ?? 'N/A' }}</p>
                <p><strong>Relationship Duration:</strong> {{ $application->bankDetail->relationship_duration ?? 'N/A' }} years</p>
                <p><strong>OD Limit:</strong> {{ $application->bankDetail->od_limit ?? 'N/A' }}</p>
                <p><strong>OD Security:</strong> {{ $application->bankDetail->od_security ?? 'N/A' }}</p>
            @else
                <p>No bank details provided.</p>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5>Declarations</h5>
        </div>
        <div class="card-body">
            @if($application->declarations->count() > 0)
                @php
                    $declarationQuestions = [
                        'is_other_distributor' => 'Are you a distributor of any other seed company in the same or similar crops as VNR Seeds?',
                        'has_sister_concern' => 'Do you have any sister concern engaged in the seed business?',
                        'has_question_c' => 'Do you have any distributorship of other seed companies for other crops which VNR Seeds is not dealing in?',
                        'has_question_d' => 'Do you have any association / tie-up / distributorship / dealership in any other Agro Inputs (like fertilizers, pesticides, etc.) with any company?',
                        'has_question_e' => 'Have you ever been a distributor/dealer/agent of VNR Seeds in the past?',
                        'has_disputed_dues' => 'Are there any disputed dues/payments outstanding with any seed company/other company/financial institution against you/your proprietorship/partnership/company/LLP/co-operative society/trust or any of your partners/directors/committee members/trustees/authorized persons?',
                        'has_question_g' => 'Have you or your proprietorship/partnership/company/LLP/co-operative society/trust or any of your partners/directors/committee members/trustees/authorized persons ever ceased to be an agent/distributor/dealer of any seed company/other company due to any dispute or non-performance?',
                        'has_question_h' => 'Is any of your relative working with VNR Seeds or is associated with any other seed company?',
                        'has_question_i' => 'Do you, your proprietorship/partnership/company/LLP/co-operative society/trust or any of your partners/directors/committee members/trustees/authorized persons have any involvement, direct or indirect, with any other company (seed or otherwise)?',
                        'has_question_j' => 'Has this Distributor been referred by someone known to VNR Seeds management/employees?',
                        'has_question_k' => 'Are you engaged in marketing your own brand of seeds?',
                        'has_question_l' => 'Have you or any of your partners/directors/committee members/trustees/authorized persons ever been employed in the Agro-Input Industry?',
                        'declaration_truthful' => 'I/We hereby declare that the information provided in this application is true and correct to the best of my/our knowledge and belief.',
                        'declaration_update' => 'I/We undertake to inform VNR Seeds of any changes in the above information immediately.'
                    ];
                @endphp

                @foreach($application->declarations as $declaration)
                    @php
                        $questionText = $declarationQuestions[$declaration->question_key] ?? Str::title(str_replace('_', ' ', $declaration->question_key));
                        $hasIssue = (bool)$declaration->has_issue;
                        $details = $declaration->details;
                    @endphp
                    <p><strong>{{ $questionText }}</strong></p>
                    <p>Answer: {{ $hasIssue ? 'Yes' : 'No' }}</p>
                    @if($hasIssue && !empty($details))
                        <ul>
                            @foreach($details as $detailKey => $detailValue)
                                <li><strong>{{ Str::title(str_replace('_', ' ', $detailKey)) }}:</strong> {{ $detailValue }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <hr>
                @endforeach
            @else
                <p>No declarations provided.</p>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5>Confirm Accuracy</h5>
        </div>
        <div class="card-body">
            <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" id="confirm_accuracy">
                <label class="form-check-label" for="confirm_accuracy">I confirm that all the information provided above is accurate and complete to the best of my knowledge.</label>
                <div class="invalid-feedback text-danger">Please confirm the accuracy of the details before submitting.</div>
            </div>
        </div>
    </div>
</div>