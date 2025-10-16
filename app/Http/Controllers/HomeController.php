<?php

namespace App\Http\Controllers;

use App\Models\ApprovalLog;
use App\Models\Onboarding;
use App\Models\Employee;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\helpers;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            'bu' => $request->input('bu', 'All'),
            'zone' => $request->input('zone', 'All'),
            'region' => $request->input('region', 'All'),
            'territory' => $request->input('territory', 'All'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $employee_details = $user->employee;
        $access_level = 'territory';
        if ($employee_details) {
            if ($user->hasAnyRole('Super Admin', 'Admin', 'SP Admin', 'Management', 'Mis Admin', 'Mis User')) {
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

        $isAdminUser = $user->hasAnyRole(['Super Admin', 'Admin']) || $user->hasPermissionTo('distributor_approval');
        $isMisUser = $user->hasAnyRole(['Mis Admin', 'Mis User']);
        $approverDesignations = ['Regional Business Manager', 'Zonal Business Manager', 'General Manager']; // Add more as needed

        $isApprover = !$isAdminUser && !$isMisUser && in_array($employee_details->emp_designation ?? '', $approverDesignations);
        $showAdminDashboard = $isAdminUser;
        $showMisDashboard = $isMisUser;
        $showApproverDashboard = $isApprover;
        $showSalesDashboard = Auth::check() && !$showAdminDashboard && !$showMisDashboard && !$showApproverDashboard;

        // Fetch all active statuses dynamically
        $allStatuses = Status::where('is_active', 1)->orderBy('sort_order', 'asc')->get();

        // Define dynamic status groups for KPIs (tailored for dashboard)
        $statusGroups = [
            'pending' => [
                'label' => 'Pending',
                'slugs' => $allStatuses->whereIn('name', ['under_level1_review', 'under_level2_review', 'under_level3_review', 'reverted', 'on_hold'])->pluck('name')->implode(',')
            ],
            'actionable' => [
                'label' => 'Actionable',
                'slugs' => $allStatuses->whereIn('name', ['under_level1_review', 'under_level2_review', 'under_level3_review', 'reverted', 'on_hold'])->pluck('name')->implode(',')
            ],
            'mis' => [
                'label' => 'MIS Processing',
                'slugs' => $allStatuses->where('category', 'mis_processing')->pluck('name')->implode(',')
            ],
            'completed' => [
                'label' => 'Completed',
                'slugs' => $allStatuses->where('category', 'completion')->pluck('name')->implode(',')
            ],
            'rejected' => [
                'label' => 'Rejected',
                'slugs' => $allStatuses->where('category', 'rejection')->pluck('name')->implode(',')
            ],
            'hold' => [
                'label' => 'On Hold',
                'slugs' => $allStatuses->where('name', 'on_hold')->pluck('name')->implode(',')
            ],
            'reverted' => [
                'label' => 'Reverted',
                'slugs' => $allStatuses->where('name', 'reverted')->pluck('name')->implode(',')
            ],
            'approved' => [
                'label' => 'Approved',
                'slugs' => $allStatuses->where('name', 'approved')->pluck('name')->implode(',')
            ]
        ];

        // Specific KPI status mappings (using groups where possible)
        $kpiStatusMappings = [
            'document_verified' => $allStatuses->where('name', 'documents_verified')->first()->name ?? 'documents_verified',
            'agreement_created' => $allStatuses->where('name', 'agreement_created')->first()->name ?? 'agreement_created',
            'physical_docs_verified' => $allStatuses->where('name', 'physical_docs_verified')->first()->name ?? 'physical_docs_verified',
            'distributorship_created' => $allStatuses->where('name', 'distributorship_created')->first()->name ?? 'distributorship_created',
            'mis_rejected' => $statusGroups['rejected']['slugs'],  // Use group
            'in_process' => $statusGroups['mis']['slugs'],  // Use group
            // Add more as needed
        ];

        if ($showSalesDashboard) {
            $data = $this->getSalesData($filters, $user, $access_level, $request, $statusGroups, $kpiStatusMappings);
        } elseif ($showApproverDashboard) {
            $data = $this->getApproverData($filters, $user, $access_level, $request, $statusGroups, $kpiStatusMappings);
        } elseif ($showMisDashboard) {
            $data = $this->getMISData($filters, $user, $access_level, $request, $statusGroups, $kpiStatusMappings);
            // dd($data);
        } else {
            $data = $this->getDashboardData($filters, $user, $access_level, $request, $statusGroups, $kpiStatusMappings);
        }
        extract($data);

        $pendingApplications = $pendingApplications ?? new LengthAwarePaginator([], 0, 10);
        $approverPendingApplications = $approverPendingApplications ?? new LengthAwarePaginator([], 0, 10);
        $myApplications = $myApplications ?? new LengthAwarePaginator([], 0, 10);
        $misApplications = $misApplications ?? new LengthAwarePaginator([], 0, 10);
        $masterReportApplications = $masterReportApplications ?? new LengthAwarePaginator([], 0, 10);
        $tatData = $tatData ?? $this->getDefaultTatData();
        $bu_list = $bu_list ?? [];
        $zone_list = $zone_list ?? [];
        $region_list = $region_list ?? [];
        $territory_list = $territory_list ?? [];
        $actionSummary = $actionSummary ?? [];
        $statuses = $statuses ?? collect();
        return view('dashboard.dashboard', compact(
            'pendingApplications',
            'approverPendingApplications',
            'myApplications',
            'misApplications',
            'masterReportApplications',
            'actionSummary',
            'data',
            'filters',
            'tatData',
            'bu_list',
            'zone_list',
            'region_list',
            'territory_list',
            'access_level',
            'showSalesDashboard',
            'showAdminDashboard',
            'showApproverDashboard',
            'showMisDashboard',
            'statusGroups',
            'kpiStatusMappings',
            'statuses'
        ));
    }

    public function dynamicData(Request $request)
    {
        try {
            $user = Auth::user();
            $dashboardType = $request->input('dashboard_type', 'regular');

            $filters = [
                'bu' => $request->input('bu', 'All'),
                'zone' => $request->input('zone', 'All'),
                'region' => $request->input('region', 'All'),
                'territory' => $request->input('territory', 'All'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
            ];

            $employee_details = $user->employee;
            $access_level = 'territory';
            if ($employee_details) {
                if ($user->hasAnyRole(['Super Admin', 'Admin', 'SP Admin', 'Management', 'Mis Admin', 'Mis User'])) {
                    $access_level = 'all';
                } elseif ($employee_details->territory > 0) {
                    $access_level = 'territory';
                } elseif ($employee_details->region > 0) {
                    $access_level = 'region';
                } elseif ($employee_details->zone > 0) {
                    $access_level = 'zone';
                } elseif ($employee_details->bu > 0) {
                    $access_level = 'bu';
                } elseif ($employee_details->isMisTeam()) {
                    $access_level = 'all';
                }
            }

            $isAdminUser = $user->hasAnyRole(['Super Admin', 'Admin']) || $user->hasPermissionTo('distributor_approval');
            $isMisUser = $user->hasAnyRole(['Mis Admin', 'Mis User']);
            $approverDesignations = ['Regional Business Manager', 'Zonal Business Manager', 'General Manager'];
            $isApprover = !$isAdminUser && !$isMisUser && in_array($employee_details->emp_designation ?? '', $approverDesignations);
            $isSalesUser = !$isAdminUser && !$isMisUser && !$isApprover;

            // Fetch all active statuses dynamically
            $allStatuses = Status::where('is_active', 1)->orderBy('sort_order', 'asc')->get();

            // Define dynamic status groups for KPIs
            $statusGroups = [
                'pending' => [
                    'label' => 'Pending',
                    'slugs' => $allStatuses->whereIn('name', ['under_level1_review', 'under_level2_review', 'under_level3_review', 'reverted', 'on_hold'])->pluck('name')->implode(',')
                ],
                'actionable' => [
                    'label' => 'Actionable',
                    'slugs' => $allStatuses->whereIn('name', ['under_level1_review', 'under_level2_review', 'under_level3_review', 'reverted', 'on_hold'])->pluck('name')->implode(',')
                ],
                'mis' => [
                    'label' => 'MIS Processing',
                    'slugs' => $allStatuses->where('category', 'mis_processing')->pluck('name')->implode(',')
                ],
                'completed' => [
                    'label' => 'Completed',
                    'slugs' => $allStatuses->where('category', 'completion')->pluck('name')->implode(',')
                ],
                'rejected' => [
                    'label' => 'Rejected',
                    'slugs' => $allStatuses->where('category', 'rejection')->pluck('name')->implode(',')
                ],
                'hold' => [
                    'label' => 'On Hold',
                    'slugs' => $allStatuses->where('name', 'on_hold')->pluck('name')->implode(',')
                ],
                'reverted' => [
                    'label' => 'Reverted',
                    'slugs' => $allStatuses->where('name', 'reverted')->pluck('name')->implode(',')
                ],
                'approved' => [
                    'label' => 'Approved',
                    'slugs' => $allStatuses->where('name', 'approved')->pluck('name')->implode(',')
                ]
            ];

            // Specific KPI status mappings
            $kpiStatusMappings = [
                'document_verified' => $allStatuses->where('name', 'documents_verified')->first()->name ?? 'documents_verified',
                'agreement_created' => $allStatuses->where('name', 'agreement_created')->first()->name ?? 'agreement_created',
                'physical_docs_verified' => $allStatuses->where('name', 'physical_docs_verified')->first()->name ?? 'physical_docs_verified',
                'distributorship_created' => $allStatuses->where('name', 'distributorship_created')->first()->name ?? 'distributorship_created',
                'mis_rejected' => $statusGroups['rejected']['slugs'],
                'in_process' => $statusGroups['mis']['slugs'],
            ];

            if ($dashboardType === 'approver') {
                $data = $this->getApproverData($filters, $user, $access_level, $request, $statusGroups, $kpiStatusMappings);

                $data['approver_pending_table_html'] = view('dashboard._approver-pending-table', [
                    'approverPendingApplications' => $data['approverPendingApplications'] ?? new LengthAwarePaginator([], 0, 10)
                ])->render();
                $data['statusGroups'] = $statusGroups;
                $data['kpiStatusMappings'] = $kpiStatusMappings;

                return response()->json($data);
            } elseif ($dashboardType === 'mis') {
                $data = $this->getMISData($filters, $user, $access_level, $request, $statusGroups, $kpiStatusMappings);
                // dd($data);
                $data['mis_table_html'] = view('dashboard._mis-table', [
                    'misApplications' => $data['misApplications'] ?? new LengthAwarePaginator([], 0, 10),
                    'statuses' => $data['statuses'] ?? []
                ])->render();
                $data['statusGroups'] = $statusGroups;
                $data['kpiStatusMappings'] = $kpiStatusMappings;
                return response()->json($data);
            } elseif ($dashboardType === 'sales') {
                $data = $this->getSalesData($filters, $user, $access_level, $request, $statusGroups, $kpiStatusMappings);
                $data['my_table_html'] = view('dashboard._sales-table', [
                    'myApplications' => $data['myApplications'] ?? new LengthAwarePaginator([], 0, 10),
                    'territories' => $data['territories'] ?? collect(),
                    'statuses' => $data['statuses'] ?? []
                ])->render();
                $data['statusGroups'] = $statusGroups;
                $data['kpiStatusMappings'] = $kpiStatusMappings;

                return response()->json($data);
            } else {
                // Admin dashboard
                $data = $this->getDashboardData($filters, $user, $access_level, $request, $statusGroups, $kpiStatusMappings);
                $data['counts'] = $data['counts'] ?? $this->getDefaultCounts();
                $data['tat'] = $data['tat'] ?? $this->getDefaultTatData();
                $data['kpi_trends'] = $data['kpi_trends'] ?? $this->getDefaultKpiTrends();
                $data['actionSummary'] = $data['actionSummary'] ?? [];

                $data['master_table_html'] = view('dashboard._master-table', [
                    'masterReportApplications' => $data['masterReportApplications'] ?? new LengthAwarePaginator([], 0, 10),
                    'tatData' => $data['tat']
                ])->render();

                $data['pending_table_html'] = view('dashboard._approver-table', [
                    'pendingApplications' => $data['pendingApplications'] ?? new LengthAwarePaginator([], 0, 10)
                ])->render();

                $data['statusGroups'] = $statusGroups;
                $data['kpiStatusMappings'] = $kpiStatusMappings;

                return response()->json($data);
            }
        } catch (\Exception $e) {
            Log::error('Error in dynamicData: ' . $e->getMessage() . ' on line ' . $e->getLine() . ' in file ' . $e->getFile());
            return response()->json(['error' => 'Server error: Unable to fetch dashboard data. Please try again later.'], 500);
        }
    }

    // Helper method to get status names by category
    protected function getStatusesByCategory($category, $names = [])
    {
        $query = Status::where('category', $category)->where('is_active', 1)->orderBy('sort_order');
        if (!empty($names)) {
            $query->whereIn('name', $names);
        }
        return $query->pluck('name')->toArray();
    }

    protected function getMISData(array $filters, $user, $access_level, Request $request, $statusGroups, $kpiStatusMappings)
    {
        $bu_list = $this->helper->getAssociatedBusinessUnitList($user->emp_id);
        $zone_list = $this->helper->getAssociatedZoneList($user->emp_id);
        $region_list = $this->helper->getAssociatedRegionList($user->emp_id);
        $territory_list = $this->helper->getAssociatedTerritoryList($user->emp_id);

        // Use dynamic groups for queries - INCLUDE distributorship_created in MIS statuses
        $misStatuses = array_merge(
            explode(',', $statusGroups['mis']['slugs']),
            [$kpiStatusMappings['distributorship_created']] // Add distributorship_created
        );

        $rejectionSlugs = explode(',', $statusGroups['rejected']['slugs']);

        // Base query for ALL applications available to MIS (without status filter)
        $allMISApplicationsQuery = Onboarding::query()
            ->with(['entityDetails', 'createdBy', 'currentApprover', 'territoryDetail', 'regionDetail', 'zoneDetail', 'approvalLogs'])
            ->select([
                'onboardings.id',
                'onboardings.application_code',
                'onboardings.territory',
                'onboardings.region',
                'onboardings.zone',
                'onboardings.business_unit',
                'onboardings.status',
                'onboardings.doc_verification_status',
                'onboardings.agreement_status',
                'onboardings.physical_docs_status',
                'onboardings.final_status',
                'onboardings.created_by',
                'onboardings.current_approver_id',
                'onboardings.final_approver_id',
                'onboardings.approval_level',
                'onboardings.created_at',
                'onboardings.updated_at',
                'core_region.region_name',
                'core_zone.zone_name',
                'core_territory.territory_name',
                'core_employee.emp_name as created_by_name',
                'core_employee.emp_designation as created_by_designation',
                'ca.emp_name as current_approver_name',
                'entity_details.establishment_name',
            ])
            ->leftJoin('core_territory', 'onboardings.territory', '=', 'core_territory.id')
            ->leftJoin('core_region', 'onboardings.region', '=', 'core_region.id')
            ->leftJoin('core_zone', 'onboardings.zone', '=', 'core_zone.id')
            ->leftJoin('core_employee', 'onboardings.created_by', '=', 'core_employee.employee_id')
            ->leftJoin('core_employee as ca', 'onboardings.current_approver_id', '=', 'ca.employee_id')
            ->leftJoin('entity_details', 'onboardings.id', '=', 'entity_details.application_id');

        // Apply filters to the base query
        if ($filters['territory'] && $filters['territory'] !== 'All') {
            $allMISApplicationsQuery->where('onboardings.territory', $filters['territory']);
        }
        if ($filters['region'] && $filters['region'] !== 'All') {
            $allMISApplicationsQuery->where('onboardings.region', $filters['region']);
        }
        if ($filters['zone'] && $filters['zone'] !== 'All') {
            $allMISApplicationsQuery->where('onboardings.zone', $filters['zone']);
        }
        if ($filters['bu'] && $filters['bu'] !== 'All') {
            $allMISApplicationsQuery->where('onboardings.business_unit', $filters['bu']);
        }
        if ($filters['date_from'] && $filters['date_to']) {
            $allMISApplicationsQuery->whereBetween('onboardings.created_at', [$filters['date_from'], $filters['date_to']]);
        }

        // Create a separate query for MIS status applications (for pagination and in_process count)
        $misApplicationsQuery = (clone $allMISApplicationsQuery)->whereIn('onboardings.status', $misStatuses);
        $misApplications = $misApplicationsQuery->paginate(10);

        // Dynamic counts using mappings and groups
        // TOTAL: All applications available to MIS (regardless of status)
        $counts = [
            'total' => (clone $allMISApplicationsQuery)->count(),
            'document_verified' => (clone $allMISApplicationsQuery)->where('onboardings.status', $kpiStatusMappings['document_verified'])->count(),
            'agreement_created' => (clone $allMISApplicationsQuery)->where('onboardings.status', $kpiStatusMappings['agreement_created'])->count(),
            'physical_docs_verified' => (clone $allMISApplicationsQuery)->where('onboardings.status', $kpiStatusMappings['physical_docs_verified'])->count(),
            'distributorship_created' => (clone $allMISApplicationsQuery)->where('onboardings.status', $kpiStatusMappings['distributorship_created'])->count(),
            'mis_rejected' => (clone $allMISApplicationsQuery)->whereIn('onboardings.status', $rejectionSlugs)->count(),
            'in_process' => (clone $allMISApplicationsQuery)->whereIn('onboardings.status', $misStatuses)->count(),
        ];
        $counts = array_merge($this->getDefaultCounts(), $counts);
        // Trends (using last month, same logic with dynamic)
        $lastMonthFilters = $filters;
        $lastMonthFilters['date_from'] = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $lastMonthFilters['date_to'] = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        $lastMonthQuery = (clone $allMISApplicationsQuery)->whereBetween('onboardings.created_at', [$lastMonthFilters['date_from'], $lastMonthFilters['date_to']]);

        $lastMonthCounts = [
            'total' => $lastMonthQuery->count(),
            'document_verified' => (clone $lastMonthQuery)->where('onboardings.status', $kpiStatusMappings['document_verified'])->count(),
            'agreement_created' => (clone $lastMonthQuery)->where('onboardings.status', $kpiStatusMappings['agreement_created'])->count(),
            'physical_docs_verified' => (clone $lastMonthQuery)->where('onboardings.status', $kpiStatusMappings['physical_docs_verified'])->count(),
            'distributorship_created' => (clone $lastMonthQuery)->where('onboardings.status', $kpiStatusMappings['distributorship_created'])->count(),
            'mis_rejected' => (clone $lastMonthQuery)->whereIn('onboardings.status', $rejectionSlugs)->count(),
            'in_process' => (clone $lastMonthQuery)->whereIn('onboardings.status', $misStatuses)->count(),
        ];

        $kpi_trends = [
            'total_submitted' => $this->calculatePercentageChange($lastMonthCounts['total'], $counts['total']),
            'document_verified' => $this->calculatePercentageChange($lastMonthCounts['document_verified'], $counts['document_verified']),
            'agreement_created' => $this->calculatePercentageChange($lastMonthCounts['agreement_created'], $counts['agreement_created']),
            'physical_docs_verified' => $this->calculatePercentageChange($lastMonthCounts['physical_docs_verified'], $counts['physical_docs_verified']),
            'distributorship_created' => $this->calculatePercentageChange($lastMonthCounts['distributorship_created'], $counts['distributorship_created']),
            'mis_rejected' => $this->calculatePercentageChange($lastMonthCounts['mis_rejected'], $counts['mis_rejected']),
            'in_process' => $this->calculatePercentageChange($lastMonthCounts['in_process'], $counts['in_process']),
        ];
        $kpi_trends = array_merge($this->getDefaultKpiTrends(), $kpi_trends);
        $tatData = $this->calculateMisTAT($misApplicationsQuery, $statusGroups);

        // Add statuses to the data
        $statuses = Status::where('is_active', 1)->orderBy('sort_order')->get();

        return compact(
            'misApplications',
            'bu_list',
            'zone_list',
            'region_list',
            'territory_list',
            'counts',
            'tatData',
            'kpi_trends',
            'statuses'
        );
    }

    protected function getSalesData(array $filters, $user, $access_level, Request $request, $statusGroups, $kpiStatusMappings)
    {
        $bu_list = $this->helper->getAssociatedBusinessUnitList($user->emp_id);
        $zone_list = $this->helper->getAssociatedZoneList($user->emp_id);
        $region_list = $this->helper->getAssociatedRegionList($user->emp_id);
        $territory_list = $this->helper->getAssociatedTerritoryList($user->emp_id);

        // Use dynamic groups
        $approvalSlugs = explode(',', $statusGroups['actionable']['slugs']);
        $misSlugs = explode(',', $statusGroups['mis']['slugs']);
        $completionSlugs = explode(',', $statusGroups['completed']['slugs']);
        $rejectionSlugs = explode(',', $statusGroups['rejected']['slugs']);

        $myApplicationsQuery = Onboarding::query()
            ->with(['entityDetails', 'createdBy', 'territoryDetail', 'regionDetail', 'zoneDetail', 'approvalLogs'])
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

        // Apply access level filters
        $employee_details = $user->employee;
        if ($employee_details) {
            if ($access_level === 'territory' && $employee_details->territory > 0) {
                $myApplicationsQuery->where('onboardings.territory', $employee_details->territory);
            } elseif ($access_level === 'region' && $employee_details->region > 0) {
                $myApplicationsQuery->where('onboardings.region', $employee_details->region);
            } elseif ($access_level === 'zone' && $employee_details->zone > 0) {
                $myApplicationsQuery->where('onboardings.zone', $employee_details->zone);
            } elseif ($access_level === 'bu' && $employee_details->bu > 0) {
                $myApplicationsQuery->where('onboardings.business_unit', $employee_details->bu);
            }
        }

        // Apply filters
        if ($filters['territory'] && $filters['territory'] != 'All') {
            $myApplicationsQuery->where('onboardings.territory', $filters['territory']);
        }
        if ($filters['region'] && $filters['region'] != 'All') {
            $myApplicationsQuery->where('onboardings.region', $filters['region']);
        }
        if ($filters['zone'] && $filters['zone'] != 'All') {
            $myApplicationsQuery->where('onboardings.zone', $filters['zone']);
        }
        if ($filters['bu'] && $filters['bu'] != 'All') {
            $myApplicationsQuery->where('onboardings.business_unit', $filters['bu']);
        }
        if ($filters['date_from'] && $filters['date_to']) {
            $myApplicationsQuery->whereBetween('onboardings.created_at', [$filters['date_from'], $filters['date_to']]);
        }

        // Paginate myApplications for table rendering
        $myApplications = $myApplicationsQuery->orderBy('created_at', 'desc')->paginate(10);

        // Fetch territories and statuses for the table
        $territories = \App\Models\CoreTerritory::orderBy('territory_name')->get(); // Adjust model if needed
        $statuses = Status::where('is_active', 1)->orderBy('sort_order')->pluck('name')->toArray();

        // Dynamic counts
        $sales_counts = [
            'total_created' => (clone $myApplicationsQuery)->count(),
            'in_approval' => (clone $myApplicationsQuery)->whereIn('onboardings.status', $approvalSlugs)->count(),
            'to_mis' => (clone $myApplicationsQuery)->whereIn('onboardings.status', $misSlugs)->count(),
            'completed' => (clone $myApplicationsQuery)->whereIn('onboardings.status', $completionSlugs)->count(),
            'rejected' => (clone $myApplicationsQuery)->whereIn('onboardings.status', $rejectionSlugs)->count(),
        ];
$sales_counts = array_merge($this->getDefaultCounts(), $sales_counts);
        // Last month trends
        $lastMonthFilters = $filters;
        $lastMonthFilters['date_from'] = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $lastMonthFilters['date_to'] = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        $lastMonthMyQuery = (clone $myApplicationsQuery)->whereBetween('onboardings.created_at', [$lastMonthFilters['date_from'], $lastMonthFilters['date_to']]);

        $lastMonthSalesCounts = [
            'total_created' => $lastMonthMyQuery->count(),
            'in_approval' => (clone $lastMonthMyQuery)->whereIn('onboardings.status', $approvalSlugs)->count(),
            'to_mis' => (clone $lastMonthMyQuery)->whereIn('onboardings.status', $misSlugs)->count(),
            'completed' => (clone $lastMonthMyQuery)->whereIn('onboardings.status', $completionSlugs)->count(),
            'rejected' => (clone $lastMonthMyQuery)->whereIn('onboardings.status', $rejectionSlugs)->count(),
        ];

        $sales_kpi_trends = [
            'total_created' => $this->calculatePercentageChange($lastMonthSalesCounts['total_created'], $sales_counts['total_created']),
            'in_approval' => $this->calculatePercentageChange($lastMonthSalesCounts['in_approval'], $sales_counts['in_approval']),
            'to_mis' => $this->calculatePercentageChange($lastMonthSalesCounts['to_mis'], $sales_counts['to_mis']),
            'completed' => $this->calculatePercentageChange($lastMonthSalesCounts['completed'], $sales_counts['completed']),
            'rejected' => $this->calculatePercentageChange($lastMonthSalesCounts['rejected'], $sales_counts['rejected']),
        ];
$sales_kpi_trends = array_merge($this->getDefaultSalesKpiTrends(), $sales_kpi_trends);
        return compact('bu_list', 'zone_list', 'region_list', 'territory_list', 'sales_counts', 'sales_kpi_trends', 'myApplications', 'territories', 'statuses');
    }

    protected function getApproverData(array $filters, $user, $access_level, Request $request, $statusGroups, $kpiStatusMappings)
    {
        $bu_list = $this->helper->getAssociatedBusinessUnitList($user->emp_id);
        $zone_list = $this->helper->getAssociatedZoneList($user->emp_id);
        $region_list = $this->helper->getAssociatedRegionList($user->emp_id);
        $territory_list = $this->helper->getAssociatedTerritoryList($user->emp_id);

        // Dynamic pending statuses
        $pendingSlugs = explode(',', $statusGroups['actionable']['slugs']);
        $holdSlugs = explode(',', $statusGroups['hold']['slugs']);
        $revertedSlugs = explode(',', $statusGroups['reverted']['slugs']);

        $approverPendingQuery = Onboarding::query()
            ->with(['entityDetails', 'createdBy', 'territoryDetail', 'regionDetail', 'zoneDetail'])
            ->where('onboardings.current_approver_id', $user->emp_id);

        // Apply access level and filters
        $employee_details = $user->employee;
        if ($employee_details) {
            if ($access_level === 'territory' && $employee_details->territory > 0) {
                $approverPendingQuery->where('onboardings.territory', $employee_details->territory);
            } elseif ($access_level === 'region' && $employee_details->region > 0) {
                $approverPendingQuery->where('onboardings.region', $employee_details->region);
            } elseif ($access_level === 'zone' && $employee_details->zone > 0) {
                $approverPendingQuery->where('onboardings.zone', $employee_details->zone);
            } elseif ($access_level === 'bu' && $employee_details->bu > 0) {
                $approverPendingQuery->where('onboardings.business_unit', $employee_details->bu);
            }
        }

        if ($filters['territory'] && $filters['territory'] !== 'All') {
            $approverPendingQuery->where('onboardings.territory', $filters['territory']);
        }
        if ($filters['region'] && $filters['region'] !== 'All') {
            $approverPendingQuery->where('onboardings.region', $filters['region']);
        }
        if ($filters['zone'] && $filters['zone'] !== 'All') {
            $approverPendingQuery->where('onboardings.zone', $filters['zone']);
        }
        if ($filters['bu'] && $filters['bu'] !== 'All') {
            $approverPendingQuery->where('onboardings.business_unit', $filters['bu']);
        }
        if ($filters['date_from'] && $filters['date_to']) {
            $approverPendingQuery->whereBetween('onboardings.created_at', [$filters['date_from'], $filters['date_to']]);
        }

        $approverPendingApplications = $approverPendingQuery->orderBy('created_at', 'desc')->paginate(10);

        // Build date filter for logs (if dates provided)
        $logDateFilter = [];
        if ($filters['date_from'] && $filters['date_to']) {
            $logDateFilter = [
                ['created_at', '>=', $filters['date_from']],
                ['created_at', '<=', $filters['date_to']]
            ];
        }
        $onHoldQuery = clone $approverPendingQuery;
        // Counts using ApprovalLog for historical actions + current query for pending/on_hold
        $counts = [
            'pending_your_approval' => $approverPendingQuery->count(), // Current pending (no log needed)
            'on_hold_by_you' => $onHoldQuery->where('onboardings.status', 'on_hold')->count(),
            'approved_by_you' => ApprovalLog::where('user_id', $user->emp_id)
                ->where('action', 'approved')
                ->where($logDateFilter)
                ->count(),
            'rejected_by_you' => ApprovalLog::where('user_id', $user->emp_id)
                ->where('action', 'rejected')
                ->where($logDateFilter)
                ->count(),
            'reverted_by_you' => ApprovalLog::where('user_id', $user->emp_id)
                ->where('action', 'reverted')
                ->where($logDateFilter)
                ->count(),
        ];
        $counts = array_merge($this->getDefaultCounts(), $counts);
        //dd($counts);
        // Trends (last month) - Similar log-based with date filter
        $lastMonthFrom = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $lastMonthTo = Carbon::now()->subMonth()->endOfMonth()->toDateString();
        $lastMonthLogFilter = [
            ['created_at', '>=', $lastMonthFrom],
            ['created_at', '<=', $lastMonthTo]
        ];

        $lastMonthCounts = [
            'pending_your_approval' => (clone $approverPendingQuery)
                ->whereBetween('onboardings.created_at', [$lastMonthFrom, $lastMonthTo])
                ->count(), // Adjust for last month pending (if needed; else use 0 or log-based)
            'on_hold_by_you' => (clone $onHoldQuery)
                ->whereBetween('onboardings.created_at', [$lastMonthFrom, $lastMonthTo])
                ->where('onboardings.status', 'on_hold')
                ->count(),
            'approved_by_you' => ApprovalLog::where('user_id', $user->emp_id)
                ->where('action', 'approved')
                ->where($lastMonthLogFilter)
                ->count(),
            'rejected_by_you' => ApprovalLog::where('user_id', $user->emp_id)
                ->where('action', 'rejected')
                ->where($lastMonthLogFilter)
                ->count(),
            'reverted_by_you' => ApprovalLog::where('user_id', $user->emp_id)
                ->where('action', 'reverted')
                ->where($lastMonthLogFilter)
                ->count(),
        ];

        $kpi_trends = [
            'pending_your_approval' => $this->calculatePercentageChange($lastMonthCounts['pending_your_approval'], $counts['pending_your_approval']),
            'on_hold_by_you' => $this->calculatePercentageChange($lastMonthCounts['on_hold_by_you'], $counts['on_hold_by_you']),
            'approved_by_you' => $this->calculatePercentageChange($lastMonthCounts['approved_by_you'], $counts['approved_by_you']),
            'rejected_by_you' => $this->calculatePercentageChange($lastMonthCounts['rejected_by_you'], $counts['rejected_by_you']),
            'reverted_by_you' => $this->calculatePercentageChange($lastMonthCounts['reverted_by_you'], $counts['reverted_by_you']),
        ];
$kpi_trends = array_merge($this->getDefaultKpiTrends(), $kpi_trends);
        $statuses = Status::where('is_active', 1)->orderBy('sort_order')->get();
        return compact(
            'approverPendingApplications',
            'bu_list',
            'zone_list',
            'region_list',
            'territory_list',
            'counts',
            'kpi_trends',
            'statuses'
        );
    }

    protected function getDashboardData(array $filters, $user, $access_level, Request $request, $statusGroups, $kpiStatusMappings)
    {
        $bu_list = $this->helper->getAssociatedBusinessUnitList($user->emp_id);
        $zone_list = $this->helper->getAssociatedZoneList($user->emp_id);
        $region_list = $this->helper->getAssociatedRegionList($user->emp_id);
        $territory_list = $this->helper->getAssociatedTerritoryList($user->emp_id);

        // Use dynamic groups
        $approvalSlugs = explode(',', $statusGroups['actionable']['slugs']);
        $misSlugs = explode(',', $statusGroups['mis']['slugs']);
        $completionSlugs = explode(',', $statusGroups['completed']['slugs']);
        $rejectionSlugs = explode(',', $statusGroups['rejected']['slugs']);
        $revertedSlugs = explode(',', $statusGroups['reverted']['slugs']);
        $holdSlugs = explode(',', $statusGroups['hold']['slugs']);
        $approvedSlugs = explode(',', $statusGroups['approved']['slugs']);

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

        // Apply filters
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
        if ($filters['date_from'] && $filters['date_to']) {
            $masterDashboardBaseQuery->whereBetween('onboardings.created_at', [$filters['date_from'], $filters['date_to']]);
        }

        $pendingApplicationsQuery = Onboarding::query()
            ->with(['entityDetails', 'createdBy', 'currentApprover', 'territoryDetail', 'regionDetail', 'zoneDetail'])
            ->where('onboardings.current_approver_id', $user->emp_id)
            ->whereIn('onboardings.status', $approvalSlugs);

        // Apply filters to pending
        if ($filters['territory'] && $filters['territory'] != 'All') {
            $pendingApplicationsQuery->where('onboardings.territory', $filters['territory']);
        }
        if ($filters['region'] && $filters['region'] != 'All') {
            $pendingApplicationsQuery->where('onboardings.region', $filters['region']);
        }
        if ($filters['zone'] && $filters['zone'] != 'All') {
            $pendingApplicationsQuery->where('onboardings.zone', $filters['zone']);
        }
        if ($filters['bu'] && $filters['bu'] != 'All') {
            $pendingApplicationsQuery->where('onboardings.business_unit', $filters['bu']);
        }
        if ($filters['date_from'] && $filters['date_to']) {
            $pendingApplicationsQuery->whereBetween('onboardings.created_at', [$filters['date_from'], $filters['date_to']]);
        }

        $pendingApplications = $pendingApplicationsQuery->paginate(10);

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

        // Apply filters to my
        if ($filters['territory'] && $filters['territory'] != 'All') {
            $myApplicationsQuery->where('onboardings.territory', $filters['territory']);
        }
        if ($filters['region'] && $filters['region'] != 'All') {
            $myApplicationsQuery->where('onboardings.region', $filters['region']);
        }
        if ($filters['zone'] && $filters['zone'] != 'All') {
            $myApplicationsQuery->where('onboardings.zone', $filters['zone']);
        }
        if ($filters['bu'] && $filters['bu'] != 'All') {
            $myApplicationsQuery->where('onboardings.business_unit', $filters['bu']);
        }
        if ($filters['date_from'] && $filters['date_to']) {
            $myApplicationsQuery->whereBetween('onboardings.created_at', [$filters['date_from'], $filters['date_to']]);
        }

        $myApplications = $myApplicationsQuery->paginate(10);

        $misApplications = new LengthAwarePaginator([], 0, 10);
        if ($employee_details && $employee_details->isMisTeam()) {
            $allSlugs = array_unique(array_merge($misSlugs, $completionSlugs, $rejectionSlugs, $approvalSlugs));

            $misApplicationsQuery = Onboarding::query()
                ->with(['entityDetails', 'createdBy', 'currentApprover', 'territoryDetail', 'regionDetail', 'zoneDetail', 'approvalLogs'])
                ->whereIn('onboardings.status', $allSlugs);

            // Apply filters to mis
            if ($filters['territory'] && $filters['territory'] != 'All') {
                $misApplicationsQuery->where('onboardings.territory', $filters['territory']);
            }
            if ($filters['region'] && $filters['region'] != 'All') {
                $misApplicationsQuery->where('onboardings.region', $filters['region']);
            }
            if ($filters['zone'] && $filters['zone'] != 'All') {
                $misApplicationsQuery->where('onboardings.zone', $filters['zone']);
            }
            if ($filters['bu'] && $filters['bu'] != 'All') {
                $misApplicationsQuery->where('onboardings.business_unit', $filters['bu']);
            }
            if ($filters['date_from'] && $filters['date_to']) {
                $misApplicationsQuery->whereBetween('onboardings.created_at', [$filters['date_from'], $filters['date_to']]);
            }

            $misApplications = $misApplicationsQuery->paginate(10);
        }

        $masterReportApplications = $masterDashboardBaseQuery->paginate(10);

        // Dynamic counts for admin
        $filteredApplicationIds = $masterDashboardBaseQuery->pluck('onboardings.id')->toArray();
        $counts = [
            'total' => (clone $masterDashboardBaseQuery)->count(),
            'pending' => (clone $pendingApplicationsQuery)->count(),
            'my' => (clone $myApplicationsQuery)->count(),
            'mis' => ($employee_details && $employee_details->isMisTeam()) ? (clone $misApplicationsQuery)->count() : 0,
            'approved' => (clone $masterDashboardBaseQuery)->whereIn('onboardings.status', $approvedSlugs)->count(),
            'in_process' => (clone $masterDashboardBaseQuery)->whereIn('onboardings.status', array_merge($approvalSlugs, $misSlugs))->count(),
            'rejected' => (clone $masterDashboardBaseQuery)->whereIn('onboardings.status', $rejectionSlugs)->count(),
            'reverted' => (clone $masterDashboardBaseQuery)->whereIn('onboardings.status', $revertedSlugs)->count(),
            'pending_rbm' => (clone $masterDashboardBaseQuery)->where('onboardings.approval_level', 'Regional Business Manager')->whereIn('onboardings.status', $approvalSlugs)->count(),
            'pending_zbm' => (clone $masterDashboardBaseQuery)->where('onboardings.approval_level', 'Zonal Business Manager')->whereIn('onboardings.status', $approvalSlugs)->count(),
            'pending_gm' => (clone $masterDashboardBaseQuery)->where('onboardings.approval_level', 'General Manager')->whereIn('onboardings.status', $approvalSlugs)->count(),
            'forwarded_to_mis' => (clone $masterDashboardBaseQuery)->whereIn('onboardings.status', $misSlugs)->count(),
            'distributorship_created' => (clone $masterDashboardBaseQuery)->whereIn('onboardings.status', $completionSlugs)->count(),
            'on_hold_by_you' => ApprovalLog::where('user_id', $user->emp_id)->where('action', 'on_hold')
                ->whereIn('application_id', $filteredApplicationIds)->count(),
            'approved_by_you' => ApprovalLog::where('user_id', $user->emp_id)->where('action', 'approved')
                ->whereIn('application_id', $filteredApplicationIds)->count(),
            'rejected_by_you' => ApprovalLog::where('user_id', $user->emp_id)->where('action', 'rejected')
                ->whereIn('application_id', $filteredApplicationIds)->count(),
            'reverted_by_you' => ApprovalLog::where('user_id', $user->emp_id)->where('action', 'reverted')
                ->whereIn('application_id', $filteredApplicationIds)->count(),
            'doc_verification_pending' => (clone $masterDashboardBaseQuery)->whereIn('onboardings.status', [$kpiStatusMappings['document_verified'] ?? 'mis_processing'])->count(),
            'agreements_created' => (clone $masterDashboardBaseQuery)->whereIn('onboardings.status', [$kpiStatusMappings['agreement_created'] ?? 'agreement_created'])->count(),
            'physical_docs_pending' => (clone $masterDashboardBaseQuery)
                ->whereIn('onboardings.status', [$kpiStatusMappings['physical_docs_verified'] ?? 'documents_received'])
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('physical_document_checks')
                        ->whereColumn('physical_document_checks.application_id', 'onboardings.id')
                        ->where('physical_document_checks.status', 'verified');
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

        // Apply last month filters
        if ($lastMonthFilters['territory'] && $lastMonthFilters['territory'] != 'All') {
            $lastMonthMasterDashboardBaseQuery->where('onboardings.territory', $lastMonthFilters['territory']);
        }
        if ($lastMonthFilters['region'] && $lastMonthFilters['region'] != 'All') {
            $lastMonthMasterDashboardBaseQuery->where('rtm.region_id', $lastMonthFilters['region']);
        }
        if ($lastMonthFilters['zone'] && $lastMonthFilters['zone'] != 'All') {
            $lastMonthMasterDashboardBaseQuery->where('zrm.zone_id', $lastMonthFilters['zone']);
        }
        if ($lastMonthFilters['bu'] && $lastMonthFilters['bu'] != 'All') {
            $lastMonthMasterDashboardBaseQuery->where('onboardings.business_unit', $lastMonthFilters['bu']);
        }
        $lastMonthMasterDashboardBaseQuery->whereBetween('onboardings.created_at', [$lastMonthFilters['date_from'], $lastMonthFilters['date_to']]);

        $lastMonthCounts = [
            'total' => (clone $lastMonthMasterDashboardBaseQuery)->count(),
            'distributorship_created' => (clone $lastMonthMasterDashboardBaseQuery)->whereIn('onboardings.status', $completionSlugs)->count(),
            'reverted' => (clone $lastMonthMasterDashboardBaseQuery)->whereIn('onboardings.status', $revertedSlugs)->count(),
            'rejected' => (clone $lastMonthMasterDashboardBaseQuery)->whereIn('onboardings.status', $rejectionSlugs)->count(),
            'forwarded_to_mis' => (clone $lastMonthMasterDashboardBaseQuery)->whereIn('onboardings.status', $misSlugs)->count(),
        ];

        $kpi_trends = [
            'total_submitted' => $this->calculatePercentageChange($lastMonthCounts['total'], $counts['total']),
            'appointments_completed' => $this->calculatePercentageChange($lastMonthCounts['distributorship_created'], $counts['distributorship_created']),
            'reverted' => $this->calculatePercentageChange($lastMonthCounts['reverted'], $counts['reverted']),
            'rejected' => $this->calculatePercentageChange($lastMonthCounts['rejected'], $counts['rejected']),
            'forwarded_to_mis' => $this->calculatePercentageChange($lastMonthCounts['forwarded_to_mis'], $counts['forwarded_to_mis']),
        ];

        $tatData = $this->calculateTAT(clone $masterDashboardBaseQuery, clone $masterDashboardBaseQuery, $statusGroups);

        $actionSummary = [];
        $userActions = [];
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
        $statuses = Status::where('is_active', 1)->orderBy('sort_order')->get();
        return compact(
            'pendingApplications',
            'myApplications',
            'misApplications',
            'masterReportApplications',
            'actionSummary',
            'counts',
            'tatData',
            'kpi_trends',
            'bu_list',
            'zone_list',
            'region_list',
            'territory_list',
            'access_level',
            'filters',
            'statuses'
        );
    }

    protected function calculatePercentageChange($oldValue, $newValue)
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }
        return round(($newValue - $oldValue) / $oldValue * 100, 1);
    }

    protected function calculateMisTAT($baseQuery, $statusGroups)
    {
        $misSlugs = explode(',', $statusGroups['mis']['slugs']);
        $completionSlugs = explode(',', $statusGroups['completed']['slugs']);

        $q = (clone $baseQuery)->whereIn('onboardings.status', $completionSlugs)
            ->select('onboardings.id', 'onboardings.created_at', 'onboardings.updated_at')
            ->whereExists(function ($subQ) use ($misSlugs) {
                $subQ->select(DB::raw(1))
                    ->from('approval_logs')
                    ->whereColumn('approval_logs.application_id', 'onboardings.id')
                    ->whereIn('approval_logs.role', $misSlugs);
            });

        $applications = $q->get();

        $durations = [];
        foreach ($applications as $app) {
            if ($app->created_at && $app->updated_at) {
                $misStart = Carbon::parse($app->created_at);
                $finalDate = Carbon::parse($app->updated_at);
                $durations[] = $misStart->diffInDays($finalDate);
            }
        }

        $durationsCollection = collect($durations);
        return [
            'total' => [
                'avg_tat' => $durationsCollection->isNotEmpty() ? round($durationsCollection->avg(), 1) : 0,
                'max_tat' => $durationsCollection->isNotEmpty() ? $durationsCollection->max() : 0,
                'exceeding_sla' => $durationsCollection->filter(fn($days) => $days > 5)->count(),
            ]
        ];
    }

    protected function calculateTAT($baseQueryForTat, $masterReportQueryForTat, $statusGroups)
    {
        // Dynamic TAT stages using groups
        $tatStages = [
            'submission_to_rbm' => [
                'from_status' => $this->getStatusesByCategory('draft'),
                'to_status' => explode(',', $statusGroups['pending']['slugs']),
                'sla' => 3
            ],
            'rbm_to_zbm' => [
                'from_status' => $this->getStatusesByCategory('approval', ['under_level1_review']),
                'to_status' => $this->getStatusesByCategory('approval', ['under_level2_review']),
                'sla' => 3
            ],
            'zbm_to_gm' => [
                'from_status' => $this->getStatusesByCategory('approval', ['under_level2_review']),
                'to_status' => $this->getStatusesByCategory('approval', ['under_level3_review']),
                'sla' => 3
            ],
            'gm_to_mis' => [
                'from_status' => explode(',', $statusGroups['approved']['slugs']),
                'to_status' => explode(',', $statusGroups['mis']['slugs']),
                'sla' => 2
            ],
            'mis_to_final' => [
                'from_status' => explode(',', $statusGroups['mis']['slugs']),
                'to_status' => explode(',', $statusGroups['completed']['slugs']),
                'sla' => 5
            ],
        ];

        $tatResults = [];

        foreach ($tatStages as $stageKey => $stageInfo) {
            // Get applications that moved through this stage by checking status transitions
            $q = (clone $baseQueryForTat);
            $q->select(
                'onboardings.id',
                'onboardings.created_at',
                DB::raw('MIN(from_log.created_at) as from_date'),
                DB::raw('MIN(to_log.created_at) as to_date')
            )
                ->join('approval_logs as from_log', function ($join) use ($stageInfo) {
                    $join->on('onboardings.id', '=', 'from_log.application_id')
                        ->where('from_log.action', 'submitted') // or appropriate action
                        ->whereNotNull('from_log.created_at');
                })
                ->join('approval_logs as to_log', function ($join) use ($stageInfo) {
                    $join->on('onboardings.id', '=', 'to_log.application_id')
                        ->where('to_log.action', 'approved')
                        ->whereNotNull('to_log.created_at')
                        ->whereRaw('to_log.created_at > from_log.created_at');
                })
                ->where(function ($query) use ($stageInfo) {
                    // Check if application passed through these statuses
                    foreach ($stageInfo['from_status'] as $fromStatus) {
                        $query->orWhere('onboardings.status', 'like', "%{$fromStatus}%");
                    }
                })
                ->groupBy('onboardings.id', 'onboardings.created_at');

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
                'exceeding_sla' => $durationsCollection->filter(fn($days) => $days > $stageInfo['sla'])->count(),
            ];
        }

        // Alternative approach: Calculate TAT based on status change timestamps from approval_logs
        $tatResults = $this->calculateTATFromLogs($baseQueryForTat, $tatStages, $statusGroups);

        // Total TAT calculation
        $completionSlugs = explode(',', $statusGroups['completed']['slugs']);
        $totalApplicationsQuery = (clone $masterReportQueryForTat)
            ->whereIn('onboardings.status', $completionSlugs)
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
            'exceeding_sla' => $totalDurationsCollection->filter(fn($days) => $days > 12)->count(),
        ];

        return $tatResults;
    }

    // Alternative method using approval_logs actions and timestamps
    protected function calculateTATFromLogs($baseQueryForTat, $tatStages, $statusGroups)
    {
        $tatResults = [];

        foreach ($tatStages as $stageKey => $stageInfo) {
            // Get the first log entry for from_status and first log entry for to_status
            $q = (clone $baseQueryForTat);
            $q->select(
                'onboardings.id',
                DB::raw('(SELECT MIN(created_at) FROM approval_logs WHERE application_id = onboardings.id AND action IN ("submitted", "approved") LIMIT 1) as from_date'),
                DB::raw('(SELECT MIN(created_at) FROM approval_logs WHERE application_id = onboardings.id AND action = "approved" AND created_at > (SELECT MIN(created_at) FROM approval_logs WHERE application_id = onboardings.id AND action IN ("submitted", "approved") LIMIT 1) LIMIT 1) as to_date')
            )
                ->whereIn('onboardings.status', $stageInfo['to_status']) // Applications that reached the target status
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
                'exceeding_sla' => $durationsCollection->filter(fn($days) => $days > $stageInfo['sla'])->count(),
            ];
        }

        return $tatResults;
    }

    // Helper method to get status transitions based on approval logs
    protected function getStatusTransitionTimes($applicationId, $fromActions, $toActions)
    {
        $fromLog = DB::table('approval_logs')
            ->where('application_id', $applicationId)
            ->whereIn('action', $fromActions)
            ->orderBy('created_at', 'asc')
            ->first();

        $toLog = DB::table('approval_logs')
            ->where('application_id', $applicationId)
            ->whereIn('action', $toActions)
            ->where('created_at', '>', $fromLog ? $fromLog->created_at : now())
            ->orderBy('created_at', 'asc')
            ->first();

        return [
            'from_date' => $fromLog->created_at ?? null,
            'to_date' => $toLog->created_at ?? null
        ];
    }
    protected function getDefaultCounts()
    {
        return [
            'pending' => 0,
            'my' => 0,
            'mis' => 0,
            'total' => 0,
            'approved' => 0,
            'in_process' => 0,
            'rejected' => 0,
            'reverted' => 0,
            'pending_rbm' => 0,
            'pending_zbm' => 0,
            'pending_gm' => 0,
            'forwarded_to_mis' => 0,
            'distributorship_created' => 0,
            'on_hold_by_you' => 0,
            'approved_by_you' => 0,
            'rejected_by_you' => 0,
            'reverted_by_you' => 0,
            'doc_verification_pending' => 0,
            'agreements_created' => 0,
            'physical_docs_pending' => 0,
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
        ];
    }

    protected function getDefaultSalesKpiTrends()
    {
        return [
            'total_created' => 0,
            'in_approval' => 0,
            'to_mis' => 0,
            'completed' => 0,
            'rejected' => 0,
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
