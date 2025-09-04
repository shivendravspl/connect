<div class="form-section">
    <h6 class="section-header">Legal Information of Vendor</h6>

    <div class="row mb-3">
        <div class="form-group">
            <label class="form-label small" for="legal_status">Legal Status *</label>
            <select class="form-control form-control-sm" id="legal_status" name="legal_status" required>
                <option value="">Select Legal Status</option>
                @foreach(['Sole Proprietorship', 'Partnership', 'LLP', 'Private Limited Company', 'Public Company', 'One Person Company', 'Other'] as $option)
                <option value="{{ $option }}"
                    @if(old('legal_status', $vendor->legal_status ?? '') == $option) selected @endif>
                    {{ $option }}
                </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label small" for="pan_number">PAN Number *</label>
                <input type="text" class="form-control form-control-sm" id="pan_number" name="pan_number"
                    value="{{ old('pan_number', $vendor->pan_number ?? '') }}" required>
                <small class="form-text text-muted">Format: AAAAA9999A</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="pan_card_copy" class="form-label small">PAN Card Copy</label>
                <div class="input-group">
                    <input type="file" class="form-control form-control-sm" id="pan_card_copy" name="pan_card_copy">
                    @if(isset($vendor) && $vendor->pan_card_copy_path)
                    <button type="button" class="btn btn-sm btn-outline-primary view-document"
                        data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'pan_card']) }}">
                        <i class="ri-eye-line"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="aadhar_number" class="form-label small">Aadhar Card No. *</label>
                <input type="text" class="form-control form-control-sm" id="aadhar_number" name="aadhar_number"
                    value="{{ old('aadhar_number', $vendor->aadhar_number ?? '') }}" required>
                <small class="form-text text-muted">12-digit number</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="aadhar_card_copy" class="form-label small">Aadhar Card Copy</label>
                <div class="input-group">
                    <input type="file" class="form-control form-control-sm" id="aadhar_card_copy" name="aadhar_card_copy">
                    @if(isset($vendor) && $vendor->aadhar_card_copy_path)
                    <button type="button" class="btn btn-sm btn-outline-primary view-document"
                        data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'aadhar_card']) }}">
                        <i class="ri-eye-line"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="gst_number" class="form-label small">GST Number *</label>
                <input type="text" class="form-control form-control-sm" id="gst_number" name="gst_number"
                    value="{{ old('gst_number', $vendor->gst_number ?? '') }}">
                <small class="form-text text-muted">Format: 22AAAAA0000A1Z5</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="gst_certificate_copy" class="form-label small">GST Registration Certificate Copy *</label>
                <div class="input-group">
                    <input type="file" class="form-control" id="gst_certificate_copy" name="gst_certificate_copy">
                    @if(isset($vendor) && $vendor->gst_certificate_copy_path)
                    <button type="button" class="btn btn-sm btn-outline-primary view-document"
                        data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'gst_certificate']) }}">
                        <i class="ri-eye-line"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="msme_number" class="form-label small">MSME Registration / Udyog Aadhar No. *</label>
                <input type="text" class="form-control form-control-sm" id="msme_number" name="msme_number"
                    value="{{ old('msme_number', $vendor->msme_number ?? '') }}">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="msme_certificate_copy" class="form-label small">Udyam Certificate / Udyog Aadhar Copy *</label>
                <div class="input-group">
                    <input type="file" class="form-control form-control-sm" id="msme_certificate_copy" name="msme_certificate_copy">
                    @if(isset($vendor) && $vendor->msme_certificate_copy_path)
                    <button type="button" class="btn btn-sm btn-outline-primary view-document"
                        data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'msme_certificate']) }}">
                        <i class="ri-eye-line"></i>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>