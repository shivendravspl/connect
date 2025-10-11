@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            <i class="ri-check-line me-2"></i> MIS Document Verification for <b>{{ $application->entityDetails->establishment_name }}</b>
        </h5>
        @if($isSubmitted)
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                <i class="ri-printer-line me-1"></i> Print
            </button>
        @endif
    </div>

    @if($application->status === 'documents_resubmitted')
        <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
            <i class="ri-information-line me-2"></i> Verifying re-submitted documents. Only documents marked for re-submission are editable.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Entity Details Verification -->
    <div class="card mb-3 shadow-sm rounded-3">
        <div class="card-header bg-light py-1">
            <h6 class="mb-0 d-flex justify-content-between align-items-center small">
                <span><i class="ri-building-line me-2"></i> Entity Details Verification</span>
                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#entityDetailsCollapse" 
                        aria-expanded="true">
                    <i class="ri-arrow-down-s-line"></i>
                </button>
            </h6>
        </div>
        <div class="card-body p-2 collapse show" id="entityDetailsCollapse">
            <form id="entityDetailsForm" 
                  action="{{ route('approvals.update-entity-details', $application) }}" 
                  method="POST">
                @csrf
                <div class="row g-2 small">
                    @php
                        $entityFields = [
                            'establishment_name' => 'Establishment Name',
                            'business_address' => 'Business Address',
                            'house_no' => 'House No',
                            'landmark' => 'Landmark',
                            'pan_number' => 'PAN Number',
                            'seed_license' => 'Seed License',
                            'seed_license_validity' => 'Seed License Validity',
                            'bank_name' => 'Bank Name',
                            'account_holder_name' => 'Account Holder Name',
                            'account_number' => 'Account Number',
                            'ifsc_code' => 'IFSC Code',
                        ];
                    @endphp

                    @foreach($entityFields as $field => $label)
                        <div class="col-md-3 col-sm-6">
                            <label class="form-label small mb-1">{{ $label }}</label>
                            @if($field === 'seed_license_validity')
                                <input type="date" 
                                       name="entity_fields[{{ $field }}]" 
                                       class="form-control form-control-sm entity-field" 
                                       value="{{ optional($application->entityDetails->$field)->format('Y-m-d') ?? '' }}" readonly>
                            @else
                                <input type="text" 
                                       name="entity_fields[{{ $field }}]" 
                                       class="form-control form-control-sm entity-field" 
                                       value="{{ $application->entityDetails->$field ?? '' }}" readonly>
                            @endif
                        </div>
                    @endforeach

                    <!-- Authorized Persons -->
                    @if($application->entityDetails->has_authorized_persons === 'yes' && $application->authorizedPersons->isNotEmpty())
                        <div class="col-12 mt-2">
                            <label class="form-label small mb-1"><strong>Authorized Persons</strong></label>
                        </div>
                        @foreach($application->authorizedPersons as $index => $person)
                            <input type="hidden" name="authorized_persons[{{ $index }}][id]" value="{{ $person->id }}">
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label small mb-1">Name (Person {{ $index + 1 }})</label>
                                <input type="text"
                                       name="authorized_persons[{{ $index }}][name]"
                                       class="form-control form-control-sm entity-field"
                                       value="{{ $person->name ?? '' }}" readonly
                                       {{ $isSubmitted && !in_array($application->status, ['documents_resubmitted', 'documents_pending']) ? 'readonly' : '' }}
                                       required>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label small mb-1">Contact (Person {{ $index + 1 }})</label>
                                <input type="text"
                                       name="authorized_persons[{{ $index }}][contact]"
                                       class="form-control form-control-sm entity-field"
                                       value="{{ $person->contact ?? '' }}" readonly>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label small mb-1">Address (Person {{ $index + 1 }})</label>
                                <input type="text"
                                       name="authorized_persons[{{ $index }}][address]"
                                       class="form-control form-control-sm entity-field"
                                       value="{{ $person->address ?? '' }}" readonly>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label small mb-1">Aadhar Number (Person {{ $index + 1 }})</label>
                                <input type="text"
                                       name="authorized_persons[{{ $index }}][aadhar_number]"
                                       class="form-control form-control-sm entity-field"
                                       value="{{ $person->aadhar_number ?? '' }}" readonly>
                            </div>
                        @endforeach
                    @endif

                    <!-- Edit and Verification Controls -->
                    <div class="col-12 mt-3">
                        @if(!$isSubmitted || in_array($application->status, ['documents_resubmitted', 'documents_pending']))
                            <button type="button" class="btn btn-outline-primary btn-sm edit-entity-fields mb-2">
                                <i class="ri-edit-line"></i> Edit
                            </button>
                        @endif
                    </div>
                    <div class="col-12">
                    <label class="form-label small mb-1">Verification</label>
                    @if($isSubmitted && !in_array($application->status, ['documents_resubmitted', 'documents_pending']))
                        @php
                            $isVerified = ($verifications['entity_details'] ?? '') === 'verified';
                        @endphp
                        <span class="badge {{ $isVerified ? 'bg-success' : 'bg-danger' }} fs-sm">
                            {{ $isVerified ? 'Verified' : 'Not Verified' }}
                        </span>
                    @else
                        <div class="d-flex gap-3 align-items-center">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input form-check-sm verification-radio" 
                                    type="radio" 
                                    name="entity_verification" 
                                    id="verify-entity-yes"
                                    value="verified"
                                    {{ ($verifications['entity_details'] ?? '') === 'verified' ? 'checked' : '' }}
                                    data-target="remark-entity">
                                <label class="form-check-label small" for="verify-entity-yes">
                                    <i class="ri-check-line text-success"></i> Verified
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input form-check-sm verification-radio" 
                                    type="radio" 
                                    name="entity_verification" 
                                    id="verify-entity-no"
                                    value="not_verified"
                                    {{ ($verifications['entity_details'] ?? '') === 'not_verified' ? 'checked' : '' }}
                                    data-target="remark-entity">
                                <label class="form-check-label small" for="verify-entity-no">
                                    <i class="ri-close-line text-danger"></i> Not Verified
                                </label>
                            </div>
                            <input type="text" 
                                name="entity_note" 
                                id="remark-entity"
                                class="form-control form-control-sm w-50 remark-input" 
                                placeholder="Remark (if not verified)"
                                value="{{ $verificationNotes['entity_details'] ?? '' }}"
                                style="display: {{ ($verifications['entity_details'] ?? '') === 'verified' ? 'none' : 'block' }};">
                        </div>
                    @endif
                </div>
                </div>

                @if(!$isSubmitted || in_array($application->status, ['documents_resubmitted', 'documents_pending']))
                    <div class="card-footer py-1 bg-light mt-1">
                        <button type="submit" class="btn btn-success btn-sm" id="submitEntityBtn">
                            <i class="ri-check-line me-1"></i> Submit
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Main Documents Verification -->
    <form id="verifyForm" action="{{ route('approvals.update-documents', $application) }}" method="POST">
        @csrf
        <div class="card mb-3 shadow-sm rounded-3">
            <div class="card-header bg-light py-2">
                <h6 class="mb-0 d-flex justify-content-between align-items-center">
                    <span><i class="ri-file-line me-2"></i> Main Documents Verification</span>
                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-bs-toggle="collapse" data-bs-target="#mainDocsCollapse" aria-expanded="true">
                        <i class="ri-arrow-down-s-line"></i>
                    </button>
                </h6>
            </div>
            <div class="card-body p-0 collapse show" id="mainDocsCollapse">
                @if(!empty($mainDocuments))
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:20%">Type</th>
                                <th style="width:32%">Details</th>
                                <th style="width:8%" class="text-center">View</th>
                                <th style="width:20%" class="text-center">Verification</th>
                                <th style="width:20%">Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mainDocuments as $index => $doc)
                                <tr class="{{ $doc['resubmitted'] ? 'table-warning' : '' }}">
                                    <td class="align-middle small">
                                        {{ $doc['type'] }}
                                        @if($doc['resubmitted'])
                                            <span class="badge bg-info fs-sm ms-1">Re-submitted</span>
                                        @endif
                                    </td>
                                    <td class="align-middle small">
                                        @foreach($doc['details'] as $key => $value)
                                            <div class="text-muted text-truncate" style="max-width: 200px;" title="{{ $value }}">
                                                {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($doc['path'])
                                        <button type="button" class="btn btn-outline-primary btn-sm view-main-document" 
                                                data-type="{{ $doc['type'] }}"
                                                data-path="{{ $doc['path'] }}" 
                                                data-s3-folder="{{ $doc['s3_folder'] }}"
                                                data-index="{{ $index }}"
                                                data-modal="mainDocumentModal{{ $index }}"
                                                title="View & Zoom Document">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                        @else
                                        <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($isSubmitted && !$doc['resubmitted'])
                                            <span class="badge {{ $verifications['main'][$index] ? 'bg-success' : 'bg-danger' }} fs-sm">
                                                {{ $verifications['main'][$index] ? 'Verified' : 'Not Verified' }}
                                            </span>
                                        @else
                                            <div class="d-flex justify-content-center gap-2">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input verification-radio" type="radio" 
                                                           name="document_verifications[main][{{ $index }}]" 
                                                           id="verify-main{{ $index }}-yes"
                                                           value="verified"
                                                           {{ $verifications['main'][$index] ? 'checked' : '' }}
                                                           data-target="remark-main{{ $index }}"
                                                           {{ $application->status === 'documents_resubmitted' && !$doc['resubmitted'] ? 'disabled' : '' }}>
                                                    <label class="form-check-label small" for="verify-main{{ $index }}-yes">
                                                        <i class="ri-check-line text-success"></i> Verified
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input verification-radio" type="radio" 
                                                           name="document_verifications[main][{{ $index }}]" 
                                                           id="verify-main{{ $index }}-no"
                                                           value="not_verified"
                                                           {{ !$verifications['main'][$index] ? 'checked' : '' }}
                                                           data-target="remark-main{{ $index }}"
                                                           {{ $application->status === 'documents_resubmitted' && !$doc['resubmitted'] ? 'disabled' : '' }}>
                                                    <label class="form-check-label small" for="verify-main{{ $index }}-no">
                                                        <i class="ri-close-line text-danger"></i> Not Verified
                                                    </label>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        <input type="text" 
                                               name="verification_notes[main][{{ $index }}]" 
                                               id="remark-main{{ $index }}"
                                               class="form-control form-control-sm remark-input" 
                                               placeholder="Remark (if not verified)"
                                               value="{{ $verificationNotes['main'][$index] ?? '' }}"
                                               {{ $isSubmitted && !$doc['resubmitted'] ? 'readonly' : '' }}
                                               style="display: {{ $verifications['main'][$index] ? 'none' : 'block' }};">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-3 text-center text-muted">
                    <i class="ri-file-line fs-3 mb-2 d-block"></i>
                    <p class="mb-0 small">No main documents available</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Authorized Persons Documents Section -->
        @if($application->entityDetails->has_authorized_persons === 'yes' && !empty($authorizedDocs))
        <div class="card mb-3 shadow-sm rounded-3">
            <div class="card-header bg-light py-2">
                <h6 class="mb-0 d-flex justify-content-between align-items-center">
                    <span><i class="ri-user-line me-2"></i> Authorized Persons Documents Verification</span>
                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-bs-toggle="collapse" data-bs-target="#authDocsCollapse" aria-expanded="true">
                        <i class="ri-arrow-down-s-line"></i>
                    </button>
                </h6>
            </div>
            <div class="card-body p-0 collapse show" id="authDocsCollapse">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:25%">Document Type</th>
                                <th style="width:20%">Person Name</th>
                                <th style="width:15%">Relation</th>
                                <th style="width:10%" class="text-center">View</th>
                                <th style="width:15%" class="text-center">Verification</th>
                                <th style="width:15%">Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($authorizedDocs as $authIndex => $authDoc)
                            <tr class="{{ $authDoc['resubmitted'] ? 'table-warning' : '' }}">
                                <td class="align-middle small">
                                    <span class="badge bg-info fs-sm">{{ $authDoc['type'] }}</span>
                                    @if($authDoc['resubmitted'])
                                        <span class="badge bg-info fs-sm ms-1">Re-submitted</span>
                                    @endif
                                </td>
                                <td class="align-middle small">{{ htmlspecialchars($authDoc['person_name']) }}</td>
                                <td class="align-middle small">{{ htmlspecialchars($authDoc['person_relation']) }}</td>
                                <td class="text-center align-middle">
                                    @if($authDoc['path'])
                                    <button type="button" class="btn btn-outline-primary btn-sm view-auth-document" 
                                            data-modal="{{ $authDoc['doc_type'] === 'letter' ? 'authLetterModal' . $authDoc['person_index'] : 'authAadharModal' . $authDoc['person_index'] }}"
                                            data-person="{{ $authDoc['person_index'] }}"
                                            data-person-name="{{ htmlspecialchars($authDoc['person_name']) }}"
                                            data-doc-type="{{ $authDoc['doc_type'] }}"
                                            data-path="{{ $authDoc['path'] }}"
                                            data-s3-folder="{{ $authDoc['s3_folder'] }}"
                                            title="View {{ $authDoc['type'] }}">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    @else
                                    <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    @if($isSubmitted && !$authDoc['resubmitted'])
                                        <span class="badge {{ $verifications['authorized'][$authIndex] ? 'bg-success' : 'bg-danger' }} fs-sm">
                                            {{ $verifications['authorized'][$authIndex] ? 'Verified' : 'Not Verified' }}
                                        </span>
                                    @else
                                        <div class="d-flex justify-content-center gap-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input verification-radio" type="radio" 
                                                       name="document_verifications[authorized][{{ $authIndex }}]" 
                                                       id="verify-auth{{ $authIndex }}-yes"
                                                       value="verified"
                                                       {{ $verifications['authorized'][$authIndex] ? 'checked' : '' }}
                                                       data-target="remark-auth{{ $authIndex }}"
                                                       {{ $application->status === 'documents_resubmitted' && !$authDoc['resubmitted'] ? 'disabled' : '' }}>
                                                <label class="form-check-label small" for="verify-auth{{ $authIndex }}-yes">
                                                    <i class="ri-check-line text-success"></i> Verified
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input verification-radio" type="radio" 
                                                       name="document_verifications[authorized][{{ $authIndex }}]" 
                                                       id="verify-auth{{ $authIndex }}-no"
                                                       value="not_verified"
                                                       {{ !$verifications['authorized'][$authIndex] ? 'checked' : '' }}
                                                       data-target="remark-auth{{ $authIndex }}"
                                                       {{ $application->status === 'documents_resubmitted' && !$authDoc['resubmitted'] ? 'disabled' : '' }}>
                                                <label class="form-check-label small" for="verify-auth{{ $authIndex }}-no">
                                                    <i class="ri-close-line text-danger"></i> Not Verified
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <input type="text" 
                                           name="verification_notes[authorized][{{ $authIndex }}]" 
                                           id="remark-auth{{ $authIndex }}"
                                           class="form-control form-control-sm remark-input" 
                                           placeholder="Remark (if not verified)"
                                           value="{{ $verificationNotes['authorized'][$authIndex] ?? '' }}"
                                           {{ $isSubmitted && !$authDoc['resubmitted'] ? 'readonly' : '' }}
                                           style="display: {{ $verifications['authorized'][$authIndex] ? 'none' : 'block' }};">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Additional Documents Verification -->
        @if($additionalDocs->isNotEmpty())
        <div class="card mb-3 shadow-sm rounded-3">
            <div class="card-header bg-light py-2">
                <h6 class="mb-0 d-flex justify-content-between align-items-center">
                    <span>Additional Documents Verification</span>
                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-bs-toggle="collapse" data-bs-target="#additionalDocsVerificationCollapse" aria-expanded="true">
                        <i class="ri-arrow-down-s-line"></i>
                    </button>
                </h6>
            </div>
            <div class="card-body p-0 collapse show" id="additionalDocsVerificationCollapse">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:25%">Document Name</th>
                                <th style="width:25%">Remark</th>
                                <th style="width:10%" class="text-center">View</th>
                                <th style="width:20%" class="text-center">Verification</th>
                                <th style="width:20%">Verification Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($additionalDocs as $index => $doc)
                            <tr class="{{ $doc->resubmitted ? 'table-warning' : '' }}">
                                <td class="align-middle small">
                                    {{ $doc->document_name }}
                                    @if($doc->resubmitted)
                                        <span class="badge bg-info fs-sm ms-1">Re-submitted</span>
                                    @endif
                                </td>
                                <td class="align-middle small">{{ $doc->remark ?? 'N/A' }}</td>
                                <td class="text-center align-middle">
                                    @if($doc->upload_path)
                                    <button type="button" class="btn btn-outline-primary btn-sm view-additional-document" 
                                            data-modal="additionalDocumentModal{{ $index }}"
                                            data-doc-id="{{ $doc->id }}"
                                            data-path="{{ $doc->upload_path }}"
                                            data-s3-folder="additional_documents"
                                            title="View {{ $doc->document_name }}">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    @else
                                    <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    @if($isSubmitted && !$doc->resubmitted)
                                        <span class="badge {{ $verifications['additional'][$index] ? 'bg-success' : 'bg-danger' }} fs-sm">
                                            {{ $verifications['additional'][$index] ? 'Verified' : 'Not Verified' }}
                                        </span>
                                    @else
                                        <div class="d-flex justify-content-center gap-2">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input verification-radio" type="radio" 
                                                       name="document_verifications[additional][{{ $index }}]" 
                                                       id="verify-additional{{ $index }}-yes"
                                                       value="verified"
                                                       {{ $verifications['additional'][$index] ? 'checked' : '' }}
                                                       data-target="remark-additional{{ $index }}">
                                                <label class="form-check-label small" for="verify-additional{{ $index }}-yes">
                                                    <i class="ri-check-line text-success"></i> Verified
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input verification-radio" type="radio" 
                                                       name="document_verifications[additional][{{ $index }}]" 
                                                       id="verify-additional{{ $index }}-no"
                                                       value="not_verified"
                                                       {{ !$verifications['additional'][$index] ? 'checked' : '' }}
                                                       data-target="remark-additional{{ $index }}">
                                                <label class="form-check-label small" for="verify-additional{{ $index }}-no">
                                                    <i class="ri-close-line text-danger"></i> Not Verified
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <input type="text" 
                                           name="verification_notes[additional][{{ $index }}]" 
                                           id="remark-additional{{ $index }}"
                                           class="form-control form-control-sm remark-input" 
                                           placeholder="Remark (if not verified)"
                                           value="{{ $verificationNotes['additional'][$index] ?? '' }}"
                                           {{ $isSubmitted && !$doc->resubmitted ? 'readonly' : '' }}
                                           style="display: {{ $verifications['additional'][$index] ? 'none' : 'block' }};">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Additional Requirements -->
        <div class="card mb-3 shadow-sm rounded-3">
            <div class="card-header bg-light py-2">
                <h6 class="mb-0 d-flex justify-content-between align-items-center">
                    <span><i class="ri-add-line me-2"></i> Additional Requirements</span>
                    <button type="button" class="btn btn-link p-0 text-decoration-none" data-bs-toggle="collapse" data-bs-target="#additionalDocsCollapse" aria-expanded="true">
                        <i class="ri-arrow-down-s-line"></i>
                    </button>
                </h6>
            </div>
            <div class="card-body p-0 collapse show" id="additionalDocsCollapse">
                <table class="table table-bordered table-sm mb-0" id="additional-docs-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width:60%">Document Name</th>
                            <th style="width:30%">Remark</th>
                            <th style="width:10%" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($additionalDocs as $index => $doc)
                        <tr>
                            <td>
                                <input type="text" 
                                       name="additional_documents[{{$index}}][id]" 
                                       value="{{ $doc->id }}" 
                                       hidden>
                                <input type="text" 
                                       name="additional_documents[{{$index}}][name]" 
                                       class="form-control form-control-sm" 
                                       value="{{ $doc->document_name }}" 
                                       {{ $isSubmitted && !$doc->resubmitted && $doc->status !== 'pending' ? 'readonly' : '' }}
                                       required>
                            </td>
                            <td>
                                <input type="text" 
                                       name="additional_documents[{{$index}}][remark]" 
                                       class="form-control form-control-sm" 
                                       value="{{ $doc->remark ?? '' }}" 
                                       {{ $isSubmitted && !$doc->resubmitted && $doc->status !== 'pending' ? 'readonly' : '' }}>
                            </td>
                            <td class="text-center align-middle">
                                @if(!$isSubmitted || $doc->resubmitted || $doc->status === 'pending')
                                    <button type="button" class="btn btn-danger btn-sm remove-row" title="Remove"><i class="ri-close-line"></i></button>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted small py-2">No additional requirements</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(!$isSubmitted || $application->status === 'documents_resubmitted' || $application->status === 'documents_pending')
            <div class="card-footer py-2 bg-light">
                <button type="button" class="btn btn-outline-primary btn-sm" id="add-doc">
                    <i class="ri-add-line me-1"></i> Add Document
                </button>
            </div>
            @endif
        </div>

        @if((!$isSubmitted || $application->status === 'documents_resubmitted' || $application->status === 'documents_pending') && (!empty($mainDocuments) || !empty($authorizedDocs) || !empty($additionalDocs)))
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success btn-sm" id="submitBtn">
                <i class="ri-check-line me-1"></i> Submit Verification
            </button>
        </div>
        @endif
    </form>

    <!-- Main Documents Modals -->
    @foreach($mainDocuments as $index => $doc)
        <div class="modal fade" id="mainDocumentModal{{ $index }}" tabindex="-1" aria-labelledby="mainDocumentLabel{{ $index }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content rounded-3">
                    <div class="modal-header bg-primary text-white">
                        <h6 class="modal-title" id="mainDocumentLabel{{ $index }}">{{ $doc['type'] }} Document @if($doc['resubmitted'])(Re-submitted)@endif</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="zoom-container" id="mainZoomContainer{{ $index }}">
                            <iframe id="mainDocumentFrame{{ $index }}"
                                    src="{{ Storage::disk('s3')->url('Connect/Distributor/' . $doc['s3_folder'] . '/' . $doc['path']) }}"
                                    title="{{ $doc['type'] }} Document"
                                    style="width: 100%; height: 500px; border: none;"></iframe>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <div class="d-flex align-items-center me-auto">
                            <button type="button" class="btn btn-outline-secondary btn-sm me-2" id="mainZoomOutBtn{{ $index }}">
                                <i class="ri-zoom-out-line"></i>
                            </button>
                            <span id="mainZoomLevel{{ $index }}">100%</span>
                            <button type="button" class="btn btn-outline-secondary btn-sm ms-2" id="mainZoomInBtn{{ $index }}">
                                <i class="ri-zoom-in-line"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm ms-2" id="mainResetZoomBtn{{ $index }}">
                                <i class="ri-restart-line"></i>
                            </button>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Authorized Persons Document Modals -->
    @foreach($authorizedDocs as $index => $authDoc)
        <div class="modal fade" id="{{ $authDoc['doc_type'] === 'letter' ? 'authLetterModal' . $authDoc['person_index'] : 'authAadharModal' . $authDoc['person_index'] }}" tabindex="-1" aria-labelledby="{{ $authDoc['doc_type'] === 'letter' ? 'authLetterModalLabel' : 'authAadharModalLabel' }}{{ $authDoc['person_index'] }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content rounded-3">
                    <div class="modal-header bg-primary text-white">
                        <h6 class="modal-title" id="{{ $authDoc['doc_type'] === 'letter' ? 'authLetterModalLabel' : 'authAadharModalLabel' }}{{ $authDoc['person_index'] }}">{{ $authDoc['type'] }} - {{ $authDoc['person_name'] }} @if($authDoc['resubmitted'])(Re-submitted)@endif</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <iframe src="{{ Storage::disk('s3')->url('Connect/Distributor/' . $authDoc['s3_folder'] . '/' . $authDoc['path']) }}"
                                title="{{ $authDoc['type'] }}"
                                style="width: 100%; height: 500px; border: none;"></iframe>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Additional Documents Modals -->
    @foreach($additionalDocs as $index => $doc)
        @if($doc->upload_path)
        <div class="modal fade" id="additionalDocumentModal{{ $index }}" tabindex="-1" aria-labelledby="additionalDocumentLabel{{ $index }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content rounded-3">
                    <div class="modal-header bg-primary text-white">
                        <h6 class="modal-title" id="additionalDocumentLabel{{ $index }}">{{ $doc->document_name }} @if($doc->resubmitted)(Re-submitted)@endif</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="zoom-container" id="additionalZoomContainer{{ $index }}">
                            <iframe id="additionalDocumentFrame{{ $index }}"
                                    src="{{ Storage::disk('s3')->url('Connect/Distributor/additional_documents/' . $doc->upload_path) }}"
                                    title="{{ $doc->document_name }}"
                                    style="width: 100%; height: 500px; border: none;"></iframe>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <div class="d-flex align-items-center me-auto">
                            <button type="button" class="btn btn-outline-secondary btn-sm me-2" id="additionalZoomOutBtn{{ $index }}">
                                <i class="ri-zoom-out-line"></i>
                            </button>
                            <span id="additionalZoomLevel{{ $index }}">100%</span>
                            <button type="button" class="btn btn-outline-secondary btn-sm ms-2" id="additionalZoomInBtn{{ $index }}">
                                <i class="ri-zoom-in-line"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm ms-2" id="additionalResetZoomBtn{{ $index }}">
                                <i class="ri-restart-line"></i>
                            </button>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endforeach
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
    .fs-sm {
        font-size: 0.8rem !important;
    }
    .modal-lg {
        max-width: 80%;
    }
    .zoom-container {
        background: #f8f9fa;
        overflow: auto;
        position: relative;
        min-height: 500px;
    }
    .zoom-container iframe {
        transition: transform 0.3s ease;
        min-width: 100%;
        transform-origin: 0 0;
    }
    .remark-input {
        transition: all 0.2s ease;
    }
    .remark-input:read-only {
        background-color: #f8f9fa;
    }
    .text-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .modal-header {
        background: linear-gradient(135deg, #a8dadc 0%, #f1faee 100%) !important;
        border-bottom: none !important;
    }
    .is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }
    .alert {
        border-radius: 0.375rem;
    }
    .form-check-input[type="radio"] {
        margin-top: 0.15rem;
    }
    .form-check-sm {
        margin-bottom: 0.25rem;
    }
    .form-check-sm .form-check-input {
        margin-top: 0.1rem;
    }
    .verification-radio:checked + .form-check-label {
        font-weight: 600;
    }
    .table-warning {
        background-color: rgba(255, 193, 7, 0.1);
    }
    .modal-body {
        padding: 0 !important;
        overflow: hidden;
    }
    .modal-footer {
        padding: 0.75rem 1rem;
    }
    .entity-field:read-only, .entity-field:disabled {
        background-color: #f8f9fa;
    }
    @media (max-width: 576px) {
        .modal-lg {
            max-width: 95%;
        }
        .table-responsive {
            font-size: 0.75rem;
        }
        .form-check-inline {
            display: block;
            margin-bottom: 0.5rem;
        }
        .zoom-container {
            min-height: 300px;
        }
        .zoom-container iframe {
            height: 300px !important;
        }
    }
    @media print {
        .btn, .modal, .card-footer, .alert, .btn-link { display: none !important; }
        .table { font-size: 0.8rem; }
        .collapse { display: block !important; }
        .form-control, .form-select { border: none !important; background: transparent !important; }
        .row > div { margin-bottom: 0.5rem; }
    }
</style>

<script>
let mainZoom = {};
let additionalZoom = {};
const zoomStep = 0.25;
const minZoom = 0.5;
const maxZoom = 3.0;
let additionalDocsCounter = {{ count($additionalDocs) }};

document.addEventListener('DOMContentLoaded', function() {
    // Initialize zoom levels for all documents
    @foreach($mainDocuments as $index => $doc)
        mainZoom['{{ $index }}'] = 1;
    @endforeach
    @foreach($additionalDocs as $index => $doc)
        @if($doc->upload_path)
        additionalZoom['{{ $index }}'] = 1;
        @endif
    @endforeach

    // Toggle remarks based on verification status
    document.querySelectorAll('.verification-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            const targetId = this.dataset.target;
            const remarkInput = document.getElementById(targetId);
            if (remarkInput) {
                remarkInput.style.display = this.value === 'verified' ? 'none' : 'block';
                if (this.value === 'verified') {
                    remarkInput.value = '';
                }
            }
        });
        
        // Initialize remark visibility on page load
        if (radio.checked) {
            const targetId = radio.dataset.target;
            const remarkInput = document.getElementById(targetId);
            if (remarkInput) {
                remarkInput.style.display = radio.value === 'verified' ? 'none' : 'block';
            }
        }
    });

    // Toggle editability of entity fields
    document.querySelectorAll('.edit-entity-fields').forEach(btn => {
        btn.addEventListener('click', function() {
            const inputs = document.querySelectorAll('.entity-field');
            const isEditable = inputs[0]?.readOnly || inputs[0]?.disabled || false;
            inputs.forEach(input => {
                if (input.type === 'checkbox') {
                    input.disabled = !isEditable;
                } else if (input.tagName === 'SELECT') {
                    input.disabled = !isEditable;
                } else {
                    input.readOnly = !isEditable;
                }
                input.classList.toggle('entity-field:read-only', !isEditable);
            });
            btn.innerHTML = isEditable ? '<i class="ri-save-line"></i> Update' : '<i class="ri-edit-line"></i> Edit';
        });
    });

    // Entity Details Form Submission
    const entityForm = document.getElementById('entityDetailsForm');
    if (entityForm) {
        entityForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitEntityBtn');
            if (!submitBtn) return;

            const notVerifiedRadio = entityForm.querySelector('input[name="entity_verification"]:checked[value="not_verified"]');
            const hasNotVerifiedFields = !!notVerifiedRadio;

            // Validate required fields
            const requiredFields = [
                { selector: 'input[name="entity_fields[establishment_name]"]', message: 'Establishment Name is required.' },
            ];

            let hasErrors = false;
            requiredFields.forEach(field => {
                const input = entityForm.querySelector(field.selector);
                if (input && input.value.trim() === '') {
                    input.classList.add('is-invalid');
                    alert(field.message);
                    hasErrors = true;
                } else {
                    input.classList.remove('is-invalid');
                }
            });

            // Validate authorized persons if applicable
            // const hasAuthPersons = entityForm.querySelector('select[name="entity_fields[has_authorized_persons]"]').value === 'yes';
            // if (hasAuthPersons) {
            //     const authNameInputs = entityForm.querySelectorAll('input[name$="[name]"]');
            //     authNameInputs.forEach(input => {
            //         if (input.value.trim() === '') {
            //             input.classList.add('is-invalid');
            //             alert('Authorized Person Name is required.');
            //             hasErrors = true;
            //         } else {
            //             input.classList.remove('is-invalid');
            //         }
            //     });
            // }

            if (hasErrors) return;

            if (hasNotVerifiedFields && !confirm('Entity details are marked as "Not Verified". This will send the application back for corrections. Do you want to continue?')) {
                return;
            }

            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="ri-loader-2-line spinner-border spinner-border-sm me-1"></i>Processing...';
            submitBtn.disabled = true;

            const formData = new FormData(entityForm);
            fetch(entityForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Remove existing alerts
                    document.querySelectorAll('.alert').forEach(alert => {
                        if (!alert.classList.contains('alert-info')) {
                            alert.remove();
                        }
                    });

                    // Show success message
                    const successMsg = document.createElement('div');
                    successMsg.className = 'alert alert-success alert-dismissible fade show';
                    successMsg.innerHTML = `
                        <i class="ri-check-line me-2"></i>${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    `;
                    entityForm.parentNode.insertBefore(successMsg, entityForm);
                    successMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    // Update verification status in UI
                    const verificationValue = entityForm.querySelector('input[name="entity_verification"]:checked')?.value;
                    if (verificationValue) {
                        const badge = entityForm.querySelector('span.badge');
                        if (badge) {
                            badge.className = `badge ${verificationValue === 'verified' ? 'bg-success' : 'bg-danger'} fs-sm`;
                            badge.textContent = verificationValue === 'verified' ? 'Verified' : 'Not Verified';
                        }
                    }

                    // Disable inputs and buttons if submitted
                    if (data.status === 'document_verified' || !['documents_resubmitted', 'documents_pending'].includes('{{ $application->status }}')) {
                        entityForm.querySelectorAll('.entity-field, input[name="entity_verification"], input[name="entity_note"]').forEach(input => {
                            if (input.type === 'checkbox' || input.tagName === 'SELECT') {
                                input.disabled = true;
                            } else {
                                input.readOnly = true;
                            }
                            input.classList.add('entity-field:read-only');
                        });
                        entityForm.querySelectorAll('.edit-entity-fields').forEach(btn => btn.style.display = 'none');
                        submitBtn.style.display = 'none';
                    }
                } else {
                    throw new Error(data.message || 'Something went wrong');
                }
            })
            .catch(error => {
                console.error('Entity submission error:', error);
                const errorMsg = document.createElement('div');
                errorMsg.className = 'alert alert-danger alert-dismissible fade show';
                errorMsg.innerHTML = `
                    <i class="ri-error-warning-line me-2"></i>
                    <strong>Error:</strong> ${error.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                entityForm.parentNode.insertBefore(errorMsg, entityForm);
                errorMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    }

    // Main documents modal handlers
    document.querySelectorAll('.view-main-document').forEach(btn => {
        btn.addEventListener('click', function() {
            const index = this.dataset.index;
            const s3Folder = this.dataset.s3Folder;
            const path = this.dataset.path;
            const modalId = this.dataset.modal;
            
            const modalElement = document.getElementById(modalId);
            const modal = new bootstrap.Modal(modalElement);
            
            mainZoom[index] = 1;
            const frame = document.getElementById(`mainDocumentFrame${index}`);
            if (frame) {
                frame.src = '{{ Storage::disk("s3")->url("Connect/Distributor/") }}' + s3Folder + '/' + path;
                frame.style.transform = 'scale(1)';
                const zoomLevel = document.getElementById(`mainZoomLevel${index}`);
                if (zoomLevel) {
                    zoomLevel.textContent = '100%';
                }
            }
            
            modal.show();
        });
    });

    // Authorized persons document handlers
    document.querySelectorAll('.view-auth-document').forEach(btn => {
        btn.addEventListener('click', function() {
            const modalId = this.dataset.modal;
            const path = this.dataset.path;
            const s3Folder = this.dataset.s3Folder;
            
            const modalElement = document.getElementById(modalId);
            const modal = new bootstrap.Modal(modalElement);
            const iframe = modalElement.querySelector('iframe');
            
            if (iframe && path) {
                iframe.src = '{{ Storage::disk("s3")->url("Connect/Distributor/") }}' + s3Folder + '/' + path;
            }
            
            modal.show();
        });
    });

    // Additional documents modal handlers
    document.querySelectorAll('.view-additional-document').forEach(btn => {
        btn.addEventListener('click', function() {
            const modalId = this.dataset.modal;
            const path = this.dataset.path;
            const s3Folder = this.dataset.s3Folder;
            const docId = this.dataset.docId;
            
            const modalElement = document.getElementById(modalId);
            const modal = new bootstrap.Modal(modalElement);
            
            const index = modalId.replace('additionalDocumentModal', '');
            additionalZoom[index] = 1;
            
            const iframe = document.getElementById(`additionalDocumentFrame${index}`);
            if (iframe && path) {
                iframe.src = '{{ Storage::disk("s3")->url("Connect/Distributor/") }}' + s3Folder + '/' + path;
                iframe.style.transform = 'scale(1)';
                const zoomLevel = document.getElementById(`additionalZoomLevel${index}`);
                if (zoomLevel) {
                    zoomLevel.textContent = '100%';
                }
            }
            
            modal.show();
        });
    });

    // Setup zoom controls for main documents
    @foreach($mainDocuments as $index => $doc)
        setupZoomControls('main', '{{ $index }}');
    @endforeach

    // Setup zoom controls for additional documents
    @foreach($additionalDocs as $index => $doc)
        @if($doc->upload_path)
        setupZoomControls('additional', '{{ $index }}');
        @endif
    @endforeach

    // Modal hidden event to clean up iframes
    document.querySelectorAll('.modal').forEach(modalElement => {
        modalElement.addEventListener('hidden.bs.modal', function() {
            const iframes = this.querySelectorAll('iframe');
            iframes.forEach(iframe => {
                iframe.src = 'about:blank';
            });
        });
    });

    // Add document functionality
    document.getElementById('add-doc')?.addEventListener('click', function() {
        const tbody = document.querySelector('#additional-docs-table tbody');
        if (tbody) {
            const emptyRow = tbody.querySelector('tr td[colspan="3"]');
            if (emptyRow) emptyRow.closest('tr').remove();
            
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <input type="text" 
                           name="additional_documents[${additionalDocsCounter}][name]" 
                           class="form-control form-control-sm" 
                           placeholder="Document name" 
                           required>
                </td>
                <td>
                    <input type="text" 
                           name="additional_documents[${additionalDocsCounter}][remark]" 
                           class="form-control form-control-sm" 
                           placeholder="Remark">
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-danger btn-sm remove-row" title="Remove">
                        <i class="ri-close-line"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
            additionalDocsCounter++;
        }
    });

    // Remove row functionality
    document.addEventListener('click', function(e) {
        const removeBtn = e.target.closest('.remove-row');
        if (removeBtn) {
            if (confirm('Are you sure you want to remove this requirement?')) {
                const row = removeBtn.closest('tr');
                row.remove();
                
                const tbody = document.querySelector('#additional-docs-table tbody');
                if (tbody && tbody.children.length === 0) {
                    const emptyRow = document.createElement('tr');
                    emptyRow.innerHTML = '<td colspan="3" class="text-center text-muted small py-2">No additional requirements</td>';
                    tbody.appendChild(emptyRow);
                }
            }
        }
    });

    // Enhanced form validation for document verification
    const verifyForm = document.getElementById('verifyForm');
    if (verifyForm) {
    verifyForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitBtn');
        if (!submitBtn) return;
        
        const notVerifiedRadios = verifyForm.querySelectorAll('input[name^="document_verifications"]:checked[value="not_verified"]');
        let hasMissingRemarks = false;
        
        // Check if all "Not Verified" documents have remarks
        notVerifiedRadios.forEach(radio => {
            const targetId = radio.dataset.target;
            const remarkInput = document.getElementById(targetId);
            if (remarkInput && (!remarkInput.value || remarkInput.value.trim() === '')) {
                hasMissingRemarks = true;
                remarkInput.classList.add('is-invalid');
                
                // Scroll to the first missing remark
                if (!document.querySelector('.is-invalid:not(.checked)')) {
                    remarkInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    remarkInput.focus();
                }
                remarkInput.classList.add('checked');
            } else if (remarkInput) {
                remarkInput.classList.remove('is-invalid');
                remarkInput.classList.remove('checked');
            }
        });
        
        // Validate additional document names
        const additionalNameInputs = verifyForm.querySelectorAll('input[name$="[name]"]');
        let hasInvalidAdditional = false;

        additionalNameInputs.forEach(input => {
            const row = input.closest('tr');
            const remarkInput = row.querySelector('input[name$="[remark]"]');
            if (input.value.trim() === '' && remarkInput && remarkInput.value.trim() !== '') {
                hasInvalidAdditional = true;
                input.classList.add('is-invalid');
                if (remarkInput) remarkInput.classList.add('is-invalid');
            } else if (input.value.trim() === '') {
                hasInvalidAdditional = true;
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
                if (remarkInput) remarkInput.classList.remove('is-invalid');
            }
        });

        if (hasInvalidAdditional) {
            alert('Please fill all additional document names or clear the remarks for empty documents.');
            return;
        }
        
        if (hasMissingRemarks) {
            alert('Please provide remarks for all documents marked as "Not Verified". Remarks are mandatory when rejecting documents.');
            return;
        }
        
        if (notVerifiedRadios.length > 0 && !confirm('Some documents are marked as "Not Verified". This will send the application back for corrections. Do you want to continue?')) {
            return;
        }
        
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="ri-loader-2-line spinner-border spinner-border-sm me-1"></i>Processing...';
        submitBtn.disabled = true;
        
        let token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (token) {
            const existingToken = verifyForm.querySelector('input[name="_token"]');
            if (!existingToken) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = '_token';
                hiddenInput.value = token;
                verifyForm.appendChild(hiddenInput);
            }
        }
        
        const formData = new FormData(verifyForm);
        fetch(verifyForm.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                document.querySelectorAll('.alert').forEach(alert => {
                    if (!alert.classList.contains('alert-info')) {
                        alert.remove();
                    }
                });
                
                const successMsg = document.createElement('div');
                successMsg.className = 'alert alert-success alert-dismissible fade show';
                successMsg.innerHTML = `
                    <i class="ri-check-line me-2"></i>${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                verifyForm.parentNode.insertBefore(successMsg, verifyForm);
                
                setTimeout(() => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        location.reload();
                    }
                }, 1500);
            } else {
                throw new Error(data.message || 'Something went wrong');
            }
        })
        .catch(error => {
            console.error('Submission error:', error);
            document.querySelectorAll('.alert.alert-danger').forEach(alert => alert.remove());
            const errorMsg = document.createElement('div');
            errorMsg.className = 'alert alert-danger alert-dismissible fade show';
            errorMsg.innerHTML = `
                <i class="ri-error-warning-line me-2"></i>
                <strong>Error:</strong> ${error.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            verifyForm.parentNode.insertBefore(errorMsg, verifyForm);
            errorMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
}

   // Real-time validation for remarks when "Not Verified" is selected
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('verification-radio') && e.target.value === 'not_verified') {
        const targetId = e.target.dataset.target;
        const remarkInput = document.getElementById(targetId);
        if (remarkInput) {
            // Check if remark is empty and show validation immediately
            if (!remarkInput.value || remarkInput.value.trim() === '') {
                remarkInput.classList.add('is-invalid');
            } else {
                remarkInput.classList.remove('is-invalid');
            }
        }
    }
});

// Real-time validation when user types in remark fields
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('remark-input')) {
        // Remove invalid class when user starts typing
        if (e.target.value.trim() !== '') {
            e.target.classList.remove('is-invalid');
        }
        
        // Also validate additional documents
        if (e.target.name && e.target.name.includes('additional_documents') && e.target.name.includes('[name]')) {
            const row = e.target.closest('tr');
            const remarkInput = row.querySelector('input[name$="[remark]"]');
            if (e.target.value.trim() === '' && remarkInput && remarkInput.value.trim() !== '') {
                e.target.classList.add('is-invalid');
                remarkInput.classList.add('is-invalid');
            } else {
                e.target.classList.remove('is-invalid');
                if (remarkInput) remarkInput.classList.remove('is-invalid');
            }
        }
    }
});
});

// Zoom control setup function
function setupZoomControls(type, index) {
    const zoomInBtn = document.getElementById(`${type}ZoomInBtn${index}`);
    const zoomOutBtn = document.getElementById(`${type}ZoomOutBtn${index}`);
    const resetZoomBtn = document.getElementById(`${type}ResetZoomBtn${index}`);
    
    if (zoomInBtn) {
        zoomInBtn.addEventListener('click', function() {
            const currentZoom = type === 'main' ? mainZoom[index] : additionalZoom[index];
            if (currentZoom < maxZoom) {
                const newZoom = currentZoom + zoomStep;
                updateZoom(type, index, newZoom);
            }
        });
    }
    
    if (zoomOutBtn) {
        zoomOutBtn.addEventListener('click', function() {
            const currentZoom = type === 'main' ? mainZoom[index] : additionalZoom[index];
            if (currentZoom > minZoom) {
                const newZoom = currentZoom - zoomStep;
                updateZoom(type, index, newZoom);
            }
        });
    }
    
    if (resetZoomBtn) {
        resetZoomBtn.addEventListener('click', function() {
            updateZoom(type, index, 1);
        });
    }
}

// Zoom update function
function updateZoom(type, index, zoomLevel) {
    const frame = document.getElementById(`${type}DocumentFrame${index}`);
    const levelDisplay = document.getElementById(`${type}ZoomLevel${index}`);
    
    if (frame && levelDisplay) {
        const clampedZoom = Math.max(minZoom, Math.min(maxZoom, zoomLevel));
        frame.style.transform = `scale(${clampedZoom})`;
        frame.style.transformOrigin = '0 0';
        levelDisplay.textContent = Math.round(clampedZoom * 100) + '%';
        
        if (type === 'main') {
            mainZoom[index] = clampedZoom;
        } else {
            additionalZoom[index] = clampedZoom;
        }
    }
}
</script>
@endsection