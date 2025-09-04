@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card" style="font-size: 0.9rem;">
                <div class="card-header d-flex justify-content-between align-items-center p-2">
                    <h5 class="mb-0" style="font-size: 1rem;">Vendor Application: {{ $vendor->company_name }}</h5>
                    <div class="badge bg-{{ $vendor->is_completed ? 'success' : 'warning' }} text-white" style="font-size: 0.75rem;">
                        {{ $vendor->is_completed ? 'Completed' : 'In Progress (Step '.$vendor->current_step.')' }}
                    </div>
                </div>
                <div class="card-body p-3">
                    @if(session('success'))
                    <div class="alert alert-success" style="font-size: 0.70rem; padding: 0.5rem;">{{ session('success') }}</div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light p-2">
                                    <h5 class="mb-0" style="font-size: 0.95rem;">Company Information</h5>
                                </div>
                                <div class="card-body p-2">
                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>Company Name:</strong> {{ $vendor->company_name }}</p>
                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>Nature of Business:</strong> {{ $vendor->nature_of_business }}</p>
                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>Purpose of Transaction:</strong> {{ $vendor->purpose_of_transaction }}</p>
                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>Legal Status:</strong> {{ $vendor->legal_status }}</p>
                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>Address:</strong><br>
                                        {{ $vendor->company_address }},<br>
                                        {{ $vendor->company_city }},
                                        {{ $vendor->state->state_name ?? 'N/A' }} - {{ $vendor->pincode }}
                                    </p>
                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>GST Number:</strong> {{ $vendor->gst_number ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light p-2">
                                    <h5 class="mb-0" style="font-size: 0.95rem;">Contact Information</h5>
                                </div>
                                <div class="card-body p-2">
                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>Contact Person:</strong> {{ $vendor->contact_person_name }}</p>
                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>Email:</strong> {{ $vendor->vendor_email }}</p>
                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>Phone:</strong> {{ $vendor->contact_number }}</p>
                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>VNR Contact Person:</strong> {{ $vendor->vnrContactPerson->emp_name ?? 'N/A' }}</p>
                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>Payment Terms:</strong> {{ $vendor->payment_terms }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light p-2">
                                    <h5 class="mb-0" style="font-size: 0.95rem;">Legal Documents</h5>
                                </div>
                                <div class="card-body p-2">
                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>PAN Number:</strong> {{ $vendor->pan_number }}</p>
                                    @if($vendor->pan_card_copy_path)
                                    <p class="mb-1">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary view-document"
                                            data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'pan_card']) }}"
                                            style="font-size: 0.8rem; padding: 0.2rem 0.5rem;">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                    </p>
                                    @endif

                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>Aadhar Number:</strong> {{ $vendor->aadhar_number }}</p>
                                    @if($vendor->aadhar_card_copy_path)
                                    <p class="mb-1">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary view-document"
                                            data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'aadhar_card']) }}"
                                            style="font-size: 0.8rem; padding: 0.2rem 0.5rem;">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                    </p>
                                    @endif

                                    @if($vendor->gst_number)
                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>GST Certificate:</strong></p>
                                    @if($vendor->gst_certificate_copy_path)
                                    <p class="mb-1">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary view-document"
                                            data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'gst_certificate']) }}"
                                            style="font-size: 0.8rem; padding: 0.2rem 0.5rem;">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                    </p>
                                    @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light p-2">
                                    <h5 class="mb-0" style="font-size: 0.95rem;">Banking Information</h5>
                                </div>
                                <div class="card-body p-2">
                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>Account Holder:</strong> {{ $vendor->bank_account_holder_name }}</p>
                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>Account Number:</strong> {{ $vendor->bank_account_number }}</p>
                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>Bank Name:</strong> {{ $vendor->bank_name }}</p>
                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>IFSC Code:</strong> {{ $vendor->ifsc_code }}</p>
                                    <p class="mb-1" style="font-size: 0.70rem;"><strong>Branch:</strong> {{ $vendor->bank_branch }}</p>

                                    @if($vendor->cancelled_cheque_copy_path)
                                    <p class="mb-1">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary view-document"
                                            data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'cancelled_cheque']) }}"
                                            style="font-size: 0.8rem; padding: 0.2rem 0.5rem;">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Documents Section -->
                    <div class="card mb-3">
                        <div class="card-header bg-light p-2">
                            <h5 class="mb-0" style="font-size: 0.95rem;">Additional Documents</h5>
                        </div>
                        <div class="card-body p-2">
                            @if($vendor->msme_certificate_copy_path)
                            <div class="mb-2">
                                <strong style="font-size: 0.70rem;">MSME Certificate:</strong><br>
                                <button type="button"
                                    class="btn btn-sm btn-outline-primary view-document"
                                    data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'msme_certificate']) }}"
                                    style="font-size: 0.8rem; padding: 0.2rem 0.5rem;">
                                    <i class="ri-eye-line"></i>
                                </button>
                            </div>
                            @endif

                            @if($vendor->agreement_copy_path)
                            <div class="mb-2">
                                <strong style="font-size: 0.70rem;">Agreement Copy:</strong><br>
                                <a href="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'agreement']) }}"
                                    class="btn btn-sm btn-outline-primary" target="_blank"
                                    style="font-size: 0.8rem; padding: 0.2rem 0.5rem;">
                                    <i class="fas fa-download"></i> Download Agreement
                                </a>
                            </div>
                            @endif

                            @if($vendor->other_documents_path)
                            <div class="mb-2">
                                <strong style="font-size: 0.70rem;">Other Documents:</strong><br>
                                <a href="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'other']) }}"
                                    class="btn btn-sm btn-outline-primary" target="_blank"
                                    style="font-size: 0.8rem; padding: 0.2rem 0.5rem;">
                                    <i class="fas fa-download"></i> Download Other Documents
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('vendors.index') }}" class="btn btn-secondary btn-sm" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>

                        <div>
                            @if(!$vendor->is_completed)
                            <a href="{{ route('vendors.edit', $vendor->id) }}" class="btn btn-primary btn-sm" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;">
                                <i class="fas fa-edit"></i> Continue Application
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Preview Modal -->
<div class="modal fade" id="documentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header p-2">
                <h5 class="modal-title" id="documentModalLabel" style="font-size: 0.95rem;">Document Preview</h5>
                <div id="zoomControls" class="ms-2" style="display: none;">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="zoomIn" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;">
                        <i class="ri-zoom-in-line"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="zoomOut" style="font-size: 0.8rem; padding: 0.2rem 0.5rem;">
                        <i class="ri-zoom-out-line"></i>
                    </button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="documentPreviewContent" style="height: 60vh;">
                <!-- Document will be loaded here -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .card-header.bg-light {
        background-color: #f8f9fa !important;
    }

    .btn-outline-primary {
        border-color: #0d6efd;
        color: #0d6efd;
    }

    .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: white;
    }

    #documentPreviewContent {
        height: 60vh;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    #documentPreviewContent iframe,
    #documentPreviewContent img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        display: block;
        transition: transform 0.2s ease;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const previewContainer = document.getElementById('documentPreviewContent');
        const zoomControls = document.getElementById('zoomControls');
        const zoomInBtn = document.getElementById('zoomIn');
        const zoomOutBtn = document.getElementById('zoomOut');
        let currentScale = 1;
        const scaleStep = 0.2;
        const minScale = 0.5;
        const maxScale = 3;

        document.querySelectorAll('.view-document').forEach(function(btn) {
            btn.addEventListener('click', function() {
                let url = this.getAttribute('data-url');

                // Clear old content
                previewContainer.innerHTML = '';
                zoomControls.style.display = 'none';
                currentScale = 1;

                // Show PDF/image inside iframe or img
                if (url.endsWith('.pdf')) {
                    previewContainer.innerHTML = `<iframe src="${url}" frameborder="0" style="width:100%; height:100%;"></iframe>`;
                } else {
                    previewContainer.innerHTML = `<img src="${url}" class="img-fluid" alt="Document" style="transform: scale(${currentScale});">`;
                    zoomControls.style.display = 'inline-flex';
                }

                new bootstrap.Modal(document.getElementById('documentModal')).show();
            });
        });

        zoomInBtn.addEventListener('click', function() {
            if (currentScale < maxScale) {
                currentScale += scaleStep;
                const img = previewContainer.querySelector('img');
                if (img) {
                    img.style.transform = `scale(${currentScale})`;
                }
            }
        });

        zoomOutBtn.addEventListener('click', function() {
            if (currentScale > minScale) {
                currentScale -= scaleStep;
                const img = previewContainer.querySelector('img');
                if (img) {
                    img.style.transform = `scale(${currentScale})`;
                }
            }
        });
    });
</script>
@endpush