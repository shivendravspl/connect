<div class="form-section">
    <h4 class="section-header">Legal Information of Vendor</h4>

    <div class="form-group">
        <label>Legal Status *</label>
        <div class="radio-group">
            @foreach(['Sole Proprietorship', 'Partnership', 'LLP', 'Private Limited Company', 'Public Company', 'One Person Company', 'Other'] as $option)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="legal_status"
                    id="legal_{{ $loop->index }}" value="{{ $option }}"
                    @if(old('legal_status', $vendor->legal_status ?? '') == $option) checked @endif required>
                <label class="form-check-label" for="legal_{{ $loop->index }}">{{ $option }}</label>
            </div>
            @endforeach
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="pan_number">PAN Number *</label>
                <input type="text" class="form-control" id="pan_number" name="pan_number"
                    value="{{ old('pan_number', $vendor->pan_number ?? '') }}" required>
                <small class="form-text text-muted">Format: AAAAA9999A</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="pan_card_copy">PAN Card Copy</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="pan_card_copy" name="pan_card_copy">
                </div>
                    @if(isset($vendor) && $vendor->pan_card_copy_path)
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary view-document"
                                data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'pan_card']) }}">
                                View Uploaded
                            </button>
                        </div>
                    @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="aadhar_number">Aadhar Card No. *</label>
                <input type="text" class="form-control" id="aadhar_number" name="aadhar_number"
                    value="{{ old('aadhar_number', $vendor->aadhar_number ?? '') }}" required>
                <small class="form-text text-muted">12-digit number</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="aadhar_card_copy">Aadhar Card Copy</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="aadhar_card_copy" name="aadhar_card_copy">
                    <label class="custom-file-label" for="aadhar_card_copy">Choose file</label>
                </div>
                 @if(isset($vendor) && $vendor->aadhar_card_copy_path)
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary view-document"
                            data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'aadhar_card']) }}">
                            View Uploaded
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="gst_number">GST Number *</label>
                <input type="text" class="form-control" id="gst_number" name="gst_number"
                    value="{{ old('gst_number', $vendor->gst_number ?? '') }}">
                <small class="form-text text-muted">Format: 22AAAAA0000A1Z5</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="gst_certificate_copy">GST Registration Certificate Copy *</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="gst_certificate_copy" name="gst_certificate_copy">
                    <label class="custom-file-label" for="gst_certificate_copy">Choose file</label>
                </div>
                @if(isset($vendor) && $vendor->gst_certificate_copy_path)
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary view-document"
                            data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'gst_certificate']) }}">
                            View Uploaded
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="msme_number">MSME Registration / Udyog Aadhar No. *</label>
                <input type="text" class="form-control" id="msme_number" name="msme_number"
                    value="{{ old('msme_number', $vendor->msme_number ?? '') }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="msme_certificate_copy">Udyam Certificate / Udyog Aadhar Copy *</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="msme_certificate_copy" name="msme_certificate_copy">
                    <label class="custom-file-label" for="msme_certificate_copy">Choose file</label>
                </div>
                @if(isset($vendor) && $vendor->msme_certificate_copy_path)
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary view-document"
                            data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'msme_certificate']) }}">
                            View Uploaded
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>



