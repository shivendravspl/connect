<div id="financial-info" class="form-section p-2">
    @php
        $financialInfo = $application->financialInfo ?? null;
        // Decode the JSON if it exists, otherwise use empty array
        $turnover = [];
        if (isset($financialInfo->annual_turnover)) {
            $turnover = json_decode($financialInfo->annual_turnover, true) ?? [];
        }
        $turnover = old('annual_turnover', $turnover);
    @endphp

    <div class="row g-2">
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="net_worth" class="form-label small">Net Worth (Previous FY) *</label>
                <input type="number" step="0.01" class="form-control form-control-sm" id="net_worth" name="net_worth" value="{{ old('net_worth', $financialInfo->net_worth ?? '') }}" required>
                @error('net_worth')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="shop_ownership" class="form-label small">Shop Ownership *</label>
                <select class="form-control form-control-sm" id="shop_ownership" name="shop_ownership" required>
                    <option value="">Select Ownership</option>
                    <option value="owned" {{ old('shop_ownership', $financialInfo->shop_ownership ?? '') == 'owned' ? 'selected' : '' }}>Owned</option>
                    <option value="rented" {{ old('shop_ownership', $financialInfo->shop_ownership ?? '') == 'rented' ? 'selected' : '' }}>Rented</option>
                    <option value="lease" {{ old('shop_ownership', $financialInfo->shop_ownership ?? '') == 'lease' ? 'selected' : '' }}>Lease</option>
                </select>
                @error('shop_ownership')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="godown_area" class="form-label small">Godown Area & Ownership *</label>
                <input type="text" class="form-control form-control-sm" id="godown_area" name="godown_area" value="{{ old('godown_area', $financialInfo->godown_area ?? '') }}" required>
                @error('godown_area')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="years_in_business" class="form-label small">Years in Business *</label>
                <input type="number" class="form-control form-control-sm" id="years_in_business" name="years_in_business" min="0" value="{{ old('years_in_business', $financialInfo->years_in_business ?? '') }}" required>
                @error('years_in_business')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-12">
            <div class="form-group mb-2">
                <label class="form-label small">Annual Turnover (Enter at least one year) *</label>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 40%;">Financial Year</th>
                                <th style="width: 60%;">Net Turnover (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($financialYears as $year)
                            <tr>
                                <td>
                                    FY {{ $year->period }}
                                    <input type="hidden" name="annual_turnover[year][]" value="{{ $year->period }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control" name="annual_turnover[amount][{{ $year->period }}]" value="{{ old("annual_turnover.amount.{$year->period}", $turnover[$year->period] ?? '') }}" placeholder="Enter turnover (₹)" aria-describedby="turnover_error_{{ $year->period }}">
                                    @error("annual_turnover.amount.{$year->period}")
                                    <div id="turnover_error_{{ $year->period }}" class="text-danger">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @error('annual_turnover')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

<!-- Existing Distributorships Section (unchanged) -->
<div id="existing-distributorships" class="form-section p-2">
    <h5 class="mb-2 fs-6">Existing Distributorships (Agro Inputs) <small class="text-muted">(Leave blank if none)</small></h5>
    
    <div id="distributorship-container">
        @php
            $existingDistributorships = $application->existingDistributorships ?? [];
            $hasExisting = count($existingDistributorships) > 0;
            $errors = $errors ?? session()->get('errors');
        @endphp
        
        @if($hasExisting)
            @foreach($existingDistributorships as $index => $distributorship)
            <div class="distributorship-row mb-3">
                <div class="row g-2">
                    <div class="col-12">
                        <div class="form-group mb-2">
                            <label class="form-label small">Company Name</label>
                            <input type="text" class="form-control form-control-sm {{ isset($errors) && $errors->has('existing_distributorships.'.$index.'.company_name') ? 'is-invalid' : '' }}" 
                                   name="existing_distributorships[{{ $index }}][company_name]" 
                                   value="{{ old("existing_distributorships.$index.company_name", $distributorship->company_name) }}"
                                   placeholder="Leave blank if no distributorships">
                            @if(isset($errors) && $errors->has('existing_distributorships.'.$index.'.company_name'))
                                <div class="invalid-feedback">{{ $errors->first('existing_distributorships.'.$index.'.company_name') }}</div>
                            @endif
                            <input type="hidden" name="existing_distributorships[{{ $index }}][id]" value="{{ $distributorship->id }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        @if($index > 0)
                        <button type="button" class="btn btn-danger btn-sm remove-distributorship" style="margin-top: 30px;">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <div class="distributorship-row mb-2">
                <div class="row g-2">
                    <div class="col-12">
                        <div class="form-group mb-2">
                            <label class="form-label small">Company Name</label>
                            <input type="text" class="form-control form-control-sm {{ isset($errors) && $errors->has('existing_distributorships.0.company_name') ? 'is-invalid' : '' }}" 
                                   name="existing_distributorships[0][company_name]"
                                   value="{{ old('existing_distributorships.0.company_name') }}"
                                   placeholder="Leave blank if no distributorships">
                            @if(isset($errors) && $errors->has('existing_distributorships.0.company_name'))
                                <div class="invalid-feedback">{{ $errors->first('existing_distributorships.0.company_name') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-2">
                        <!-- No remove button for first row -->
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <button type="button" class="btn btn-sm btn-primary add-distributorship">
        <i class="fas fa-plus"></i> Add Another Company
    </button>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Existing distributorships dynamic rows
        $(document).on('click', '.add-distributorship', function() {
            const container = $(this).closest('.form-section').find('#distributorship-container');
            const index = container.find('.distributorship-row').length;
            const newRow = $(`
                <div class="distributorship-row mb-3">
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="form-group mb-2">
                                <label class="form-label small">Company Name</label>
                                <input type="text" class="form-control form-control-sm" name="existing_distributorships[${index}][company_name]" placeholder="Leave blank if no distributorships">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-danger remove-distributorship" style="margin-top: 30px;">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `);
            container.append(newRow);
        });

        $(document).on('click', '.remove-distributorship', function() {
            $(this).closest('.distributorship-row').remove();
        });
    });
</script>
@endpush