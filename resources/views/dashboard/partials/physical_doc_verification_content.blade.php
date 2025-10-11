@php
    $canVerify = $checks->isNotEmpty() && $checks->min('created_at')?->lte(now());
    $allVerified = $checks->every(fn($check) => $check->status === 'verified');

    // Verifier (from submitted_by; assumes Employee model)
    $verifierId = $checks->first()?->submitted_by;
    $verifierName = $verifierId 
        ? \App\Models\Employee::where('employee_id', $verifierId)->value('emp_name') ?? 'N/A' 
        : 'N/A';

    // Verified date (min across verified)
    $verifiedDate = $checks->where('status', 'verified')->min('verified_date')
        ? \Carbon\Carbon::parse($checks->where('status', 'verified')->min('verified_date'))->format('Y-m-d')
        : 'N/A';
        
    // Base S3 path
    $baseS3Path = 'Connect/Distributor/';
@endphp

<div class="table-responsive">
    <table class="table table-sm table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>Document Type</th>
                <th>Received</th>
                <th>Verified</th>
                <th>Document/File</th>
                <th>Remarks/Amount</th>
                <th>Verified By</th>
                <th>Verified Date</th>
            </tr>
        </thead>
        <tbody>
            @if ($checks->isNotEmpty() || $checkpointTypes->isNotEmpty() || (isset($supportingDocuments) && $supportingDocuments->isNotEmpty()))
                {{-- Core Physical Documents --}}
                @if($coreTypes->isNotEmpty())
                    <tr><td colspan="7" class="bg-light"><strong>Core Physical Documents</strong></td></tr>
                    @foreach($coreTypes as $type)
                        @php 
                            $files = $groupedChecks->get($type, collect());
                            $label = match($type) {
                                'agreement_copy' => 'Agreement Copy',
                                'security_cheques' => 'Security Cheques',
                                'security_deposit' => 'Security Deposit',
                                default => ucfirst(str_replace('_', ' ', $type))
                            };
                            $isAmount = $type === 'security_deposit' && filled($files->first()?->amount);
                            $isMultiFile = $type === 'security_cheques';
                        @endphp
                        @if($files->isNotEmpty())
                            <tr>
                                <td>{{ $label }}</td>
                                <td><span class="badge bg-{{ $files->first()->received ? 'success' : 'secondary' }}">{{ $files->first()->received ? 'Yes' : 'No' }}</span></td>
                                <td><span class="badge bg-{{ $files->first()->status === 'verified' ? 'success' : 'danger' }}">{{ $files->first()->status === 'verified' ? 'Yes' : 'No' }}</span></td>
                                <td>
                                    @if($isMultiFile)
                                        {{-- Multiple files for security cheques --}}
                                        <div class="d-flex flex-column gap-1">
                                            @if($files->where('file_path', '!=', null)->isNotEmpty())
                                                @foreach($files as $fileCheck)
                                                    @if($fileCheck->file_path)
                                                        @php
                                                            // Construct S3 path and URL
                                                            $s3Path = $baseS3Path . $fileCheck->document_type . '/' . $fileCheck->file_path;
                                                            $url = Storage::disk('s3')->url($s3Path);
                                                            $filename = $fileCheck->original_filename ?? basename($fileCheck->file_path);
                                                            $fileExtension = strtolower(pathinfo($fileCheck->file_path, PATHINFO_EXTENSION));
                                                            $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                            $isPdf = $fileExtension === 'pdf';
                                                            $target = $isImage || $isPdf ? '_blank' : '_blank'; // Open all in new tab
                                                        @endphp
                                                        <div class="file-preview-item">
                                                            <a href="{{ $url }}" target="{{ $target }}" class="btn btn-outline-primary btn-sm" download="{{ $filename }}">
                                                                <i class="ri-eye-line me-1"></i> View {{ $loop->iteration }}
                                                            </a>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @else
                                                <span class="text-muted small">No files uploaded</span>
                                            @endif
                                        </div>
                                    @else
                                        {{-- Single file for other types --}}
                                        @php
                                            $checkFile = $files->first();
                                            if ($checkFile && $checkFile->file_path) {
                                                // Construct S3 path and URL
                                                $s3Path = $baseS3Path . $checkFile->document_type . '/' . $checkFile->file_path;
                                                $url = Storage::disk('s3')->url($s3Path);
                                                $filename = $checkFile->original_filename ?? basename($checkFile->file_path);
                                                $fileExtension = strtolower(pathinfo($checkFile->file_path, PATHINFO_EXTENSION));
                                                $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                $isPdf = $fileExtension === 'pdf';
                                                $target = $isImage || $isPdf ? '_blank' : '_blank';
                                            } else {
                                                $url = null;
                                                $filename = '';
                                            }
                                            $displayType = Str::title(str_replace('_', ' ', $label));
                                        @endphp
                                        @if($url)
                                            <div class="file-preview-item">
                                                <a href="{{ $url }}" target="{{ $target }}" class="btn btn-outline-primary btn-sm" download="{{ $filename }}">
                                                    <i class="ri-eye-line me-1"></i> View
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-muted small">No file</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if($isAmount)
                                        ₹{{ number_format($files->first()->amount, 2) }}
                                    @elseif($files->first()->reason && $files->first()->status !== 'verified')
                                        {{ $files->first()->reason }}
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $verifierName }}</td>
                                <td>{{ $verifiedDate }}</td>
                            </tr>
                        @endif
                    @endforeach
                @endif

                {{-- Supporting Documents --}}
                @if(isset($supportingDocuments) && $supportingDocuments->isNotEmpty())
                    <tr><td colspan="7" class="bg-light"><strong>Supporting Documents (Digital Copies)</strong></td></tr>
                    @foreach($supportingTypes as $type)
                        @php 
                            $check = $groupedChecks->get($type, collect())->first();
                            $label = ucfirst(str_replace('_', ' ', $type));
                            $supportingDoc = $supportingDocuments->firstWhere('type', $type);
                            
                            if ($supportingDoc && isset($supportingDoc['path'])) {
                                // Construct S3 path and URL for supporting documents
                                $s3Path = $baseS3Path . $type . '/' . $supportingDoc['path'];
                                $url = Storage::disk('s3')->url($s3Path);
                                $fileExtension = strtolower(pathinfo($supportingDoc['path'], PATHINFO_EXTENSION));
                                $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                $isPdf = $fileExtension === 'pdf';
                                $target = $isImage || $isPdf ? '_blank' : '_blank';
                            } else {
                                $url = null;
                            }
                            
                            $filename = $supportingDoc['existing_file'] ?? 'Digital File';
                            $received = $check->received ?? false;
                            $status = $check->status ?? 'pending';
                            $reason = $check->reason ?? '';
                        @endphp
                        <tr>
                            <td>{{ $label }}</td>
                            <td><span class="badge bg-{{ $received ? 'success' : 'secondary' }}">{{ $received ? 'Yes' : 'No' }}</span></td>
                            <td><span class="badge bg-{{ $status === 'verified' ? 'success' : 'danger' }}">{{ $status === 'verified' ? 'Yes' : 'No' }}</span></td>
                            <td>
                                <div class="file-preview-item">
                                    @if($url)
                                        <a href="{{ $url }}" target="{{ $target }}" class="btn btn-outline-primary btn-sm" download="{{ $filename }}">
                                            <i class="ri-eye-line me-1"></i> View
                                        </a>
                                    @else
                                        <span class="text-muted small">Digital available</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $reason && $status !== 'verified' ? $reason : 'N/A' }}</td>
                            <td>{{ $verifierName }}</td>
                            <td>{{ $verifiedDate }}</td>
                        </tr>
                    @endforeach
                @endif

   {{-- Checkpoint Documents --}}
@if($checkpointTypes->isNotEmpty())
    <tr><td colspan="7" class="bg-light"><strong>Checkpoint Documents</strong></td></tr>
    @foreach($checkpointTypes as $type)
        @php 
            $physicalCheck = $groupedChecks->get($type, collect())->first();
            $checkpoint = $application->checkpoints->firstWhere('checkpoint_name', $type);
            
            // Skip if no record exists in either table
            if (!$physicalCheck && !$checkpoint) {
                continue;
            }
            
            // Determine which data to use
            if ($physicalCheck) {
                $check = $physicalCheck;
                $wasReceived = $check->received;
                $isVerified = $check->status === 'verified';
                $reason = $check->reason;
                $verifierId = $check->submitted_by;
                $verifiedDate = $check->verified_date 
                    ? \Carbon\Carbon::parse($check->verified_date)->format('Y-m-d')
                    : 'N/A';
            } else {
                $check = $checkpoint;
                $wasReceived = true; // Checkpoints are always considered received
                $isVerified = $check->status === 'verified';
                $reason = $check->reason;
                $verifierId = $check->submitted_by;
                $verifiedDate = $check->updated_at 
                    ? \Carbon\Carbon::parse($check->updated_at)->format('Y-m-d')
                    : 'N/A';
            }
            
            $label = isset($customLabels[$type]) 
                ? $customLabels[$type] 
                : preg_replace('/_(\d+)$/', '', ucfirst(str_replace('_', ' ', $type)));
            
            // Get verifier info
            $verifierName = $verifierId 
                ? \App\Models\Employee::where('employee_id', $verifierId)->value('emp_name') ?? 'N/A' 
                : 'N/A';
        @endphp
        <tr>
            <td>{{ $label }}</td>
            <td><span class="badge bg-{{ $wasReceived ? 'success' : 'secondary' }}">{{ $wasReceived ? 'Yes' : 'No' }}</span></td>
            <td><span class="badge bg-{{ $isVerified ? 'success' : 'danger' }}">{{ $isVerified ? 'Yes' : 'No' }}</span></td>
            <td><span class="text-muted small">No upload required</span></td>
            <td>{{ $reason && !$isVerified ? $reason : 'N/A' }}</td>
            <td>{{ $verifierName }}</td>
            <td>{{ $verifiedDate }}</td>
        </tr>
    @endforeach
@endif


                @if(!$allVerified)
                    <tr><td colspan="7" class="text-warning text-center">Some documents are pending verification.</td></tr>
                @else
                    <tr><td colspan="7" class="text-success text-center">All physical documents verified!</td></tr>
                @endif
            @else
                <tr><td colspan="7" class="text-center">No physical documents found for this application.</td></tr>
            @endif
        </tbody>
    </table>
</div>

@if (!$allVerified && $application->physical_docs_status == 'physical_docs_pending' && $canVerify)
    <div class="mt-3">
        <a href="{{ route('approvals.physical-documents', $application) }}" class="btn btn-sm btn-primary">
            <i class="ri-edit-line me-1"></i> Manage Physical Documents
        </a>
    </div>
@endif

<script>
// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>