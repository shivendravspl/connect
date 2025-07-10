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
<div id="entity-details" class="form-section">
    <h5 class="mb-3">Entity Details</h5>

    <div class="row g-2">
        <div class="col-12 col-md-6">
            <div class="form-group mb-2">
                <label for="establishment_name" class="form-label small">Name of Establishment *</label>
                <input type="text" class="form-control form-control-sm" id="establishment_name" name="establishment_name"
                    value="{{ old('establishment_name', isset($application->entityDetails) ? $application->entityDetails->establishment_name : '') }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="entity_type" class="form-label">Type/Nature of Establishment *</label>
                <select class="form-control" id="entity_type" name="entity_type" required onchange="showRelevantFields()">
                    <option value="">Select Type</option>
                    @foreach(['sole_proprietorship' => 'Sole Proprietorship', 'partnership' => 'Partnership', 'llp' => 'Limited Liability Partnership (LLP)', 'private_company' => 'Private Company', 'public_company' => 'Public Company', 'cooperative_society' => 'Cooperative Societies', 'trust' => 'Trust'] as $value => $label)
                    <option value="{{ $value }}"
                        {{ old('entity_type', isset($application->entityDetails) ? $application->entityDetails->entity_type : '') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Sole Proprietorship Fields -->
    <div id="sole_proprietorship_fields" class="entity-specific-fields" style="display:none;">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Proprietor Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="proprietor_name" class="form-label">Name of Proprietor *</label>
                            <input type="text" class="form-control" id="proprietor_name" name="proprietor_name"
                                value="{{ old('proprietor_name', isset($application->entityDetails->additional_data['proprietor']['name']) ? $application->entityDetails->additional_data['proprietor']['name'] : '') }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="proprietor_dob" class="form-label">Date of Birth *</label>
                            <input type="date" class="form-control" id="proprietor_dob" name="proprietor_dob"
                                value="{{ old('proprietor_dob', isset($application->entityDetails->additional_data['proprietor']['dob']) ? $application->entityDetails->additional_data['proprietor']['dob'] : '') }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="proprietor_father_name" class="form-label">Father's/Husband's Name *</label>
                            <input type="text" class="form-control" id="proprietor_father_name" name="proprietor_father_name"
                                value="{{ old('proprietor_father_name', isset($application->entityDetails->additional_data['proprietor']['father_name']) ? $application->entityDetails->additional_data['proprietor']['father_name'] : '') }}" required>
                        </div>
                    </div>
                </div>
                <h6 class="mt-4 mb-3">Permanent Address</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="proprietor_address" class="form-label">Full Address *</label>
                            <textarea class="form-control" id="proprietor_address" name="proprietor_address" rows="2" required>{{ old('proprietor_address', isset($application->entityDetails->additional_data['proprietor']['address']) ? $application->entityDetails->additional_data['proprietor']['address'] : '') }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="proprietor_pincode" class="form-label">Pincode *</label>
                            <input type="text" class="form-control" id="proprietor_pincode" name="proprietor_pincode"
                                value="{{ old('proprietor_pincode', isset($application->entityDetails->additional_data['proprietor']['pincode']) ? $application->entityDetails->additional_data['proprietor']['pincode'] : '') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="proprietor_country" class="form-label">Country *</label>
                            <input type="text" class="form-control" id="proprietor_country" name="proprietor_country"
                                value="{{ old('proprietor_country', isset($application->entityDetails->additional_data['proprietor']['country']) ? $application->entityDetails->additional_data['proprietor']['country'] : 'India') }}" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Partnership Fields -->
    <div id="partnership_fields" class="entity-specific-fields" style="display:none;">
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Partners Details</h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addPartner()">+ Add Partner</button>
            </div>
            <div class="card-body">
                <div id="partners_container">
                    @php
                    $partners = old('partner_name', isset($application->entityDetails->additional_data['partners']) && $application->entityDetails->entity_type === 'partnership' ? $application->entityDetails->additional_data['partners'] : []);
                    if (empty($partners)) {
                    $partners[] = ['name' => '', 'father_name' => '', 'contact' => '', 'email' => '', 'address' => ''];
                    }
                    @endphp
                    @foreach($partners as $index => $partner)
                    <div class="partner-entry mb-4 border-bottom pb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Partner Name *</label>
                                    <input type="text" class="form-control" name="partner_name[]" value="{{ old("partner_name.$index", $partner['name'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Father's/Husband's Name *</label>
                                    <input type="text" class="form-control" name="partner_father_name[]" value="{{ old("partner_father_name.$index", $partner['father_name'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Contact Number *</label>
                                    <input type="tel" class="form-control" name="partner_contact[]" value="{{ old("partner_contact.$index", $partner['contact'] ?? '') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" name="partner_email[]" value="{{ old("partner_email.$index", $partner['email'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Full Address *</label>
                                    <textarea class="form-control" name="partner_address[]" rows="2" required>{{ old("partner_address.$index", $partner['address'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removePartner(this)">Remove</button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- LLP Fields -->
    <div id="llp_fields" class="entity-specific-fields" style="display:none;">
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Designated Partners Details</h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addLLPPartner()">+ Add Partner</button>
            </div>
            <div class="card-body">
                <div id="llp_partners_container">
                    @php
                    $llpPartners = old('llp_partner_name', isset($application->entityDetails->additional_data['partners']) && $application->entityDetails->entity_type === 'llp' ? $application->entityDetails->additional_data['partners'] : []);
                    if (empty($llpPartners)) {
                    $llpPartners[] = ['name' => '', 'dpin_number' => '', 'contact' => '', 'address' => ''];
                    }
                    @endphp
                    @foreach($llpPartners as $index => $partner)
                    <div class="llp-partner-entry mb-4 border-bottom pb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Partner Name *</label>
                                    <input type="text" class="form-control" name="llp_partner_name[]" value="{{ old("llp_partner_name.$index", $partner['name'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">DPIN Number *</label>
                                    <input type="text" class="form-control" name="llp_partner_dpin[]" value="{{ old("llp_partner_dpin.$index", $partner['dpin_number'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Contact Number *</label>
                                    <input type="tel" class="form-control" name="llp_partner_contact[]" value="{{ old("llp_partner_contact.$index", $partner['contact'] ?? '') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Full Address *</label>
                            <textarea class="form-control" name="llp_partner_address[]" rows="2" required>{{ old("llp_partner_address.$index", $partner['address'] ?? '') }}</textarea>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeLLPPartner(this)">Remove</button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="llpin_number" class="form-label">LLPIN Number *</label>
                    <input type="text" class="form-control" id="llpin_number" name="llpin_number"
                        value="{{ old('llpin_number', isset($application->entityDetails->additional_data['llp']['llpin_number']) ? $application->entityDetails->additional_data['llp']['llpin_number'] : '') }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="llp_incorporation_date" class="form-label">Date of Incorporation *</label>
                    <input type="date" class="form-control" id="llp_incorporation_date" name="llp_incorporation_date"
                        value="{{ old('llp_incorporation_date', isset($application->entityDetails->additional_data['llp']['incorporation_date']) ? $application->entityDetails->additional_data['llp']['incorporation_date'] : '') }}" required>
                </div>
            </div>
        </div>
    </div>

    <!-- Company Fields -->
    <div id="company_fields" class="entity-specific-fields" style="display:none;">
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Directors Details</h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addDirector()">+ Add Director</button>
            </div>
            <div class="card-body">
                <div id="directors_container">
                    @php
                    $directors = old('director_name', isset($application->entityDetails->additional_data['partners']) && in_array($application->entityDetails->entity_type, ['private_company', 'public_company']) ? $application->entityDetails->additional_data['partners'] : []);
                    if (empty($directors)) {
                    $directors[] = ['name' => '', 'din_number' => '', 'contact' => '', 'address' => ''];
                    }
                    @endphp
                    @foreach($directors as $index => $director)
                    <div class="director-entry mb-4 border-bottom pb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Director Name *</label>
                                    <input type="text" class="form-control" name="director_name[]" value="{{ old("director_name.$index", $director['name'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">DIN Number *</label>
                                    <input type="text" class="form-control" name="director_din[]" value="{{ old("director_din.$index", $director['din_number'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Contact Number *</label>
                                    <input type="tel" class="form-control" name="director_contact[]" value="{{ old("director_contact.$index", $director['contact'] ?? '') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Full Address *</label>
                            <textarea class="form-control" name="director_address[]" rows="2" required>{{ old("director_address.$index", $director['address'] ?? '') }}</textarea>
                        </div>
                        <button type="button" class="btn btnsm btnWI danger" onclick="removeDirector(this)">Remove</button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="cin_number" class="form-label">CIN Number *</label>
                    <input type="text" class="form-control" id="cin_number" name="cin_number"
                        value="{{ old('cin_number', isset($application->entityDetails->additional_data['company']['cin_number']) ? $application->entityDetails->additional_data['company']['cin_number'] : '') }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="incorporation_date" class="form-label">Date of Incorporation *</label>
                    <input type="date" class="form-control" id="incorporation_date" name="incorporation_date"
                        value="{{ old('incorporation_date', isset($application->entityDetails->additional_data['company']['incorporation_date']) ? $application->entityDetails->additional_data['company']['incorporation_date'] : '') }}" required>
                </div>
            </div>
        </div>
    </div>

    <!-- Cooperative Society Fields -->
    <div id="cooperative_fields" class="entity-specific-fields" style="display:none;">
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Committee Members Details</h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addCommitteeMember()">+ Add Member</button>
            </div>
            <div class="card-body">
                <div id="committee_container">
                    @php
                    $committeeMembers = old('committee_name', isset($application->entityDetails->additional_data['partners']) && $application->entityDetails->entity_type === 'cooperative_society' ? $application->entityDetails->additional_data['partners'] : []);
                    if (empty($committeeMembers)) {
                    $committeeMembers[] = ['name' => '', 'designation' => '', 'contact' => '', 'address' => ''];
                    }
                    @endphp
                    @foreach($committeeMembers as $index => $member)
                    <div class="committee-entry mb-4 border-bottom pb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Member Name *</label>
                                    <input type="text" class="form-control" name="committee_name[]" value="{{ old("committee_name.$index", $member['name'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Designation *</label>
                                    <input type="text" class="form-control" name="committee_designation[]" value="{{ old("committee_designation.$index", $member['designation'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Contact Number *</label>
                                    <input type="tel" class="form-control" name="committee_contact[]" value="{{ old("committee_contact.$index", $member['contact'] ?? '') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Full Address *</label>
                            <textarea class="form-control" name="committee_address[]" rows="2" required>{{ old("committee_address.$index", $member['address'] ?? '') }}</textarea>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeCommitteeMember(this)">Remove</button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="cooperative_reg_number" class="form-label">Registration Number *</label>
                    <input type="text" class="form-control" id="cooperative_reg_number" name="cooperative_reg_number"
                        value="{{ old('cooperative_reg_number', isset($application->entityDetails->additional_data['cooperative']['reg_number']) ? $application->entityDetails->additional_data['cooperative']['reg_number'] : '') }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="cooperative_reg_date" class="form-label">Registration Date *</label>
                    <input type="date" class="form-control" id="cooperative_reg_date" name="cooperative_reg_date"
                        value="{{ old('cooperative_reg_date', isset($application->entityDetails->additional_data['cooperative']['reg_date']) ? $application->entityDetails->additional_data['cooperative']['reg_date'] : '') }}" required>
                </div>
            </div>
        </div>
    </div>

    <!-- Trust Fields -->
    <div id="trust_fields" class="entity-specific-fields" style="display:none;">
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Trustees Details</h6>
                <button type="button" class="btn btn-sm btn-primary" onclick="addTrustee()">+ Add Trustee</button>
            </div>
            <div class="card-body">
                <div id="trustees_container">
                    @php
                    $trustees = old('trustee_name', isset($application->entityDetails->additional_data['partners']) && $application->entityDetails->entity_type === 'trust' ? $application->entityDetails->additional_data['partners'] : []);
                    if (empty($trustees)) {
                    $trustees[] = ['name' => '', 'designation' => '', 'contact' => '', 'address' => ''];
                    }
                    @endphp
                    @foreach($trustees as $index => $trustee)
                    <div class="trustee-entry mb-4 border-bottom pb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Trustee Name *</label>
                                    <input type="text" class="form-control" name="trustee_name[]" value="{{ old("trustee_name.$index", $trustee['name'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Designation *</label>
                                    <input type="text" class="form-control" name="trustee_designation[]" value="{{ old("trustee_designation.$index", $trustee['designation'] ?? '') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Contact Number *</label>
                                    <input type="tel" class="form-control" name="trustee_contact[]" value="{{ old("trustee_contact.$index", $trustee['contact'] ?? '') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Full Address *</label>
                            <textarea class="form-control" name="trustee_address[]" rows="2" required>{{ old("trustee_address.$index", $trustee['address'] ?? '') }}</textarea>
                        </div>
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeTrustee(this)">Remove</button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="trust_reg_number" class="form-label">Registration Number *</label>
                    <input type="text" class="form-control" id="trust_reg_number" name="trust_reg_number"
                        value="{{ old('trust_reg_number', isset($application->entityDetails->additional_data['trust']['reg_number']) ? $application->entityDetails->additional_data['trust']['reg_number'] : '') }}" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="trust_reg_date" class="form-label">Registration Date *</label>
                    <input type="date" class="form-control" id="trust_reg_date" name="trust_reg_date"
                        value="{{ old('trust_reg_date', isset($application->entityDetails->additional_data['trust']['reg_date']) ? $application->entityDetails->additional_data['trust']['reg_date'] : '') }}" required>
                </div>
            </div>
        </div>
    </div>

    <!-- Common Fields -->
    <div class="row">
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label for="business_address" class="form-label">Business Place/Shop Address *</label>
                <textarea class="form-control" id="business_address" name="business_address" rows="2" required>{{ old('business_address', isset($application->entityDetails) ? $application->entityDetails->business_address : '') }}</textarea>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label for="house_no" class="form-label">House No. / Building</label>
                <input type="text" class="form-control" id="house_no" name="house_no"
                    value="{{ old('house_no', isset($application->entityDetails) ? $application->entityDetails->house_no : '') }}">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group mb-3">
                <label for="landmark" class="form-label">Landmark</label>
                <input type="text" class="form-control" id="landmark" name="landmark"
                    value="{{ old('landmark', isset($application->entityDetails) ? $application->entityDetails->landmark : '') }}">
            </div>
        </div>
    </div>
    <div class="row">
        <!-- City -->
        <div class="col-md-2">
            <div class="form-group mb-3">
                <label for="city" class="form-label">City *</label>
                <input type="text" class="form-control" id="city" name="city"
                    value="{{ old('city', isset($application->entityDetails) ? $application->entityDetails->city : '') }}" required>
            </div>
        </div>
        <!-- State -->
        <div class="col-md-3">
            <div class="form-group mb-3">
                <label for="state_id" class="form-label">State *</label>
                <select class="form-select" id="state_id" name="state_id" required>
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
        <!-- District -->
        <div class="col-md-3">
            <div class="form-group mb-3">
                <label for="district_id" class="form-label">District *</label>
                <select class="form-select" id="district_id" name="district_id" required>
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
        <!-- Pincode -->
        <div class="col-md-2">
            <div class="form-group mb-3">
                <label for="pincode" class="form-label">Pincode *</label>
                <input type="text" class="form-control" id="pincode" name="pincode"
                    value="{{ old('pincode', isset($application->entityDetails) ? $application->entityDetails->pincode : '') }}" required>
            </div>
        </div>
        <!-- Country -->
        <div class="col-md-2">
            <div class="form-group mb-3">
                <label for="country" class="form-label">Country *</label>
                <input type="text" class="form-control" id="country" name="country"
                    value="India" readonly>
                <input type="hidden" name="country_id" value="1">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="mobile" class="form-label">Mobile Number *</label>
                <input type="tel" class="form-control" id="mobile" name="mobile"
                    value="{{ old('mobile', isset($application->entityDetails) ? $application->entityDetails->mobile : '') }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="email" class="form-label">Email Address *</label>
                <input type="email" class="form-control" id="email" name="email"
                    value="{{ old('edit', isset($application->entityDetails) ? $application->entityDetails->email : '') }}" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="pan_number" class="form-label">PAN Number *</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="pan_number" name="pan_number"
                        value="{{ old('pan_number', $panDoc['details']['pan_number'] ?? ($application->entityDetails->pan_number ?? '')) }}"
                        required>
                    <input type="file" class="form-control d-none" id="pan_file" name="pan_file" accept=".pdf,.jpg,.jpeg,.png">
                    <button type="button" class="btn btn-outline-secondary" id="pan_upload_btn">Upload</button>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="pan_verified" name="pan_verified"
                        {{ old('pan_verified', $panDoc['verified'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="pan_verified">
                        I confirm the PAN number matches the uploaded document
                    </label>
                </div>
                <div id="pan_file_name" class="small text-muted mt-1 {{ $panDoc ? '' : 'd-none' }}">
                    @if($panDoc)
                    <a href="{{ asset('storage/' . $panDoc['path']) }}" target="_blank">View PAN Document</a> (Uploaded
                    on {{ $panDoc['remarks'] }})
                    <input type="hidden" name="existing_pan_file" value="{{ $panDoc['path'] }}">
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="gst_applicable" class="form-label">GST Applicable *</label>
                <select class="form-control" id="gst_applicable" name="gst_applicable" required onchange="toggleGSTFields()">
                    <option value="" disabled {{ old('gst_applicable', isset($application->entityDetails) ? '' : 'selected') }}>-- Select --</option>
                    <option value="yes" {{ old('gst_applicable', isset($application->entityDetails) && $application->entityDetails->gst_applicable === 'yes' ? 'selected' : '') }}>Yes</option>
                    <option value="no" {{ old('gst_applicable', isset($application->entityDetails) && $application->entityDetails->gst_applicable === 'no' ? 'selected' : '') }}>No</option>
                </select>
            </div>
        </div>

    </div>
    <div id="gst_fields" style="display: {{ old('gst_applicable', ($application->entityDetails->gst_applicable ?? ($gstDoc ? 'yes' : 'no'))) === 'yes' ? 'block' : 'none' }};">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="gst_number" class="form-label">GST Number *</label>
                    <input type="text" class="form-control" id="gst_number" name="gst_number"
                        value="{{ old('gst_number', $gstDoc['details']['gst_number'] ?? ($application->entityDetails->gst_number ?? '')) }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="gst_validity" class="form-label">GST Validity Date *</label>
                    <input type="date" class="form-control" id="gst_validity" name="gst_validity"
                        value="{{ old('gst_validity', $gstDoc['details']['gst_validity'] ?? ($application->entityDetails->additional_data['gst_validity'] ?? '')) }}">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group mb-3">
                    <label for="gst_file" class="form-label">Upload GST Document *</label>
                    <div class="input-group">
                        <input type="file" class="form-control d-none" id="gst_file" name="gst_file" accept=".pdf,.jpg,.jpeg,.png">
                        <button type="button" class="btn btn-outline-secondary" id="gst_upload_btn">Upload</button>
                    </div>
                    <div id="gst_file_name" class="small text-muted mt-1 {{ $gstDoc ? '' : 'd-none' }}">
                        @if($gstDoc)
                        <a href="{{ asset('storage/'.$gstDoc['path']) }}" target="_blank">View GST Document</a> (Uploaded on {{ $gstDoc['remarks'] }})
                        <input type="hidden" name="existing_gst_file" value="{{ $gstDoc['path'] }}">
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="seed_license" class="form-label">Seed License Number *</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="seed_license" name="seed_license"
                        value="{{ old('seed_license', $seedLicenseDoc['details']['seed_license_number'] ?? ($application->entityDetails->seed_license ?? '')) }}" required>
                    <input type="file" class="form-control d-none" id="seed_license_file" name="seed_license_file" accept=".pdf,.jpg,.jpeg,.png">
                    <button type="button" class="btn btn-outline-secondary" id="seed_license_upload_btn">Upload</button>
                </div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="seed_license_verified" name="seed_license_verified"
                        {{ old('seed_license_verified', $panDoc['verified'] ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="seed_license_verified">
                        I confirm the Seed License number matches the uploaded document
                    </label>
                </div>
                <div id="seed_license_file_name" class="small text-muted mt-1 {{ $seedLicenseDoc ? '' : 'd-none' }}">
                    @if($seedLicenseDoc)
                    <a href="{{ asset('storage/'.$seedLicenseDoc['path']) }}" target="_blank">View Seed License Document</a> (Uploaded on {{ $seedLicenseDoc['remarks'] }})
                    <input type="hidden" name="existing_seed_license_file" value="{{ $seedLicenseDoc['path'] }}">
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="seed_license_validity" class="form-label">Seed License Validity Date *</label>
                <input type="date" class="form-control" id="seed_license_validity" name="seed_license_validity"
                    value="{{ old('seed_license_validity', $seedLicenseDoc['details']['seed_license_validity'] ?? ($application->entityDetails->additional_data['seed_license_validity'] ?? '')) }}" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="tan_number" class="form-label">TAN Number (if any)</label>
                <input type="text" class="form-control" id="tan_number" name="tan_number"
                    value="{{ old('tan_number', isset($application->entityDetails->additional_data['tan_number']) ? $application->entityDetails->additional_data['tan_number'] : '') }}">
            </div>
        </div>
    </div>

    <!-- Bank Details -->
    <div class="row">
        <div class="col-md-12">
            <h4>Bank Details</h4>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="bank_name" class="form-label">Name of the Bank *</label>
                <input type="text" class="form-control" id="bank_name" name="bank_name"
                    value="{{ old('bank_name', $bankDoc['details']['bank_name'] ?? ($application->entityDetails->additional_data['bank_details']['bank_name'] ?? '')) }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="account_holder" class="form-label">Name of Bank Account Holder *</label>
                <input type="text" class="form-control" id="account_holder" name="account_holder"
                    value="{{ old('account_holder', $bankDoc['details']['account_holder'] ?? ($application->entityDetails->additional_data['bank_details']['account_holder'] ?? '')) }}" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="account_number" class="form-label">Account Number *</label>
                <input type="text" class="form-control" id="account_number" name="account_number"
                    value="{{ old('account_number', $bankDoc['details']['account_number'] ?? ($application->entityDetails->additional_data['bank_details']['account_number'] ?? '')) }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="ifsc_code" class="form-label">IFSC Code of Bank *</label>
                <input type="text" class="form-control" id="ifsc_code" name="ifsc_code"
                    value="{{ old('ifsc_code', $bankDoc['details']['ifsc_code'] ?? ($application->entityDetails->additional_data['bank_details']['ifsc_code'] ?? '')) }}" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group mb-3">
                <label class="form-label">Upload Bank Document (Passbook/Cancelled Cheque) *</label>
                <div class="input-group">
                    <input type="file" class="form-control d-none" id="bank_file" name="bank_file" accept=".pdf,.jpg,.jpeg,.png">
                    <button type="button" class="btn btn-outline-secondary" id="bank_upload_btn">Upload</button>
                </div>
                <div id="bank_file_name" class="small text-muted mt-1 {{ $bankDoc ? '' : 'd-none' }}">
                    @if($bankDoc)
                    <a href="{{ asset('storage/'.$bankDoc['path']) }}" target="_blank">View Bank Document</a> (Uploaded on {{ $bankDoc['remarks'] }})
                    <input type="hidden" name="existing_bank_file" value="{{ $bankDoc['path'] }}">
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Authorized Persons Section -->
    <div class="card mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Authorized Persons Details (if any)</h6>
            <button type="button" class="btn btn-sm btn-primary" onclick="addAuthorizedPerson()">+ Add</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="authorized_persons_table">
                    <thead>
                        <tr>
                            <th>Name *</th>
                            <th>Contact Number *</th>
                            <th>Email Address</th>
                            <th>Full Address *</th>
                            <th>Relation *</th>
                            <th>Letter of Authorisation *</th>
                            <th>Aadhar *</th>
                            <th>Action</th>
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
                            <td data-label="Name">
                                <input type="text" class="form-control" name="auth_person_name[]" value="{{ old("auth_person_name.$index", $person['name'] ?? '') }}" required>
                                <div class="invalid-feedback"></div>
                            </td>
                            <td data-label="Contact Number">
                                <input type="tel" class="form-control" name="auth_person_contact[]" value="{{ old("auth_person_contact.$index", $person['contact'] ?? '') }}" required>
                                <div class="invalid-feedback"></div>
                            </td>
                            <td data-label="Email Address">
                                <input type="email" class="form-control" name="auth_person_email[]" value="{{ old("auth_person_email.$index", $person['email'] ?? '') }}">
                                <div class="invalid-feedback"></div>
                            </td>
                            <td data-label="Full Address">
                                <textarea class="form-control" name="auth_person_address[]" rows="2" required>{{ old("auth_person_address.$index", $person['address'] ?? '') }}</textarea>
                                <div class="invalid-feedback"></div>
                            </td>
                            <td data-label="Relation">
                                <input type="text" class="form-control" name="auth_person_relation[]" value="{{ old("auth_person_relation.$index", $person['relation'] ?? '') }}" required>
                                <div class="invalid-feedback"></div>
                            </td>
                                <td data-label="Letter of Authorisation">
                                <input type="file" class="form-control" name="auth_person_letter[]" accept=".pdf,.doc,.docx">
                                <div class="invalid-feedback"></div>
                                <div id="auth_person_letter_{{ $index }}_name" class="small text-muted mt-1 {{ isset($person['letter']) ? '' : 'd-none' }}">
                                    @if(isset($person['letter']) && $person['letter'])
                                        <a href="{{ asset('storage/' . $person['letter']) }}" target="_blank">View Letter</a>
                                        <input type="hidden" name="existing_auth_person_letter[]" value="{{ $person['letter'] }}">
                                    @endif
                                </div>
                            </td>
                            <td data-label="Aadhar">
                                <input type="file" class="form-control" name="auth_person_aadhar[]" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="invalid-feedback"></div>
                                <div id="auth_person_aadhar_{{ $index }}_name" class="small text-muted mt-1 {{ isset($person['aadhar']) ? '' : 'd-none' }}">
                                    @if(isset($person['aadhar']) && $person['aadhar'])
                                        <a href="{{ asset('storage/' . $person['aadhar']) }}" target="_blank">View Aadhar</a>
                                        <input type="hidden" name="existing_auth_person_aadhar[]" value="{{ $person['aadhar'] }}">
                                    @endif
                                </div>
                            </td>
                            <td data-label="Action">
                                <button type="button" class="btn btn-sm btn-danger" onclick="removeAuthorizedPerson(this)">Remove</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <input type="hidden" name="original_entity_type" value="{{ old('entity_type', isset($application->entityDetails) ? $application->entityDetails->entity_type : '') }}">

    <script>
        function showRelevantFields() {
            const entityType = document.getElementById('entity_type').value;
            const originalEntityType = document.querySelector('input[name="original_entity_type"]').value;
            const specificFields = document.querySelectorAll('.entity-specific-fields');

            // Hide all entity-specific fields and disable their inputs
            specificFields.forEach(el => {
                el.style.display = 'none';
                el.querySelectorAll('input, textarea, select').forEach(input => input.disabled = true);
            });

            // Only clear inapplicable fields if entity_type has changed
            if (entityType !== originalEntityType) {
                clearInapplicableFields(entityType);
                // Update original_entity_type
                document.querySelector('input[name="original_entity_type"]').value = entityType;
            }

            // Show and enable relevant fields
            let targetField = null;
            switch (entityType) {
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
                targetField.querySelectorAll('input, textarea, select').forEach(input => input.disabled = false);
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
                'sole_proprietorship': ['proprietor_name', 'proprietor_dob', 'proprietor_father_name', 'proprietor_address', 'proprietor_pincode', 'proprietor_country'],
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
            newEntry.className = 'partner-entry mb-4 border-bottom pb-3';
            newEntry.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label">Partner Name *</label>
                            <input type="text" class="form-control" name="partner_name[]" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label">Father's/Husband's Name *</label>
                            <input type="text" class="form-control" name="partner_father_name[]" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label">Contact Number *</label>
                            <input type="tel" class="form-control" name="partner_contact[]" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-control" name="partner_email[]" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label class="form-label">Full Address *</label>
                            <textarea class="form-control" name="partner_address[]" rows="2" required></textarea>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="removePartner(this)">Remove</button>
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

        function addLLPPartner() {
            const container = document.getElementById('llp_partners_container');
            const newEntry = document.createElement('div');
            newEntry.className = 'llp-partner-entry mb-4 border-bottom pb-3';
            newEntry.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label">Partner Name *</label>
                            <input type="text" class="form-control" name="llp_partner_name[]" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label">DPIN Number *</label>
                            <input type="text" class="form-control" name="llp_partner_dpin[]" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label">Contact Number *</label>
                            <input type="tel" class="form-control" name="llp_partner_contact[]" required>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Full Address *</label>
                    <textarea class="form-control" name="llp_partner_address[]" rows="2" required></textarea>
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeLLPPartner(this)">Remove</button>
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
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label">Director Name *</label>
                            <input type="text" class="form-control" name="director_name[]" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label">DIN Number *</label>
                            <input type="text" class="form-control" name="director_din[]" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label">Contact Number *</label>
                            <input type="tel" class="form-control" name="director_contact[]" required>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Full Address *</label>
                    <textarea class="form-control" name="director_address[]" rows="2" required></textarea>
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeDirector(this)">Remove</button>
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
            newEntry.className = 'committee-entry mb-4 border-bottom pb-3';
            newEntry.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label">Member Name *</label>
                            <input type="text" class="form-control" name="committee_name[]" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label">Designation *</label>
                            <input type="text" class="form-control" name="committee_designation[]" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label">Contact Number *</label>
                            <input type="tel" class="form-control" name="committee_contact[]" required>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Full Address *</label>
                    <textarea class="form-control" name="committee_address[]" rows="2" required></textarea>
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeCommitteeMember(this)">Remove</button>
            `;
            container.appendChild(newEntry);
        }

        function removeCommitteeMember(button) {
            const entries = document.querySelectorAll('.committee-entry');
            if (entries.length > 1) {
                button.closest('.committee-entry').remove();
            } else {
                alert('At least one committee member is required.');
            }
        }

        function addTrustee() {
            const container = document.getElementById('trustees_container');
            const newEntry = document.createElement('div');
            newEntry.className = 'trustee-entry mb-4 border-bottom pb-3';
            newEntry.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label">Trustee Name *</label>
                            <input type="text" class="form-control" name="trustee_name[]" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label">Designation *</label>
                            <input type="text" class="form-control" name="trustee_designation[]" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label class="form-label">Contact Number *</label>
                            <input type="tel" class="form-control" name="trustee_contact[]" required>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Full Address *</label>
                    <textarea class="form-control" name="trustee_address[]" rows="2" required></textarea>
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeTrustee(this)">Remove</button>
            `;
            container.appendChild(newEntry);
        }

        function removeTrustee(button) {
            const entries = document.querySelectorAll('.trustee-entry');
            if (entries.length > 1) {
                button.closest('.trustee-entry').remove();
            } else {
                alert('At least one trustee is required.');
            }
        }

        function handleFileChange(input, index, type) {
            const fileNameDiv = document.getElementById(`auth_person_${type}_${index}_name`);
            if (input.files.length > 0) {
                fileNameDiv.classList.add('d-none');
            } else {
                fileNameDiv.classList.remove('d-none');
            }
        }


        function addAuthorizedPerson() {
            const container = document.getElementById('authorized_persons_container');
            const newRow = document.createElement('tr');
            newRow.className = 'authorized-person-entry';
            newRow.innerHTML = `
        <td data-label="Name">
            <input type="text" class="form-control" name="auth_person_name[]" required>
            <div class="invalid-feedback"></div>
        </td>
        <td data-label="Contact Number">
            <input type="tel" class="form-control" name="auth_person_contact[]" required>
            <div class="invalid-feedback"></div>
        </td>
        <td data-label="Email Address">
            <input type="email" class="form-control" name="auth_person_email[]">
            <div class="invalid-feedback"></div>
        </td>
        <td data-label="Full Address">
            <textarea class="form-control" name="auth_person_address[]" rows="2" required></textarea>
            <div class="invalid-feedback"></div>
        </td>
        <td data-label="Relation">
            <input type="text" class="form-control" name="auth_person_relation[]" required>
            <div class="invalid-feedback"></div>
        </td>
        <td data-label="Letter of Authorisation">
            <input type="file" class="form-control" name="auth_person_letter[]" accept=".pdf,.doc,.docx" required>
            <div class="invalid-feedback"></div>
        </td>
        <td data-label="Aadhar">
            <input type="file" class="form-control" name="auth_person_aadhar[]" accept=".pdf,.jpg,.jpeg,.png" required>
            <div class="invalid-feedback"></div>
        </td>
        <td data-label="Action">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeAuthorizedPerson(this)">Remove</button>
        </td>
    `;
            container.appendChild(newRow);

            const index = document.querySelectorAll('.authorized-person-entry').length - 1;
            const letterInput = newRow.querySelector('input[name="auth_person_letter[]"]');
            const aadharInput = newRow.querySelector('input[name="auth_person_aadhar[]"]');
            
            letterInput.addEventListener('change', () => handleFileChange(letterInput, index, 'letter'));
            aadharInput.addEventListener('change', () => handleFileChange(aadharInput, index, 'aadhar'));
        }

        function removeAuthorizedPerson(button) {
            const entries = document.querySelectorAll('.authorized-person-entry');
            const row = button.closest('.authorized-person-entry');
            const inputs = row.querySelectorAll('input:not([type="file"]), textarea');
            const isEmpty = Array.from(inputs).every(input => input.value.trim() === '');
            const fileInputs = row.querySelectorAll('input[type="file"]');
            const filesEmpty = Array.from(fileInputs).every(input => !input.files.length);

            if (entries.length > 1 || (isEmpty && filesEmpty)) {
                row.remove();
            } else {
                alert('At least one authorized person entry is required if any field is filled.');
            }
        }

        // Initialize event listeners
        document.addEventListener('DOMContentLoaded', function() {
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

            // Call showRelevantFields and toggleGSTFields if Step 2 is active
            const step2 = document.querySelector('.step-content[data-step="2"]');
            if (step2 && step2.style.display !== 'none') {
                showRelevantFields();
                toggleGSTFields();
            }
        });
    </script>
</div>
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
    async function processGSTDocument(file, fileNameField) {
        try {
            fileNameField.textContent = 'Processing GST document...';
            fileNameField.classList.remove('d-none');

            const processedImage = await preprocessImage(file);
            const extractedText = await extractTextFromImage(processedImage);
            console.log('Extracted GST Text:', extractedText);

            const gstNumber = extractField(extractedText, [
                /GST\s*[:\s]*([0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1})/i,
                /Goods\s*and\s*Services\s*Tax\s*[:\s]*([0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1})/i,
                /([0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1})/
            ], /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/);

            if (gstNumber) {
                setFieldValue('gst_number', gstNumber);
                fileNameField.textContent = `GST extracted: ${gstNumber}`;
                return gstNumber;
            } else {
                throw new Error('GST number not found');
            }
        } catch (error) {
            console.error('GST processing error:', error);
            fileNameField.textContent = 'Error processing GST document';
            throw error;
        }
    }

    // Initialize all upload handlers
    function initializeUploaders() {
        // Bank Document
        const bankUploadBtn = document.getElementById('bank_upload_btn');
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
        const panUploadBtn = document.getElementById('pan_upload_btn');
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
        const seedUploadBtn = document.getElementById('seed_license_upload_btn');
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
        const gstUploadBtn = document.getElementById('gst_upload_btn');
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
    });
</script>
@endpush