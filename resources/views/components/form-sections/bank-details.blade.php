<div id="bank-details" class="form-section">
    <h5 class="mb-4">Bank Details</h5>

    @php
    $bankDetails = $application->bankDetail ?? null;
    @endphp

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="financial_status" class="form-label">Financial Status *</label>
                <select class="form-control" id="financial_status" name="financial_status" required>
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
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="retailer_count" class="form-label">No. of Retailers Dealt With *</label>
                <input type="number" class="form-control" id="retailer_count" name="retailer_count" value="{{ old('retailer_count', $bankDetails->retailer_count ?? '') }}" min="0" required>
                @error('retailer_count')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="bank_name" class="form-label">Bank Name *</label>
                <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{ old('bank_name', $bankDetails->bank_name ?? '') }}" required>
                @error('bank_name')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="account_holder" class="form-label">Account Holder Name *</label>
                <input type="text" class="form-control" id="account_holder" name="account_holder" value="{{ old('account_holder', $bankDetails->account_holder ?? '') }}" required>
                @error('account_holder')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="account_number" class="form-label">Account Number *</label>
                <input type="text" class="form-control" id="account_number" name="account_number" value="{{ old('account_number', $bankDetails->account_number ?? '') }}" required>
                @error('account_number')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="ifsc_code" class="form-label">IFSC Code *</label>
                <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" value="{{ old('ifsc_code', $bankDetails->ifsc_code ?? '') }}" required>
                @error('ifsc_code')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="account_type" class="form-label">Account Type *</label>
                <select class="form-control" id="account_type" name="account_type" required>
                    <option value="">Select Type</option>
                    <option value="current" {{ old('account_type', $bankDetails->account_type ?? '') == 'current' ? 'selected' : '' }}>Current</option>
                    <option value="savings" {{ old('account_type', $bankDetails->account_type ?? '') == 'savings' ? 'selected' : '' }}>Savings</option>
                </select>
                @error('account_type')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="relationship_duration" class="form-label">Relationship Duration (Years) *</label>
                <input type="number" class="form-control" id="relationship_duration" name="relationship_duration" min="0" value="{{ old('relationship_duration', $bankDetails->relationship_duration ?? '') }}" required>
                @error('relationship_duration')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="od_limit" class="form-label">OD Limit (if any)</label>
                <input type="text" class="form-control" id="od_limit" name="od_limit" value="{{ old('od_limit', $bankDetails->od_limit ?? '') }}">
                @error('od_climit')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="od_security" class="form-label">OD Security</label>
                <input type="text" class="form-control" id="od_security" name="od_security" value="{{ old('od_security', $bankDetails->od_security ?? '') }}">
                @error('od_security')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>


</div>