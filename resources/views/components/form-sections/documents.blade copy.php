@php
// Get existing documents from the application
$existingDocuments = [];
if (isset($application->documents)) {
    $existingDocuments = $application->documents->pluck('path', 'type')->toArray();
}

// Map document types to input names
$documentTypes = [
    'business_entity' => 'business_entity_proof',
    'ownership' => 'ownership_proof',
    'pan' => 'pan_card',
    'gst' => 'gst_certificate',
    'address' => 'address_proof',
    'bank' => 'bank_proof',
    'photo' => 'photo',
    'shop_photo' => 'shop_photo',
    'seed_license' => 'seed_license',
    'other' => 'other_document'
];
@endphp

<div id="documents" class="form-section step-content" data-step="9">
    <h5 class="mb-4">Documents Upload</h5>

    <div class="alert alert-info">
        <strong>Note:</strong> Please upload clear scanned copies of all required documents. Maximum file size: 2MB. Allowed formats: PDF, JPG, PNG.
    </div>

    <div class="row">
        <!-- Business Entity Proof -->
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="business_entity_proof" class="form-label">Business Entity Proof *</label>
                <div class="file-upload-wrapper">
                    <input type="file" class="form-control required-field" id="business_entity_proof" name="documents[business_entity_proof]" accept=".pdf,.jpg,.jpeg,.png" {{ isset($existingDocuments['business_entity']) ? '' : 'required' }}>
                    <small class="form-text text-muted">e.g., Certificate of Incorporation, Partnership Deed, etc.</small>
                    <div class="file-preview text-muted">
                        @if(isset($existingDocuments['business_entity']))
                            <a href="{{ asset('storage/'.$existingDocuments['business_entity']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                View Uploaded Document
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-document" data-type="business_entity">
                                Remove
                            </button>
                        @else
                            No file chosen
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Ownership Proof -->
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="ownership_proof" class="form-label">Ownership Proof *</label>
                <div class="file-upload-wrapper">
                    <input type="file" class="form-control required-field" id="ownership_proof" name="documents[ownership_proof]" accept=".pdf,.jpg,.jpeg,.png" {{ isset($existingDocuments['ownership']) ? '' : 'required' }}>
                    <small class="form-text text-muted">e.g., Proprietor ID, Partner IDs, Director IDs</small>
                    <div class="file-preview text-muted">
                        @if(isset($existingDocuments['ownership']))
                            <a href="{{ asset('storage/'.$existingDocuments['ownership']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                View Uploaded Document
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-document" data-type="ownership">
                                Remove
                            </button>
                        @else
                            No file chosen
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- PAN Card -->
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="pan_card" class="form-label">PAN Card *</label>
                <div class="file-upload-wrapper">
                    <input type="file" class="form-control required-field" id="pan_card" name="documents[pan_card]" accept=".pdf,.jpg,.jpeg,.png" {{ isset($existingDocuments['pan']) ? '' : 'required' }}>
                    <div class="file-preview text-muted">
                        @if(isset($existingDocuments['pan']))
                            <a href="{{ asset('storage/'.$existingDocuments['pan']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                View Uploaded Document
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-document" data-type="pan">
                                Remove
                            </button>
                        @else
                            No file chosen
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- GST Certificate -->
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="gst_certificate" class="form-label">GST Certificate</label>
                <div class="file-upload-wrapper">
                    <input type="file" class="form-control" id="gst_certificate" name="documents[gst_certificate]" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="file-preview text-muted">
                        @if(isset($existingDocuments['gst']))
                            <a href="{{ asset('storage/'.$existingDocuments['gst']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                View Uploaded Document
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-document" data-type="gst">
                                Remove
                            </button>
                        @else
                            No file chosen
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Address Proof -->
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="address_proof" class="form-label">Address Proof *</label>
                <div class="file-upload-wrapper">
                    <input type="file" class="form-control required-field" id="address_proof" name="documents[address_proof]" accept=".pdf,.jpg,.jpeg,.png" {{ isset($existingDocuments['address']) ? '' : 'required' }}>
                    <small class="form-text text-muted">e.g., Aadhar, Voter ID, Electricity Bill</small>
                    <div class="file-preview text-muted">
                        @if(isset($existingDocuments['address']))
                            <a href="{{ asset('storage/'.$existingDocuments['address']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                View Uploaded Document
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-document" data-type="address">
                                Remove
                            </button>
                        @else
                            No file chosen
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Bank Proof -->
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="bank_proof" class="form-label">Bank Proof *</label>
                <div class="file-upload-wrapper">
                    <input type="file" class="form-control required-field" id="bank_proof" name="documents[bank_proof]" accept=".pdf,.jpg,.jpeg,.png" {{ isset($existingDocuments['bank']) ? '' : 'required' }}>
                    <small class="form-text text-muted">Cancelled cheque or bank statement</small>
                    <div class="file-preview text-muted">
                        @if(isset($existingDocuments['bank']))
                            <a href="{{ asset('storage/'.$existingDocuments['bank']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                View Uploaded Document
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-document" data-type="bank">
                                Remove
                            </button>
                        @else
                            No file chosen
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Seed License -->
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="seed_license" class="form-label">Seed License</label>
                <div class="file-upload-wrapper">
                    <input type="file" class="form-control" id="seed_license" name="documents[seed_license]" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="file-preview text-muted">
                        @if(isset($existingDocuments['seed_license']))
                            <a href="{{ asset('storage/'.$existingDocuments['seed_license']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                View Uploaded Document
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-document" data-type="seed_license">
                                Remove
                            </button>
                        @else
                            No file chosen
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Photo -->
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="photo" class="form-label">Photograph *</label>
                <div class="file-upload-wrapper">
                    <input type="file" class="form-control required-field" id="photo" name="documents[photo]" accept=".jpg,.jpeg,.png" {{ isset($existingDocuments['photo']) ? '' : 'required' }}>
                    <small class="form-text text-muted">Passport size photograph</small>
                    <div class="file-preview text-muted">
                        @if(isset($existingDocuments['photo']))
                            <a href="{{ asset('storage/'.$existingDocuments['photo']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                View Uploaded Document
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-document" data-type="photo">
                                Remove
                            </button>
                        @else
                            No file chosen
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Shop Photo -->
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="shop_photo" class="form-label">Shop Photograph *</label>
                <div class="file-upload-wrapper">
                    <input type="file" class="form-control required-field" id="shop_photo" name="documents[shop_photo]" accept=".jpg,.jpeg,.png" {{ isset($existingDocuments['shop_photo']) ? '' : 'required' }}>
                    <div class="file-preview text-muted">
                        @if(isset($existingDocuments['shop_photo']))
                            <a href="{{ asset('storage/'.$existingDocuments['shop_photo']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                View Uploaded Document
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-document" data-type="shop_photo">
                                Remove
                            </button>
                        @else
                            No file chosen
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Other Document -->
        <div class="col-md-6">
            <div class="form-group mb-3">
                <label for="other_document" class="form-label">Other Relevant Document</label>
                <div class="file-upload-wrapper">
                    <input type="file" class="form-control" id="other_document" name="documents[other_document]" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="file-preview text-muted">
                        @if(isset($existingDocuments['other']))
                            <a href="{{ asset('storage/'.$existingDocuments['other']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                View Uploaded Document
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-document" data-type="other">
                                Remove
                            </button>
                        @else
                            No file chosen
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>