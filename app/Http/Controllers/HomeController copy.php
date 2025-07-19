<?php

namespace App\Http\Controllers;

use App\Models\ApprovalLog;
use App\Models\Onboarding;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\helpers;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class HomeController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->middleware('auth');
        $this->helper = new helpers();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->dynamicData($request);
        }

        $user = Auth::user();
        $filters = [
            'territory' => $request->input('territory', 'All'),
            'region' => $request->input('region', 'All'),
            'zone' => $request->input('zone', 'All'),
            'bu' => $request->input('bu', 'All'),
            'initiator_role' => $request->input('initiator_role'),
            'approval_stage' => $request->input('approval_stage'),
            'status' => $request->input('status'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'date_range_type' => $request->input('date_range_type', 'submission'),
        ];

        $employee_details = $user->employee;
        $access_level = 'territory';
        if ($employee_details) {
            if ($user->hasAnyRole('Super Admin', 'Admin', 'SP Admin', 'Management')) {
                $access_level = 'all';
            } elseif ($employee_details->territory > 0) {
                $access_level = 'territory';
            } elseif ($employee_details->region > 0) {
                $access_level = 'region';
            } elseif ($employee_details->zone > 0) {
                $access_level = 'zone';
            } elseif ($employee_details->bu > 0) {
                $access_level = 'bu';
            }
        }

        $data = $this->getDashboardData($filters, $user, $access_level);
        extract($data);

        $pendingApplications = $pendingApplications ?? new LengthAwarePaginator([], 0, 10);
        $myApplications = $myApplications ?? new LengthAwarePaginator([], 0, 10);
        $misApplications = $misApplications ?? collect();
        $masterReportApplications = $masterReportApplications ?? new LengthAwarePaginator([], 0, 10);
        $tatData = $tatData ?? $this->getDefaultTatData();

        $bu_list = $bu_list ?? [];
        $zone_list = $zone_list ?? [];
        $region_list = $region_list ?? [];
        $territory_list = $territory_list ?? [];

        return view('dashboard.dashboard', compact(
            'pendingApplications', 'myApplications', 'misApplications', 'masterReportApplications',
            'actionSummary', 'data', 'filters', 'tatData', 'bu_list', 'zone_list', 'region_list', 'territory_list', 'access_level'
        ));
    }

    public function dynamicData(Request $request)
    {
        try {
            $user = Auth::user();
            $filters = [
                'territory' => $request->input('territory', 'All'),
                'region' => $request->input('region', 'All'),
                'zone' => $request->input('zone', 'All'),
                'bu' => $request->input('bu', 'All'),
                'initiator_role' => $request->input('initiator_role'),
                'approval_stage' => $request->input('approval_stage'),
                'status' => $request->input('status'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'date_range_type' => $request->input('date_range_type', 'submission'),
            ];

            $employee_details = $user->employee;
            $access_level = 'territory';
            if ($employee_details) {
                if ($user->hasAnyRole('Super Admin', 'Admin', 'SP Admin', 'Management')) {
                    $access_level = 'all';
                } elseif ($employee_details->territory > 0) {
                    $access_level = 'territory';
                } elseif ($employee_details->region > 0) {
                    $access_level = 'region';
                } elseif ($employee_details->zone > 0) {
                    $access_level = 'zone';
                } elseif ($employee_details->bu > 0) {
                    $access_level = 'bu';
                }
            }

            $data = $this->getDashboardData($filters, $user, $access_level);
            $data['counts'] = $data['counts'] ?? $this->getDefaultCounts();
            $data['tat'] = $data['tat'] ?? $this->getDefaultTatData();
            $data['kpi_trends'] = $data['kpi_trends'] ?? $this->getDefaultKpiTrends();

            $data['master_table_html'] = view('dashboard._master-table', [
                'masterReportApplications' => $data['masterReportApplications'] ?? new LengthAwarePaginator([], 0, 10),
                'tatData' => $data['tat']
            ])->render();

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Error in dynamicData: ' . $e->getMessage() . ' on line ' . $e->getLine());
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    protected function getDashboardData(array $filters, $user, $access_level)
    {
        // Base query components
        $selectFields = [
            'o.id',
            'o.territory',
            'o.business_unit',
            'o.created_by',
            'o.current_approver_id',
            'o.final_approver_id',
            'o.approval_level',
            'o.status',
            'o.created_at',
            'o.updated_at',
            't.zone_id',
            'r.region_name',
            'e.emp_name as created_by_name',
            'ca.emp_name as current_approver_name',
            'ed.id as entity_details_id',
            'ed.establishment_name'
        ];

        $baseSql = 'SELECT ' . implode(', ', $selectFields) . '
                    FROM onboardings o
                    LEFT JOIN core_territory t ON o.territory = t.id
                    LEFT JOIN core_region r ON t.region = r.id
                    LEFT JOIN core_employee e ON o.created_by = e.id
                    LEFT JOIN core_employee ca ON o.current_approver_id = ca.id
                    LEFT JOIN entity_details ed ON o.id = ed.application_id';

        $whereClauses = [];
        $bindings = [];

        // Apply access level restrictions
        $employee_details = $user->employee;
        if ($employee_details) {
            if ($access_level === 'territory' && $employee_details->territory > 0) {
                $whereClauses[] = 'o.territory = ?';
                $bindings[] = $employee_details->territory;
            } elseif ($access_level === 'region' && $employee_details->region > 0) {
                $whereClauses[] = 't.region = ?';
                $bindings[] = $employee_details->region;
            } elseif ($access_level === 'zone' && $employee_details->zone > 0) {
                $whereClauses[] = 't.zone_id = ?';
                $bindings[] = $employee_details->zone;
            } elseif ($access_level === 'bu' && $employee_details->bu > 0) {
                $whereClauses[] = 'o.business_unit = ?';
                $bindings[] = $employee_details->bu;
            }
        }

        // Apply filters
        if ($filters['territory'] && $filters['territory'] != 'All') {
            $whereClauses[] = 'o.territory = ?';
            $bindings[] = $filters['territory'];
        }
        if ($filters['region'] && $filters['region'] != 'All') {
            $whereClauses[] = 't.region = ?';
            $bindings[] = $filters['region'];
        }
        if ($filters['zone'] && $filters['zone'] != 'All') {
            $whereClauses[] = 't.zone_id = ?';
            $bindings[] = $filters['zone'];
        }
        if ($filters['bu'] && $filters['bu'] != 'All') {
            $whereClauses[] = 'o.business_unit = ?';
            $bindings[] = $filters['bu'];
        }
        if ($filters['initiator_role']) {
            $whereClauses[] = 'e.emp_designation = ?';
            $bindings[] = $filters['initiator_role'];
        }
        if ($filters['approval_stage']) {
            $stageMap = [
                'initiated' => 'initiated',
                'Regional Business Manager' => 'Regional Business Manager',
                'Zonal Business Manager' => 'Zonal Business Manager',
                'General Manager' => 'General Manager',
                'mis' => 'mis_processing',
                'completed' => 'distributorship_created'
            ];
            if (in_array($filters['approval_stage'], ['Regional Business Manager', 'Zonal Business Manager', 'General Manager'])) {
                $whereClauses[] = 'o.approval_level IN (?, ?, ?)';
                $bindings = array_merge($bindings, ['Regional Business Manager', 'Zonal Business Manager', 'General Manager']);
            } elseif (isset($stageMap[$filters['approval_stage']])) {
                $whereClauses[] = 'o.approval_level = ?';
                $bindings[] = $stageMap[$filters['approval_stage']];
            }
        }
        if ($filters['status']) {
            $whereClauses[] = 'o.status = ?';
            $bindings[] = $filters['status'];
        }
        if ($filters['date_from'] && $filters['date_to']) {
            $dateField = match ($filters['date_range_type']) {
                'submission' => 'o.created_at',
                'approval' => 'al.created_at',
                'appointment' => 'o.updated_at',
                default => 'o.created_at',
            };
            if ($filters['date_range_type'] == 'approval') {
                $whereClauses[] = 'EXISTS (
                    SELECT 1 FROM approval_logs al
                    WHERE al.application_id = o.id
                    AND al.action = ?
                    AND al.created_at BETWEEN ? AND ?
                )';
                $bindings = array_merge($bindings, ['approved', $filters['date_from'], $filters['date_to']]);
            } else {
                $whereClauses[] = "$dateField BETWEEN ? AND ?";
                $bindings = array_merge($bindings, [$filters['date_from'], $filters['date_to']]);
            }
        }

        $whereSql = !empty($whereClauses) ? ' WHERE ' . implode(' AND ', $whereClauses) : '';

        // Fetch helper lists
        $bu_list = $this->helper->getAssociatedBusinessUnitList($user->emp_id);
        $zone_list = $this->helper->getAssociatedZoneList($user->emp_id);
        $region_list = $this->helper->getAssociatedRegionList($user->emp_id);
        $territory_list = $this->helper->getAssociatedTerritoryList($user->emp_id);

        // Pending Applications
        $pendingWhere = array_merge($whereClauses, [
            'o.current_approver_id = ?',
            'o.status IN (?, ?, ?, ?)',
            'o.approval_level = ?'
        ]);
        $pendingBindings = array_merge($bindings, [
            $user->emp_id,
            'initiated', 'under_review', 'on_hold', 'reverted',
            $employee_details->emp_designation ?? ''
        ]);
        $pendingSql = $baseSql . (!empty($pendingWhere) ? ' WHERE ' . implode(' AND ', $pendingWhere) : '');
        $pendingCount = DB::selectOne('SELECT COUNT(*) as total FROM (' . $pendingSql . ') as sub', $pendingBindings)->total;
        $pendingApplications = $this->paginateRawQuery($pendingSql, $pendingBindings, 10, $request->input('page_pending', 1));

        // My Applications
        $myWhere = array_merge($whereClauses, [
            '(o.created_by = ? OR o.final_approver_id = ? OR EXISTS (
                SELECT 1 FROM approval_logs al WHERE al.application_id = o.id AND al.user_id = ?
            ))'
        ]);
        $myBindings = array_merge($bindings, [$user->emp_id, $user->emp_id, $user->emp_id]);
        $mySql = $baseSql . (!empty($myWhere) ? ' WHERE ' . implode(' AND ', $myWhere) : '');
        $myCount = DB::selectOne('SELECT COUNT(*) as total FROM (' . $mySql . ') as sub', $myBindings)->total;
        $myApplications = $this->paginateRawQuery($mySql, $myBindings, 10, $request->input('page_my', 1));

        // MIS Applications
        $misApplications = collect();
        if ($employee_details && $employee_details->isMisTeam()) {
            $misWhere = array_merge($whereClauses, [
                'o.status IN (?, ?, ?, ?)'
            ]);
            $misBindings = array_merge($bindings, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received']);
            $misSql = $baseSql . (!empty($misWhere) ? ' WHERE ' . implode(' AND ', $misWhere) : '');
            $misCount = DB::selectOne('SELECT COUNT(*) as total FROM (' . $misSql . ') as sub', $misBindings)->total;
            $misApplications = $this->paginateRawQuery($misSql, $misBindings, 10, $request->input('page_mis', 1));
        }

        // Master Report Applications
        $masterSql = $baseSql . $whereSql;
        $masterCount = DB::selectOne('SELECT COUNT(*) as total FROM (' . $masterSql . ') as sub', $bindings)->total;
        $masterReportApplications = $this->paginateRawQuery($masterSql, $bindings, 10, $request->input('page_master', 1));

        // Action Summary
        $actionSummarySql = '
            SELECT al.user_id, e.emp_name, al.action, COUNT(*) as action_count
            FROM approval_logs al
            JOIN employees e ON al.user_id = e.id
            WHERE al.application_id IN (
                SELECT o.id FROM (' . $baseSql . ') as o ' . $whereSql . '
            )
            GROUP BY al.user_id, e.emp_name, al.action
        ';
        $actionSummaryRaw = DB::select($actionSummarySql, $bindings);
        $actionSummary = [];
        $userActions = [];
        foreach ($actionSummaryRaw as $row) {
            if (!isset($userActions[$row->user_id])) {
                $userActions[$row->user_id] = ['user_name' => $row->emp_name, 'actions' => []];
            }
            $userActions[$row->user_id]['actions'][$row->action] = $row->action_count;
        }
        $actionSummary = array_values($userActions);

        // Counts
        $counts = [
            'pending' => $pendingCount,
            'my' => $myCount,
            'mis' => $employee_details && $employee_details->isMisTeam() ? $misCount : 0,
            'total' => $masterCount,
            'approved' => DB::selectOne('SELECT COUNT(*) as total FROM (' . $baseSql . $whereSql . ' AND o.status IN (?, ?)) as sub', array_merge($bindings, ['approved', 'distributorship_created']))->total,
            'in_process' => DB::selectOne('SELECT COUNT(*) as total FROM (' . $baseSql . $whereSql . ' AND o.status IN (?, ?, ?, ?, ?, ?)) as sub', array_merge($bindings, ['initiated', 'under_review', 'mis_processing', 'document_verified', 'agreement_created', 'documents_received']))->total,
            'rejected' => DB::selectOne('SELECT COUNT(*) as total FROM (' . $baseSql . $whereSql . ' AND o.status IN (?, ?)) as sub', array_merge($bindings, ['rejected', 'mis_rejected']))->total,
            'reverted' => DB::selectOne('SELECT COUNT(*) as total FROM (' . $baseSql . $whereSql . ' AND o.status = ?) as sub', array_merge($bindings, ['reverted']))->total,
            'pending_rbm' => DB::selectOne('SELECT COUNT(*) as total FROM (' . $baseSql . $whereSql . ' AND o.approval_level = ? AND o.status IN (?, ?, ?, ?)) as sub', array_merge($bindings, ['Regional Business Manager', 'initiated', 'under_review', 'on_hold', 'reverted']))->total,
            'pending_zbm' => DB::selectOne('SELECT COUNT(*) as total FROM (' . $baseSql . $whereSql . ' AND o.approval_level = ? AND o.status IN (?, ?, ?, ?)) as sub', array_merge($bindings, ['Zonal Business Manager', 'initiated', 'under_review', 'on_hold', 'reverted']))->total,
            'pending_gm' => DB::selectOne('SELECT COUNT(*) as total FROM (' . $baseSql . $whereSql . ' AND o.approval_level = ? AND o.status IN (?, ?, ?, ?)) as sub', array_merge($bindings, ['General Manager', 'initiated', 'under_review', 'on_hold', 'reverted']))->total,
            'forwarded_to_mis' => DB::selectOne('SELECT COUNT(*) as total FROM (' . $baseSql . $whereSql . ' AND o.status IN (?, ?, ?, ?, ?)) as sub', array_merge($bindings, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'distributorship_created']))->total,
            'distributors_created' => DB::selectOne('SELECT COUNT(*) as total FROM (' . $baseSql . $whereSql . ' AND o.status = ?) as sub', array_merge($bindings, ['distributorship_created']))->total,
            'on_hold_by_you' => DB::selectOne('SELECT COUNT(*) as total FROM (' . $baseSql . $whereSql . ' AND o.status = ? AND o.current_approver_id = ?) as sub', array_merge($bindings, ['on_hold', $user->emp_id]))->total,
            'approved_by_you' => DB::selectOne('SELECT COUNT(*) as total FROM approval_logs al WHERE al.user_id = ? AND al.action = ? AND al.application_id IN (' . $baseSql . $whereSql . ')', array_merge([$user->emp_id, 'approved'], $bindings))->total,
            'rejected_by_you' => DB::selectOne('SELECT COUNT(*) as total FROM approval_logs al WHERE al.user_id = ? AND al.action = ? AND al.application_id IN (' . $baseSql . $whereSql . ')', array_merge([$user->emp_id, 'rejected'], $bindings))->total,
            'reverted_by_you' => DB::selectOne('SELECT COUNT(*) as total FROM approval_logs al WHERE al.user_id = ? AND al.action = ? AND al.application_id IN (' . $baseSql . $whereSql . ')', array_merge([$user->emp_id, 'reverted'], $bindings))->total,
            'doc_verification_pending' => DB::selectOne('SELECT COUNT(*) as total FROM (' . $baseSql . $whereSql . ' AND o.status = ?) as sub', array_merge($bindings, ['mis_processing']))->total,
            'agreements_created' => DB::selectOne('SELECT COUNT(*) as total FROM (' . $baseSql . $whereSql . ' AND o.status = ?) as sub', array_merge($bindings, ['agreement_created']))->total,
            'physical_docs_pending' => DB::selectOne('
                SELECT COUNT(*) as total
                FROM (' . $baseSql . $whereSql . ' AND o.status = ?) as sub
                WHERE NOT EXISTS (
                    SELECT 1 FROM physical_documents pd
                    WHERE pd.application_id = sub.id
                    AND pd.agreement_verified = 1
                    AND pd.security_cheque_verified = 1
                    AND pd.security_deposit_verified = 1
                )', array_merge($bindings, ['documents_received']))->total,
        ];

        // KPI Trends (Last Month)
        $lastMonthWhere = $whereClauses;
        $lastMonthBindings = $bindings;
        $lastMonthFilters = $filters;
        $lastMonthFilters['date_from'] = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $lastMonthFilters['date_to'] = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        if ($lastMonthFilters['date_from'] && $lastMonthFilters['date_to']) {
            $dateField = match ($filters['date_range_type']) {
                'submission' => 'o.created_at',
                'approval' => 'al.created_at',
                'appointment' => 'o.updated_at',
                default => 'o.created_at',
            };
            if ($filters['date_range_type'] == 'approval') {
                $lastMonthWhere[] = 'EXISTS (
                    SELECT 1 FROM approval_logs al
                    WHERE al.application_id = o.id
                    AND al.action = ?
                    AND al.created_at BETWEEN ? AND ?
                )';
                $lastMonthBindings = array_merge($lastMonthBindings, ['approved', $lastMonthFilters['date_from'], $lastMonthFilters['date_to']]);
            } else {
                $lastMonthWhere[] = "$dateField BETWEEN ? AND ?";
                $lastMonthBindings = array_merge($lastMonthBindings, [$lastMonthFilters['date_from'], $lastMonthFilters['date_to']]);
            }
        }

        $lastMonthWhereSql = !empty($lastMonthWhere) ? ' WHERE ' . implode(' AND ', $lastMonthWhere) : '';
        $lastMonthSql = $baseSql . $lastMonthWhereSql;

        $lastMonthCounts = [
            'total' => DB::selectOne('SELECT COUNT(*) as total FROM (' . $lastMonthSql . ') as sub', $lastMonthBindings)->total,
            'distributors_created' => DB::selectOne('SELECT COUNT(*) as total FROM (' . $lastMonthSql . ' AND o.status = ?) as sub', array_merge($lastMonthBindings, ['distributorship_created']))->total,
            'reverted' => DB::selectOne('SELECT COUNT(*) as total FROM (' . $lastMonthSql . ' AND o.status = ?) as sub', array_merge($lastMonthBindings, ['reverted']))->total,
            'rejected' => DB::selectOne('SELECT COUNT(*) as total FROM (' . $lastMonthSql . ' AND o.status IN (?, ?)) as sub', array_merge($lastMonthBindings, ['rejected', 'mis_rejected']))->total,
            'forwarded_to_mis' => DB::selectOne('SELECT COUNT(*) as total FROM (' . $lastMonthSql . ' AND o.status IN (?, ?, ?, ?, ?)) as sub', array_merge($lastMonthBindings, ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'distributorship_created']))->total,
        ];

        $kpi_trends = [
            'total_submitted' => $lastMonthCounts['total'] > 0 ? round(($counts['total'] - $lastMonthCounts['total']) / $lastMonthCounts['total'] * 100, 1) : 0,
            'appointments_completed' => $lastMonthCounts['distributors_created'] > 0 ? round(($counts['distributors_created'] - $lastMonthCounts['distributors_created']) / $lastMonthCounts['distributors_created'] * 100, 1) : 0,
            'reverted' => $lastMonthCounts['reverted'] > 0 ? round(($counts['reverted'] - $lastMonthCounts['reverted']) / $lastMonthCounts['reverted'] * 100, 1) : 0,
            'rejected' => $lastMonthCounts['rejected'] > 0 ? round(($counts['rejected'] - $lastMonthCounts['rejected']) / $lastMonthCounts['rejected'] * 100, 1) : 0,
            'forwarded_to_mis' => $lastMonthCounts['forwarded_to_mis'] > 0 ? round(($counts['forwarded_to_mis'] - $lastMonthCounts['forwarded_to_mis']) / $lastMonthCounts['forwarded_to_mis'] * 100, 1) : 0,
            'avg_tat' => $this->calculateTAT($baseSql, $bindings)['total']['avg_tat'] > 0 ? round($this->calculateTAT($baseSql, $bindings)['total']['avg_tat'] - $this->calculateTAT($lastMonthSql, $lastMonthBindings)['total']['avg_tat'], 1) : 0,
        ];

        // TAT Calculations
        $tatData = $this->calculateTAT($baseSql, $bindings);

        return compact(
            'pendingApplications', 'myApplications', 'misApplications', 'masterReportApplications',
            'actionSummary', 'counts', 'tatData', 'kpi_trends', 'bu_list', 'zone_list', 'region_list', 'territory_list', 'access_level', 'filters'
        );
    }

    protected function calculateTAT($baseSql, $bindings)
    {
        $stages = [
            'submission_to_rbm' => ['from_role' => 'initiated', 'to_role' => 'Regional Business Manager', 'sla' => 3],
            'rbm_to_zbm' => ['from_role' => 'Regional Business Manager', 'to_role' => 'Zonal Business Manager', 'sla' => 3],
            'zbm_to_gm' => ['from_role' => 'Zonal Business Manager', 'to_role' => 'General Manager', 'sla' => 3],
            'gm_to_mis' => ['from_role' => 'General Manager', 'to_role' => 'mis_processing', 'sla' => 2],
            'mis_to_final' => ['from_role' => 'mis_processing', 'to_role' => 'distributorship_created', 'sla' => 5],
        ];

        $tatResults = [];
        foreach ($stages as $stageKey => $stageInfo) {
            $stageSql = '
                SELECT o.id, MIN(from_log.created_at) as from_date, MIN(to_log.created_at) as to_date
                FROM (' . $baseSql . ') as o
                JOIN approval_logs from_log ON o.id = from_log.application_id AND from_log.role = ?
                JOIN approval_logs to_log ON o.id = to_log.application_id AND to_log.role = ? AND to_log.action = ?
                GROUP BY o.id
            ';
            $stageBindings = array_merge($bindings, [$stageInfo['from_role'], $stageInfo['to_role'], 'approved']);
            $applicationsInStage = DB::select($stageSql, $stageBindings);

            $durations = [];
            foreach ($applicationsInStage as $app) {
                if ($app->from_date && $app->to_date) {
                    $fromDate = Carbon::parse($app->from_date);
                    $toDate = Carbon::parse($app->to_date);
                    $durations[] = $fromDate->diffInDays($toDate);
                }
            }

            $durationsCollection = collect($durations);
            $tatResults[$stageKey] = [
                'avg_tat' => $durationsCollection->isNotEmpty() ? round($durationsCollection->avg(), 1) : 0,
                'max_tat' => $durationsCollection->isNotEmpty() ? $durationsCollection->max() : 0,
                'exceeding_sla' => $durationsCollection->filter(fn($days) => $days > $stageInfo['sla'])->count(),
            ];
        }

        // Total TAT
        $totalSql = 'SELECT o.id, o.created_at, o.updated_at
                     FROM (' . $baseSql . ') as o
                     WHERE o.status = ?';
        $totalApplications = DB::select($totalSql, array_merge($bindings, ['distributorship_created']));

        $totalDurations = [];
        foreach ($totalApplications as $app) {
            if ($app->created_at && $app->updated_at) {
                $submissionDate = Carbon::parse($app->created_at);
                $finalAppointmentDate = Carbon::parse($app->updated_at);
                $totalDurations[] = $submissionDate->diffInDays($finalAppointmentDate);
            }
        }

        $totalDurationsCollection = collect($totalDurations);
        $tatResults['total'] = [
            'avg_tat' => $totalDurationsCollection->isNotEmpty() ? round($totalDurationsCollection->avg(), 1) : 0,
            'max_tat' => $totalDurationsCollection->isNotEmpty() ? $totalDurationsCollection->max() : 0,
            'exceeding_sla' => $totalDurationsCollection->filter(fn($days) => $days > 12)->count(),
        ];

        return $tatResults;
    }

    protected function paginateRawQuery($sql, $bindings, $perPage, $currentPage)
    {
        $total = DB::selectOne('SELECT COUNT(*) as total FROM (' . $sql . ') as sub', $bindings)->total;
        $offset = ($currentPage - 1) * $perPage;
        $sql .= ' LIMIT ? OFFSET ?';
        $bindings = array_merge($bindings, [$perPage, $offset]);
        $results = DB::select($sql, $bindings);
        return new LengthAwarePaginator($results, $total, $perPage, $currentPage, [
            'path' => request()->url(),
            'query' => request()->query()
        ]);
    }

    protected function getDefaultCounts()
    {
        return [
            'pending' => 0, 'my' => 0, 'mis' => 0, 'total' => 0,
            'approved' => 0, 'in_process' => 0, 'rejected' => 0, 'reverted' => 0,
            'pending_rbm' => 0, 'pending_zbm' => 0, 'pending_gm' => 0,
            'forwarded_to_mis' => 0, 'distributors_created' => 0,
            'on_hold_by_you' => 0, 'approved_by_you' => 0, 'rejected_by_you' => 0, 'reverted_by_you' => 0,
            'doc_verification_pending' => 0, 'agreements_created' => 0, 'physical_docs_pending' => 0,
        ];
    }

    protected function getDefaultTatData()
    {
        return [
            'submission_to_rbm' => ['avg_tat' => 0, 'max_tat' => 0, 'exceeding_sla' => 0],
            'rbm_to_zbm' => ['avg_tat' => 0, 'max_tat' => 0, 'exceeding_sla' => 0],
            'zbm_to_gm' => ['avg_tat' => 0, 'max_tat' => 0, 'exceeding_sla' => 0],
            'gm_to_mis' => ['avg_tat' => 0, 'max_tat' => 0, 'exceeding_sla' => 0],
            'mis_to_final' => ['avg_tat' => 0, 'max_tat' => 0, 'exceeding_sla' => 0],
            'total' => ['avg_tat' => 0, 'max_tat' => 0, 'exceeding_sla' => 0],
        ];
    }

    protected function getDefaultKpiTrends()
    {
        return [
            'total_submitted' => 0,
            'appointments_completed' => 0,
            'reverted' => 0,
            'rejected' => 0,
            'forwarded_to_mis' => 0,
            'avg_tat' => 0,
        ];
    }
}
