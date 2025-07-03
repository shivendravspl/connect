<div id="existing-distributorships" class="form-section">
    <h5 class="mb-4">Existing Distributorships (Agro Inputs) <small class="text-muted">(Leave blank if none)</small></h5>
    
    <div id="distributorship-container">
        @php
        $existingDistributorships = $application->existingDistributorships ?? [];
        $hasExisting = count($existingDistributorships) > 0;
        $errors = $errors ?? session()->get('errors'); // Safely get errors
        @endphp
        
        @if($hasExisting)
            @foreach($existingDistributorships as $index => $distributorship)
            <div class="distributorship-row mb-3">
                <div class="row">
                    <div class="col-md-10">
                        <div class="form-group mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" class="form-control {{ isset($errors) && $errors->has('existing_distributorships.'.$index.'.company_name') ? 'is-invalid' : '' }}" 
                                   name="existing_distributorships[{{ $index }}][company_name]" 
                                   value="{{ old("existing_distributorships.$index.company_name", $distributorship->company_name) }}"
                                   placeholder="Leave blank if no distributorships">
                            @if(isset($errors) && $errors->has('existing_distributorships.'.$index.'.company_name'))
                                <div class="invalid-feedback">{{ $errors->first('existing_distributorships.'.$index.'.company_name') }}</div>
                            @endif
                            <input type="hidden" name="existing_distributorships[{{ $index }}][id]" value="{{ $distributorship->id }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        @if($index > 0)
                        <button type="button" class="btn btn-sm btn-danger remove-distributorship" style="margin-top: 30px;">
                            <i class="fas fa-trash"></i>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <div class="distributorship-row mb-3">
                <div class="row">
                    <div class="col-md-10">
                        <div class="form-group mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" class="form-control {{ isset($errors) && $errors->has('existing_distributorships.0.company_name') ? 'is-invalid' : '' }}" 
                                   name="existing_distributorships[0][company_name]"
                                   value="{{ old('existing_distributorships.0.company_name') }}"
                                   placeholder="Leave blank if no distributorships">
                            @if(isset($errors) && $errors->has('existing_distributorships.0.company_name'))
                                <div class="invalid-feedback">{{ $errors->first('existing_distributorships.0.company_name') }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-2">
                        <!-- No remove button for first row -->
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <button type="button" class="btn btn-sm btn-primary add-distributorship">
        <i class="fas fa-plus"></i> Add Another Company
    </button>
</div>