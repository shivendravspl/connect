<div class="form-section">
    <h4 class="section-header">Company Information</h4>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="company_name">Company Name *</label>
                <input type="text" class="form-control" id="company_name" name="company_name"
                    value="{{ old('company_name', $vendor->company_name ?? '') }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label>Nature Of Business/Trade *</label>
                <div class="radio-group @error('nature_of_business') is-invalid @enderror">
                    @foreach(['Goods', 'Service Provider', 'Consultant', 'Contractors', 'Hotel', 'Transport Service', 'Courier', 'Trader', 'Manufacturer', 'Others'] as $option)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="nature_of_business"
                            id="nature_{{ $loop->index }}" value="{{ $option }}"
                            @if(old('nature_of_business', $vendor->nature_of_business ?? '') == $option) checked @endif>
                        <label class="form-check-label" for="nature_{{ $loop->index }}">{{ $option }}</label>
                    </div>
                    @endforeach
                </div>
                @error('nature_of_business')
                <span class="invalid-feedback d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="purpose_of_transaction">Purpose Of Transaction with Company *</label>
                <textarea class="form-control" id="purpose_of_transaction" name="purpose_of_transaction"
                    rows="2" required>{{ old('purpose_of_transaction', $vendor->purpose_of_transaction ?? '') }}</textarea>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="company_address">Company Address *</label>
                <textarea class="form-control" id="company_address" name="company_address"
                    rows="2" required>{{ old('company_address', $vendor->company_address ?? '') }}</textarea>
            </div>
        </div>
    </div>




    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="company_state_id">Company's State *</label>
                <select class="form-control" id="company_state_id" name="company_state_id" required>
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

        <div class="col-md-4">
            <div class="form-group">
                <label for="company_city">Company's City *</label>
                <input type="text" class="form-control" id="company_city" name="company_city"
                    value="{{ old('company_city', $vendor->company_city ?? '') }}" required>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="pincode">Pincode *</label>
                <input type="text" class="form-control" id="pincode" name="pincode"
                    value="{{ old('pincode', $vendor->pincode ?? '') }}" required>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="vendor_email">Vendor Email Id *</label>
                <input type="email" class="form-control" id="vendor_email" name="vendor_email"
                    value="{{ old('vendor_email', $vendor->vendor_email ?? '') }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="contact_person_name">Contact Person Name *</label>
                <input type="text" class="form-control" id="contact_person_name" name="contact_person_name"
                    value="{{ old('contact_person_name', $vendor->contact_person_name ?? '') }}" required>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="contact_number">Contact Number *</label>
                <input type="text" class="form-control" id="contact_number" name="contact_number"
                    value="{{ old('contact_number', $vendor->contact_number ?? '') }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="vnr_contact_department_id">VNR Department *</label>
                <select class="form-control select2" id="vnr_contact_department_id" name="vnr_contact_department_id" required>
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
    </div>

    <div class="row">

        <div class="col-md-6">
            <div class="form-group">
                <label for="vnr_contact_person_id">VNR Contact Person *</label>
                <select id="vnr_contact_person_id" name="vnr_contact_person_id" class="form-control select2">
                    @if(isset($vendor->vnr_contact_person_id) && isset($employees) && count($employees))
                    @foreach($employees as $employee)
                    <option value="{{ $employee->id }}"
                        @if($vendor->vnr_contact_person_id == $employee->id) selected @endif>
                        {{ $employee->name }}
                    </option>
                    @endforeach
                    @else
                    <option value="">Select Employee</option>
                    @endif
                </select>
            </div>
        </div>


        <div class="col-md-6">
            <div class="form-group">
                <label for="payment_terms">Payment Terms *</label>
                <input type="text" class="form-control" id="payment_terms" name="payment_terms"
                    value="{{ old('payment_terms', $vendor->payment_terms ?? '') }}" required>
            </div>
        </div>

    </div>

</div>