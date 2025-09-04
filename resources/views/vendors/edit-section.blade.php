@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Edit {{ ucfirst($section) }} Information</h6>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('store.section', $vendor->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="section" value="{{ $section }}">

                        @if($section === 'company')
                            <div class="mb-3">
                                <label for="company_name" class="form-label">Company Name</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" value="{{ old('company_name', $vendor->company_name) }}" required>
                                @error('company_name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="nature_of_business" class="form-label">Nature of Business</label>
                                <input type="text" class="form-control" id="nature_of_business" name="nature_of_business" value="{{ old('nature_of_business', $vendor->nature_of_business) }}" required>
                                @error('nature_of_business') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="purpose_of_transaction" class="form-label">Purpose of Transaction</label>
                                <textarea class="form-control" id="purpose_of_transaction" name="purpose_of_transaction" required>{{ old('purpose_of_transaction', $vendor->purpose_of_transaction) }}</textarea>
                                @error('purpose_of_transaction') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="company_address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="company_address" name="company_address" value="{{ old('company_address', $vendor->company_address) }}" required>
                                @error('company_address') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="company_state_id" class="form-label">State</label>
                                <select class="form-control" id="company_state_id" name="company_state_id" required>
                                    <option value="">Select State</option>
                                    @foreach($states as $state)
                                        <option value="{{ $state->id }}" {{ old('company_state_id', $vendor->company_state_id) == $state->id ? 'selected' : '' }}>{{ $state->state_name }}</option>
                                    @endforeach
                                </select>
                                @error('company_state_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="company_city" class="form-label">City</label>
                                <input type="text" class="form-control" id="company_city" name="company_city" value="{{ old('company_city', $vendor->company_city) }}" required>
                                @error('company_city') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="pincode" class="form-label">Pincode</label>
                                <input type="text" class="form-control" id="pincode" name="pincode" value="{{ old('pincode', $vendor->pincode) }}" required>
                                @error('pincode') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="gst_number" class="form-label">GST Number</label>
                                <input type="text" class="form-control" id="gst_number" name="gst_number" value="{{ old('gst_number', $vendor->gst_number) }}">
                                @error('gst_number') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @elseif($section === 'contact')
                            <div class="mb-3">
                                <label for="vendor_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="vendor_email" name="vendor_email" value="{{ old('vendor_email', $vendor->vendor_email) }}" required>
                                @error('vendor_email') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="contact_person_name" class="form-label">Contact Person Name</label>
                                <input type="text" class="form-control" id="contact_person_name" name="contact_person_name" value="{{ old('contact_person_name', $vendor->contact_person_name) }}" required>
                                @error('contact_person_name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="contact_number" class="form-label">Contact Number</label>
                                <input type="text" class="form-control" id="contact_number" name="contact_number" value="{{ old('contact_number', $vendor->contact_number) }}" required>
                                @error('contact_number') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="vnr_contact_department_id" class="form-label">Department</label>
                                <select class="form-control" id="vnr_contact_department_id" name="vnr_contact_department_id" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ old('vnr_contact_department_id', $vendor->vnr_contact_department_id) == $department->id ? 'selected' : '' }}>{{ $department->department_name }}</option>
                                    @endforeach
                                </select>
                                @error('vnr_contact_department_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="vnr_contact_person_id" class="form-label">Contact Person</label>
                                <select class="form-control" id="vnr_contact_person_id" name="vnr_contact_person_id" required>
                                    <option value="">Select Contact Person</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ old('vnr_contact_person_id', $vendor->vnr_contact_person_id) == $employee->id ? 'selected' : '' }}>{{ $employee->emp_name }}</option>
                                    @endforeach
                                </select>
                                @error('vnr_contact_person_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="payment_terms" class="form-label">Payment Terms</label>
                                <input type="text" class="form-control" id="payment_terms" name="payment_terms" value="{{ old('payment_terms', $vendor->payment_terms) }}" required>
                                @error('payment_terms') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @elseif($section === 'legal')
                            <div class="mb-3">
                                <label for="legal_status" class="form-label">Legal Status</label>
                                <input type="text" class="form-control" id="legal_status" name="legal_status" value="{{ old('legal_status', $vendor->legal_status) }}" required>
                                @error('legal_status') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="pan_number" class="form-label">PAN Number</label>
                                <input type="text" class="form-control" id="pan_number" name="pan_number" value="{{ old('pan_number', $vendor->pan_number) }}" required>
                                @error('pan_number') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="pan_card_copy" class="form-label">PAN Card Copy</label>
                                <input type="file" class="form-control" id="pan_card_copy" name="pan_card_copy">
                                @if($vendor->pan_card_copy_path)
                                    <a href="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'pan_card']) }}" target="_blank">View Current</a>
                                @endif
                                @error('pan_card_copy') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="aadhar_number" class="form-label">Aadhar Number</label>
                                <input type="text" class="form-control" id="aadhar_number" name="aadhar_number" value="{{ old('aadhar_number', $vendor->aadhar_number) }}" required>
                                @error('aadhar_number') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="aadhar_card_copy" class="form-label">Aadhar Card Copy</label>
                                <input type="file" class="form-control" id="aadhar_card_copy" name="aadhar_card_copy">
                                @if($vendor->aadhar_card_copy_path)
                                    <a href="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'aadhar_card']) }}" target="_blank">View Current</a>
                                @endif
                                @error('aadhar_card_copy') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="gst_certificate_copy" class="form-label">GST Certificate Copy</label>
                                <input type="file" class="form-control" id="gst_certificate_copy" name="gst_certificate_copy">
                                @if($vendor->gst_certificate_copy_path)
                                    <a href="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'gst_certificate']) }}" target="_blank">View Current</a>
                                @endif
                                @error('gst_certificate_copy') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="msme_number" class="form-label">MSME Number</label>
                                <input type="text" class="form-control" id="msme_number" name="msme_number" value="{{ old('msme_number', $vendor->msme_number) }}">
                                @error('msme_number') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="msme_certificate_copy" class="form-label">MSME Certificate Copy</label>
                                <input type="file" class="form-control" id="msme_certificate_copy" name="msme_certificate_copy">
                                @if($vendor->msme_certificate_copy_path)
                                    <a href="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'msme_certificate']) }}" target="_blank">View Current</a>
                                @endif
                                @error('msme_certificate_copy') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @elseif($section === 'banking')
                            <div class="mb-3">
                                <label for="bank_account_holder_name" class="form-label">Account Holder Name</label>
                                <input type="text" class="form-control" id="bank_account_holder_name" name="bank_account_holder_name" value="{{ old('bank_account_holder_name', $vendor->bank_account_holder_name) }}" required>
                                @error('bank_account_holder_name') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="bank_account_number" class="form-label">Account Number</label>
                                <input type="text" class="form-control" id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number', $vendor->bank_account_number) }}" required>
                                @error('bank_account_number') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="ifsc_code" class="form-label">IFSC Code</label>
                                <input type="text" class="form-control" id="ifsc_code" name="ifsc_code" value="{{ old('ifsc_code', $vendor->ifsc_code) }}" required>
                                @error('ifsc_code') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="bank_branch" class="form-label">Bank Branch</label>
                                <input type="text" class="form-control" id="bank_branch" name="bank_branch" value="{{ old('bank_branch', $vendor->bank_branch) }}" required>
                                @error('bank_branch') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="cancelled_cheque_copy" class="form-label">Cancelled Cheque Copy</label>
                                <input type="file" class="form-control" id="cancelled_cheque_copy" name="cancelled_cheque_copy">
                                @if($vendor->cancelled_cheque_copy_path)
                                    <a href="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'cancelled_cheque']) }}" target="_blank">View Current</a>
                                @endif
                                @error('cancelled_cheque_copy') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @elseif($section === 'additional')
                            <div class="mb-3">
                                <label for="agreement_copy" class="form-label">Agreement Copy</label>
                                <input type="file" class="form-control" id="agreement_copy" name="agreement_copy">
                                @if($vendor->agreement_copy_path)
                                    <a href="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'agreement']) }}" target="_blank">View Current</a>
                                @endif
                                @error('agreement_copy') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="other_documents" class="form-label">Other Documents</label>
                                <input type="file" class="form-control" id="other_documents" name="other_documents">
                                @if($vendor->other_documents_path)
                                    <a href="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'other']) }}" target="_blank">View Current</a>
                                @endif
                                @error('other_documents') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('vendors.profile') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Submit for Approval</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .app-menu.navbar-menu {
        display: block !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure sidebar is visible
        const sidebar = document.querySelector('.app-menu.navbar-menu');
        if (sidebar) {
            sidebar.classList.add('show');
        }

        // Handle department change for employee dropdown
        const departmentSelect = document.getElementById('vnr_contact_department_id');
        if (departmentSelect) {
            departmentSelect.addEventListener('change', function() {
                const departmentId = this.value;
                const employeeSelect = document.getElementById('vnr_contact_person_id');
                if (!departmentId) {
                    employeeSelect.innerHTML = '<option value="">Select Contact Person</option>';
                    return;
                }

                const baseUrl = "{{ route('vendors.employees.by-department', ':departmentId') }}";
                const url = baseUrl.replace(':departmentId', departmentId);

                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        employeeSelect.innerHTML = '<option value="">Select Contact Person</option>';
                        data.forEach(employee => {
                            const option = document.createElement('option');
                            option.value = employee.employee_id;
                            option.textContent = employee.text;
                            employeeSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching employees:', error);
                        employeeSelect.innerHTML = '<option value="">Error loading employees</option>';
                    });
            });
        }
    });
</script>
@endpush
