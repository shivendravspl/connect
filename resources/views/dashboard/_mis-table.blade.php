@if($misApplications->isEmpty())
<div class="alert alert-info no-data-message">No applications pending MIS processing.</div>
@else
<div class="table-responsive">
    <table class="table table-hover compact-table">
        <thead class="table-light">
            <tr>
                <th>Sr. No</th>
                <th>Distributor Name</th>
                <th>Zone</th>
                <th>Date Received</th>
                <th>Doc Verification</th>
                <th>Agreement Status</th>
                <th>Physical Docs</th>
                <th>Final Status</th>
                <th>Action</th>
                <th>Doc View</th>
            </tr>
        </thead>
        <tbody>
            @foreach($misApplications as $index => $application)
            <tr class="status-card {{ $application->status_badge ?? '' }} expandable-panel">
                <td>{{ $misApplications->firstItem() + $index }}</td>
                <td>{{ $application->entityDetails->legal_name ?? 'N/A' }}</td>
                <td>{{ $application->territoryDetail->zone->zone_name ?? 'N/A' }}</td>
                <td>
                    @php
                        $gmApprovedLog = $application->approvalLogs->where('action', 'approved')->where('role', 'General Manager')->sortByDesc('created_at')->first();
                    @endphp
                    {{ $gmApprovedLog ? $gmApprovedLog->created_at->format('d M Y') : 'N/A' }}
                </td>
                <td>
                    @if($application->documentVerifications->isNotEmpty() && $application->documentVerifications->where('status', 'verified')->count() > 0)
                        <span class="badge bg-success">Verified</span>
                    @elseif($application->documentVerifications->isNotEmpty() && $application->documentVerifications->where('status', 'pending')->count() > 0)
                        <span class="badge bg-warning">In Progress</span>
                    @else
                        <span class="text-muted">Not Started</span>
                    @endif
                </td>
                <td>
                    @if($application->distributorAgreement)
                        <span class="badge bg-success">Created</span>
                    @else
                        <span class="badge bg-warning">Pending</span>
                    @endif
                </td>
                <td>
                    @php
                        $physicalDoc = $application->physicalDocuments->first();
                        $allPhysicalDocsVerified = $physicalDoc && $physicalDoc->agreement_verified && $physicalDoc->security_cheque_verified && $physicalDoc->security_deposit_verified;
                    @endphp
                    @if($physicalDoc)
                        <span class="badge bg-{{ $allPhysicalDocsVerified ? 'success' : 'warning' }}">
                            {{ $allPhysicalDocsVerified ? 'All Received' : 'Pending' }}
                        </span>
                    @else
                        <span class="text-muted">Not Initiated</span>
                    @endif
                </td>
                <td>
                    <span class="badge bg-{{ $application->status == 'distributorship_created' ? 'success' : 'warning' }}">
                        {{ $application->status == 'distributorship_created' ? 'Created' : 'Pending' }}
                    </span>
                </td>
                <td>
                    <div class="btn-group">
                        @if($application->status === 'mis_processing')
                        <a href="{{ route('approvals.verify-documents', $application->id) }}" class="btn btn-sm btn-success" data-bs-toggle="tooltip" title="Start Verification"><i class="ri-checkbox-fill"></i></a>
                        @elseif($application->status === 'document_verified')
                        <a href="{{ route('approvals.upload-agreement', $application->id) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Upload Agreement"><i class="ri-upload-fill"></i></a>
                        @elseif($application->status === 'agreement_created' || $application->status === 'documents_received')
                        <a href="{{ route('approvals.track-documents', $application->id) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Update Physical Docs"><i class="ri-file-2-line"></i></a>
                        @elseif($application->status === 'distributorship_created')
                        <span class="badge bg-success p-2"><i class="ri-checkbox-circle-line"></i> Finalized</span>
                        @endif
                    </div>
                </td>
                <td>
                    <a href="{{ route('approvals.show', $application->id) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="View"><i class="ri-eye-line"></i></a>
                </td>
            </tr>
            <tr class="panel-content" style="display: none;">
                <td colspan="10">
                    <div class="card mb-2">
                        <div class="card-body">
                            <h6>Document Verification</h6>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Document Type</th>
                                        <th>Verified?</th>
                                        <th>Status</th>
                                        <th>Reason/Requirement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($application->documentVerifications as $doc)
                                    <tr>
                                        <td>{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</td>
                                        <td>{!! $doc->status == 'verified' ? '✅' : '❌' !!}</td>
                                        <td><span class="doc-status-{{ $doc->status }}">{{ ucfirst($doc->status) }}</span></td>
                                        <td>{{ $doc->remarks ?? '-' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No document verification details.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($application->distributorAgreement)
                    <div class="card mb-2">
                        <div class="card-body">
                            <h6>Agreement Creation</h6>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Step</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Uploaded By</th>
                                        <th>Draft Download</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Draft Agreement Created</td>
                                        <td>✅</td>
                                        <td>{{ $application->distributorAgreement->generated_at->format('d M Y') }}</td>
                                        <td>{{ $application->distributorAgreement->generatedBy->emp_name ?? 'N/A' }}</td>
                                        <td><a href="{{ Storage::url($application->distributorAgreement->agreement_path) }}" class="btn btn-sm btn-info" target="_blank">🔽 Download</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                    @if($application->physicalDocuments->isNotEmpty())
                    <div class="card mb-2">
                        <div class="card-body">
                            <h6>Physical Document Tracking</h6>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Document Type</th>
                                        <th>Received?</th>
                                        <th>Verified?</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $physicalDoc = $application->physicalDocuments->first(); @endphp
                                    <tr>
                                        <td>Agreement Copy</td>
                                        <td>{!! $physicalDoc->agreement_received ? '✅' : '❌' !!}</td>
                                        <td>{!! $physicalDoc->agreement_verified ? '✅' : '❌' !!}</td>
                                        <td>{{ $physicalDoc->agreement_verified_date ? $physicalDoc->agreement_verified_date->format('d M Y') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Security Cheques</td>
                                        <td>{!! $physicalDoc->security_cheque_received ? '✅' : '❌' !!}</td>
                                        <td>{!! $physicalDoc->security_cheque_verified ? '✅' : '❌' !!}</td>
                                        <td>{{ $physicalDoc->security_cheque_verified_date ? $physicalDoc->security_cheque_verified_date->format('d M Y') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Security Deposits</td>
                                        <td>{!! $physicalDoc->security_deposit_received ? '✅' : '❌' !!}</td>
                                        <td>{!! $physicalDoc->security_deposit_verified ? '✅' : '❌' !!}</td>
                                        <td>{{ $physicalDoc->security_deposit_verified_date ? $physicalDoc->security_deposit_verified_date->format('d M Y') : '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                    @if($application->status == 'distributorship_created')
                    <div class="card">
                        <div class="card-body">
                            <h6>Final Appointment</h6>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Step</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Distributor Created in FOCUS</td>
                                        <td>✅</td>
                                        <td>{{ $application->updated_at->format('d M Y') }}</td>
                                        <td>Code: {{ \App\Models\DistributorMaster::where('application_id', $application->id)->first()->distributor_code ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Auto Email Notification Sent</td>
                                        <td>✅</td>
                                        <td>{{ $application->updated_at->format('d M Y') }}</td>
                                        <td>Sent to TM, RBM, ZBM, GM, MIS, Business Head</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $misApplications->links() }}
</div>
@endif