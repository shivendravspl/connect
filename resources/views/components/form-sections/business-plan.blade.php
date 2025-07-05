<div id="business-plan" class="form-section">
    <h5 class="mb-4">Business Plan (Next Two Years)</h5>

    @php
        // Get the specific year IDs we need
        $year2025 = App\Models\Year::where('period', '2025-26')->first();
        $year2026 = App\Models\Year::where('period', '2026-27')->first();
        
        $businessPlans = old('business_plans', []);
        
        // If editing existing application
        if(isset($application->businessPlan) && $application->businessPlan->count() > 0) {
            $businessPlans = $application->businessPlan->map(function($plan) use ($year2025, $year2026) {
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

    <table class="table table-bordered">
        <thead>
            <tr>
                <th style="width: 50%;">Crop *</th>
                <th style="width: 20%;">FY 2025-26 (MT) *</th>
                <th style="width: 20%;">FY 2026-27 (MT) *</th>
                <th style="width: 10%;">Action</th>
            </tr>
        </thead>
        <tbody id="business-plan-body">
            @foreach($businessPlans as $index => $plan)
            <tr class="business-plan-row">
                <td>
                    <input type="text" class="form-control"
                           name="business_plans[{{ $index }}][crop]"
                           value="{{ $plan['crop'] ?? '' }}" required>
                </td>
                <td>
                    <input type="number" class="form-control"
                           name="business_plans[{{ $index }}][fy2025_26]"
                           value="{{ $plan['fy2025_26'] ?? '' }}"
                           min="0" step="0.01" required>
                </td>
                <td>
                    <input type="number" class="form-control"
                           name="business_plans[{{ $index }}][fy2026_27]"
                           value="{{ $plan['fy2026_27'] ?? '' }}"
                           min="0" step="0.01" required>
                </td>
                <td>
                    @if($index > 0)
                    <button type="button" class="btn btn-sm btn-danger remove-business-plan">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <button type="button" class="btn btn-sm btn-primary add-business-plan mt-2">
        <i class="fas fa-plus"></i> Add Another Crop
    </button>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Start the index from the number of rows already rendered by PHP
        let businessPlanIndex = {{ count($businessPlans) }};

        // Add business plan row
        $('.add-business-plan').click(function() {
            const newRow = `
            <tr class="business-plan-row">
                <td>
                    <input type="text" class="form-control" name="business_plans[${businessPlanIndex}][crop]" required>
                </td>
                <td>
                    <input type="number" class="form-control" name="business_plans[${businessPlanIndex}][fy2025_26]" min="0" step="0.01" required>
                </td>
                <td>
                    <input type="number" class="form-control" name="business_plans[${businessPlanIndex}][fy2026_27]" min="0" step="0.01" required>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-business-plan">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </td>
            </tr>`;

            // Append the new row to the table body
            $('#business-plan-body').append(newRow);
            businessPlanIndex++;
        });

        // Remove business plan row (this delegation logic is correct and doesn't need changes)
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