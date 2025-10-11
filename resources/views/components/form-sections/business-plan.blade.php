<div id="business-plan" class="form-section">
    @php
        // Get current and next financial years from controller
        $currentFinancialYear = $financialYears->first();
        $nextFinancialYear = $financialYears->last();
        
        $businessPlans = old('business_plans', []);
        
        // If editing existing application
        if(isset($application->businessPlans) && $application->businessPlans->count() > 0) {
            $businessPlans = $application->businessPlans->map(function($plan) {
                return [
                    'crop' => $plan->crop,
                    'current_financial_year_mt' => $plan->current_financial_year_mt ?? '',
                    'current_financial_year_amount' => $plan->current_financial_year_amount ?? '',
                    'next_financial_year_mt' => $plan->next_financial_year_mt ?? '',
                    'next_financial_year_amount' => $plan->next_financial_year_amount ?? ''
                ];
            })->toArray();
        }
        
        // If no existing data, create one empty row
        if(empty($businessPlans)) {
            $businessPlans = [[
                'crop' => '', 
                'current_financial_year_mt' => '', 
                'current_financial_year_amount' => '', 
                'next_financial_year_mt' => '', 
                'next_financial_year_amount' => ''
            ]];
        }
    @endphp
    <style>
        /* Responsive table and form styling */
        .table-responsive {
            overflow-x: auto;
        }
        .table th, .table td {
            font-size: 0.80rem;
            padding: 0.4rem;
            vertical-align: middle;
        }
        .form-control {
            font-size: 0.80rem;
            padding: 0.25rem 0.4rem;
            height: auto;
        }
        .btn-sm {
            font-size: 0.70rem;
            padding: 0.25rem 0.4rem;
        }
        .table th {
            white-space: nowrap;
            text-align: center;
        }
        .sub-header {
            font-size: 0.75rem;
            font-weight: normal;
        }
        @media (max-width: 768px) {
            .table th, .table td {
                font-size: 0.70rem;
                padding: 0.3rem;
            }
            .form-control {
                font-size: 0.70rem;
                padding: 0.2rem 0.3rem;
            }
        }
    </style>

    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th class="form-label fw-normal small" style="width: 20%;">Crop *</th>           
                    <th colspan="2" class="form-label fw-normal small text-center" style="width: 30%;">
                        {{ $currentFinancialYear->period ?? 'Current FY' }}
                    </th>
                    <th colspan="2" class="form-label fw-normal small text-center" style="width: 30%;">
                        {{ $nextFinancialYear->period ?? 'Next FY' }}
                    </th>
                </tr>
                <tr>
                    <th></th>
                    <!-- Current FY Sub-headers -->
                    <th class="form-label fw-normal sub-header" style="width: 15%;">MT *</th>
                    <th class="form-label fw-normal sub-header" style="width: 15%;">Amount *</th>
                    <!-- Next FY Sub-headers -->
                    <th class="form-label fw-normal sub-header" style="width: 15%;">MT *</th>
                    <th class="form-label fw-normal sub-header" style="width: 15%;">Amount *</th>
                    <th></th>
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
                    <!-- Current FY Columns -->
                    <td>
                        <input type="number" class="form-control"
                               name="business_plans[{{ $index }}][current_financial_year_mt]"
                               value="{{ old('business_plans.' . $index . '.current_financial_year_mt', $plan['current_financial_year_mt']) }}"
                               min="0" step="0.01" 
                               placeholder="MT" required>
                    </td>
                    <td>
                        <input type="number" class="form-control"
                               name="business_plans[{{ $index }}][current_financial_year_amount]"
                               value="{{ old('business_plans.' . $index . '.current_financial_year_amount', $plan['current_financial_year_amount']) }}"
                               min="0" step="0.01" 
                               placeholder="Amount" required>
                    </td>
                    <!-- Next FY Columns -->
                    <td>
                        <input type="number" class="form-control"
                               name="business_plans[{{ $index }}][next_financial_year_mt]"
                               value="{{ old('business_plans.' . $index . '.next_financial_year_mt', $plan['next_financial_year_mt']) }}"
                               min="0" step="0.01" 
                               placeholder="MT" required>
                    </td>
                    <td>
                        <input type="number" class="form-control"
                               name="business_plans[{{ $index }}][next_financial_year_amount]"
                               value="{{ old('business_plans.' . $index . '.next_financial_year_amount', $plan['next_financial_year_amount']) }}"
                               min="0" step="0.01" 
                               placeholder="Amount" required>
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
                <!-- Current FY Columns -->
                <td>
                    <input type="number" class="form-control" 
                           name="business_plans[${businessPlanIndex}][current_financial_year_mt]" 
                           min="0" step="0.01" placeholder="MT" required>
                </td>
                <td>
                    <input type="number" class="form-control" 
                           name="business_plans[${businessPlanIndex}][current_financial_year_amount]" 
                           min="0" step="0.01" placeholder="Amount" required>
                </td>
                <!-- Next FY Columns -->
                <td>
                    <input type="number" class="form-control" 
                           name="business_plans[${businessPlanIndex}][next_financial_year_mt]" 
                           min="0" step="0.01" placeholder="MT" required>
                </td>
                <td>
                    <input type="number" class="form-control" 
                           name="business_plans[${businessPlanIndex}][next_financial_year_amount]" 
                           min="0" step="0.01" placeholder="Amount" required>
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