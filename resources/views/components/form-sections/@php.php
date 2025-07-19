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

    <div id="partnership_fields" class="entity-specific-fields" style="display:none;">
        <div class="card mb-4">
            <div class="card-header bg-light p-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fs-6">Partner Details</h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addPartner()">+</button>
            </div>
            <div class="card-body">
                <div id="partners_container">
                    @php
                        $partners = old('partner_name', isset($application->entityDetails->additional_data['partners']) && $application->entityDetails->entity_type === 'partnership' ? $application->entityDetails->additional_data['partners'] : []);
                        if (empty($partners)) {
                            $partners[] = ['name' => '', 'pan' => '', 'contact' => ''];
                        }
                    @endphp
                    @foreach($partners as $index => $partner)
                    <div class="partner-entry mb-2 border-bottom pb-2">
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
                                    <input type="tel" class="form-control form-control-sm" name="partner_contact[]" value="{{ old("partner_contact.$index", $partner['contact'] ?? '') }}" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2 d-flex align-items-end">
                                <div class="form-group mb-2 w-100">
                                    <button type="button" class="btn btn-sm btn-danger w-100" onclick="removePartner(this)">-</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-light p-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fs-6">Signatory Details</h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addSignatory()">+</button>
            </div>
            <div class="card-body">
                <div id="signatories_container">
                    @php
                        $signatories = old('signatory_name', isset($application->entityDetails->additional_data['signatories']) && $application->entityDetails->entity_type === 'partnership' ? $application->entityDetails->additional_data['signatories'] : []);
                        if (empty($signatories)) {
                            $signatories[] = ['name' => '', 'designation' => '', 'contact' => ''];
                        }
                    @endphp
                    @foreach($signatories as $index => $signatory)
                    <div class="signatory-entry mb-2 border-bottom pb-2">
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
                                    <input type="tel" class="form-control form-control-sm" name="signatory_contact[]" value="{{ old("signatory_contact.$index", $signatory['contact'] ?? '') }}" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-12 col-md-2 d-flex align-items-end">
                                <div class="form-group mb-2 w-100">
                                    <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeSignatory(this)">-</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div id="llp_fields" class="entity-specific-fields" style="display:none;">
        <div class="card mb-2">
            <div class="card-header bg-light p-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fs-6">Designated Partners Details</h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addLLPPartner()">+</button>
            </div>
            <div class="card-body p-2">
                <div id="llp_partners_container">
                    @php
                    $llpPartners = old('llp_partner_name', isset($application->entityDetails->additional_data['partners']) && $application->entityDetails->entity_type === 'llp' ? $application->entityDetails->additional_data['partners'] : []);
                    if (empty($llpPartners)) {
                        $llpPartners[] = ['name' => '', 'dpin_number' => '', 'contact' => '', 'address' => ''];
                    }
                    @endphp
                    @foreach($llpPartners as $index => $partner)
                    <div class="llp-partner-entry mb-2 border-bottom pb-2">
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
                            <div class="col-12 col-md-3">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Contact Number *</label>
                                    <input type="tel" class="form-control form-control-sm" name="llp_partner_contact[]" value="{{ old("llp_partner_contact.$index", $partner['contact'] ?? '') }}" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label small">Full Address *</label>
                                <input type="text" class="form-control form-control-sm" name="llp_partner_address[]" value="{{ old("llp_partner_address.$index", $partner['address'] ?? '') }}" required>
                            </div>
                            <div class="col-12 col-md-1 d-flex align-items-end">
                                <div class="form-group mb-2 w-100">
                                    <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeLLPPartner(this)">-</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="row g-2">
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
    </div>

    <div id="company_fields" class="entity-specific-fields" style="display:none;">
        <div class="card mb-2">
            <div class="card-header bg-light p-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fs-6">Directors Details</h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addDirector()">+</button>
            </div>
            <div class="card-body p-2">
                <div id="directors_container">
                    @php
                    $directors = old('director_name', isset($application->entityDetails->additional_data['partners']) && in_array($application->entityDetails->entity_type, ['private_company', 'public_company']) ? $application->entityDetails->additional_data['partners'] : []);
                    if (empty($directors)) {
                        $directors[] = ['name' => '', 'din_number' => '', 'contact' => '', 'address' => ''];
                    }
                    @endphp
                    @foreach($directors as $index => $director)
                    <div class="director-entry mb-2 border-bottom pb-2">
                        <div class="row g-2">
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Director Name *</label>
                                    <input type="text" class="form-control form-control-sm" name="director_name[]" value="{{ old("director_name.$index", $director['name'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label class="form-label small">DIN Number *</label>
                                    <input type="text" class="form-control form-control-sm" name="director_din[]" value="{{ old("director_din.$index", $director['din_number'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Contact Number *</label>
                                    <input type="tel" class="form-control form-control-sm" name="director_contact[]" value="{{ old("director_contact.$index", $director['contact'] ?? '') }}" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label class="form-label small">Full Address *</label>
                            <textarea class="form-control form-control-sm" name="director_address[]" rows="2" required>{{ old("director_address.$index", $director['address'] ?? '') }}</textarea>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeDirector(this)">-</button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="row g-2">
            <div class="col-12 col-md-6">
                <div class="form-group mb-2">
                    <label for="cin_number" class="form-label small">CIN Number *</label>
                    <input type="text" class="form-control form-control-sm" id="cin_number" name="cin_number"
                        value="{{ old('cin_number', isset($application->entityDetails->additional_data['company']['cin_number']) ? $application->entityDetails->additional_data['company']['cin_number'] : '') }}" required>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group mb-2">
                    <label for="incorporation_date" class="form-label small">Date of Incorporation *</label>
                    <input type="date" class="form-control form-control-sm" id="incorporation_date" name="incorporation_date"
                        value="{{ old('incorporation_date', isset($application->entityDetails->additional_data['company']['incorporation_date']) ? $application->entityDetails->additional_data['company']['incorporation_date'] : '') }}" required>
                </div>
            </div>
        </div>
    </div>

    <div id="cooperative_fields" class="entity-specific-fields" style="display:none;">
        <div class="card mb-2">
            <div class="card-header bg-light p-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fs-6">Committee Members Details</h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addCommitteeMember()">+</button>
            </div>
            <div class="card-body p-2">
                <div id="committee_container">
                    @php
                    $committeeMembers = old('committee_name', isset($application->entityDetails->additional_data['partners']) && $application->entityDetails->entity_type === 'cooperative_society' ? $application->entityDetails->additional_data['partners'] : []);
                    if (empty($committeeMembers)) {
                        $committeeMembers[] = ['name' => '', 'designation' => '', 'contact' => '', 'address' => ''];
                    }
                    @endphp
                    @foreach($committeeMembers as $index => $member)
                    <div class="committee-entry mb-2 border-bottom pb-2">
                        <div class="row g-2">
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Member Name *</label>
                                    <input type="text" class="form-control form-control-sm" name="committee_name[]" value="{{ old("committee_name.$index", $member['name'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Designation *</label>
                                    <input type="text" class="form-control form-control-sm" name="committee_designation[]" value="{{ old("committee_designation.$index", $member['designation'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Contact Number *</label>
                                    <input type="tel" class="form-control form-control-sm" name="committee_contact[]" value="{{ old("committee_contact.$index", $member['contact'] ?? '') }}" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label class="form-label small">Full Address *</label>
                            <textarea class="form-control form-control-sm" name="committee_address[]" rows="2" required>{{ old("committee_address.$index", $member['address'] ?? '') }}</textarea>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeCommitteeMember(this)">-</button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="row g-2">
            <div class="col-12 col-md-6">
                <div class="form-group mb-2">
                    <label for="cooperative_reg_number" class="form-label small">Registration Number *</label>
                    <input type="text" class="form-control form-control-sm" id="cooperative_reg_number" name="cooperative_reg_number"
                        value="{{ old('cooperative_reg_number', isset($application->entityDetails->additional_data['cooperative']['reg_number']) ? $application->entityDetails->additional_data['cooperative']['reg_number'] : '') }}" required>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group mb-2">
                    <label for="cooperative_reg_date" class="form-label small">Registration Date *</label>
                    <input type="date" class="form-control form-control-sm" id="cooperative_reg_date" name="cooperative_reg_date"
                        value="{{ old('cooperative_reg_date', isset($application->entityDetails->additional_data['cooperative']['reg_date']) ? $application->entityDetails->additional_data['cooperative']['reg_date'] : '') }}" required>
                </div>
            </div>
        </div>
    </div>

    <div id="trust_fields" class="entity-specific-fields" style="display:none;">
        <div class="card mb-2">
            <div class="card-header bg-light p-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fs-6">Trustees Details</h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addTrustee()">+</button>
            </div>
            <div class="card-body p-2">
                <div id="trustees_container">
                    @php
                    $trustees = old('trustee_name', isset($application->entityDetails->additional_data['partners']) && $application->entityDetails->entity_type === 'trust' ? $application->entityDetails->additional_data['partners'] : []);
                    if (empty($trustees)) {
                        $trustees[] = ['name' => '', 'designation' => '', 'contact' => '', 'address' => ''];
                    }
                    @endphp
                    @foreach($trustees as $index => $trustee)
                    <div class="trustee-entry mb-2 border-bottom pb-2">
                        <div class="row g-2">
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Trustee Name *</label>
                                    <input type="text" class="form-control form-control-sm" name="trustee_name[]" value="{{ old("trustee_name.$index", $trustee['name'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Designation *</label>
                                    <input type="text" class="form-control form-control-sm" name="trustee_designation[]" value="{{ old("trustee_designation.$index", $trustee['designation'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group mb-2">
                                    <label class="form-label small">Contact Number *</label>
                                    <input type="tel" class="form-control form-control-sm" name="trustee_contact[]" value="{{ old("trustee_contact.$index", $trustee['contact'] ?? '') }}" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label class="form-label small">Full Address *</label>
                            <textarea class="form-control form-control-sm" name="trustee_address[]" rows="2" required>{{ old("trustee_address.$index", $trustee['address'] ?? '') }}</textarea>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeTrustee(this)">-</button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="row g-2">
            <div class="col-12 col-md-6">
                <div class="form-group mb-2">
                    <label for="trust_reg_number" class="form-label small">Registration Number *</label>
                    <input type="text" class="form-control form-control-sm" id="trust_reg_number" name="trust_reg_number"
                        value="{{ old('trust_reg_number', isset($application->entityDetails->additional_data['trust']['reg_number']) ? $application->entityDetails->additional_data['trust']['reg_number'] : '') }}" required>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group mb-2">
                    <label for="trust_reg_date" class="form-label small">Registration Date *</label>
                    <input type="date" class="form-control form-control-sm" id="trust_reg_date" name="trust_reg_date"
                        value="{{ old('trust_reg_date', isset($application->entityDetails->additional_data['trust']['reg_date']) ? $application->entityDetails->additional_data['trust']['reg_date'] : '') }}" required>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-12 col-md-4">
            <div class="form-group mb-2">
                <label for="business_address" class="form-label small">Business Place/Shop Address *</label>
                <textarea class="form-control form-control-sm" id="business_address" name="business_address" rows="2" required>{{ old('business_address', isset($application->entityDetails) ? $application->entityDetails->business_address : '') }}</textarea>
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
        <div class="col-12 col-md-2">
            <div class="form-group mb-2">
                <label for="city" class="form-label small">City *</label>
                <input type="text" class="form-control form-control-sm" id="city" name="city"
                    value="{{ old('city', isset($application->entityDetails) ? $application->entityDetails->city : '') }}" required>
            </div>
        </div>
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
        <div class="col-12 col-md-6">
            <div class="form-group mb-2">
                <label for="mobile" class="form-label small">Mobile Number *</label>
            <input type="tel" class="form-control form-control-sm" id="mobile" name="mobile"
                value="{{ old('mobile', isset($application->entityDetails) ? $application->entityDetails->mobile : '') }}"
                maxlength="10" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="form-group mb-2">
                <label for="email" class="form-label small">Email Address *</label>
                <input type="email" class="form-control form-control-sm" id="email" name="email"
                    value="{{ old('edit', isset($application->entityDetails) ? $application->entityDetails->email : '') }}" required>
            </div>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-12 col-md-6">
            <div class="form-group mb-2">
                <label for="pan_file" class="form-label small">Upload PAN Document *</label> {{-- Label for file upload --}}
                <div class="input-group input-group-sm">
                    <input type="file" class="form-control d-none" id="pan_file" name="pan_file" accept=".pdf,.jpg,.jpeg,.png">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="pan_upload_btn">Upload & Extract PAN</button>
                    <div id="pan_file_name_display" class="input-group-text small text-muted {{ $panDoc ? '' : 'd-none' }}">
                        @if($panDoc)
                        <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="{{ asset('storage/' . $panDoc['path']) }}">View Document</a>
                        <input type="hidden" name="existing_pan_file" value="{{ $panDoc['path'] }}">
                        @else
                        No file chosen.
                        @endif
                    </div>
                </div>
                <label for="pan_number" class="form-label small mt-2">PAN Number *</label> {{-- Label for PAN number input --}}
                <input type="text" class="form-control form-control-sm" id="pan_number" name="pan_number"
                    value="{{ old('pan_number', $panDoc['details']['pan_number'] ?? ($application->entityDetails->pan_number ?? '')) }}"
                    required>
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" id="pan_verified" name="pan_verified"
                        {{ old('pan_verified', $panDoc['verified'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label small" for="pan_verified">
                        I confirm the PAN number matches the uploaded document
                    </label>
                </div>
                <div id="pan_feedback" class="small text-muted mt-1"></div> {{-- For OCR feedback messages --}}
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="form-group mb-2">
                <label for="gst_applicable" class="form-label small">GST Applicable *</label>
                <select class="form-select form-select-sm" id="gst_applicable" name="gst_applicable" required onchange="toggleGSTFields()">
                    <option value="" disabled {{ old('gst_applicable', isset($application->entityDetails) ? '' : 'selected') }}>-- Select --</option>
                    <option value="yes" {{ old('gst_applicable', isset($application->entityDetails) && $application->entityDetails->gst_applicable === 'yes' ? 'selected' : '') }}>Yes</option>
                    <option value="no" {{ old('gst_applicable', isset($application->entityDetails) && $application->entityDetails->gst_applicable === 'no' ? 'selected' : '') }}>No</option>
                </select>
            </div>
        </div>
    </div>

    <div id="gst_fields" style="display: {{ old('gst_applicable', ($application->entityDetails->gst_applicable ?? ($gstDoc ? 'yes' : 'no'))) === 'yes' ? 'block' : 'none' }};">
        <div class="row g-2">
            <div class="col-12 col-md-6">
                <div class="form-group mb-2">
                    <label for="gst_number" class="form-label small">GST Number *</label>
                    <input type="text" class="form-control form-control-sm" id="gst_number" name="gst_number"
                        value="{{ old('gst_number', $gstDoc['details']['gst_number'] ?? ($application->entityDetails->gst_number ?? '')) }}">
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group mb-2">
                    <label for="gst_validity" class="form-label small">GST Validity Date *</label>
                    <input type="date" class="form-control form-control-sm" id="gst_validity" name="gst_validity"
                        min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                        value="{{ old('gst_validity', $gstDoc['details']['gst_validity'] ?? ($application->entityDetails->additional_data['gst_validity'] ?? '')) }}">
                </div>
            </div>
            <div class="col-12">
                <div class="form-group mb-2">
                    <label for="gst_file" class="form-label small">Upload GST Document *</label>
                    <div class="input-group input-group-sm">
                        <input type="file" class="form-control d-none" id="gst_file" name="gst_file" accept=".pdf,.jpg,.jpeg,.png">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="gst_upload_btn">Upload & Extract GST</button>
                        <div id="gst_file_name_display" class="input-group-text small text-muted {{ $gstDoc ? '' : 'd-none' }}">
                            @if($gstDoc)
                            <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="{{ asset('storage/' . $gstDoc['path']) }}">View Document</a>
                            <input type="hidden" name="existing_gst_file" value="{{ $gstDoc['path'] }}">
                            @else
                            No file chosen.
                            @endif
                        </div>
                    </div>
                    <div id="gst_feedback" class="small text-muted mt-1"></div> {{-- For OCR feedback messages --}}
                </div>
            </div>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-12 col-md-6">
            <div class="form-group mb-2">
                <label for="seed_license_file" class="form-label small">Upload Seed License Document *</label>
                <div class="input-group input-group-sm">
                    <input type="file" class="form-control d-none" id="seed_license_file" name="seed_license_file" accept=".pdf,.jpg,.jpeg,.png">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="seed_license_upload_btn">Upload & Extract License</button>
                    <div id="seed_license_file_name_display" class="input-group-text small text-muted {{ $seedLicenseDoc ? '' : 'd-none' }}">
                        @if($seedLicenseDoc)
                        <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="{{ asset('storage/' . $seedLicenseDoc['path']) }}">View Document</a>
                        <input type="hidden" name="existing_seed_license_file" value="{{ $seedLicenseDoc['path'] }}">
                        @else
                        No file chosen.
                        @endif
                    </div>
                </div>
                <label for="seed_license" class="form-label small mt-2">Seed License Number *</label>
                <input type="text" class="form-control form-control-sm" id="seed_license" name="seed_license"
                    value="{{ old('seed_license', $seedLicenseDoc['details']['seed_license_number'] ?? ($application->entityDetails->seed_license ?? '')) }}" required>
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" id="seed_license_verified" name="seed_license_verified"
                        {{ old('seed_license_verified', $seedLicenseDoc['verified'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label small" for="seed_license_verified">
                        I confirm the Seed License number matches the uploaded document
                    </label>
                </div>
                <div id="seed_license_feedback" class="small text-muted mt-1"></div> {{-- For OCR feedback messages --}}
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="form-group mb-2">
                <label for="seed_license_validity" class="form-label small">Seed License Validity Date *</label>
                <input type="date" class="form-control form-control-sm" id="seed_license_validity" name="seed_license_validity"
                min="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                value="{{ old('seed_license_validity', $seedLicenseDoc['details']['seed_license_validity'] ?? ($application->entityDetails->additional_data['seed_license_validity'] ?? '')) }}" required>
            </div>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-12 col-md-6">
            <div class="form-group mb-2">
                <label for="tan_number" class="form-label small">TAN Number (if any)</label>
                <input type="text" class="form-control form-control-sm" id="tan_number" name="tan_number"
                    value="{{ old('tan_number', isset($application->entityDetails->additional_data['tan_number']) ? $application->entityDetails->additional_data['tan_number'] : '') }}">
            </div>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-12">
            <h4 class="fs-6">Bank Details</h4>
        </div>
    </div>
    <div class="row g-2">
        <div class="col-12">
            <div class="form-group mb-2">
                <label for="bank_file" class="form-label small">Upload Bank Document (Passbook/Cancelled Cheque) *</label>
                <div class="input-group input-group-sm">
                    <input type="file" class="form-control d-none" id="bank_file" name="bank_file" accept=".pdf,.jpg,.jpeg,.png">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="bank_upload_btn">Upload & Extract Bank Details</button>
                    <div id="bank_file_name_display" class="input-group-text small text-muted {{ $bankDoc ? '' : 'd-none' }}">
                        @if($bankDoc)
                        <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="{{ asset('storage/' . $bankDoc['path']) }}">View Document</a>
                        <input type="hidden" name="existing_bank_file" value="{{ $bankDoc['path'] }}">
                        @else
                        No file chosen.
                        @endif
                    </div>
                </div>
                <div id="bank_feedback" class="small text-muted mt-1"></div> {{-- For OCR feedback messages --}}
            </div>
        </div>
    </div>
    <div class="row g-2">
        <div class="col-12 col-md-3">
            <div class="form-group mb-2">
                <label for="bank_name" class="form-label small">Name of the Bank *</label>
                <input type="text" class="form-control form-control-sm" id="bank_name" name="bank_name"
                    value="{{ old('bank_name', $bankDoc['details']['bank_name'] ?? ($application->entityDetails->additional_data['bank_details']['bank_name'] ?? '')) }}" required>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="form-group mb-2">
                <label for="account_holder" class="form-label small">Name of Bank Account Holder *</label>
                <input type="text" class="form-control form-control-sm" id="account_holder" name="account_holder"
                    value="{{ old('account_holder', $bankDoc['details']['account_holder'] ?? ($application->entityDetails->additional_data['bank_details']['account_holder'] ?? '')) }}" required>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="form-group mb-2">
                <label for="account_number" class="form-label small">Account Number *</label>
                <input type="text" class="form-control form-control-sm" id="account_number" name="account_number"
                    value="{{ old('account_number', $bankDoc['details']['account_number'] ?? ($application->entityDetails->additional_data['bank_details']['account_number'] ?? '')) }}" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="form-group mb-2">
                <label for="ifsc_code" class="form-label small">IFSC Code of Bank *</label>
                <input type="text" class="form-control form-control-sm" id="ifsc_code" name="ifsc_code"
                    value="{{ old('ifsc_code', $bankDoc['details']['ifsc_code'] ?? ($application->entityDetails->additional_data['bank_details']['ifsc_code'] ?? '')) }}" required>
            </div>
        </div>
    </div>

    <div class="card mb-2">
        <div class="card-header bg-light p-2">
            <h6 class="mb-0 fs-6">Authorized Persons Details</h6>
        </div>
        <div class="card-body p-2">
            <div class="mb-2">
                <label for="has_authorized_persons" class="form-label small">Do you have authorized persons?</label>
                <select id="has_authorized_persons" name="has_authorized_persons" class="form-select form-select-sm">
                    <option value="no" {{ old('has_authorized_persons', isset($application->entityDetails->additional_data['authorized_persons']) && empty($application->entityDetails->additional_data['authorized_persons']) ? 'no' : (isset($application->entityDetails->additional_data['authorized_persons']) ? 'yes' : 'no')) == 'no' ? 'selected' : '' }}>No</option>
                    <option value="yes" {{ old('has_authorized_persons', isset($application->entityDetails->additional_data['authorized_persons']) && !empty($application->entityDetails->additional_data['authorized_persons']) ? 'yes' : (isset($application->entityDetails->additional_data['authorized_persons']) ? 'yes' : 'no')) == 'yes' ? 'selected' : '' }}>Yes</option>
                </select>
            </div>
            <div id="authorized_persons_section" class="{{ old('has_authorized_persons', isset($application->entityDetails->additional_data['authorized_persons']) && !empty($application->entityDetails->additional_data['authorized_persons']) ? 'yes' : 'no') == 'yes' ? '' : 'd-none' }}">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover compact-table" id="authorized_persons_table">
                        <thead>
                            <tr>
                                <th class="small">Name *</th>
                                <th class="small">Contact Number *</th>
                                <th class="small">Email Address</th>
                                <th class="small">Full Address *</th>
                                <th class="small">Relation *</th>
                                <th class="small">Letter of Authorisation *</th>
                                <th class="small">Aadhar *</th>
                                <th class="small">Action</th>
                            </tr>
                        </thead>
                        <tbody id="authorized_persons_container">
                            @php
                            $authPersons = old('auth_person_name', $application->entityDetails->additional_data['authorized_persons'] ?? []);
                            if (empty($authPersons) && old('has_authorized_persons', isset($application->entityDetails->additional_data['authorized_persons']) ? 'yes' : 'no') == 'yes') {
                                $authPersons[] = ['name' => '', 'contact' => '', 'email' => '', 'address' => '', 'relation' => '', 'letter' => '', 'aadhar' => ''];
                            } elseif(empty($authPersons) && old('has_authorized_persons', isset($application->entityDetails->additional_data['authorized_persons']) ? 'yes' : 'no') == 'no') {
                                $authPersons = []; // No persons if "No" is selected
                            }
                            @endphp
                            @foreach($authPersons as $index => $person)
                            <tr class="authorized-person-entry">
                                <td data-label="Name">
                                    <input type="text" class="form-control form-control-sm" name="auth_person_name[]" value="{{ old("auth_person_name.$index", $person['name'] ?? '') }}" data-required-if-yes>
                                    <div class="invalid-feedback"></div>
                                </td>
                                <td data-label="Contact Number">
                                    <input type="tel" class="form-control form-control-sm" name="auth_person_contact[]" value="{{ old("auth_person_contact.$index", $person['contact'] ?? '') }}" data-required-if-yes oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    <div class="invalid-feedback"></div>
                                </td>
                                <td data-label="Email Address">
                                    <input type="email" class="form-control form-control-sm" name="auth_person_email[]" value="{{ old("auth_person_email.$index", $person['email'] ?? '') }}">
                                    <div class="invalid-feedback"></div>
                                </td>
                                <td data-label="Full Address">
                                    <textarea class="form-control form-control-sm" name="auth_person_address[]" rows="2" data-required-if-yes>{{ old("auth_person_address.$index", $person['address'] ?? '') }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </td>
                                <td data-label="Relation">
                                    <input type="text" class="form-control form-control-sm" name="auth_person_relation[]" value="{{ old("auth_person_relation.$index", $person['relation'] ?? '') }}" data-required-if-yes>
                                    <div class="invalid-feedback"></div>
                                </td>
                                <td data-label="Letter of Authorisation">
                                    <div class="input-group input-group-sm">
                                        <input type="file" class="form-control d-none" id="auth_person_letter_{{ $index }}" name="auth_person_letter[]" accept=".pdf,.doc,.docx" data-required-if-yes>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('auth_person_letter_{{ $index }}').click()">Upload</button>
                                        <div id="auth_person_letter_{{ $index }}_display" class="input-group-text small text-muted {{ isset($person['letter']) && $person['letter'] ? '' : 'd-none' }}">
                                            @if(isset($person['letter']) && $person['letter'])
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="{{ asset('storage/' . $person['letter']) }}">View Letter</a>
                                            <input type="hidden" name="existing_auth_person_letter[]" value="{{ $person['letter'] }}">
                                            @else
                                            No file chosen.
                                            @endif
                                        </div>
                                    </div>
                                    <div id="auth_person_letter_{{ $index }}_feedback" class="small text-muted mt-1"></div> {{-- For OCR feedback --}}
                                </td>
                                <td data-label="Aadhar">
                                    <div class="input-group input-group-sm">
                                        <input type="file" class="form-control d-none" id="auth_person_aadhar_{{ $index }}" name="auth_person_aadhar[]" accept=".pdf,.jpg,.jpeg,.png" data-required-if-yes>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('auth_person_aadhar_{{ $index }}').click()">Upload</button>
                                        <div id="auth_person_aadhar_{{ $index }}_display" class="input-group-text small text-muted {{ isset($person['aadhar']) && $person['aadhar'] ? '' : 'd-none' }}">
                                            @if(isset($person['aadhar']) && $person['aadhar'])
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="{{ asset('storage/' . $person['aadhar']) }}">View Aadhar</a>
                                            <input type="hidden" name="existing_auth_person_aadhar[]" value="{{ $person['aadhar'] }}">
                                            @else
                                            No file chosen.
                                            @endif
                                        </div>
                                    </div>
                                    <div id="auth_person_aadhar_{{ $index }}_feedback" class="small text-muted mt-1"></div> {{-- For OCR feedback --}}
                                </td>
                                <td data-label="Action">
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeAuthorizedPerson(this)">-</button>
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

@push('scripts')
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
            // Disable all fields within hidden sections and remove required attribute
            el.querySelectorAll('input, textarea, select').forEach(input => {
                input.disabled = true;
                input.removeAttribute('required'); // Always remove required when hidden
            });
        });

        // Show the relevant field based on entity type and re-add required
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
            // Enable fields within the visible section and add required if originally marked as such
            targetField.querySelectorAll('input, textarea, select').forEach(input => {
                input.disabled = false;
                // Add `data-required` attribute in HTML for fields that are always required when visible
                if (input.hasAttribute('data-required')) {
                    input.setAttribute('required', '');
                }
            });
        }
        clearInapplicableFields(entityType); // Clear data from hidden fields
    }


    function toggleGSTFields() {
        const gstApplicable = document.getElementById('gst_applicable').value;
        const gstFields = document.getElementById('gst_fields');
        const gstNumberInput = document.getElementById('gst_number');
        const gstValidityInput = document.getElementById('gst_validity');
        const gstFileInput = document.getElementById('gst_file');
        const gstFileNameDisplay = document.getElementById('gst_file_name_display'); // Corrected ID

        if (gstApplicable === 'yes') {
            gstFields.style.display = 'block';
            gstNumberInput.disabled = false;
            gstValidityInput.disabled = false;
            gstFileInput.disabled = false;

            gstNumberInput.required = true;
            gstValidityInput.required = true;
            gstFileInput.required = true; // Make file input required
        } else {
            gstFields.style.display = 'none';
            gstNumberInput.disabled = true;
            gstValidityInput.disabled = true;
            gstFileInput.disabled = true;

            gstNumberInput.required = false;
            gstValidityInput.required = false;
            gstFileInput.required = false; // Make file input not required

            // Clear values only if the section is hidden
            gstNumberInput.value = '';
            gstValidityInput.value = '';
            gstFileInput.value = ''; // Clear file input
            gstFileNameDisplay.classList.add('d-none'); // Hide file name display
            gstFileNameDisplay.innerHTML = 'No file chosen.'; // Reset text
            const existingGstFile = document.querySelector('input[name="existing_gst_file"]');
            if(existingGstFile) existingGstFile.value = ''; // Clear hidden existing file path
        }
    }


    function clearInapplicableFields(entityType) {
        const containers = {
            'individual_person': ['individual_name', 'individual_dob', 'individual_father_name','individual_age'],
            'sole_proprietorship': ['proprietor_name', 'proprietor_dob', 'proprietor_father_name','proprietor_age'],
            'partnership': ['partners_container', 'signatories_container'], // Added signatories
            'llp': ['llp_partners_container', 'llpin_number', 'llp_incorporation_date'],
            'private_company': ['directors_container', 'cin_number', 'incorporation_date'],
            'public_company': ['directors_container', 'cin_number', 'incorporation_date'],
            'cooperative_society': ['committee_container', 'cooperative_reg_number', 'cooperative_reg_date'],
            'trust': ['trustees_container', 'trust_reg_number', 'trust_reg_date']
        };

        // Clear scalar fields and dynamic containers for inactive entity types
        Object.keys(containers).forEach(type => {
            if (type !== entityType) {
                containers[type].forEach(field => {
                    const inputElement = document.getElementById(field) || document.querySelector(`[name="${field}"]`);
                    if (inputElement && (inputElement.tagName === 'INPUT' || inputElement.tagName === 'TEXTAREA' || inputElement.tagName === 'SELECT')) {
                        inputElement.value = '';
                        inputElement.classList.remove('is-invalid'); // Clear validation
                        const feedbackDiv = inputElement.nextElementSibling;
                        if(feedbackDiv && feedbackDiv.classList.contains('invalid-feedback')) {
                            feedbackDiv.textContent = '';
                        }
                    } else { // Handle dynamic containers
                        const container = document.getElementById(field);
                        if (container && (field.includes('_container') || field === 'signatories_container')) { // Explicitly check for container IDs
                            container.innerHTML = ''; // Clear all dynamic entries
                        }
                    }
                });
            }
        });

        // Initialize default entry if dynamic container becomes empty and active
        if (entityType === 'partnership' && document.getElementById('partners_container').children.length === 0) {
            addPartner();
        }
        if (entityType === 'partnership' && document.getElementById('signatories_container').children.length === 0) {
            addSignatory();
        }
        else if (entityType === 'llp' && document.getElementById('llp_partners_container').children.length === 0) {
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
        newEntry.className = 'partner-entry mb-2 border-bottom pb-2'; // Adjusted mb/pb for consistency
        newEntry.innerHTML = `
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <div class="form-group mb-2">
                        <label class="form-label small">Partner Name *</label>
                        <input type="text" class="form-control form-control-sm" name="partner_name[]" required>
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
                        <input type="tel" class="form-control form-control-sm" name="partner_contact[]" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end">
                    <div class="form-group mb-2 w-100">
                        <button type="button" class="btn btn-sm btn-danger w-100" onclick="removePartner(this)">-</button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(newEntry);
    }

    function removePartner(button) {
        const entries = document.querySelectorAll('#partners_container .partner-entry');
        if (entries.length > 1) {
            button.closest('.partner-entry').remove();
        } else {
            alert('At least one partner is required for Partnership entity type.');
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
                        <input type="tel" class="form-control form-control-sm" name="signatory_contact[]" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end">
                    <div class="form-group mb-2 w-100">
                        <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeSignatory(this)">-</button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(newEntry);
    }

    function removeSignatory(button) {
        const entries = document.querySelectorAll('#signatories_container .signatory-entry');
        if (entries.length > 1) {
            button.closest('.signatory-entry').remove();
        } else {
            // For signatories, if it's the last one, clear fields instead of removing
            const entry = button.closest('.signatory-entry');
            entry.querySelectorAll('input').forEach(input => {
                input.value = '';
                input.classList.remove('is-invalid');
                const feedbackDiv = input.nextElementSibling;
                if(feedbackDiv && feedbackDiv.classList.contains('invalid-feedback')) feedbackDiv.textContent = '';
            });
            alert('At least one signatory entry is usually required, but fields cleared.');
        }
    }

    function addLLPPartner() {
        const container = document.getElementById('llp_partners_container');
        const newEntry = document.createElement('div');
        newEntry.className = 'llp-partner-entry mb-2 border-bottom pb-2'; // Adjusted mb/pb for consistency
        newEntry.innerHTML = `
            <div class="row g-2">
                <div class="col-12 col-md-3">
                    <div class="form-group mb-2">
                        <label class="form-label small">Partner Name *</label>
                        <input type="text" class="form-control form-control-sm" name="llp_partner_name[]" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-12 col-md-2">
                    <div class="form-group mb-2">
                        <label class="form-label small">DPIN Number *</label>
                        <input type="text" class="form-control form-control-sm" name="llp_partner_dpin[]" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="form-group mb-2">
                        <label class="form-label small">Contact Number *</label>
                        <input type="tel" class="form-control form-control-sm" name="llp_partner_contact[]" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="form-group mb-2">
                        <label class="form-label small">Full Address *</label>
                        <input type="text" class="form-control form-control-sm" name="llp_partner_address[]" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-12 col-md-1 d-flex align-items-end">
                    <div class="form-group mb-2 w-100">
                        <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeLLPPartner(this)">-</button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(newEntry);
    }

    function removeLLPPartner(button) {
        const entries = document.querySelectorAll('#llp_partners_container .llp-partner-entry');
        if (entries.length > 1) {
            button.closest('.llp-partner-entry').remove();
        } else {
            alert('At least one designated partner is required for LLP entity type.');
        }
    }

    function addDirector() {
        const container = document.getElementById('directors_container');
        const newEntry = document.createElement('div');
        newEntry.className = 'director-entry mb-2 border-bottom pb-2'; // Adjusted mb/pb
        newEntry.innerHTML = `
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <div class="form-group mb-2">
                        <label class="form-label small">Director Name *</label>
                        <input type="text" class="form-control form-control-sm" name="director_name[]" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-group mb-2">
                        <label class="form-label small">DIN Number *</label>
                        <input type="text" class="form-control form-control-sm" name="director_din[]" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-group mb-2">
                        <label class="form-label small">Contact Number *</label>
                        <input type="tel" class="form-control form-control-sm" name="director_contact[]" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="form-group mb-2">
                <label class="form-label small">Full Address *</label>
                <textarea class="form-control form-control-sm" name="director_address[]" rows="2" required></textarea>
                <div class="invalid-feedback"></div>
            </div>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeDirector(this)">-</button>
        `;
        container.appendChild(newEntry);
    }

    function removeDirector(button) {
        const entries = document.querySelectorAll('#directors_container .director-entry');
        if (entries.length > 1) {
            button.closest('.director-entry').remove();
        } else {
            alert('At least one director is required for Company entity type.');
        }
    }

    function addCommitteeMember() {
        const container = document.getElementById('committee_container');
        const newEntry = document.createElement('div');
        newEntry.className = 'committee-entry mb-2 border-bottom pb-2'; // Adjusted mb/pb
        newEntry.innerHTML = `
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <div class="form-group mb-2">
                        <label class="form-label small">Member Name *</label>
                        <input type="text" class="form-control form-control-sm" name="committee_name[]" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-group mb-2">
                        <label class="form-label small">Designation *</label>
                        <input type="text" class="form-control form-control-sm" name="committee_designation[]" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-group mb-2">
                        <label class="form-label small">Contact Number *</label>
                        <input type="tel" class="form-control form-control-sm" name="committee_contact[]" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="form-group mb-2">
                <label class="form-label small">Full Address *</label>
                <textarea class="form-control form-control-sm" name="committee_address[]" rows="2" required></textarea>
                <div class="invalid-feedback"></div>
            </div>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeCommitteeMember(this)">-</button>
        `;
        container.appendChild(newEntry);
    }

    function removeCommitteeMember(button) {
        const entries = document.querySelectorAll('#committee_container .committee-entry');
        if (entries.length > 1) {
            button.closest('.committee-entry').remove();
        } else {
            alert('At least one committee member is required for Cooperative Society entity type.');
        }
    }

    function addTrustee() {
        const container = document.getElementById('trustees_container');
        const newEntry = document.createElement('div');
        newEntry.className = 'trustee-entry mb-2 border-bottom pb-2'; // Adjusted mb/pb
        newEntry.innerHTML = `
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <div class="form-group mb-2">
                        <label class="form-label small">Trustee Name *</label>
                        <input type="text" class="form-control form-control-sm" name="trustee_name[]" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-group mb-2">
                        <label class="form-label small">Designation *</label>
                        <input type="text" class="form-control form-control-sm" name="trustee_designation[]" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-group mb-2">
                        <label class="form-label small">Contact Number *</label>
                        <input type="tel" class="form-control form-control-sm" name="trustee_contact[]" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
            <div class="form-group mb-2">
                <label class="form-label small">Full Address *</label>
                <textarea class="form-control form-control-sm" name="trustee_address[]" rows="2" required></textarea>
                <div class="invalid-feedback"></div>
            </div>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeTrustee(this)">-</button>
        `;
        container.appendChild(newEntry);
    }

    function removeTrustee(button) {
        const entries = document.querySelectorAll('#trustees_container .trustee-entry');
        if (entries.length > 1) {
            button.closest('.trustee-entry').remove();
        } else {
            alert('At least one trustee is required for Trust entity type.');
        }
    }

    function handleFileChange(fileInput, displayElementId, feedbackElementId, fileType, isAuthPerson = false) {
        const displayElement = document.getElementById(displayElementId);
        const feedbackElement = document.getElementById(feedbackElementId);

        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const fileName = file.name;
            const tempUrl = URL.createObjectURL(file);

            // Hide previous static "View Document" link and show new one
            displayElement.classList.remove('d-none');
            displayElement.innerHTML = `<a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="${tempUrl}">View ${fileName}</a>`;

            // Clear any old hidden input for existing files
            const existingFileInput = fileInput.closest('.form-group') ? fileInput.closest('.form-group').querySelector(`input[name^="existing_${fileType}"]`) : null;
            if (existingFileInput) existingFileInput.value = '';

            // Update feedback area with processing message
            feedbackElement.classList.remove('d-none');
            feedbackElement.innerHTML = `<span>Processing... <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span></span>`;

            // Process with OCR
            const processor = getProcessorFunction(fileType);
            if (processor) {
                processor(file, feedbackElement, fileInput) // Pass fileInput to processor if it needs to update associated inputs
                    .then(() => {
                        feedbackElement.innerHTML = `<span class="text-success">Extraction successful.</span>`;
                    })
                    .catch((error) => {
                        console.error(`Error processing ${fileType}:`, error);
                        feedbackElement.innerHTML = `<span class="text-danger">Extraction failed. Please manually enter.</span>`;
                        // Clear the associated input field if extraction fails to prevent stale data
                        const associatedInput = getAssociatedInputField(fileType, fileInput);
                        if (associatedInput) associatedInput.value = '';
                    });
            } else {
                feedbackElement.innerHTML = `<span class="text-info">No extraction logic for this document type.</span>`;
            }

        } else {
            // No file selected, revert display
            const existingFileValueInput = fileInput.closest('.form-group') ? fileInput.closest('.form-group').querySelector(`input[name^="existing_${fileType}"]`) : null;

            if (existingFileValueInput && existingFileValueInput.value) {
                // If there was an existing file and input is cleared, show original link
                displayElement.classList.remove('d-none');
                displayElement.innerHTML = `<a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="{{ asset('storage/' . ($panDoc['path'] ?? '')) }}">View Existing Document</a>`; // Needs correct path for other docs
                // This part needs to be dynamic based on which document it is.
                // It's better to NOT clear the existing_file hidden input on change, only on new upload success.
            } else {
                displayElement.classList.add('d-none');
                displayElement.innerHTML = `No file chosen.`;
            }
            feedbackElement.innerHTML = ''; // Clear feedback
            feedbackElement.classList.add('d-none');
        }
    }


    // Helper to get the correct OCR processing function
    function getProcessorFunction(fileType) {
        switch (fileType) {
            case 'pan_file': return processPANCard;
            case 'bank_file': return processBankDocument;
            case 'seed_license_file': return processSeedLicense;
            case 'gst_file': return processGSTDocument;
            // For authorized persons, OCR might be complex if you need to extract data into specific fields
            // For now, these are just file uploads
            // case 'auth_person_letter': return processAuthLetter;
            // case 'auth_person_aadhar': return processAuthAadhar;
            default: return null;
        }
    }

    // Helper to get the associated input field for OCR extraction
    function getAssociatedInputField(fileType, fileInput) {
        switch (fileType) {
            case 'pan_file': return document.getElementById('pan_number');
            case 'bank_file': return {
                bank_name: document.getElementById('bank_name'),
                account_holder: document.getElementById('account_holder'),
                account_number: document.getElementById('account_number'),
                ifsc_code: document.getElementById('ifsc_code')
            };
            case 'seed_license_file': return document.getElementById('seed_license');
            case 'gst_file': return {
                gst_number: document.getElementById('gst_number')
            };
            default: return null;
        }
    }


    const hasAuthorizedPersons = document.getElementById('has_authorized_persons');
    const authorizedPersonsSection = document.getElementById('authorized_persons_section');
    const authorizedPersonsContainer = document.getElementById('authorized_persons_container');

    function toggleAuthorizedPersonsSection() {
        const isEnabled = hasAuthorizedPersons.value === 'yes';
        authorizedPersonsSection.classList.toggle('d-none', !isEnabled);

        if (isEnabled && authorizedPersonsContainer.children.length === 0) {
            addAuthorizedPerson(); // Add a default row if enabling and no rows exist
        } else if (!isEnabled) {
            authorizedPersonsContainer.innerHTML = ''; // Clear all rows if disabling
        }

        // Set required/disabled for all inputs in this section based on toggle
        authorizedPersonsSection.querySelectorAll('input, textarea').forEach(input => {
            if (isEnabled) {
                // For 'yes', apply 'required' attribute to all initially required fields
                if (input.dataset.hasOwnProperty('requiredIfYes')) { // Use a data attribute for clarity
                     input.setAttribute('required', '');
                }
            } else {
                input.removeAttribute('required');
                input.value = ''; // Clear values when disabling
                input.classList.remove('is-invalid'); // Clear validation
                const feedbackDiv = input.nextElementSibling;
                if(feedbackDiv && feedbackDiv.classList.contains('invalid-feedback')) feedbackDiv.textContent = '';
                if(input.type === 'file') {
                    input.value = ''; // Clear file input
                }
            }
        });

        // Hide existing file links if section is disabled
        if (!isEnabled) {
            authorizedPersonsSection.querySelectorAll('[id$="_display"]').forEach(el => {
                el.classList.add('d-none');
                el.innerHTML = 'No file chosen.'; // Reset text
            });
            authorizedPersonsSection.querySelectorAll('input[type="hidden"][name^="existing_auth_person_"]').forEach(input => {
                input.value = ''; // Clear hidden existing file paths
            });
        }
    }


    function addAuthorizedPerson() {
        const container = document.getElementById('authorized_persons_container');
        const newRow = document.createElement('tr');
        const newIndex = container.children.length; // Get current number of rows to use as new index
        newRow.className = 'authorized-person-entry';
        newRow.innerHTML = `
        <td data-label="Name">
            <input type="text" class="form-control form-control-sm" name="auth_person_name[]" data-required-if-yes required>
            <div class="invalid-feedback"></div>
        </td>
        <td data-label="Contact Number">
            <input type="tel" class="form-control form-control-sm" name="auth_person_contact[]" data-required-if-yes required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            <div class="invalid-feedback"></div>
        </td>
        <td data-label="Email Address">
            <input type="email" class="form-control form-control-sm" name="auth_person_email[]">
            <div class="invalid-feedback"></div>
        </td>
        <td data-label="Full Address">
            <textarea class="form-control form-control-sm" name="auth_person_address[]" rows="2" data-required-if-yes required></textarea>
            <div class="invalid-feedback"></div>
        </td>
        <td data-label="Relation">
            <input type="text" class="form-control form-control-sm" name="auth_person_relation[]" data-required-if-yes required>
            <div class="invalid-feedback"></div>
        </td>
        <td data-label="Letter of Authorisation">
            <div class="input-group input-group-sm">
                <input type="file" class="form-control d-none" id="auth_person_letter_${newIndex}" name="auth_person_letter[]" accept=".pdf,.doc,.docx" data-required-if-yes required>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('auth_person_letter_${newIndex}').click()">Upload</button>
                <div id="auth_person_letter_${newIndex}_display" class="input-group-text small text-muted d-none">No file chosen.</div>
            </div>
            <div id="auth_person_letter_${newIndex}_feedback" class="small text-muted mt-1"></div>
        </td>
        <td data-label="Aadhar">
            <div class="input-group input-group-sm">
                <input type="file" class="form-control d-none" id="auth_person_aadhar_${newIndex}" name="auth_person_aadhar[]" accept=".pdf,.jpg,.jpeg,.png" data-required-if-yes required>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('auth_person_aadhar_${newIndex}').click()">Upload</button>
                <div id="auth_person_aadhar_${newIndex}_display" class="input-group-text small text-muted d-none">No file chosen.</div>
            </div>
            <div id="auth_person_aadhar_${newIndex}_feedback" class="small text-muted mt-1"></div>
        </td>
        <td data-label="Action">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeAuthorizedPerson(this)">-</button>
        </td>
        `;
        container.appendChild(newRow);

        // Attach event listeners for newly added file inputs
        newRow.querySelector(`#auth_person_letter_${newIndex}`).addEventListener('change', (e) =>
            handleFileChange(e.target, `auth_person_letter_${newIndex}_display`, `auth_person_letter_${newIndex}_feedback`, `auth_person_letter`, true));
        newRow.querySelector(`#auth_person_aadhar_${newIndex}`).addEventListener('change', (e) =>
            handleFileChange(e.target, `auth_person_aadhar_${newIndex}_display`, `auth_person_aadhar_${newIndex}_feedback`, `auth_person_aadhar`, true));

        // Manually trigger required check for newly added row if "has authorized persons" is 'yes'
        if (hasAuthorizedPersons.value === 'yes') {
             newRow.querySelectorAll('[data-required-if-yes]').forEach(input => input.setAttribute('required', ''));
        }
    }

    function removeAuthorizedPerson(button) {
        const entries = document.querySelectorAll('#authorized_persons_container .authorized-person-entry');
        if (entries.length > 1) {
            button.closest('.authorized-person-entry').remove();
        } else {
            // If it's the last row, instead of removing, uncheck "has authorized persons"
            // and clear all fields.
            alert('At least one authorized person entry is required if "Yes" is selected. Clearing fields instead.');
            hasAuthorizedPersons.value = 'no';
            toggleAuthorizedPersonsSection();
        }
    }

    // Initialize all upload handlers
    function initializeUploaders() {
        // PAN Card
        const panUploadBtn = document.getElementById('pan_upload_btn');
        const panFileInput = document.getElementById('pan_file');
        const panFileNameDisplay = document.getElementById('pan_file_name_display');
        const panFeedback = document.getElementById('pan_feedback');

        if (panUploadBtn && panFileInput && panFileNameDisplay && panFeedback) {
            panUploadBtn.addEventListener('click', () => panFileInput.click());
            panFileInput.addEventListener('change', (e) => handleFileChange(e.target, 'pan_file_name_display', 'pan_feedback', 'pan_file'));
        }

        // GST Document
        const gstUploadBtn = document.getElementById('gst_upload_btn');
        const gstFileInput = document.getElementById('gst_file');
        const gstFileNameDisplay = document.getElementById('gst_file_name_display');
        const gstFeedback = document.getElementById('gst_feedback');

        if (gstUploadBtn && gstFileInput && gstFileNameDisplay && gstFeedback) {
            gstUploadBtn.addEventListener('click', () => gstFileInput.click());
            gstFileInput.addEventListener('change', (e) => handleFileChange(e.target, 'gst_file_name_display', 'gst_feedback', 'gst_file'));
        }

        // Seed License
        const seedUploadBtn = document.getElementById('seed_license_upload_btn');
        const seedFileInput = document.getElementById('seed_license_file');
        const seedFileNameDisplay = document.getElementById('seed_license_file_name_display');
        const seedFeedback = document.getElementById('seed_license_feedback');

        if (seedUploadBtn && seedFileInput && seedFileNameDisplay && seedFeedback) {
            seedUploadBtn.addEventListener('click', () => seedFileInput.click());
            seedFileInput.addEventListener('change', (e) => handleFileChange(e.target, 'seed_license_file_name_display', 'seed_license_feedback', 'seed_license_file'));
        }

        // Bank Document
        const bankUploadBtn = document.getElementById('bank_upload_btn');
        const bankFileInput = document.getElementById('bank_file');
        const bankFileNameDisplay = document.getElementById('bank_file_name_display');
        const bankFeedback = document.getElementById('bank_feedback');
        if (bankUploadBtn && bankFileInput && bankFileNameDisplay && bankFeedback) {
            bankUploadBtn.addEventListener('click', () => bankFileInput.click());
            bankFileInput.addEventListener('change', (e) => handleFileChange(e.target, 'bank_file_name_display', 'bank_feedback', 'bank_file'));
        }

        // Initialize listeners for dynamically added authorized person file inputs (on initial load)
        document.querySelectorAll('.authorized-person-entry').forEach((row, index) => {
            const letterInput = row.querySelector(`input[name="auth_person_letter[]"]`);
            const aadharInput = row.querySelector(`input[name="auth_person_aadhar[]"]`);
            if (letterInput) {
                letterInput.addEventListener('change', (e) =>
                    handleFileChange(e.target, `auth_person_letter_${index}_display`, `auth_person_letter_${index}_feedback`, `auth_person_letter`, true));
            }
            if (aadharInput) {
                aadharInput.addEventListener('change', (e) =>
                    handleFileChange(e.target, `auth_person_aadhar_${index}_display`, `auth_person_aadhar_${index}_feedback`, `auth_person_aadhar`, true));
            }
        });
    }

    // Date of Birth validation functions
    function calculateAge(dobString) {
        const dob = new Date(dobString);
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const m = today.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
            age--;
        }
        return age;
    }

    function validateAndSetAge(dobInput) {
        const ageInput = dobInput.closest('.form-group').nextElementSibling?.querySelector('.age-display');
        const errorDiv = dobInput.closest('.form-group').querySelector('.invalid-feedback.dob-error');

        dobInput.classList.remove('is-invalid');
        if (errorDiv) errorDiv.textContent = ''; // Clear existing error message

        if (!dobInput.value) {
            if (ageInput) ageInput.value = '';
            return true; // No DOB entered, no age validation needed
        }

        const age = calculateAge(dobInput.value);

        if (ageInput) {
            ageInput.value = age >= 0 ? age : ''; // Avoid negative age
        }

        if (age < 18) {
            dobInput.classList.add('is-invalid');
            if (errorDiv) errorDiv.textContent = 'Must be at least 18 years old.';
            return false;
        }
        return true;
    }

    // Client-side form submission validation
    document.querySelector('form').addEventListener('submit', function(e) {
        let formIsValid = true;

        // Validate DOB inputs
        document.querySelectorAll('.dob-input').forEach(dobInput => {
            if (!validateAndSetAge(dobInput)) {
                formIsValid = false;
            }
        });

        // Validate authorized persons section if enabled
        const hasAuthPersonsSelect = document.getElementById('has_authorized_persons');
        if (hasAuthPersonsSelect && hasAuthPersonsSelect.value === 'yes') {
            const authPersonEntries = document.querySelectorAll('#authorized_persons_container .authorized-person-entry');
            if (authPersonEntries.length === 0) {
                 alert('You indicated having authorized persons, but no entries were added. Please add at least one entry or select "No".');
                 formIsValid = false;
            } else {
                authPersonEntries.forEach(entry => {
                    entry.querySelectorAll('[data-required-if-yes]').forEach(input => {
                        input.classList.remove('is-invalid');
                        const feedbackDiv = input.nextElementSibling;
                        if(feedbackDiv && feedbackDiv.classList.contains('invalid-feedback')) feedbackDiv.textContent = '';

                        // Check for empty required text/number inputs
                        if (input.type !== 'file' && input.hasAttribute('required') && input.value.trim() === '') {
                            input.classList.add('is-invalid');
                            if(feedbackDiv) feedbackDiv.textContent = 'This field is required.';
                            formIsValid = false;
                        }
                        // Check for required file inputs where no file is selected and no existing file exists
                         if (input.type === 'file' && input.hasAttribute('required') && (!input.files.length && !input.closest('td').querySelector('input[name^="existing_"][value]'))) {
                            input.classList.add('is-invalid');
                            // Find the appropriate feedback div for file inputs (might be a sibling, not direct next)
                            const fileFeedbackDiv = input.closest('.input-group').nextElementSibling;
                            if(fileFeedbackDiv && fileFeedbackDiv.id.includes('_feedback')) {
                                fileFeedbackDiv.textContent = 'Document is required.';
                            } else {
                                const defaultFeedback = input.closest('td').querySelector('.invalid-feedback');
                                if(defaultFeedback) defaultFeedback.textContent = 'Document is required.';
                            }
                            formIsValid = false;
                        }
                    });
                });
            }
        }


        if (!formIsValid) {
            e.preventDefault(); // Prevent form submission
            // Scroll to the first invalid element if possible
            const firstInvalid = document.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus();
            }
        }
    });


    // Initial setup when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        showRelevantFields();
        toggleGSTFields();
        toggleAuthorizedPersonsSection(); // Ensure correct initial state for authorized persons

        // Event listeners for main type changes
        document.getElementById('entity_type').addEventListener('change', showRelevantFields);
        document.getElementById('gst_applicable').addEventListener('change', toggleGSTFields);
        document.getElementById('has_authorized_persons').addEventListener('change', toggleAuthorizedPersonsSection);

        // State/District cascade (ensure this is part of the DOMContentLoaded)
        $('#state_id').on('change', function() {
            const stateId = $(this).val();
            $('#district_id').html('<option value="">Loading...</option>');
            if (stateId) {
                $.ajax({
                    url: '/get-districts/' + stateId, // Ensure this route is defined
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
                        console.error('AJAX Error fetching districts:', xhr.responseText);
                        $('#district_id').html('<option value="">Error loading districts</option>');
                    }
                });
            } else {
                $('#district_id').html('<option value="">Select District</option>');
            }
        }).trigger('change'); // Trigger on load to populate districts if state is pre-selected

        // Initial DOB validation for existing data
        document.querySelectorAll('.dob-input').forEach(dobInput => {
            validateAndSetAge(dobInput);
            dobInput.addEventListener('change', () => validateAndSetAge(dobInput));
        });

        // Initialize all OCR uploaders after DOM is ready
        initializeUploaders();
    });

    // Helper function to safely set field values
    function setFieldValue(fieldId, value) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.value = (value || '').toString().trim();
            // Trigger change event to update any dependent logic (like age calculation for DOB)
            const event = new Event('change');
            field.dispatchEvent(event);
            return true;
        }
        console.warn(`Field not found: ${fieldId}`);
        return false;
    }

    // OCR related functions (keep them in a separate script block or file if preferred)
    // Ensure Tesseract.js is loaded before these functions are called
    // <script src='https://unpkg.com/tesseract.js@2.1.0/dist/tesseract.min.js'></script>

    // Image preprocessing for better OCR
    async function preprocessImage(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');

                    // Scale down image if too large to prevent canvas limitations/performance issues
                    const MAX_DIM = 2000; // Max dimension for width or height
                    let width = img.width;
                    let height = img.height;

                    if (width > height) {
                        if (width > MAX_DIM) {
                            height *= MAX_DIM / width;
                            width = MAX_DIM;
                        }
                    } else {
                        if (height > MAX_DIM) {
                            width *= MAX_DIM / height;
                            height = MAX_DIM;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);

                    // Apply basic grayscale and contrast (can be more advanced)
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    const data = imageData.data;
                    for (let i = 0; i < data.length; i += 4) {
                        const avg = (data[i] + data[i + 1] + data[i + 2]) / 3;
                        data[i] = data[i + 1] = data[i + 2] = avg < 128 ? 0 : 255; // Simple black/white threshold
                    }
                    ctx.putImageData(imageData, 0, 0);

                    canvas.toBlob((blob) => {
                        resolve(blob); // Resolve with a Blob, which Tesseract can handle
                    }, 'image/jpeg', 0.9); // Quality 0.9
                };
                img.onerror = reject;
                img.src = e.target.result;
            };
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }


    // OCR text extraction with error handling
    async function extractTextFromImage(file) {
        try {
            const { data: { text } } = await Tesseract.recognize(file, 'eng', {
                // Tesseract parameters can be tuned for better results
                // Refer to Tesseract.js documentation for more options
                // For example, `tessedit_pageseg_mode: '6'` for a single uniform block of text.
                // `tessedit_ocr_engine_mode: '1'` for LSTM OCR engine.
                logger: m => console.log(m) // Optional: log Tesseract progress
            });
            return text
                .replace(/\s+/g, ' ') // Replace multiple spaces with single
                .replace(/[|\\]/g, '') // Remove common OCR errors for pipe/backslash
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
                let value = match[1].trim();
                // Clean common OCR errors in numbers/codes (e.g., O for 0, I for 1)
                value = value.replace(/O/g, '0').replace(/I/g, '1').replace(/l/g, '1');

                if (!validationRegex || validationRegex.test(value)) {
                    return value;
                }
            }
        }
        return null;
    }

    // Bank document processor
    async function processBankDocument(file, feedbackElement) { // Removed fileNameDisplayElement from params
        try {
            feedbackElement.innerHTML = '<span>Processing bank document... <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span></span>';

            const processedImageBlob = await preprocessImage(file);
            const extractedText = await extractTextFromImage(processedImageBlob);
            console.log('Extracted Bank Text:', extractedText);

            const extractedData = {
                bankName: extractField(extractedText, [
                    /Bank\s*Name[:\s]*([^\n\r]+)/i,
                    /([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*\s+Bank)/i,
                    /(State Bank of India|SBI|HDFC Bank|ICICI Bank|Axis Bank|Kotak Mahindra Bank)/i
                ]),
                accountHolder: extractField(extractedText, [
                    /Name\s*(?:of)?\s*(?:Account)?\s*Holder[:\s]*(?:Mr\.?|Mrs\.?|Ms\.?)?\s*([^\n\r]+)/i,
                    /Account\s*Holder\s*[:\s]*([^\n\r]+)/i,
                    /(?:Mr|Mrs|Ms)\.?\s*([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)/i
                ]),
                accountNumber: extractField(extractedText, [
                    /Account\s*No\.?\s*[:\s]*(\d{9,18})/i,
                    /A\/C\s*No\.?\s*[:\s]*(\d{9,18})/i,
                    /(\b\d{10,18}\b)/
                ], /^\d{10,18}$/),
                ifscCode: extractField(extractedText, [
                    /IFSC\s*Code[:\s]*([A-Z]{4}0[A-Z0-9]{6})/i,
                    /IFSC\s*([A-Z]{4}0[A-Z0-9]{6})/i,
                    /([A-Z]{4}0[A-Z0-9]{6})/
                ], /^[A-Z]{4}0[A-Z0-9]{6}$/)
            };

            setFieldValue('bank_name', extractedData.bankName);
            setFieldValue('account_holder', extractedData.accountHolder);
            setFieldValue('account_number', extractedData.accountNumber);
            setFieldValue('ifsc_code', extractedData.ifscCode);

            feedbackElement.innerHTML = `<span class="text-success">Bank details extracted.</span>`;
            return extractedData;
        } catch (error) {
            console.error('Bank processing error:', error);
            feedbackElement.innerHTML = '<span class="text-danger">Error extracting bank details.</span>';
            throw error;
        }
    }

    // PAN Card processor
    async function processPANCard(file, feedbackElement) { // Removed fileNameDisplayElement
        try {
            feedbackElement.innerHTML = '<span>Processing PAN card... <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span></span>';

            const processedImageBlob = await preprocessImage(file);
            const extractedText = await extractTextFromImage(processedImageBlob);
            console.log('Extracted PAN Text:', extractedText);

            const panNumber = extractField(extractedText, [
                /PAN\s*[:\s]*([A-Z]{5}\d{4}[A-Z])/i,
                /Permanent\s*Account\s*Number\s*[:\s]*([A-Z]{5}\d{4}[A-Z])/i,
                /([A-Z]{5}\d{4}[A-Z])/
            ], /^[A-Z]{5}\d{4}[A-Z]$/);

            if (panNumber) {
                setFieldValue('pan_number', panNumber);
                document.getElementById('pan_verified').checked = true;
                feedbackElement.innerHTML = `<span class="text-success">PAN extracted: ${panNumber}</span>`;
                return panNumber;
            } else {
                throw new Error('PAN number not found');
            }
        } catch (error) {
            console.error('PAN processing error:', error);
            feedbackElement.innerHTML = '<span class="text-danger">Error extracting PAN.</span>';
            throw error;
        }
    }

    // Seed License processor
    async function processSeedLicense(file, feedbackElement) { // Removed fileNameDisplayElement
        try {
            feedbackElement.innerHTML = '<span>Processing seed license... <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span></span>';

            const processedImageBlob = await preprocessImage(file);
            const extractedText = await extractTextFromImage(processedImageBlob);
            console.log('Extracted Seed License Text:', extractedText);

            const licenseNumber = extractField(extractedText, [
                /Seed\s*License\s*(?:No\.?)?[:\s]*([A-Z0-9\s-]{6,20})/i,
                /License\s*Number[:\s]*([A-Z0-9\s-]{6,20})/i,
                /(\b[A-Z0-9]{2,4}-[A-Z0-9]{2,4}-[A-Z0-9]{2,4}\b)/i,
                /([A-Z0-9]{6,20})/
            ], /^[A-Z0-9\s-]{6,20}$/i);

            if (licenseNumber) {
                setFieldValue('seed_license', licenseNumber);
                document.getElementById('seed_license_verified').checked = true;
                feedbackElement.innerHTML = `<span class="text-success">License extracted: ${licenseNumber}</span>`;
                return licenseNumber;
            } else {
                throw new Error('Seed License number not found');
            }
        } catch (error) {
            console.error('Seed License processing error:', error);
            feedbackElement.innerHTML = '<span class="text-danger">Error extracting license.</span>';
            throw error;
        }
    }

    // GST Document processor
    async function processGSTDocument(file, feedbackElement) { // Removed fileNameDisplayElement
        try {
            feedbackElement.innerHTML = '<span>Processing GST document... <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span></span>';

            const processedImageBlob = await preprocessImage(file);
            const extractedText = await extractTextFromImage(processedImageBlob);
            console.log('Extracted GST Text:', extractedText);

            const gstNumber = extractField(extractedText, [
                /GST(?:IN)?\s*[:\s]*([0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1})/i,
                /Goods\s*and\s*Services\s*Tax\s*(?:Identification\s*Number)?[:\s]*([0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1})/i,
                /([0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1})/
            ], /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/);

            if (gstNumber) {
                setFieldValue('gst_number', gstNumber);
                feedbackElement.innerHTML = `<span class="text-success">GST extracted: ${gstNumber}</span>`;
                return gstNumber;
            } else {
                throw new Error('GST number not found');
            }
        } catch (error) {
            console.error('GST processing error:', error);
            feedbackElement.innerHTML = '<span class="text-danger">Error extracting GST.</span>';
            throw error;
        }
    }
</script>