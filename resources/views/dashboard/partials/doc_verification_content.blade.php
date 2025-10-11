<div class="table-responsive">
    <table class="table table-sm table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>Checkpoint Name</th>
                <th>Status</th>
                <th>Reason</th>
                <th>Submitted By</th>
                <th>Created At</th>
                <th>Updated At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($application->checkpoints as $checkpoint)
            <tr>
                <td>
                @php
                    $cleanName = preg_replace('/_\d+$/', '', $checkpoint->checkpoint_name);
                    // Extract the original index for multiples (e.g., _0 → (1), _1 → (2))
                    preg_match('/_(\d+)$/', $checkpoint->checkpoint_name, $matches);
                    $index = !empty($matches[1]) ? ' (' . ($matches[1] + 1) . ')' : '';
                    $displayName = ucfirst(str_replace('_', ' ', $cleanName)) . $index;
                @endphp
                {{ $displayName }}
                </td>
                <td>
                    <span class="badge bg-{{ $checkpoint->status == 'verified' ? 'success' : ($checkpoint->status == 'rejected' ? 'danger' : 'warning') }}">
                        {{ ucfirst($checkpoint->status) }}
                    </span>
                </td>
                <td>{{ $checkpoint->reason ?? 'N/A' }}</td>
                <td>{{ $checkpoint->submittedBy->emp_name ?? 'N/A' }}</td>
                <td>{{ $checkpoint->created_at->format('Y-m-d') }}</td>
                <td>{{ $checkpoint->updated_at->format('Y-m-d') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No checkpoints found for this application.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>