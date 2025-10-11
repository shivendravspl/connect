@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            <i class="ri-file-text-line me-2"></i> Manage Physical Documents for {{ $application->entityDetails->establishment_name }}
        </h5>
        <a href="{{ route('mis.verification-list') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ri-arrow-left-line me-1"></i> Back to List
        </a>
    </div>

    <div class="card shadow-sm rounded-3">
        <div class="card-header bg-light py-2">
            <h6 class="mb-0"><i class="ri-file-list-line me-2"></i> Physical Document Tracking</h6>
        </div>
        <div class="card-body">
            <form id="physicalDocumentsForm" action="{{ route('approvals.update-physical-documents', $application) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <!-- Date of Receiving Documents -->
                    <div class="col-md-6">
                        <label for="receive_date" class="form-label">Date of Receiving Documents</label>
                        <input type="date" name="receive_date" id="receive_date" class="form-control" value="{{ old('receive_date', $application->physicalDispatch->receive_date ?? '') }}" max="{{ now()->toDateString() }}">
                        <div id="receive_date_error" class="text-danger small mt-1"></div>
                    </div>
                    <!-- Verified Date -->
                    <div class="col-md-6">
                        <label for="verified_date" class="form-label">Verified Date</label>
                        <input type="date" name="verified_date" id="verified_date" class="form-control" value="{{ old('verified_date', now()->toDateString()) }}" readonly>
                        <div id="verified_date_error" class="text-danger small mt-1"></div>
                    </div>
                </div>

                <!-- Physical Documents Table -->
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Document Type</th>
                                <th>Received?</th>
                                <th>Verification Status</th>
                                <th>Upload</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $physicalDocumentTypes = [
                                    'agreement_copy' => 'Agreement Copy',
                                    'security_cheques' => 'Security Cheques', 
                                    'security_deposit' => 'Security Deposit',
                                ];
                                $checkpoints = $application->checkpoints ?? collect([]);
                                $physicalDocumentChecks = $physicalDocumentChecks->groupBy('document_type');
                            @endphp

                            <!-- Standard Physical Documents with Upload -->
                            @foreach($physicalDocumentTypes as $type => $label)
                                @php
                                    $checks = $physicalDocumentChecks->get($type, collect([(object)[
                                        'received' => false,
                                        'status' => 'pending',
                                        'reason' => null,
                                        'file_path' => null,
                                        'original_filename' => null,
                                    ]]));
                                    $check = $checks->first();
                                    $hasFile = $check->file_path ?? false;
                                @endphp
                                <tr>
                                    <td class="align-middle">{{ $label }}</td>
                                    <td class="align-middle">
                                        <input type="checkbox" name="documents[{{ $type }}][received]" class="form-check-input received-checkbox" value="1" {{ old("documents.$type.received", $check->received) ? 'checked' : '' }}>
                                        <div id="documents_{{ $type }}_received_error" class="text-danger small mt-1"></div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="documents[{{ $type }}][status]" class="form-check-input status-radio" value="verified" id="{{ $type }}_verified_yes" {{ old("documents.$type.status", $check->status) === 'verified' ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="{{ $type }}_verified_yes">Verified</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="documents[{{ $type }}][status]" class="form-check-input status-radio" value="not_verified" id="{{ $type }}_verified_no" {{ old("documents.$type.status", $check->status) === 'not_verified' ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="{{ $type }}_verified_no">Not Verified</label>
                                        </div>
                                        <div id="documents_{{ $type }}_status_error" class="text-danger small mt-1"></div>
                                    </td>
                                    <td class="align-middle">
                                        @if($type === 'security_cheques')
                                            <div class="security-cheques-upload">
                                                @foreach($checks as $index => $check)
                                                    @php $hasFileInRow = $check->file_path ?? false; @endphp
                                                    <div class="file-upload-row mb-1 d-flex align-items-center" data-index="{{ $loop->index }}">
                                                        <input type="file" class="form-control form-control-sm me-2 document-upload {{ $hasFileInRow ? 'd-none' : '' }}" data-type="{{ $type }}" data-display-type="Security Cheques" accept=".pdf,.doc,.docx,.jpg,.png" id="file-input-{{ $type }}-{{ $loop->index }}" {{ $hasFileInRow ? 'disabled' : '' }}>
                                                        @if($hasFileInRow)
                                                            <button type="button" class="btn btn-sm btn-warning replace-file-btn me-2" data-row-index="{{ $loop->index }}">Replace</button>
                                                        @endif
                                                        <span class="file-name small me-2" id="file-name-{{ $type }}-{{ $loop->index }}">
                                                            @if($check->file_path)
                                                                <a href="#" class="view-existing" data-type="{{ $type }}" data-filename="{{ $check->file_path }}">View</a> ({{ $check->original_filename ?? basename($check->file_path) }})
                                                            @endif
                                                        </span>
                                                        @if($loop->index > 0)
                                                            <button type="button" class="btn btn-sm btn-danger remove-file-upload"><i class="ri-delete-bin-line"></i></button>
                                                        @endif
                                                        <div class="hidden-file-container d-none">
                                                            @if($check->file_path)
                                                                <input type="hidden" name="existing_{{ $type }}_file[]" value="{{ $check->file_path }}">
                                                                <input type="hidden" name="existing_{{ $type }}_file_original[]" value="{{ $check->original_filename ?? basename($check->file_path) }}">
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                                <button type="button" class="btn btn-sm btn-primary add-file-upload mt-1"><i class="ri-add-circle-line"></i></button>
                                                <div id="documents_security_cheques_files_error" class="text-danger small mt-1"></div>
                                            </div>
                                        @else
                                            <input type="file" class="form-control form-control-sm document-upload {{ $hasFile ? 'd-none' : '' }}" data-type="{{ $type }}" data-display-type="{{ Str::title(str_replace('_', ' ', $label)) }}" accept=".pdf,.doc,.docx,.jpg,.png" id="file-input-{{ $type }}" {{ $hasFile ? 'disabled' : '' }}>
                                            @if($hasFile)
                                                <button type="button" class="btn btn-sm btn-warning replace-file-btn">Replace</button>
                                            @endif
                                            <span class="file-name small" id="file-name-{{ $type }}">
                                                @if($check->file_path)
                                                    <a href="#" class="view-existing" data-type="{{ $type }}" data-filename="{{ $check->file_path }}">View</a> ({{ $check->original_filename ?? basename($check->file_path) }})
                                                @endif
                                            </span>
                                            <div id="documents_{{ $type }}_file_error" class="text-danger small mt-1"></div>
                                            <div class="hidden-file-container d-none">
                                                @if($check->file_path)
                                                    <input type="hidden" name="existing_{{ $type }}_file" value="{{ $check->file_path }}">
                                                    <input type="hidden" name="existing_{{ $type }}_file_original" value="{{ $check->original_filename ?? basename($check->file_path) }}">
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                  <td class="align-middle">
    @if($type === 'security_deposit')
        @php
            $existingAmount = old('security_deposit_amount', $check->amount ?? '');
            $existingReason = old("documents.$type.reason", $check->reason ?? '');
            $currentStatus = old("documents.$type.status", $check->status ?? 'pending');
            
            // Always show amount field, show reason field only when not verified
            $showReasonField = $currentStatus === 'not_verified';
        @endphp
        
        <!-- Always show amount field -->
        <input type="number" 
               name="security_deposit_amount" 
               id="security_deposit_amount" 
               class="form-control form-control-sm mb-1" 
               value="{{ $existingAmount }}" 
               step="0.01" 
               min="0" 
               placeholder="Enter amount">
        
        @if($showReasonField)
            <textarea name="documents[{{ $type }}][reason]" 
                      id="{{ $type }}_reason" 
                      class="form-control form-control-sm" 
                      rows="1" 
                      placeholder="Enter remarks if not verified">{{ $existingReason }}</textarea>
        @endif
        
        <div id="documents_{{ $type }}_reason_error" class="text-danger small mt-1" style="{{ $showReasonField ? '' : 'display: none;' }}"></div>
        <div id="security_deposit_amount_error" class="text-danger small mt-1"></div>
    @else
        <textarea name="documents[{{ $type }}][reason]" id="{{ $type }}_reason" class="form-control form-control-sm" rows="2" placeholder="Enter remarks if not verified">{{ old("documents.$type.reason", $check->reason) }}</textarea>
        <div id="documents_{{ $type }}_reason_error" class="text-danger small mt-1"></div>
    @endif
</td>
                                </tr>
                            @endforeach

                            <!-- Supporting Documents Section (only if paths exist) -->
                            @if($supportingDocuments->isNotEmpty())
                                <tr>
                                    <td colspan="5" class="bg-light"><strong>Supporting Documents (for Verification - Digital Copies)</strong></td>
                                </tr>
                                @foreach($supportingDocuments as $doc)
                                    @php
                                        $type = $doc['type'];
                                        $label = $doc['label'];
                                        $existingPath = $doc['path'];
                                        $checks = $physicalDocumentChecks->get($type, collect([(object)[
                                            'received' => false,
                                            'status' => 'pending',
                                            'reason' => null,
                                            'file_path' => null,
                                            'original_filename' => null,
                                        ]]));
                                        $check = $checks->first();
                                    @endphp
                                    <tr>
                                        <td class="align-middle">{{ $label }} <small class="text-muted">(Digital: {{ $doc['existing_file'] }})</small></td>
                                        <td class="align-middle">
                                            <input type="checkbox" name="documents[{{ $type }}][received]" class="form-check-input" value="1" {{ old("documents.$type.received", $check->received) ? 'checked' : '' }}>
                                            <div id="documents_{{ $type }}_received_error" class="text-danger small mt-1"></div>
                                        </td>
                                        <td class="align-middle">
                                            <div class="form-check form-check-inline">
                                                <input type="radio" name="documents[{{ $type }}][status]" class="form-check-input" value="verified" id="{{ $type }}_verified_yes" {{ old("documents.$type.status", $check->status) === 'verified' ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="{{ $type }}_verified_yes">Verified</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input type="radio" name="documents[{{ $type }}][status]" class="form-check-input" value="not_verified" id="{{ $type }}_verified_no" {{ old("documents.$type.status", $check->status) === 'not_verified' ? 'checked' : '' }}>
                                                <label class="form-check-label small" for="{{ $type }}_verified_no">Not Verified</label>
                                            </div>
                                            <div id="documents_{{ $type }}_status_error" class="text-danger small mt-1"></div>
                                        </td>
                                        <td class="align-middle">
                                            <span class="text-muted small">Digital copy available</span>
                                        </td>
                                        <td class="align-middle">
                                            <textarea name="documents[{{ $type }}][reason]" id="{{ $type }}_reason" class="form-control form-control-sm" rows="2" placeholder="Enter remarks if not verified">{{ old("documents.$type.reason", $check->reason) }}</textarea>
                                            <div id="documents_{{ $type }}_reason_error" class="text-danger small mt-1"></div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif

                            <!-- List of Documents from Checkpoints (NO UPLOAD) -->
                            <tr>
                                <td colspan="5" class="bg-light"><strong>List of Documents</strong></td>
                            </tr>
                            @foreach($checkpoints as $checkpoint)
                                @php
                                    $check = $physicalDocumentChecks->get($checkpoint->checkpoint_name, collect([(object)[
                                        'received' => false,
                                        'status' => 'pending',
                                        'reason' => null,
                                        'file_path' => null,
                                        'original_filename' => null,
                                    ]]))->first();
                                @endphp
                                <tr>
                                    <td class="align-middle">{{ str_replace('_', ' ', ucfirst($checkpoint->checkpoint_name)) }}</td>
                                    <td class="align-middle">
                                        <input type="checkbox" name="documents[{{ $checkpoint->checkpoint_name }}][received]" class="form-check-input" value="1" {{ old("documents.{$checkpoint->checkpoint_name}.received", $check->received) ? 'checked' : '' }}>
                                        <div id="documents_{{ $checkpoint->checkpoint_name }}_received_error" class="text-danger small mt-1"></div>
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="documents[{{ $checkpoint->checkpoint_name }}][status]" class="form-check-input" value="verified" id="{{ $checkpoint->checkpoint_name }}_verified_yes" {{ old("documents.{$checkpoint->checkpoint_name}.status", $check->status) === 'verified' ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="{{ $checkpoint->checkpoint_name }}_verified_yes">Verified</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="documents[{{ $checkpoint->checkpoint_name }}][status]" class="form-check-input" value="not_verified" id="{{ $checkpoint->checkpoint_name }}_verified_no" {{ old("documents.{$checkpoint->checkpoint_name}.status", $check->status) === 'not_verified' ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="{{ $checkpoint->checkpoint_name }}_verified_no">Not Verified</label>
                                        </div>
                                        <div id="documents_{{ $checkpoint->checkpoint_name }}_status_error" class="text-danger small mt-1"></div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="text-muted small">No upload required</span>
                                    </td>
                                    <td class="align-middle">
                                        <textarea name="documents[{{ $checkpoint->checkpoint_name }}][reason]" id="{{ $checkpoint->checkpoint_name }}_reason" class="form-control form-control-sm" rows="2" placeholder="Enter remarks if not verified">{{ old("documents.{$checkpoint->checkpoint_name}.reason", $check->reason) }}</textarea>
                                        <div id="documents_{{ $checkpoint->checkpoint_name }}_reason_error" class="text-danger small mt-1"></div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success btn-sm" id="submitPhysicalDocsBtn">
                        <i class="ri-check-line me-1"></i> Save Physical Document Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Document Modal for Viewing Files -->
    <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentModalLabel">Document Viewer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe id="documentFrame" src="" style="width: 100%; height: 500px;"></iframe>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card {
            border-radius: 0.75rem !important;
            transition: all 0.2s ease;
        }
        .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        }
        .table-sm th, .table-sm td {
            padding: 0.4rem 0.5rem;
            font-size: 0.85rem;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }
        .form-control-sm {
            font-size: 0.85rem;
        }
        .text-primary, .file-name a {
            font-size: 0.8rem;
            color: #0d6efd;
        }
        .text-danger {
            font-size: 0.75rem;
        }
        .text-muted {
            font-size: 0.8rem;
        }
        .form-check-inline {
            margin-right: 0.5rem;
        }
        .file-upload-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .add-file-upload, .remove-file-upload, .replace-file-btn {
            padding: 0.2rem 0.5rem;
        }
        @media (max-width: 576px) {
            .table-responsive {
                font-size: 0.75rem;
            }
            .form-check-inline {
                display: block;
                margin-right: 0;
                margin-bottom: 0.25rem;
            }
            .file-upload-row {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        // S3 URL Constructor
        function getS3Url(type, filename) {
            return `https://s3.ap-south-1.amazonaws.com/developerinvnr.bkt/Connect/Distributor/${type}/${filename}`;
        }

        // processDocument function
        async function processDocument(file, fileNameField, existingFile, config) {
            try {
                console.log('processDocument called with config:', config);

                let type = config?.type;
                let displayType = config?.displayType || type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                const endpoint = config?.endpoint || '{{ route("process-document") }}';

                if (!type || !displayType) {
                    console.error('Config missing type or displayType');
                    throw new Error('Invalid document configuration');
                }

                fileNameField.textContent = `Uploading ${displayType.toLowerCase()}...`;
                fileNameField.classList.remove('d-none', 'text-danger');

                const formData = new FormData();
                formData.append(type, file);
                if (existingFile) {
                    formData.append(`existing_${type}_file`, existingFile);
                }
                formData.append('doc_type', type);

                console.log('Sending to endpoint:', endpoint);

                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: formData,
                });

                console.log('Response status:', response.status);

                const result = await response.json();

                if (!response.ok || result.status !== 'SUCCESS') {
                    throw new Error(result.message || `${displayType} upload failed`);
                }

                const { filename, displayName, url } = result.data;
                const viewUrl = url || getS3Url(type, filename);

                fileNameField.innerHTML = `
                    <a href="#" data-bs-toggle="modal" data-bs-target="#documentModal" data-src="${viewUrl}" data-type="${displayType}">View</a> (${displayName})
                `;

                // Hide file input
                const container = fileNameField.closest('.file-upload-row') || fileNameField.closest('td');
                const fileInput = container.querySelector('.document-upload');
                if (fileInput) {
                    fileInput.classList.add('d-none');
                    fileInput.disabled = true;
                }

                // Hidden inputs for filename
                let input = this; // Since called with .call(input, ...)
                let hiddenContainer = container.querySelector('.hidden-file-container');
                if (!hiddenContainer) {
                    hiddenContainer = document.createElement('div');
                    hiddenContainer.className = 'hidden-file-container d-none';
                    container.appendChild(hiddenContainer);
                }

                hiddenContainer.innerHTML = ''; // Clear existing hidden for this row (replacement)

                if (type !== 'security_cheques') {
                    hiddenContainer.innerHTML = `
                        <input type="hidden" name="existing_${type}_file" value="${filename}">
                        <input type="hidden" name="existing_${type}_file_original" value="${displayName}">
                    `;
                } else {
                    const index = input.closest('.file-upload-row').dataset.index || 0;
                    hiddenContainer.innerHTML = `
                        <input type="hidden" name="existing_${type}_file[]" value="${filename}">
                        <input type="hidden" name="existing_${type}_file_original[]" value="${displayName}">
                    `;
                }

                // Ensure replace button is visible or create it
                let replaceBtn = container.querySelector('.replace-file-btn');
                if (replaceBtn) {
                    replaceBtn.classList.remove('d-none');
                } else {
                    replaceBtn = document.createElement('button');
                    replaceBtn.type = 'button';
                    replaceBtn.className = 'btn btn-sm btn-warning replace-file-btn me-2';
                    replaceBtn.innerHTML = 'Replace';
                    if (container.matches('.file-upload-row')) {
                        replaceBtn.dataset.rowIndex = container.dataset.index;
                    }
                    fileNameField.parentNode.insertBefore(replaceBtn, fileNameField);
                }

                console.log('Upload success, filename stored:', filename);

                return result;
            } catch (error) {
                console.error(`${displayType} upload error:`, error);
                fileNameField.textContent = `Error uploading ${displayType.toLowerCase()} : ${error.message}`;
                fileNameField.classList.add('text-danger');
                throw error;
            }
        }

        // Event Delegation for file changes
        document.addEventListener('change', function(e) {
            if (e.target.matches('.document-upload')) {
                const input = e.target;
                const file = input.files[0];
                if (!file) return;

                const type = input.dataset.type || 'unknown';
                const displayType = input.dataset.displayType || type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                const index = input.closest('.file-upload-row') ? input.closest('.file-upload-row').dataset.index : '';
                const fileNameField = document.getElementById(`file-name-${type}${index ? '-' + index : ''}`);
                const existingFileInput = input.closest('.file-upload-row')?.querySelector(`input[name="existing_${type}_file[]"]`) || input.closest('td')?.querySelector(`input[name="existing_${type}_file"]`);
                const existingFile = existingFileInput ? existingFileInput.value : '';

                console.log('Change event triggered for:', type, displayType);

                processDocument.call(input, file, fileNameField, existingFile, {
                    type: type,
                    displayType: displayType,
                    endpoint: '{{ route("process-document") }}'
                }).catch(error => {
                    // Handled in function
                });
            }
        });

        // Handle Replace buttons
        document.addEventListener('click', function(e) {
            if (e.target.matches('.replace-file-btn')) {
                const container = e.target.closest('.file-upload-row') || e.target.closest('td');
                const fileInput = container.querySelector('.document-upload');
                const fileNameField = container.querySelector('.file-name');
                const hiddenContainer = container.querySelector('.hidden-file-container');
                const replaceBtn = container.querySelector('.replace-file-btn');

                // Clear current file display and hidden
                if (fileNameField) fileNameField.innerHTML = '';
                if (hiddenContainer) hiddenContainer.innerHTML = '';

                // Show file input and hide replace btn
                if (fileInput) {
                    fileInput.classList.remove('d-none');
                    fileInput.disabled = false;
                }
                if (replaceBtn) replaceBtn.classList.add('d-none');
            }
        });

        // Handle clicks on existing "View" links
        document.addEventListener('click', function(e) {
            if (e.target.matches('.view-existing')) {
                e.preventDefault();
                const type = e.target.dataset.type;
                const filename = e.target.dataset.filename;
                if (type && filename) {
                    const src = getS3Url(type, filename);
                    const displayType = e.target.dataset.displayType || type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    document.getElementById('documentFrame').src = src;
                    document.getElementById('documentModalLabel').textContent = `Viewing ${displayType}`;
                    const modalElement = document.getElementById('documentModal');
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            }
        });

        // Add file upload field for security cheques
        document.addEventListener('click', function(e) {
            if (e.target.closest('.add-file-upload')) {
                const container = document.querySelector('.security-cheques-upload');
                const index = container.querySelectorAll('.file-upload-row').length;
                const newRow = document.createElement('div');
                newRow.className = 'file-upload-row mb-1 d-flex align-items-center';
                newRow.dataset.index = index;
                newRow.innerHTML = `
                    <input type="file" class="form-control form-control-sm me-2 document-upload" data-type="security_cheques" data-display-type="Security Cheques" accept=".pdf,.doc,.docx,.jpg,.png">
                    <span class="file-name small me-2" id="file-name-security_cheques-${index}"></span>
                    <button type="button" class="btn btn-sm btn-danger remove-file-upload"><i class="ri-delete-bin-line"></i></button>
                    <div class="hidden-file-container d-none"></div>
                `;
                container.insertBefore(newRow, e.target.closest('.add-file-upload'));
            }
        });

        // Remove file upload field
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-file-upload')) {
                const container = document.querySelector('.security-cheques-upload');
                if (container.querySelectorAll('.file-upload-row').length > 1) {
                    e.target.closest('.file-upload-row').remove();
                } else {
                    alert('At least one file upload field is required for Security Cheques.');
                }
            }
        });

        // Form submission
        document.getElementById('physicalDocumentsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('submitPhysicalDocsBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="ri-loader-2-line spinner-border spinner-border-sm me-1"></i>Processing...';
            submitBtn.disabled = true;

            // Clear previous error messages
            document.querySelectorAll('.text-danger').forEach(el => el.innerHTML = '');

            const formData = new FormData(this);
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (response.status === 422) {
                    return response.json().then(data => {
                        throw { status: 422, errors: data.errors, message: data.message };
                    });
                } else if (!response.ok) {
                    throw new Error('An unexpected error occurred');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const successMsg = document.createElement('div');
                    successMsg.className = 'alert alert-success alert-dismissible fade show';
                    successMsg.innerHTML = `
                        <i class="ri-check-line me-2"></i>${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    this.parentNode.insertBefore(successMsg, this);
                    successMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    // Redirect to dashboard after a 2-second delay
                    setTimeout(() => {
                        window.location.href = "{{ route('dashboard') }}";
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Something went wrong');
                }
            })
            .catch(error => {
                if (error.status === 422) {
                    // Display validation errors per field
                    const errorsObj = error.errors;
                    Object.entries(errorsObj).forEach(([field, msgs]) => {
                        const msg = Array.isArray(msgs) ? msgs[0] : msgs;
                        let errorId = null;
                        if (field === 'receive_date') {
                            errorId = 'receive_date_error';
                        } else if (field === 'verified_date') {
                            errorId = 'verified_date_error';
                        } else if (field === 'security_deposit_amount') {
                            errorId = 'security_deposit_amount_error';
                        } else if (field.startsWith('documents.')) {
                            const parts = field.split('.');
                            if (parts.length === 3) {
                                const type = parts[1];
                                const sub = parts[2];
                                if (sub === 'status') {
                                    errorId = `documents_${type}_status_error`;
                                } else if (sub === 'reason') {
                                    errorId = `documents_${type}_reason_error`;
                                } else if (sub === 'received') {
                                    errorId = `documents_${type}_received_error`;
                                }
                            }
                        }
                        const errorEl = document.getElementById(errorId);
                        if (errorEl) {
                            errorEl.textContent = msg;
                        }
                    });
                } else {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
                    alertDiv.innerHTML = `<i class="ri-error-warning-line me-2"></i>${error.message || 'An error occurred'} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
                    this.parentNode.insertBefore(alertDiv, this);
                }
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });

        // Real-time validation for remarks when "Not Verified" is selected
        document.addEventListener('change', function(e) {
            if (e.target.matches('input[name*="status"]')) {
                const type = e.target.name.match(/documents\[([^\]]+)\]\[status\]/)[1];
                const remarkField = document.getElementById(`${type}_reason`);
                const errorElement = document.getElementById(`documents_${type}_reason_error`);
                
                if (e.target.value === 'not_verified' && remarkField && !remarkField.value.trim()) {
                    errorElement.innerHTML = 'Remarks are required when document is not verified';
                } else if (errorElement) {
                    errorElement.innerHTML = '';
                }
            }
        });

        // Validate remarks on input
        document.addEventListener('input', function(e) {
            if (e.target.matches('textarea[name*="reason"]')) {
                const type = e.target.name.match(/documents\[([^\]]+)\]\[reason\]/)[1];
                const errorElement = document.getElementById(`documents_${type}_reason_error`);
                const notVerifiedRadio = document.querySelector(`input[name="documents[${type}][status]"][value="not_verified"]`);
                
                if (notVerifiedRadio && notVerifiedRadio.checked && !e.target.value.trim()) {
                    errorElement.innerHTML = 'Remarks are required when document is not verified';
                } else {
                    errorElement.innerHTML = '';
                }
            }
        });

        // Validate status on change
        document.addEventListener('change', function(e) {
            if (e.target.matches('input[name*="status"]')) {
                const type = e.target.name.match(/documents\[([^\]]+)\]\[status\]/)[1];
                const errorElement = document.getElementById(`documents_${type}_status_error`);
                if (errorElement) {
                    errorElement.innerHTML = '';
                }
            }
        });


        document.addEventListener('change', function(e) {
    if (e.target.matches('input[name*="status"]')) {
        const type = e.target.name.match(/documents\[([^\]]+)\]\[status\]/)[1];
        const errorElement = document.getElementById(`documents_${type}_status_error`);
        if (errorElement) {
            errorElement.innerHTML = '';
        }
    }
});

// ========== ADD SECURITY DEPOSIT RADIO BUTTON HANDLING HERE ==========

// Handle security deposit status changes
document.addEventListener('change', function(e) {
    if (e.target.matches('input[name="documents[security_deposit][status]"]')) {
        const status = e.target.value;
        const reasonField = document.getElementById('security_deposit_reason');
        const amountField = document.getElementById('security_deposit_amount');
        const reasonError = document.getElementById('documents_security_deposit_reason_error');
        const amountError = document.getElementById('security_deposit_amount_error');
        
        if (status === 'verified') {
            // Show amount field, hide reason field
            if (amountField) {
                amountField.style.display = 'block';
                amountField.required = true;
            }
            if (reasonField) {
                reasonField.style.display = 'none';
                reasonField.required = false;
                reasonField.value = ''; // Clear reason when switching to verified
            }
            if (amountError) amountError.style.display = 'block';
            if (reasonError) reasonError.style.display = 'none';
        } else if (status === 'not_verified') {
            // Show reason field, hide amount field
            if (amountField) {
                amountField.style.display = 'none';
                amountField.required = false;
                // DON'T clear the amount field - preserve the value
            }
            if (reasonField) {
                reasonField.style.display = 'block';
                reasonField.required = true;
            }
            if (amountError) amountError.style.display = 'none';
            if (reasonError) reasonError.style.display = 'block';
        }
    }
});

// Initialize security deposit fields on page load
document.addEventListener('DOMContentLoaded', function() {
    const securityDepositStatus = document.querySelector('input[name="documents[security_deposit][status]"]:checked');
    if (securityDepositStatus) {
        // Trigger the change event to set initial state
        securityDepositStatus.dispatchEvent(new Event('change'));
    }
});

        // Modal document viewer
        document.getElementById('documentModal').addEventListener('show.bs.modal', function(event) {
            const link = event.relatedTarget;
            if (!link) return; // Prevent error if relatedTarget is undefined
            const src = link.getAttribute('data-src');
            const type = link.getAttribute('data-type');
            const iframe = document.getElementById('documentFrame');
            iframe.src = src;
            document.getElementById('documentModalLabel').textContent = `Viewing ${type}`;
        });
    </script>
@endsection