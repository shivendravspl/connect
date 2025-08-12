<div class="table-responsive">
    <table class="table table-hover compact-table">
        <thead class="table-light">
            <tr>
                <th>Sr. No</th>
                <th>Date Submitted</th>
                <th>Distributor Name</th>
                <th>Initiated By</th>
                <th>Current Stage</th>
                <th>Status</th>
                <th>TAT (Days)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($masterReportApplications as $index => $application)
            <tr class="status-card {{ $application->status_badge }}">
                <td>{{ $masterReportApplications->firstItem() + $index }}</td>
                <td>{{ $application->created_at->format('d M Y') }}</td>
                <td>{{ $application->entityDetails->legal_name ?? 'N/A' }}</td>
                <td>{{ $application->createdBy->emp_name ?? 'N/A' }} ({{ $application->createdBy->emp_designation ?? 'N/A' }})</td>
                <td>{{ ucfirst($application->approval_level ?? 'N/A') }}</td>
                <td>
                    <span class="badge bg-{{ $application->status_badge }}">
                        {{ ucwords(str_replace('_', ' ', $application->status)) }}
                    </span>
                </td>
                <td>{{ $application->created_at->diffInDays($application->updated_at) }}</td>
                <td>
                    <a href="{{ route('approvals.show', $application) }}" class="btn btn-sm btn-primary"><i class="ri-eye-line"></i> View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $masterReportApplications->links() }}
</div>
<!-- TAT Metrics -->
<div class="card mt-3">
    <div class="card-body">
        <h5>TAT Metrics</h5>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Stage</th>
                    <th>Avg. TAT (Days)</th>
                    <th>Max TAT (Days)</th>
                    <th>Forms Exceeding SLA</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Submission → RBM</td>
                    <td>{{ $tatData['submission_to_rbm']['avg_tat'] }}</td>
                    <td>{{ $tatData['submission_to_rbm']['max_tat'] }}</td>
                    <td>{{ $tatData['submission_to_rbm']['exceeding_sla'] }}</td>
                </tr>
                <tr>
                    <td>RBM → ZBM</td>
                    <td>{{ $tatData['rbm_to_zbm']['avg_tat'] }}</td>
                    <td>{{ $tatData['rbm_to_zbm']['max_tat'] }}</td>
                    <td>{{ $tatData['rbm_to_zbm']['exceeding_sla'] }}</td>
                </tr>
                <tr>
                    <td>ZBM → GM</td>
                    <td>{{ $tatData['zbm_to_gm']['avg_tat'] }}</td>
                    <td>{{ $tatData['zbm_to_gm']['max_tat'] }}</td>
                    <td>{{ $tatData['zbm_to_gm']['exceeding_sla'] }}</td>
                </tr>
                <tr>
                    <td>GM → MIS</td>
                    <td>{{ $tatData['gm_to_mis']['avg_tat'] }}</td>
                    <td>{{ $tatData['gm_to_mis']['max_tat'] }}</td>
                    <td>{{ $tatData['gm_to_mis']['exceeding_sla'] }}</td>
                </tr>
                <tr>
                    <td>MIS → Final Creation</td>
                    <td>{{ $tatData['mis_to_final']['avg_tat'] }}</td>
                    <td>{{ $tatData['mis_to_final']['max_tat'] }}</td>
                    <td>{{ $tatData['mis_to_final']['exceeding_sla'] }}</td>
                </tr>
                <tr>
                    <td>Total</td>
                    <td>{{ $tatData['total']['avg_tat'] }}</td>
                    <td>{{ $tatData['total']['max_tat'] }}</td>
                    <td>{{ $tatData['total']['exceeding_sla'] }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>