@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Vendor Application: {{ $vendor->company_name }}</h3>
                    <div class="badge bg-{{ $vendor->is_completed ? 'success' : 'warning' }} text-white">
                        {{ $vendor->is_completed ? 'Completed' : 'In Progress (Step '.$vendor->current_step.')' }}
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Company Information</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Company Name:</strong> {{ $vendor->company_name }}</p>
                                    <p><strong>Nature of Business:</strong> {{ $vendor->nature_of_business }}</p>
                                    <p><strong>Purpose of Transaction:</strong> {{ $vendor->purpose_of_transaction }}</p>
                                    <p><strong>Legal Status:</strong> {{ $vendor->legal_status }}</p>
                                    <p><strong>Address:</strong><br>
                                        {{ $vendor->company_address }},<br>
                                        {{ $vendor->company_city }},
                                        {{ $vendor->state->state_name ?? 'N/A' }} - {{ $vendor->pincode }}
                                    </p>
                                    <p><strong>GST Number:</strong> {{ $vendor->gst_number ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Contact Information</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Contact Person:</strong> {{ $vendor->contact_person_name }}</p>
                                    <p><strong>Email:</strong> {{ $vendor->vendor_email }}</p>
                                    <p><strong>Phone:</strong> {{ $vendor->contact_number }}</p>
                                    <p><strong>VNR Contact Person:</strong> {{ $vendor->vnrContactPerson->emp_name ?? 'N/A' }}</p>
                                    <p><strong>Payment Terms:</strong> {{ $vendor->payment_terms }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Legal Documents</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>PAN Number:</strong> {{ $vendor->pan_number }}</p>
                                    @if($vendor->pan_card_copy_path)
                                    <p>
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary view-document"
                                            data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'pan_card']) }}">
                                            <i class="fas fa-eye"></i> View PAN Card
                                        </button>
                                    </p>
                                    @endif

                                    <p><strong>Aadhar Number:</strong> {{ $vendor->aadhar_number }}</p>
                                    @if($vendor->aadhar_card_copy_path)
                                    <p>
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary view-document"
                                            data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'aadhar_card']) }}">
                                            <i class="fas fa-eye"></i> View Aadhar Card
                                        </button>
                                    </p>
                                    @endif

                                    @if($vendor->gst_number)
                                    <p><strong>GST Certificate:</strong></p>
                                    @if($vendor->gst_certificate_copy_path)
                                    <p>
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary view-document"
                                            data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'gst_certificate']) }}">
                                            <i class="fas fa-eye"></i> View GST Certificate
                                        </button>
                                    </p>
                                    @endif
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Banking Information</h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Account Holder:</strong> {{ $vendor->bank_account_holder_name }}</p>
                                    <p><strong>Account Number:</strong> {{ $vendor->bank_account_number }}</p>
                                    <p><strong>Bank Name:</strong> {{ $vendor->bank_name }}</p>
                                    <p><strong>IFSC Code:</strong> {{ $vendor->ifsc_code }}</p>
                                    <p><strong>Branch:</strong> {{ $vendor->bank_branch }}</p>

                                    @if($vendor->cancelled_cheque_copy_path)
                                    <p>
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary view-document"
                                            data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'cancelled_cheque']) }}">
                                            <i class="fas fa-eye"></i> View Bank Document
                                        </button>
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Documents Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Additional Documents</h5>
                        </div>
                        <div class="card-body">
                            @if($vendor->msme_certificate_copy_path)
                            <div class="mb-3">
                                <strong>MSME Certificate:</strong><br>
                                <button type="button"
                                    class="btn btn-sm btn-outline-primary view-document"
                                    data-url="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'msme_certificate']) }}">
                                    <i class="fas fa-eye"></i> View Msme Document
                                </button>
                            </div>
                            @endif

                            @if($vendor->agreement_copy_path)
                            <div class="mb-3">
                                <strong>Agreement Copy:</strong><br>
                                <a href="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'agreement']) }}"
                                    class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-download"></i> Download Agreement
                                </a>
                            </div>
                            @endif

                            @if($vendor->other_documents_path)
                            <div class="mb-3">
                                <strong>Other Documents:</strong><br>
                                <a href="{{ route('vendors.documents.show', ['id' => $vendor->id, 'type' => 'other']) }}"
                                    class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-download"></i> Download Other Documents
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('vendors.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>

                        <div>
                            @if(!$vendor->is_completed)
                            <a href="{{ route('vendors.edit', $vendor->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Continue Application
                            </a>
                            @endif

                            <!-- Add any other conditional buttons here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Preview Modal -->
<div class="modal fade" id="documentModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentModalLabel">Document Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body  p-0" id="documentPreviewContent" style="height:80vh;">
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
        height: 80vh;
        overflow: hidden;
        /* hide overflow outside container */
        display: flex;
        justify-content: center;
        align-items: center;
    }

    #documentPreviewContent iframe,
    #documentPreviewContent img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        /* prevents stretching */
        display: block;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.view-document').forEach(function(btn) {
            btn.addEventListener('click', function() {
                let url = this.getAttribute('data-url');
                let previewContainer = document.getElementById('documentPreviewContent');

                // Clear old content
                previewContainer.innerHTML = '';

                // Show PDF/image inside iframe or img
                if (url.endsWith('.pdf')) {
                    previewContainer.innerHTML = `<iframe src="${url}" frameborder="0" style="width:100%; height:100%;"></iframe>`;
                } else {
                    previewContainer.innerHTML = `<img src="${url}" class="img-fluid" alt="Document">`;
                }

                new bootstrap.Modal(document.getElementById('documentModal')).show();
            });
        });
    });
</script>
@endpush