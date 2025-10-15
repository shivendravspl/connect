<div class="form-section">
    <h6 class="section-header">Company Information</h6>
 
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="form-group">
                <label for="company_name" class="form-label small">Company Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm mt-1" id="company_name" name="company_name"
                    value="{{ old('company_name', $vendor->company_name ?? '') }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="nature_of_business" class="form-label small">Nature Of Business/Trade <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm form-control mt-1" id="nature_of_business" name="nature_of_business" required>
                    <option value="">Select Nature of Business</option>
                    @foreach(['Goods', 'Service Provider', 'Consultant', 'Contractors', 'Hotel', 'Transport Service', 'Courier', 'Trader', 'Manufacturer', 'Others'] as $option)
                    <option value="{{ $option }}"
                        @if(old('nature_of_business', $vendor->nature_of_business ?? '') == $option) selected @endif>
                        {{ $option }}
                    </option>
                    @endforeach
                </select>
                @error('nature_of_business')
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="form-group">
                <label for="purpose_of_transaction" class="form-label small">Purpose Of Transaction with Company <span class="text-danger">*</span></label>
                <textarea class="form-control form-control-sm mt-1" id="purpose_of_transaction" name="purpose_of_transaction"
                    rows="2" required>{{ old('purpose_of_transaction', $vendor->purpose_of_transaction ?? '') }}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="company_address" class="form-label small">Company Address <span class="text-danger">*</span></label>
                <textarea class="form-control form-control-sm mt-1" id="company_address" name="company_address"
                    rows="2" required>{{ old('company_address', $vendor->company_address ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3 mb-3">
            <div class="form-group">
                <label for="company_state_id" class="form-label small">Company's State <span class="text-danger">*</span></label>
                <select class="form-select form-control-sm mt-1" id="company_state_id" name="company_state_id" required>
                    <option value="">Select State</option>
                    @foreach($states as $state)
                    <option value="{{ $state->id }}"
                        @if(old('company_state_id', $vendor->company_state_id ?? '') == $state->id) selected @endif>
                        {{ $state->state_name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="form-group">
                <label for="company_city" class="form-label small">Company's City <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm mt-1" id="company_city" name="company_city"
                    value="{{ old('company_city', $vendor->company_city ?? '') }}" required>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="form-group">
                <label for="pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                <input type="text" class="form-control mt-1" id="pincode" name="pincode"
                    value="{{ old('pincode', $vendor->pincode ?? '') }}" required>
            </div>
        </div>
    
        <div class="col-md-3 mb-3">
            <div class="form-group">
                <label for="vendor_email" class="form-label small">Vendor Email Id <span class="text-danger">*</span></label>
                <input type="email" class="form-control form-control-sm mt-1" id="vendor_email" name="vendor_email"
                    value="{{ old('vendor_email', $vendor->vendor_email ?? '') }}" required>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="form-group">
                <label for="contact_person_name" class="form-label small">Contact Person Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm mt-1" id="contact_person_name" name="contact_person_name"
                    value="{{ old('contact_person_name', $vendor->contact_person_name ?? '') }}" required>
            </div>
        </div>
   
        <div class="col-md-3 mb-3">
            <div class="form-group">
                <label for="contact_number" class="form-label small">Contact Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm mt-1" id="contact_number" name="contact_number"
                    value="{{ old('contact_number', $vendor->contact_number ?? '') }}" required>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="form-group">
                <label for="vnr_contact_department_id" class="form-label small">VNR Department <span class="text-danger">*</span></label>
                <select class="form-select form-control form-select-sm mt-1" id="vnr_contact_department_id" name="vnr_contact_department_id" required>
                    <option value="">Select Department</option>
                    @foreach($departments as $department)
                    <option value="{{ $department->id }}"
                        @if(old('vnr_contact_department_id', $vendor->vnr_contact_department_id ?? '') == $department->id) selected @endif>
                        {{ $department->department_name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
   
        <div class="col-md-3 mb-3">
            <div class="form-group">
        <label for="vnr_contact_person_id" class="form-label small">VNR Contact Person <span class="text-danger">*</span></label>
        <select id="vnr_contact_person_id" name="vnr_contact_person_id" class="form-select form-control-sm mt-1" required>
            <option value="">Select Employee</option>
            @if(isset($employees) && count($employees))
            @foreach($employees as $employee)
            <option value="{{ $employee->employee_id }}"
                @if(old('vnr_contact_person_id', $vendor->vnr_contact_person_id ?? '') == $employee->employee_id) selected @endif>
                {{ $vendor->vnr_contact_person_id }} - {{ $employee->employee_id }}
            </option>
            @endforeach
            @endif
        </select>
        @error('vnr_contact_person_id')
        <span class="invalid-feedback d-block" role="alert">
            <strong>{{ $message }}</strong>
        </span>
        @enderror
    </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="form-group">
                <label for="payment_terms" class="form-label small">Payment Terms <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm mt-1" id="payment_terms" name="payment_terms"
                    value="{{ old('payment_terms', $vendor->payment_terms ?? '') }}" required>
            </div>
        </div>
    </div>
</div>