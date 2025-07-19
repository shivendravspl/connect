@if($myApplications->isEmpty())
<div class="alert alert-info no-data-message">You haven't submitted or acted on any applications yet.</div>
@else
<div class="table-responsive">
    <table class="table table-hover compact-table">
        <thead class="table-light">
            <tr>
                <th>Sr. No</th>
                <th>Date of Submission</th>
                <th>Distributor Name</th>
                <th>Status</th>
                <th>Approval Progression</th> {{-- Renamed for clarity --}}
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($myApplications as $index => $application)
            <tr class="status-card {{ $application->status_badge ?? '' }}">
                <td>{{ $myApplications->firstItem() + $index }}</td>
                <td>{{ $application->created_at->format('d M Y') }}</td>
                <td>{{ $application->entityDetails->legal_name ?? 'N/A' }}</td>
                <td>
                    <span class="badge bg-{{ $application->status_badge ?? 'secondary' }}">
                        {{ ucwords(str_replace('_', ' ', $application->status ?? 'N/A')) }}
                    </span>
                </td>
                <td>
                    <table class="table table-sm mb-0 small">
                        <thead>
                            <tr>
                                <th>Level</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $approvalLevels = [
                                    'Regional Business Manager' => null,
                                    'Zonal Business Manager' => null,
                                    'General Manager' => null,
                                    'mis_processing' => null,
                                    'distributorship_created' => null,
                                ];

                                foreach ($application->approvalLogs as $log) {
                                    if (array_key_exists($log->role, $approvalLevels) && is_null($approvalLevels[$log->role])) {
                                        $approvalLevels[$log->role] = $log;
                                    }
                                }
                            @endphp

                            <tr>
                                <td>RBM</td>
                                <td>{{ $approvalLevels['Regional Business Manager'] ? $approvalLevels['Regional Business Manager']->created_at->format('d M Y') : '-' }}</td>
                                <td>{{ $approvalLevels['Regional Business Manager'] ? ucfirst($approvalLevels['Regional Business Manager']->action) : '-' }}</td>
                                <td>{{ $approvalLevels['Regional Business Manager'] ? $approvalLevels['Regional Business Manager']->remarks : '-' }}</td>
                            </tr>
                            <tr>
                                <td>ZBM</td>
                                <td>{{ $approvalLevels['Zonal Business Manager'] ? $approvalLevels['Zonal Business Manager']->created_at->format('d M Y') : '-' }}</td>
                                <td>{{ $approvalLevels['Zonal Business Manager'] ? ucfirst($approvalLevels['Zonal Business Manager']->action) : '-' }}</td>
                                <td>{{ $approvalLevels['Zonal Business Manager'] ? $approvalLevels['Zonal Business Manager']->remarks : '-' }}</td>
                            </tr>
                            <tr>
                                <td>GM</td>
                                <td>{{ $approvalLevels['General Manager'] ? $approvalLevels['General Manager']->created_at->format('d M Y') : '-' }}</td>
                                <td>{{ $approvalLevels['General Manager'] ? ucfirst($approvalLevels['General Manager']->action) : '-' }}</td>
                                <td>{{ $approvalLevels['General Manager'] ? $approvalLevels['General Manager']->remarks : '-' }}</td>
                            </tr>
                            <tr>
                                <td>MIS Verification</td>
                                <td>{{ $application->status == 'mis_processing' || $application->status == 'document_verified' || $application->status == 'agreement_created' || $application->status == 'documents_received' || $application->status == 'distributorship_created' ? $application->updated_at->format('d M Y') : '-' }}</td>
                                <td>
                                    @if ($application->status === 'document_verified') Verified
                                    @elseif ($application->status === 'mis_processing') Pending
                                    @else -
                                    @endif
                                </td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>Final Appointment</td>
                                <td>{{ $application->status == 'distributorship_created' ? $application->updated_at->format('d M Y') : '-' }}</td>
                                <td>{{ $application->status == 'distributorship_created' ? 'Completed' : '-' }}</td>
                                <td>-</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td>
                    <div class="btn-group">
                        <a href="{{ route('approvals.show', $application->id) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="View"><i class="fas fa-eye"></i></a>
                        @if($application->status === 'reverted' && $application->created_by === Auth::user()->emp_id)
                        <a href="{{ route('applications.edit', $application->id) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a>
                        @endif
                        @if($application->status === 'distributorship_created')
                        <span class="badge bg-success p-2"><i class="fas fa-check-circle"></i> Finalized</span>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $myApplications->links() }}
</div>
@endif