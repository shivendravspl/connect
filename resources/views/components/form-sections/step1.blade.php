<div id="basic-details" class="form-section">
    <div class="row g-2">
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="territory" class="form-label small">Territory *</label>
                <select class="form-control form-control-sm" id="territory" name="territory" required onchange="updateDependentFields()">
                    <option value="">Select Territory</option>
                    @foreach($territory_list as $id => $name)
                        <option value="{{ $id }}"
                            {{ (isset($application) && $application->territory == $id) || (isset($preselected['territory']) && $preselected['territory'] == $id) ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4 col-lg-2">
            <div class="form-group mb-2">
                <label class="form-label small">Region *</label>
                <input type="text" class="form-control form-control-sm" id="region_display" readonly
                    value="{{ isset($application) && $application->region && isset($region_list[$application->region]) ? $region_list[$application->region] : (isset($preselected['region']) && isset($region_list[$preselected['region']]) ? $region_list[$preselected['region']] : '') }}">
                <input type="hidden" name="region" id="region_id"  <--- CHANGED NAME HERE
                    value="{{ isset($application) && $application->region ? $application->region : (isset($preselected['region']) ? $preselected['region'] : '') }}">
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4 col-lg-2">
            <div class="form-group mb-2">
                <label class="form-label small">Zone *</label>
                <input type="text" class="form-control form-control-sm" id="zone_display" readonly
                    value="{{ isset($application) && $application->zone && isset($zone_list[$application->zone]) ? $zone_list[$application->zone] : (isset($preselected['zone']) && isset($zone_list[$preselected['zone']]) ? $zone_list[$preselected['zone']] : '') }}">
                <input type="hidden" name="zone" id="zone_id"
                    value="{{ isset($application) && $application->zone ? $application->zone : (isset($preselected['zone']) ? $preselected['zone'] : '') }}">
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label class="form-label small">Business Unit *</label>
                <input type="text" class="form-control form-control-sm" id="bu_display" readonly
                    value="{{ isset($application) && $application->business_unit && isset($bu_list[$application->business_unit]) ? $bu_list[$application->business_unit] : (isset($preselected['bu']) && isset($bu_list[$preselected['bu']]) ? $bu_list[$preselected['bu']] : '') }}">
                <input type="hidden" name="business_unit" id="bu_id"
                    value="{{ isset($application) && $application->business_unit ? $application->business_unit : (isset($preselected['bu']) ? $preselected['bu'] : '') }}">
            </div>
        </div>
       
    </div>

    <div class="row g-2">
         <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="crop_vertical" class="form-label small">Crop Vertical *</label>
                <select class="form-control form-control-sm" id="crop_vertical" name="crop_vertical" required>
                    <option value="">Select Crop Vertical</option>
                    @foreach($crop_type as $id => $name)
                        <option value="{{ $id }}"
                            {{ (isset($application) && $application->crop_vertical == $id) || (isset($preselected['crop_vertical']) && $preselected['crop_vertical'] == $id) ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="dis_state" class="form-label small">State *</label>
                <select class="form-control form-control-sm" id="dis_state" name="state" required> <--- CHANGED NAME HERE
                    <option value="">Select State</option>
                    @foreach($states as $state)
                        <option value="{{ $state->id }}"
                            {{ isset($application) && $application->state == $state->id ? 'selected' : '' }}>
                            {{ $state->state_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="district" class="form-label small">District *</label>
                <select class="form-control form-control-sm" id="district" name="district" required>
                    <option value="">Select District</option>
                    @if(isset($application) && $application->district)
                        @php
                            $district = DB::table('core_district')->where('id', $application->district)->first();
                        @endphp
                        @if($district)
                            <option value="{{ $district->id }}" selected>{{ $district->district_name }}</option>
                        @endif
                    @endif
                </select>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>

function updateDependentFields() {
        const territoryId = $('#territory').val();
        const $regionDisplay = $('#region_display');
        const $regionId = $('#region_id'); // This is the ID of the hidden input
        const $zoneDisplay = $('#zone_display');
        const $zoneId = $('#zone_id');     // This is the ID of the hidden input
        const $buDisplay = $('#bu_display');
        const $buId = $('#bu_id');         // This is the ID of the hidden input

        // Clear previous values
        $regionDisplay.val('Loading region...');
        $zoneDisplay.val('Loading zone...');
        $buDisplay.val('Loading Bu...');
        $regionId.val(''); // Clear the value of the hidden input
        $zoneId.val('');   // Clear the value of the hidden input
        $buId.val('');     // Clear the value of the hidden input

        if (!territoryId) {
            $regionDisplay.val('');
            $zoneDisplay.val('');
            $buDisplay.val('');
            return;
        }

        $.ajax({
            url: '/get-territory-data',
            method: 'GET',
            data: {
                territory_id: territoryId
            },
            success: function(response) {
                // Handle Region
                if (response.regions && Object.keys(response.regions).length > 0) {
                    const regionId = Object.keys(response.regions)[0];
                    const regionName = response.regions[regionId];
                    $regionDisplay.val(regionName);
                    $regionId.val(regionId);
                } else {
                    $regionDisplay.val('No region found');
                    $regionId.val('');
                }

                // Handle Zone
                if (response.zones && Object.keys(response.zones).length > 0) {
                    const zoneId = Object.keys(response.zones)[0];
                    const zoneName = response.zones[zoneId];
                    $zoneDisplay.val(zoneName);
                    $zoneId.val(zoneId); 
                } else {
                    $zoneDisplay.val('No zone found');
                    $zoneId.val('');
                }

                // Handle Business Unit
                if (response.businessUnits && Object.keys(response.businessUnits).length > 0) {
                    console.log(response);
                    const buId = Object.keys(response.businessUnits)[0];
                    const buName = response.businessUnits[buId];
                    $buDisplay.val(buName);
                    $buId.val(buId); 
                } else {
                    $buDisplay.val('No Bu found');
                    $buId.val('');
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr.responseText);
                $regionDisplay.val('Error loading region');
                $zoneDisplay.val('Error loading zone');
                $buDisplay.val('Error loading business unit');
                $regionId.val('');
                $zoneId.val('');
                $buId.val('');
            }
        });
    }

    // Load districts based on state
    let isLoading = false;
    $('#dis_state').on('change', function () { // dis_state is now name="state" in Blade
        if (isLoading) return;
        isLoading = true;
        $('#district').html('<option value="">Loading...</option>');
        const stateId = $(this).val();

        if (stateId) {
            $.ajax({
                url: '/get-districts/' + stateId,
                type: 'GET',
                success: function (data) {
                    let options = '<option value="">Select District</option>';
                    $.each(data, function (index, district) {
                        // Ensure $application->district is available if it exists
                        options += `<option value="${district.id}" ${district.id == "{{ $application->district ?? '' }}" ? 'selected' : ''}>${$('<div>').text(district.district_name).html()}</option>`;
                    });
                    $('#district').html(options);
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr.responseText);
                    $('#district').html('<option value="">Error loading districts</option>');
                },
                complete: function() {
                    isLoading = false;
                }
            });
        } else {
            $('#district').html('<option value="">Select District</option>');
            isLoading = false;
        }
    });
    // This is important for pre-selected values on edit mode
    $(document).ready(function () {
        if ($('#dis_state').val()) {
            $('#dis_state').trigger('change');
        }
    });
</script>
@endpush

<style>
    input[readonly] {
        background-color: #e9ecef;
        cursor: not-allowed;
        opacity: 1;
    }
</style>