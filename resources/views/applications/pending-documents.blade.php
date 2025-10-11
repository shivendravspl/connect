@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <a href="{{ route('applications.index') }}" class="btn btn-outline-secondary btn-sm mb-2 mb-md-0">
                    <i class="ri-arrow-left-line me-1"></i> Back to Applications
                </a>
                <h4 class="mb-0 text-center text-md-start">
                    <i class="ri-file-upload-line text-primary me-2"></i>
                    Pending Documents
                    @if($applications->count() > 0)
                    <span class="badge bg-warning ms-2">{{ $applications->count() }}</span>
                    @endif
                </h4>
            </div>

            @if($applications->isEmpty())
            <!-- Empty State -->
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-6 text-center py-5">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-4">
                            <i class="ri-file-check-line mb-3 text-success" style="font-size: 3rem;"></i>
                            <h5 class="text-success mb-2">{{ $message ?? 'No Pending Documents' }}</h5>
                            <p class="text-muted mb-4">All your applications are up to date. No action required!</p>
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <a href="{{ route('applications.index') }}" class="btn btn-outline-success">
                                    <i class="ri-list-ordered me-1"></i> View Applications
                                </a>
                                <a href="{{ route('applications.create') }}" class="btn btn-primary">
                                    <i class="ri-add-line me-1"></i> Create New
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <!-- Applications Grid -->
            <div class="row g-3 g-lg-4">
                @foreach($applications as $application)
                @php
                $totalFeedback = ($application->checkpoints?->where('status', 'not_verified')->count() ?? 0) +
                ($application->additionalDocs?->where('status', 'pending')->count() ?? 0);
                $allPendingDocs = [];

                // Get all not_verified checkpoints
                if ($application->checkpoints) {
                $pendingCheckpoints = $application->checkpoints->where('status', 'not_verified');

                foreach ($pendingCheckpoints as $cp) {
                $checkpointName = $cp->checkpoint_name;

                // Main Documents
                if (str_starts_with($checkpointName, 'main_document_')) {
                $allPendingDocs[] = [
                'type' => 'main_documents',
                'item_name' => ucwords(str_replace('_', ' ', str_replace('main_document_', '', $checkpointName))),
                'checkpoint_name' => $checkpointName,
                'reason' => $cp->reason ?? 'Document requires re-upload'
                ];
                }
                // Authorized Persons
                elseif (str_starts_with($checkpointName, 'authorized_')) {
                $name = str_replace('_', ' ', str_replace('authorized_', '', $checkpointName));
                $itemName = ucwords(preg_replace('/\d+$/', '', $name)) . ' (Person ' . (preg_match('/\d+$/', $checkpointName, $matches) ? $matches[0] : '') . ')';
                $allPendingDocs[] = [
                'type' => 'authorized_persons',
                'item_name' => $itemName,
                'checkpoint_name' => $checkpointName,
                'reason' => $cp->reason ?? 'Document requires re-upload'
                ];
                }
                // Additional Documents
                elseif (str_starts_with($checkpointName, 'additional_doc_')) {
                $docId = str_replace('additional_doc_', '', $checkpointName);
                $additionalDoc = $application->additionalDocs->where('id', $docId)->first();
                $allPendingDocs[] = [
                'type' => 'additional_documents',
                'item_name' => $additionalDoc ? $additionalDoc->document_name : "Additional Document {$docId}",
                'checkpoint_name' => $checkpointName,
                'reason' => $additionalDoc && $additionalDoc->remark ? $additionalDoc->remark : ($cp->reason ?? 'Document requires re-upload')
                ];
                }
                // Other checkpoints (like entity_details, etc.)
                else {
                $allPendingDocs[] = [
                'type' => 'other_documents',
                'item_name' => ucwords(str_replace('_', ' ', $checkpointName)),
                'checkpoint_name' => $checkpointName,
                'reason' => $cp->reason ?? 'Verification required'
                ];
                }
                }
                }

                // Additional Documents with pending status
                if ($application->additionalDocs) {
                foreach ($application->additionalDocs->where('status', 'pending') as $doc) {
                $checkpointName = "additional_doc_{$doc->id}";
                // Avoid duplicates
                if (!in_array($checkpointName, array_column($allPendingDocs, 'checkpoint_name'))) {
                $allPendingDocs[] = [
                'type' => 'additional_documents',
                'item_name' => $doc->document_name,
                'checkpoint_name' => $checkpointName,
                'reason' => $doc->remark ?? 'Additional document required'
                ];
                }
                }
                }
                @endphp
                <div class="col-12">
                    <div class="card h-100 shadow-sm border-0 position-relative rounded-3">
                        @if($totalFeedback > 0)
                        <span class="position-absolute top-0 end-0 m-2 badge bg-danger rounded-pill px-2 py-1">
                            <i class="ri-fire-line me-1"></i> {{ $totalFeedback }}
                        </span>
                        @endif

                        <div class="card-header bg-white border-bottom py-2 px-3">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="flex-grow-1 me-2">
                                    <h6 class="mb-1 fw-semibold text-truncate" style="max-width: 180px;"
                                        title="{{ $application->entityDetails->establishment_name ?? 'Application' }}">
                                        {{ Str::limit($application->entityDetails->establishment_name ?? 'Application', 20) }}
                                    </h6>
                                    <small class="text-muted d-block mb-1">
                                        <i class="ri-id-card-line me-1"></i> {{ $application->application_code }}
                                    </small>
                                    <small class="text-muted">
                                        <i class="ri-time-line me-1"></i>
                                        {{ $application->mis_rejected_at?->format('M j, Y') ?? 'Pending' }}
                                    </small>
                                </div>
                                <span class="badge bg-warning text-dark px-2 py-1">
                                    {{ $totalFeedback }}
                                </span>
                            </div>
                        </div>

                        <div class="card-body p-3">
                            @if($totalFeedback > 0)
                            <div class="mb-2">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="ri-alert-line text-warning me-2"></i>
                                    <small class="fw-semibold text-muted">Required Documents ({{ $totalFeedback }} file{{ $totalFeedback > 1 ? 's' : '' }})</small>
                                </div>
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="p-2 bg-light rounded-2">
                                            <ul class="list-unstyled mb-2">
                                                @foreach($allPendingDocs as $doc)
                                                <li class="d-flex align-items-center mb-1">
                                                    <i class="ri-file-text-line text-primary me-2"></i>
                                                    <small>
                                                        {{ $doc['item_name'] }} ({{ str_replace('_', ' ', $doc['type']) }})
                                                        <br>
                                                        <span class="text-danger">Reason: {{ $doc['reason'] }}</span>
                                                    </small>
                                                </li>
                                                @endforeach
                                            </ul>
                                            <button class="btn btn-sm btn-outline-primary upload-trigger rounded-pill px-2"
                                                data-app="{{ $application->id }}"
                                                data-all-docs='@json($allPendingDocs)' {{-- Use @json instead of json_encode --}}
                                                data-total-feedback="{{ $totalFeedback }}">
                                                <i class="ri-upload-line me-1"></i> Upload All
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="card-footer bg-transparent border-top py-2 px-3">
                            <div class="d-flex justify-content-between align-items-center text-muted small">
                                <span><i class="ri-user-line me-1"></i> Your application</span>
                                <span class="badge bg-light text-dark">
                                    <i class="ri-eye-line me-1"></i> Awaiting Review
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Upload Modal -->
            <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content rounded-3">
                        <div class="modal-header bg-primary text-white border-0">
                            <h5 class="modal-title" id="uploadModalLabel">
                                <i class="ri-upload-line me-2"></i> Upload Documents
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <form id="uploadForm" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="application_id" id="uploadAppId">
                                <div id="fileInputs" class="row g-3"></div>
                                <div class="col-12 mt-3">
                                    <div class="alert alert-info border-0 p-2 small">
                                        <i class="ri-information-line me-1 text-info"></i>
                                        Upload PDF, JPG, or PNG files (Max 2MB each). All documents are required.
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer border-0 bg-light">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="submitUpload()">
                                <i class="ri-upload-line me-1"></i> Upload Files
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $(document).on('click', '.upload-trigger', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const appId = $(this).data('app');
            const allDocs = $(this).data('all-docs'); // This is a JSON string

            const totalFeedback = $(this).data('total-feedback');


            console.log('Raw allDocs data:', allDocs); // Debug log

            if (!allDocs || !allDocs.length) {
                alert('No pending documents found. Please contact support.');
                return;
            }

            $('#uploadAppId').val(appId);
            $('#fileInputs').empty();

            allDocs.forEach((doc, index) => {
                const fileId = `file-${appId}-${index}`;
                const html = `
                <div class="col-12 col-md-6 mb-3">
                    <label for="${fileId}" class="form-label small fw-semibold">${doc.item_name} (${doc.type.replace('_', ' ')})</label>
                    <div class="input-group input-group-sm">
                        <input type="file" 
                               class="form-control" 
                               id="${fileId}" 
                               name="documents[${index}][file]" 
                               accept=".pdf,.jpg,.jpeg,.png" 
                               required>
                        <span class="input-group-text bg-light">
                            <i class="ri-attachment-line text-muted"></i>
                        </span>
                    </div>
                    <input type="hidden" name="documents[${index}][type]" value="${doc.type}">
                    <input type="hidden" name="documents[${index}][item_name]" value="${doc.item_name}">
                    <input type="hidden" name="documents[${index}][checkpoint_name]" value="${doc.checkpoint_name}">
                    <div class="form-text small mt-1">Reason: ${doc.reason}</div>
                    <div class="form-text small mt-1">Max 2MB - PDF, JPG, PNG</div>
                </div>
            `;
                $('#fileInputs').append(html);
            });

            $('#uploadModal').modal('show');
        });
    });

    function submitUpload() {
        const $form = $('#uploadForm');
        const appId = $('#uploadAppId').val();
        const $btn = $('.btn-primary');

        const fileInputs = $form.find('input[type="file"]');
        const checkpoints = $form.find('input[name*="[checkpoint_name]"]').map(function() {
            return $(this).val();
        }).get();

        // Check if all required documents have a corresponding file
        let missingFiles = [];
        fileInputs.each(function(index) {
            const checkpoint = checkpoints[index];
            const itemName = $form.find(`input[name="documents[${index}][item_name]"]`).val();
            if (!this.files || this.files.length === 0) {
                missingFiles.push(itemName);
            }
        });

        if (missingFiles.length > 0) {
            alert(`Please upload files for all required documents:\n- ${missingFiles.join('\n- ')}`);
            return;
        }

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Uploading...');

        const formData = new FormData($form[0]);

        $.ajax({
            url: `/applications/pending-documents/${appId}/upload`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('Upload successful! MIS will review your documents.');
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        location.reload();
                    }
                } else {
                    alert(response.message || 'Upload failed. Please try again.');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                if (response && response.errors) {
                    let errorMsg = 'Please fix the following:\n';
                    Object.keys(response.errors).forEach(key => {
                        response.errors[key].forEach(msg => {
                            errorMsg += `- ${msg}\n`;
                        });
                    });
                    alert(errorMsg);
                } else if (response && response.message) {
                    alert(response.message);
                } else {
                    alert('An error occurred during upload.');
                }
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="ri-upload-line me-1"></i> Upload Files');
                $('#uploadModal').modal('hide');
            }
        });
    }
</script>

<style>
    .card {
        border-radius: 0.75rem !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        overflow: hidden;
    }

    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1) !important;
    }

    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #e9ecef;
    }

    .card-footer {
        background-color: #f8f9fa;
    }

    .upload-trigger {
        transition: all 0.2s ease;
        font-size: 0.85rem;
        padding: 0.25rem 0.75rem;
    }

    .upload-trigger:hover {
        background-color: #e7f3ff !important;
        border-color: #0d6efd !important;
        transform: translateY(-1px);
    }

    .modal-header {
        background: linear-gradient(135deg, #a8dadc 0%, #f1faee 100%) !important;
        border-bottom: none !important;
    }

    .modal-content {
        border-radius: 0.75rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .form-control:focus {
        border-color: #5a67d8;
        box-shadow: 0 0 0 0.2rem rgba(90, 103, 216, 0.2);
    }

    .badge {
        font-weight: 500;
        padding: 0.4rem 0.6rem;
    }

    h6 {
        font-size: 1rem;
        font-weight: 600;
    }

    small {
        font-size: 0.8rem;
    }

    ul.list-unstyled li {
        font-size: 0.85rem;
    }

    @media (max-width: 576px) {
        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .modal-dialog {
            margin: 0.5rem;
        }

        .card-header h6 {
            font-size: 0.9rem;
        }

        .card-body {
            padding: 0.75rem;
        }
    }
</style>
@endpush
@endsection