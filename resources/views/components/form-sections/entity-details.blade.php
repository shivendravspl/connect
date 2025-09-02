@php
// Ensure documents_data is an array
$documentsData = $application->entityDetails->documents_data ?? [];
if (is_string($documentsData)) {
$documentsData = json_decode($documentsData, true) ?? [];
}
$documentsCollection = collect($documentsData);
$panDoc = $documentsCollection->firstWhere('type', 'pan');
$seedLicenseDoc = $documentsCollection->firstWhere('type', 'seed_license');
$bankDoc = $documentsCollection->firstWhere('type', 'bank');
$gstDoc = $documentsCollection->firstWhere('type', 'gst');
@endphp
<div id="entity-details" class="form-section p-2">
    <div class="row g-2">
        <div class="col-12 col-md-6">
            <div class="form-group mb-2">
                <label for="establishment_name" class="form-label small">Name of Establishment *</label>
                <input type="text" class="form-control form-control-sm" id="establishment_name" name="establishment_name"
                    value="{{ old('establishment_name', isset($application->entityDetails) ? $application->entityDetails->establishment_name : '') }}" required>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="form-group mb-2">
                <label for="entity_type" class="form-label small">Type/Nature of Establishment *</label>
                <select class="form-select form-select-sm" id="entity_type" name="entity_type" required onchange="showRelevantFields()">
                    <option value="">Select Type</option>
                    @foreach(['individual_person' => 'Individual Person','sole_proprietorship' => 'Sole Proprietorship', 'partnership' => 'Partnership', 'llp' => 'Limited Liability Partnership (LLP)', 'private_company' => 'Private Company', 'public_company' => 'Public Company', 'cooperative_society' => 'Cooperative Societies', 'trust' => 'Trust'] as $value => $label)
                    <option value="{{ $value }}"
                        {{ old('entity_type', isset($application->entityDetails) ? $application->entityDetails->entity_type : '') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    

    <!-- Individual Person Fields -->
    <div id="individual_person_fields" class="entity-specific-fields" style="display:none;">
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
                                value="{{ old('individual_name', isset($application->entityDetails->additional_data['individual']['name']) ? $application->entityDetails->additional_data['individual']['name'] : '') }}" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="form-group mb-2">
                            <label for="individual_father_name" class="form-label small">Father’s / Spouse’s Name *</label>
                            <input type="text" class="form-control form-control-sm" id="individual_father_name" name="individual_father_name"
                                value="{{ old('individual_father_name', isset($application->entityDetails->additional_data['individual']['father_name']) ? $application->entityDetails->additional_data['individual']['father_name'] : '') }}" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="form-group mb-2">
                            <label for="individual_dob" class="form-label small">Date of Birth *</label>
                            <input type="date" class="form-control form-control-sm dob-input" id="individual_dob" name="individual_dob"
                                value="{{ old('individual_dob', isset($application->entityDetails->additional_data['individual']['dob']) ? $application->entityDetails->additional_data['individual']['dob'] : '') }}" required>
                            <div class="invalid-feedback dob-error"></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="form-group mb-2">
                            <label for="individual_age" class="form-label small">Age</label>
                            <input type="number" class="form-control form-control-sm age-display" id="individual_age" name="individual_age"
                                value="{{ old('individual_age', isset($application->entityDetails->additional_data['individual']['age']) ? $application->entityDetails->additional_data['individual']['age'] : '') }}" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sole Proprietorship Fields -->
    <div id="sole_proprietorship_fields" class="entity-specific-fields" style="display:none;">
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
                                value="{{ old('proprietor_name', isset($application->entityDetails->additional_data['proprietor']['name']) ? $application->entityDetails->additional_data['proprietor']['name'] : '') }}" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="form-group mb-2">
                            <label for="proprietor_father_name" class="form-label small">Father's/Spouse's Name *</label>
                            <input type="text" class="form-control form-control-sm" id="proprietor_father_name" name="proprietor_father_name"
                                value="{{ old('proprietor_father_name', isset($application->entityDetails->additional_data['proprietor']['father_name']) ? $application->entityDetails->additional_data['proprietor']['father_name'] : '') }}" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="form-group mb-2">
                            <label for="proprietor_dob" class="form-label small">Date of Birth *</label>
                            <input type="date" class="form-control form-control-sm dob-input" id="proprietor_dob" name="proprietor_dob"
                                value="{{ old('proprietor_dob', isset($application->entityDetails->additional_data['proprietor']['dob']) ? $application->entityDetails->additional_data['proprietor']['dob'] : '') }}" required>
                            <div class="invalid-feedback dob-error"></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="form-group mb-2">
                            <label for="proprietor_age" class="form-label small">Age</label>
                            <input type="number" class="form-control form-control-sm age-display" id="proprietor_age" name="proprietor_age"
                                value="{{ old('proprietor_age', isset($application->entityDetails->additional_data['proprietor']['age']) ? $application->entityDetails->additional_data['proprietor']['age'] : '') }}" readonly>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Partnership Fields -->
    <div id="partnership_fields" class="entity-specific-fields" style="display:none;">
    <!-- Partnership Details Card -->
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
                    @php
                    $partners = old('partner_name', isset($application->entityDetails->additional_data['partners']) && $application->entityDetails->entity_type === 'partnership' ? $application->entityDetails->additional_data['partners'] : []);
                    if (empty($partners)) {
                    $partners[] = ['name' => '', 'pan' => '', 'contact' => ''];
                    }
                    @endphp
                    @foreach($partners as $index => $partner)
                    <div class="partner-entry mb-3 @if($index > 0 || count($partners) > 1) border-bottom pb-3 @endif">
                        <div class="row g-2">
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Partner Name *</label>
                                    <input type="text" class="form-control form-control-sm" name="partner_name[]" value="{{ old("partner_name.$index", $partner['name'] ?? '') }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group mb-2">
                                    <label class="form-label small">PAN *</label>
                                    <input type="text" class="form-control form-control-sm" name="partner_pan[]" value="{{ old("partner_pan.$index", $partner['pan'] ?? '') }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Contact Number *</label>
                                    <input type="tel" class="form-control form-control-sm" name="partner_contact[]" value="{{ old("partner_contact.$index", $partner['contact'] ?? '') }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2 d-flex align-items-end">
                                <div class="form-group mb-2 w-100">
                                    @if($index > 0 || count($partners) > 1)
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePartner(this)" title="Remove Partner">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    @else
                                    <div style="height: 38px;"></div> <!-- Spacer to maintain alignment -->
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
                    $signatories = old('signatory_name', isset($application->entityDetails->additional_data['signatories']) && $application->entityDetails->entity_type === 'partnership' ? $application->entityDetails->additional_data['signatories'] : []);
                    if (empty($signatories)) {
                    $signatories[] = ['name' => '', 'designation' => '', 'contact' => ''];
                    }
                    @endphp
                    @foreach($signatories as $index => $signatory)
                    <div class="signatory-entry mb-3 @if($index > 0 || count($signatories) > 1) border-bottom pb-3 @endif">
                        <div class="row g-2">
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Signatory Name</label>
                                    <input type="text" class="form-control form-control-sm" name="signatory_name[]" value="{{ old("signatory_name.$index", $signatory['name'] ?? '') }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Designation *</label>
                                    <input type="text" class="form-control form-control-sm" name="signatory_designation[]" value="{{ old("signatory_designation.$index", $signatory['designation'] ?? '') }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Contact Number *</label>
                                    <input type="tel" class="form-control form-control-sm" name="signatory_contact[]" value="{{ old("signatory_contact.$index", $signatory['contact'] ?? '') }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2 d-flex align-items-end">
                                <div class="form-group mb-2 w-100">
                                    @if($index > 0 || count($signatories) > 1)
                                    <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeSignatory(this)" title="Remove Signatory">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    @else
                                    <div style="height: 38px;"></div> <!-- Spacer to maintain alignment -->
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
   <div id="llp_fields" class="entity-specific-fields" style="display:none;">
    <!-- Unified LLP Details Card -->
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
                            value="{{ old('llpin_number', isset($application->entityDetails->additional_data['llp']['llpin_number']) ? $application->entityDetails->additional_data['llp']['llpin_number'] : '') }}" required>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group mb-2">
                        <label for="llp_incorporation_date" class="form-label small">Date of Incorporation *</label>
                        <input type="date" class="form-control form-control-sm" id="llp_incorporation_date" name="llp_incorporation_date"
                            value="{{ old('llp_incorporation_date', isset($application->entityDetails->additional_data['llp']['incorporation_date']) ? $application->entityDetails->additional_data['llp']['incorporation_date'] : '') }}" required>
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
                    @php
                    $llpPartners = old('llp_partner_name', isset($application->entityDetails->additional_data['partners']) && $application->entityDetails->entity_type === 'llp' ? $application->entityDetails->additional_data['partners'] : []);
                    if (empty($llpPartners)) {
                    $llpPartners[] = ['name' => '', 'dpin_number' => '', 'contact' => '', 'address' => ''];
                    }
                    @endphp
                    @foreach($llpPartners as $index => $partner)
                    <div class="llp-partner-entry mb-3 @if($index > 0 || count($llpPartners) > 1) border-bottom pb-3 @endif">
                        <div class="row g-2">
                            <div class="col-12 col-md-3">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Partner Name *</label>
                                    <input type="text" class="form-control form-control-sm" name="llp_partner_name[]" value="{{ old("llp_partner_name.$index", $partner['name'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group mb-2">
                                    <label class="form-label small">DPIN Number *</label>
                                    <input type="text" class="form-control form-control-sm" name="llp_partner_dpin[]" value="{{ old("llp_partner_dpin.$index", $partner['dpin_number'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Contact Number *</label>
                                    <input type="tel" class="form-control form-control-sm" name="llp_partner_contact[]" value="{{ old("llp_partner_contact.$index", $partner['contact'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Full Address *</label>
                                    <input type="text" class="form-control form-control-sm" name="llp_partner_address[]" value="{{ old("llp_partner_address.$index", $partner['address'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-1 d-flex align-items-end">
                                <div class="form-group mb-2 w-100">
                                    @if($index > 0 || count($llpPartners) > 1)
                                    <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeLLPPartner(this)" title="Remove Partner">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                    @else
                                    <div style="height: 38px;"></div> <!-- Spacer for alignment -->
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
<div id="company_fields" class="entity-specific-fields" style="display:none;">
    <!-- Unified Company Details Card -->
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
                            value="{{ old('cin_number', isset($application->entityDetails->additional_data['company']['cin_number']) ? $application->entityDetails->additional_data['company']['cin_number'] : '') }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Date of Incorporation *</label>
                        <input type="date" class="form-control" id="incorporation_date" name="incorporation_date"
                            value="{{ old('incorporation_date', isset($application->entityDetails->additional_data['company']['incorporation_date']) ? $application->entityDetails->additional_data['company']['incorporation_date'] : '') }}" required>
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
                    @php
                    $directors = old('director_name', isset($application->entityDetails->additional_data['partners']) && in_array($application->entityDetails->entity_type, ['private_company', 'public_company']) ? $application->entityDetails->additional_data['partners'] : []);
                    if (empty($directors)) {
                    $directors[] = ['name' => '', 'din_number' => '', 'contact' => '', 'address' => ''];
                    }
                    @endphp
                    @foreach($directors as $index => $director)
                    <div class="director-entry mb-3 @if($index > 0 || count($directors) > 1) border-bottom pb-3 @endif">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">Director Name *</label>
                                    <input type="text" class="form-control" name="director_name[]" value="{{ old("director_name.$index", $director['name'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">DIN Number *</label>
                                    <input type="text" class="form-control" name="director_din[]" value="{{ old("director_din.$index", $director['din_number'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">Contact Number *</label>
                                    <input type="tel" class="form-control" name="director_contact[]" value="{{ old("director_contact.$index", $director['contact'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label class="form-label">Full Address *</label>
                                    <textarea class="form-control form-control-sm" name="director_address[]" rows="1" required>{{ old("director_address.$index", $director['address'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                @if($index > 0 || count($directors) > 1)
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
    <div id="cooperative_fields" class="entity-specific-fields" style="display:none;">
    <!-- Unified Cooperative Society Details Card -->
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
                            value="{{ old('cooperative_reg_number', isset($application->entityDetails->additional_data['cooperative']['reg_number']) ? $application->entityDetails->additional_data['cooperative']['reg_number'] : '') }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Registration Date *</label>
                        <input type="date" class="form-control" id="cooperative_reg_date" name="cooperative_reg_date"
                            value="{{ old('cooperative_reg_date', isset($application->entityDetails->additional_data['cooperative']['reg_date']) ? $application->entityDetails->additional_data['cooperative']['reg_date'] : '') }}" required>
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
                    @php
                    $committeeMembers = old('committee_name', isset($application->entityDetails->additional_data['partners']) && $application->entityDetails->entity_type === 'cooperative_society' ? $application->entityDetails->additional_data['partners'] : []);
                    if (empty($committeeMembers)) {
                    $committeeMembers[] = ['name' => '', 'designation' => '', 'contact' => '', 'address' => ''];
                    }
                    @endphp
                    @foreach($committeeMembers as $index => $member)
                    <div class="committee-entry mb-3 @if($index > 0 || count($committeeMembers) > 1) border-bottom pb-3 @endif">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">Member Name *</label>
                                    <input type="text" class="form-control" name="committee_name[]" value="{{ old("committee_name.$index", $member['name'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label class="form-label">Designation *</label>
                                    <input type="text" class="form-control" name="committee_designation[]" value="{{ old("committee_designation.$index", $member['designation'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label class="form-label">Contact Number *</label>
                                    <input type="tel" class="form-control" name="committee_contact[]" value="{{ old("committee_contact.$index", $member['contact'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Full Address *</label>
                                    <textarea class="form-control form-control-sm" name="committee_address[]" rows="1" required>{{ old("committee_address.$index", $member['address'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                @if($index > 0 || count($committeeMembers) > 1)
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
    <div id="trust_fields" class="entity-specific-fields" style="display:none;">
    <!-- Unified Trust Details Card -->
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
                            value="{{ old('trust_reg_number', isset($application->entityDetails->additional_data['trust']['reg_number']) ? $application->entityDetails->additional_data['trust']['reg_number'] : '') }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Registration Date *</label>
                        <input type="date" class="form-control" id="trust_reg_date" name="trust_reg_date"
                            value="{{ old('trust_reg_date', isset($application->entityDetails->additional_data['trust']['reg_date']) ? $application->entityDetails->additional_data['trust']['reg_date'] : '') }}" required>
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
                    @php
                    $trustees = old('trustee_name', isset($application->entityDetails->additional_data['partners']) && $application->entityDetails->entity_type === 'trust' ? $application->entityDetails->additional_data['partners'] : []);
                    if (empty($trustees)) {
                    $trustees[] = ['name' => '', 'designation' => '', 'contact' => '', 'address' => ''];
                    }
                    @endphp
                    @foreach($trustees as $index => $trustee)
                    <div class="trustee-entry mb-3 @if($index > 0 || count($trustees) > 1) border-bottom pb-3 @endif">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">Trustee Name *</label>
                                    <input type="text" class="form-control" name="trustee_name[]" value="{{ old("trustee_name.$index", $trustee['name'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">Designation *</label>
                                    <input type="text" class="form-control" name="trustee_designation[]" value="{{ old("trustee_designation.$index", $trustee['designation'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">Contact Number *</label>
                                    <input type="tel" class="form-control" name="trustee_contact[]" value="{{ old("trustee_contact.$index", $trustee['contact'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label class="form-label">Full Address *</label>
                                    <textarea class="form-control form-control-sm" name="trustee_address[]" rows="1" required>{{ old("trustee_address.$index", $trustee['address'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                @if($index > 0 || count($trustees) > 1)
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
    <input type="text" 
           class="form-control form-control-sm" 
           id="business_address" 
           name="business_address" 
           value="{{ old('business_address', isset($application->entityDetails) ? $application->entityDetails->business_address : '') }}" 
           required>
</div>

        </div>
        <div class="col-12 col-md-4">
            <div class="form-group mb-2">
                <label for="house_no" class="form-label small">House No. / Building</label>
                <input type="text" class="form-control form-control-sm" id="house_no" name="house_no"
                    value="{{ old('house_no', isset($application->entityDetails) ? $application->entityDetails->house_no : '') }}" required>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="form-group mb-2">
                <label for="landmark" class="form-label small">Landmark</label>
                <input type="text" class="form-control form-control-sm" id="landmark" name="landmark"
                    value="{{ old('landmark', isset($application->entityDetails) ? $application->entityDetails->landmark : '') }}" required>
            </div>
        </div>
    </div>
    <div class="row g-2">
          <div class="col-12 col-md-3">
            <div class="form-group mb-2">
                <label for="state_id" class="form-label small">State *</label>
                <select class="form-select form-select-sm" id="state_id" name="state_id" required>
                    <option value="">-- Select State --</option>
                    @foreach($states as $state)
                    <option value="{{ $state->id }}"
                        {{ isset($application->entityDetails) && $application->entityDetails->state_id == $state->id ? 'selected' : '' }}>
                        {{ $state->state_name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="form-group mb-2">
                <label for="district_id" class="form-label small">District *</label>
                <select class="form-select form-select-sm" id="district_id" name="district_id" required>
                    <option value="">Select District</option>
                    @if(isset($application->entityDetails) && $application->entityDetails->district_id)
                    @php
                    $district = DB::table('core_district')->where('id', $application->entityDetails->district_id)->first();
                    @endphp
                    @if($district)
                    <option value="{{ $district->id }}" selected>{{ $district->district_name }}</option>
                    @endif
                    @endif
                </select>
            </div>
        </div>
        <div class="col-12 col-md-2">
            <div class="form-group mb-2">
                <label for="city" class="form-label small">City *</label>
                <input type="text" class="form-control form-control-sm" id="city" name="city"
                    value="{{ old('city', isset($application->entityDetails) ? $application->entityDetails->city : '') }}" required>
            </div>
        </div>
      
        <div class="col-12 col-md-2">
            <div class="form-group mb-2">
                <label for="pincode" class="form-label small">Pincode *</label>
                <input type="text" class="form-control form-control-sm" id="pincode" name="pincode"
                    value="{{ old('pincode', isset($application->entityDetails) ? $application->entityDetails->pincode : '') }}" required>
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
    <div class="row g-2">
        <div class="col-12 col-md-4">
            <div class="form-group mb-2">
                <label for="mobile" class="form-label small">Mobile Number *</label>
                <input type="tel" class="form-control form-control-sm" id="mobile" name="mobile"
                    value="{{ old('mobile', isset($application->entityDetails) ? $application->entityDetails->mobile : '') }}" pattern="[0-9]{10}"
                    maxlength="10" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="form-group mb-2">
                <label for="email" class="form-label small">Email Address *</label>
                <input type="email" class="form-control form-control-sm" id="email" name="email"
                    value="{{ old('edit', isset($application->entityDetails) ? $application->entityDetails->email : '') }}" required>
            </div>
        </div>
          <div class="col-12 col-md-4">
            <div class="form-group mb-2">
                <label for="tan_number" class="form-label small">TAN Number (if any)</label>
                <input type="text" class="form-control form-control-sm" id="tan_number" name="tan_number"
                    value="{{ old('tan_number', isset($application->entityDetails->additional_data['tan_number']) ? $application->entityDetails->additional_data['tan_number'] : '') }}">
            </div>
        </div>
    </div>

    <!-- PAN File -->
<div class="row g-2 align-items-center">
    <!-- PAN Upload -->
    <div class="col-md-3">
        <label for="pan_file" class="form-label small">PAN Document *</label>
        <div class="input-group input-group-sm">
            <input type="file" class="form-control form-control-sm d-none" id="pan_file" name="pan_file" accept=".pdf,.jpg,.jpeg,.png">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="pan_file_upload_btn">Upload PAN</button>
        </div>
        <div id="pan_file_name" class="small text-muted {{ $panDoc ? '' : 'd-none' }}">
            @if($panDoc)
                <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="{{ asset('storage/' . $panDoc['path']) }}">View</a> ({{ $panDoc['remarks'] }})
                <input type="hidden" name="existing_pan_file" value="{{ $panDoc['path'] }}">
                <button type="button" class="btn btn-sm btn-link text-danger" onclick="removeExistingFile(this, 'pan_file')">Remove</button>
            @endif
        </div>
    </div>

    <!-- PAN Number + Verified -->
    <div class="col-md-5">
        <label for="pan_number" class="form-label small">PAN Number *</label>
        <div class="input-group input-group-sm">
            <input type="text" 
                   class="form-control form-control-sm" 
                   id="pan_number" 
                   name="pan_number" 
                   value="{{ old('pan_number', $panDoc['details']['pan_number'] ?? ($application->entityDetails->pan_number ?? '')) }}" 
                   pattern="[A-Z]{5}[0-9]{4}[A-Z]" 
                   maxlength="10" required>
            <div class="input-group-text">
                <input class="form-check-input mt-0" 
                       type="checkbox" 
                       id="pan_verified" 
                       name="pan_verified" 
                       {{ old('pan_verified', $panDoc['verified'] ?? false) ? 'checked' : '' }}>
                <label for="pan_verified" class="ms-1 small mb-0">Verified</label>
            </div>
        </div>
    </div>

    <!-- GST Applicable -->
    <div class="col-md-4">
        <label for="gst_applicable" class="form-label small">GST Applicable *</label>
        <select class="form-control form-control-sm" id="gst_applicable" name="gst_applicable" required onchange="toggleGSTFields()">
            <option value="" disabled {{ old('gst_applicable', isset($application->entityDetails) ? '' : 'selected') }}>-- Select --</option>
            <option value="yes" {{ old('gst_applicable', isset($application->entityDetails) && $application->entityDetails->gst_applicable === 'yes' ? 'selected' : '') }}>Yes</option>
            <option value="no" {{ old('gst_applicable', isset($application->entityDetails) && $application->entityDetails->gst_applicable === 'no' ? 'selected' : '') }}>No</option>
        </select>
    </div>
</div>



    <!-- GST Applicable -->
    {{--<div class="col-12 col-md-6">
        <div class="form-group mb-2">
            <label for="gst_applicable" class="form-label small">GST Applicable *</label>
            <select class="form-control form-control-sm" id="gst_applicable" name="gst_applicable" required onchange="toggleGSTFields()">
                <option value="" disabled {{ old('gst_applicable', isset($application->entityDetails) ? '' : 'selected') }}>-- Select --</option>
                <option value="yes" {{ old('gst_applicable', isset($application->entityDetails) && $application->entityDetails->gst_applicable === 'yes' ? 'selected' : '') }}>Yes</option>
                <option value="no" {{ old('gst_applicable', isset($application->entityDetails) && $application->entityDetails->gst_applicable === 'no' ? 'selected' : '') }}>No</option>
            </select>
        </div>
    </div>--}}
<!-- GST Fields -->
 <div id="gst_fields" style="display: {{ old('gst_applicable', ($application->entityDetails->gst_applicable ?? ($gstDoc ? 'yes' : 'no'))) === 'yes' ? 'block' : 'none' }};">
    <div class="row g-2 align-items-center">
        <!-- GST Document Upload -->
        <div class="col-md-3">
            <label for="gst_file" class="form-label small">GST Document *</label>
            <div class="input-group input-group-sm">
                <input type="file" class="form-control form-control-sm d-none" id="gst_file" name="gst_file" accept=".pdf,.jpg,.jpeg,.png">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="gst_file_upload_btn">Upload GST</button>
            </div>
            <div id="gst_file_name" class="small text-muted {{ $gstDoc ? '' : 'd-none' }}">
                @if($gstDoc)
                    <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="{{ asset('storage/' . $gstDoc['path']) }}">View</a> ({{ $gstDoc['remarks'] }})
                    <input type="hidden" name="existing_gst_file" value="{{ $gstDoc['path'] }}">
                    <button type="button" class="btn btn-sm btn-link text-danger" onclick="removeExistingFile(this, 'gst_file')">Remove</button>
                @endif
            </div>
        </div>

        <!-- GST Number + Verified -->
        <div class="col-md-5">
            <label for="gst_number" class="form-label small">GST Number *</label>
            <div class="input-group input-group-sm">
                <input type="text" 
                       class="form-control form-control-sm" 
                       id="gst_number" 
                       name="gst_number" 
                       value="{{ old('gst_number', $gstDoc['details']['gst_number'] ?? ($application->entityDetails->gst_number ?? '')) }}" 
                       pattern="[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}" 
                       maxlength="15" 
                       placeholder="22AAAAA0000A1Z5" required>
                <div class="input-group-text">
                    <input class="form-check-input mt-0" 
                           type="checkbox" 
                           id="gst_verified" 
                           name="gst_verified" 
                           {{ old('gst_verified', $gstDoc['verified'] ?? false) ? 'checked' : '' }}>
                    <label for="gst_verified " class="ms-1 small mb-0">Verified</label>
                </div>
            </div>
        </div>

        <!-- GST Validity -->
        <div class="col-md-4">
    <label for="gst_validity" class="form-label small">GST Validity *</label>
    <input type="date" 
           class="form-control form-control-sm" 
           id="gst_validity" 
           name="gst_validity" 
           min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" 
           value="{{ old('gst_validity', $gstDoc['details']['gst_validity'] ?? ($application->entityDetails->additional_data['gst_validity'] ?? '')) }}" 
           required>
</div>

    </div>
</div>

    

                    <!-- Seed License File -->
<div class="row g-2 align-items-center">
    <!-- Upload Seed License -->
    <div class="col-md-3">
        <label for="seed_license_file" class="form-label small">Seed License Document *</label>
        <div class="input-group input-group-sm">
            <input type="file" class="form-control form-control-sm d-none" id="seed_license_file" name="seed_license_file" accept=".pdf,.jpg,.jpeg,.png">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="seed_license_file_upload_btn">Upload</button>
        </div>
        <div id="seed_license_file_name" class="small text-muted {{ $seedLicenseDoc ? '' : 'd-none' }}">
            @if($seedLicenseDoc)
                <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="{{ asset('storage/' . $seedLicenseDoc['path']) }}">View</a> ({{ $seedLicenseDoc['remarks'] }})
                <input type="hidden" name="existing_seed_license_file" value="{{ $seedLicenseDoc['path'] }}">
                <button type="button" class="btn btn-sm btn-link text-danger" onclick="removeExistingFile(this, 'seed_license_file')">Remove</button>
            @endif
        </div>
    </div>

    <!-- Seed License Number + Verified -->
    <div class="col-md-5">
        <label for="seed_license" class="form-label small">License Number *</label>
        <div class="input-group input-group-sm">
            <input type="text" 
                   class="form-control form-control-sm" 
                   id="seed_license" 
                   name="seed_license" 
                   value="{{ old('seed_license', $seedLicenseDoc['details']['seed_license_number'] ?? ($application->entityDetails->seed_license ?? '')) }}" 
                   required>
            <div class="input-group-text">
                <input class="form-check-input mt-0" 
                       type="checkbox" 
                       id="seed_license_verified" 
                       name="seed_license_verified" 
                       {{ old('seed_license_verified', $seedLicenseDoc['verified'] ?? false) ? 'checked' : '' }}>
                <label for="seed_license_verified" class="ms-1 small mb-0">Verified</label>
            </div>
        </div>
    </div>

    <!-- Validity Date -->
    <div class="col-md-4">
        <label for="seed_license_validity" class="form-label small">Validity Date *</label>
        <input type="date" 
               class="form-control form-control-sm" 
               id="seed_license_validity" 
               name="seed_license_validity" 
               min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}" 
               value="{{ old('seed_license_validity', $seedLicenseDoc['details']['seed_license_validity'] ?? ($application->entityDetails->additional_data['seed_license_validity'] ?? '')) }}" 
               required>
    </div>
</div>

    
    <!-- Bank Details -->
    <div class="row g-2">
        <div class="col-12">
            <h4 class="fs-6">Bank Details</h4>
        </div>
    </div>
                    <!-- Bank File -->
<div class="row g-2 align-items-center">
    <!-- Bank Document Upload -->
    <div class="col-md-3">
        <label for="bank_file" class="form-label small">Bank Document *</label>
        <div class="input-group input-group-sm">
            <input type="file" class="form-control form-control-sm d-none" id="bank_file" name="bank_file" accept=".pdf,.jpg,.jpeg,.png">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="bank_file_upload_btn">Upload</button>
        </div>
        <div id="bank_file_name" class="small text-muted {{ $bankDoc ? '' : 'd-none' }}">
            @if($bankDoc)
                <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="{{ asset('storage/' . $bankDoc['path']) }}">View</a> ({{ $bankDoc['remarks'] }})
                <input type="hidden" name="existing_bank_file" value="{{ $bankDoc['path'] }}">
                <button type="button" class="btn btn-sm btn-link text-danger" onclick="removeExistingFile(this, 'bank_file')">Remove</button>
            @endif
        </div>
    </div>

    <!-- Bank Name + Account Holder -->
    <div class="col-md-5">
        <div class="row g-2">
            <div class="col-md-6">
                <label for="bank_name" class="form-label small">Bank Name *</label>
                <input type="text" 
                       class="form-control form-control-sm" 
                       id="bank_name" 
                       name="bank_name" 
                       value="{{ old('bank_name', $bankDoc['details']['bank_name'] ?? ($application->entityDetails->additional_data['bank_details']['bank_name'] ?? '')) }}" 
                       required>
            </div>
            <div class="col-md-6">
                <label for="account_holder" class="form-label small">Account Holder *</label>
                <input type="text" 
                       class="form-control form-control-sm" 
                       id="account_holder" 
                       name="account_holder" 
                       value="{{ old('account_holder', $bankDoc['details']['account_holder'] ?? ($application->entityDetails->additional_data['bank_details']['account_holder'] ?? '')) }}" 
                       required>
            </div>
        </div>
    </div>

    <!-- Account Number + IFSC Code -->
    <div class="col-md-4">
        <div class="row g-2">
            <div class="col-md-6">
                <label for="account_number" class="form-label small">Account Number *</label>
                <input type="text" 
                       class="form-control form-control-sm" 
                       id="account_number" 
                       name="account_number" 
                       value="{{ old('account_number', $bankDoc['details']['account_number'] ?? ($application->entityDetails->additional_data['bank_details']['account_number'] ?? '')) }}" 
                       inputmode="numeric" 
                       required>
            </div>
            <div class="col-md-6">
                <label for="ifsc_code" class="form-label small">IFSC Code *</label>
                <input type="text" 
                       class="form-control form-control-sm" 
                       id="ifsc_code" 
                       name="ifsc_code" 
                       value="{{ old('ifsc_code', $bankDoc['details']['ifsc_code'] ?? ($application->entityDetails->additional_data['bank_details']['ifsc_code'] ?? '')) }}" 
                       required>
            </div>
        </div>
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
                <select id="has_authorized_persons" name="has_authorized_persons">
                    <option value="yes" {{ old('has_authorized_persons', isset($application->entityDetails->additional_data['authorized_persons']) ? 'yes' : 'no') == 'yes' ? 'selected' : '' }}>Yes</option>
                    <option value="no" {{ old('has_authorized_persons', isset($application->entityDetails->additional_data['authorized_persons']) ? 'yes' : 'no') == 'no' ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div id="authorized_persons_section" class="{{ old('has_authorized_persons', isset($application->entityDetails->additional_data['authorized_persons']) ? 'yes' : 'no') == 'yes' ? '' : 'd-none' }}">
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
                                <th class="form-label fw-normal small">Aadhar *</th>
                                <th class="form-label fw-normal small">Action</th>
                            </tr>
                        </thead>
                        <tbody id="authorized_persons_container">
                            @php
                            $authPersons = old('auth_person_name', $application->entityDetails->additional_data['authorized_persons'] ?? []);
                            if (empty($authPersons)) {
                            $authPersons[] = ['name' => '', 'contact' => '', 'email' => '', 'address' => '', 'relation' => '', 'letter' => '', 'aadhar' => ''];
                            }
                            @endphp
                            @foreach($authPersons as $index => $person)
    <tr class="authorized-person-entry">
        <!-- Name -->
        <td data-label="Name">
            <input type="text" class="form-control form-control-sm"
                name="auth_person_name[]"
                value="{{ old("auth_person_name.$index", $person['name'] ?? '') }}"
                {{ old('has_authorized_persons', isset($application->entityDetails->additional_data['authorized_persons']) ? 'yes' : 'no') == 'yes' ? 'required' : '' }}>
            <div class="invalid-feedback">Please enter a valid name</div>
        </td>

        <!-- Contact Number -->
        <td data-label="Contact Number">
            <input type="tel" class="form-control form-control-sm"
                name="auth_person_contact[]"
                value="{{ old("auth_person_contact.$index", $person['contact'] ?? '') }}"
                pattern="[0-9]{10}"
                {{ old('has_authorized_persons', isset($application->entityDetails->additional_data['authorized_persons']) ? 'yes' : 'no') == 'yes' ? 'required' : '' }}>
            <div class="invalid-feedback">Please enter a 10-digit phone number</div>
        </td>

        <!-- Email Address -->
        <td data-label="Email Address">
            <input type="email" class="form-control form-control-sm"
                name="auth_person_email[]"
                value="{{ old("auth_person_email.$index", $person['email'] ?? '') }}">
            <div class="invalid-feedback">Please enter a valid email address</div>
        </td>

        <!-- Full Address -->
        <td data-label="Full Address">
            <textarea class="form-control form-control-sm"
                name="auth_person_address[]"
                rows="1"
                {{ old('has_authorized_persons', isset($application->entityDetails->additional_data['authorized_persons']) ? 'yes' : 'no') == 'yes' ? 'required' : '' }}>{{ old("auth_person_address.$index", $person['address'] ?? '') }}</textarea>
            <div class="invalid-feedback">Please enter the full address</div>
        </td>

        <!-- Relation -->
        <td data-label="Relation">
            <input type="text" class="form-control form-control-sm"
                name="auth_person_relation[]"
                value="{{ old("auth_person_relation.$index", $person['relation'] ?? '') }}"
                {{ old('has_authorized_persons', isset($application->entityDetails->additional_data['authorized_persons']) ? 'yes' : 'no') == 'yes' ? 'required' : '' }}>
            <div class="invalid-feedback">Please specify the relation</div>
        </td>

        <!-- Letter of Authorization -->
        <td data-label="Letter of Authorisation">
            <input type="file" class="form-control form-control-sm"
                name="auth_person_letter[]"
                accept=".pdf,.doc,.docx"
                onchange="handleAuthPersonFileChange(this, {{ $index }}, 'letter')"
                {{ old('has_authorized_persons', isset($application->entityDetails->additional_data['authorized_persons']) ? 'yes' : 'no') == 'yes' ? 'required' : '' }}>
            <div class="invalid-feedback">Letter of Authorization is required</div>
            <div id="auth_person_letter_{{ $index }}_name" class="small text-muted mt-1 {{ isset($person['letter']) && !in_array($index, old('removed_auth_person_letter', [])) ? '' : 'd-none' }}">
                @if(isset($person['letter']) && $person['letter'] && !in_array($index, old('removed_auth_person_letter', [])))
                <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal"
                    data-src="{{ asset('storage/' . $person['letter']) }}">View Letter</a>
                <input type="hidden" name="existing_auth_person_letter[{{ $index }}]" value="{{ $person['letter'] }}">
                <button type="button" class="btn btn-sm btn-outline-danger ms-2"
                    onclick="removeExistingAuthPersonFile(this, 'letter', {{ $index }})">
                    Remove
                </button>
                @endif
            </div>
        </td>

        <!-- Aadhar -->
        <td data-label="Aadhar">
            <input type="file" class="form-control form-control-sm"
                name="auth_person_aadhar[]"
                accept=".pdf,.jpg,.jpeg,.png"
                onchange="handleAuthPersonFileChange(this, {{ $index }}, 'aadhar')"
                {{ old('has_authorized_persons', isset($application->entityDetails->additional_data['authorized_persons']) ? 'yes' : 'no') == 'yes' ? 'required' : '' }}>
            <div class="invalid-feedback">Aadhar document is required</div>
            <div id="auth_person_aadhar_{{ $index }}_name" class="small text-muted mt-1 {{ isset($person['aadhar']) && !in_array($index, old('removed_auth_person_aadhar', [])) ? '' : 'd-none' }}">
                @if(isset($person['aadhar']) && $person['aadhar'] && !in_array($index, old('removed_auth_person_aadhar', [])))
                <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal"
                    data-src="{{ asset('storage/' . $person['aadhar']) }}">View Aadhar</a>
                <input type="hidden" name="existing_auth_person_aadhar[{{ $index }}]" value="{{ $person['aadhar'] }}">
                <button type="button" class="btn btn-sm btn-outline-danger ms-2"
                    onclick="removeExistingAuthPersonFile(this, 'aadhar', {{ $index }})">
                    Remove
                </button>
                @endif
            </div>
        </td>

        <!-- Action -->
        <td data-label="Action">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeAuthorizedPerson(this)"><i class="ri-delete-bin-line"></i></button>
        </td>
    </tr>
@endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-1">
                    <button type="button" class="btn btn-sm btn-primary" onclick="addAuthorizedPerson()">+</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Modal -->
    <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fs-6" id="documentModalLabel">Document Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe id="documentFrame" src="" style="width:100%;height:500px;border:none;"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="original_entity_type" value="{{ old('entity_type', isset($application->entityDetails) ? $application->entityDetails->entity_type : '') }}">
</div>
<script>
    
    document.addEventListener('DOMContentLoaded', function() {
        var documentModal = document.getElementById('documentModal');
        documentModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var src = button.getAttribute('data-src');
            var iframe = documentModal.querySelector('#documentFrame');
            iframe.src = src;
        });
        documentModal.addEventListener('hidden.bs.modal', function() {
            var iframe = documentModal.querySelector('#documentFrame');
            iframe.src = ''; // Clear iframe src when modal closes
        });
    });

    function showRelevantFields() {
        const entityType = document.getElementById('entity_type').value;
        const specificFields = document.querySelectorAll('.entity-specific-fields');

        // First hide all entity-specific fields
        specificFields.forEach(el => {
            el.style.display = 'none';
            el.querySelectorAll('input, textarea, select').forEach(input => {
                input.disabled = true;
                input.required = false;
            });
        });

        // Show the relevant field based on entity type
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
    }

    function toggleGSTFields() {
        const gstApplicable = document.getElementById('gst_applicable').value;
        const gstFields = document.getElementById('gst_fields');
        if (gstApplicable === 'yes') {
            gstFields.style.display = 'block';
            gstFields.querySelectorAll('input, textarea').forEach(input => input.disabled = false);
            document.getElementById('gst_number').required = true;
            document.getElementById('gst_validity').required = true;
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
            document.getElementById('gst_validity').required = false;
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

        // Clear scalar fields
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

        // Clear dynamic containers only if they have no filled inputs
        const dynamicContainers = ['partners_container', 'llp_partners_container', 'directors_container', 'committee_container', 'trustees_container'];
        dynamicContainers.forEach(containerId => {
            if (!containers[entityType].includes(containerId)) {
                const container = document.getElementById(containerId);
                if (container && !container.querySelectorAll('input[value]:not([value=""]), textarea:not(:empty)').length) {
                    container.innerHTML = '';
                }
            }
        });

        // Initialize default entry if container is empty
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

    function addPartner() {
        const container = document.getElementById('partners_container');
        const newEntry = document.createElement('div');
        newEntry.className = 'partner-entry mb-2 border-bottom pb-2';
        newEntry.innerHTML = `
        <div class="row g-2">
            <div class="col-12 col-md-4">
                <div class="form-group mb-2">
                    <label class="form-label small">Partner Name *</label>
                    <input type="text" class="form-control form-control-sm" name="  []" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group mb-2">
                    <label class="form-label small">PAN *</label>
                    <input type="text" class="form-control form-control-sm" name="partner_pan[]" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group mb-2">
                    <label class="form-label small">Contact Number *</label>
                    <input type="tel" class="form-control form-control-sm" name="partner_contact[]" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-12 col-md-2 d-flex align-items-end">
                <div class="form-group mb-2 w-100">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removePartner(this)"><i class="ri-delete-bin-line"></i></button>
                </div>
            </div>
        </div>
    `;
        container.appendChild(newEntry);
    }

    function removePartner(button) {
        const entries = document.querySelectorAll('.partner-entry');
        if (entries.length > 1) {
            button.closest('.partner-entry').remove();
        } else {
            alert('At least one partner is required.');
        }
    }

    function addSignatory() {
        const container = document.getElementById('signatories_container');
        const newEntry = document.createElement('div');
        newEntry.className = 'signatory-entry mb-2 border-bottom pb-2';
        newEntry.innerHTML = `
        <div class="row g-2">
            <div class="col-12 col-md-4">
                <div class="form-group mb-2">
                    <label class="form-label small">Signatory Name</label>
                    <input type="text" class="form-control form-control-sm" name="signatory_name[]">
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group mb-2">
                    <label class="form-label small">Designation *</label>
                    <input type="text" class="form-control form-control-sm" name="signatory_designation[]" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group mb-2">
                    <label class="form-label small">Contact Number *</label>
                    <input type="tel" class="form-control form-control-sm" name="signatory_contact[]" required>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-12 col-md-2 d-flex align-items-end">
                <div class="form-group mb-2 w-100">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeSignatory(this)"><i class="ri-delete-bin-line"></i></button>
                </div>
            </div>
        </div>
    `;
        container.appendChild(newEntry);
    }

    function removeSignatory(button) {
        const entries = document.querySelectorAll('.signatory-entry');
        if (entries.length > 1) {
            button.closest('.signatory-entry').remove();
        } else {
            // Clear fields instead of removing the last entry (since signatories are optional)
            const entry = button.closest('.signatory-entry');
            entry.querySelector('input[name="signatory_name[]"]').value = '';
            entry.querySelector('input[name="signatory_designation[]"]').value = '';
            entry.querySelector('input[name="signatory_contact[]"]').value = '';
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
    const index = container.children.length;
    
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
    
    // Add border to first entry if it was the only one
    if (container.children.length === 2) {
        const firstEntry = container.children[0];
        firstEntry.classList.add('border-bottom', 'pb-3');
        // Show remove button on first entry
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
        
        // If only one entry remains, remove its border and hide remove button
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
    const index = container.children.length;
    
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
    
    // Add border to first entry if it was the only one
    if (container.children.length === 2) {
        const firstEntry = container.children[0];
        firstEntry.classList.add('border-bottom', 'pb-3');
        // Show remove button on first entry
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
        
        // If only one entry remains, remove its border and hide remove button
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
     let removedDocuments = {}; 
    function handleFileChange(input, fieldName) {
    const fileNameDiv = document.getElementById(`${fieldName}_name`);
    if (fileNameDiv) {
        if (input.files.length > 0) {
            fileNameDiv.classList.add('d-none');
            const hiddenInput = fileNameDiv.querySelector('input[type="hidden"]');
            if (hiddenInput) hiddenInput.remove();
            const removeBtn = fileNameDiv.querySelector('button');
            if (removeBtn) removeBtn.remove();
            delete removedDocuments[fieldName];
        } else {
            const existingLink = fileNameDiv.querySelector('a');
            if (existingLink) fileNameDiv.classList.remove('d-none');
        }
    }
}

// Generalized function to handle removal of existing files
function removeExistingFile(button, fieldName) {
    const container = button.closest(`#${fieldName}_name`);
    if (container) {
        container.classList.add('d-none');
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = `removed_${fieldName}`;
        hiddenInput.value = '1';
        container.appendChild(hiddenInput);
        removedDocuments[fieldName] = true;
        const fileInput = container.closest('.form-group, .file-upload-wrapper').querySelector(`input[name="${fieldName}"]`);
        if (fileInput) fileInput.required = true;
    }
}


    const hasAuthorizedPersons = document.getElementById('has_authorized_persons');
    const authorizedPersonsSection = document.getElementById('authorized_persons_section');

    function toggleAuthorizedPersonsSection() {
        if (hasAuthorizedPersons.value === 'yes') {
            authorizedPersonsSection.classList.remove('d-none');
            // Add required attributes to inputs
            const inputs = authorizedPersonsSection.querySelectorAll('input:not([type="email"]), textarea');
            inputs.forEach(input => input.setAttribute('required', ''));
        } else {
            authorizedPersonsSection.classList.add('d-none');
            // Remove required attributes and clear inputs
            const inputs = authorizedPersonsSection.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.removeAttribute('required');
                if (input.type !== 'file') input.value = '';
            });
            const fileInputs = authorizedPersonsSection.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => input.value = '');
        }
    }

    hasAuthorizedPersons.addEventListener('change', toggleAuthorizedPersonsSection);
    toggleAuthorizedPersonsSection();

    // Handle file input changes for authorized persons
    // Handle file input change for authorized persons
function handleAuthPersonFileChange(input, index, type) {
    const fileNameDiv = document.getElementById(`auth_person_${type}_${index}_name`);
    if (fileNameDiv) {
        if (input.files.length > 0) {
            fileNameDiv.classList.add('d-none');
            const hiddenInput = fileNameDiv.querySelector('input[type="hidden"]');
            if (hiddenInput) hiddenInput.remove();
            const removeBtn = fileNameDiv.querySelector('button');
            if (removeBtn) removeBtn.remove();
            const removedInput = document.querySelector(`input[name="removed_auth_person_${type}[]"][value="${index}"]`);
            if (removedInput) removedInput.remove();
        } else {
            const existingLink = fileNameDiv.querySelector('a');
            if (existingLink) fileNameDiv.classList.remove('d-none');
        }
    }
}

// Handle removal of existing authorized person files
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
    // Add new authorized person row
function addAuthorizedPerson() {
    const container = document.getElementById('authorized_persons_container');
    const newRow = document.createElement('tr');
    newRow.className = 'authorized-person-entry';
    
    // Get the next available index
    const index = document.querySelectorAll('.authorized-person-entry').length;
    
    newRow.innerHTML = `
        <!-- Name -->
        <td data-label="Name">
            <input type="text" class="form-control form-control-sm" name="auth_person_name[]" required>
            <div class="invalid-feedback">Please enter a valid name</div>
        </td>
        
        <!-- Contact Number -->
        <td data-label="Contact Number">
            <input type="tel" class="form-control form-control-sm" name="auth_person_contact[]" required pattern="[0-9]{10}">
            <div class="invalid-feedback">Please enter a 10-digit phone number</div>
        </td>
        
        <!-- Email Address -->
        <td data-label="Email Address">
            <input type="email" class="form-control form-control-sm" name="auth_person_email[]">
            <div class="invalid-feedback">Please enter a valid email address</div>
        </td>
        
        <!-- Full Address -->
        <td data-label="Full Address">
            <textarea class="form-control form-control-sm" name="auth_person_address[]" rows="2" required></textarea>
            <div class="invalid-feedback">Please enter the full address</div>
        </td>
        
        <!-- Relation -->
        <td data-label="Relation">
            <input type="text" class="form-control form-control-sm" name="auth_person_relation[]" required>
            <div class="invalid-feedback">Please specify the relation</div>
        </td>
        
        <!-- Letter of Authorization -->
        <td data-label="Letter of Authorisation">
            <input type="file" class="form-control form-control-sm" name="auth_person_letter[]" accept=".pdf,.doc,.docx" required>
            <div class="invalid-feedback">Letter of Authorization is required</div>
            <div id="auth_person_letter_${index}_name" class="small text-muted mt-1 d-none"></div>
        </td>
        
        <!-- Aadhar -->
        <td data-label="Aadhar">
            <input type="file" class="form-control form-control-sm" name="auth_person_aadhar[]" accept=".pdf,.jpg,.jpeg,.png" required>
            <div class="invalid-feedback">Aadhar document is required</div>
            <div id="auth_person_aadhar_${index}_name" class="small text-muted mt-1 d-none"></div>
        </td>
        
        <!-- Action -->
        <td data-label="Action">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeAuthorizedPerson(this)"><i class="ri-delete-bin-line"></i></button>
        </td>
    `;
    
    container.appendChild(newRow);
}

// Remove authorized person row with confirmation
function removeAuthorizedPerson(button) {
    const row = button.closest('tr');
    const container = row.parentElement;
    const rows = container.querySelectorAll('tr');
    
    // Check if row has any data
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
        // For the last row, clear all fields instead of removing
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

    // Initialize event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize fields immediately
        showRelevantFields();
        toggleGSTFields();

        // Set up change handlers
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
    });

    // Also run immediately if DOM is already loaded
    if (document.readyState === 'complete') {
        showRelevantFields();
        toggleGSTFields();
    }
</script>
@push('scripts')
<script>
    // Load districts based on state
    $('#state_id').on('change', function() {
        const stateId = $(this).val();
        $('#district_id').html('<option value="">Loading...</option>');

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
                    $('#district_id').html(options);
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr.responseText);
                    $('#district_id').html('<option value="">Error loading districts</option>');
                }
            });
        } else {
            $('#district_id').html('<option value="">Select District</option>');
        }
    });

    // Helper function to safely set field values
    function setFieldValue(fieldId, value) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.value = (value || '').toString().trim();
            return true;
        }
        console.warn(`Field not found: ${fieldId}`);
        return false;
    }

    // Client-side validation for authorized persons
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

        if (!e.defaultPrevented) { // If no field-specific errors prevented default
            const dobErrorDivs = document.querySelectorAll('.dob-error');
            dobErrorDivs.forEach(errorDiv => {
                if (errorDiv.textContent !== '') {
                    // An age validation error exists, prevent form submission
                    e.preventDefault();
                    // Scroll to the first problematic DOB input
                    const invalidDobInput = errorDiv.previousElementSibling;
                    if (invalidDobInput) {
                        invalidDobInput.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        invalidDobInput.focus();
                    }
                }
            });
        }

    });


    // Image preprocessing for better OCR
    async function preprocessImage(file) {
        return new Promise((resolve) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);

                // Apply contrast enhancement
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

    // OCR text extraction with error handling
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

    // Generic field extractor with multiple patterns
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

    // Bank document processor
    async function processBankDocument(file, fileNameField) {
        try {
            fileNameField.textContent = 'Processing bank document...';
            fileNameField.classList.remove('d-none');

            const processedImage = await preprocessImage(file);
            const extractedText = await extractTextFromImage(processedImage);
            console.log('Extracted Bank Text:', extractedText);

            // Enhanced extraction patterns
            const extractedData = {
                bankName: extractField(extractedText, [
                    /Bank\s*Name[:\s]*([^\n]+)/i,
                    /([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*\s+Bank)/i,
                    /(State Bank of India|SBI|HDFC Bank|ICICI Bank)/i
                ]) || 'State Bank of India', // Default fallback

                branch: extractField(extractedText, [
                    /Branch\s*[:\s]*([^\n]+)/i,
                    /Br\s*[:\s]*([^\n]+)/i,
                    /Office\s*[:\s]*([^\n]+)/i
                ]),

                ifscCode: extractField(extractedText, [
                    /IFSC\s*[:\s]*([A-Z]{4}0[A-Z0-9]{6})/i,
                    /([A-Z]{4}0[A-Z0-9]{6})/
                ], /^[A-Z]{4}0[A-Z0-9]{6}$/),

                accountHolder: extractField(extractedText, [
                    /Name\s*[:\s]*(Mr\.?|Mrs\.?|Ms\.?)?\s*([^\n]+)/i,
                    /Account\s*Holder\s*[:\s]*([^\n]+)/i,
                    /(?:Mr|Mrs|Ms)\.?\s*([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)/i
                ]),

                accountNumber: extractField(extractedText, [
                    /Account\s*No\.?\s*[:\s]*(\d{9,18})/i,
                    /A\/C\s*No\.?\s*[:\s]*(\d{9,18})/i,
                    /(\d{10,18})/
                ], /^\d{10,18}$/),

                cifNumber: extractField(extractedText, [
                    /CIF\s*[:\s]*(\d{8,15})/i,
                    /Customer\s*ID\s*[:\s]*(\d{8,15})/i
                ], /^\d{8,15}$/)
            };

            // Auto-fill all bank-related fields
            setFieldValue('bank_name', extractedData.bankName);
            setFieldValue('bank_branch', extractedData.branch);
            setFieldValue('account_holder', extractedData.accountHolder);
            setFieldValue('account_number', extractedData.accountNumber);
            setFieldValue('ifsc_code', extractedData.ifscCode);
            setFieldValue('cif_number', extractedData.cifNumber);

            fileNameField.textContent = 'Bank details extracted successfully';
            return extractedData;
        } catch (error) {
            console.error('Bank processing error:', error);
            fileNameField.textContent = 'Error processing bank document';
            throw error;
        }
    }

    // PAN Card processor
    async function processPANCard(file, fileNameField) {
        try {
            fileNameField.textContent = 'Processing PAN card...';
            fileNameField.classList.remove('d-none');

            const processedImage = await preprocessImage(file);
            const extractedText = await extractTextFromImage(processedImage);
            console.log('Extracted PAN Text:', extractedText);

            const panNumber = extractField(extractedText, [
                /PAN\s*[:\s]*([A-Z]{5}\d{4}[A-Z])/i,
                /Permanent\s*Account\s*Number\s*[:\s]*([A-Z]{5}\d{4}[A-Z])/i,
                /([A-Z]{5}\d{4}[A-Z])/
            ], /^[A-Z]{5}\d{4}[A-Z]$/);

            if (panNumber) {
                setFieldValue('pan_number', panNumber);
                document.getElementById('pan_verified').checked = true;
                fileNameField.textContent = `PAN extracted: ${panNumber}`;
                return panNumber;
            } else {
                throw new Error('PAN number not found');
            }
        } catch (error) {
            console.error('PAN processing error:', error);
            fileNameField.textContent = 'Error processing PAN card';
            throw error;
        }
    }

    // Seed License processor
    async function processSeedLicense(file, fileNameField) {
        try {
            fileNameField.textContent = 'Processing seed license...';
            fileNameField.classList.remove('d-none');

            const processedImage = await preprocessImage(file);
            const extractedText = await extractTextFromImage(processedImage);
            console.log('Extracted Seed License Text:', extractedText);

            const licenseNumber = extractField(extractedText, [
                /Seed\s*License\s*[:\s]*([A-Z0-9]{6,15})/i,
                /License\s*Number\s*[:\s]*([A-Z0-9]{6,15})/i,
                /([A-Z0-9]{6,15})/
            ], /^[A-Z0-9]{6,15}$/);

            if (licenseNumber) {
                setFieldValue('seed_license', licenseNumber);
                document.getElementById('seed_license_verified').checked = true;
                fileNameField.textContent = `License extracted: ${licenseNumber}`;
                return licenseNumber;
            } else {
                throw new Error('License number not found');
            }
        } catch (error) {
            console.error('License processing error:', error);
            fileNameField.textContent = 'Error processing license';
            throw error;
        }
    }

    // GST Document processor
    async function processGSTDocument(file, gstFileName) {
    gstFileName.textContent = file.name;

    return new Promise((resolve, reject) => {
        Tesseract.recognize(
            file,
            'eng',
            { logger: info => console.log(info) }
        ).then(({ data: { text } }) => {
            console.log("OCR Text:", text);
            const match = text.match(/\d{2}[A-Z0-9]{10}[A-Z0-9]Z\d/); // Regex for GSTIN
            //alert(match);
            if (match) {
                document.getElementById('gst_number').value = match[0];
                resolve();
            } else {
                reject('GSTIN not found in document.');
            }
        }).catch(reject);
    });
}

    // Initialize all upload handlers
    function initializeUploaders() {
        // Bank Document
        const bankUploadBtn = document.getElementById('bank_file_upload_btn');
        const bankFileInput = document.getElementById('bank_file');
        const bankFileName = document.getElementById('bank_file_name');
        if (bankUploadBtn && bankFileInput && bankFileName) {
            bankUploadBtn.addEventListener('click', () => bankFileInput.click());
            bankFileInput.addEventListener('change', (e) => {
                if (e.target.files.length) {
                    processBankDocument(e.target.files[0], bankFileName)
                        .then(() => {
                            const existingBank = document.querySelector('input[name="existing_bank_file"]');
                            if (existingBank) existingBank.value = '';
                        })
                        .catch(() => alert('Failed to extract bank details. Please check the document and try again.'));
                }
            });
        }

        // PAN Card
        const panUploadBtn = document.getElementById('pan_file_upload_btn');
        const panFileInput = document.getElementById('pan_file');
        const panFileName = document.getElementById('pan_file_name');

        if (panUploadBtn && panFileInput && panFileName) {
            panUploadBtn.addEventListener('click', () => panFileInput.click());
            panFileInput.addEventListener('change', (e) => {
                if (e.target.files.length) {
                    processPANCard(e.target.files[0], panFileName)
                        .then(() => {
                            const existingPan = document.querySelector('input[name="existing_pan_file"]');
                            if (existingPan) existingPan.value = '';
                        })
                        .catch(() => alert('Failed to extract PAN. Please check the document and try again.'));
                }
            });
        }

        // Seed License
        const seedUploadBtn = document.getElementById('seed_license_file_upload_btn');
        const seedFileInput = document.getElementById('seed_license_file');
        const seedFileName = document.getElementById('seed_license_file_name');
        if (seedUploadBtn && seedFileInput && seedFileName) {
            seedUploadBtn.addEventListener('click', () => seedFileInput.click());
            seedFileInput.addEventListener('change', (e) => {
                if (e.target.files.length) {
                    processSeedLicense(e.target.files[0], seedFileName)
                        .then(() => {
                            const existingSeed = document.querySelector('input[name="existing_seed_license_file"]');
                            if (existingSeed) existingSeed.value = '';
                        })
                        .catch(() => alert('Failed to extract license. Please check the document and try again.'));
                }
            });
        }

        // GST Document
        const gstUploadBtn = document.getElementById('gst_file_upload_btn');
        const gstFileInput = document.getElementById('gst_file');
        const gstFileName = document.getElementById('gst_file_name');
        if (gstUploadBtn && gstFileInput && gstFileName) {
            gstUploadBtn.addEventListener('click', () => gstFileInput.click());
            gstFileInput.addEventListener('change', (e) => {
                if (e.target.files.length) {
                    processGSTDocument(e.target.files[0], gstFileName)
                        .then(() => {
                            const existingGst = document.querySelector('input[name="existing_gst_file"]');
                            if (existingGst) existingGst.value = '';
                        })
                        .catch(() => alert('Failed to extract GST. Please check the document and try again.'));
                }
            });
        }
    }




    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        initializeUploaders();

        // Trigger state change on page load to load districts if state is selected
        if ($('#state_id').val()) {
            $('#state_id').trigger('change');
        }

        // Initialize DOB validation for all date inputs
        const dobInputs = document.querySelectorAll('.dob-input');
        console.log("DOB Inputs found:", dobInputs.length);

        dobInputs.forEach(dobInput => {
            // Find related elements
            const ageInput = dobInput.closest('.col-12').nextElementSibling.querySelector('.age-display');
            let errorDiv = dobInput.parentNode.querySelector('.invalid-feedback.dob-error');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.classList.add('invalid-feedback', 'dob-error');
                dobInput.parentNode.appendChild(errorDiv);
            }
            const form = dobInput.closest('form');
            // Function to validate DOB
            const validateDOB = () => {
                // Clear previous error state first
                dobInput.classList.remove('is-invalid');
                errorDiv.textContent = ''; // Clear existing error message
                const dob = new Date(dobInput.value);
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const m = today.getMonth() - dob.getMonth();

                // Adjust age if birthday hasn't occurred yet this year
                if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                    age--;
                }

                // Update the age field
                ageInput.value = age > 0 ? age : '';

                // Validate age (minimum 18 years)
                if (dobInput.value && age < 18) {
                    dobInput.classList.add('is-invalid');
                    errorDiv.textContent = 'Must be at least 18 years old.';

                    // Disable form submission if configured
                    if (form) {
                        const submitBtn = form.querySelector('button[type="submit"]');
                        if (submitBtn) submitBtn.disabled = true;
                    }
                    return false;
                } else {
                    dobInput.classList.remove('is-invalid');
                    errorDiv.textContent = '';

                    // Re-enable form submission if configured
                    if (form) {
                        const submitBtn = form.querySelector('button[type="submit"]');
                        if (submitBtn) submitBtn.disabled = false;
                    }
                    return true;
                }
            };

            // Event listener for change
            dobInput.addEventListener('change', validateDOB);

            // Event listener for blur (when field loses focus)
            dobInput.addEventListener('blur', validateDOB);

            // Also validate on form submission
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!validateDOB()) {
                        e.preventDefault(); // Prevent form submission if invalid
                        // Scroll to the first error
                        dobInput.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                        dobInput.focus();
                    }
                });
            }

            // Trigger validation on page load if DOB is already set
            if (dobInput.value) {
                validateDOB();
            }
        });

    });

    $(document).ready(function() {
    const fileFields = ['bank_file', 'seed_license_file', 'pan_file', 'gst_file'];
    fileFields.forEach(field => {
        // Trigger file input click when upload button is clicked
        // $(`#${field}_upload_btn`).on('click', function() {
        //     $(`#${field}`).trigger('click');
        // });
        // Handle file input change
        $(`#${field}`).on('change', function() {
            handleFileChange(this, field);
        });
    });
});
</script>
@endpush