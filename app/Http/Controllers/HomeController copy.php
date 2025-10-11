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
use Illuminate\Http\JsonResponse;
use App\Models\Notification;


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

        $data = $this->getDashboardData($filters, $user, $access_level, $request);
        extract($data);

        $pendingApplications = $pendingApplications ?? new LengthAwarePaginator([], 0, 10);
        $myApplications = $myApplications ?? new LengthAwarePaginator([], 0, 10);
        $misApplications = $misApplications ?? new LengthAwarePaginator([], 0, 10);;
        $masterReportApplications = $masterReportApplications ?? new LengthAwarePaginator([], 0, 10);
        $tatData = $tatData ?? $this->getDefaultTatData();
        $bu_list = $bu_list ?? [];
        $zone_list = $zone_list ?? [];
        $region_list = $region_list ?? [];
        $territory_list = $territory_list ?? [];
        $actionSummary = $actionSummary ?? [];

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
                if ($user->hasAnyRole(['Super Admin', 'Admin', 'SP Admin', 'Management'])) {
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

            $data = $this->getDashboardData($filters, $user, $access_level, $request);
            $data['counts'] = $data['counts'] ?? $this->getDefaultCounts();
            $data['tat'] = $data['tat'] ?? $this->getDefaultTatData();
            $data['kpi_trends'] = $data['kpi_trends'] ?? $this->getDefaultKpiTrends();
            $data['actionSummary'] = $data['actionSummary'] ?? [];

            // Render table HTML for dynamic updates
            $data['master_table_html'] = view('dashboard._master-table', [
                'masterReportApplications' => $data['masterReportApplications'] ?? new LengthAwarePaginator([], 0, 10),
                'tatData' => $data['tat']
            ])->render();

            $data['pending_table_html'] = view('dashboard._approver-table', [
                'pendingApplications' => $data['pendingApplications'] ?? new LengthAwarePaginator([], 0, 10)
            ])->render();

            $data['my_table_html'] = view('dashboard._sales-table', [
                'myApplications' => $data['myApplications'] ?? new LengthAwarePaginator([], 0, 10)
            ])->render();

            $data['mis_table_html'] = view('dashboard._mis-table', [
                'misApplications' => $data['misApplications'] ?? new LengthAwarePaginator([], 0, 10)
            ])->render();

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Error in dynamicData: ' . $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile());
            return response()->json(['error' => 'Server error: Unable to fetch dashboard data. Please try again later.'], 500);
        }
    }

    protected function getDashboardData(array $filters, $user, $access_level, Request $request)
    {
        $bu_list = $this->helper->getAssociatedBusinessUnitList($user->emp_id);
        $zone_list = $this->helper->getAssociatedZoneList($user->emp_id);
        $region_list = $this->helper->getAssociatedRegionList($user->emp_id);
        $territory_list = $this->helper->getAssociatedTerritoryList($user->emp_id);

        $masterDashboardBaseQuery = Onboarding::query()
            ->with(['entityDetails', 'createdBy', 'currentApprover', 'territoryDetail', 'regionDetail', 'zoneDetail', 'approvalLogs'])
            ->select([
                'onboardings.id',
                'onboardings.territory',
                'onboardings.business_unit',
                'onboardings.created_by',
                'onboardings.current_approver_id',
                'onboardings.final_approver_id',
                'onboardings.approval_level',
                'onboardings.status',
                'onboardings.created_at',
                'onboardings.updated_at',
                'core_region.id as region_id',
                'core_zone.id as zone_id',
                'core_region.region_name',
                'core_territory.territory_name',
                'core_employee.emp_name as created_by_name',
                'core_employee.emp_designation as created_by_designation',
                'ca.emp_name as current_approver_name',
                'entity_details.id as entity_details_id',
                'entity_details.establishment_name',
            ])
            ->leftJoin('core_territory', 'onboardings.territory', '=', 'core_territory.id')
            ->leftJoin('core_region_territory_mapping as rtm', 'core_territory.id', '=', 'rtm.territory_id')
            ->leftJoin('core_region', 'rtm.region_id', '=', 'core_region.id')
            ->leftJoin('core_zone_region_mapping as zrm', 'core_region.id', '=', 'zrm.region_id')
            ->leftJoin('core_zone', 'zrm.zone_id', '=', 'core_zone.id')
            ->leftJoin('core_employee', 'onboardings.created_by', '=', 'core_employee.employee_id')
            ->leftJoin('core_employee as ca', 'onboardings.current_approver_id', '=', 'ca.employee_id')
            ->leftJoin('entity_details', 'onboardings.id', '=', 'entity_details.application_id');

        $employee_details = $user->employee;
        if ($employee_details) {
            if ($access_level === 'territory' && $employee_details->territory > 0) {
                $masterDashboardBaseQuery->where('onboardings.territory', $employee_details->territory);
            } elseif ($access_level === 'region' && $employee_details->region > 0) {
                $masterDashboardBaseQuery->where('rtm.region_id', $employee_details->region);
            } elseif ($access_level === 'zone' && $employee_details->zone > 0) {
                $masterDashboardBaseQuery->where('zrm.zone_id', $employee_details->zone);
            } elseif ($access_level === 'bu' && $employee_details->bu > 0) {
                $masterDashboardBaseQuery->where('onboardings.business_unit', $employee_details->bu);
            }
        }

        if ($filters['territory'] && $filters['territory'] != 'All') {
            $masterDashboardBaseQuery->where('onboardings.territory', $filters['territory']);
        }
        if ($filters['region'] && $filters['region'] != 'All') {
            $masterDashboardBaseQuery->where('rtm.region_id', $filters['region']);
        }
        if ($filters['zone'] && $filters['zone'] != 'All') {
            $masterDashboardBaseQuery->where('zrm.zone_id', $filters['zone']);
        }
        if ($filters['bu'] && $filters['bu'] != 'All') {
            $masterDashboardBaseQuery->where('onboardings.business_unit', $filters['bu']);
        }
        if ($filters['initiator_role']) {
            $masterDashboardBaseQuery->where('core_employee.emp_designation', $filters['initiator_role']);
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
                $masterDashboardBaseQuery->whereIn('onboardings.approval_level', ['Regional Business Manager', 'Zonal Business Manager', 'General Manager']);
            } elseif (isset($stageMap[$filters['approval_stage']])) {
                $masterDashboardBaseQuery->where('onboardings.approval_level', $stageMap[$filters['approval_stage']]);
            }
        }
        if ($filters['status']) {
            $masterDashboardBaseQuery->where('onboardings.status', $filters['status']);
        }
        if ($filters['date_from'] && $filters['date_to']) {
            $dateField = match ($filters['date_range_type']) {
                'submission' => 'onboardings.created_at',
                'approval' => 'approval_logs.created_at',
                'appointment' => 'onboardings.updated_at',
                default => 'onboardings.created_at',
            };
            if ($filters['date_range_type'] == 'approval') {
                $masterDashboardBaseQuery->whereExists(function ($q) use ($filters) {
                    $q->select(DB::raw(1))
                      ->from('approval_logs')
                      ->whereColumn('approval_logs.application_id', 'onboardings.id')
                      ->where('approval_logs.action', 'approved')
                      ->whereBetween('approval_logs.created_at', [$filters['date_from'], $filters['date_to']]);
                });
            } else {
                $masterDashboardBaseQuery->whereBetween($dateField, [$filters['date_from'], $filters['date_to']]);
            }
        }
        
        $pendingApplicationsQuery = Onboarding::query()
            ->with(['entityDetails', 'createdBy', 'currentApprover', 'territoryDetail', 'regionDetail', 'zoneDetail'])
            ->where('onboardings.current_approver_id', $user->emp_id)
            ->whereIn('onboardings.status', ['initiated', 'under_review', 'on_hold', 'reverted']);
            //->where('onboardings.approval_level', $employee_details->emp_designation ?? '');
        $pendingApplications = $pendingApplicationsQuery->paginate(10, ['*'], 'page_pending');
          //dd($pendingApplicationsQuery);
        $myApplicationsQuery = Onboarding::query()
            ->with(['entityDetails', 'createdBy', 'currentApprover', 'territoryDetail', 'regionDetail', 'zoneDetail', 'approvalLogs'])
            ->where(function ($q) use ($user) {
                $q->where('onboardings.created_by', $user->emp_id)
                  ->orWhere('onboardings.final_approver_id', $user->emp_id)
                  ->orWhereExists(function ($subQ) use ($user) {
                      $subQ->select(DB::raw(1))
                           ->from('approval_logs')
                           ->whereColumn('approval_logs.application_id', 'onboardings.id')
                           ->where('approval_logs.user_id', $user->emp_id);
                  });
            });
        $myApplications = $myApplicationsQuery->paginate(10, ['*'], 'page_my');

        $misApplications = new LengthAwarePaginator([], 0, 10);
        if ($employee_details && $employee_details->isMisTeam()) {
            $misApplicationsQuery = Onboarding::query()
                ->with(['entityDetails', 'createdBy', 'currentApprover', 'territoryDetail', 'regionDetail', 'zoneDetail','approvalLogs'])
                ->whereIn('onboardings.status', ['draft','initiated','under_review','mis_processing', 'approved','document_verified', 'agreement_created', 'documents_received','distributorship_created','mis_rejected','physical_docs_verified']);
            $misApplications = $misApplicationsQuery->paginate(10, ['*'], 'page_mis');
        }

        $masterReportApplications = $masterDashboardBaseQuery->paginate(10, ['*'], 'page_master');

        $counts = [
            'total' => (clone $masterDashboardBaseQuery)->count(),
            'pending' => $pendingApplicationsQuery->count(),
            'my' => $myApplicationsQuery->count(),
            'mis' => ($employee_details && $employee_details->isMisTeam()) ? (clone $misApplicationsQuery)->count() : 0,
            'approved' => (clone $masterDashboardBaseQuery)->whereIn('onboardings.status', ['approved', 'distributorship_created'])->count(),
            'in_process' => (clone $masterDashboardBaseQuery)->whereIn('onboardings.status', ['initiated', 'under_review', 'mis_processing', 'document_verified', 'agreement_created', 'documents_received'])->count(),
            'rejected' => (clone $masterDashboardBaseQuery)->whereIn('onboardings.status', ['rejected', 'mis_rejected'])->count(),
            'reverted' => (clone $masterDashboardBaseQuery)->where('onboardings.status', 'reverted')->count(),
            'pending_rbm' => (clone $masterDashboardBaseQuery)->where('onboardings.approval_level', 'Regional Business Manager')->whereIn('onboardings.status', ['initiated', 'under_review', 'on_hold', 'reverted'])->count(),
            'pending_zbm' => (clone $masterDashboardBaseQuery)->where('onboardings.approval_level', 'Zonal Business Manager')->whereIn('onboardings.status', ['initiated', 'under_review', 'on_hold', 'reverted'])->count(),
            'pending_gm' => (clone $masterDashboardBaseQuery)->where('onboardings.approval_level', 'General Manager')->whereIn('onboardings.status', ['initiated', 'under_review', 'on_hold', 'reverted'])->count(),
            'forwarded_to_mis' => (clone $masterDashboardBaseQuery)->whereIn('onboardings.status', ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'distributorship_created'])->count(),
            'distributors_created' => (clone $masterDashboardBaseQuery)->where('onboardings.status', 'distributorship_created')->count(),
            'on_hold_by_you' => (clone $masterDashboardBaseQuery)->where('onboardings.status', 'on_hold')->where('onboardings.current_approver_id', $user->emp_id)->count(),
            'approved_by_you' => ApprovalLog::where('user_id', $user->emp_id)->where('action', 'approved')
                                ->whereIn('application_id', (clone $masterDashboardBaseQuery)->select('onboardings.id')->pluck('id')->toArray())->count(),
            'rejected_by_you' => ApprovalLog::where('user_id', $user->emp_id)->where('action', 'rejected')
                                ->whereIn('application_id', (clone $masterDashboardBaseQuery)->select('onboardings.id')->pluck('id')->toArray())->count(),
            'reverted_by_you' => ApprovalLog::where('user_id', $user->emp_id)->where('action', 'reverted')
                                ->whereIn('application_id', (clone $masterDashboardBaseQuery)->select('onboardings.id')->pluck('id')->toArray())->count(),
            'doc_verification_pending' => (clone $masterDashboardBaseQuery)->where('onboardings.status', 'mis_processing')->count(),
            'agreements_created' => (clone $masterDashboardBaseQuery)->where('onboardings.status', 'agreement_created')->count(),
            'physical_docs_pending' => (clone $masterDashboardBaseQuery)
                ->where('onboardings.status', 'documents_received')
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                      ->from('physical_documents')
                      ->whereColumn('physical_documents.application_id', 'onboardings.id')
                      ->where('physical_documents.agreement_verified', 1)
                      ->where('physical_documents.security_cheque_verified', 1)
                      ->where('physical_documents.security_deposit_verified', 1);
                })->count(),
        ];

        $lastMonthFilters = $filters;
        $lastMonthFilters['date_from'] = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $lastMonthFilters['date_to'] = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        $lastMonthMasterDashboardBaseQuery = Onboarding::query()
            ->with(['entityDetails', 'createdBy', 'currentApprover', 'territoryDetail', 'regionDetail', 'zoneDetail'])
            ->select([
                'onboardings.id',
                'onboardings.territory',
                'onboardings.business_unit',
                'onboardings.created_by',
                'onboardings.current_approver_id',
                'onboardings.final_approver_id',
                'onboardings.approval_level',
                'onboardings.status',
                'onboardings.created_at',
                'onboardings.updated_at',
                'core_region.id as region_id',
                'core_zone.id as zone_id',
                'core_region.region_name',
                'core_territory.territory_name',
                'core_employee.emp_name as created_by_name',
                'core_employee.emp_designation as created_by_designation',
                'ca.emp_name as current_approver_name',
                'entity_details.id as entity_details_id',
                'entity_details.establishment_name',
            ])
            ->leftJoin('core_territory', 'onboardings.territory', '=', 'core_territory.id')
            ->leftJoin('core_region_territory_mapping as rtm', 'core_territory.id', '=', 'rtm.territory_id')
            ->leftJoin('core_region', 'rtm.region_id', '=', 'core_region.id')
            ->leftJoin('core_zone_region_mapping as zrm', 'core_region.id', '=', 'zrm.region_id')
            ->leftJoin('core_zone', 'zrm.zone_id', '=', 'core_zone.id')
            ->leftJoin('core_employee', 'onboardings.created_by', '=', 'core_employee.employee_id')
            ->leftJoin('core_employee as ca', 'onboardings.current_approver_id', '=', 'ca.employee_id')
            ->leftJoin('entity_details', 'onboardings.id', '=', 'entity_details.application_id');

        if ($employee_details) {
            if ($access_level === 'territory' && $employee_details->territory > 0) {
                $lastMonthMasterDashboardBaseQuery->where('onboardings.territory', $employee_details->territory);
            } elseif ($access_level === 'region' && $employee_details->region > 0) {
                $lastMonthMasterDashboardBaseQuery->where('rtm.region_id', $employee_details->region);
            } elseif ($access_level === 'zone' && $employee_details->zone > 0) {
                $lastMonthMasterDashboardBaseQuery->where('zrm.zone_id', $employee_details->zone);
            } elseif ($access_level === 'bu' && $employee_details->bu > 0) {
                $lastMonthMasterDashboardBaseQuery->where('onboardings.business_unit', $employee_details->bu);
            }
        }

        if ($filters['date_range_type'] == 'approval') {
            $lastMonthMasterDashboardBaseQuery->whereExists(function ($q) use ($lastMonthFilters) {
                $q->select(DB::raw(1))
                  ->from('approval_logs')
                  ->whereColumn('approval_logs.application_id', 'onboardings.id')
                  ->where('approval_logs.action', 'approved')
                  ->whereBetween('approval_logs.created_at', [$lastMonthFilters['date_from'], $lastMonthFilters['date_to']]);
            });
        } else {
            $dateField = match ($filters['date_range_type']) {
                'submission' => 'onboardings.created_at',
                'appointment' => 'onboardings.updated_at',
                default => 'onboardings.created_at',
            };
            $lastMonthMasterDashboardBaseQuery->whereBetween($dateField, [$lastMonthFilters['date_from'], $lastMonthFilters['date_to']]);
        }

        $lastMonthCounts = [
            'total' => (clone $lastMonthMasterDashboardBaseQuery)->count(),
            'distributors_created' => (clone $lastMonthMasterDashboardBaseQuery)->where('onboardings.status', 'distributorship_created')->count(),
            'reverted' => (clone $lastMonthMasterDashboardBaseQuery)->where('onboardings.status', 'reverted')->count(),
            'rejected' => (clone $lastMonthMasterDashboardBaseQuery)->whereIn('onboardings.status', ['rejected', 'mis_rejected'])->count(),
            'forwarded_to_mis' => (clone $lastMonthMasterDashboardBaseQuery)->whereIn('onboardings.status', ['mis_processing', 'document_verified', 'agreement_created', 'documents_received', 'distributorship_created'])->count(),
        ];

        $kpi_trends = [
            'total_submitted' => $this->calculatePercentageChange($lastMonthCounts['total'], $counts['total']),
            'appointments_completed' => $this->calculatePercentageChange($lastMonthCounts['distributors_created'], $counts['distributors_created']),
            'reverted' => $this->calculatePercentageChange($lastMonthCounts['reverted'], $counts['reverted']),
            'rejected' => $this->calculatePercentageChange($lastMonthCounts['rejected'], $counts['rejected']),
            'forwarded_to_mis' => $this->calculatePercentageChange($lastMonthCounts['forwarded_to_mis'], $counts['forwarded_to_mis']),
            'avg_tat' => (($currentTatAvg = $this->calculateTAT(clone $masterDashboardBaseQuery, clone $masterDashboardBaseQuery)['total']['avg_tat']) !== 0 && ($lastMonthTatAvg = $this->calculateTAT(clone $lastMonthMasterDashboardBaseQuery, clone $lastMonthMasterDashboardBaseQuery)['total']['avg_tat']) !== 0)
                ? round($currentTatAvg - $lastMonthTatAvg, 1)
                : 0,
        ];

        $tatData = $this->calculateTAT(clone $masterDashboardBaseQuery, clone $masterDashboardBaseQuery);

        $actionSummary = [];
        $userActions = [];
        $filteredApplicationIds = (clone $masterDashboardBaseQuery)->pluck('onboardings.id')->toArray();

        if (!empty($filteredApplicationIds)) {
            $actionSummaryRaw = ApprovalLog::select('approval_logs.user_id', 'core_employee.emp_name', 'approval_logs.action', DB::raw('COUNT(*) as action_count'))
                ->join('core_employee', 'approval_logs.user_id', '=', 'core_employee.employee_id')
                ->whereIn('approval_logs.application_id', $filteredApplicationIds)
                ->groupBy('approval_logs.user_id', 'core_employee.emp_name', 'approval_logs.action')
                ->get();

            foreach ($actionSummaryRaw as $row) {
                if (!isset($userActions[$row->user_id])) {
                    $userActions[$row->user_id] = ['user_name' => $row->emp_name, 'actions' => []];
                }
                $userActions[$row->user_id]['actions'][$row->action] = $row->action_count;
            }
        }
        $actionSummary = array_values($userActions);

        return compact(
            'pendingApplications', 'myApplications', 'misApplications', 'masterReportApplications',
            'actionSummary', 'counts', 'tatData', 'kpi_trends', 'bu_list', 'zone_list', 'region_list', 'territory_list', 'access_level', 'filters'
        );
    }

    protected function calculatePercentageChange($oldValue, $newValue)
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }
        return round(($newValue - $oldValue) / $oldValue * 100, 1);
    }

    protected function calculateTAT($baseQueryForTat, $masterReportQueryForTat)
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
            $q = (clone $baseQueryForTat);
            $q->select(
                'onboardings.id',
                DB::raw('MIN(from_log.created_at) as from_date'),
                DB::raw('MIN(to_log.created_at) as to_date')
            )
              ->join('approval_logs as from_log', function ($join) use ($stageInfo) {
                  $join->on('onboardings.id', '=', 'from_log.application_id')
                       ->where('from_log.role', $stageInfo['from_role']);
              })
              ->join('approval_logs as to_log', function ($join) use ($stageInfo) {
                  $join->on('onboardings.id', '=', 'to_log.application_id')
                       ->where('to_log.role', $stageInfo['to_role'])
                       ->where('to_log.action', 'approved');
              })
              ->groupBy('onboardings.id');

            $applicationsInStage = $q->get();

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
                'exceeding_sla' => $durationsCollection->filter(fn ($days) => $days > $stageInfo['sla'])->count(),
            ];
        }

        $totalApplicationsQuery = (clone $masterReportQueryForTat)
            ->where('onboardings.status', 'distributorship_created')
            ->select('onboardings.created_at', 'onboardings.updated_at');

        $totalApplications = $totalApplicationsQuery->get();

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
            'exceeding_sla' => $totalDurationsCollection->filter(fn ($days) => $days > 12)->count(),
        ];

        return $tatResults;
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

      public function notificationMarkRead(Request $request): JsonResponse
    {
        $notification = Notification::find($request->id);
        $notification->notification_read = 1;
        $query = $notification->save();
        if (!$query) {
            return response()->json(['status' => 400, 'msg' => 'Something went wrong..!!']);
        } else {
            return response()->json(['status' => 200]);
        }
    }

    /**
     * @return JsonResponse
     */
    public function markAllRead(): JsonResponse
    {
        $query = Notification::where('userid', '=', Auth::user()->id)->update(array('notification_read' => 1));
        if (!$query) {
            return response()->json(['status' => 400, 'msg' => 'Something went wrong..!!']);
        } else {
            return response()->json(['status' => 200]);
        }
    }
}
