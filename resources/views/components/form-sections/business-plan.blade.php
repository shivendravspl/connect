<div id="business-plan" class="form-section">
    @php
        $year2025 = App\Models\Year::where('period', '2025-26')->first();
        $year2026 = App\Models\Year::where('period', '2026-27')->first();
        
        $businessPlans = old('business_plans', []);
        
        // If editing existing application
        if(isset($application->businessPlans) && $application->businessPlans->count() > 0) {
            $businessPlans = $application->businessPlans->map(function($plan) use ($year2025, $year2026) {
                return [
                    'crop' => $plan->crop,
                    'fy2025_26' => $plan->yearly_targets[$year2025->id] ?? '',
                    'fy2026_27' => $plan->yearly_targets[$year2026->id] ?? ''
                ];
            })->toArray();
        }
        
        // If no existing data, create one empty row
        if(empty($businessPlans)) {
            $businessPlans = [['crop' => '', 'fy2025_26' => '', 'fy2026_27' => '']];
        }
    @endphp

    <style>
        /* Responsive table and form styling */
        .table-responsive {
            overflow-x: auto;
        }
        .table th, .table td {
            font-size: 0.85rem; /* Smaller font size for better mobile display */
            padding: 0.5rem; /* Reduced padding */
            vertical-align: middle;
        }
        .form-control {
            font-size: 0.85rem; /* Smaller input font size */
            padding: 0.3rem 0.5rem; /* Reduced input padding */
            height: auto;
        }
        .btn-sm {
            font-size: 0.75rem; /* Smaller button font size */
            padding: 0.3rem 0.5rem; /* Smaller button padding */
        }
        @media (max-width: 576px) {
            .table th, .table td {
                font-size: 0.75rem; /* Even smaller font for mobile */
            }
            .form-control {
                font-size: 0.75rem;
            }
            .btn-sm {
                font-size: 0.7rem;
                padding: 0.2rem 0.4rem;
            }
        }
    </style>

    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th class="form-label fw-normal small" style="width: 40%;">Crop *</th>
                    <th class="form-label fw-normal small" style="width: 25%;">FY 2025-26 (MT) *</th>
                    <th class="form-label fw-normal small" style="width: 25%;">FY 2026-27 (MT) *</th>
                    <th class="form-label fw-normal small" style="width: 10%;">Action</th>

                </tr>
            </thead>
            <tbody id="business-plan-body">
                @foreach($businessPlans as $index => $plan)
                <tr class="business-plan-row">
                    <td>
                        <select class="form-control" name="business_plans[{{ $index }}][crop]" required>
                            <option value="">Select Crop</option>
                            @foreach($crops as $crop)
                                <option value="{{ $crop->crop_name }}"
                                    {{ old('business_plans.' . $index . '.crop', $plan['crop']) == $crop->crop_name ? 'selected' : '' }}>
                                    {{ $crop->crop_name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control"
                               name="business_plans[{{ $index }}][fy2025_26]"
                               value="{{ old('business_plans.' . $index . '.fy2025_26', $plan['fy2025_26']) }}"
                               min="0" step="0.01" required>
                    </td>
                    <td>
                        <input type="number" class="form-control"
                               name="business_plans[{{ $index }}][fy2026_27]"
                               value="{{ old('business_plans.' . $index . '.fy2026_27', $plan['fy2026_27']) }}"
                               min="0" step="0.01" required>
                    </td>
                    <td>
                        @if($index > 0)
                        <button type="button" class="btn btn-sm btn-danger remove-business-plan">
                           <i class="ri-delete-bin-line"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <button type="button" class="btn btn-sm btn-primary add-business-plan mt-2" style="margin-left:4px;">
        <i class="fas fa-plus"></i> Add Crop
    </button>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        let businessPlanIndex = {{ count($businessPlans) }};

        // Add business plan row
        $('.add-business-plan').click(function() {
            const newRow = `
            <tr class="business-plan-row">
                <td>
                    <select class="form-control" name="business_plans[${businessPlanIndex}][crop]" required>
                        <option value="">Select Crop</option>
                        @foreach($crops as $crop)
                            <option value="{{ $crop->crop_name }}">{{ $crop->crop_name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control" name="business_plans[${businessPlanIndex}][fy2025_26]" min="0" step="0.01" required>
                </td>
                <td>
                    <input type="number" class="form-control" name="business_plans[${businessPlanIndex}][fy2026_27]" min="0" step="0.01" required>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-business-plan">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>`;

            $('#business-plan-body').append(newRow);
            businessPlanIndex++;
        });

        // Remove business plan row
        $(document).on('click', '.remove-business-plan', function() {
            if ($('.business-plan-row').length > 1) {
                $(this).closest('.business-plan-row').remove();
            } else {
                alert('At least one business plan is required.');
            }
        });
    });
</script>
@endpush