<div id="financial-info" class="form-section p-2">
    @php
        $financialInfo = $application->financialInfo ?? null;
        $turnover = [];
        if (isset($financialInfo->annual_turnover)) {
            $turnover = json_decode($financialInfo->annual_turnover, true) ?? [];
        }
        $turnover = old('annual_turnover', $turnover);
        
        // Get all validation errors
        $errors = $errors ?? session()->get('errors');
    @endphp

    <div class="row g-2">
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="net_worth" class="form-label small">Net Worth (Previous FY) *</label>
                <input type="number" step="0.01" class="form-control form-control-sm" id="net_worth" name="net_worth" value="{{ old('net_worth', $financialInfo->net_worth ?? '') }}">
                @error('net_worth')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="shop_uom" class="form-label small">Shop Area UOM *</label>
                <select class="form-select form-select-sm" id="shop_uom" name="shop_uom">
                    <option value="">Select UOM</option>
                    <option value="sq_ft" {{ old('shop_uom', $financialInfo->shop_uom ?? '') == 'sq_ft' ? 'selected' : '' }}>Square Feet</option>
                    <option value="sq_m" {{ old('shop_uom', $financialInfo->shop_uom ?? '') == 'sq_m' ? 'selected' : '' }}>Square Meter</option>
                </select>
                @error('shop_uom')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="shop_area" class="form-label small">Shop Area *</label>
                <input type="number" step="0.01" class="form-control form-control-sm" id="shop_area" name="shop_area" value="{{ old('shop_area', $financialInfo->shop_area ?? '') }}" min="0">
                @error('shop_area')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
         <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="shop_ownership" class="form-label small">Shop Ownership *</label>
                <select class="form-select form-select-sm" id="shop_ownership" name="shop_ownership">
                    <option value="">Select Ownership</option>
                    <option value="owned" {{ old('shop_ownership', $financialInfo->shop_ownership ?? '') == 'owned' ? 'selected' : '' }}>Owned</option>
                    <option value="rented" {{ old('shop_ownership', $financialInfo->shop_ownership ?? '') == 'rented' ? 'selected' : '' }}>Rented</option>
                    <option value="lease" {{ old('shop_ownership', $financialInfo->shop_ownership ?? '') == 'lease' ? 'selected' : '' }}>Lease</option>
                </select>
                @error('shop_ownership')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="godown_uom" class="form-label small">Godown UOM *</label>
                <select class="form-select form-control-sm" id="godown_uom" name="godown_uom">
                    <option value="">Select UOM</option>
                    <option value="sq_ft" {{ old('godown_uom', $financialInfo->godown_uom ?? '') == 'sq_ft' ? 'selected' : '' }}>Square Feet</option>
                    <option value="sq_m" {{ old('godown_uom', $financialInfo->godown_uom ?? '') == 'sq_m' ? 'selected' : '' }}>Square Meter</option>
                </select>
                @error('godown_uom')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="godown_area" class="form-label small">Godown Area *</label>
                <input type="number" step="0.01" class="form-control form-control-sm" id="godown_area" name="godown_area" value="{{ old('godown_area', $financialInfo->godown_area ?? '') }}" min="0">
                @error('godown_area')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="godown_ownership" class="form-label small">Godown Ownership *</label>
                <select class="form-select form-select-sm" id="godown_ownership" name="godown_ownership">
                    <option value="">Select Ownership</option>
                    <option value="owned" {{ old('godown_ownership', $financialInfo->godown_ownership ?? '') == 'owned' ? 'selected' : '' }}>Owned</option>
                    <option value="rented" {{ old('godown_ownership', $financialInfo->godown_ownership ?? '') == 'rented' ? 'selected' : '' }}>Rented</option>
                </select>
                @error('godown_ownership')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="years_in_business" class="form-label small">Years in Business *</label>
                <input type="number" class="form-control form-control-sm" id="years_in_business" name="years_in_business" min="0" value="{{ old('years_in_business', $financialInfo->years_in_business ?? '') }}">
                @error('years_in_business')
                <div class="text-danger small">{{ $message }}</div>
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
                                <th style="width: 40%;">Financial Information</th>
                                <th style="width: 60%;">Net Turnover (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($financialYears as $year)
                            @php
                                $yearKey = $year->period;
                                $errorKey = "annual_turnover.amount.{$yearKey}";
                            @endphp
                            <tr>
                                <td>
                                    FY {{ $yearKey }}
                                    <input type="hidden" name="annual_turnover[year][]" value="{{ $yearKey }}">
                                </td>
                                <td>
                                    <input type="number" step="0.01" 
                                           class="form-control {{ $errors->has($errorKey) ? 'is-invalid' : '' }}" 
                                           name="annual_turnover[amount][{{ $yearKey }}]" 
                                           value="{{ old("annual_turnover.amount.{$yearKey}", $turnover[$yearKey] ?? '') }}" 
                                           placeholder="Enter turnover (₹)">
                                    @error($errorKey)
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div id="annual-turnover-error" class="text-danger small mt-1" style="display: none;"></div>
                @error('annual_turnover.amount')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                @error('annual_turnover.year')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

<style>
.text-danger.small {
    font-size: 0.875em;
    color: #dc3545;
    margin-top: 0.5rem;
    display: block;
    padding: 8px 12px;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
    font-weight: 500;
}

.invalid-feedback.d-block {
    display: block !important;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
}

.is-invalid {
    border-color: #dc3545 !important;
}

.table-danger td {
    background-color: #f8d7da !important;
}
</style>
