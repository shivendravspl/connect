<div id="bank-details" class="form-section p-2">
    @php
        // **CORRECTED: Fetch bank details directly from entity_details columns**
        $entityDetails = $application->entityDetails;
        $bankDetails = $application->bankDetail ?? null;
        
        // Use entity_details columns first, fallback to bank_detail table
        $bankData = [
            'bank_name' => $entityDetails->bank_name ?? $bankDetails->bank_name ?? '',
            'account_holder_name' => $entityDetails->account_holder_name ?? $bankDetails->account_holder ?? '',
            'account_number' => $entityDetails->account_number ?? $bankDetails->account_number ?? '',
            'ifsc_code' => $entityDetails->ifsc_code ?? $bankDetails->ifsc_code ?? '',
        ];
    @endphp

    <div class="row g-2">
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="financial_status" class="form-label small">Financial Status *</label>
                <select class="form-control form-control-sm" id="financial_status" name="financial_status" required>
                    <option value="">Select Status</option>
                    @foreach(['Good', 'Very Good', 'Excellent', 'Average'] as $status)
                    <option value="{{ $status }}" 
                        {{ old('financial_status', $bankDetails->financial_status ?? '') === $status ? 'selected' : '' }}>
                        {{ $status }}
                    </option>
                    @endforeach
                </select>
                @error('financial_status')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="retailer_count" class="form-label small">No. of Retailers Dealt With *</label>
                <input type="number" 
                       class="form-control form-control-sm" 
                       id="retailer_count" 
                       name="retailer_count" 
                       value="{{ old('retailer_count', $bankDetails->retailer_count ?? '') }}" 
                       min="0" 
                       required>
                @error('retailer_count')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
    <div class="form-group mb-2">
        <label for="bank_details_bank_name" class="form-label small">Bank Name *</label>
        <input type="text" 
               class="form-control form-control-sm" 
               id="bank_details_bank_name" 
               name="bank_name" 
               value="{{ old('bank_name', $bankData['bank_name']) }}" 
               required>
        @error('bank_name')
        <div class="text-danger small">{{ $message }}</div>
        @enderror
        @if($entityDetails && $entityDetails->bank_document_path)
            <small class="text-muted">
                <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" 
                   data-src="{{ Storage::disk('s3')->url('Connect/Distributor/bank/' . $entityDetails->bank_document_path) }}" 
                   data-type="Bank Statement">View Bank Document</a>
            </small>
        @endif
    </div>
</div>
        
      <div class="col-12 col-sm-6 col-md-4 col-lg-3">
        <div class="form-group mb-2">
            <label for="bank_details_account_holder" class="form-label small">Account Holder Name *</label>
            <input type="text" 
                class="form-control form-control-sm" 
                id="bank_details_account_holder" 
                name="account_holder" 
                value="{{ old('account_holder', $bankData['account_holder_name']) }}" 
                required>
            @error('account_holder')
            <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
</div>

    </div>

    <div class="row g-2">
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
    <div class="form-group mb-2">
        <label for="bank_details_account_number" class="form-label small">Account Number *</label>
        <input type="text" 
               class="form-control form-control-sm" 
               id="bank_details_account_number" 
               name="account_number" 
               value="{{ old('account_number', $bankData['account_number']) }}" 
               required>
        @error('account_number')
        <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="col-12 col-sm-6 col-md-4 col-lg-3">
    <div class="form-group mb-2">
        <label for="bank_details_ifsc_code" class="form-label small">IFSC Code *</label>
        <input type="text" 
               class="form-control form-control-sm" 
               id="bank_details_ifsc_code" 
               name="ifsc_code" 
               value="{{ old('ifsc_code', $bankData['ifsc_code']) }}" 
               required>
        @error('ifsc_code')
        <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>
</div>
        
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="account_type" class="form-label small">Account Type *</label>
                <select class="form-control form-control-sm" id="account_type" name="account_type" required>
                    <option value="">Select Type</option>
                    <option value="current" {{ old('account_type', $bankDetails->account_type ?? '') === 'current' ? 'selected' : '' }}>Current</option>
                    <option value="savings" {{ old('account_type', $bankDetails->account_type ?? '') === 'savings' ? 'selected' : '' }}>Savings</option>
                </select>
                @error('account_type')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="relationship_duration" class="form-label small">Relationship Duration (Years) *</label>
                <input type="number" 
                       class="form-control form-control-sm" 
                       id="relationship_duration" 
                       name="relationship_duration" 
                       min="0" 
                       value="{{ old('relationship_duration', $bankDetails->relationship_duration ?? '') }}" 
                       required>
                @error('relationship_duration')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="od_limit" class="form-label small">OD Limit (if any)</label>
                <input type="text" 
                       class="form-control form-control-sm" 
                       id="od_limit" 
                       name="od_limit" 
                       value="{{ old('od_limit', $bankDetails->od_limit ?? '') }}"
                       placeholder="e.g., 5,00,000">
                @error('od_limit')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="form-group mb-2">
                <label for="od_security" class="form-label small">OD Security</label>
                <input type="text" 
                       class="form-control form-control-sm" 
                       id="od_security" 
                       name="od_security" 
                       value="{{ old('od_security', $bankDetails->od_security ?? '') }}"
                       placeholder="e.g., Property, Fixed Deposit">
                @error('od_security')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>     
    </div>    
</div>