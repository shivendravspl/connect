<div id="basic-details" class="form-section">
    <div class="row g-2 p-2">
        <div class="col-12 col-sm-6 col-md-4 col-lg-2">
            <div class="form-group mb-2">
                <label for="territory" class="form-label small">Territory <span class="text-danger">*</span></label>
                <select class="form-control form-control-sm form-select-sm select2-territory" id="territory" name="territory" required>
                    <option value="">Select Territory</option>
                    @foreach($territory_list as $id => $name)
                        <option value="{{ $id }}"
                            {{ (isset($application) && $application->exists && $application->territory == $id) || (isset($preselected['territory']) && $preselected['territory'] == $id) ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label class="form-label small">Region <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" id="region_display" readonly
                    value="{{ isset($application) && $application->exists && $application->region && isset($region_list[$application->region]) ? $region_list[$application->region] : (isset($preselected['region']) && isset($region_list[$preselected['region']]) ? $region_list[$preselected['region']] : '') }}">
                <input type="hidden" name="region" id="region_id"
                    value="{{ isset($application) && $application->exists && $application->region ? $application->region : (isset($preselected['region']) ? $preselected['region'] : '') }}">
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label class="form-label small">Zone <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" id="zone_display" readonly
                    value="{{ isset($application) && $application->exists && $application->zone && isset($zone_list[$application->zone]) ? $zone_list[$application->zone] : (isset($preselected['zone']) && isset($zone_list[$preselected['zone']]) ? $zone_list[$preselected['zone']] : '') }}">
                <input type="hidden" name="zone" id="zone_id"
                    value="{{ isset($application) && $application->exists && $application->zone ? $application->zone : (isset($preselected['zone']) ? $preselected['zone'] : '') }}">
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-2">
            <div class="form-group mb-2">
                <label class="form-label small">Business Unit <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" id="bu_display" readonly
                    value="{{ isset($application) && $application->exists && $application->business_unit && isset($bu_list[$application->business_unit]) ? $bu_list[$application->business_unit] : (isset($preselected['bu']) && isset($bu_list[$preselected['bu']]) ? $bu_list[$preselected['bu']] : '') }}">
                <input type="hidden" name="business_unit" id="bu_id"
                    value="{{ isset($application) && $application->exists && $application->business_unit ? $application->business_unit : (isset($preselected['bu']) ? $preselected['bu'] : '') }}">
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-2">
            <div class="form-group mb-2">
                <label class="form-label small">Crop Vertical <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm" id="crop_vertical_display" readonly
                    value="{{ isset($application) && $application->exists && $application->crop_vertical && isset($vertical_list[$application->crop_vertical]) ? $vertical_list[$application->crop_vertical] : (isset($preselected['crop_vertical']) && isset($vertical_list[$preselected['crop_vertical']]) ? $vertical_list[$preselected['crop_vertical']] : '') }}">
                <input type="hidden" name="crop_vertical" id="crop_vertical"
                    value="{{ isset($application) && $application->exists && $application->crop_vertical ? $application->crop_vertical : (isset($preselected['crop_vertical']) ? $preselected['crop_vertical'] : '') }}">
            </div>
        </div>
    </div>

   
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        // Initialize Select2 for the territory dropdown
        $('#territory').select2({
            placeholder: 'Select Territory',
            allowClear: true,
            width: '100%' // Ensure it fits the container
        });

        

        // Trigger updateDependentFields on territory change
        $('#territory').on('select2:select', function (e) {
            updateDependentFields();
        });

        // Clear selection handling (optional, if allowClear is enabled)
        $('#territory').on('select2:clear', function (e) {
            updateDependentFields();
        });

        // Function for updating dependent fields
        function updateDependentFields() {
            const territoryId = $('#territory').val();
            const $regionDisplay = $('#region_display');
            const $regionId = $('#region_id');
            const $zoneDisplay = $('#zone_display');
            const $zoneId = $('#zone_id');
            const $buDisplay = $('#bu_display');
            const $buId = $('#bu_id');
            const $cropVerticalDisplay = $('#crop_vertical_display');
            const $cropVerticalId = $('#crop_vertical');


            // Clear previous values
            $regionDisplay.val('Loading region...');
            $zoneDisplay.val('Loading zone...');
            $buDisplay.val('Loading Bu...');
            $regionId.val('');
            $zoneId.val('');
            $buId.val('');
            $cropVerticalId.val('');

            if (!territoryId) {
                $regionDisplay.val('');
                $zoneDisplay.val('');
                $buDisplay.val('');
                $cropVerticalDisplay.val('');
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
                        const buId = Object.keys(response.businessUnits)[0];
                        const buName = response.businessUnits[buId];
                        $buDisplay.val(buName);
                        $buId.val(buId);
                    } else {
                        $buDisplay.val('No Bu found');
                        $buId.val('');
                    }

                     // Handle Crop Vertical
                    if (response.verticals && Object.keys(response.verticals).length > 0) {
                        const verticalId = Object.keys(response.verticals)[0];
                        const verticalName = response.verticals[verticalId];
                        console.log('Setting Crop Vertical:', { id: verticalId, name: verticalName }); // Debug
                        $cropVerticalDisplay.val(verticalName);
                        $cropVerticalId.val(verticalId);
                    } else {
                        console.log('No verticals found in response'); // Debug
                        $cropVerticalDisplay.val('No crop vertical found');
                        $cropVerticalId.val('');
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
                    $cropVerticalId.val('');
                }
            });
        }      

        // Trigger updateDependentFields for pre-selected territory
        if ($('#territory').val()) {
            updateDependentFields();
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

    .select2-container .select2-selection--single {
        height: calc(1.5em + 0.5rem + 2px); /* Match form-control-sm height */
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem; /* Match small font size */
        border: 1px solid #ced4da;
        border-radius: 0.2rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 1.5;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + 0.5rem);
    }

    .select2-container .select2-selection--single:focus {
        outline: none;
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
</style>