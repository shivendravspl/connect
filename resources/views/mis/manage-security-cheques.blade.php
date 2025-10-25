{{-- resources/views/mis/manage-security-cheques.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">
            <i class="ri-file-text-line me-2"></i> Manage Security Cheques for {{ $application->entityDetails->establishment_name }}
            @if($application->distributor_code)
                <small class="text-muted">(Distributor Code: {{ $application->distributor_code }})</small>
            @endif
        </h6>
        <a href="{{ route('mis.list-security-cheques') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ri-arrow-left-line me-1"></i> Back to List
        </a>
    </div>

    <div class="card shadow-sm rounded-3">
        <div class="card-header bg-light py-2">
            <h6 class="mb-0"><i class="ri-file-list-line me-2"></i> Security Cheque Details</h6>
        </div>
        <div class="card-body">
            <form id="securityChequesForm" action="{{ route('approvals.update-security-cheque-details', $application) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Sr. No</th>
                                <th>Cheque No</th>
                                <th>Date Obtained</th>
                                <th>File</th>
                                <th>Date of Use</th>
                                <th>Purpose of Use</th>
                                <th>Date of Return</th>
                                <th>Remark/Reason of Return</th>
                            </tr>
                        </thead>
                        <tbody id="existingChequesBody">
                            @forelse($securityChequeChecks as $checkIndex => $check)
                                @php $detailIndex = 0; @endphp
                                @forelse($check->securityChequeDetails as $detail)
                                    <tr data-cheque-id="{{ $detail->id }}">
                                        <td>{{ $checkIndex + 1 }}</td>
                                        <td>{{ $detail->cheque_no }}</td>
                                        <td>{{ $detail->date_obtained?->format('d-m-Y') }}</td>
                                        <td>
                                            @if($check->file_path)
                                                <a href="#" class="view-cheque-file" data-filename="{{ $check->file_path }}" data-original="{{ $check->original_filename }}">
                                                    <i class="ri-eye-line"></i> View
                                                </a>
                                            @else
                                                No File
                                            @endif
                                        </td>
                                        <td>
                                            <input type="date" name="existing_cheques[{{ $checkIndex }}][{{ $detailIndex }}][date_use]" 
                                                value="{{ $detail->date_use?->format('Y-m-d') }}" class="form-control form-control-sm" max="{{ now()->toDateString() }}">
                                        </td>
                                        <td>
                                            <input type="text" name="existing_cheques[{{ $checkIndex }}][{{ $detailIndex }}][purpose]" 
                                                value="{{ $detail->purpose }}" class="form-control form-control-sm" maxlength="200">
                                        </td>
                                        <td>
                                            <input type="date" name="existing_cheques[{{ $checkIndex }}][{{ $detailIndex }}][date_return]" 
                                                value="{{ $detail->date_return?->format('Y-m-d') }}" class="form-control form-control-sm" max="{{ now()->toDateString() }}">
                                        </td>
                                        <td>
                                            <textarea name="existing_cheques[{{ $checkIndex }}][{{ $detailIndex }}][remark_return]" 
                                                    class="form-control form-control-sm" rows="2" maxlength="500">{{ $detail->remark_return }}</textarea>
                                        </td>
                                        <input type="hidden" name="existing_cheques[{{ $checkIndex }}][{{ $detailIndex }}][id]" value="{{ $detail->id }}">
                                    </tr>
                                    @php $detailIndex++; @endphp
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No details for Cheque {{ $checkIndex + 1 }}.</td>
                                    </tr>
                                @endforelse
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No security cheques found. Add new ones below.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Add New Cheque Section -->
                <div class="border-top pt-3 mb-3">
                    <h6 class="mb-2"><i class="ri-add-circle-line me-2"></i> Add New Security Cheque</h6>
                    <div id="newChequesContainer">
                        {{-- Empty by default; rows added via JS --}}
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addNewChequeBtn">
                        <i class="ri-add-line me-1"></i> Add New Cheque
                    </button>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="ri-save-line me-1"></i> Save Cheque Details
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cheque File Modal -->
    <div class="modal fade" id="chequeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">View Cheque File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <iframe id="chequeFrame" src="" style="width: 100%; height: 500px;"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let newChequeIndex = 0; // Start at 0 since no default row

    // Add new cheque row
    document.getElementById('addNewChequeBtn').addEventListener('click', function() {
        const container = document.getElementById('newChequesContainer');
        const newRow = document.createElement('div');
        newRow.className = 'new-cheque-row mb-3 p-3 border rounded';
        newRow.dataset.index = newChequeIndex;
        newRow.innerHTML = `
            <div class="row g-2">
                <div class="col-md-2">
                    <label class="form-label small">Cheque No <span class="text-danger">*</span></label>
                    <input type="text" name="new_cheques[${newChequeIndex}][cheque_no]" class="form-control form-control-sm cheque-no" maxlength="50">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Date Obtained <span class="text-danger">*</span></label>
                    <input type="date" name="new_cheques[${newChequeIndex}][date_obtained]" class="form-control form-control-sm date-obtained" max="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Upload Cheque File <span class="text-danger">*</span></label>
                    <input type="file" name="new_cheques[${newChequeIndex}][file]" class="form-control form-control-sm new-cheque-file" accept=".pdf,.doc,.docx,.jpg,.png">
                    <small class="text-muted">Max 5MB: PDF, DOC, DOCX, JPG, PNG</small>
                    <span class="file-name small mt-1" id="file-name-new-${newChequeIndex}"></span>
                    <div class="hidden-file-container d-none"></div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Date of Use</label>
                    <input type="date" name="new_cheques[${newChequeIndex}][date_use]" class="form-control form-control-sm" max="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Purpose</label>
                    <input type="text" name="new_cheques[${newChequeIndex}][purpose]" class="form-control form-control-sm" maxlength="200">
                </div>
            </div>
            <div class="row g-2 mt-2">
                <div class="col-md-3">
                    <label class="form-label small">Date of Return</label>
                    <input type="date" name="new_cheques[${newChequeIndex}][date_return]" class="form-control form-control-sm" max="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Remark/Reason of Return</label>
                    <textarea name="new_cheques[${newChequeIndex}][remark_return]" class="form-control form-control-sm" rows="2" maxlength="500"></textarea>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-new-cheque">Remove</button>
                </div>
            </div>
        `;
        container.appendChild(newRow);
        newChequeIndex++;
    });

    // Remove new cheque row
    document.addEventListener('click', function(e) {
        if (e.target.matches('.remove-new-cheque')) {
            e.target.closest('.new-cheque-row').remove();
        }
    });

    // S3 Upload for New Cheque Files (like physical docs)
    async function processNewChequeFile(file, fileNameField, index) {
        fileNameField.textContent = 'Uploading...';
        fileNameField.classList.remove('text-danger');

        const formData = new FormData();
        formData.append('file', file);
        formData.append('application_id', {{ $application->id }});

        try {
            const response = await fetch('{{ route("mis.process-security-cheque") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData,
            });

            const result = await response.json();
            if (response.ok && result.status === 'SUCCESS') {
                const { filename, displayName, url } = result.data;
                fileNameField.innerHTML = `<a href="#" class="view-new-cheque" data-src="${url}" data-original="${displayName}">View</a> (${displayName})`;

                // Store in hidden inputs
                const hiddenContainer = fileNameField.parentNode.querySelector('.hidden-file-container');
                hiddenContainer.innerHTML = `
                    <input type="hidden" name="new_cheques[${index}][file_path]" value="${filename}">
                    <input type="hidden" name="new_cheques[${index}][original_filename]" value="${displayName}">
                `;
            } else {
                throw new Error(result.message || 'Upload failed');
            }
        } catch (error) {
            fileNameField.textContent = `Error: ${error.message}`;
            fileNameField.classList.add('text-danger');
        }
    }

    // Handle new file changes
    document.addEventListener('change', function(e) {
        if (e.target.matches('.new-cheque-file')) {
            const file = e.target.files[0];
            if (!file) return;

            const index = e.target.closest('.new-cheque-row').dataset.index;
            const fileNameField = document.getElementById(`file-name-new-${index}`);
            const fileInput = e.target;

            processNewChequeFile(file, fileNameField, index);
        }
    });

    // View new uploaded files
    document.addEventListener('click', function(e) {
        if (e.target.matches('.view-new-cheque')) {
            e.preventDefault();
            const src = e.target.dataset.src;
            const original = e.target.dataset.original;
            document.getElementById('chequeFrame').src = src;
            document.querySelector('#chequeModal .modal-title').textContent = `New Cheque: ${original}`;
            new bootstrap.Modal(document.getElementById('chequeModal')).show();
        }
    });

    // Existing view cheque file (unchanged)
    document.addEventListener('click', function(e) {
        if (e.target.matches('.view-cheque-file')) {
            e.preventDefault();
            const filename = e.target.dataset.filename;
            const original = e.target.dataset.original;
            const src = `https://s3.ap-south-1.amazonaws.com/developerinvnr.bkt/Connect/Distributor/security_cheques/${filename}`;
            document.getElementById('chequeFrame').src = src;
            document.querySelector('#chequeModal .modal-title').textContent = `Cheque File: ${original}`;
            new bootstrap.Modal(document.getElementById('chequeModal')).show();
        }
    });

    // Form submission (unchanged, but now handles empty new_cheques gracefully)
    document.getElementById('securityChequesForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });
</script>
@endsection