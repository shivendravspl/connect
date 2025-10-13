@php
    use Illuminate\Support\Str;
@endphp

<div class="form-section">
    <h3>Basic Information</h3>
    <table class="table">
        <tr>
            <td class="label">Application Code:</td>
            <td class="value">{{ $application->application_code ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Territory:</td>
            <td class="value">{{ $application->territoryDetail->territory_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Crop Vertical:</td>
            <td class="value">{{ $application->vertical->vertical_name ?? 'N/A' }}</td>
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
            <td class="label">Business Unit:</td>
            <td class="value">{{ $application->businessUnit->business_unit_name ?? 'Not Assigned' }}</td>
        </tr>
    </table>
</div>

<div class="form-section">
    <h3>Entity Details</h3>
    @if($application->entityDetails)
        @php
            $entityDetails = $application->entityDetails;
            $entityType = $entityDetails->entity_type;
            $entityTypeLabel = $entityTypeLabels[$entityType] ?? Str::title(str_replace('_', ' ', $entityType));
        @endphp
        
        <table class="table">
            <tr>
                <td class="label">Establishment Name:</td>
                <td class="value">{{ $entityDetails->establishment_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Entity Type:</td>
                <td class="value">{{ $entityTypeLabel }}</td>
            </tr>
            <tr>
                <td class="label">Business Address:</td>
                <td class="value">
                    @if($entityDetails->house_no || $entityDetails->landmark || $entityDetails->city || 
                        $entityDetails->district_id || $entityDetails->state_id || $entityDetails->pincode)
                        {{ $entityDetails->house_no ? $entityDetails->house_no . ', ' : '' }}
                        {{ $entityDetails->landmark ? $entityDetails->landmark . ', ' : '' }}
                        {{ $entityDetails->city ? $entityDetails->city . ', ' : '' }}
                        {{ $entityDetails->district_id && isset($districts[$entityDetails->district_id]) ? $districts[$entityDetails->district_id]->district_name . ', ' : '' }}
                        {{ $entityDetails->state_id && isset($states[$entityDetails->state_id]) ? $states[$entityDetails->state_id]->state_name . ', ' : '' }}
                        {{ $entityDetails->pincode ? $entityDetails->pincode : '' }}
                        @if($entityDetails->country_id && isset($countries[$entityDetails->country_id]))
                            , {{ $countries[$entityDetails->country_id]->country_name }}
                        @endif
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Mobile Number:</td>
                <td class="value">{{ $entityDetails->mobile ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Email Address:</td>
                <td class="value">{{ $entityDetails->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">PAN Number:</td>
                <td class="value">{{ $entityDetails->pan_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">PAN Verified:</td>
                <td class="value">{{ $entityDetails->pan_verified ? 'Yes' : 'No' }}</td>
            </tr>
            <tr>
                <td class="label">GST Applicable:</td>
                <td class="value">{{ $entityDetails->gst_applicable ? Str::title($entityDetails->gst_applicable) : 'N/A' }}</td>
            </tr>
            @if($entityDetails->gst_applicable === 'yes')
                <tr>
                    <td class="label">GST Number:</td>
                    <td class="value">{{ $entityDetails->gst_number ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">GST Verified:</td>
                    <td class="value">{{ isset($entityDetails->gst_verified) ? ($entityDetails->gst_verified ? 'Yes' : 'No') : 'N/A' }}</td>
                </tr>
            @endif
            <tr>
                <td class="label">Seed License Number:</td>
                <td class="value">{{ $entityDetails->seed_license ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Seed License Validity:</td>
                <td class="value">{{ $entityDetails->seed_license_validity ? \Carbon\Carbon::parse($entityDetails->seed_license_validity)->format('d-m-Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Seed License Verified:</td>
                <td class="value">{{ $entityDetails->seed_license_verified ? 'Yes' : 'No' }}</td>
            </tr>
            <tr>
                <td class="label">TAN Number:</td>
                <td class="value">{{ $entityDetails->tan_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Has Authorized Persons:</td>
                <td class="value">{{ $entityDetails->has_authorized_persons === 'yes' ? 'Yes' : 'No' }}</td>
            </tr>
        </table>
         <div class="form-section">
            <h4>Supporting Documents</h4>
             <table class="table">
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
                                            <td class="label">{{ $entityProofLabel }}</td>
                                            <td class="value">{{ basename($application->entityDetails->entity_proof_path) }}</td>
                                        </tr>
                                        @endif

                                        <!-- Ownership Information -->
                                        @if(in_array($entityType, ['sole_proprietorship', 'llp', 'private_company', 'public_company', 'cooperative_society', 'trust']))
                                            @if(($entityType === 'sole_proprietorship' && $application->proprietorDetails && $application->proprietorDetails->ownership_info_path) || 
                                                ($entityType !== 'sole_proprietorship' && $application->entityDetails->ownership_info_path))
                                        <tr>
                                            <th class="label">{{ $ownershipInfoLabel }}</th>
                                            <td class="value">
                                                <span class="badge bg-success">Uploaded</span>
                                                @if($entityType === 'sole_proprietorship' && $application->proprietorDetails && $application->proprietorDetails->ownership_info_path)
                                               <td class="value">{{ basename($application->entityDetails->ownership_info_path) }}</td>
                                                @elseif($application->entityDetails->ownership_info_path)
                                                <td class="value">{{ basename($application->entityDetails->ownership_info_path) }}</td>
                                                @endif
                                            </td>
                                        </tr>
                                            @endif
                                        @endif

                                        <!-- Bank Statement (Always shown) -->
                                        @if($application->entityDetails->bank_statement_path)
                                        <tr>
                                            <td class="label">{{ $bankStatementLabel }}</td>
                                            <td class="value">{{ basename($application->entityDetails->bank_statement_path) }}</td>
                                            
                                        </tr>
                                        @endif

                                        <!-- Credit Worthiness Documents (Only shown when entity type is set) -->
                                        @if($entityType)
                                            <!-- ITR Acknowledgement -->
                                            @if($application->entityDetails->itr_acknowledgement_path)
                                            <tr>
                                                <td class="label">{{ $itrAcknowledgementLabel }}</td>
                                                <td class="value">
                                                 {{ basename($application->entityDetails->itr_acknowledgement_path) }}
                                                </td>
                                            </tr>
                                            @endif

                                            <!-- Balance Sheet (Optional) -->
                                            @if($application->entityDetails->balance_sheet_path)
                                            <tr>
                                                <td class="label">{{ $balanceSheetLabel }}</td>
                                                <td class="value">
                                                   {{ basename($application->entityDetails->balance_sheet_path) }}
                                                </td>
                                            </tr>
                                            @endif
                                        @endif
                                    </tbody>
                                </table>
         </div>

        {{-- **UPDATED: Ownership Details - Use direct table relationships ** --}}
        <div class="form-section">
            <h4>Ownership Details</h4>
            
            @switch($entityType)
                @case('individual_person')
                    @if($application->individualDetails)
                        <h5>Individual Person Details</h5>
                        <table class="table">
                            <tr>
                                <td class="label">Full Name:</td>
                                <td class="value">{{ $application->individualDetails->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Father's/Spouse's Name:</td>
                                <td class="value">{{ $application->individualDetails->father_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Date of Birth:</td>
                                <td class="value">{{ $application->individualDetails->dob ? \Carbon\Carbon::parse($application->individualDetails->dob)->format('d-m-Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Age:</td>
                                <td class="value">{{ $application->individualDetails->age ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    @endif
                    @break
                    
                @case('sole_proprietorship')
                    @if($application->proprietorDetails)
                        <h5>Proprietorship Details</h5>
                        <table class="table">
                            <tr>
                                <td class="label">Proprietor Name:</td>
                                <td class="value">{{ $application->proprietorDetails->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Father's/Spouse's Name:</td>
                                <td class="value">{{ $application->proprietorDetails->father_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Date of Birth:</td>
                                <td class="value">{{ $application->proprietorDetails->dob ? \Carbon\Carbon::parse($application->proprietorDetails->dob)->format('d-m-Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Age:</td>
                                <td class="value">{{ $application->proprietorDetails->age ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    @endif
                    @break
                    
                @case('partnership')
                    @if($application->partnershipPartners->isNotEmpty())
                        <h5>Partners Details</h5>
                        @foreach($application->partnershipPartners as $index => $partner)
                            <p><strong>Partner {{ $index + 1 }}:</strong></p>
                            <table class="table">
                                <tr>
                                    <td class="label">Name:</td>
                                    <td class="value">{{ $partner->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">PAN Number:</td>
                                    <td class="value">{{ $partner->pan ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Contact Number:</td>
                                    <td class="value">{{ $partner->contact ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                     <td class="label">Aadhar:</td>
                                     <td class="value">{{ $partner->aadhar_path ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        @endforeach
                    @endif
                    
                    @if($application->partnershipSignatories->isNotEmpty())
                        <h5>Signatory Details</h5>
                        @foreach($application->partnershipSignatories as $index => $signatory)
                            <p><strong>Signatory {{ $index + 1 }}:</strong></p>
                            <table class="table">
                                <tr>
                                    <td class="label">Name:</td>
                                    <td class="value">{{ $signatory->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Designation:</td>
                                    <td class="value">{{ $signatory->designation ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Contact Number:</td>
                                    <td class="value">{{ $signatory->contact ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        @endforeach
                    @endif
                    @break
                    
                @case('llp')
                    @if($application->llpDetails)
                        <h5>LLP Details</h5>
                        <table class="table">
                            <tr>
                                <td class="label">LLPIN Number:</td>
                                <td class="value">{{ $application->llpDetails->llpin_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Date of Incorporation:</td>
                                <td class="value">{{ $application->llpDetails->incorporation_date ? \Carbon\Carbon::parse($application->llpDetails->incorporation_date)->format('d-m-Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    @endif
                    
                    @if($application->llpPartners->isNotEmpty())
                        <h5>Designated Partners</h5>
                        @foreach($application->llpPartners as $index => $partner)
                            <p><strong>Partner {{ $index + 1 }}:</strong></p>
                            <table class="table">
                                <tr>
                                    <td class="label">Name:</td>
                                    <td class="value">{{ $partner->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">DPIN Number:</td>
                                    <td class="value">{{ $partner->dpin_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Contact Number:</td>
                                    <td class="value">{{ $partner->contact ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Address:</td>
                                    <td class="value">{{ $partner->address ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        @endforeach
                    @endif
                    @break
                    
                @case('private_company')
                @case('public_company')
                    @if($application->companyDetails)
                        <h5>Company Details</h5>
                        <table class="table">
                            <tr>
                                <td class="label">CIN Number:</td>
                                <td class="value">{{ $application->companyDetails->cin_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Date of Incorporation:</td>
                                <td class="value">{{ $application->companyDetails->incorporation_date ? \Carbon\Carbon::parse($application->companyDetails->incorporation_date)->format('d-m-Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Company Type:</td>
                                <td class="value">{{ $application->companyDetails->entity_type === 'private_company' ? 'Private Limited' : 'Public Limited' }}</td>
                            </tr>
                        </table>
                    @endif
                    
                    @if($application->directors->isNotEmpty())
                        <h5>Directors Details</h5>
                        @foreach($application->directors as $index => $director)
                            <p><strong>Director {{ $index + 1 }}:</strong></p>
                            <table class="table">
                                <tr>
                                    <td class="label">Name:</td>
                                    <td class="value">{{ $director->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">DIN Number:</td>
                                    <td class="value">{{ $director->din_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Contact Number:</td>
                                    <td class="value">{{ $director->contact ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Address:</td>
                                    <td class="value">{{ $director->address ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        @endforeach
                    @endif
                    @break
                    
                @case('cooperative_society')
                    @if($application->cooperativeDetails)
                        <h5>Cooperative Society Details</h5>
                        <table class="table">
                            <tr>
                                <td class="label">Registration Number:</td>
                                <td class="value">{{ $application->cooperativeDetails->reg_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Registration Date:</td>
                                <td class="value">{{ $application->cooperativeDetails->reg_date ? \Carbon\Carbon::parse($application->cooperativeDetails->reg_date)->format('d-m-Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    @endif
                    
                    @if($application->committeeMembers->isNotEmpty())
                        <h5>Committee Members</h5>
                        @foreach($application->committeeMembers as $index => $member)
                            <p><strong>Member {{ $index + 1 }}:</strong></p>
                            <table class="table">
                                <tr>
                                    <td class="label">Name:</td>
                                    <td class="value">{{ $member->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Designation:</td>
                                    <td class="value">{{ $member->designation ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Contact Number:</td>
                                    <td class="value">{{ $member->contact ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Address:</td>
                                    <td class="value">{{ $member->address ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        @endforeach
                    @endif
                    @break
                    
                @case('trust')
                    @if($application->trustDetails)
                        <h5>Trust Details</h5>
                        <table class="table">
                            <tr>
                                <td class="label">Registration Number:</td>
                                <td class="value">{{ $application->trustDetails->reg_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Registration Date:</td>
                                <td class="value">{{ $application->trustDetails->reg_date ? \Carbon\Carbon::parse($application->trustDetails->reg_date)->format('d-m-Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    @endif
                    
                    @if($application->trustees->isNotEmpty())
                        <h5>Trustees Details</h5>
                        @foreach($application->trustees as $index => $trustee)
                            <p><strong>Trustee {{ $index + 1 }}:</strong></p>
                            <table class="table">
                                <tr>
                                    <td class="label">Name:</td>
                                    <td class="value">{{ $trustee->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Designation:</td>
                                    <td class="value">{{ $trustee->designation ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Contact Number:</td>
                                    <td class="value">{{ $trustee->contact ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Address:</td>
                                    <td class="value">{{ $trustee->address ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        @endforeach
                    @endif
                    @break
                    
                @default
                    <table class="table">
                        <tr>
                            <td class="label">Ownership Details:</td>
                            <td class="value">No ownership details available for entity type: {{ $entityTypeLabel }}</td>
                        </tr>
                    </table>
            @endswitch
        </div>

        {{-- **UPDATED: Authorized Persons Section ** --}}
        @if($entityDetails->has_authorized_persons === 'yes')
            <div class="form-section">
                <h4>Authorized Persons Details</h4>
                @if($application->authorizedPersons->isNotEmpty())
                    @foreach($application->authorizedPersons as $index => $person)
                        <p><strong>Authorized Person {{ $index + 1 }}:</strong></p>
                        <table class="table">
                            <tr>
                                <td class="label">Name:</td>
                                <td class="value">{{ $person->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Contact Number:</td>
                                <td class="value">{{ $person->contact ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Email Address:</td>
                                <td class="value">{{ $person->email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Full Address:</td>
                                <td class="value">{{ $person->address ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Relation with Entity:</td>
                                <td class="value">{{ $person->relation ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Aadhar Number:</td>
                                <td class="value">{{ $person->aadhar_number ?? 'N/A' }}</td>
                            </tr>
                            @if($person->letter_path)
                                <tr>
                                    <td class="label">Letter of Authorization:</td>
                                    <td class="value">Uploaded - {{ basename($person->letter_path) }}</td>
                                </tr>
                            @endif
                            @if($person->aadhar_path)
                                <tr>
                                    <td class="label">Aadhar Document:</td>
                                    <td class="value">Uploaded - {{ basename($person->aadhar_path) }}</td>
                                </tr>
                            @endif
                        </table>
                    @endforeach
                @else
                    <table class="table">
                        <tr>
                            <td class="label">Authorized Persons:</td>
                            <td class="value">No authorized persons details provided</td>
                        </tr>
                    </table>
                @endif
            </div>
        @endif
    @else
        <div class="form-section">
            <table class="table">
                <tr>
                    <td class="label">Entity Details:</td>
                    <td class="value">No entity details available.</td>
                </tr>
            </table>
        </div>
    @endif
</div>

{{-- **UPDATED: Bank Details Section - Combined approach ** --}}
<div class="form-section">
    <h3>Bank Details</h3>
    @php
        $entityDetails = $application->entityDetails;
        $bankDetail = $application->bankDetail;
        
        // **PRIORITY 1: Entity Details (primary bank info)**
        $primaryBankInfo = [
            'bank_name' => $entityDetails->bank_name ?? 'N/A',
            'account_holder' => $entityDetails->account_holder_name ?? 'N/A',
            'account_number' => $entityDetails->account_number ?? 'N/A',
            'ifsc_code' => $entityDetails->ifsc_code ?? 'N/A',
            'document_path' => $entityDetails->bank_document_path ?? null
        ];
        
        // **PRIORITY 2: Bank Detail (additional info)**
        $additionalBankInfo = $bankDetail ? [
            'financial_status' => $bankDetail->financial_status ?? 'N/A',
            'retailer_count' => $bankDetail->retailer_count ?? 'N/A',
            'account_type' => $bankDetail->account_type ? Str::title($bankDetail->account_type) : 'N/A',
            'relationship_duration' => $bankDetail->relationship_duration ?? 'N/A',
            'od_limit' => $bankDetail->od_limit ?? 'N/A',
            'od_security' => $bankDetail->od_security ?? 'N/A',
            'bank_statement_path' => $bankDetail->bank_statement_path ?? null
        ] : [];
    @endphp
    
    <h4>Primary Bank Account Information</h4>
    <table class="table">
        <tr>
            <td class="label">Bank Name:</td>
            <td class="value">{{ $primaryBankInfo['bank_name'] }}</td>
        </tr>
        <tr>
            <td class="label">Account Holder Name:</td>
            <td class="value">{{ $primaryBankInfo['account_holder'] }}</td>
        </tr>
        <tr>
            <td class="label">Account Number:</td>
            <td class="value">{{ $primaryBankInfo['account_number'] }}</td>
        </tr>
        <tr>
            <td class="label">IFSC Code:</td>
            <td class="value">{{ $primaryBankInfo['ifsc_code'] }}</td>
        </tr>
        @if($primaryBankInfo['document_path'])
            <tr>
                <td class="label">Bank Document:</td>
                <td class="value">Uploaded - {{ basename($primaryBankInfo['document_path']) }}</td>
            </tr>
        @endif
    </table>

    @if(!empty($additionalBankInfo))
        <h4>Additional Banking Information</h4>
        <table class="table">
            @if($additionalBankInfo['financial_status'] !== 'N/A')
                <tr>
                    <td class="label">Financial Status:</td>
                    <td class="value">{{ $additionalBankInfo['financial_status'] }}</td>
                </tr>
            @endif
            @if($additionalBankInfo['retailer_count'] !== 'N/A')
                <tr>
                    <td class="label">No. of Retailers Dealt With:</td>
                    <td class="value">{{ $additionalBankInfo['retailer_count'] }}</td>
                </tr>
            @endif
            @if($additionalBankInfo['account_type'] !== 'N/A')
                <tr>
                    <td class="label">Account Type:</td>
                    <td class="value">{{ $additionalBankInfo['account_type'] }}</td>
                </tr>
            @endif
            @if($additionalBankInfo['relationship_duration'] !== 'N/A')
                <tr>
                    <td class="label">Relationship Duration:</td>
                    <td class="value">{{ $additionalBankInfo['relationship_duration'] }} years</td>
                </tr>
            @endif
            @if($additionalBankInfo['od_limit'] !== 'N/A')
                <tr>
                    <td class="label">OD Limit:</td>
                    <td class="value">{{ $additionalBankInfo['od_limit'] }}</td>
                </tr>
            @endif
            @if($additionalBankInfo['od_security'] !== 'N/A')
                <tr>
                    <td class="label">OD Security:</td>
                    <td class="value">{{ $additionalBankInfo['od_security'] }}</td>
                </tr>
            @endif
            @if($additionalBankInfo['bank_statement_path'])
                <tr>
                    <td class="label">Additional Bank Statement:</td>
                    <td class="value">Uploaded - {{ basename($additionalBankInfo['bank_statement_path']) }}</td>
                </tr>
            @endif
        </table>
    @endif
    
    @if(!$entityDetails->bank_name && !$bankDetail)
        <div class="alert alert-warning">
            <strong>Note:</strong> No bank details provided in the application.
        </div>
    @endif
</div>

<div class="form-section">
    <h3>Distribution Details</h3>
    @if($application->distributionDetail)
        @php
            $distDetail = $application->distributionDetail;
            $areaCovered = is_array($distDetail->area_covered) ? $distDetail->area_covered : json_decode($distDetail->area_covered, true) ?? [];
        @endphp
        <table class="table">
            <tr>
                <td class="label">Area to be Covered:</td>
                <td class="value">{{ !empty($areaCovered) ? implode(', ', $areaCovered) : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Appointment Type:</td>
                <td class="value">{{ $distDetail->appointment_type ? Str::title(str_replace('_', ' ', $distDetail->appointment_type)) : 'N/A' }}</td>
            </tr>
            
            @if($distDetail->appointment_type === 'replacement')
                <tr>
                    <td class="label">Replacement Reason:</td>
                    <td class="value">{{ $distDetail->replacement_reason ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Outstanding Recovery Commitment:</td>
                    <td class="value">{{ $distDetail->outstanding_recovery ? ($distDetail->outstanding_recovery === 'yes' ? 'Yes' : 'No') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Previous Firm Name:</td>
                    <td class="value">{{ $distDetail->previous_firm_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Previous Firm Code:</td>
                    <td class="value">{{ $distDetail->previous_firm_code ?? 'N/A' }}</td>
                </tr>
            @elseif($distDetail->appointment_type === 'new_area')
                <tr>
                    <td class="label">Earlier Distributor:</td>
                    <td class="value">{{ $distDetail->earlier_distributor ? ($distDetail->earlier_distributor === 'yes' ? 'Yes' : 'No') : 'N/A' }}</td>
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

@if(isset($application->businessPlans) && $application->businessPlans->isNotEmpty())
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
                        <th class="sub-header">MT *</th>
                        <th class="sub-header">Amount *</th>
                        <th class="sub-header">MT *</th>
                        <th class="sub-header">Amount *</th>
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


<div class="form-section">
    <h3>Financial & Operational Information</h3>
    @if($application->financialInfo)
        @php
            $financialInfo = $application->financialInfo;
            $annualTurnover = is_array($financialInfo->annual_turnover) 
                ? $financialInfo->annual_turnover 
                : json_decode($financialInfo->annual_turnover, true) ?? [];
        @endphp
        <table class="table">
            <tr>
                <td class="label">Net Worth (Previous FY):</td>
                <td class="value">{{ $financialInfo->net_worth ? '₹' . number_format($financialInfo->net_worth, 2) : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Shop Ownership:</td>
                <td class="value">{{ $financialInfo->shop_ownership ? Str::title($financialInfo->shop_ownership) : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Godown Area:</td>
                <td class="value">{{ $financialInfo->godown_area ?? 'N/A' }} {{ $financialInfo->shop_ownership ? '(Owned/Rented)' : '' }}</td>
            </tr>
            <tr>
                <td class="label">Years in Business:</td>
                <td class="value">{{ $financialInfo->years_in_business ?? 'N/A' }}</td>
            </tr>
        </table>
        
        @if(!empty($annualTurnover))
            <h4>Annual Turnover (₹)</h4>
            <table class="table">
                <tr>
                    <th>Financial Year</th>
                    <th>Net Turnover</th>
                </tr>
                @foreach($annualTurnover as $year => $amount)
                    <tr>
                        <td>{{ $year }}</td>
                        <td>{{ number_format($amount, 2) }}</td>
                    </tr>
                @endforeach
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
    <h3>Declarations</h3>
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
                'has_question_h' => 'Whether the Distributor\'s relative is connected in any way with VNR Seeds and any other Seed Company?',
                'has_question_i' => 'Whether the Distributor is involved in any other capacity with the Company apart from this application?',
                'has_question_j' => 'Whether the Distributor has been referred by any Distributors or other parties associated with the Company?',
                'has_question_k' => 'Whether the Distributor is currently marketing or selling products under its own brand name?',
                'has_question_l' => 'Whether the Distributor has been employed in the agro-input industry at any point during the past 5 years?',
                'declaration_truthful' => 'I hereby solemnly affirm and declare that the information furnished in this form is true, correct, and complete to the best of my knowledge and belief.'
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
                    @if($declaration->question_key === 'has_disputed_dues' && is_array($details))
                        @if(isset($details['amount']) && $details['amount'])
                            <tr><td class="q_label">Amount:</td><td class="q_value">₹{{ number_format($details['amount'], 2) }}</td></tr>
                        @endif
                        @if(isset($details['nature']) && $details['nature'])
                            <tr><td class="q_label">Nature of Dispute:</td><td class="q_value">{{ $details['nature'] }}</td></tr>
                        @endif
                        @if(isset($details['year']) && $details['year'])
                            <tr><td class="q_label">Year of Dispute:</td><td class="q_value">{{ $details['year'] }}</td></tr>
                        @endif
                        @if(isset($details['present_position']) && $details['present_position'])
                            <tr><td class="q_label">Present Position:</td><td class="q_value">{{ $details['present_position'] }}</td></tr>
                        @endif
                        @if(isset($details['reason']) && $details['reason'])
                            <tr><td class="q_label">Reason for Default:</td><td class="q_value">{{ $details['reason'] }}</td></tr>
                        @endif
                    @else
                        @foreach($details as $detailKey => $detailValue)
                            @if(is_string($detailValue) && !empty(trim($detailValue)))
                                <tr>
                                    <td class="q_label">{{ Str::title(str_replace('_', ' ', $detailKey)) }}:</td>
                                    <td class="q_value">{{ $detailValue }}</td>
                                </tr>
                            @endif
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

{{-- **UPDATED: Final Declaration Section ** --}}
<div class="form-section">
    <h3>Final Declaration</h3>
    <div class="declaration-text">
        <p><em><strong>I hereby solemnly affirm and declare that the information furnished in this form is true, correct, and complete to the best of my knowledge and belief.
</strong></em></p>
    </div>
    
    <table class="table">
        <tr>
            <td class="label" style="width: 30%;">Form Filled By:</td>
            <td class="value" style="width: 70%;">{{ $application->entityDetails->establishment_name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Date of Submission:</td>
            <td class="value">{{ $application->created_at ? $application->created_at->format('d-m-Y H:i') : now()->format('d-m-Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Current Status:</td>
            <td class="value">
                <span class="badge bg-{{ $application->getStatusBadgeAttribute() }}" style="padding: 4px 8px; font-size: 9pt;">
                    {{ Str::title(str_replace('_', ' ', $application->status ?? 'pending')) }}
                </span>
            </td>
        </tr>
        @if($application->createdBy)
            <tr>
                <td class="label">Created By:</td>
                <td class="value">{{ $application->createdBy->name ?? 'N/A' }}</td>
            </tr>
        @endif
    </table>
</div>