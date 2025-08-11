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
                    <label class="custom-file-label" for="pan_card_copy">Choose file</label>
                </div>
               @if(isset($vendor) && $vendor->pan_card_copy_path)
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                data-toggle="modal" data-target="#documentModal"
                                data-url="{{ Storage::url($vendor->pan_card_copy_path) }}">
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
                        <a href="{{ Storage::url($vendor->aadhar_card_copy_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View Uploaded</a>
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
                        <a href="{{ Storage::url($vendor->gst_certificate_copy_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View Uploaded</a>
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
                        <a href="{{ Storage::url($vendor->msme_certificate_copy_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">View Uploaded</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="documentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Document Viewer</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <iframe id="documentFrame" src="" style="width:100%; height:500px;" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Show file name when file is selected
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });
});
</script>
@endpush