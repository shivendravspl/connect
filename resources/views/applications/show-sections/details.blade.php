<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Application Information</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Application Code</dt>
                    <dd class="col-sm-8">{{ $application->application_code }}</dd>
                    
                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-{{ $application->status_badge }}">
                            {{ ucfirst($application->status) }}
                        </span>
                    </dd>
                    
                    <dt class="col-sm-4">Territory</dt>
                    <dd class="col-sm-8">{{ $application->territory }}</dd>
                    
                    <dt class="col-sm-4">Crop Vertical</dt>
                    <dd class="col-sm-8">{{ $application->crop_vertical }}</dd>
                    
                    <dt class="col-sm-4">Zone</dt>
                    <dd class="col-sm-8">{{ $application->zone }}</dd>
                    
                    <dt class="col-sm-4">District</dt>
                    <dd class="col-sm-8">{{ $application->district }}</dd>
                    
                    <dt class="col-sm-4">State</dt>
                    <dd class="col-sm-8">{{ $application->state }}</dd>
                    
                    <dt class="col-sm-4">Submitted On</dt>
                    <dd class="col-sm-8">{{ $application->created_at->format('d-M-Y H:i') }}</dd>
                    
                    <dt class="col-sm-4">Last Updated</dt>
                    <dd class="col-sm-8">{{ $application->updated_at->format('d-M-Y H:i') }}</dd>
                </dl>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Entity Details</h6>
            </div>
            <div class="card-body">
                @if($application->entityDetails)
                <dl class="row mb-0">
                    <dt class="col-sm-4">Establishment Name</dt>
                    <dd class="col-sm-8">{{ $application->entityDetails->establishment_name }}</dd>
                    
                    <dt class="col-sm-4">Entity Type</dt>
                    <dd class="col-sm-8">{{ ucwords(str_replace('_', ' ', $application->entityDetails->entity_type)) }}</dd>
                    
                    <dt class="col-sm-4">Business Address</dt>
                    <dd class="col-sm-8">{{ $application->entityDetails->business_address }}</dd>
                    
                    <dt class="col-sm-4">City</dt>
                    <dd class="col-sm-8">{{ $application->entityDetails->city }}</dd>
                    
                    <dt class="col-sm-4">Pincode</dt>
                    <dd class="col-sm-8">{{ $application->entityDetails->pincode }}</dd>
                    
                    <dt class="col-sm-4">Mobile</dt>
                    <dd class="col-sm-8">{{ $application->entityDetails->mobile }}</dd>
                    
                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">{{ $application->entityDetails->email }}</dd>
                    
                    <dt class="col-sm-4">PAN Number</dt>
                    <dd class="col-sm-8">{{ $application->entityDetails->pan_number }}</dd>
                    
                    @if($application->entityDetails->gst_number)
                    <dt class="col-sm-4">GST Number</dt>
                    <dd class="col-sm-8">{{ $application->entityDetails->gst_number }}</dd>
                    @endif
                    
                    @if($application->entityDetails->seed_license)
                    <dt class="col-sm-4">Seed License</dt>
                    <dd class="col-sm-8">{{ $application->entityDetails->seed_license }}</dd>
                    @endif
                </dl>
                @else
                <div class="alert alert-warning mb-0">Entity details not available</div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- More sections for ownership, bank details, etc. -->