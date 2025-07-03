<div id="basic-details" class="form-section">
    <h5 class="mb-4">Basic Details</h5>
    <div class="row">
        <!-- Territory Dropdown -->
        <div class="col-md-3">
            <div class="form-group mb-3">
                <label for="territory" class="form-label">Territory *</label>
                <select class="form-control" id="territory" name="territory" required onchange="updateDependentFields()">
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

        <!-- Region Field -->
        <div class="col-md-2">
            <div class="form-group mb-3">
                <label class="form-label">Region *</label>
                <input type="text" class="form-control" id="region_display" readonly
                    value="{{ isset($application) && $application->region && isset($region_list[$application->region]) ? $region_list[$application->region] : (isset($preselected['region']) && isset($region_list[$preselected['region']]) ? $region_list[$preselected['region']] : '') }}">
                <input type="hidden" name="region_id" id="region_id"
                    value="{{ isset($application) && $application->region ? $application->region : (isset($preselected['region']) ? $preselected['region'] : '') }}">
            </div>
        </div>

        <!-- Zone Field -->
        <div class="col-md-2">
            <div class="form-group mb-3">
                <label class="form-label">Zone *</label>
                <input type="text" class="form-control" id="zone_display" readonly
                    value="{{ isset($application) && $application->zone && isset($zone_list[$application->zone]) ? $zone_list[$application->zone] : (isset($preselected['zone']) && isset($zone_list[$preselected['zone']]) ? $zone_list[$preselected['zone']] : '') }}">
                <input type="hidden" name="zone_id" id="zone_id"
                    value="{{ isset($application) && $application->zone ? $application->zone : (isset($preselected['zone']) ? $preselected['zone'] : '') }}">
            </div>
        </div>
        <!-- Business Unit Dropdown -->
        <div class="col-md-3">
            <div class="form-group mb-3">
                <label class="form-label">Business Unit *</label>
                <input type="text" class="form-control" id="bu_display" readonly
                    value="{{ isset($application) && $application->bu && isset($bu_list[$application->bu]) ? $bu_list[$application->bu] : (isset($preselected['bu']) && isset($bu_list[$preselected['bu']]) ? $bu_list[$preselected['bu']] : '') }}">
                <input type="hidden" name="bu_id" id="bu_id"
                    value="{{ isset($application) && $application->bu ? $application->bu : (isset($preselected['bu']) ? $preselected['bu'] : '') }}">
            </div>
        </div>
        <!-- Crop Vertical -->
        <div class="col-md-2">
            <div class="form-group mb-3">
                <label for="crop_vertical" class="form-label">Crop Vertical *</label>
                <select class="form-control" id="crop_vertical" name="crop_vertical" required>
                    @foreach($crop_type as $id => $name)
                        <option value="{{ $id }}"
                            {{ (isset($application) && $application->crop_vertical == $id) || (isset($preselected['crop_vertical']) && $preselected['crop_vertical'] == $id) ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="dis_state" class="form-label">State *</label>
                <select class="form-control" id="dis_state" name="dis_state" required>
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
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="district" class="form-label">District *</label>
                <select class="form-control" id="district" name="district" required>
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
    // Update dependent fields when Territory changes
    function updateDependentFields() {
        const territoryId = $('#territory').val();
        const $regionDisplay = $('#region_display');
        const $regionId = $('#region_id');
        const $zoneDisplay = $('#zone_display');
        const $buDisplay = $('#bu_display');
        
        const $zoneId = $('#zone_id');
        const $buId = $('#bu_id');
        // Clear previous values
        $regionDisplay.val('Loading region...');
        $zoneDisplay.val('Loading zone...');
        $buDisplay.val('Loading Bu...');
        $regionId.val('');
        $zoneId.val('');
        $buId.val('');

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
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'region',
                        value: regionId
                    }).appendTo('#distributorForm');
                } else {
                    $regionDisplay.val('No region found');
                }

                // Handle Zone
                if (response.zones && Object.keys(response.zones).length > 0) {
                    const zoneId = Object.keys(response.zones)[0];
                    const zoneName = response.zones[zoneId];
                    $zoneDisplay.val(zoneName);
                    $zoneId.val(zoneId);
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'zone',
                        value: zoneId
                    }).appendTo('#distributorForm');
                } else {
                    $zoneDisplay.val('No zone found');
                }

                 // Handle Business UNit
                if (response.businessUnits && Object.keys(response.businessUnits).length > 0) {
                    const buId = Object.keys(response.businessUnits)[0];
                    const buName = response.businessUnits[buId];
                    $buDisplay.val(buName);
                    $buId.val(buId);
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'bu',
                        value: buId
                    }).appendTo('#distributorForm');
                } else {
                    $buDisplay.val('No Bu found');
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr.responseText);
                $regionDisplay.val('Error loading region');
                $zoneDisplay.val('Error loading zone');
                $buDisplay.val('Error loading business unit');
            }
        });
    }

    // Load districts based on state
    let isLoading = false;
$('#dis_state').on('change', function () {
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
                    options += `<option value="${district.id}" ${district.id == '{{ $application->district ?? '' }}' ? 'selected' : ''}>${$('<div>').text(district.district_name).html()}</option>`;
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

    // Trigger state change on page load to load districts
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