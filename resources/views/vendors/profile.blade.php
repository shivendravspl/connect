@extends('layouts.app')

@section('content')
<div class="container container-tight py-2">
    <div class="row justify-content-center g-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <h3 class="mb-0 fs-5">Vendor Profile: {{ $vendor->company_name }}</h3>
                </div>
                <div class="card-body p-2">
                    @if(session('success'))
                    <div class="alert alert-success mb-2 py-1 px-2">{{ session('success') }}</div>
                    @endif
                    @if(session('info'))
                    <div class="alert alert-info mb-2 py-1 px-2">{{ session('info') }}</div>
                    @endif
                    @if($errors->any())
                    <div class="alert alert-danger mb-2 py-1 px-2">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- First Row - Company, Contact, VNR Contact -->
                    <div class="row mb-2 g-2">
                        <div class="col-lg-4 col-12">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center py-1 px-2">
                                    <h5 class="mb-0 fs-6">Company Information</h5>
                                    <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#companyModal">
                                        <i class="ri-pencil-fill ri-sm"></i>
                                    </a>
                                </div>
                                <div class="card-body p-2 d-flex flex-column">
                                    <div class="flex-grow-1">
                                        <p class="mb-1 small"><strong>Company Name:</strong> {{ $vendor->company_name }}</p>
                                        <p class="mb-1 small"><strong>Nature of Business:</strong> {{ $vendor->nature_of_business }}</p>
                                        <p class="mb-1 small"><strong>Purpose of Transaction:</strong> {{ $vendor->purpose_of_transaction }}</p>
                                        <p class="mb-1 small"><strong>Legal Status:</strong> {{ $vendor->legal_status }}</p>
                                        <p class="mb-1 small"><strong>Address:</strong><br>
                                            {{ $vendor->company_address }},<br>
                                            {{ $vendor->company_city }}, {{ $vendor->state->state_name ?? 'N/A' }} - {{ $vendor->pincode }}
                                        </p>
                                        <p class="mb-1 small"><strong>GST Number:</strong> {{ $vendor->gst_number ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-12">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center py-1 px-2">
                                    <h5 class="mb-0 fs-6">Contact Information</h5>
                                    <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#contactModal">
                                        <i class="ri-pencil-fill ri-sm"></i>
                                    </a>
                                </div>
                                <div class="card-body p-2 d-flex flex-column">
                                    <div class="flex-grow-1">
                                        <p class="mb-1 small"><strong>Contact Person:</strong> {{ $vendor->contact_person_name }}</p>
                                        <p class="mb-1 small"><strong>Email:</strong> {{ $vendor->vendor_email }}</p>
                                        <p class="mb-1 small"><strong>Phone:</strong> {{ $vendor->contact_number }}</p>
                                        <p class="mb-1 small"><strong>Payment Terms:</strong> {{ $vendor->payment_terms }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-12">
                            <div class="card h-100">
                                <div class="card-header py-1 px-2">
                                    <h5 class="mb-0 fs-6">VNR Contact Person</h5>
                                </div>
                                <div class="card-body p-2 d-flex flex-column">
                                    <div class="flex-grow-1">
                                        <p class="mb-1 small"><strong>VNR Contact Person:</strong> {{ $vendor->vnrContactPerson->emp_name ?? 'N/A' }}</p>
                                        <p class="mb-1 small"><strong>Department:</strong> {{ $vendor->department->department_name ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Second Row - Banking and Legal Documents -->
                    <div class="row mb-2 g-2">
                        <div class="col-lg-6 col-12">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center py-1 px-2">
                                    <h5 class="mb-0 fs-6">Banking Information</h5>
                                    <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#bankingModal">
                                        <i class="ri-pencil-fill ri-sm"></i>
                                    </a>
                                </div>
                                <div class="card-body p-2 d-flex flex-column">
                                    <div class="flex-grow-1">
                                        <p class="mb-1 small"><strong>Account Holder:</strong> {{ $vendor->bank_account_holder_name }}</p>
                                        <p class="mb-1 small"><strong>Account Number:</strong> {{ $vendor->bank_account_number }}</p>
                                        <p class="mb-1 small"><strong>Bank Name:</strong> {{ $vendor->bank_name ?? 'N/A' }}</p>
                                        <p class="mb-1 small"><strong>IFSC Code:</strong> {{ $vendor->ifsc_code }}</p>
                                        <p class="mb-1 small"><strong>Branch:</strong> {{ $vendor->bank_branch }}</p>
                                        @if($vendor->cancelled_cheque_copy_path)
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <p class="mb-0 small"><strong>Cancelled Cheque:</strong></p>
                                            <div>
                                                <button type="button" class="btn btn-xs btn-outline-primary view-document p-0 px-1"
                                                    data-url="{{ Storage::url($vendor->cancelled_cheque_copy_path) }}">
                                                    <i class="ri-eye-fill ri-xs"></i>
                                                </button>
                                                <a href="#" class="text-primary ms-1 edit-document"
                                                    data-type="cancelled_cheque"
                                                    data-title="Edit Cancelled Cheque"
                                                    data-input-name="cancelled_cheque_copy"
                                                    data-current-url="{{ Storage::url($vendor->cancelled_cheque_copy_path) }}">
                                                    <i class="ri-pencil-fill ri-xs"></i>
                                                </a>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-12">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center py-1 px-2">
                                    <h5 class="mb-0 fs-6">Legal Documents</h5>
                                </div>
                                <div class="card-body p-2 d-flex flex-column">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <p class="mb-0 small"><strong>PAN Number:</strong> {{ $vendor->pan_number }}</p>
                                            @if($vendor->pan_card_copy_path)
                                            <div>
                                                <button type="button" class="btn btn-xs btn-outline-primary view-document p-0 px-1"
                                                    data-url="{{ Storage::url($vendor->pan_card_copy_path) }}">
                                                    <i class="ri-eye-fill ri-xs"></i>
                                                </button>
                                                <a href="#" class="text-primary ms-1 edit-document"
                                                    data-type="pan_card"
                                                    data-title="Edit PAN Card"
                                                    data-input-name="pan_card_copy"
                                                    data-current-url="{{ Storage::url($vendor->pan_card_copy_path) }}">
                                                    <i class="ri-pencil-fill ri-xs"></i>
                                                </a>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <p class="mb-0 small"><strong>Aadhar Number:</strong> {{ $vendor->aadhar_number }}</p>
                                            @if($vendor->aadhar_card_copy_path)
                                            <div>
                                                <button type="button" class="btn btn-xs btn-outline-primary view-document p-0 px-1"
                                                    data-url="{{ Storage::url($vendor->aadhar_card_copy_path) }}">
                                                    <i class="ri-eye-fill ri-xs"></i>
                                                </button>
                                                <a href="#" class="text-primary ms-1 edit-document"
                                                    data-type="aadhar_card"
                                                    data-title="Edit Aadhar Card"
                                                    data-input-name="aadhar_card_copy"
                                                    data-current-url="{{ Storage::url($vendor->aadhar_card_copy_path) }}">
                                                    <i class="ri-pencil-fill ri-xs"></i>
                                                </a>
                                            </div>
                                            @endif
                                        </div>
                                        @if($vendor->gst_number)
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <p class="mb-0 small"><strong>GST Number:</strong> {{ $vendor->gst_number }}</p>
                                            @if($vendor->gst_certificate_copy_path)
                                            <div>
                                                <button type="button" class="btn btn-xs btn-outline-primary view-document p-0 px-1"
                                                    data-url="{{ Storage::url($vendor->gst_certificate_copy_path) }}">
                                                    <i class="ri-eye-fill ri-xs"></i>
                                                </button>
                                                <a href="#" class="text-primary ms-1 edit-document"
                                                    data-type="gst_certificate"
                                                    data-title="Edit GST Certificate"
                                                    data-input-name="gst_certificate_copy"
                                                    data-current-url="{{ Storage::url($vendor->gst_certificate_copy_path) }}">
                                                    <i class="ri-pencil-fill ri-xs"></i>
                                                </a>
                                            </div>
                                            @endif
                                        </div>
                                        @endif
                                        @if($vendor->msme_number)
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <p class="mb-0 small"><strong>MSME Number:</strong> {{ $vendor->msme_number }}</p>
                                            @if($vendor->msme_certificate_copy_path)
                                            <div>
                                                <button type="button" class="btn btn-xs btn-outline-primary view-document p-0 px-1"
                                                    data-url="{{ Storage::url($vendor->msme_certificate_copy_path) }}">
                                                    <i class="ri-eye-fill ri-xs"></i>
                                                </button>
                                                <a href="#" class="text-primary ms-1 edit-document"
                                                    data-type="msme_certificate"
                                                    data-title="Edit MSME Certificate"
                                                    data-input-name="msme_certificate_copy"
                                                    data-current-url="{{ Storage::url($vendor->msme_certificate_copy_path) }}">
                                                    <i class="ri-pencil-fill ri-xs"></i>
                                                </a>
                                            </div>
                                            @endif
                                        </div>
                                        @endif
                                        @if($vendor->agreement_copy_path)
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <p class="mb-0 small"><strong>Agreement Copy:</strong></p>
                                            <div>
                                                <button type="button" class="btn btn-xs btn-outline-primary view-document p-0 px-1"
                                                    data-url="{{ Storage::url($vendor->agreement_copy_path) }}">
                                                    <i class="ri-eye-fill ri-xs"></i>
                                                </button>
                                                <a href="#" class="text-primary ms-1 edit-document"
                                                    data-type="agreement"
                                                    data-title="Edit Agreement Copy"
                                                    data-input-name="agreement_copy"
                                                    data-current-url="{{ Storage::url($vendor->agreement_copy_path) }}">
                                                    <i class="ri-pencil-fill ri-xs"></i>
                                                </a>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    {{--<div class="d-flex justify-content-start mt-2">
                        <a href="{{ route('vendors.index') }}" class="btn btn-sm btn-secondary py-0 px-2">
                            <i class="ri-arrow-left-line ri-sm"></i> Back to List
                        </a>
                    </div>--}}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals for Editing Sections -->
<!-- Company Modal -->
<div class="modal fade" id="companyModal" tabindex="-1" aria-labelledby="companyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header py-1 px-2">
                <h5 class="modal-title fs-6" id="companyModalLabel">Edit Company Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <form action="{{ route('vendors.store.section', $vendor->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="section" value="company">
                    <div class="mb-2">
                        <label for="company_name" class="form-label small">Company Name</label>
                        <input type="text" class="form-control form-control-sm" id="company_name" name="company_name" value="{{ old('company_name', $vendor->company_name) }}">
                        @error('company_name') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-2">
                        <label for="nature_of_business" class="form-label small">Nature of Business</label>
                        <input type="text" class="form-control form-control-sm" id="nature_of_business" name="nature_of_business" value="{{ old('nature_of_business', $vendor->nature_of_business) }}">
                        @error('nature_of_business') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-2">
                        <label for="purpose_of_transaction" class="form-label small">Purpose of Transaction</label>
                        <textarea class="form-control form-control-sm" id="purpose_of_transaction" name="purpose_of_transaction" rows="2">{{ old('purpose_of_transaction', $vendor->purpose_of_transaction) }}</textarea>
                        @error('purpose_of_transaction') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-2">
                        <label for="company_address" class="form-label small">Address</label>
                        <input type="text" class="form-control form-control-sm" id="company_address" name="company_address" value="{{ old('company_address', $vendor->company_address) }}">
                        @error('company_address') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-2">
                        <label for="company_state_id" class="form-label small">State</label>
                        <select class="form-control form-control-sm" id="company_state_id" name="company_state_id">
                            <option value="">Select State</option>
                            @foreach($states as $state)
                            <option value="{{ $state->id }}" {{ old('company_state_id', $vendor->company_state_id) == $state->id ? 'selected' : '' }}>{{ $state->state_name }}</option>
                            @endforeach
                        </select>
                        @error('company_state_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-2">
                        <label for="company_city" class="form-label small">City</label>
                        <input type="text" class="form-control form-control-sm" id="company_city" name="company_city" value="{{ old('company_city', $vendor->company_city) }}">
                        @error('company_city') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-2">
                        <label for="pincode" class="form-label small">Pincode</label>
                        <input type="text" class="form-control form-control-sm" id="pincode" name="pincode" value="{{ old('pincode', $vendor->pincode) }}">
                        @error('pincode') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-2">
                        <label for="gst_number" class="form-label small">GST Number</label>
                        <input type="text" class="form-control form-control-sm" id="gst_number" name="gst_number" value="{{ old('gst_number', $vendor->gst_number) }}">
                        @error('gst_number') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="modal-footer py-1 px-2">
                        <button type="button" class="btn btn-sm btn-secondary py-0 px-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary py-0 px-2">Submit for Approval</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header py-1 px-2">
                <h5 class="modal-title fs-6" id="contactModalLabel">Edit Contact Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <form action="{{ route('vendors.store.section', $vendor->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="section" value="contact">
                    <div class="mb-2">
                        <label for="vendor_email" class="form-label small">Email</label>
                        <input type="email" class="form-control form-control-sm" id="vendor_email" name="vendor_email" value="{{ old('vendor_email', $vendor->vendor_email) }}">
                        @error('vendor_email') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-2">
                        <label for="contact_person_name" class="form-label small">Contact Person Name</label>
                        <input type="text" class="form-control form-control-sm" id="contact_person_name" name="contact_person_name" value="{{ old('contact_person_name', $vendor->contact_person_name) }}">
                        @error('contact_person_name') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-2">
                        <label for="contact_number" class="form-label small">Contact Number</label>
                        <input type="text" class="form-control form-control-sm" id="contact_number" name="contact_number" value="{{ old('contact_number', $vendor->contact_number) }}">
                        @error('contact_number') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-2">
                        <label for="payment_terms" class="form-label small">Payment Terms</label>
                        <input type="text" class="form-control form-control-sm" id="payment_terms" name="payment_terms" value="{{ old('payment_terms', $vendor->payment_terms) }}">
                        @error('payment_terms') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="modal-footer py-1 px-2">
                        <button type="button" class="btn btn-sm btn-secondary py-0 px-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary py-0 px-2">Submit for Approval</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Banking Modal -->
<div class="modal fade" id="bankingModal" tabindex="-1" aria-labelledby="bankingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header py-1 px-2">
                <h5 class="modal-title fs-6" id="bankingModalLabel">Edit Banking Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <form action="{{ route('vendors.store.section', $vendor->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="section" value="banking">
                    <div class="mb-2">
                        <label for="bank_account_holder_name" class="form-label small">Account Holder Name</label>
                        <input type="text" class="form-control form-control-sm" id="bank_account_holder_name" name="bank_account_holder_name" value="{{ old('bank_account_holder_name', $vendor->bank_account_holder_name) }}">
                        @error('bank_account_holder_name') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-2">
                        <label for="bank_account_number" class="form-label small">Account Number</label>
                        <input type="text" class="form-control form-control-sm" id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number', $vendor->bank_account_number) }}">
                        @error('bank_account_number') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-2">
                        <label for="ifsc_code" class="form-label small">IFSC Code</label>
                        <input type="text" class="form-control form-control-sm" id="ifsc_code" name="ifsc_code" value="{{ old('ifsc_code', $vendor->ifsc_code) }}">
                        @error('ifsc_code') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-2">
                        <label for="bank_branch" class="form-label small">Bank Branch</label>
                        <input type="text" class="form-control form-control-sm" id="bank_branch" name="bank_branch" value="{{ old('bank_branch', $vendor->bank_branch) }}">
                        @error('bank_branch') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="modal-footer py-1 px-2">
                        <button type="button" class="btn btn-sm btn-secondary py-0 px-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary py-0 px-2">Submit for Approval</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Document Edit Modal -->
<div class="modal fade" id="documentEditModal" tabindex="-1" aria-labelledby="documentEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header py-1 px-2">
                <h5 class="modal-title fs-6" id="documentEditModalLabel">Edit Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <form id="documentEditForm" action="{{ route('vendors.store.section', $vendor->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="section" id="documentSection">
                    <div class="mb-2">
                        <label id="documentLabel" class="form-label small">Document</label>
                        <input type="file" class="form-control form-control-sm" id="documentInput" name="">
                        @error('cancelled_cheque_copy') <span class="text-danger small">{{ $message }}</span> @enderror
                        @error('pan_card_copy') <span class="text-danger small">{{ $message }}</span> @enderror
                        @error('aadhar_card_copy') <span class="text-danger small">{{ $message }}</span> @enderror
                        @error('gst_certificate_copy') <span class="text-danger small">{{ $message }}</span> @enderror
                        @error('msme_certificate_copy') <span class="text-danger small">{{ $message }}</span> @enderror
                        @error('agreement_copy') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="modal-footer py-1 px-2">
                        <button type="button" class="btn btn-sm btn-secondary py-0 px-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary py-0 px-2">Submit for Approval</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Document Preview Modal -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1 px-2">
                <h5 class="modal-title fs-6" id="documentModalLabel">Document Preview</h5>
                <div class="zoom-controls ms-auto me-2">
                    <button type="button" class="btn btn-sm btn-outline-primary zoom-btn" data-action="zoom-in">
                        <i class="ri-zoom-in-line ri-sm"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary zoom-btn" data-action="zoom-out">
                        <i class="ri-zoom-out-line ri-sm"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary zoom-btn" data-action="reset">
                        <i class="ri-restart-line ri-sm"></i>
                    </button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="documentPreviewContent" style="height:60vh; overflow:auto;">
                <!-- Document will be loaded here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .container-tight {
        max-width: 1000px;
        padding-left: 10px;
        padding-right: 10px;
    }

    .app-menu.navbar-menu {
        display: block !important;
    }

    .card-header {
        border-bottom: 1px solid #e9ecef;
    }

    .btn-outline-primary {
        border-color: #0d6efd;
        color: #0d6efd;
        padding: 0.15rem 0.3rem;
    }

    .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: white;
    }

    #documentPreviewContent {
        height: 60vh;
        overflow: auto;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
    }

    #documentPreviewContent iframe,
    #documentPreviewContent img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        display: block;
        transition: transform 0.2s ease;
    }

    .ri-pencil-fill,
    .ri-eye-fill,
    .ri-zoom-in-line,
    .ri-zoom-out-line,
    .ri-restart-line {
        font-size: 0.9rem;
    }

    .ri-xs {
        font-size: 0.8rem;
    }

    p,
    .form-label {
        font-size: 0.85rem;
    }

    .small {
        font-size: 0.8rem;
    }

    .card {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .modal-header {
        padding: 0.25rem 0.5rem;
    }

    .modal-body {
        padding: 0.5rem;
    }

    .modal-footer {
        padding: 0.25rem 0.5rem;
    }

    .btn-sm {
        padding: 0.15rem 0.3rem;
        font-size: 0.8rem;
    }

    .zoom-controls {
        display: flex;
        gap: 5px;
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

        // Initialize zoom state for document preview
        let zoomLevel = 1;
        const zoomStep = 0.2;
        const minZoom = 0.5;
        const maxZoom = 3;

        // Handle document preview
        document.querySelectorAll('.view-document').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('data-url');
                const previewContainer = document.getElementById('documentPreviewContent');
                previewContainer.innerHTML = url.endsWith('.pdf') ?
                    `<iframe src="${url}" frameborder="0" style="width:100%; height:100%;" id="previewIframe"></iframe>` :
                    `<img src="${url}" class="img-fluid" alt="Document" id="previewImage">`;

                // Reset zoom level
                zoomLevel = 1;
                const previewElement = document.getElementById(url.endsWith('.pdf') ? 'previewIframe' : 'previewImage');
                if (previewElement) {
                    previewElement.style.transform = `scale(${zoomLevel})`;
                }

                const modal = new bootstrap.Modal(document.getElementById('documentModal'));
                modal.show();
            });
        });

        // Handle zoom controls
        document.querySelectorAll('.zoom-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const action = this.getAttribute('data-action');
                const previewElement = document.getElementById('previewIframe') || document.getElementById('previewImage');

                if (!previewElement) return;

                if (action === 'zoom-in' && zoomLevel < maxZoom) {
                    zoomLevel += zoomStep;
                } else if (action === 'zoom-out' && zoomLevel > minZoom) {
                    zoomLevel -= zoomStep;
                } else if (action === 'reset') {
                    zoomLevel = 1;
                }

                previewElement.style.transform = `scale(${zoomLevel})`;
            });
        });

        // Handle dynamic document edit modal
        document.querySelectorAll('.edit-document').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const modalEl = document.getElementById('documentEditModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

                const modalTitle = modalEl.querySelector('#documentEditModalLabel');
                const form = modalEl.querySelector('#documentEditForm');
                const sectionInput = modalEl.querySelector('#documentSection');
                const fileInput = modalEl.querySelector('#documentInput');
                const label = modalEl.querySelector('#documentLabel');

                modalTitle.textContent = this.getAttribute('data-title');
                sectionInput.value = this.getAttribute('data-type');
                fileInput.name = this.getAttribute('data-input-name');
                label.textContent = this.getAttribute('data-title').replace('Edit ', '');
                fileInput.value = ''; // Clear file input

                modal.show();
            });
        });

        // Initialize modals
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const target = this.getAttribute('data-bs-target');
                const modalEl = document.querySelector(target);
                if (modalEl) {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.show();
                }
            });
        });
    });
</script>
@endpush