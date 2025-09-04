<div class="form-section">
    <h6 class="section-header">Banking Information Of Vendor</h6>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="bank_account_holder_name" class="form-label small">Bank Account Holder's Name *</label>
                <input type="text" class="form-control form-control-sm" id="bank_account_holder_name" name="bank_account_holder_name" 
                       value="{{ old('bank_account_holder_name', $vendor->bank_account_holder_name ?? '') }}" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="bank_account_number" class="form-label small">Bank Account Number *</label>
                <input type="text" class="form-control form-control-sm" id="bank_account_number" name="bank_account_number" 
                       value="{{ old('bank_account_number', $vendor->bank_account_number ?? '') }}" required>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="ifsc_code" class="form-label small">IFSC Code *</label>
                <input type="text" class="form-control form-control-sm" id="ifsc_code" name="ifsc_code" 
                       value="{{ old('ifsc_code', $vendor->ifsc_code ?? '') }}" required>
                <small class="form-text text-muted">Format: ABCD0123456</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="bank_branch" class="form-label small">Bank Branch *</label>
                <input type="text" class="form-control form-control-sm" id="bank_branch" name="bank_branch" 
                       value="{{ old('bank_branch', $vendor->bank_branch ?? '') }}" required>
            </div>
        </div>
    </div>
    
  

     <div class="form-group">
                <label for="cancelled_cheque_copy" class="form-label small">Cancelled Check Copy/Passbook Front Page *</label>
                <div class="input-group">
                    <input type="file" class="form-control form-control-sm" id="cancelled_cheque_copy" name="cancelled_cheque_copy">
                    @if(isset($vendor) && $vendor->cancelled_cheque_copy_path)
                    <button type="button" class="btn btn-sm btn-outline-primary view-document"
                        data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'cancelled_cheque']) }}">
                        <i class="ri-eye-line"></i>
                    </button>
                    @endif
                </div>
     </div>
</div>

