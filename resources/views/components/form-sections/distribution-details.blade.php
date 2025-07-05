<!-- Step 3: Distribution Details -->
<div id="distribution-details" class="form-section">
    <h5 class="mb-4">Distribution Details</h5>
    
    <div class="row">
        <div class="col-md-12">
            <div class="form-group mb-3">
                <label for="area_covered" class="form-label">Area to be covered *</label>
                <select id="area_covered" name="area_covered[]" style="width: 100%;" multiple required>
                    @php
                        // Get areas from old input or database
                        $selectedAreas = old('area_covered', $application->distributionDetail->area_covered ?? []);

                        // Handle malformed JSON or string
                        if (is_string($selectedAreas)) {
                            $decoded = json_decode($selectedAreas, true);
                            if (is_array($decoded)) {
                                if (count($decoded) === 1 && strpos($decoded[0], ',') !== false) {
                                    $selectedAreas = array_map('trim', explode(',', $decoded[0]));
                                } else {
                                    $selectedAreas = $decoded;
                                }
                            } else {
                                $selectedAreas = array_map('trim', explode(',', $selectedAreas));
                            }
                        } elseif (!is_array($selectedAreas)) {
                            $selectedAreas = [];
                        }

                        // Fetch active districts from core_district
                        $districts = DB::table('core_district')->where('is_active', 1)->pluck('district_name');
                        \Log::info('Available districts:', $districts->toArray());
                        \Log::info('Selected areas:', (array)$selectedAreas);
                    @endphp
                    
                    @if($districts->isEmpty())
                        <option value="" disabled>No active districts available</option>
                    @else
                        @foreach($districts as $district)
                            <option value="{{ $district }}" {{ in_array($district, (array)$selectedAreas) ? 'selected' : '' }}>
                                {{ $district }}
                            </option>
                        @endforeach
                    @endif
                </select>
                @error('area_covered.*')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="form-group mb-3">
                <label for="appointment_type" class="form-label">Appointment Type *</label>
                <select class="form-select" id="appointment_type" name="appointment_type" required>
                    <option value="new_area" {{ old('appointment_type', $application->distributionDetail->appointment_type ?? '') == 'new_area' ? 'selected' : '' }}>New Area</option>
                    <option value="replacement" {{ old('appointment_type', $application->distributionDetail->appointment_type ?? '') == 'replacement' ? 'selected' : '' }}>Replacement of an existing Distributor</option>
                    <option value="addition" {{ old('appointment_type', $application->distributionDetail->appointment_type ?? '') == 'addition' ? 'selected' : '' }}>Addition in current distributor area</option>
                </select>
                @error('appointment_type')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
    
    <div id="replacement-details" class="replacement-section" style="display: none;">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group mb-3">
                    <label for="replacement_reason" class="form-label">Reason for Replacement *</label>
                    <textarea class="form-control" id="replacement_reason" name="replacement_reason" rows="2">{{ old('replacement_reason', $application->distributionDetail->replacement_reason ?? '') }}</textarea>
                    @error('replacement_reason')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="form-group mb-3">
                    <label for="outstanding_recovery" class="form-label">Commitment to Recover Outstanding *</label>
                    <textarea class="form-control" id="outstanding_recovery" name="outstanding_recovery" rows="2">{{ old('outstanding_recovery', $application->distributionDetail->outstanding_recovery ?? '') }}</textarea>
                    @error('outstanding_recovery')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="previous_firm_name" class="form-label">Name of Previous Firm *</label>
                    <input type="text" class="form-control" id="previous_firm_name" name="previous_firm_name" value="{{ old('previous_firm_name', $application->distributionDetail->previous_firm_name ?? '') }}">
                    @error('previous_firm_name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="previous_firm_code" class="form-label">Code of Previous Firm *</label>
                    <input type="text" class="form-control" id="previous_firm_code" name="previous_firm_code" value="{{ old('previous_firm_code', $application->distributionDetail->previous_firm_code ?? '') }}">
                    @error('previous_firm_code')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    
    <div id="new-area-details" class="new-area-section">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group mb-3">
                    <label for="earlier_distributor" class="form-label">If New area, who was the earlier distributor covering that area *</label>
                    <input type="text" class="form-control" id="earlier_distributor" name="earlier_distributor" value="{{ old('earlier_distributor', $application->distributionDetail->earlier_distributor ?? 'None') }}">
                    @error('earlier_distributor')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 for the area field
        $('#area_covered').select2({
            tags: false, // Restrict to core_district values only
            placeholder: "Select one or more districts",
            allowClear: true,
            multiple: true,
            tokenSeparators: [], // Disable comma-based tokenization
            minimumInputLength: 0 // Allow immediate selection
        });

        // Log selections for debugging
        $('#area_covered').on('select2:select select2:unselect', function (e) {
            console.log('Selected area_covered:', $(this).val());
            $(this).trigger('change');
        });

        // Debug form submission
        $('form').on('submit', function(e) {
            console.log('Form submission - area_covered:', $('#area_covered').val());
            // Uncomment to prevent submission for debugging
            // e.preventDefault();
        });

        // Show/hide sections based on appointment type
        $('select[name="appointment_type"]').change(function() {
            const type = $(this).val();
            $('.replacement-section, .new-area-section').hide();
            
            if (type === 'replacement') {
                $('#replacement-details').show();
                $('#replacement_reason, #outstanding_recovery, #previous_firm_name, #previous_firm_code').prop('required', true);
                $('#earlier_distributor').prop('required', false);
            } else if (type === 'new_area') {
                $('#new-area-details').show();
                $('#earlier_distributor').prop('required', true);
                $('#replacement_reason, #outstanding_recovery, #previous_firm_name, #previous_firm_code').prop('required', false);
            } else {
                $('#replacement-details, #new-area-details').hide();
                $('#replacement_reason, #outstanding_recovery, #previous_firm_name, #previous_firm_code, #earlier_distributor').prop('required', false);
            }
        }).trigger('change');
        
        // Initialize with correct section
        const appointmentType = '{{ old("appointment_type", $application->distributionDetail->appointment_type ?? "new_area") }}';
        if (appointmentType === 'replacement') {
            $('#replacement-details').show();
        } else if (appointmentType === 'new_area') {
            $('#new-area-details').show();
        }
    });
</script>
@endpush