
       @php
    // Get the latest log for each role
    $latestLogs = [];
    
    foreach ($application->approvalLogs as $log) {
        $role = $log->role;
        // Always use the latest log for each role
        if (!isset($latestLogs[$role]) || $log->created_at > $latestLogs[$role]->created_at) {
            $latestLogs[$role] = $log;
        }
    }

    // Map roles to approval levels - CORRECTED MAPPING
    $approvalLevels = [
        'Regional Business Manager' => $latestLogs['Regional Business Manager'] ?? null,
        'Zonal Business Manager' => $latestLogs['Zonal Business Manager'] ?? null,
        'General Manager' => $latestLogs['General Manager'] ?? null,
        'MIS' => $latestLogs['MIS'] ?? $latestLogs['Senior Executive'] ?? null,
    ];

    $currentStatus = $application->status;
    $currentApprovalLevel = $application->approval_level;

    // Build stages
    $stages = [];

    // Stage 1: Draft/Initiated
    $stages[] = [
        'label' => 'Draft/Initiated',
        'log' => null,
        'status' => in_array($currentStatus, ['draft', 'initiated']) ? 'pending' : 'approved',
        'date' => $application->created_at->format('d M Y'),
        'remarks' => 'Application submitted',
        'icon' => 'ri-draft-fill'
    ];

    // Stage 2: Regional Business Manager
    $rbmLog = $approvalLevels['Regional Business Manager'];
    $rbmStatus = $rbmLog ? $rbmLog->action : 'not-started';
    $rbmDate = $rbmLog ? $rbmLog->created_at->format('d M Y') : '-';
    $rbmRemarks = $rbmLog ? $rbmLog->remarks : '-';

    $stages[] = [
        'label' => 'RBM',
        'log' => $rbmLog,
        'status' => $rbmStatus,
        'date' => $rbmDate,
        'remarks' => $rbmRemarks,
        'icon' => 'ri-user-3-fill'
    ];

    // Stage 3: Zonal Business Manager (only if exists)
    $zbmLog = $approvalLevels['Zonal Business Manager'];
    if ($zbmLog) {
        $stages[] = [
            'label' => 'ZBM',
            'log' => $zbmLog,
            'status' => $zbmLog->action,
            'date' => $zbmLog->created_at->format('d M Y'),
            'remarks' => $zbmLog->remarks,
            'icon' => 'ri-user-4-fill'
        ];
    }

    // Stage 4: General Manager
    $gmLog = $approvalLevels['General Manager'];
    $gmStatus = 'not-started';
    $gmDate = '-';
    $gmRemarks = '-';

    if ($gmLog) {
        $gmStatus = $gmLog->action;
        $gmDate = $gmLog->created_at->format('d M Y');
        $gmRemarks = $gmLog->remarks;
    } elseif ($rbmStatus == 'approved' && !$zbmLog) {
        // If RBM approved and no ZBM, GM should be pending
        $gmStatus = 'pending';
    } elseif ($zbmLog && $zbmLog->action == 'approved') {
        // If ZBM approved, GM should be pending
        $gmStatus = 'pending';
    }

    $stages[] = [
        'label' => 'GM',
        'log' => $gmLog,
        'status' => $gmStatus,
        'date' => $gmDate,
        'remarks' => $gmRemarks,
        'icon' => 'ri-user-5-fill'
    ];

    // Stage 5: MIS
    $misLog = $approvalLevels['MIS'];
    $misStatus = 'not-started';
    $misDate = '-';
    $misRemarks = '-';

    if ($misLog) {
        $misStatus = $misLog->action;
        $misDate = $misLog->created_at->format('d M Y');
        $misRemarks = $misLog->remarks;
    } elseif ($gmStatus == 'approved' && in_array($currentStatus, ['mis_processing', 'documents_pending', 'documents_resubmitted', 'documents_verified', 'agreement_created', 'documents_received', 'physical_docs_verified'])) {
        $misStatus = 'pending';
        $misDate = $application->updated_at->format('d M Y');
    } elseif ($currentStatus == 'distributorship_created') {
        $misStatus = 'approved';
        $misDate = $application->updated_at->format('d M Y');
    } elseif ($currentStatus == 'mis_rejected') {
        $misStatus = 'rejected';
        $misDate = $application->updated_at->format('d M Y');
    }

    $stages[] = [
        'label' => 'MIS',
        'log' => $misLog,
        'status' => $misStatus,
        'date' => $misDate,
        'remarks' => $misRemarks,
        'icon' => 'ri-file-text-fill'
    ];

    // Final Stage
    $finalStatus = $currentStatus == 'distributorship_created' ? 'approved' : 'not-started';
    $finalDate = $currentStatus == 'distributorship_created' ? $application->updated_at->format('d M Y') : '-';

    $stages[] = [
        'label' => 'Final',
        'log' => null,
        'status' => $finalStatus,
        'date' => $finalDate,
        'remarks' => $currentStatus == 'distributorship_created' ? 'Distributorship created successfully' : '-',
        'icon' => 'ri-checkbox-circle-fill'
    ];

    // Handle special statuses
    if ($currentStatus == 'reverted') {
        foreach ($stages as &$stage) {
            if ($stage['label'] == $currentApprovalLevel) {
                $stage['status'] = 'reverted';
                $stage['remarks'] = 'Application reverted for corrections';
            }
        }
    }
@endphp

        <div class="timeline-container d-flex flex-row align-items-center justify-content-start p-3 overflow-x-auto overflow-y-hidden bg-light rounded">
            @foreach($stages as $index => $stage)
            <div class="timeline-item text-center px-2" style="min-width: 140px;">
                <i class="{{ $stage['icon'] }} mb-2 {{ $stage['status'] == 'approved' ? 'text-success' : ($stage['status'] == 'rejected' ? 'text-danger' : ($stage['status'] == 'pending' ? 'text-warning' : ($stage['status'] == 'hold' ? 'text-info' : ($stage['status'] == 'reverted' ? 'text-info' : 'text-muted')))) }}"
                    title="{{ $stage['label'] }} - {{ ucfirst($stage['status']) }}"
                    style="font-size: 1.2rem;"></i>
                <div class="timeline-stage-info">
                    <strong class="small">{{ $stage['label'] }}</strong><br>
                    <span class="badge bg-{{ $stage['status'] == 'approved' ? 'success' : ($stage['status'] == 'rejected' ? 'danger' : ($stage['status'] == 'pending' ? 'warning' : ($stage['status'] == 'hold' ? 'info' : ($stage['status'] == 'reverted' ? 'info' : 'secondary')))) }} small">
                        {{ ucfirst($stage['status']) }}
                    </span><br>
                    <small class="text-muted d-block"><strong>Date:</strong> {{ $stage['date'] }}</small>
                    <small class="text-muted d-block"><strong>Remarks:</strong> {{ \Illuminate\Support\Str::limit($stage['remarks'], 30) }}</small>
                </div>
            </div>
            @if(!$loop->last)
            <div class="arrow px-1 d-flex align-items-center">
                <span style="font-size: 1.2rem; color: #6c757d;">→</span>
            </div>
            @endif
            @endforeach
        </div>
 