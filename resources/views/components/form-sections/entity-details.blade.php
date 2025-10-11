@php
// Access documents from individual columns instead of JSON
$panDoc = $application->entityDetails ? [
    'path' => $application->entityDetails->pan_path,
    'original_filename' => $application->entityDetails->pan_path ? basename($application->entityDetails->pan_path) : null,
    'verified' => $application->entityDetails->pan_verified ?? false,
    'details' => ['pan_number' => $application->entityDetails->pan_number]
] : null;

$gstDoc = $application->entityDetails && $application->entityDetails->gst_applicable === 'yes' ? [
    'path' => $application->entityDetails->gst_path,
    'original_filename' => $application->entityDetails->gst_path ? basename($application->entityDetails->gst_path) : null,
    'verified' => $application->entityDetails->gst_verified ?? false,
    'details' => [
        'gst_number' => $application->entityDetails->gst_number,
    ]
] : null;

$seedLicenseDoc = $application->entityDetails ? [
    'path' => $application->entityDetails->seed_license_path,
    'original_filename' => $application->entityDetails->seed_license_path ? basename($application->entityDetails->seed_license_path) : null,
    'verified' => $application->entityDetails->seed_license_verified ?? false,
    'details' => [
        'seed_license_number' => $application->entityDetails->seed_license,
        'seed_license_validity' => $application->entityDetails->seed_license_validity
    ]
] : null;

$bankDoc = $application->entityDetails ? [
    'path' => $application->entityDetails->bank_document_path,
    'original_filename' => $application->entityDetails->bank_document_path ? basename($application->entityDetails->bank_document_path) : null,
    'verified' => false,
    'details' => [
        'bank_name' => $application->entityDetails->bank_name,
        'account_holder' => $application->entityDetails->account_holder_name,
        'account_number' => $application->entityDetails->account_number,
        'ifsc_code' => $application->entityDetails->ifsc_code,
    ]
] : null;

$entityProofDoc = $application->entityDetails ? [
    'path' => $application->entityDetails->entity_proof_path,
    'original_filename' => $application->entityDetails->entity_proof_path ? basename($application->entityDetails->entity_proof_path) : null,
] : null;

$ownershipInfoDoc = $application->entityDetails ? [
    'path' => $application->entityDetails->ownership_info_path,
    'original_filename' => $application->entityDetails->ownership_info_path ? basename($application->entityDetails->ownership_info_path) : null,
] : null;

$bankStatementDoc = $application->entityDetails ? [
    'path' => $application->entityDetails->bank_statement_path,
    'original_filename' => $application->entityDetails->bank_statement_path ? basename($application->entityDetails->bank_statement_path) : null,
] : null;

$itrAcknowledgementDoc = $application->entityDetails ? [
    'path' => $application->entityDetails->itr_acknowledgement_path,
    'original_filename' => $application->entityDetails->itr_acknowledgement_path ? basename($application->entityDetails->itr_acknowledgement_path) : null,
] : null;

$balanceSheetDoc = $application->entityDetails ? [
    'path' => $application->entityDetails->balance_sheet_path,
    'original_filename' => $application->entityDetails->balance_sheet_path ? basename($application->entityDetails->balance_sheet_path) : null,
] : null;

// **IMPROVED: Get entity-specific data with better null handling**
$individual = $application->individualDetails;
$proprietor = $application->proprietorDetails;
$partners = $application->partnershipPartners ?? collect([]);
$signatories = $application->partnershipSignatories ?? collect([]);
$llpDetails = $application->llpDetails ?? null;
$llpPartners = $application->llpPartners ?? collect([]);
$companyDetails = $application->companyDetails;
$directors = $application->directors ?? collect([]);
$cooperativeDetails = $application->cooperativeDetails;
$committeeMembers = $application->committeeMembers ?? collect([]);
$trustDetails = $application->trustDetails;
$trustees = $application->trustees ?? collect([]);

// **NEW: Helper function for date formatting**
function formatDateForInput($date)
{
    if (!$date) return '';
    if ($date instanceof \Carbon\Carbon) {
        return $date->format('Y-m-d');
    }
    if (is_string($date)) {
        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return '';
        }
    }
    return '';
}

// **CORRECTED: Get authorized persons based on entityDetails flag**
$hasAuthorizedPersons = old('has_authorized_persons', $application->entityDetails->has_authorized_persons ?? 'no');
$showAuthorizedPersonsSection = $hasAuthorizedPersons === 'yes';

// Get authorized persons from separate table
$authPersons = $application->authorizedPersons ?? collect([]);

// **CORRECTED: Convert to array with consistent structure based on flag**
if ($showAuthorizedPersonsSection) {
    if ($authPersons->isEmpty()) {
        // Show empty row for data entry when section is enabled
        $authPersonsArray = [
            [
                'name' => '',
                'contact' => '',
                'email' => '',
                'address' => '',
                'relation' => '',
                'aadhar_number' => '',
                'letter_path' => null,
                'aadhar_path' => null,
                'letter_original_filename' => null,
                'aadhar_original_filename' => null
            ]
        ];
    } else {
        // Convert collection to array with consistent structure
        $authPersonsArray = $authPersons->map(function ($person) {
            return [
                'name' => $person->name,
                'contact' => $person->contact,
                'email' => $person->email,
                'address' => $person->address,
                'relation' => $person->relation,
                'aadhar_number' => $person->aadhar_number,
                'letter_path' => $person->letter_path,
                'aadhar_path' => $person->aadhar_path,
                'letter_original_filename' => $person->letter_path ? basename($person->letter_path) : null,
                'aadhar_original_filename' => $person->aadhar_path ? basename($person->aadhar_path) : null,
                'index' => $person->id // Preserve original ID for updates
            ];
        })->toArray();
    }
} else {
    // When section is disabled, still get data but don't show empty rows
    $authPersonsArray = $authPersons->map(function ($person) {
        return [
            'name' => $person->name,
            'contact' => $person->contact,
            'email' => $person->email,
            'address' => $person->address,
            'relation' => $person->relation,
            'aadhar_number' => $person->aadhar_number,
            'letter_path' => $person->letter_path,
            'aadhar_path' => $person->aadhar_path,
            'letter_original_filename' => $person->letter_path ? basename($person->letter_path) : null,
            'aadhar_original_filename' => $person->aadhar_path ? basename($person->aadhar_path) : null,
            'index' => $person->id
        ];
    })->toArray();
    
    // If no authorized persons and section is disabled, empty array
    if ($authPersons->isEmpty()) {
        $authPersonsArray = [];
    }
}

// **NEW: Map partners to a consistent array structure**
 $partnersArray = $partners->isEmpty() ? collect([['name' => '', 'pan' => '', 'contact' => '', 'aadhar_path' => null, 'aadhar_original_filename' => null]]) : $partners->map(function ($partner) {
        return [
            'name' => $partner->name ?? '',
            'pan' => $partner->pan ?? '',
            'contact' => $partner->contact ?? '',
            'aadhar_path' => $partner->aadhar_path ?? null,
            'aadhar_original_filename' => $partner->aadhar_original_filename ?? ($partner->aadhar_path ? basename($partner->aadhar_path) : null),
            'index' => $partner->id ?? null
        ];
    })->toArray();

    // Fix for signatories - ensure it's always a collection
    $signatories = $application->partnershipSignatories ?? collect([]);
    $existingPartnerFiles = [];
    $existingPartnerFilesOriginal = [];
    
    // Populate existing partner files data
    foreach ($partnersArray as $index => $partner) {
        $existingPartnerFiles[$index] = $partner['aadhar_path'] ?? null;
        $existingPartnerFilesOriginal[$index] = $partner['aadhar_original_filename'] ?? null;
    }
if ($llpPartners->isEmpty()) $llpPartners = collect([['name' => '', 'dpin_number' => '', 'contact' => '', 'address' => '']]);
if ($directors->isEmpty()) $directors = collect([['name' => '', 'din_number' => '', 'contact' => '', 'address' => '']]);
if ($committeeMembers->isEmpty()) $committeeMembers = collect([['name' => '', 'designation' => '', 'contact' => '', 'address' => '']]);
if ($trustees->isEmpty()) $trustees = collect([['name' => '', 'designation' => '', 'contact' => '', 'address' => '']]);
@endphp
<div id="global-loader" style="display: none; 
        position: fixed; 
        top: 50%; 
        left: 50%; 
        transform: translate(-50%, -50%); 
        z-index: 1050; 
        background: rgba(255, 255, 255, 0.7); 
        padding: 20px; 
        border-radius: 8px;">
    <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
        <span class="visually-hidden">Loading...</span>
    </div>
    <div class="mt-2 small text-muted">Processing...</div>
</div>
<div id="entity-details" class="form-section p-2">
    <div class="row g-2">
        <div class="col-12 col-md-6">
            <div class="form-group mb-2">
                <label for="establishment_name" class="form-label small">Name of Establishment *</label>
                <input type="text" class="form-control form-control-sm" id="establishment_name" name="establishment_name"
                    value="{{ old('establishment_name', $application->entityDetails->establishment_name ?? '') }}" required>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="form-group mb-2">
                <label for="entity_type" class="form-label small">Type/Nature of Establishment *</label>
                <select class="form-select form-select-sm" id="entity_type" name="entity_type" required onchange="showRelevantFields()">
                    <option value="">Select Type</option>
                    @foreach(['individual_person' => 'Individual Person','sole_proprietorship' => 'Sole Proprietorship', 'partnership' => 'Partnership', 'llp' => 'Limited Liability Partnership (LLP)', 'private_company' => 'Private Company', 'public_company' => 'Public Company', 'cooperative_society' => 'Cooperative Societies', 'trust' => 'Trust'] as $value => $label)
                    <option value="{{ $value }}"
                        {{ old('entity_type', $application->entityDetails->entity_type ?? '') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Individual Person Fields -->
    <div id="individual_person_fields" class="entity-specific-fields" 
        style="display: {{ old('entity_type', $application->entityDetails->entity_type ?? '') === 'individual_person' ? 'block' : 'none' }};">
        <div class="card mb-2">
            <div class="card-header bg-light p-2">
                <h6 class="mb-0 fs-6">Personal Information</h6>
            </div>
            <div class="card-body p-2">
                <div class="row g-2">
                    <div class="col-12 col-md-3">
                        <div class="form-group mb-2">
                            <label for="individual_name" class="form-label small">Full Name *</label>
                            <input type="text" class="form-control form-control-sm" id="individual_name" name="individual_name"
                                value="{{ old('individual_name', $individual->name ?? '') }}" 
                                data-required="true" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="form-group mb-2">
                            <label for="individual_father_name" class="form-label small">Father's / Spouse's Name *</label>
                            <input type="text" class="form-control form-control-sm" id="individual_father_name" name="individual_father_name"
                                value="{{ old('individual_father_name', $individual->father_name ?? '') }}" 
                                data-required="true" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="form-group mb-2">
                            <label for="individual_dob" class="form-label small">Date of Birth *</label>
                            <input type="date" 
                                class="form-control form-control-sm dob-input" 
                                id="individual_dob" 
                                name="individual_dob"
                                value="{{ old('individual_dob', formatDateForInput($individual?->dob)) }}" 
                                required>
                            <div class="invalid-feedback dob-error"></div>
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="form-group mb-2">
                            <label for="individual_age" class="form-label small">Age</label>
                            <input type="number" class="form-control form-control-sm age-display" id="individual_age" name="individual_age"
                                value="{{ old('individual_age', $individual->age ?? '') }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sole Proprietorship Fields -->
    <div id="sole_proprietorship_fields" class="entity-specific-fields" 
     style="display: {{ old('entity_type', $application->entityDetails->entity_type ?? '') === 'sole_proprietorship' ? 'block' : 'none' }};">
        <div class="card mb-2">
            <div class="card-header bg-light p-2">
                <h6 class="mb-0 fs-6">Proprietor Details</h6>
            </div>
            <div class="card-body p-2">
                <div class="row g-2">
                    <div class="col-12 col-md-3">
                        <div class="form-group mb-2">
                            <label for="proprietor_name" class="form-label small">Full Name *</label>
                            <input type="text" class="form-control form-control-sm" id="proprietor_name" name="proprietor_name"
                                value="{{ old('proprietor_name', $proprietor->name ?? '') }}" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="form-group mb-2">
                            <label for="proprietor_father_name" class="form-label small">Father's/Spouse's Name *</label>
                            <input type="text" class="form-control form-control-sm" id="proprietor_father_name" name="proprietor_father_name"
                                value="{{ old('proprietor_father_name', $proprietor->father_name ?? '') }}" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="form-group mb-2">
                            <label for="proprietor_dob" class="form-label small">Date of Birth *</label>
                            <input type="date" class="form-control form-control-sm dob-input" id="proprietor_dob" name="proprietor_dob"
                                value="{{ old('proprietor_dob', optional($proprietor)->dob ? optional($proprietor->dob)->format('Y-m-d') : '') }}" required>
                            <div class="invalid-feedback dob-error"></div>
                            
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="form-group mb-2">
                            <label for="proprietor_age" class="form-label small">Age</label>
                            <input type="number" class="form-control form-control-sm age-display" id="proprietor_age" name="proprietor_age"
                                value="{{ old('proprietor_age', $proprietor->age ?? '') }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

  <!-- Partnership Fields -->
<div id="partnership_fields" class="entity-specific-fields" 
     style="display: {{ old('entity_type', $application->entityDetails->entity_type ?? '') === 'partnership' ? 'block' : 'none' }};">
    <div class="card mb-4">
        <div class="card-header bg-light p-2">
            <h6 class="mb-0">Partnership Firm Details</h6>
        </div>
        <div class="card-body">
            <!-- Partner Details Section -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Partner Details</h6>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addPartner()">+ Add Partner</button>
                </div>
                <div id="partners_container">                    
                    @foreach($partnersArray as $index => $partner)
                    <div class="partner-entry mb-3 @if($index > 0) border-bottom pb-3 @endif" data-index="{{ $index }}">
                        <div class="row g-2">
                            <div class="col-12 col-md-3">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Partner Name *</label>
                                    <input type="text" class="form-control form-control-sm" name="partner_name[]" 
                                        value="{{ old("partner_name.$index", $partner['name'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group mb-2">
                                    <label class="form-label small">PAN *</label>
                                    <input type="text" class="form-control form-control-sm" name="partner_pan[]" 
                                        value="{{ old("partner_pan.$index", $partner['pan'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Contact Number *</label>
                                    <input type="tel" class="form-control form-control-sm" name="partner_contact[]" 
                                        value="{{ old("partner_contact.$index", $partner['contact'] ?? '') }}" required>
                                </div>
                            </div>
                                <div class="col-12 col-md-3">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Aadhar Card *</label>
                                    <div class="input-group input-group-sm">
                                        <input type="file" class="form-control form-control-sm d-none partner-aadhar-file" 
                                            id="partner_aadhar_{{ $index }}" name="partner_aadhar[]" 
                                            accept=".pdf,.jpg,.jpeg,.png" 
                                            data-index="{{ $index }}"
                                            onchange="handlePartnerFileChange(this, {{ $index }})">
                                        <button type="button" class="btn btn-sm btn-outline-secondary w-100" 
                                                onclick="document.getElementById('partner_aadhar_{{ $index }}').click()">
                                            Upload Aadhar
                                        </button>
                                    </div>

                                    <!-- Display uploaded file -->
                                    <div id="partner_aadhar_{{ $index }}_name" class="small mt-1 text-muted @if(isset($existingPartnerFiles[$index]) && $existingPartnerFiles[$index]) text-primary d-block @else d-none @endif">
                                        @if(isset($existingPartnerFiles[$index]) && $existingPartnerFiles[$index])
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal"
                                            data-src="{{ Storage::disk('s3')->url('Connect/Distributor/partner_aadhar/' . $existingPartnerFiles[$index]) }}"
                                            data-type="Partner Aadhar">View</a>
                                            ({{ $existingPartnerFilesOriginal[$index] ?? 'Uploaded file' }})
                                        @endif
                                    </div>

                                    <input type="hidden" name="existing_partner_aadhar_file[{{ $index }}]" value="{{ $existingPartnerFiles[$index] ?? '' }}">
                                    <input type="hidden" name="existing_partner_aadhar_file_original[{{ $index }}]" value="{{ $existingPartnerFilesOriginal[$index] ?? '' }}">
                                    <div class="invalid-feedback">Aadhar document is required</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-2 d-flex align-items-end">
                                <div class="form-group mb-2 w-100">
                                    @if($index > 0 || count($partnersArray) > 1)
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="removePartner(this)" title="Remove Partner">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    @else
                                        <div style="height: 38px;"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Signatory Details Section -->
            <div class="mb-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Signatory Details</h6>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addSignatory()">+ Add Signatory</button>
                </div>
                <div id="signatories_container">
                    @php
                        // Ensure we have at least one signatory entry
                        $signatoryEntries = $signatories->isEmpty() ? [['name' => '', 'designation' => '', 'contact' => '']] : $signatories;
                    @endphp
                    
                    @foreach($signatoryEntries as $index => $signatory)
                    <div class="signatory-entry mb-3 @if($index > 0) border-bottom pb-3 @endif">
                        <div class="row g-2">
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Signatory Name</label>
                                    <input type="text" class="form-control form-control-sm" name="signatory_name[]" 
                                        value="{{ old("signatory_name.$index", is_array($signatory) ? ($signatory['name'] ?? '') : ($signatory->name ?? '')) }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Designation *</label>
                                    <input type="text" class="form-control form-control-sm" name="signatory_designation[]" 
                                        value="{{ old("signatory_designation.$index", is_array($signatory) ? ($signatory['designation'] ?? '') : ($signatory->designation ?? '')) }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Contact Number *</label>
                                    <input type="tel" class="form-control form-control-sm" name="signatory_contact[]" 
                                        value="{{ old("signatory_contact.$index", is_array($signatory) ? ($signatory['contact'] ?? '') : ($signatory->contact ?? '')) }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2 d-flex align-items-end">
                                <div class="form-group mb-2 w-100">
                                    @if($index > 0 || count($signatoryEntries) > 1)
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSignatory(this)" title="Remove Signatory">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    @else
                                    <div style="height: 38px;"></div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- LLP Fields -->
    <div id="llp_fields" class="entity-specific-fields" 
     style="display: {{ old('entity_type', $application->entityDetails->entity_type ?? '') === 'llp' ? 'block' : 'none' }};">
        <div class="card mb-4">
            <div class="card-header bg-light p-2">
                <h6 class="mb-0">Limited Liability Partnership (LLP) Details</h6>
            </div>
            <div class="card-body">
                <!-- Basic LLP Information -->
                <div class="row g-2 mb-4">
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-2">
                            <label for="llpin_number" class="form-label small">LLPIN Number *</label>
                            <input type="text" class="form-control form-control-sm" id="llpin_number" name="llpin_number"
                                value="{{ old('llpin_number', $llpDetails->llpin_number ?? '') }}" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group mb-2">
                            <label for="llp_incorporation_date" class="form-label small">Date of Incorporation *</label>
                            <input type="date" class="form-control form-control-sm" id="llp_incorporation_date" name="llp_incorporation_date"
                                value="{{ old('llp_incorporation_date', optional($llpDetails->incorporation_date ? \Carbon\Carbon::parse($llpDetails->incorporation_date) : null)->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>

                <!-- Designated Partners Section -->
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Designated Partners Details</h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addLLPPartner()">+ Add Partner</button>
                    </div>
                    <div id="llp_partners_container">
                        @foreach($llpPartners as $index => $partner)
                        <div class="llp-partner-entry mb-3 @if($index > 0) border-bottom pb-3 @endif">
                            <div class="row g-2">
                                <div class="col-12 col-md-3">
                                    <div class="form-group mb-2">
                                        <label class="form-label small">Partner Name *</label>
                                        <input type="text" class="form-control form-control-sm" name="llp_partner_name[]" 
                                            value="{{ old("llp_partner_name.$index", $partner->name ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-2">
                                    <div class="form-group mb-2">
                                        <label class="form-label small">DPIN Number *</label>
                                        <input type="text" class="form-control form-control-sm" name="llp_partner_dpin[]" 
                                            value="{{ old("llp_partner_dpin.$index", $partner->dpin_number ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-2">
                                    <div class="form-group mb-2">
                                        <label class="form-label small">Contact Number *</label>
                                        <input type="tel" class="form-control form-control-sm" name="llp_partner_contact[]" 
                                            value="{{ old("llp_partner_contact.$index", $partner->contact ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="form-group mb-2">
                                        <label class="form-label small">Full Address *</label>
                                        <input type="text" class="form-control form-control-sm" name="llp_partner_address[]" 
                                            value="{{ old("llp_partner_address.$index", $partner->address ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-1 d-flex align-items-end">
                                    <div class="form-group mb-2 w-100">
                                        @if($index > 0 || $llpPartners->count() > 1)
                                        <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeLLPPartner(this)" title="Remove Partner">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                        @else
                                        <div style="height: 38px;"></div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Company Fields -->
    <div id="company_fields" class="entity-specific-fields" 
     style="display: {{ in_array(old('entity_type', $application->entityDetails->entity_type ?? ''), ['private_company', 'public_company']) ? 'block' : 'none' }};">
        <div class="card mb-4">
            <div class="card-header bg-light p-2">
                <h6 class="mb-0">Company Details</h6>
            </div>
            <div class="card-body">
                <!-- Basic Company Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">CIN Number *</label>
                            <input type="text" class="form-control" id="cin_number" name="cin_number"
                                value="{{ old('cin_number', $companyDetails->cin_number ?? '') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Date of Incorporation *</label>
                            <input type="date" class="form-control" id="incorporation_date" name="incorporation_date"
                                value="{{ old('incorporation_date', optional(\Carbon\Carbon::parse($companyDetails->incorporation_date ?? null))->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>

                <!-- Directors Section -->
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Directors Details</h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addDirector()">+ Add Director</button>
                    </div>
                    <div id="directors_container">
                        @foreach($directors as $index => $director)
                        <div class="director-entry mb-3 @if($index > 0) border-bottom pb-3 @endif">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Director Name *</label>
                                        <input type="text" class="form-control" name="director_name[]" 
                                            value="{{ old("director_name.$index", $director->name ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">DIN Number *</label>
                                        <input type="text" class="form-control" name="director_din[]" 
                                            value="{{ old("director_din.$index", $director->din_number ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Contact Number *</label>
                                        <input type="tel" class="form-control" name="director_contact[]" 
                                            value="{{ old("director_contact.$index", $director->contact ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Full Address *</label>
                                        <textarea class="form-control form-control-sm" name="director_address[]" rows="1" required>{{ old("director_address.$index", $director->address ?? '') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    @if($index > 0 || $directors->count() > 1)
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeDirector(this)">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cooperative Society Fields -->
    <div id="cooperative_fields" class="entity-specific-fields" 
     style="display: {{ old('entity_type', $application->entityDetails->entity_type ?? '') === 'cooperative_society' ? 'block' : 'none' }};">
        <div class="card mb-4">
            <div class="card-header bg-light p-2">
                <h6 class="mb-0">Cooperative Society Details</h6>
            </div>
            <div class="card-body">
                <!-- Basic Cooperative Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Registration Number *</label>
                            <input type="text" class="form-control" id="cooperative_reg_number" name="cooperative_reg_number"
                                value="{{ old('cooperative_reg_number', $cooperativeDetails->reg_number ?? '') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Registration Date *</label>
                            <input type="date" class="form-control" id="cooperative_reg_date" name="cooperative_reg_date"
                               value="{{ old('cooperative_reg_date', optional(\Carbon\Carbon::parse($cooperativeDetails->reg_date ?? null))->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>

                <!-- Committee Members Section -->
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Committee Members Details</h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addCommitteeMember()">+ Add Member</button>
                    </div>
                    <div id="committee_container">
                        @foreach($committeeMembers as $index => $member)
                        <div class="committee-entry mb-3 @if($index > 0) border-bottom pb-3 @endif">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Member Name *</label>
                                        <input type="text" class="form-control" name="committee_name[]" 
                                            value="{{ old("committee_name.$index", $member->name ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Designation *</label>
                                        <input type="text" class="form-control" name="committee_designation[]" 
                                            value="{{ old("committee_designation.$index", $member->designation ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Contact Number *</label>
                                        <input type="tel" class="form-control" name="committee_contact[]" 
                                            value="{{ old("committee_contact.$index", $member->contact ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Full Address *</label>
                                        <textarea class="form-control form-control-sm" name="committee_address[]" rows="1" required>{{ old("committee_address.$index", $member->address ?? '') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    @if($index > 0 || $committeeMembers->count() > 1)
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeCommitteeMember(this)">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Trust Fields -->
    <div id="trust_fields" class="entity-specific-fields" 
     style="display: {{ old('entity_type', $application->entityDetails->entity_type ?? '') === 'trust' ? 'block' : 'none' }};">
    <!-- ... rest of the content ... -->
        <div class="card mb-4">
            <div class="card-header bg-light p-2">
                <h6 class="mb-0">Trust Details</h6>
            </div>
            <div class="card-body">
                <!-- Basic Trust Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Registration Number *</label>
                            <input type="text" class="form-control" id="trust_reg_number" name="trust_reg_number"
                                value="{{ old('trust_reg_number', $trustDetails->reg_number ?? '') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Registration Date *</label>
                            <input type="date" class="form-control" id="trust_reg_date" name="trust_reg_date"
                                value="{{ old('trust_reg_date', optional(\Carbon\Carbon::parse($trustDetails->reg_date ?? null))->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>

                <!-- Trustees Section -->
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Trustees Details</h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addTrustee()">+ Add Trustee</button>
                    </div>
                    <div id="trustees_container">
                        @foreach($trustees as $index => $trustee)
                        <div class="trustee-entry mb-3 @if($index > 0) border-bottom pb-3 @endif">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Trustee Name *</label>
                                        <input type="text" class="form-control" name="trustee_name[]" 
                                            value="{{ old("trustee_name.$index", $trustee->name ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Designation *</label>
                                        <input type="text" class="form-control" name="trustee_designation[]" 
                                            value="{{ old("trustee_designation.$index", $trustee->designation ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Contact Number *</label>
                                        <input type="tel" class="form-control" name="trustee_contact[]" 
                                            value="{{ old("trustee_contact.$index", $trustee->contact ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Full Address *</label>
                                        <textarea class="form-control form-control-sm" name="trustee_address[]" rows="1" required>{{ old("trustee_address.$index", $trustee->address ?? '') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    @if($index > 0 || $trustees->count() > 1)
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeTrustee(this)">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Common Fields -->
    <div class="row g-2">
        <div class="col-12 col-md-4">
            <div class="form-group mb-2">
                <label for="business_address" class="form-label small">Business Place/Shop Address *</label>
                <input type="text" class="form-control form-control-sm" id="business_address" name="business_address" 
                    value="{{ old('business_address', $application->entityDetails->business_address ?? '') }}" required>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="form-group mb-2">
                <label for="house_no" class="form-label small">House No. / Building</label>
                <input type="number" class="form-control form-control-sm" id="house_no" name="house_no"
                    value="{{ old('house_no', $application->entityDetails->house_no ?? '') }}" required>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="form-group mb-2">
                <label for="landmark" class="form-label small">Landmark</label>
                <input type="text" class="form-control form-control-sm" id="landmark" name="landmark"
                    value="{{ old('landmark', $application->entityDetails->landmark ?? '') }}" required>
            </div>
        </div>
    </div>
    
<div class="row g-2">
    <div class="col-12 col-md-2">
        <div class="form-group mb-2">
            <label for="pincode" class="form-label small">Pincode *</label>
            <input type="text" class="form-control form-control-sm" id="pincode" name="pincode"
                   value="{{ old('pincode', $application->entityDetails->pincode ?? '') }}"
                   maxlength="6" inputmode="numeric" pattern="^[1-9][0-9]{5}$"
                   title="Please enter a valid 6-digit Indian pincode" required
                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,6);">
            <div class="invalid-feedback" id="pincode_error"></div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="form-group mb-2">
            <label for="state_id" class="form-label small">State *</label>
            <select class="form-select form-select-sm select2" id="state_id" name="state_id" required>
                <option value="">-- Select State --</option>
                @foreach($states as $state)
                    <option value="{{ $state->id }}"
                            {{ old('state_id', $application->entityDetails->state_id ?? '') == $state->id ? 'selected' : '' }}>
                        {{ $state->state_name }}
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback" id="state_id_error"></div>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="form-group mb-2">
            <label for="district_id" class="form-label small">District *</label>
            <select class="form-select form-select-sm select2" id="district_id" name="district_id" required>
                <option value="">Select District</option>
                @if($application->entityDetails && $application->entityDetails->district_id)
                    @php
                        $district = DB::table('core_district')->where('id', $application->entityDetails->district_id)->first();
                    @endphp
                    @if($district)
                        <option value="{{ $district->id }}" selected>{{ $district->district_name }}</option>
                    @endif
                @endif
            </select>
            <div class="invalid-feedback" id="district_id_error"></div>
        </div>
    </div>
    <div class="col-12 col-md-2">
        <div class="form-group mb-2">
            <label for="city" class="form-label small">City *</label>
            <div id="city_container">
                <input type="text" class="form-control form-control-sm" id="city" name="city"
                       value="{{ old('city', $application->entityDetails->city ?? '') }}" required>
            </div>
            <div class="invalid-feedback" id="city_error"></div>
        </div>
    </div>
    <div class="col-12 col-md-2">
        <div class="form-group mb-2">
            <label for="country" class="form-label small">Country *</label>
            <input type="text" class="form-control form-control-sm" id="country" name="country"
                   value="India" readonly>
            <input type="hidden" name="country_id" value="1">
        </div>
    </div>
</div>

<!-- Modal for multiple PIN code matches -->
<div class="modal fade" id="pincodeModal" tabindex="-1" aria-labelledby="pincodeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pincodeModalLabel">Select Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Multiple locations found for the entered PIN code. Please select one:</p>
                <select class="form-select" id="pincodeLocationSelect">
                    <option value="">Select a location</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="pincodeLocationConfirm">Confirm</button>
            </div>
        </div>
    </div>
</div>

    
    <!-- Documents Row 1: Entity Proof, Ownership Info, Bank Statement -->
<div class="row g-2 mb-3" id="documents_row_1">
    <div class="col-md-4" id="entity_proof_col">
        <label for="entity_proof_file" class="form-label small" id="entity_proof_label">Entity Proof Document *</label>
        <div class="input-group input-group-sm">
            <input type="file" class="form-control form-control-sm d-none" id="entity_proof_file" name="entity_proof_file" accept=".pdf,.jpg,.jpeg,.png">
            <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="entity_proof_file_upload_btn">Upload Entity Proof</button>
        </div>
        <div id="entity_proof_file_name" class="small text-muted {{ $entityProofDoc && $entityProofDoc['path'] ? '' : 'd-none' }} mt-1">
            @if($entityProofDoc && $entityProofDoc['path'])
                <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" 
                   data-src="{{ Storage::disk('s3')->url('Connect/Distributor/entity_proof/' . $entityProofDoc['path']) }}" 
                   data-type="Entity Proof Document">View</a> 
                   ({{ $entityProofDoc['original_filename'] ?? basename($entityProofDoc['path']) }})
                <input type="hidden" name="existing_entity_proof_file" value="{{ $entityProofDoc['path'] }}">
                <input type="hidden" name="existing_entity_proof_file_original" value="{{ $entityProofDoc['original_filename'] ?? basename($entityProofDoc['path']) }}">
            @else
                <span>No file uploaded</span>
            @endif
        </div>
    </div>
    <div class="col-md-4" id="ownership_info_col">
        <label for="ownership_info_file" class="form-label small" id="ownership_info_label">Ownership Information *</label>
        <div class="input-group input-group-sm">
            <input type="file" class="form-control form-control-sm d-none" id="ownership_info_file" name="ownership_info_file" accept=".pdf,.jpg,.jpeg,.png">
            <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="ownership_info_file_upload_btn">Upload Ownership Information</button>
        </div>
        <div id="ownership_info_file_name" class="small text-muted {{ $ownershipInfoDoc && $ownershipInfoDoc['path'] ? '' : 'd-none' }} mt-1">
            @if($ownershipInfoDoc && $ownershipInfoDoc['path'])
                <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" 
                   data-src="{{ Storage::disk('s3')->url('Connect/Distributor/ownership_info/' . $ownershipInfoDoc['path']) }}" 
                   data-type="Ownership Information">View</a> 
                   ({{ $ownershipInfoDoc['original_filename'] ?? basename($ownershipInfoDoc['path']) }})
                <input type="hidden" name="existing_ownership_info_file" value="{{ $ownershipInfoDoc['path'] }}">
                <input type="hidden" name="existing_ownership_info_file_original" value="{{ $ownershipInfoDoc['original_filename'] ?? basename($ownershipInfoDoc['path']) }}">
            @else
                <span>No file uploaded</span>
            @endif
        </div>
    </div>
    <div class="col-md-4" id="bank_statement_col">
        <label for="bank_statement_file" class="form-label small" id="bank_statement_label">Bank Statement (6 Months) *</label>
        <div class="input-group input-group-sm">
            <input type="file" class="form-control form-control-sm d-none" id="bank_statement_file" name="bank_statement_file" accept=".pdf,.jpg,.jpeg,.png">
            <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="bank_statement_file_upload_btn">Upload Bank Statement</button>
        </div>
        <div id="bank_statement_file_name" class="small text-muted {{ $bankStatementDoc && $bankStatementDoc['path'] ? '' : 'd-none' }} mt-1">
            @if($bankStatementDoc && $bankStatementDoc['path'])
                <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" 
                   data-src="{{ Storage::disk('s3')->url('Connect/Distributor/bank_statement/' . $bankStatementDoc['path']) }}" 
                   data-type="Bank Statement">View</a> 
                   ({{ $bankStatementDoc['original_filename'] ?? basename($bankStatementDoc['path']) }})
                <input type="hidden" name="existing_bank_statement_file" value="{{ $bankStatementDoc['path'] }}">
                <input type="hidden" name="existing_bank_statement_file_original" value="{{ $bankStatementDoc['original_filename'] ?? basename($bankStatementDoc['path']) }}">
            @else
                <span>No file uploaded</span>
            @endif
        </div>
    </div>
</div>

<!-- Documents Row 2: Credit Worthiness -->
<div class="row g-2 mb-3" id="documents_row_2">
    <div class="col-md-6" id="itr_acknowledgement_col">
        <label for="itr_acknowledgement_file" class="form-label small" id="itr_acknowledgement_label">Income Tax Return Acknowledgement *</label>
        <div class="input-group input-group-sm">
            <input type="file" class="form-control form-control-sm d-none" id="itr_acknowledgement_file" name="itr_acknowledgement_file" accept=".pdf,.jpg,.jpeg,.png">
            <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="itr_acknowledgement_file_upload_btn">Upload ITR Acknowledgement</button>
        </div>
        <div id="itr_acknowledgement_file_name" class="small text-muted {{ $itrAcknowledgementDoc && $itrAcknowledgementDoc['path'] ? '' : 'd-none' }} mt-1">
            @if($itrAcknowledgementDoc && $itrAcknowledgementDoc['path'])
                <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" 
                   data-src="{{ Storage::disk('s3')->url('Connect/Distributor/itr_acknowledgement/' . $itrAcknowledgementDoc['path']) }}" 
                   data-type="Income Tax Return Acknowledgement">View</a> 
                   ({{ $itrAcknowledgementDoc['original_filename'] ?? basename($itrAcknowledgementDoc['path']) }})
                <input type="hidden" name="existing_itr_acknowledgement_file" value="{{ $itrAcknowledgementDoc['path'] }}">
                <input type="hidden" name="existing_itr_acknowledgement_file_original" value="{{ $itrAcknowledgementDoc['original_filename'] ?? basename($itrAcknowledgementDoc['path']) }}">
            @else
                <span>No file uploaded</span>
            @endif
        </div>
    </div>
    <div class="col-md-6" id="balance_sheet_col">
        <label for="balance_sheet_file" class="form-label small" id="balance_sheet_label">Balancesheet of Latest FY</label>
        <div class="input-group input-group-sm">
            <input type="file" class="form-control form-control-sm d-none" id="balance_sheet_file" name="balance_sheet_file" accept=".pdf,.jpg,.jpeg,.png">
            <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="balance_sheet_file_upload_btn">Upload Balancesheet</button>
        </div>
        <div id="balance_sheet_file_name" class="small text-muted {{ $balanceSheetDoc && $balanceSheetDoc['path'] ? '' : 'd-none' }} mt-1">
            @if($balanceSheetDoc && $balanceSheetDoc['path'])
                <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" 
                   data-src="{{ Storage::disk('s3')->url('Connect/Distributor/balance_sheet/' . $balanceSheetDoc['path']) }}" 
                   data-type="Balancesheet of Latest FY">View</a> 
                   ({{ $balanceSheetDoc['original_filename'] ?? basename($balanceSheetDoc['path']) }})
                <input type="hidden" name="existing_balance_sheet_file" value="{{ $balanceSheetDoc['path'] }}">
                <input type="hidden" name="existing_balance_sheet_file_original" value="{{ $balanceSheetDoc['original_filename'] ?? basename($balanceSheetDoc['path']) }}">
            @else
                <span>No file uploaded</span>
            @endif
        </div>
    </div>
</div>

    
    <div class="row g-2">
        <div class="col-12 col-md-4">
            <div class="form-group mb-2">
                <label for="mobile" class="form-label small">Mobile Number *</label>
                <input type="tel" class="form-control form-control-sm" id="mobile" name="mobile"
                    value="{{ old('mobile', $application->entityDetails->mobile ?? '') }}" 
                    pattern="[0-9]{10}" maxlength="10" required 
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="form-group mb-2">
                <label for="email" class="form-label small">Email Address *</label>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control form-control-sm" id="email" name="email"
                        value="{{ old('email', $application->entityDetails->email ?? '') }}" required
                        oninput="validateEmail(this)">
                    <div class="input-group-text">
                        <input class="form-check-input mt-0" type="checkbox" id="no_email" name="no_email" 
                               {{ old('no_email') ? 'checked' : '' }}>
                        <label for="no_email" class="ms-1 small mb-0">Not Available</label>
                    </div>
                </div>
                <div class="invalid-feedback email-error">Please enter a valid email address (e.g., user@example.com).</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="form-group mb-2">
                <label for="tan_number" class="form-label small">TAN Number (if any)</label>
                <input type="text" class="form-control form-control-sm" id="tan_number" name="tan_number"
                    value="{{ old('tan_number', $application->entityDetails->tan_number ?? '') }}"
                    maxlength="10" oninput="validateTAN(this)">
                <div class="invalid-feedback tan-error">Please enter a valid TAN number (e.g., ABCD12345E).</div>
            </div>
        </div>
    </div>

    <!-- PAN Details -->
    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <label for="pan_file" class="form-label small">PAN Document *</label>
            <div class="input-group input-group-sm">
                <input type="file" class="form-control form-control-sm d-none" id="pan_file" name="pan_file" accept=".pdf,.jpg,.jpeg,.png">
                <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="pan_file_upload_btn">Upload PAN</button>
            </div>
            <div id="pan_file_name" class="small text-muted {{ $panDoc && $panDoc['path'] ? '' : 'd-none' }} mt-1">
                @if($panDoc && $panDoc['path'])
                    <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" 
                       data-src="{{ Storage::disk('s3')->url('Connect/Distributor/pan/' . $panDoc['path']) }}" 
                       data-type="PAN Document">View</a> 
                       ({{ $panDoc['original_filename'] ?? basename($panDoc['path']) }})
                    <input type="hidden" name="existing_pan_file" value="{{ $panDoc['path'] }}">
                    <input type="hidden" name="existing_pan_file_original" value="{{ $panDoc['original_filename'] ?? basename($panDoc['path']) }}">
                @endif
            </div>
        </div>
        <div class="col-md-4">
            <label for="pan_number" class="form-label small">PAN Number *</label>
            <div class="input-group input-group-sm">
                <input type="text" class="form-control form-control-sm" id="pan_number" name="pan_number" 
                    value="{{ old('pan_number', $application->entityDetails->pan_number ?? '') }}" 
                    pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}" maxlength="10" 
                    title="PAN must be 10 characters (e.g., ABCDE1234F)" required 
                    oninput="validatePAN(this)">
                <div class="input-group-text">
                    <input class="form-check-input mt-0" type="checkbox" id="pan_verified" name="pan_verified" 
                           {{ old('pan_verified', $application->entityDetails->pan_verified ?? false) ? 'checked' : '' }}>
                    <label for="pan_verified" class="ms-1 small mb-0">Verified</label>
                </div>
            </div>
            <div class="invalid-feedback pan-error">Please enter a valid PAN number (e.g., ABCDE1234F).</div>
        </div>
        <div class="col-md-4">
            <label for="gst_applicable" class="form-label small">GST Applicable *</label>
            <select class="form-control form-control-sm" id="gst_applicable" name="gst_applicable" required onchange="toggleGSTFields()">
                <option value="" disabled {{ old('gst_applicable') ? '' : 'selected' }}>-- Select --</option>
                <option value="yes" {{ old('gst_applicable', $application->entityDetails->gst_applicable ?? 'no') === 'yes' ? 'selected' : '' }}>Yes</option>
                <option value="no" {{ old('gst_applicable', $application->entityDetails->gst_applicable ?? 'no') === 'no' ? 'selected' : '' }}>No</option>
            </select>
        </div>
    </div>

    <!-- GST Details -->
    <div id="gst_fields" style="display: {{ old('gst_applicable', $application->entityDetails->gst_applicable ?? 'no') === 'yes' ? 'block' : 'none' }};" class="mb-3">
        <div class="row g-2">
            <div class="col-md-4">
                <label for="gst_file" class="form-label small">GST Document *</label>
                <div class="input-group input-group-sm">
                    <input type="file" class="form-control form-control-sm d-none" id="gst_file" name="gst_file" accept=".pdf,.jpg,.jpeg,.png">
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="gst_file_upload_btn">Upload GST</button>
                </div>
                <div id="gst_file_name" class="small text-muted {{ $gstDoc && $gstDoc['path'] ? '' : 'd-none' }} mt-1">
                    @if($gstDoc && $gstDoc['path'])
                        <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" 
                           data-src="{{ Storage::disk('s3')->url('Connect/Distributor/gst/' . $gstDoc['path']) }}" 
                           data-type="GST Document">View</a> 
                           ({{ $gstDoc['original_filename'] ?? basename($gstDoc['path']) }})
                        <input type="hidden" name="existing_gst_file" value="{{ $gstDoc['path'] }}">
                        <input type="hidden" name="existing_gst_file_original" value="{{ $gstDoc['original_filename'] ?? basename($gstDoc['path']) }}">
                    @endif
                </div>
                @error('gst_file')
    <div class="text-danger small mt-1">{{ $message }}</div>
@enderror
            </div>
            <div class="col-md-4">
                <label for="gst_number" class="form-label small">GST Number *</label>
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control form-control-sm" id="gst_number" name="gst_number" 
                        value="{{ old('gst_number', $application->entityDetails->gst_number ?? '') }}" 
                        pattern="[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}" maxlength="15" 
                        title="GST must be 15 characters (e.g., 22AAAAA0000A1Z5)" placeholder="22AAAAA0000A1Z5" required 
                        oninput="validateGST(this)">
                    <div class="input-group-text">
                        <input class="form-check-input mt-0" type="checkbox" id="gst_verified" name="gst_verified" 
                               {{ old('gst_verified', $application->entityDetails->gst_verified ?? false) ? 'checked' : '' }}>
                        <label for="gst_verified" class="ms-1 small mb-0">Verified</label>
                    </div>
                </div>
                <div class="invalid-feedback gst-error">Please enter a valid GST number (e.g., 22AAAAA0000A1Z5).</div>
            </div>
           
        </div>
    </div>

    <!-- Seed License Details -->
    <div class="row g-2 mb-3">
        <div class="col-md-4">
            <label for="seed_license_file" class="form-label small">Seed License Document *</label>
            <div class="input-group input-group-sm">
                <input type="file" class="form-control form-control-sm d-none" id="seed_license_file" name="seed_license_file" accept=".pdf,.jpg,.jpeg,.png">
                <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="seed_license_file_upload_btn">Upload License</button>
            </div>
            <div id="seed_license_file_name" class="small text-muted {{ $seedLicenseDoc && $seedLicenseDoc['path'] ? '' : 'd-none' }} mt-1">
                @if($seedLicenseDoc && $seedLicenseDoc['path'])
                    <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" 
                       data-src="{{ Storage::disk('s3')->url('Connect/Distributor/seed_license/' . $seedLicenseDoc['path']) }}" 
                       data-type="Seed License Document">View</a> 
                       ({{ $seedLicenseDoc['original_filename'] ?? basename($seedLicenseDoc['path']) }})
                    <input type="hidden" name="existing_seed_license_file" value="{{ $seedLicenseDoc['path'] }}">
                    <input type="hidden" name="existing_seed_license_file_original" value="{{ $seedLicenseDoc['original_filename'] ?? basename($seedLicenseDoc['path']) }}">
                @endif
            </div>
        </div>
        <div class="col-md-4">
            <label for="seed_license" class="form-label small">License Number *</label>
            <div class="input-group input-group-sm">
                <input type="text" class="form-control form-control-sm" id="seed_license" name="seed_license" 
                    value="{{ old('seed_license', $application->entityDetails->seed_license ?? '') }}" 
                    pattern="[A-Z0-9]{6,15}" maxlength="15" 
                    title="Seed License must be 6-15 alphanumeric characters" required 
                    oninput="validateSeedLicense(this)">
                <div class="input-group-text">
                    <input class="form-check-input mt-0" type="checkbox" id="seed_license_verified" name="seed_license_verified" 
                           {{ old('seed_license_verified', $application->entityDetails->seed_license_verified ?? false) ? 'checked' : '' }}>
                    <label for="seed_license_verified" class="ms-1 small mb-0">Verified</label>
                </div>
            </div>
            <div class="invalid-feedback license-error">Please enter a valid Seed License number (6-15 alphanumeric characters).</div>
        </div>
        <div class="col-md-4">
            <label for="seed_license_validity" class="form-label small">Validity Date *</label>
            <input type="date" class="form-control form-control-sm" id="seed_license_validity" name="seed_license_validity" 
                min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" 
                value="{{ old('seed_license_validity', optional($application->entityDetails)->seed_license_validity?->format('Y-m-d') ?? '') }}"required
                >
        </div>
    </div>

    <!-- Bank Details -->
    <div class="row g-2 mb-3">
        <div class="col-md-2">
            <label for="bank_file" class="form-label small">Bank Document *</label>
            <div class="input-group input-group-sm">
                <input type="file" class="form-control form-control-sm d-none" id="bank_file" name="bank_file" accept=".pdf,.jpg,.jpeg,.png">
                <button type="button" class="btn btn-sm btn-outline-secondary w-100" id="bank_file_upload_btn">Upload Bank Doc</button>
            </div>
            <div id="bank_file_name" class="small text-muted {{ $bankDoc && $bankDoc['path'] ? '' : 'd-none' }} mt-1">
                @if($bankDoc && $bankDoc['path'])
                    <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" 
                       data-src="{{ Storage::disk('s3')->url('Connect/Distributor/bank/' . $bankDoc['path']) }}" 
                       data-type="Bank Document">View</a> 
                       ({{ $bankDoc['original_filename'] ?? basename($bankDoc['path']) }})
                    <input type="hidden" name="existing_bank_file" value="{{ $bankDoc['path'] }}">
                    <input type="hidden" name="existing_bank_file_original" value="{{ $bankDoc['original_filename'] ?? basename($bankDoc['path']) }}">
                @endif
            </div>
        </div>
        <div class="col-md-2">
            <label for="account_number" class="form-label small">Account Number *</label>
            <input type="number" class="form-control form-control-sm" id="account_number" name="account_number" 
                value="{{ old('account_number', $application->entityDetails->account_number ?? '') }}" 
                inputmode="numeric" pattern="[0-9]+" required>
        </div>
        <div class="col-md-2">
            <label for="ifsc_code" class="form-label small">IFSC Code *</label>
            <input type="text" class="form-control form-control-sm" id="ifsc_code" name="ifsc_code" 
                value="{{ old('ifsc_code', $application->entityDetails->ifsc_code ?? '') }}" required>
        </div>
        <div class="col-md-3">
            <label for="bank_name" class="form-label small">Bank Name *</label>
            <input type="text" class="form-control form-control-sm" id="bank_name" name="bank_name" 
                value="{{ old('bank_name', $application->entityDetails->bank_name ?? '') }}" readonly required>
        </div>
        <div class="col-md-3">
            <label for="account_holder" class="form-label small">Account Holder *</label>
            <input type="text" class="form-control form-control-sm" id="account_holder" name="account_holder" 
                value="{{ old('account_holder', $application->entityDetails->account_holder_name ?? '') }}" readonly required>
        </div>
    </div>

    <!-- Authorized Persons Section -->
<div class="card mb-2">
    <div class="card-header bg-light p-2 mt-2">
        <h6 class="mb-0 fs-6">Authorized Persons Details</h6>
    </div>
    <div class="card-body p-2">
        <div class="mb-2">
            <label for="has_authorized_persons" class="form-label small">Do you have authorized persons?</label>
            <select id="has_authorized_persons" name="has_authorized_persons" class="form-select form-select-sm">
                <option value="no" {{ old('has_authorized_persons', $application->entityDetails->has_authorized_persons ?? 'no') === 'no' ? 'selected' : '' }}>No</option>
                <option value="yes" {{ old('has_authorized_persons', $application->entityDetails->has_authorized_persons ?? 'no') === 'yes' ? 'selected' : '' }}>Yes</option>
            </select>
        </div>

        <div id="authorized_persons_section" class="{{ $showAuthorizedPersonsSection ? '' : 'd-none' }}">
            @if($showAuthorizedPersonsSection)
                <div class="alert alert-info small mb-3">
                    <i class="ri-information-line me-1"></i>
                    Please provide details of all authorized persons who can represent this entity.
                </div>
            @endif
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover compact-table table-sm" id="authorized_persons_table">
                    <thead>
                        <tr>
                            <th class="form-label fw-normal small">Name *</th>
                            <th class="form-label fw-normal small">Contact Number *</th>
                            <th class="form-label fw-normal small">Email Address</th>
                            <th class="form-label fw-normal small">Full Address *</th>
                            <th class="form-label fw-normal small">Relation *</th>
                            <th class="form-label fw-normal small">Letter of Authorisation *</th>
                            <th class="form-label fw-normal small">Aadhar Upload *</th>
                            <th class="form-label fw-normal small">Aadhar Number *</th>
                            <th class="form-label fw-normal small">Action</th>
                        </tr>
                    </thead>
                    <tbody id="authorized_persons_container">
                        @forelse($authPersonsArray as $index => $person)
                            <tr class="authorized-person-entry" data-index="{{ $index }}">
                                <!-- Name -->
                                <td data-label="Name">
                                    <input type="text" 
                                           class="form-control form-control-sm"
                                           name="auth_person_name[]" 
                                           value="{{ old("auth_person_name.$index", $person['name'] ?? '') }}"
                                           required
                                           placeholder="Enter full name">
                                    <div class="invalid-feedback">Please enter a valid name</div>
                                </td>

                                <!-- Contact Number -->
                                <td data-label="Contact Number">
                                    <input type="tel" 
                                           class="form-control form-control-sm"
                                           name="auth_person_contact[]"
                                           value="{{ old("auth_person_contact.$index", $person['contact'] ?? '') }}"
                                           pattern="[0-9]{10}"
                                           maxlength="10"
                                           required
                                           placeholder="10-digit number"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);">
                                    <div class="invalid-feedback">Please enter a valid 10-digit phone number</div>
                                </td>

                                <!-- Email Address -->
                                <td data-label="Email Address">
                                    <input type="email" 
                                           class="form-control form-control-sm"
                                           name="auth_person_email[]"
                                           value="{{ old("auth_person_email.$index", $person['email'] ?? '') }}"
                                           placeholder="example@email.com">
                                    <div class="invalid-feedback">Please enter a valid email address</div>
                                </td>

                                <!-- Full Address -->
                                <td data-label="Full Address">
                                    <textarea class="form-control form-control-sm"
                                              name="auth_person_address[]"
                                              rows="2"
                                              required
                                              placeholder="Enter complete address">{{ old("auth_person_address.$index", $person['address'] ?? '') }}</textarea>
                                    <div class="invalid-feedback">Please enter the full address</div>
                                </td>

                                <!-- Relation -->
                                <td data-label="Relation">
                                    <input type="text" 
                                           class="form-control form-control-sm"
                                           name="auth_person_relation[]"
                                           value="{{ old("auth_person_relation.$index", $person['relation'] ?? '') }}"
                                           required
                                           placeholder="e.g., Manager, Director, Authorized Representative">
                                    <div class="invalid-feedback">Please specify the relation</div>
                                </td>

                                <!-- Letter of Authorization -->
                                <td data-label="Letter of Authorisation">
                                    <input type="file" 
                                           class="form-control form-control-sm d-none" 
                                           id="auth_person_letter_{{ $index }}" 
                                           name="auth_person_letter[]" 
                                           accept=".pdf,.doc,.docx" 
                                           onchange="handleAuthPersonFileChange(this, {{ $index }}, 'letter')"
                                           required>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary w-100 text-start" 
                                            id="auth_person_letter_{{ $index }}_upload_btn">
                                        <i class="ri-upload-cloud-line me-1"></i>Upload
                                    </button>
                                    @if($person['letter_path'])
                                        <div id="auth_person_letter_{{ $index }}_name" class="small text-success mt-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <a href="#" 
                                                   class="text-decoration-none" 
                                                   data-bs-toggle="modal" 
                                                   data-bs-target="#documentModal"
                                                   data-src="{{ Storage::disk('s3')->url('Connect/Distributor/authorized_persons/' . $person['letter_path']) }}"
                                                   data-type="Letter of Authorisation">
                                                    <i class="ri-eye-line me-1 text-primary"></i>View
                                                </a>
                                                <span class="badge bg-success">Uploaded</span>
                                            </div>
                                            <small class="text-muted d-block">{{ $person['letter_original_filename'] }}</small>
                                            <input type="hidden" name="existing_auth_person_letter[{{ $index }}]" value="{{ $person['letter_path'] }}">
                                            <input type="hidden" name="existing_auth_person_letter_original[{{ $index }}]" value="{{ $person['letter_original_filename'] }}">
                                        </div>
                                    @else
                                        <div id="auth_person_letter_{{ $index }}_name" class="small text-muted mt-1 d-none"></div>
                                    @endif
                                    <div class="invalid-feedback">Letter of Authorization is required</div>
                                </td>
                                
                                <!-- Aadhar Document -->
                                <td data-label="Aadhar Document">
                                    <input type="file" 
                                           class="form-control form-control-sm d-none" 
                                           id="auth_person_aadhar_{{ $index }}" 
                                           name="auth_person_aadhar[]" 
                                           accept=".pdf,.jpg,.jpeg,.png" 
                                           onchange="handleAuthPersonFileChange(this, {{ $index }}, 'aadhar')"
                                           required>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary w-100 text-start" 
                                            id="auth_person_aadhar_{{ $index }}_upload_btn">
                                        <i class="ri-upload-cloud-line me-1"></i>Upload
                                    </button>
                                    @if($person['aadhar_path'])
                                        <div id="auth_person_aadhar_{{ $index }}_name" class="small text-success mt-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <a href="#" 
                                                   class="text-decoration-none" 
                                                   data-bs-toggle="modal" 
                                                   data-bs-target="#documentModal"
                                                   data-src="{{ Storage::disk('s3')->url('Connect/Distributor/authorized_persons/' . $person['aadhar_path']) }}"
                                                   data-type="Aadhar Document">
                                                    <i class="ri-eye-line me-1 text-primary"></i>View
                                                </a>
                                                <span class="badge bg-success">Uploaded</span>
                                            </div>
                                            <small class="text-muted d-block">{{ $person['aadhar_original_filename'] }}</small>
                                            <input type="hidden" name="existing_auth_person_aadhar[{{ $index }}]" value="{{ $person['aadhar_path'] }}">
                                            <input type="hidden" name="existing_auth_person_aadhar_original[{{ $index }}]" value="{{ $person['aadhar_original_filename'] }}">
                                        </div>
                                    @else
                                        <div id="auth_person_aadhar_{{ $index }}_name" class="small text-muted mt-1 d-none"></div>
                                    @endif
                                    <div class="invalid-feedback">Aadhar document is required</div>
                                </td>
                                
                                <!-- Aadhar Number -->
                                <td data-label="Aadhar Number">
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           name="auth_person_aadhar_number[]"
                                           id="auth_person_aadhar_number_{{ $index }}"
                                           value="{{ old("auth_person_aadhar_number.$index", $person['aadhar_number'] ?? '') }}"
                                           placeholder="Will be auto-filled"
                                           readonly
                                           {{ $person['aadhar_number'] ? 'class=bg-light' : '' }}>
                                    <div class="invalid-feedback">Aadhar number is invalid</div>
                                    @if($person['aadhar_number'])
                                        <small class="text-success"><i class="ri-check-line"></i> Extracted</small>
                                    @endif
                                </td>
                                
                                <!-- Action -->
                                <td data-label="Action">
                                    @if($index > 0 || count($authPersonsArray) > 1)
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                onclick="removeAuthorizedPerson(this)"
                                                title="Remove this authorized person">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    @else
                                        <span class="text-muted small">Required</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            @if($showAuthorizedPersonsSection)
                                {{-- Show empty row when section is enabled but no data exists --}}
                                <tr class="authorized-person-entry" data-index="0">
                                    <td data-label="Name">
                                        <input type="text" class="form-control form-control-sm" name="auth_person_name[]" required placeholder="Enter full name">
                                        <div class="invalid-feedback">Please enter a valid name</div>
                                    </td>
                                    <td data-label="Contact Number">
                                        <input type="tel" class="form-control form-control-sm" name="auth_person_contact[]" required pattern="[0-9]{10}" maxlength="10" placeholder="10-digit number" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);">
                                        <div class="invalid-feedback">Please enter a valid 10-digit phone number</div>
                                    </td>
                                    <td data-label="Email Address">
                                        <input type="email" class="form-control form-control-sm" name="auth_person_email[]" placeholder="example@email.com">
                                        <div class="invalid-feedback">Please enter a valid email address</div>
                                    </td>
                                    <td data-label="Full Address">
                                        <textarea class="form-control form-control-sm" name="auth_person_address[]" rows="2" required placeholder="Enter complete address"></textarea>
                                        <div class="invalid-feedback">Please enter the full address</div>
                                    </td>
                                    <td data-label="Relation">
                                        <input type="text" class="form-control form-control-sm" name="auth_person_relation[]" required placeholder="e.g., Manager, Director">
                                        <div class="invalid-feedback">Please specify the relation</div>
                                    </td>
                                    <td data-label="Letter of Authorisation">
                                        <input type="file" class="form-control form-control-sm d-none" id="auth_person_letter_0" name="auth_person_letter[]" accept=".pdf,.doc,.docx" onchange="handleAuthPersonFileChange(this, 0, 'letter')" required>
                                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-start" id="auth_person_letter_0_upload_btn">
                                            <i class="ri-upload-cloud-line me-1"></i>Upload
                                        </button>
                                        <div id="auth_person_letter_0_name" class="small text-muted mt-1 d-none"></div>
                                        <div class="invalid-feedback">Letter of Authorization is required</div>
                                    </td>
                                    <td data-label="Aadhar Document">
                                        <input type="file" class="form-control form-control-sm d-none" id="auth_person_aadhar_0" name="auth_person_aadhar[]" accept=".pdf,.jpg,.jpeg,.png" onchange="handleAuthPersonFileChange(this, 0, 'aadhar')" required>
                                        <button type="button" class="btn btn-sm btn-outline-secondary w-100 text-start" id="auth_person_aadhar_0_upload_btn">
                                            <i class="ri-upload-cloud-line me-1"></i>Upload
                                        </button>
                                        <div id="auth_person_aadhar_0_name" class="small text-muted mt-1 d-none"></div>
                                        <div class="invalid-feedback">Aadhar document is required</div>
                                    </td>
                                    <td data-label="Aadhar Number">
                                        <input type="text" class="form-control form-control-sm" name="auth_person_aadhar_number[]" id="auth_person_aadhar_number_0" placeholder="Will be auto-filled" readonly>
                                        <div class="invalid-feedback">Aadhar number is invalid</div>
                                    </td>
                                    <td data-label="Action">
                                        <span class="text-muted small">Required</span>
                                    </td>
                                </tr>
                            @endif
                        @endforelse
                    </tbody>
                </table>
                  <div class="mt-2 text-end">
                    <button type="button" class="btn btn-sm btn-primary" onclick="addAuthorizedPerson()">
                        <i class="ri-add-line me-1"></i>Add Another Person
                    </button>
                </div>
            </div>
            
          
        </div>
    </div>
</div>

    <!-- Document Modal -->
    <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentModalLabel">Document Viewer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="documentViewer" style="width: 100%; height: 500px;">
                        <!-- Document will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="original_entity_type" value="{{ old('entity_type', $application->entityDetails->entity_type ?? '') }}">
</div>
@push('styles')
<style>
    #documents_row_1, #documents_row_2 {
        display: flex !important; /* Use flex instead of block */
    flex-wrap: wrap; /* Allow wrapping if needed */
    }
    #entity_proof_col, #ownership_info_col, #bank_statement_col, #itr_acknowledgement_col, #balance_sheet_col {
        display: none;
    }
    #documents_row_1, #documents_row_2 {
    display: none;
}
</style>
@endpush
@push('scripts')
<script>
    
    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
          function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
        const uploadButtons = document.querySelectorAll('[id$="_upload_btn"]');
        uploadButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
             // Initialize Select2 for state and district dropdowns
    $('#state_id').select2({
        placeholder: '-- Select State --',
        allowClear: true,
        width: '100%'
    });

    $('#district_id').select2({
        placeholder: 'Select District',
        allowClear: true,
        width: '100%'
    });

    // Load districts based on state (for manual selection)
    $('#state_id').on('change', function() {
        const stateId = $(this).val();
        $('#district_id').prop('disabled', true).html('<option value="">Loading...</option>');

        if (stateId) {
            $.ajax({
                url: '/get-districts/' + stateId,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    let options = '<option value="">Select District</option>';
                    $.each(data, function(index, district) {
                        options += `<option value="${district.id}" ${district.id == '{{ old("district_id", $application->entityDetails->district_id ?? "") }}' ? 'selected' : ''}>${district.district_name}</option>`;
                    });
                    $('#district_id').html(options).prop('disabled', false);
                    $('#district_id').trigger('change');
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr.responseText);
                    $('#district_id').html('<option value="">Error loading districts</option>').prop('disabled', false);
                    $('#district_id').trigger('change');
                }
            });
        } else {
            $('#district_id').html('<option value="">Select District</option>').prop('disabled', false);
            $('#district_id').trigger('change');
        }
    });

    // Load location data based on PIN code
    const fetchLocationByPincode = debounce(function(pincode) {
        const pincodeError = $('#pincode_error');
        const stateSelect = $('#state_id');
        const districtSelect = $('#district_id');
        const cityContainer = $('#city_container');
        const cityError = $('#city_error');

        // Clear previous errors
        pincodeError.text('');
        stateSelect.closest('.form-group').find('.invalid-feedback').text('');
        districtSelect.closest('.form-group').find('.invalid-feedback').text('');
        cityError.text('');

        // Reset city to text input
        cityContainer.html('<input type="text" class="form-control form-control-sm" id="city" name="city" value="" required>');

        if (pincode.length === 6 && /^[1-9][0-9]{5}$/.test(pincode)) {
            $.ajax({
                url: '/get-location-by-pincode/' + pincode,
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    stateSelect.prop('disabled', true).html('<option value="">Loading...</option>');
                    districtSelect.prop('disabled', true).html('<option value="">Loading...</option>');
                    cityContainer.find('#city').val('').prop('disabled', true);
                },
                success: function(response) {
                    if (!response.success || response.data.length === 0) {
                        pincodeError.text('No locations found for this PIN code');
                        stateSelect.html('<option value="">-- Select State --</option>@foreach($states as $state)<option value="{{ $state->id }}" {{ old('state_id', $application->entityDetails->state_id ?? '') == $state->id ? 'selected' : '' }}>{{ $state->state_name }}</option>@endforeach').prop('disabled', false);
                        districtSelect.html('<option value="">Select District</option>').prop('disabled', false);
                        cityContainer.html('<input type="text" class="form-control form-control-sm" id="city" name="city" value="" required>').find('#city').prop('disabled', false);
                        return;
                    }

                    const locations = response.data;
                    const uniqueStates = [...new Set(locations.map(loc => loc.state_id))];
                    const uniqueDistricts = [...new Set(locations.map(loc => loc.district_id))];
                    const uniqueCities = [...new Set(locations.map(loc => loc.city))];

                    // Auto-select state (assuming one state per PIN code)
                    if (uniqueStates.length === 1) {
                        const state = locations[0];
                        stateSelect.html(`<option value="${state.state_id}" selected>${state.state_name}</option>`).prop('disabled', false);
                    } else {
                        console.warn('Multiple states found for PIN code:', pincode);
                        stateSelect.html('<option value="">-- Select State --</option>@foreach($states as $state)<option value="{{ $state->id }}" {{ old('state_id', $application->entityDetails->state_id ?? '') == $state->id ? 'selected' : '' }}>{{ $state->state_name }}</option>@endforeach').prop('disabled', false);
                    }

                    // Handle districts
                    if (uniqueDistricts.length === 1) {
                        // Single district: Auto-select
                        const district = locations[0];
                        districtSelect.html(`<option value="${district.district_id}" selected>${district.district_name}</option>`).prop('disabled', false);
                    } else {
                        // Multiple districts: Populate dropdown
                        let districtOptions = '<option value="">Select District</option>';
                        uniqueDistricts.forEach(districtId => {
                            const district = locations.find(loc => loc.district_id === districtId);
                            districtOptions += `<option value="${district.district_id}">${district.district_name}</option>`;
                        });
                        districtSelect.html(districtOptions).prop('disabled', false);
                    }

                    // Handle cities
                    if (uniqueCities.length === 1) {
                        // Single city: Use text input
                        cityContainer.html(`<input type="text" class="form-control form-control-sm" id="city" name="city" value="${locations[0].city}" required>`).find('#city').prop('disabled', false);
                    } else {
                        // Multiple cities: Use select
                        let cityOptions = '<option value="">Select City</option>';
                        uniqueCities.forEach(city => {
                            cityOptions += `<option value="${city}">${city}</option>`;
                        });
                        cityContainer.html(`<select class="form-select form-select-sm select2" id="city" name="city" required>${cityOptions}</select>`);
                        cityContainer.find('#city').select2({
                            placeholder: 'Select City',
                            allowClear: true,
                            width: '100%'
                        }).prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr.responseText);
                    pincodeError.text('Error fetching location data: ' + (xhr.responseJSON?.message || 'Server error'));
                    stateSelect.html('<option value="">-- Select State --</option>@foreach($states as $state)<option value="{{ $state->id }}" {{ old('state_id', $application->entityDetails->state_id ?? '') == $state->id ? 'selected' : '' }}>{{ $state->state_name }}</option>@endforeach').prop('disabled', false);
                    districtSelect.html('<option value="">Select District</option>').prop('disabled', false);
                    cityContainer.html('<input type="text" class="form-control form-control-sm" id="city" name="city" value="" required>').find('#city').prop('disabled', false);
                }
            });
        } else {
            // Reset fields if PIN code is invalid
            stateSelect.html('<option value="">-- Select State --</option>@foreach($states as $state)<option value="{{ $state->id }}" {{ old('state_id', $application->entityDetails->state_id ?? '') == $state->id ? 'selected' : '' }}>{{ $state->state_name }}</option>@endforeach').prop('disabled', false);
            districtSelect.html('<option value="">Select District</option>').prop('disabled', false);
            cityContainer.html('<input type="text" class="form-control form-control-sm" id="city" name="city" value="" required>').find('#city').prop('disabled', false);
        }
    }, 300);

    $('#pincode').on('input', function() {
        fetchLocationByPincode(this.value);
    });

    // Trigger state change on page load if a state is pre-selected (for manual edits)
    if ($('#state_id').val()) {
        $('#state_id').trigger('change');
    }

        // Handle form validation to ensure Select2 fields are validated
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
            const pincode = $('#pincode').val();
            const stateId = $('#state_id').val();
            const districtId = $('#district_id').val();
            const city = $('#city').val();

            let hasError = false;

            // Clear previous errors
            $('#pincode_error').text('');
            $('#state_id_error').text('');
            $('#district_id_error').text('');
            $('#city_error').text('');

            if (!pincode || !/^[1-9][0-9]{5}$/.test(pincode)) {
                $('#pincode_error').text('Please enter a valid 6-digit Indian PIN code');
                hasError = true;
            }
            if (!stateId) {
                $('#state_id_error').text('Please select a state');
                hasError = true;
            }
            if (!districtId) {
                $('#district_id_error').text('Please select a district');
                hasError = true;
            }
            if (!city) {
                $('#city_error').text('Please select or enter a city');
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
                $('.invalid-feedback').filter(function() { return $(this).text(); }).first().parent().get(0).scrollIntoView({ behavior: 'smooth' });
            }
          });
        }
        // Handle modal document loading
        const documentModal = document.getElementById('documentModal');
        if (documentModal) {
            documentModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const src = button.getAttribute('data-src');
                const type = button.getAttribute('data-type');
                const modalTitle = document.getElementById('documentModalLabel');
                const documentViewer = document.getElementById('documentViewer');

                modalTitle.textContent = type;
                documentViewer.innerHTML = '';

                if (src.endsWith('.pdf')) {
                    const iframe = document.createElement('iframe');
                    iframe.src = src;
                    iframe.style.width = '100%';
                    iframe.style.height = '100%';
                    documentViewer.appendChild(iframe);
                } else {
                    const img = document.createElement('img');
                    img.src = src;
                    img.style.maxWidth = '100%';
                    img.style.maxHeight = '100%';
                    img.style.objectFit = 'contain';
                    documentViewer.appendChild(img);
                }
            });
        }

        // Email toggle
        const emailInput = document.getElementById('email');
        const noEmailCheckbox = document.getElementById('no_email');
        function toggleEmailField() {
            if (noEmailCheckbox && emailInput) {
                if (noEmailCheckbox.checked) {
                    emailInput.value = '';
                    emailInput.removeAttribute('required');
                    emailInput.classList.remove('is-invalid');
                    emailInput.disabled = true;
                } else {
                    emailInput.setAttribute('required', 'required');
                    emailInput.disabled = false;
                }
            }
        }
        if (noEmailCheckbox) {
            toggleEmailField();
            noEmailCheckbox.addEventListener('change', toggleEmailField);
        }

        // Initialize fields
        showRelevantFields();
        toggleGSTFields();

        // Entity and GST change handlers
        const entityDetails = document.getElementById('entity-details');
        if (entityDetails) {
            entityDetails.addEventListener('change', function(e) {
                if (e.target.id === 'entity_type') {
                    showRelevantFields();
                } else if (e.target.id === 'gst_applicable') {
                    toggleGSTFields();
                }
            });
        }


        // DOB validation
        const dobInputs = document.querySelectorAll('.dob-input');
        dobInputs.forEach(dobInput => {
            const ageInput = dobInput.closest('.col-12').nextElementSibling?.querySelector('.age-display');
            let errorDiv = dobInput.parentNode.querySelector('.invalid-feedback.dob-error');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.classList.add('invalid-feedback', 'dob-error');
                dobInput.parentNode.appendChild(errorDiv);
            }
            const form = dobInput.closest('form');
            const validateDOB = () => {
                dobInput.classList.remove('is-invalid');
                errorDiv.textContent = '';
                const dob = new Date(dobInput.value);
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const m = today.getMonth() - dob.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                    age--;
                }
                if (ageInput) {
                    ageInput.value = age > 0 ? age : '';
                }
                if (dobInput.value && age < 18) {
                    dobInput.classList.add('is-invalid');
                    errorDiv.textContent = 'Must be at least 18 years old.';
                    if (form) {
                        const submitBtn = form.querySelector('button[type="submit"]');
                        if (submitBtn) submitBtn.disabled = true;
                    }
                    return false;
                } else {
                    dobInput.classList.remove('is-invalid');
                    errorDiv.textContent = '';
                    if (form) {
                        const submitBtn = form.querySelector('button[type="submit"]');
                        if (submitBtn) submitBtn.disabled = false;
                    }
                    return true;
                }
            };
            dobInput.addEventListener('change', validateDOB);
            dobInput.addEventListener('blur', validateDOB);
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!validateDOB()) {
                        e.preventDefault();
                        dobInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        dobInput.focus();
                    }
                });
            }
            if (dobInput.value) {
                validateDOB();
            }
        });
        

        // File input handlers with loader
        const fileFields = [{
                id: 'pan_file',
                processor: processPANCard,
                endpoint: '/process-pan-card',
                type: 'pan'
            },
            {
                id: 'bank_file',
                processor: processBankDocument,
                endpoint: '/process-bank-document',
                type: 'bank'
            },
            {
                id: 'seed_license_file',
                processor: processSeedLicense,
                endpoint: '/process-seed-license',
                type: 'seed_license'
            },
            {
                id: 'gst_file',
                processor: processGSTDocument,
                endpoint: '/process-gst-document',
                type: 'gst'
            },
            {
                id: 'entity_proof_file',
                processor: processDocument,
                endpoint: '/process-document',
                type: 'entity_proof',
                displayType: 'Entity Proof'
            },
            {
                id: 'ownership_info_file',
                processor: processDocument,
                endpoint: '/process-document',
                type: 'ownership_info',
                displayType: 'Ownership Information'
            },
            {
                id: 'bank_statement_file',
                processor: processDocument,
                endpoint: '/process-document',
                type: 'bank_statement',
                displayType: 'Bank Statement'
            },
            {
                id: 'itr_acknowledgement_file',
                processor: processDocument,
                endpoint: '/process-document',
                type: 'itr_acknowledgement',
                displayType: 'Income Tax Return Acknowledgement'
            },
            {
                id: 'balance_sheet_file',
                processor: processDocument,
                endpoint: '/process-document',
                type: 'balance_sheet',
                displayType: 'Balancesheet of Latest FY'
            }
        ];
        const globalLoader = document.getElementById('global-loader');

        fileFields.forEach(field => {
        const uploadBtn = document.getElementById(`${field.id}_upload_btn`);
        const fileInput = document.getElementById(field.id);
        const fileNameDiv = document.getElementById(`${field.id}_name`);

        // Debugging to check if elements are found
        console.log(`Setting up field: ${field.id}`, {
            uploadBtn: !!uploadBtn,
            uploadBtnId: uploadBtn?.id,
            fileInput: !!fileInput,
            fileInputId: fileInput?.id,
            fileNameDiv: !!fileNameDiv,
            fileNameDivId: fileNameDiv?.id
        });

        if (uploadBtn && fileInput && fileNameDiv) {
            uploadBtn.addEventListener('click', () => {
                console.log(`Upload button clicked for ${field.id}`);
                fileInput.click();
            });
            fileInput.addEventListener('change', function(e) {
                console.log(`File input changed for ${field.id}`, e.target.files);
                handleFileChange(this, field.id);
                if (e.target.files.length) {
                    globalLoader.style.display = 'block';
                    fileNameDiv.textContent = `Processing ${field.id.replace('_file', '').replace('_', ' ')}...`;

                    const existingFileInput = document.querySelector(`input[name="existing_${field.type}_file"]`);
                    const existingFile = existingFileInput ? existingFileInput.value : '';

                    field.processor(e.target.files[0], fileNameDiv, existingFile, field)
                        .then((result) => {
                            const existingFileInput = document.querySelector(`input[name="existing_${field.type}_file"]`);
                            const existingFileOriginal = document.querySelector(`input[name="existing_${field.type}_file_original"]`);
                            if (existingFileInput) existingFileInput.value = result.data?.filename || '';
                            if (existingFileOriginal) existingFileOriginal.value = result.data?.displayName || '';
                            fileInput.value = ''; // Clear file input
                        })
                        .catch((error) => {
                            console.error(`Error processing ${field.id}:`, error);
                            alert(`Failed to process ${field.id.replace('_file', '').replace('_', ' ')}. Please check the document and try again.`);
                        })
                        .finally(() => {
                            globalLoader.style.display = 'none';
                        });
                }
            });
        } else {
            console.error(`Failed to set up field: ${field.id}`, {
                uploadBtn: !!uploadBtn,
                uploadBtnId: uploadBtn?.id,
                fileInput: !!fileInput,
                fileInputId: fileInput?.id,
                fileNameDiv: !!fileNameDiv,
                fileNameDivId: fileNameDiv?.id
            });
        }

      });


//       document.addEventListener('click', function (e) {
//     // Check if the clicked element is an Upload Aadhar button
//     if (e.target && e.target.id && e.target.id.includes('partner_aadhar_') && e.target.id.endsWith('_upload_btn')) {
//         const index = e.target.id.split('_')[2]; // Extract partner index
//         const fileInput = document.getElementById(`partner_aadhar_${index}`);
//         if (fileInput) {
//             fileInput.click(); // Trigger hidden file input
//         }
//     }
// });

// document.addEventListener('change', function (e) {
//     // Check if the changed element is a partner Aadhar file input
//     if (e.target && e.target.id && e.target.id.startsWith('partner_aadhar_') && !e.target.id.includes('_upload_btn')) {
//         const index = e.target.id.split('_')[2];
//         const fileInput = e.target;
//         const fileNameDiv = document.getElementById(`partner_aadhar_${index}_name`);

//         if (fileInput.files.length) {
//             console.log(`Partner Aadhar file selected for partner ${index}`, fileInput.files);

//             // Show global loader
//             globalLoader.style.display = 'block';

//             // Show processing message
//             if (fileNameDiv) {
//                 fileNameDiv.textContent = `Processing Partner Aadhar...`;
//             }

//             // Check existing hidden fields
//             const existingFileInput = document.querySelector(`input[name="existing_partner_aadhar_file[]"]`);
//             const existingFileOriginal = document.querySelector(`input[name="existing_partner_aadhar_file_original[]"]`);
//             const existingFile = existingFileInput ? existingFileInput.value : '';

//             // Call your processor function
//             processDocument(fileInput.files[0], fileNameDiv, existingFile, {
//                 type: 'partner_aadhar',
//                 displayType: 'Partner Aadhar',
//                 endpoint: '/process-document'
//             })
//                 .then((result) => {
//                     if (existingFileInput) existingFileInput.value = result.data?.filename || '';
//                     if (existingFileOriginal) existingFileOriginal.value = result.data?.displayName || '';
//                     fileInput.value = ''; // Reset file input
//                 })
//                 .catch((error) => {
//                     console.error(`Error processing Partner Aadhar for partner ${index}:`, error);
//                     alert(`Failed to process Partner Aadhar. Please try again.`);
//                 })
//                 .finally(() => {
//                     globalLoader.style.display = 'none';
//                 });
//         }
//     }
// });

     // Initialize upload buttons for existing authorized persons
        document.querySelectorAll('.authorized-person-entry').forEach((row, index) => {
            ['aadhar', 'letter'].forEach(type => {
                const input = document.getElementById(`auth_person_${type}_${index}`);
                const button = document.getElementById(`auth_person_${type}_${index}_upload_btn`);
                if (input && button) {
                    button.addEventListener('click', () => input.click());
                }
            });
        });

        // Authorized persons toggle
        const hasAuthorizedPersons = document.getElementById('has_authorized_persons');
        const authorizedPersonsSection = document.getElementById('authorized_persons_section');
        function toggleAuthorizedPersonsSection() {
       if (hasAuthorizedPersons && authorizedPersonsSection) {
        if (hasAuthorizedPersons.value === 'yes') {
            authorizedPersonsSection.classList.remove('d-none');

            const inputs = authorizedPersonsSection.querySelectorAll('input:not([type="email"]), textarea');
            inputs.forEach(input => input.setAttribute('required', ''));

            // ✅ Ensure at least 1 row exists
            const container = document.getElementById('authorized_persons_container');
            const rows = container.querySelectorAll('tr');
            if (rows.length === 0) {
                addAuthorizedPerson();
            }

        } else {
            authorizedPersonsSection.classList.add('d-none');

            const inputs = authorizedPersonsSection.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.removeAttribute('required');
                if (input.type !== 'file') input.value = '';
            });

            const fileInputs = authorizedPersonsSection.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => input.value = '');
        }
        }
       }

        if (hasAuthorizedPersons) {
            toggleAuthorizedPersonsSection();
            hasAuthorizedPersons.addEventListener('change', toggleAuthorizedPersonsSection);
        }


    });//End Dom Content


    // Generic document processor for new fields
    async function processDocument(file, fileNameField, existingFile, config) {
                try {
                    console.log('processDocument called with config:', config);

                    let type = config?.type;
                    let displayType = config?.displayType;
                    const endpoint = config?.endpoint || '/process-document';

                    if (!type || !displayType) {
                        const fieldId = fileNameField.id;
                        if (fieldId.includes('entity_proof_file')) {
                            type = 'entity_proof';
                            displayType = 'Entity Proof';
                        } else if (fieldId.includes('ownership_info')) {
                            type = 'ownership_info';
                            displayType = 'Ownership Information';
                        } else if (fieldId.includes('bank_statement')) {
                            type = 'bank_statement';
                            displayType = 'Bank Statement';
                        } else if (fieldId.includes('itr_acknowledgement')) {
                            type = 'itr_acknowledgement';
                            displayType = 'Income Tax Return Acknowledgement';
                        } else if (fieldId.includes('balance_sheet')) {
                            type = 'balance_sheet';
                            displayType = 'Balancesheet of Latest FY';
                        } else if (fieldId.includes('partner_aadhar')) {
                            type = 'partner_aadhar';
                            displayType = 'Partner Aadhar';
                        } else {
                            type = 'unknown';
                            displayType = 'Document';
                        }
                        console.warn('Config missing or incomplete, inferred type:', type);
                    }

                    fileNameField.textContent = `Uploading ${displayType.toLowerCase()}...`;
                    fileNameField.classList.remove('d-none');

                    const formData = new FormData();
                    formData.append(type, file);
                    if (existingFile) {
                        formData.append(`existing_${type}_file`, existingFile);
                    }
                    formData.append('doc_type', type);

                    const response = await fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: formData,
                    });

                    const result = await response.json();

                    if (!response.ok || result.status !== 'SUCCESS') {
                        throw new Error(result.message || `${displayType} upload failed`);
                    }

                    const { filename, displayName, url } = result.data;

                    const fileUrl = url || `https://s3.ap-south-1.amazonaws.com/developerinvnr.bkt/Connect/Distributor/${type}/${filename}`;

                    fileNameField.innerHTML = `
                        <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="${fileUrl}" data-type="${displayType}">View</a> (${displayName})
                        <input type="hidden" name="existing_${type}_file" value="${filename}">
                        <input type="hidden" name="existing_${type}_file_original" value="${displayName}">
                    `;

                    return result;
                } catch (error) {
                    console.error(`${displayType} upload error:`, error);
                    fileNameField.textContent = `Error uploading ${displayType.toLowerCase()}: ${error.message}`;
                    fileNameField.classList.add('text-danger');
                    throw error;
                }
    }

    // File change handler
    let removedDocuments = {};
    function handleFileChange(input, fieldName) {
        const fileNameDiv = document.getElementById(`${fieldName}_name`);
        if (fileNameDiv) {
            if (input.files.length > 0) {
                fileNameDiv.classList.add('d-none');
                const hiddenInput = fileNameDiv.querySelector('input[type="hidden"]');
                if (hiddenInput) hiddenInput.remove();
                delete removedDocuments[fieldName];
            } else {
                const existingLink = fileNameDiv.querySelector('a');
                if (existingLink) fileNameDiv.classList.remove('d-none');
            }
        }
    }

      // Authorized person file handling
    function handleAuthPersonFileChange(input, index, type) {
        const fileNameDiv = document.getElementById(`auth_person_${type}_${index}_name`);
        const aadharNumberInput = document.getElementById(`auth_person_aadhar_number_${index}`);
        const globalLoader = document.getElementById('global-loader');

        if (input.files.length > 0) {
            const file = input.files[0];
            const endpoint = type === 'aadhar' ? '/process-aadhar-document' : '/process-letter-document';
            const existingFileInput = `existing_auth_person_${type}[${index}]`;
            const existingFileOriginalInput = `existing_auth_person_${type}_original[${index}]`;
            const s3PathPrefix = 'https://s3.ap-south-1.amazonaws.com/developerinvnr.bkt/Connect/Distributor/authorized_persons';

            fileNameDiv.classList.remove('d-none');
            fileNameDiv.innerHTML = `Processing ${type} document... <span class="loader"></span>`;
            if (globalLoader) globalLoader.style.display = 'block';

            const formData = new FormData();
            formData.append(`auth_person_${type}`, file);
            const existingFile = document.querySelector(`input[name="${existingFileInput}"]`)?.value;
            if (existingFile) {
                formData.append(`existing_auth_person_${type}`, existingFile);
            }

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || `Server returned ${response.status}`);
                    });
                }
                return response.json();
            })
            .then(result => {
                if (result.status === 'SUCCESS') {
                    fileNameDiv.innerHTML = `
                        <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" 
                           data-src="${s3PathPrefix}/${result.data.filename}" 
                           data-type="${type.charAt(0).toUpperCase() + type.slice(1)} Document">
                           View
                        </a> (${result.data.displayName})
                        <input type="hidden" name="${existingFileInput}" value="${result.data.filename}">
                        <input type="hidden" name="${existingFileOriginalInput}" value="${result.data.displayName}">
                    `;
                    fileNameDiv.classList.remove('d-none');

                    if (type === 'aadhar' && result.data.aadharNumber) {
                        if (aadharNumberInput) {
                            aadharNumberInput.value = result.data.aadharNumber;
                        }
                    }

                    input.value = '';
                    alert(`${type.charAt(0).toUpperCase() + type.slice(1)} document uploaded successfully.`);
                } else {
                    throw new Error(result.message || `Failed to process ${type} document`);
                }
            })
            .catch(error => {
                console.error(`Error uploading ${type} document:`, error);
                fileNameDiv.innerHTML = `Error processing ${type} document: ${error.message}`;
                fileNameDiv.classList.add('text-danger');
                alert(`Failed to process ${type} document: ${error.message}`);
            })
            .finally(() => {
                if (globalLoader) globalLoader.style.display = 'none';
            });
        }
    }

    function removeExistingAuthPersonFile(button, type, index) {
        const container = button.closest(`#auth_person_${type}_${index}_name`);
        if (container) {
            container.classList.add('d-none');
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = `removed_auth_person_${type}[]`;
            hiddenInput.value = index;
            container.appendChild(hiddenInput);
            const fileInput = container.closest('tr').querySelector(`input[name="auth_person_${type}[]"]`);
            if (fileInput) fileInput.required = true;
        }
    }

    // Add authorized person
    function addAuthorizedPerson() {
        const container = document.getElementById('authorized_persons_container');
        const newRow = document.createElement('tr');
        newRow.className = 'authorized-person-entry';
        const index = document.querySelectorAll('.authorized-person-entry').length;

        newRow.innerHTML = `
            <td data-label="Name">
                <input type="text" class="form-control form-control-sm" name="auth_person_name[]" required>
                <div class="invalid-feedback">Please enter a valid name</div>
            </td>
            <td data-label="Contact Number">
                <input type="tel" class="form-control form-control-sm" name="auth_person_contact[]" required pattern="[0-9]{10}">
                <div class="invalid-feedback">Please enter a 10-digit phone number</div>
            </td>
            <td data-label="Email Address">
                <input type="email" class="form-control form-control-sm" name="auth_person_email[]">
                <div class="invalid-feedback">Please enter a valid email address</div>
            </td>
            <td data-label="Full Address">
                <textarea class="form-control form-control-sm" name="auth_person_address[]" rows="1" required></textarea>
                <div class="invalid-feedback">Please enter the full address</div>
            </td>
            <td data-label="Relation">
                <input type="text" class="form-control form-control-sm" name="auth_person_relation[]" required>
                <div class="invalid-feedback">Please specify the relation</div>
            </td>
            <td data-label="Letter of Authorisation">
                <input type="file" class="form-control form-control-sm d-none" 
                       id="auth_person_letter_${index}" 
                       name="auth_person_letter[]" 
                       accept=".pdf,.doc,.docx" 
                       onchange="handleAuthPersonFileChange(this, ${index}, 'letter')"
                       required>
                <button type="button" class="btn btn-sm btn-outline-secondary w-100" 
                        id="auth_person_letter_${index}_upload_btn">Upload</button>
                <div id="auth_person_letter_${index}_name" class="small text-muted mt-1 d-none"></div>
                <div class="invalid-feedback">Letter of Authorization is required</div>
            </td>
            <td data-label="Aadhar Document">
                <input type="file" class="form-control form-control-sm d-none" 
                       id="auth_person_aadhar_${index}" 
                       name="auth_person_aadhar[]" 
                       accept=".pdf,.jpg,.jpeg,.png" 
                       onchange="handleAuthPersonFileChange(this, ${index}, 'aadhar')"
                       required>
                <button type="button" class="btn btn-sm btn-outline-secondary w-100" 
                        id="auth_person_aadhar_${index}_upload_btn">Upload</button>
                <div id="auth_person_aadhar_${index}_name" class="small text-muted mt-1 d-none"></div>
                <div class="invalid-feedback">Aadhar document is required</div>
            </td>
            <td data-label="Aadhar Number">
                <input type="text" class="form-control form-control-sm" 
                       name="auth_person_aadhar_number[]"
                       id="auth_person_aadhar_number_${index}"
                       placeholder="Aadhar Number"
                       readonly>
                <div class="invalid-feedback">Aadhar number is invalid</div>
            </td>
            <td data-label="Action">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeAuthorizedPerson(this)">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </td>
        `;
        container.appendChild(newRow);

        ['aadhar', 'letter'].forEach(type => {
            const input = document.getElementById(`auth_person_${type}_${index}`);
            const button = document.getElementById(`auth_person_${type}_${index}_upload_btn`);
            if (input && button) {
                button.addEventListener('click', () => input.click());
            }
        });
    }

    function removeAuthorizedPerson(button) {
        const row = button.closest('tr');
        const container = row.parentElement;
        const rows = container.querySelectorAll('tr');
        const hasData = Array.from(row.querySelectorAll('input:not([type="file"]), textarea'))
            .some(input => input.value.trim() !== '');
        const hasFiles = Array.from(row.querySelectorAll('input[type="file"]'))
            .some(input => input.files.length > 0);
        const hasExistingFiles = Array.from(row.querySelectorAll('[id$="_name"] a'))
            .some(link => link !== null);
        if (rows.length > 1) {
            if (hasData || hasFiles || hasExistingFiles) {
                if (confirm('This row contains data. Are you sure you want to remove it?')) {
                    row.remove();
                }
            } else {
                row.remove();
            }
        } else {
            if (hasData || hasFiles || hasExistingFiles) {
                if (confirm('Clear all fields in this row?')) {
                    row.querySelectorAll('input:not([type="file"]), textarea').forEach(input => input.value = '');
                    row.querySelectorAll('input[type="file"]').forEach(input => input.value = '');
                    row.querySelectorAll('[id$="_name"]').forEach(div => {
                        div.classList.add('d-none');
                        div.innerHTML = '';
                    });
                }
            }
        }
    }


    // Entity-specific fields
     function showRelevantFields() {
        const entityTypeSelect = document.getElementById('entity_type');
        const entityType = entityTypeSelect.value;

        // Existing logic for entity-specific fields
        const specificFields = document.querySelectorAll('.entity-specific-fields');
        specificFields.forEach(el => {
            el.style.display = 'none';
            el.querySelectorAll('input, textarea, select').forEach(input => {
                input.disabled = true;
                input.required = false;
            });
        });
        let targetField = null;
        switch (entityType) {
            case 'individual_person':
                targetField = document.getElementById('individual_person_fields');
                break;
            case 'sole_proprietorship':
                targetField = document.getElementById('sole_proprietorship_fields');
                break;
            case 'partnership':
                targetField = document.getElementById('partnership_fields');
                break;
            case 'llp':
                targetField = document.getElementById('llp_fields');
                break;
            case 'private_company':
            case 'public_company':
                targetField = document.getElementById('company_fields');
                break;
            case 'cooperative_society':
                targetField = document.getElementById('cooperative_fields');
                break;
            case 'trust':
                targetField = document.getElementById('trust_fields');
                break;
        }
        if (targetField) {
            targetField.style.display = 'block';
            targetField.querySelectorAll('input, textarea, select').forEach(input => {
                input.disabled = false;
                if (input.hasAttribute('data-required')) {
                    input.required = true;
                }
            });
        }

        // Logic for document fields
        const documentsRow1 = document.getElementById('documents_row_1');
        const documentsRow2 = document.getElementById('documents_row_2');
        const entityProofCol = document.getElementById('entity_proof_col');
        const entityProofLabel = document.getElementById('entity_proof_label');
        const entityProofFile = document.getElementById('entity_proof_file');
        const ownershipInfoCol = document.getElementById('ownership_info_col');
        const ownershipInfoLabel = document.getElementById('ownership_info_label');
        const ownershipInfoFile = document.getElementById('ownership_info_file');
        const bankStatementCol = document.getElementById('bank_statement_col');
        const bankStatementLabel = document.getElementById('bank_statement_label');
        const bankStatementFile = document.getElementById('bank_statement_file');
        const itrAcknowledgementCol = document.getElementById('itr_acknowledgement_col');
        const itrAcknowledgementLabel = document.getElementById('itr_acknowledgement_label');
        const itrAcknowledgementFile = document.getElementById('itr_acknowledgement_file');
        const balanceSheetCol = document.getElementById('balance_sheet_col');
        const balanceSheetLabel = document.getElementById('balance_sheet_label');
        const balanceSheetFile = document.getElementById('balance_sheet_file');

        if (documentsRow1 && documentsRow2 &&
            entityProofCol && entityProofLabel && entityProofFile &&
            ownershipInfoCol && ownershipInfoLabel && ownershipInfoFile &&
            bankStatementCol && bankStatementLabel && bankStatementFile &&
            itrAcknowledgementCol && itrAcknowledgementLabel && itrAcknowledgementFile &&
            balanceSheetCol && balanceSheetLabel && balanceSheetFile) {
            
            let entityProofLabelText = 'Entity Proof Document *';
            let ownershipInfoLabelText = 'Ownership Information *';
            let bankStatementLabelText = 'Bank Statement (6 Months) *';
            let itrAcknowledgementLabelText = 'Income Tax Return Acknowledgement *';
            let balanceSheetLabelText = 'Balancesheet of Latest FY';

            // Show rows if any field is visible
            documentsRow1.style.display = 'block'; // Always show due to bank_statement_file
            documentsRow2.style.display = entityType ? 'block' : 'none'; // Show for credit worthiness if entity_type is set

            // Entity Proof
            if (['partnership', 'llp', 'private_company', 'public_company', 'cooperative_society', 'trust'].includes(entityType)) {
                entityProofCol.style.display = 'block';
                entityProofFile.disabled = false;
                entityProofFile.required = true;
                switch (entityType) {
                    case 'partnership':
                        entityProofLabelText = 'Partnership Agreement *';
                        break;
                    case 'llp':
                        entityProofLabelText = 'Certificate of Incorporation *';
                        break;
                    case 'private_company':
                    case 'public_company':
                        entityProofLabelText = 'Certificate of Incorporation *';
                        break;
                    case 'cooperative_society':
                        entityProofLabelText = 'Certificate of Registration *';
                        break;
                    case 'trust':
                        entityProofLabelText = 'Certificate of Registration *';
                        break;
                }
            } else {
                entityProofCol.style.display = 'none';
                entityProofFile.disabled = true;
                entityProofFile.required = false;
            }

            // Ownership Information
            if (['sole_proprietorship', 'llp', 'private_company', 'public_company', 'cooperative_society', 'trust'].includes(entityType)) {
                ownershipInfoCol.style.display = 'block';
                ownershipInfoFile.disabled = false;
                ownershipInfoFile.required = true;
                switch (entityType) {
                    case 'sole_proprietorship':
                        ownershipInfoLabelText = 'Aadhar Card of Proprietor *';
                        break;
                    case 'llp':
                        ownershipInfoLabelText = 'Certified List of Partners *';
                        break;
                    case 'private_company':
                    case 'public_company':
                        ownershipInfoLabelText = 'Certified List of Directors *';
                        break;
                    case 'cooperative_society':
                        ownershipInfoLabelText = 'Certified List of Directors *';
                        break;
                    case 'trust':
                        ownershipInfoLabelText = 'Certified List of Trustees *';
                        break;
                }
            } else {
                ownershipInfoCol.style.display = 'none';
                ownershipInfoFile.disabled = true;
                ownershipInfoFile.required = false;
            }

            // Bank Statement
            bankStatementCol.style.display = 'block';
            bankStatementFile.disabled = false;
            bankStatementFile.required = true;

            // Credit Worthiness
            if (entityType) {
                itrAcknowledgementCol.style.display = 'block';
                itrAcknowledgementFile.disabled = false;
                itrAcknowledgementFile.required = true;
                balanceSheetCol.style.display = 'block';
                balanceSheetFile.disabled = false;
                balanceSheetFile.required = false; // Optional
                if (entityType === 'sole_proprietorship') {
                    itrAcknowledgementLabelText = 'Income Tax Return Acknowledgement *';
                    balanceSheetLabelText = 'Balancesheet of Latest FY';
                } else {
                    itrAcknowledgementLabelText = 'Income Tax Return Acknowledgement (Entity) *';
                    balanceSheetLabelText = 'Balancesheet of Latest FY (Entity)';
                }
            } else {
                itrAcknowledgementCol.style.display = 'none';
                itrAcknowledgementFile.disabled = true;
                itrAcknowledgementFile.required = false;
                balanceSheetCol.style.display = 'none';
                balanceSheetFile.disabled = true;
                balanceSheetFile.required = false;
            }

            entityProofLabel.textContent = entityProofLabelText;
            ownershipInfoLabel.textContent = ownershipInfoLabelText;
            bankStatementLabel.textContent = bankStatementLabelText;
            itrAcknowledgementLabel.textContent = itrAcknowledgementLabelText;
            balanceSheetLabel.textContent = balanceSheetLabelText;
        }
    }


    function toggleGSTFields() {
        const gstApplicable = document.getElementById('gst_applicable').value;
        const gstFields = document.getElementById('gst_fields');
        if (gstApplicable === 'yes') {
            gstFields.style.display = 'block';
            gstFields.querySelectorAll('input, textarea').forEach(input => input.disabled = false);
            document.getElementById('gst_number').required = true;
            document.getElementById('gst_file').required = true;
        } else {
            gstFields.style.display = 'none';
            gstFields.querySelectorAll('input, textarea').forEach(input => {
                input.disabled = true;
                input.value = '';
            });
            document.getElementById('gst_file').value = '';
            document.getElementById('gst_file_name').classList.add('d-none');
            document.getElementById('gst_number').required = false;
            document.getElementById('gst_file').required = false;
        }
    }

    function clearInapplicableFields(entityType) {
        const containers = {
            'individual_person': ['individual_name', 'individual_dob', 'individual_father_name', 'individual_age'],
            'sole_proprietorship': ['proprietor_name', 'proprietor_dob', 'proprietor_father_name', 'proprietor_age'],
            'partnership': ['partners_container'],
            'llp': ['llp_partners_container', 'llpin_number', 'llp_incorporation_date'],
            'private_company': ['directors_container', 'cin_number', 'incorporation_date'],
            'public_company': ['directors_container', 'cin_number', 'incorporation_date'],
            'cooperative_society': ['committee_container', 'cooperative_reg_number', 'cooperative_reg_date'],
            'trust': ['trustees_container', 'trust_reg_number', 'trust_reg_date']
        };
        Object.keys(containers).forEach(type => {
            if (type !== entityType) {
                containers[type].forEach(field => {
                    const input = document.getElementById(field) || document.querySelector(`[name="${field}"]`);
                    if (input && (input.tagName === 'INPUT' || input.tagName === 'TEXTAREA')) {
                        input.value = '';
                    }
                });
            }
        });
        const dynamicContainers = ['partners_container', 'llp_partners_container', 'directors_container', 'committee_container', 'trustees_container'];
        dynamicContainers.forEach(containerId => {
            if (!containers[entityType].includes(containerId)) {
                const container = document.getElementById(containerId);
                if (container && !container.querySelectorAll('input[value]:not([value=""]), textarea:not(:empty)').length) {
                    container.innerHTML = '';
                }
            }
        });
        if (entityType === 'partnership' && document.getElementById('partners_container').children.length === 0) {
            addPartner();
        } else if (entityType === 'llp' && document.getElementById('llp_partners_container').children.length === 0) {
            addLLPPartner();
        } else if (['private_company', 'public_company'].includes(entityType) && document.getElementById('directors_container').children.length === 0) {
            addDirector();
        } else if (entityType === 'cooperative_society' && document.getElementById('committee_container').children.length === 0) {
            addCommitteeMember();
        } else if (entityType === 'trust' && document.getElementById('trustees_container').children.length === 0) {
            addTrustee();
        }
    }

     // Add Partner
     function addPartner() {
    const container = document.getElementById('partners_container');
    const index = container.children.length;
    
    const partnerHtml = `
        <div class="partner-entry mb-3 border-bottom pb-3" data-index="${index}">
            <div class="row g-2">
                <div class="col-12 col-md-3">
                    <div class="form-group mb-2">
                        <label class="form-label small">Partner Name *</label>
                        <input type="text" class="form-control form-control-sm" name="partner_name[]" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="form-group mb-2">
                        <label class="form-label small">PAN *</label>
                        <input type="text" class="form-control form-control-sm" name="partner_pan[]" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="form-group mb-2">
                        <label class="form-label small">Contact Number *</label>
                        <input type="tel" class="form-control form-control-sm" name="partner_contact[]" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="form-group mb-2">
                        <label class="form-label small">Aadhar Card *</label>
                        <div class="input-group input-group-sm">
                            <input type="file" class="form-control form-control-sm d-none partner-aadhar-file" 
                                   id="partner_aadhar_${index}" name="partner_aadhar[]" 
                                   accept=".pdf,.jpg,.jpeg,.png" 
                                   data-index="${index}"
                                   onchange="handlePartnerFileChange(this, ${index})">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100 partner-aadhar-upload-btn" 
                                    data-index="${index}">
                                Upload Aadhar
                            </button>
                        </div>
                        <div id="partner_aadhar_${index}_name" class="small text-muted mt-1 d-none"></div>
                        <input type="hidden" name="existing_partner_aadhar_file[${index}]" value="">
                        <input type="hidden" name="existing_partner_aadhar_file_original[${index}]" value="">
                        <div class="invalid-feedback">Aadhar document is required</div>
                    </div>
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end">
                    <div class="form-group mb-2 w-100">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePartner(this)" title="Remove Partner">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', partnerHtml);
    attachUploadButtonEvents(); // Attach upload button events
    registerPartnerAadharInputs(); // Re-index after adding
}
// Attach upload button events (called after adding or re-indexing)
function attachUploadButtonEvents() {
    document.querySelectorAll('.partner-aadhar-upload-btn').forEach(btn => {
        btn.onclick = function() {
            const index = this.getAttribute('data-index');
            document.getElementById(`partner_aadhar_${index}`).click();
        };
    });
}

function registerPartnerAadharInputs() {
    const container = document.getElementById('partners_container');
    const entries = container.querySelectorAll('.partner-entry');
    
    entries.forEach((entry, newIndex) => {
        // Update data-index attribute
        entry.setAttribute('data-index', newIndex);
        
        // Update input names for partner details
        entry.querySelectorAll('input[name^="partner_name"]').forEach(input => {
            input.name = `partner_name[${newIndex}]`;
        });
        entry.querySelectorAll('input[name^="partner_pan"]').forEach(input => {
            input.name = `partner_pan[${newIndex}]`;
        });
        entry.querySelectorAll('input[name^="partner_contact"]').forEach(input => {
            input.name = `partner_contact[${newIndex}]`;
        });
        entry.querySelectorAll('input[name^="partner_aadhar"]').forEach(input => {
            input.name = `partner_aadhar[${newIndex}]`;
            input.id = `partner_aadhar_${newIndex}`;
            input.setAttribute('data-index', newIndex);
            // Re-attach onchange event
            input.onchange = () => handlePartnerFileChange(input, newIndex);
        });
        
        // Update existing file hidden inputs (preserve values)
        const existingFileInput = entry.querySelector('input[name^="existing_partner_aadhar_file"]');
        if (existingFileInput) {
            existingFileInput.name = `existing_partner_aadhar_file[${newIndex}]`;
        }
        const existingOriginalInput = entry.querySelector('input[name^="existing_partner_aadhar_file_original"]');
        if (existingOriginalInput) {
            existingOriginalInput.name = `existing_partner_aadhar_file_original[${newIndex}]`;
        }
        
        // Update file name div ID
        const fileNameDiv = entry.querySelector('[id^="partner_aadhar_"]');
        if (fileNameDiv && fileNameDiv.id.includes('_name')) {
            fileNameDiv.id = `partner_aadhar_${newIndex}_name`;
        }
        
        // Update processed field if exists
        const processedField = entry.querySelector('input[name^="partner_aadhar_processed"]');
        if (processedField) {
            processedField.name = `partner_aadhar_processed[${newIndex}]`;
        }
        
        // Update upload button onclick using data-index
        const uploadBtn = entry.querySelector('.partner-aadhar-upload-btn');
        if (uploadBtn) {
            uploadBtn.setAttribute('data-index', newIndex);
        }
    });
    
    // Re-attach upload button events after re-indexing
    attachUploadButtonEvents();
}
// Handle Partner Aadhar File Change
function handlePartnerFileChange(input, index) {
    const fileNameDiv = document.getElementById(`partner_aadhar_${index}_name`);
    const globalLoader = document.getElementById('global-loader');

    if (!input.files.length) return;

    const file = input.files[0];
    const endpoint = '/process-partner-aadhar';
    const existingFileInputName = `existing_partner_aadhar_file[${index}]`;
    const existingFileOriginalInputName = `existing_partner_aadhar_file_original[${index}]`;
    const s3PathPrefix = 'https://s3.ap-south-1.amazonaws.com/developerinvnr.bkt/Connect/Distributor/partner_aadhar';

    // Show processing loader
    fileNameDiv.classList.remove('d-none');
    fileNameDiv.textContent = `Processing Partner Aadhar...`;
    if (globalLoader) globalLoader.style.display = 'block';

    // Prepare FormData
    const formData = new FormData();
    formData.append('partner_aadhar', file);
    const existingFileValue = document.querySelector(`input[name="${existingFileInputName}"]`)?.value;
    if (existingFileValue) {
        formData.append('existing_auth_person_aadhar', existingFileValue);
    }

    // Upload
    fetch(endpoint, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(res => res.json())
    .then(result => {
        if (result.status === 'SUCCESS') {
            fileNameDiv.innerHTML = `
                <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" 
                   data-src="${s3PathPrefix}/${result.data.filename}" 
                   data-type="Partner Aadhar">View</a> 
                (${result.data.displayName})
            `;
            document.querySelector(`input[name="${existingFileInputName}"]`).value = result.data.filename;
            document.querySelector(`input[name="${existingFileOriginalInputName}"]`).value = result.data.displayName;
            
            // IMPORTANT: Remove the required attribute and disable the input instead of clearing it
            input.required = false;
            input.disabled = true;
            
            // Create a hidden field to indicate file was processed
            let processedField = document.querySelector(`input[name="partner_aadhar_processed[${index}]"]`);
            if (!processedField) {
                processedField = document.createElement('input');
                processedField.type = 'hidden';
                processedField.name = `partner_aadhar_processed[${index}]`;
                input.parentNode.appendChild(processedField);
            }
            processedField.value = '1';
            
            alert('Partner Aadhar uploaded successfully.');
        } else {
            throw new Error(result.message || 'Upload failed');
        }
    })
    .catch(err => {
        console.error('Partner Aadhar upload error:', err);
        fileNameDiv.textContent = `Error processing Partner Aadhar: ${err.message}`;
        fileNameDiv.classList.add('text-danger');
        alert(`Failed to process Partner Aadhar: ${err.message}`);
        
        // Clear the file input on error
        input.value = '';
    })
    .finally(() => {
        if (globalLoader) globalLoader.style.display = 'none';
    });
}


    // Remove Partner
function removePartner(button) {
    const entry = button.closest('.partner-entry');
    const container = entry.parentElement;
    const entries = container.querySelectorAll('.partner-entry');

    // Check if current entry has data
    const hasData = Array.from(entry.querySelectorAll('input:not([type="file"]), textarea'))
        .some(input => input.value.trim() !== '');

    const hasFiles = Array.from(entry.querySelectorAll('input[type="file"]'))
        .some(input => input.files.length > 0);

    const hasExistingFiles = Array.from(entry.querySelectorAll('[id$="_name"] a'))
        .some(link => link !== null);

    if (entries.length > 1) {
        if (hasData || hasFiles || hasExistingFiles) {
            if (confirm('This partner entry contains data. Are you sure you want to remove it?')) {
                entry.remove();
                registerPartnerAadharInputs(); // Re-index after removal
            }
        } else {
            entry.remove();
            registerPartnerAadharInputs(); // Re-index after removal
        }
    } else {
        if (hasData || hasFiles || hasExistingFiles) {
            if (confirm('Clear all fields in this partner entry?')) {
                entry.querySelectorAll('input:not([type="file"]), textarea').forEach(input => input.value = '');
                entry.querySelectorAll('input[type="file"]').forEach(input => input.value = '');
                entry.querySelectorAll('[id$="_name"]').forEach(div => {
                    div.classList.add('d-none');
                    div.innerHTML = '';
                });
                // Clear hidden inputs
                entry.querySelectorAll('input[name^="existing_partner_aadhar_file"]').forEach(input => input.value = '');
                entry.querySelectorAll('input[name^="existing_partner_aadhar_file_original"]').forEach(input => input.value = '');
                // Re-index after clearing
                registerPartnerAadharInputs();
            }
        } else {
            alert('At least one partner is required.');
        }
    }
}

   
// Re-index signatory inputs after adding/removing signatories
function reindexSignatoryInputs() {
    const container = document.getElementById('signatories_container');
    const entries = container.querySelectorAll('.signatory-entry');
    
    entries.forEach((entry, newIndex) => {
        entry.setAttribute('data-index', newIndex);
        
        entry.querySelectorAll('input[name^="signatory_name"]').forEach(input => {
            input.name = `signatory_name[${newIndex}]`;
        });
        entry.querySelectorAll('input[name^="signatory_designation"]').forEach(input => {
            input.name = `signatory_designation[${newIndex}]`;
        });
        entry.querySelectorAll('input[name^="signatory_contact"]').forEach(input => {
            input.name = `signatory_contact[${newIndex}]`;
        });
    });
}

// Remove Signatory
function removeSignatory(button) {
    const entry = button.closest('.signatory-entry');
    const container = entry.parentElement;
    const entries = container.querySelectorAll('.signatory-entry');

    const hasData = Array.from(entry.querySelectorAll('input, textarea'))
        .some(input => input.value.trim() !== '');

    if (entries.length > 1) {
        if (hasData) {
            if (confirm('This signatory entry contains data. Are you sure you want to remove it?')) {
                entry.remove();
                reindexSignatoryInputs();
            }
        } else {
            entry.remove();
            reindexSignatoryInputs();
        }
    } else {
        if (hasData) {
            if (confirm('Clear all fields in this signatory entry?')) {
                entry.querySelectorAll('input, textarea').forEach(input => input.value = '');
            }
        } else {
            alert('At least one signatory is required.');
        }
    }
}

    function addLLPPartner() {
        const container = document.getElementById('llp_partners_container');
        const newEntry = document.createElement('div');
        newEntry.className = 'llp-partner-entry mb-4 border-bottom pb-3';
        newEntry.innerHTML = `
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label small">Partner Name *</label>
                        <input type="text" class="form-control" name="llp_partner_name[]" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-3">
                        <label class="form-label small">DPIN Number *</label>
                        <input type="text" class="form-control" name="llp_partner_dpin[]" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label small">Contact Number *</label>
                        <input type="tel" class="form-control" name="llp_partner_contact[]" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label small">Full Address *</label>
                        <textarea class="form-control form-control-sm" name="llp_partner_address[]" rows="1" required></textarea>
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end mb-3">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeLLPPartner(this)">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(newEntry);
    }

    function removeLLPPartner(button) {
        const entries = document.querySelectorAll('.llp-partner-entry');
        if (entries.length > 1) {
            button.closest('.llp-partner-entry').remove();
        } else {
            alert('At least one designated partner is required.');
        }
    }

    function addDirector() {
        const container = document.getElementById('directors_container');
        const newEntry = document.createElement('div');
        newEntry.className = 'director-entry mb-4 border-bottom pb-3';
        newEntry.innerHTML = `
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label">Director Name *</label>
                        <input type="text" class="form-control" name="director_name[]" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label">DIN Number *</label>
                        <input type="text" class="form-control" name="director_din[]" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label">Contact Number *</label>
                        <input type="tel" class="form-control" name="director_contact[]" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-3">
                        <label class="form-label">Full Address *</label>
                        <textarea class="form-control form-control-sm" name="director_address[]" rows="1" required></textarea>
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end mb-3">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeDirector(this)">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(newEntry);
    }

    function removeDirector(button) {
        const entries = document.querySelectorAll('.director-entry');
        if (entries.length > 1) {
            button.closest('.director-entry').remove();
        } else {
            alert('At least one director is required.');
        }
    }

    function addCommitteeMember() {
        const container = document.getElementById('committee_container');
        const newEntry = document.createElement('div');
        newEntry.className = 'committee-entry mb-3 border-bottom pb-3';
        newEntry.innerHTML = `
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label">Member Name *</label>
                        <input type="text" class="form-control" name="committee_name[]" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-3">
                        <label class="form-label">Designation *</label>
                        <input type="text" class="form-control" name="committee_designation[]" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-3">
                        <label class="form-label">Contact Number *</label>
                        <input type="tel" class="form-control" name="committee_contact[]" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label class="form-label">Full Address *</label>
                        <textarea class="form-control form-control-sm" name="committee_address[]" rows="1" required></textarea>
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end mb-3">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeCommitteeMember(this)">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(newEntry);
        if (container.children.length === 2) {
            const firstEntry = container.children[0];
            firstEntry.classList.add('border-bottom', 'pb-3');
            const firstRemoveBtn = firstEntry.querySelector('.btn-danger');
            if (firstRemoveBtn) {
                firstRemoveBtn.style.display = 'block';
            }
        }
    }

    function removeCommitteeMember(button) {
        const entry = button.closest('.committee-entry');
        const container = document.getElementById('committee_container');
        if (container.children.length > 1) {
            entry.remove();
            if (container.children.length === 1) {
                const firstEntry = container.children[0];
                firstEntry.classList.remove('border-bottom', 'pb-3');
                const firstRemoveBtn = firstEntry.querySelector('.btn-danger');
                if (firstRemoveBtn) {
                    firstRemoveBtn.style.display = 'none';
                }
            }
        } else {
            alert('At least one committee member is required.');
        }
    }

    function addTrustee() {
        const container = document.getElementById('trustees_container');
        const newEntry = document.createElement('div');
        newEntry.className = 'trustee-entry mb-3 border-bottom pb-3';
        newEntry.innerHTML = `
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label">Trustee Name *</label>
                        <input type="text" class="form-control" name="trustee_name[]" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label">Designation *</label>
                        <input type="text" class="form-control" name="trustee_designation[]" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-3">
                        <label class="form-label">Contact Number *</label>
                        <input type="tel" class="form-control" name="trustee_contact[]" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-3">
                        <label class="form-label">Full Address *</label>
                        <textarea class="form-control form-control-sm" name="trustee_address[]" rows="1" required></textarea>
                    </div>
                </div>
                <div class="col-md-1 d-flex align-items-end mb-3">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeTrustee(this)">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(newEntry);
        if (container.children.length === 2) {
            const firstEntry = container.children[0];
            firstEntry.classList.add('border-bottom', 'pb-3');
            const firstRemoveBtn = firstEntry.querySelector('.btn-danger');
            if (firstRemoveBtn) {
                firstRemoveBtn.style.display = 'block';
            }
        }
    }

    function removeTrustee(button) {
        const entry = button.closest('.trustee-entry');
        const container = document.getElementById('trustees_container');
        if (container.children.length > 1) {
            entry.remove();
            if (container.children.length === 1) {
                const firstEntry = container.children[0];
                firstEntry.classList.remove('border-bottom', 'pb-3');
                const firstRemoveBtn = firstEntry.querySelector('.btn-danger');
                if (firstRemoveBtn) {
                    firstRemoveBtn.style.display = 'none';
                }
            }
        } else {
            alert('At least one trustee is required.');
        }
    }

    // Validation functions
    function validatePAN(input) {
        const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
        const wrapper = input.closest('.col-md-5') || input.closest('.col-md-4') || input.parentElement;
        const errorDiv = wrapper.querySelector('.pan-error');
        const verifiedCheckbox = wrapper.querySelector('#pan_verified');
        if (!panRegex.test(input.value)) {
            input.classList.add('is-invalid');
            if (errorDiv) {
                errorDiv.style.display = 'block';
                errorDiv.textContent = 'Please enter a valid PAN number (e.g., ABCDE1234F).';
            }
            if (verifiedCheckbox) {
                verifiedCheckbox.checked = false;
            }
        } else {
            input.classList.remove('is-invalid');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        }
    }

    function validateGST(input) {
        const gstRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
        const errorDivs = document.querySelectorAll('.gst-error');
        if (!gstRegex.test(input.value)) {
            input.classList.add('is-invalid');
            errorDivs.forEach(div => {
                div.textContent = 'Please enter a valid GST number (e.g., 22AAAAA0000A1Z5).';
                div.style.display = 'block';
            });
        } else {
            input.classList.remove('is-invalid');
            errorDivs.forEach(div => {
                div.style.display = 'none';
            });
        }
    }

    function validateTAN(input) {
        const tanRegex = /^[A-Z]{4}[0-9]{5}[A-Z]{1}$/;
        const errorDivs = document.querySelectorAll('.tan-error');
        if (input.value && !tanRegex.test(input.value)) {
            input.classList.add('is-invalid');
            errorDivs.forEach(div => div.style.display = 'block');
        } else {
            input.classList.remove('is-invalid');
            errorDivs.forEach(div => div.style.display = 'none');
        }
    }

    function validateEmail(input) {
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        const errorDiv = input.parentElement.parentElement.querySelector('.email-error');
        if (input.value && !emailRegex.test(input.value)) {
            input.classList.add('is-invalid');
            errorDiv.style.display = 'block';
        } else {
            input.classList.remove('is-invalid');
            errorDiv.style.display = 'none';
        }
    }

    function validateSeedLicense(input) {
        const seedLicenseRegex = /^[A-Z0-9]{6,15}$/;
        if (!seedLicenseRegex.test(input.value)) {
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    }

    // Form validation for authorized persons
    document.querySelector('form').addEventListener('submit', function(e) {
        const entries = document.querySelectorAll('.authorized-person-entry');
        entries.forEach(entry => {
            const inputs = entry.querySelectorAll('[data-required-if-filled]');
            const anyFilled = Array.from(inputs).some(input => input.value.trim() !== '' || (input.type === 'file' && input.files.length > 0));
            if (anyFilled) {
                inputs.forEach(input => {
                    if (input.value.trim() === '' && !(input.type === 'file' && input.files.length === 0)) {
                        e.preventDefault();
                        input.classList.add('is-invalid');
                        input.nextElementSibling?.remove();
                        const error = document.createElement('div');
                        error.className = 'invalid-feedback';
                        error.textContent = 'This field is required if any field in this entry is filled.';
                        input.parentNode.appendChild(error);
                    } else {
                        input.classList.remove('is-invalid');
                        input.nextElementSibling?.remove();
                    }
                });
            }
        });
        const dobErrorDivs = document.querySelectorAll('.dob-error');
        dobErrorDivs.forEach(errorDiv => {
            if (errorDiv.textContent !== '') {
                e.preventDefault();
                const invalidDobInput = errorDiv.previousElementSibling;
                if (invalidDobInput) {
                    invalidDobInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    invalidDobInput.focus();
                }
            }
        });
    });

    // Image preprocessing for OCR
    async function preprocessImage(file) {
        return new Promise((resolve) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const data = imageData.data;
                for (let i = 0; i < data.length; i += 4) {
                    const avg = (data[i] + data[i + 1] + data[i + 2]) / 3;
                    data[i] = data[i + 1] = data[i + 2] = avg < 128 ? 0 : 255;
                }
                ctx.putImageData(imageData, 0, 0);
                resolve(canvas.toDataURL('image/jpeg'));
            };
            img.src = URL.createObjectURL(file);
        });
    }

    // OCR text extraction
    async function extractTextFromImage(file) {
        try {
            const result = await Tesseract.recognize(file, 'eng', {
                preserve_interword_spaces: '1',
                tessedit_pageseg_mode: '6',
                tessedit_ocr_engine_mode: '1'
            });
            return result.data.text
                .replace(/\s+/g, ' ')
                .replace(/[|\\]/g, '')
                .trim();
        } catch (error) {
            console.error('OCR Error:', error);
            throw new Error('Failed to extract text from image');
        }
    }

    // Generic field extractor
    function extractField(text, patterns, validationRegex) {
        if (!Array.isArray(patterns)) patterns = [patterns];
        for (const pattern of patterns) {
            const match = text.match(pattern);
            if (match && match[1]) {
                const value = match[1].trim();
                if (!validationRegex || validationRegex.test(value)) {
                    return value;
                }
            }
        }
        return null;
    }

    // Document processors
    async function processBankDocument(file, fileNameField, existingFile) {
        try {
            fileNameField.textContent = 'Processing bank document...';
            fileNameField.classList.remove('d-none', 'text-danger');
            const loader = fileNameField.querySelector('.loader');
            if (loader) loader.classList.add('active');

            const formData = new FormData();
            formData.append('bank_file', file);
            if (existingFile) {
                formData.append('existing_bank_file', existingFile);
            }

            const response = await fetch('/process-bank-document', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: formData
            });

            if (!response.ok) {
                throw new Error(`Server error: ${response.status}`);
            }

            const result = await response.json();
            if (result.status === 'SUCCESS' && result.data) {
                const accountNumberInput = document.getElementById('account_number');
                const ifscCodeInput = document.getElementById('ifsc_code');
                const bankNameInput = document.getElementById('bank_name');
                const accountHolderInput = document.getElementById('account_holder');

                if (accountNumberInput) accountNumberInput.value = result.data.accountNumber || '';
                if (ifscCodeInput) ifscCodeInput.value = result.data.verification?.ifsc_details?.ifsc || '';
                if (bankNameInput) {
                    bankNameInput.value = result.data.verification?.ifsc_details?.name || '';
                    bankNameInput.readOnly = true;
                }
                if (accountHolderInput) {
                    accountHolderInput.value = result.data.verification?.beneficiary_name || '';
                    accountHolderInput.readOnly = true;
                }

                const s3BaseUrl = 'https://s3.ap-south-1.amazonaws.com/developerinvnr.bkt/';
                const fileUrl = s3BaseUrl + `Connect/Distributor/bank/${result.data.filename}`;

                fileNameField.innerHTML = `
                    <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="${fileUrl}" data-type="Bank Document">View</a> (${result.data.displayName})
                    <input type="hidden" name="existing_bank_file" value="${result.data.filename}">
                    <input type="hidden" name="existing_bank_file_original" value="${result.data.displayName}">
                    <span class="loader"></span>
                `;

                alert('Bank document processed and verified successfully.');
                return result;
            } else {
                throw new Error(result.message || 'Failed to process bank document.');
            }
        } catch (error) {
            console.error('Bank processing error:', error);
            fileNameField.textContent = 'Error processing bank document';
            fileNameField.classList.add('text-danger');
            alert('Failed to process bank document: ' + error.message);
            throw error;
        }
    }

    async function processPANCard(file, fileNameField, existingFile) {
        try {
            fileNameField.textContent = 'Processing PAN card...';
            fileNameField.classList.remove('d-none');

            const formData = new FormData();
            formData.append('pan_file', file);
            if (existingFile) {
                formData.append('existing_pan_file', existingFile);
            }

            const response = await fetch('/process-pan-card', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: formData,
            });

            const result = await response.json();

            if (!response.ok || result.status !== 'SUCCESS') {
                throw new Error(result.message || 'PAN processing failed');
            }

            const { panNumber, verification, filename, displayName } = result.data;

            setFieldValue('pan_number', panNumber);
            const s3BaseUrl = 'https://s3.ap-south-1.amazonaws.com/developerinvnr.bkt/';
            const fileUrl = s3BaseUrl + `Connect/Distributor/pan/${filename}`;

            fileNameField.innerHTML = `
                <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="${fileUrl}" data-type="PAN Document">View</a> (${displayName})
                <input type="hidden" name="existing_pan_file" value="${filename}">
                <input type="hidden" name="existing_pan_file_original" value="${displayName}">
                <span class="loader"></span>
            `;

            if (verification.is_valid) {
                document.getElementById('pan_verified').checked = true;
                setFieldValue('first_name', verification.first_name);
                setFieldValue('last_name', verification.last_name);
            }

            return result;
        } catch (error) {
            console.error('PAN processing error:', error);
            fileNameField.textContent = `Error processing PAN card: ${error.message}`;
            fileNameField.classList.add('text-danger');
            throw error;
        }
    }

    async function processSeedLicense(file, fileNameField, existingFile) {
        try {
            fileNameField.textContent = 'Processing seed license...';
            fileNameField.classList.remove('d-none');

            const formData = new FormData();
            formData.append('seed_license_file', file);
            if (existingFile) {
                formData.append('existing_seed_license_file', existingFile);
            }

            const response = await fetch('/process-seed-license', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: formData,
            });

            const result = await response.json();

            if (!response.ok || result.status !== 'SUCCESS') {
                throw new Error(result.message || 'Seed license processing failed');
            }

            const { licenseNumber, filename, displayName } = result.data;

            setFieldValue('seed_license', licenseNumber);
            const s3BaseUrl = 'https://s3.ap-south-1.amazonaws.com/developerinvnr.bkt/';
            const fileUrl = s3BaseUrl + `Connect/Distributor/seed_license/${filename}`;

            fileNameField.innerHTML = `
                <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="${fileUrl}" data-type="Seed License">View</a> (${displayName})
                <input type="hidden" name="existing_seed_license_file" value="${filename}">
                <input type="hidden" name="existing_seed_license_file_original" value="${displayName}">
                <span class="loader"></span>
            `;

            if (result.data.verified) {
                document.getElementById('seed_license_verified').checked = true;
            }

            return result;
        } catch (error) {
            console.error('Seed license processing error:', error);
            fileNameField.textContent = `Error processing seed license: ${error.message}`;
            fileNameField.classList.add('text-danger');
            throw error;
        }
    }

    async function processGSTDocument(file, fileNameField, existingFile) {
        try {
            fileNameField.textContent = 'Processing GST document...';
            fileNameField.classList.remove('d-none');

            const formData = new FormData();
            formData.append('gst_file', file);
            if (existingFile) {
                formData.append('existing_gst_file', existingFile);
            }

            const response = await fetch('/process-gst-document', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: formData,
            });

            const result = await response.json();

            if (!response.ok || result.status !== 'SUCCESS') {
                throw new Error(result.message || 'GST processing failed');
            }

            const { gstNumber, filename, displayName } = result.data;

            setFieldValue('gst_number', gstNumber);
            const s3BaseUrl = 'https://s3.ap-south-1.amazonaws.com/developerinvnr.bkt/';
            const fileUrl = s3BaseUrl + `Connect/Distributor/gst/${filename}`;

            fileNameField.innerHTML = `
                <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="${fileUrl}" data-type="GST Document">View</a> (${displayName})
                <input type="hidden" name="existing_gst_file" value="${filename}">
                <input type="hidden" name="existing_gst_file_original" value="${displayName}">
                <span class="loader"></span>
            `;

            return result;
        } catch (error) {
            console.error('GST processing error:', error);
            fileNameField.textContent = `Error processing GST document: ${error.message}`;
            fileNameField.classList.add('text-danger');
            throw error;
        }
    }

    // Helper function to set field values
    function setFieldValue(fieldId, value) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.value = (value || '').toString().trim();
            return true;
        }
        console.warn(`Field not found: ${fieldId}`);
        return false;
    }
</script>
@endpush