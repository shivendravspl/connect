<div id="bank-details" class="form-section p-2">
    <h5 class="mb-2 fs-6">Bank Details</h5>

    @php
    $bankDetails = $application->bankDetail ?? null;
    @endphp

    <div class="row g-2">
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="financial_status" class="form-label small">Financial Status *</label>
                <select class="form-control form-control-sm" id="financial_status" name="financial_status" required>
                    <option value="">Select Status</option>
                    @foreach(['Good', 'Very Good', 'Excellent', 'Average'] as $status)
                    <option value="{{ $status }}" 
                        {{ strtolower(old('financial_status', $bankDetails->financial_status ?? '')) === strtolower($status) ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                    @endforeach
                </select>
                @error('financial_status')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="retailer_count" class="form-label small">No. of Retailers Dealt With *</label>
                <input type="number" class="form-control form-control-sm" id="retailer_count" name="retailer_count" value="{{ old('retailer_count', $bankDetails->retailer_count ?? '') }}" min="0" required>
                @error('retailer_count')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
         <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="bank_name" class="form-label small">Bank Name *</label>
                <input type="text" class="form-control form-control-sm" id="bank_name" name="bank_name" value="{{ old('bank_name', $bankDetails->bank_name ?? '') }}" required>
                @error('bank_name')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="account_holder" class="form-label small">Account Holder Name *</label>
                <input type="text" class="form-control form-control-sm" id="account_holder" name="account_holder" value="{{ old('account_holder', $bankDetails->account_holder ?? '') }}" required>
                @error('account_holder')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

    </div>


   

    <div class="row g-2">
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="account_number" class="form-label small">Account Number *</label>
                <input type="text" class="form-control form-control-sm" id="account_number" name="account_number" value="{{ old('account_number', $bankDetails->account_number ?? '') }}" required>
                @error('account_number')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="ifsc_code" class="form-label small">IFSC Code *</label>
                <input type="text" class="form-control form-control-sm" id="ifsc_code" name="ifsc_code" value="{{ old('ifsc_code', $bankDetails->ifsc_code ?? '') }}" required>
                @error('ifsc_code')
                <div class="text-danger small;">{{ $message }}</div>
                @enderror
            </div>
        </div>

         <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="account_type" class="form-label small">Account Type *</label>
                <select class="form-control form-control-sm" id="account_type" name="account_type" required>
                    <option value="">Select Type</option>
                    <option value="current" {{ old('account_type', $bankDetails->account_type ?? '') == 'current' ? 'selected' : '' }}>Current</option>
                    <option value="savings" {{ old('account_type', $bankDetails->account_type ?? '') == 'savings' ? 'selected' : '' }}>Savings</option>
                </select>
                @error('account_type')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="relationship_duration" class="form-label small">Relationship Duration (Years) *</label>
                <input type="number" class="form-control form-control-sm" id="relationship_duration" name="relationship_duration" min="0" value="{{ old('relationship_duration', $bankDetails->relationship_duration ?? '') }}" required>
                @error('relationship_duration')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

    </div>

    <div class="row g-2">
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="od_limit" class="form-label small">OD Limit (if any)</label>
                <input type="text" class="form-control form-control-sm" id="od_limit" name="od_limit" value="{{ old('od_limit', $bankDetails->od_limit ?? '') }}">
                @error('od_climit')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="od_security" class="form-label small">OD Security</label>
                <input type="text" class="form-control form-control-sm" id="od_security" name="od_security" value="{{ old('od_security', $bankDetails->od_security ?? '') }}">
                @error('od_security')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>


</div>