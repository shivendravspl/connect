@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">
            <i class="ri-file-text-line me-2"></i> Manage Physical Documents for {{ $application->entityDetails->establishment_name }}
        </h6>
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
                                <th>Upload / Details</th>
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
                                    $checks = $physicalDocumentChecks->get($type, collect());
                                    $check = $checks->firstWhere('document_type', $type) ?? (object)[
                                        'received' => false,
                                        'status' => 'pending',
                                        'reason' => null,
                                        'file_path' => null,
                                        'original_filename' => null,
                                        'securityChequeDetails' => collect(),
                                        'securityDepositDetail' => null,
                                    ];
                                    $currentStatus = old("documents.$type.status", $check->status ?? 'pending');
                                    $showReasonField = $currentStatus === 'not_verified';
                                    $existingAmount = old('security_deposit_amount', $check->amount ?? '');
                                    $existingReason = old("documents.$type.reason", $check->reason ?? '');
                                    $depositDetail = $check->securityDepositDetail;
                                    $deposit_date = old('deposit_date', $depositDetail?->deposit_date ?? '');
                                    $deposit_mode = old('deposit_mode', $depositDetail?->mode_of_payment ?? '');
                                    $deposit_reference = old('deposit_reference', $depositDetail?->reference_no ?? '');
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
                                            <input type="radio" name="documents[{{ $type }}][status]" class="form-check-input status-radio" value="verified" id="{{ $type }}_verified_yes" {{ $currentStatus === 'verified' ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="{{ $type }}_verified_yes">Verified</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" name="documents[{{ $type }}][status]" class="form-check-input status-radio" value="not_verified" id="{{ $type }}_verified_no" {{ $showReasonField ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="{{ $type }}_verified_no">Not Verified</label>
                                        </div>
                                        <div id="documents_{{ $type }}_status_error" class="text-danger small mt-1"></div>
                                    </td>
                                    <td class="align-middle">
                                        @if($type === 'security_cheques')
                                            <div class="security-cheques-section">
                                                <!-- Upload Row -->
                                                <div class="upload-row mb-2">
                                                    <label class="small fw-bold">Upload Cheques</label>
                                                    @foreach($checks as $loopIndex => $chqCheck)
                                                        @php
                                                            $chequeDetails = $chqCheck->securityChequeDetails->first();
                                                            $hasFileInRow = $chqCheck->file_path ?? false;
                                                            $hasAckFileInRow = false; // For simplicity, assume separate check for ack if needed
                                                        @endphp
                                                        <div class="file-upload-row mb-1 d-flex align-items-center" data-index="{{ $loopIndex }}">
                                                            <input type="file" class="form-control form-control-sm me-2 document-upload {{ $hasFileInRow ? 'd-none' : '' }}" data-type="security_cheques" data-display-type="Security Cheque" accept=".pdf,.doc,.docx,.jpg,.png" id="file-input-security_cheques-{{ $loopIndex }}" {{ $hasFileInRow ? 'disabled' : '' }}>
                                                            @if($hasFileInRow)
                                                                <button type="button" class="btn btn-sm btn-warning replace-file-btn me-2" data-row-index="{{ $loopIndex }}">Replace</button>
                                                            @endif
                                                            <span class="file-name small me-2" id="file-name-security_cheques-{{ $loopIndex }}">
                                                                @if($chqCheck->file_path)
                                                                    <a href="#" class="view-existing" data-type="security_cheques" data-filename="{{ $chqCheck->file_path }}">View</a> ({{ $chqCheck->original_filename ?? basename($chqCheck->file_path) }})
                                                                @endif
                                                            </span>
                                                            @if($loopIndex > 0)
                                                                <button type="button" class="btn btn-sm btn-danger remove-file-upload"><i class="ri-delete-bin-line"></i></button>
                                                            @endif
                                                            <div class="hidden-file-container d-none">
                                                                @if($chqCheck->file_path)
                                                                    <input type="hidden" name="existing_security_cheques_file[]" value="{{ $chqCheck->file_path }}">
                                                                    <input type="hidden" name="existing_security_cheques_file_original[]" value="{{ $chqCheck->original_filename ?? basename($chqCheck->file_path) }}">
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    <button type="button" class="btn btn-sm btn-primary add-file-upload"><i class="ri-add-circle-line"></i> Add Cheque</button>
                                                </div>
                                                <!-- Details Row -->
                                                <div class="details-row">
                                                    <label class="small fw-bold">Cheque Details</label>
                                                    <div id="cheque-details-container">
                                                        @foreach($checks as $loopIndex => $chqCheck)
                                                            @php $chequeDetails = $chqCheck->securityChequeDetails->first(); @endphp
                                                            <div class="cheque-detail-row mb-2 p-2 border rounded" data-index="{{ $loopIndex }}">
                                                                <div class="row g-1">
                                                                    <div class="col-md-3">
                                                                        <label class="small">Sr. No</label>
                                                                        <input type="text" class="form-control form-control-sm" value="{{ $loopIndex + 1 }}" readonly>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label class="small">Date of obtained</label>
                                                                        <input type="date" class="form-control form-control-sm" name="security_cheques_details[{{ $loopIndex }}][date_obtained]" value="{{ old("security_cheques_details.{$loopIndex}.date_obtained", $chequeDetails?->date_obtained ?? '') }}" max="{{ now()->toDateString() }}">
                                                                        <div id="security_cheques_details_{{ $loopIndex }}_date_obtained_error" class="text-danger small mt-1"></div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label class="small">Cheque No</label>
                                                                        <input type="text" class="form-control form-control-sm" name="security_cheques_details[{{ $loopIndex }}][cheque_no]" value="{{ old("security_cheques_details.{$loopIndex}.cheque_no", $chequeDetails?->cheque_no ?? '') }}" maxlength="50">
                                                                        <div id="security_cheques_details_{{ $loopIndex }}_cheque_no_error" class="text-danger small mt-1"></div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label class="small">Upload Ack</label>
                                                                        <input type="file" class="form-control form-control-sm document-upload ack-upload" data-type="security_cheques_ack" data-display-type="Ack" accept=".pdf,.doc,.docx,.jpg,.png" id="ack-input-{{ $loopIndex }}">
                                                                        <span class="file-name small ack-file-name" id="ack-file-name-{{ $loopIndex }}"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="row g-1 mt-1">
                                                                    <div class="col-md-6">
                                                                        <label class="small">Date of Use</label>
                                                                        <input type="date" class="form-control form-control-sm" name="security_cheques_details[{{ $loopIndex }}][date_use]" value="{{ old("security_cheques_details.{$loopIndex}.date_use", $chequeDetails?->date_use ?? '') }}" max="{{ now()->toDateString() }}">
                                                                        <div id="security_cheques_details_{{ $loopIndex }}_date_use_error" class="text-danger small mt-1"></div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="small">Purpose of use</label>
                                                                        <input type="text" class="form-control form-control-sm" name="security_cheques_details[{{ $loopIndex }}][purpose]" value="{{ old("security_cheques_details.{$loopIndex}.purpose", $chequeDetails?->purpose ?? '') }}" maxlength="200">
                                                                        <div id="security_cheques_details_{{ $loopIndex }}_purpose_error" class="text-danger small mt-1"></div>
                                                                    </div>
                                                                </div>
                                                                <div class="row g-1 mt-1">
                                                                    <div class="col-md-6">
                                                                        <label class="small">Date of Return</label>
                                                                        <input type="date" class="form-control form-control-sm" name="security_cheques_details[{{ $loopIndex }}][date_return]" value="{{ old("security_cheques_details.{$loopIndex}.date_return", $chequeDetails?->date_return ?? '') }}" max="{{ now()->toDateString() }}">
                                                                        <div id="security_cheques_details_{{ $loopIndex }}_date_return_error" class="text-danger small mt-1"></div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="small">Remark/reason of return</label>
                                                                        <textarea class="form-control form-control-sm" name="security_cheques_details[{{ $loopIndex }}][remark_return]" rows="1">{{ old("security_cheques_details.{$loopIndex}.remark_return", $chequeDetails?->remark_return ?? '') }}</textarea>
                                                                        <div id="security_cheques_details_{{ $loopIndex }}_remark_return_error" class="text-danger small mt-1"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div id="documents_security_cheques_files_error" class="text-danger small mt-1"></div>
                                            </div>
                                        @elseif($type === 'security_deposit')
                                            <!-- Deposit Upload Row -->
                                            <div class="upload-row mb-2">
                                                <label class="small fw-bold">Upload Deposit Receipt</label>
                                                <input type="file" class="form-control form-control-sm document-upload {{ $hasFile ? 'd-none' : '' }}" data-type="security_deposit" data-display-type="Security Deposit" accept=".pdf,.doc,.docx,.jpg,.png" id="file-input-security_deposit" {{ $hasFile ? 'disabled' : '' }}>
                                                @if($hasFile)
                                                    <button type="button" class="btn btn-sm btn-warning replace-file-btn">Replace</button>
                                                @endif
                                                <span class="file-name small" id="file-name-security_deposit">
                                                    @if($check->file_path)
                                                        <a href="#" class="view-existing" data-type="security_deposit" data-filename="{{ $check->file_path }}">View</a> ({{ $check->original_filename ?? basename($check->file_path) }})
                                                    @endif
                                                </span>
                                                <div class="hidden-file-container d-none">
                                                    @if($check->file_path)
                                                        <input type="hidden" name="existing_{{ $type }}_file" value="{{ $check->file_path }}">
                                                        <input type="hidden" name="existing_{{ $type }}_file_original" value="{{ $check->original_filename ?? basename($check->file_path) }}">
                                                    @endif
                                                </div>
                                            </div>
                                            <!-- Deposit Details Row -->
                                            <div class="details-row" id="deposit_details" style="display: {{ $showReasonField ? 'none' : '' }};">
                                                <label class="small fw-bold">Security Deposit Details</label>
                                                <div class="row g-2">
                                                    <div class="col-md-3">
                                                        <label class="small">Date</label>
                                                        <input type="date" name="deposit_date" id="deposit_date_field" class="form-control form-control-sm" value="{{ $deposit_date }}" max="{{ now()->toDateString() }}">
                                                        <div id="deposit_date_error" class="text-danger small mt-1"></div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="small">Amount</label>
                                                        <input type="number" name="security_deposit_amount" id="security_deposit_amount" class="form-control form-control-sm" value="{{ $existingAmount }}" step="0.01" min="0" placeholder="Enter amount">
                                                        <div id="security_deposit_amount_error" class="text-danger small mt-1"></div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="small">Mode of payment</label>
                                                        <select name="deposit_mode" id="deposit_mode" class="form-control form-control-sm">
                                                            <option value="">Select Mode</option>
                                                            <option value="Cash" {{ $deposit_mode == 'Cash' ? 'selected' : '' }}>Cash</option>
                                                            <option value="Cheque" {{ $deposit_mode == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                                            <option value="NEFT/Online" {{ $deposit_mode == 'NEFT/Online' ? 'selected' : '' }}>NEFT/Online</option>
                                                        </select>
                                                        <div id="deposit_mode_error" class="text-danger small mt-1"></div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="small">If NEFT/Online, Reference No.</label>
                                                        <input type="text" name="deposit_reference" id="deposit_reference" class="form-control form-control-sm" value="{{ $deposit_reference }}" maxlength="100">
                                                        <div id="deposit_reference_error" class="text-danger small mt-1"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="documents_{{ $type }}_file_error" class="text-danger small mt-1"></div>
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
                                        @if($type === 'security_deposit' && $showReasonField)
                                            <textarea name="documents[{{ $type }}][reason]" id="{{ $type }}_reason" class="form-control form-control-sm" rows="2" placeholder="Enter remarks if not verified">{{ $existingReason }}</textarea>
                                            <div id="documents_{{ $type }}_reason_error" class="text-danger small mt-1"></div>
                                        @else
                                            <textarea name="documents[{{ $type }}][reason]" id="{{ $type }}_reason" class="form-control form-control-sm" rows="2" placeholder="Enter remarks if not verified">{{ $existingReason }}</textarea>
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
        .upload-row, .details-row {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        .cheque-detail-row {
            background-color: #f8f9fa;
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

        // processDocument function - updated to handle ack uploads
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
                const input = this;
                const container = input.closest('.file-upload-row') || input.closest('td');
                const fileInput = container.querySelector('.document-upload');
                if (fileInput) {
                    fileInput.classList.add('d-none');
                    fileInput.disabled = true;
                }

                // Hidden inputs for filename
                let hiddenContainer = container.querySelector('.hidden-file-container');
                if (!hiddenContainer) {
                    hiddenContainer = document.createElement('div');
                    hiddenContainer.className = 'hidden-file-container d-none';
                    container.appendChild(hiddenContainer);
                }

                hiddenContainer.innerHTML = ''; // Clear existing

                const isAck = type.includes('ack');
                const baseType = isAck ? type.replace('_ack', '') : type;
                const fieldSuffix = '[]'; // Since multiple for cheques
                const fileFieldName = `existing_${baseType}_file${isAck ? '_ack' : ''}${fieldSuffix}`;
                const originalFieldName = `${fileFieldName}_original`;

                hiddenContainer.innerHTML = `
                    <input type="hidden" name="${fileFieldName}" value="${filename}">
                    <input type="hidden" name="${originalFieldName}" value="${displayName}">
                `;

                // Ensure replace button
                let replaceBtn = container.querySelector('.replace-file-btn');
                if (replaceBtn) {
                    replaceBtn.classList.remove('d-none');
                } else {
                    replaceBtn = document.createElement('button');
                    replaceBtn.type = 'button';
                    replaceBtn.className = 'btn btn-sm btn-warning replace-file-btn me-2';
                    replaceBtn.innerHTML = 'Replace';
                    replaceBtn.dataset.rowIndex = container.dataset.index || 0;
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

        // Event Delegation for file changes - updated for ack
        document.addEventListener('change', function(e) {
            if (e.target.matches('.document-upload')) {
                const input = e.target;
                const file = input.files[0];
                if (!file) return;

                const type = input.dataset.type || 'unknown';
                const displayType = input.dataset.displayType || type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                const index = input.closest('[data-index]') ? input.closest('[data-index]').dataset.index : '';
                const idSuffix = index ? '-' + index : '';
                let fieldId = `file-name-${type}${idSuffix}`;
                if (input.classList.contains('ack-upload')) {
                    fieldId = `ack-file-name-${type}${idSuffix}`;
                }
                const fileNameField = document.getElementById(fieldId);
                const existingFileInput = container.querySelector(`input[name="existing_${type}_file${type.includes('ack') ? '_ack' : ''}[]"]`) || container.querySelector(`input[name="existing_${type}_file"]`);
                const existingFile = existingFileInput ? existingFileInput.value : '';

                processDocument.call(input, file, fileNameField, existingFile, {
                    type: type,
                    displayType: displayType,
                    endpoint: '{{ route("process-document") }}'
                }).catch(error => {
                    // Handled in function
                });
            }
        });

        // Handle Replace buttons - updated for ack
        document.addEventListener('click', function(e) {
            if (e.target.matches('.replace-file-btn')) {
                const container = e.target.closest('.file-upload-row') || e.target.closest('td');
                const fileInput = container.querySelector('.document-upload:not(.ack-upload)');
                const fileNameField = container.querySelector('.file-name:not(.ack-file-name)');
                const hiddenContainer = container.querySelector('.hidden-file-container');
                const replaceBtn = e.target;

                if (fileNameField) fileNameField.innerHTML = '';
                if (hiddenContainer) hiddenContainer.innerHTML = '';

                if (fileInput) {
                    fileInput.classList.remove('d-none');
                    fileInput.disabled = false;
                }
                if (replaceBtn) replaceBtn.classList.add('d-none');
            } else if (e.target.matches('.replace-ack-file-btn')) {
                // Similar for ack if separate button
                const container = e.target.closest('.cheque-detail-row');
                const ackInput = container.querySelector('.ack-upload');
                const ackFileNameField = container.querySelector('.ack-file-name');
                const hiddenContainer = container.querySelector('.hidden-file-container');
                const replaceBtn = e.target;

                if (ackFileNameField) ackFileNameField.innerHTML = '';
                if (hiddenContainer) hiddenContainer.innerHTML = '';

                if (ackInput) {
                    ackInput.classList.remove('d-none');
                    ackInput.disabled = false;
                }
                if (replaceBtn) replaceBtn.classList.add('d-none');
            }
        });

        // Add file upload field for security cheques - updated to add detail row too
        document.addEventListener('click', function(e) {
            if (e.target.closest('.add-file-upload')) {
                const uploadContainer = document.querySelector('.upload-row');
                const detailsContainer = document.getElementById('cheque-details-container');
                const index = uploadContainer.querySelectorAll('.file-upload-row').length;
                // Add upload row
                const newUploadRow = document.createElement('div');
                newUploadRow.className = 'file-upload-row mb-1 d-flex align-items-center';
                newUploadRow.dataset.index = index;
                newUploadRow.innerHTML = `
                    <input type="file" class="form-control form-control-sm me-2 document-upload" data-type="security_cheques" data-display-type="Security Cheque" accept=".pdf,.doc,.docx,.jpg,.png" id="file-input-security_cheques-${index}">
                    <span class="file-name small me-2" id="file-name-security_cheques-${index}"></span>
                    <button type="button" class="btn btn-sm btn-danger remove-file-upload"><i class="ri-delete-bin-line"></i></button>
                    <div class="hidden-file-container d-none"></div>
                `;
                uploadContainer.insertBefore(newUploadRow, e.target.closest('.add-file-upload'));

                // Add detail row
                const newDetailRow = document.createElement('div');
                newDetailRow.className = 'cheque-detail-row mb-2 p-2 border rounded';
                newDetailRow.dataset.index = index;
                newDetailRow.innerHTML = `
                    <div class="row g-1">
                        <div class="col-md-3">
                            <label class="small">Sr. No</label>
                            <input type="text" class="form-control form-control-sm" value="${index + 1}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="small">Date of obtained</label>
                            <input type="date" class="form-control form-control-sm" name="security_cheques_details[${index}][date_obtained]" max="${new Date().toISOString().split('T')[0]}">
                            <div id="security_cheques_details_${index}_date_obtained_error" class="text-danger small mt-1"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="small">Cheque No</label>
                            <input type="text" class="form-control form-control-sm" name="security_cheques_details[${index}][cheque_no]" maxlength="50">
                            <div id="security_cheques_details_${index}_cheque_no_error" class="text-danger small mt-1"></div>
                        </div>
                        <div class="col-md-3">
                            <label class="small">Upload Ack</label>
                            <input type="file" class="form-control form-control-sm document-upload ack-upload" data-type="security_cheques_ack" data-display-type="Ack" accept=".pdf,.doc,.docx,.jpg,.png" id="ack-input-${index}">
                            <span class="file-name small ack-file-name" id="ack-file-name-${index}"></span>
                        </div>
                    </div>
                    <div class="row g-1 mt-1">
                        <div class="col-md-6">
                            <label class="small">Date of Use</label>
                            <input type="date" class="form-control form-control-sm" name="security_cheques_details[${index}][date_use]" max="${new Date().toISOString().split('T')[0]}">
                            <div id="security_cheques_details_${index}_date_use_error" class="text-danger small mt-1"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="small">Purpose of use</label>
                            <input type="text" class="form-control form-control-sm" name="security_cheques_details[${index}][purpose]" maxlength="200">
                            <div id="security_cheques_details_${index}_purpose_error" class="text-danger small mt-1"></div>
                        </div>
                    </div>
                    <div class="row g-1 mt-1">
                        <div class="col-md-6">
                            <label class="small">Date of Return</label>
                            <input type="date" class="form-control form-control-sm" name="security_cheques_details[${index}][date_return]" max="${new Date().toISOString().split('T')[0]}">
                            <div id="security_cheques_details_${index}_date_return_error" class="text-danger small mt-1"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="small">Remark/reason of return</label>
                            <textarea class="form-control form-control-sm" name="security_cheques_details[${index}][remark_return]" rows="1"></textarea>
                            <div id="security_cheques_details_${index}_remark_return_error" class="text-danger small mt-1"></div>
                        </div>
                    </div>
                `;
                detailsContainer.appendChild(newDetailRow);
            }
        });

        // Remove file upload field - remove both upload and detail
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-file-upload')) {
                const index = e.target.closest('.file-upload-row').dataset.index;
                e.target.closest('.file-upload-row').remove();
                document.querySelector(`.cheque-detail-row[data-index="${index}"]`).remove();
                // Re-number remaining rows
                const remainingRows = document.querySelectorAll('.file-upload-row, .cheque-detail-row');
                remainingRows.forEach((row, idx) => {
                    row.dataset.index = idx;
                    const srNo = row.querySelector('input[readonly]');
                    if (srNo) srNo.value = idx + 1;
                    // Update name attributes
                    const inputs = row.querySelectorAll('input, textarea');
                    inputs.forEach(input => {
                        const name = input.name.replace(/\[(\d+)\]/, `[${idx}]`);
                        input.name = name;
                        const idMatch = input.id.match(/(\d+)$/);
                        if (idMatch) {
                            input.id = input.id.replace(idMatch[0], idx);
                        }
                    });
                });
            }
        });

        // Form submission - updated error handling for details
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

                    setTimeout(() => {
                        window.location.href = "{{ route('dashboard') }}";
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Something went wrong');
                }
            })
            .catch(error => {
                if (error.status === 422) {
                    const errorsObj = error.errors;
                    Object.entries(errorsObj).forEach(([field, msgs]) => {
                        const msg = Array.isArray(msgs) ? msgs[0] : msgs;
                        let errorId = null;
                        // Existing handlers...
                        if (field === 'receive_date') {
                            errorId = 'receive_date_error';
                        } else if (field === 'verified_date') {
                            errorId = 'verified_date_error';
                        } else if (field === 'deposit_date') {
                            errorId = 'deposit_date_error';
                        } else if (field === 'deposit_mode') {
                            errorId = 'deposit_mode_error';
                        } else if (field === 'deposit_reference') {
                            errorId = 'deposit_reference_error';
                        } else if (field === 'security_deposit_amount') {
                            errorId = 'security_deposit_amount_error';
                        } else if (field.startsWith('documents.')) {
                            // ... existing
                        } else if (field.startsWith('security_cheques_details.')) {
                            const parts = field.split('.');
                            if (parts.length === 3) {
                                const idx = parts[1];
                                const sub = parts[2];
                                errorId = `security_cheques_details_${idx}_${sub}_error`;
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

        // Status change handler - updated for deposit and general
        document.addEventListener('change', function(e) {
            if (e.target.matches('input[name*="status"]')) {
                const match = e.target.name.match(/documents\[([^\]]+)\]\[status\]/);
                if (match) {
                    const type = match[1];
                    const status = e.target.value;
                    const errorElement = document.getElementById(`documents_${type}_status_error`);
                    if (errorElement) errorElement.innerHTML = '';

                    if (type === 'security_deposit') {
                        const depositDetails = document.getElementById('deposit_details');
                        const reasonField = document.getElementById('security_deposit_reason');
                        const reasonContainer = reasonField ? reasonField.closest('td') : null;
                        if (status === 'verified') {
                            if (depositDetails) depositDetails.style.display = 'block';
                            if (reasonContainer) reasonContainer.style.display = 'none';
                        } else {
                            if (depositDetails) depositDetails.style.display = 'none';
                            if (reasonContainer) reasonContainer.style.display = 'block';
                        }
                    }

                    // Real-time validation for not_verified remarks
                    const remarkField = document.getElementById(`${type}_reason`);
                    const reasonError = document.getElementById(`documents_${type}_reason_error`);
                    if (status === 'not_verified' && remarkField && !remarkField.value.trim()) {
                        if (reasonError) reasonError.innerHTML = 'Remarks are required when document is not verified';
                    } else if (reasonError) {
                        reasonError.innerHTML = '';
                    }
                }
            }
        });

        // Deposit mode change
        document.addEventListener('change', function(e) {
            if (e.target.id === 'deposit_mode') {
                const referenceDiv = document.getElementById('deposit_reference').parentElement;
                if (e.target.value === 'NEFT/Online') {
                    referenceDiv.style.display = 'block';
                } else {
                    referenceDiv.style.display = 'none';
                    document.getElementById('deposit_reference').value = '';
                }
            }
        });

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            const securityDepositStatus = document.querySelector('input[name="documents[security_deposit][status]"]:checked');
            if (securityDepositStatus) {
                securityDepositStatus.dispatchEvent(new Event('change'));
            }
            const depositMode = document.getElementById('deposit_mode');
            if (depositMode) {
                depositMode.dispatchEvent(new Event('change'));
            }
        });

        // View existing files
        document.addEventListener('click', function(e) {
            if (e.target.matches('.view-existing')) {
                e.preventDefault();
                const type = e.target.dataset.type;
                const filename = e.target.dataset.filename;
                const src = getS3Url(type, filename);
                const displayType = type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                document.getElementById('documentFrame').src = src;
                document.getElementById('documentModalLabel').textContent = `Viewing ${displayType}`;
                new bootstrap.Modal(document.getElementById('documentModal')).show();
            }
        });

        // Modal shown event
        document.getElementById('documentModal').addEventListener('show.bs.modal', function(event) {
            const link = event.relatedTarget;
            if (link) {
                const src = link.getAttribute('data-src');
                const type = link.getAttribute('data-type');
                document.getElementById('documentFrame').src = src;
                document.getElementById('documentModalLabel').textContent = `Viewing ${type}`;
            }
        });
    </script>
@endsection