@php
    // DEBUG: Comprehensive debugging
    $applicationId = $application->id;
    
    echo "<!-- DEBUG: Application ID: {$applicationId} -->\n";
    
    // Method 1: Direct query
    $directLogs = \App\Models\ApprovalLog::where('application_id', $applicationId)->get();
    echo "<!-- DEBUG: Direct query count: " . $directLogs->count() . " -->\n";
    
    // Method 2: Relationship
    $relationshipLogs = $application->approvalLogs;
    echo "<!-- DEBUG: Relationship count: " . $relationshipLogs->count() . " -->\n";
    
    // Method 3: Check SQL
    $sql = $application->approvalLogs()->toSql();
    $bindings = $application->approvalLogs()->getBindings();
    echo "<!-- DEBUG: SQL: {$sql} -->\n";
    echo "<!-- DEBUG: Bindings: " . json_encode($bindings) . " -->\n";
    
    // Use direct logs for now
    $allLogs = $directLogs;
    
    if ($allLogs->isEmpty()) {
        echo "<!-- DEBUG: No logs found for application ID: {$applicationId} -->\n";
        echo "<!-- DEBUG: Available application IDs in logs: " . 
             \App\Models\ApprovalLog::distinct()->pluck('application_id')->implode(', ') . " -->\n";
    }

    // Group logs by role and get the latest log for each role
    $roleLogs = [];
    foreach ($allLogs as $log) {
        if (!isset($roleLogs[$log->role]) || $log->created_at > $roleLogs[$log->role]->created_at) {
            $roleLogs[$log->role] = $log;
        }
    }

    $currentStatus = $application->status;

    // Build stages
    $stages = [];

    // Stage 1: Draft/Initiated
    $stages[] = [
        'label' => 'Draft/Initiated',
        'status' => 'approved',
        'date' => $application->created_at->format('d M Y'),
        'remarks' => 'Application submitted',
        'icon' => 'ri-draft-fill'
    ];

    // Stage 2: Regional Business Manager
    $rbmLog = $roleLogs['Regional Business Manager'] ?? null;
    $stages[] = [
        'label' => 'RBM',
        'status' => $rbmLog ? $rbmLog->action : 'not-started',
        'date' => $rbmLog ? $rbmLog->created_at->format('d M Y') : '-',
        'remarks' => $rbmLog ? $rbmLog->remarks : '-',
        'icon' => 'ri-user-3-fill'
    ];

    // Stage 3: Zonal Business Manager (if exists)
    $zbmLog = $roleLogs['Zonal Business Manager'] ?? null;
    if ($zbmLog) {
        $stages[] = [
            'label' => 'ZBM',
            'status' => $zbmLog->action,
            'date' => $zbmLog->created_at->format('d M Y'),
            'remarks' => $zbmLog->remarks,
            'icon' => 'ri-user-4-fill'
        ];
    }

    // Stage 4: General Manager
    $gmLog = $roleLogs['General Manager'] ?? null;
    $stages[] = [
        'label' => 'GM',
        'status' => $gmLog ? $gmLog->action : 'not-started',
        'date' => $gmLog ? $gmLog->created_at->format('d M Y') : '-',
        'remarks' => $gmLog ? $gmLog->remarks : '-',
        'icon' => 'ri-user-5-fill'
    ];

    // Stage 5: MIS/Senior Executive
    $misLog = $roleLogs['MIS'] ?? $roleLogs['Senior Executive'] ?? null;
    $misStatus = 'not-started';
    $misDate = '-';
    $misRemarks = '-';

    if ($misLog) {
        $misStatus = $misLog->action;
        if (in_array($misStatus, ['documents_verified', 'distributor_confirmed', 'approved'])) {
            $misStatus = 'approved';
        }
        $misDate = $misLog->created_at->format('d M Y');
        $misRemarks = $misLog->remarks;
    } elseif ($currentStatus == 'distributorship_created') {
        $misStatus = 'approved';
        $misDate = $application->updated_at->format('d M Y');
        $misRemarks = 'Distributorship created';
    }

    $stages[] = [
        'label' => 'MIS',
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
        'status' => $finalStatus,
        'date' => $finalDate,
        'remarks' => $currentStatus == 'distributorship_created' ? 'Distributorship created successfully' : '-',
        'icon' => 'ri-checkbox-circle-fill'
    ];

    // Debug info
    $debugInfo = [
        'application_id' => $application->id,
        'logs_count' => $allLogs->count(),
        'roles_found' => array_keys($roleLogs),
        'status' => $currentStatus,
    ];
@endphp

<!-- Debug Info -->
<div class="alert alert-info small mb-2">
    <strong>Timeline Debug:</strong> 
    App ID: {{ $debugInfo['application_id'] }} | 
    Logs: {{ $debugInfo['logs_count'] }} | 
    Roles: {{ implode(', ', $debugInfo['roles_found']) }} |
    Status: {{ $debugInfo['status'] }}
</div>

<div class="timeline-container d-flex flex-row align-items-center justify-content-start p-3 overflow-x-auto overflow-y-hidden bg-light rounded">
    @foreach($stages as $index => $stage)
    <div class="timeline-item text-center px-2" style="min-width: 140px;">
        <i class="{{ $stage['icon'] }} mb-2 {{ $stage['status'] == 'approved' ? 'text-success' : ($stage['status'] == 'rejected' ? 'text-danger' : ($stage['status'] == 'pending' ? 'text-warning' : ($stage['status'] == 'hold' ? 'text-info' : ($stage['status'] == 'reverted' ? 'text-info' : 'text-muted')))) }}"
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