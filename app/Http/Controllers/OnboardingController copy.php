<?php

namespace App\Http\Controllers;

use App\Models\Onboarding;
use App\Models\EntityDetails;
use App\Models\DistributionDetail;
use App\Models\BankDetail;
use App\Models\FinancialInfo;
use App\Models\ExistingDistributorship;
use App\Models\Document;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Models\Year;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\ApplicationSubmitted;
use App\Models\ApplicationAdditionalDocument;
use App\Models\ApplicationCheckpoint;
use App\Mail\DocumentResubmission;


class OnboardingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->hasAnyRole(['Admin', 'Super Admin', 'Mis Admin'])) {
            $applications = Onboarding::orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            $applications = Onboarding::where('created_by', $user->emp_id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }
        // Fetch territories for filter dropdown
        $territories = DB::table('core_territory')->select('id', 'territory_name')->orderBy('territory_name')->get();
        // Define possible statuses
        $statuses = ['draft', 'initiated', 'mis_processing', 'approved', 'rejected', 'reverted'];
        return view('applications.index', compact('applications', 'territories', 'statuses'));
    }

    public function datatable(Request $request)
    {
        try {

            $user = Auth::user();
            $query = Onboarding::query()->with(['entityDetails', 'territoryDetail']);

            // Apply role-based filtering
            if (!$user->hasAnyRole(['Admin', 'Super Admin', 'Mis Admin'])) {
                $query->where('created_by', $user->emp_id);
            }

            // Apply default DataTable-like search across visible columns
            if ($request->has('search') && !empty($request->input('search.value'))) {
                $search = trim($request->input('search.value'));
                $query->where(function ($q) use ($search) {
                    // Search application_code
                    $q->where('application_code', 'like', "%{$search}%")
                        // Search distributor (entity_details.establishment_name)
                        ->orWhereHas('entityDetails', function ($q) use ($search) {
                            $q->where('establishment_name', 'like', "%{$search}%");
                        })
                        // Search territory (core_territory.territory_name)
                        ->orWhereHas('territoryDetail', function ($q) use ($search) {
                            $q->where('territory_name', 'like', "%{$search}%");
                        })
                        // Search status
                        ->orWhere('status', 'like', "%{$search}%")
                        // Search created_at (formatted as d-M-Y)
                        ->orWhereRaw("DATE_FORMAT(created_at, '%d-%b-%Y') LIKE ?", ["%{$search}%"]);
                });
            }

            // Apply filters only if non-null and non-empty
            $territory = $request->input('territory');
            if ($territory !== null && $territory !== '') {
                $query->where('territory', $territory);
            }

            $status = $request->input('status');
            if ($status !== null && $status !== '') {
                $query->where('status', $status);
            }

            // Apply sorting
            if ($request->has('order')) {
                $orderColumnIndex = $request->input('order.0.column');
                $orderDir = $request->input('order.0.dir');
                $columns = $request->input('columns');
                $orderColumn = $columns[$orderColumnIndex]['data'];

                $columnMap = [
                    'application_code' => 'application_code',
                    'distributor' => 'entity_details.establishment_name',
                    'territory' => 'core_territory.territory_name',
                    'status' => 'status',
                    'created_at' => 'created_at',
                ];

                if (isset($columnMap[$orderColumn])) {
                    if ($orderColumn === 'distributor') {
                        $query->leftJoin('entity_details', 'onboardings.id', '=', 'entity_details.onboarding_id')
                            ->orderBy('entity_details.establishment_name', $orderDir);
                    } elseif ($orderColumn === 'territory') {
                        $query->leftJoin('core_territory', 'onboardings.territory', '=', 'core_territory.id')
                            ->orderBy('core_territory.territory_name', $orderDir);
                    } else {
                        $query->orderBy($columnMap[$orderColumn], $orderDir);
                    }
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Get total records before filtering
            $totalRecords = Onboarding::count();
            // Get filtered records count
            $totalFiltered = $query->count();
            // Apply pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $applications = $query->skip($start)->take($length)->get();
            // Prepare data for DataTables
            $data = [];
            foreach ($applications as $index => $application) {
                $data[] = [
                    's_no' => $start + $index + 1,
                    'application_code' => $application->application_code ?? 'N/A',
                    'distributor' => $application->entityDetails ? ($application->entityDetails->establishment_name ?? 'N/A') : 'N/A',
                    'territory' => $application->territoryDetail ? ($application->territoryDetail->territory_name ?? 'N/A') : 'N/A',
                    'status' => '<span class="badge bg-' . ($application->status_badge ?? 'secondary') . '" style="font-size: 0.65rem;">' . ucfirst($application->status ?? 'unknown') . '</span>',
                    'created_at' => $application->created_at ? $application->created_at->format('d-M-Y') : 'N/A',
                    'actions' => $this->getActions($application),
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred while fetching data: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getActions($application)
    {
        $user = Auth::user();
        $actions = '<div class="d-flex justify-content-center" style="gap: 0.25rem;">';
        $actions .= '<a href="' . route('applications.show', $application->id) . '" class="btn btn-info btn-action p-0" title="View"><i class="bx bx-show fs-10 d-flex justify-content-center align-items-center"></i></a>';

        if (in_array($application->status, ['draft', 'reverted']) && ($application->created_by === $user->emp_id || $user->hasAnyRole(['Admin', 'Super Admin', 'Mis Admin']))) {
            $actions .= '<a href="' . route('applications.edit', $application->id) . '" class="btn btn-info btn-action p-0" title="Edit"><i class="bx bx-pencil fs-10 d-flex justify-content-center align-items-center"></i></a>';
        }

        if ($application->status === 'draft' && ($application->created_by === $user->emp_id || $user->hasAnyRole(['Admin', 'Super Admin', 'Mis Admin']))) {
            $actions .= '<form action="' . route('applications.destroy', $application->id) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this application?\');">' .
                csrf_field() .
                method_field('DELETE') .
                '<button type="submit" class="btn btn-danger btn-action p-0" title="Delete"><i class="bx bx-trash fs-10 d-flex justify-content-center align-items-center"></i></button>' .
                '</form>';
        }

        $actions .= '</div>';
        return $actions;
    }
    function getAssociatedBusinessUnitList($employeeId)
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Mis User', 'Mis Admin'])) {
            return DB::table('core_business_unit')
                ->where('is_active', '1')
                ->where('business_type', '1')
                ->pluck('business_unit_name', 'id')
                ->prepend('All BU', 'All')->toArray();
        }

        $buId = DB::table('core_employee')
            ->where('id', $employeeId)
            ->where('zone', 0)
            ->value('bu');
        //dd($employeeId);

        if ($buId > 0) {
            return DB::table('core_business_unit')
                ->where('id', $buId)
                ->where('is_active', '1')
                ->where('business_type', '1')
                ->pluck('business_unit_name', 'id')
                ->prepend('Select BU', '')->toArray();;
        }

        return [];
    }

    public function create(Request $request, $application_id = null, $step = 1)
    {
        $user = Auth::user();
        $territory_list = [];
        $zone_list = [];
        $region_list = [];
        $preselected = [];
        $bu_list = [];
        $hasAddDistributorPermission = $user->hasAnyRole(['Mis User', 'Admin', 'Super Admin', 'Mis Admin']);
        $crop_type = [
            '1' => 'Field Crop',
            '2' => 'Veg Crop',
            '3' => 'Root Stock',
            '4' => 'Fruit Crop',
            '5' => 'Common'
        ];

        $states = DB::table('core_state')
            ->where('is_active', 1)
            ->orderBy('state_name')
            ->get();

        // Retrieve application_id from URL or request
        $application_id = $application_id ?? $request->input('application_id');
        try {
            $application = $application_id ? Onboarding::with([
                'territoryDetail',
                'regionDetail',
                'zoneDetail',
                'businessUnit',
                'entityDetails',
                'distributionDetail',
                'businessPlans',
                'financialInfo',
                'existingDistributorships',
                'bankDetail',
                'declarations'
            ])->findOrFail($application_id) : new Onboarding();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('applications.create', ['step' => 1])
                ->with('error', 'Application not found. Please start a new application.');
        }



        // Enforce step 1 for new applications
        if (!$application_id && $step != 1) {
            return redirect()->route('applications.create', ['step' => 1])
                ->with('error', 'Please start from Basic Details.');
        }

        // Validate step progression
        if ($application_id && $step > 1) {
            $requiredSteps = [
                2 => !$application->territory,
                3 => !$application->entityDetails,
                4 => !$application->distributionDetail,
                5 => !$application->businessPlans->count(),
                6 => !$application->financialInfo,
                7 => !$application->bankDetail,
                8 => !$application->declarations->count()
            ];
            for ($i = 2; $i <= $step; $i++) {
                $frontendStep = $i;
                $backendRelationship = match ($frontendStep) {
                    2 => 'entityDetails',
                    3 => 'distributionDetail',
                    4 => 'businessPlans',
                    5 => 'financialInfo',
                    6 => 'bankDetail',
                    7 => 'declarations',
                    default => null,
                };

                if ($backendRelationship === 'businessPlans' || $backendRelationship === 'declarations') {
                    if ($application->$backendRelationship->isEmpty()) {
                        return redirect()->route('applications.create', ['application_id' => $application_id, 'step' => $frontendStep - 1])
                            ->with('error', 'Please complete all previous steps.');
                    }
                } elseif ($backendRelationship && !$application->$backendRelationship) {

                    return redirect()->route('applications.create', ['application_id' => $application_id, 'step' => $frontendStep - 1])
                        ->with('error', 'Please complete all previous steps.');
                }
            }
        }

        if ($user->emp_id) {
            $employee = DB::table('core_employee')->where('employee_id', $user->emp_id)->first();

            if ($employee) {
                $bu_list = $this->getAssociatedBusinessUnitList($user->emp_id);
                if ($employee->bu > 0) {
                    $preselected['bu'] = $employee->bu;
                }
                $vertical_list = DB::table('core_vertical')
                    ->pluck('vertical_name', 'id')
                    ->toArray();

                if ($hasAddDistributorPermission) {
                    $territory_list = DB::table('core_territory')
                        ->where('is_active', 1)
                        ->pluck('territory_name', 'id')
                        ->toArray();
                } else {
                    if ($employee->territory == 0 && $employee->region == 0 && $employee->zone == 0 && $employee->bu > 0) {
                        $mapping = DB::select("
                        SELECT 
                            bzm.zone_id,
                            z.zone_name,
                            zrm.region_id,
                            r.region_name,
                            rtm.territory_id,
                            t.territory_name
                        FROM 
                            core_bu_zone_mapping bzm
                        INNER JOIN 
                            core_zone z ON bzm.zone_id = z.id
                        INNER JOIN 
                            core_zone_region_mapping zrm ON bzm.zone_id = zrm.zone_id
                        INNER JOIN 
                            core_region r ON zrm.region_id = r.id
                        LEFT JOIN 
                            core_region_territory_mapping rtm ON zrm.region_id = rtm.region_id
                        LEFT JOIN 
                            core_territory t ON rtm.territory_id = t.id
                        WHERE 
                            bzm.business_unit_id = ?
                    ", [$employee->bu]);

                        $zone_list = collect($mapping)
                            ->pluck('zone_name', 'zone_id')
                            ->unique()
                            ->filter()
                            ->toArray();

                        $region_list = collect($mapping)
                            ->pluck('region_name', 'region_id')
                            ->unique()
                            ->filter()
                            ->toArray();

                        $territory_list = collect($mapping)
                            ->pluck('territory_name', 'territory_id')
                            ->unique()
                            ->filter()
                            ->toArray();

                        if (count($zone_list) === 1) {
                            $preselected['zone'] = array_key_first($zone_list);
                        }
                        if (count($region_list) === 1) {
                            $preselected['region'] = array_key_first($region_list);
                        }
                        if (count($territory_list) === 1) {
                            $preselected['territory'] = array_key_first($territory_list);
                        }
                    } elseif ($employee->territory == 0 && $employee->region == 0 && $employee->zone > 0) {
                        $mapping = DB::select("
                        SELECT 
                            zrm.zone_id,
                            z.zone_name,
                            zrm.region_id,
                            r.region_name,
                            rtm.territory_id,
                            t.territory_name
                        FROM 
                            core_zone_region_mapping zrm
                        INNER JOIN 
                            core_zone z ON zrm.zone_id = z.id
                        INNER JOIN 
                            core_region r ON zrm.region_id = r.id
                        LEFT JOIN 
                            core_region_territory_mapping rtm ON zrm.region_id = rtm.region_id
                        LEFT JOIN 
                            core_territory t ON rtm.territory_id = t.id
                        WHERE 
                            zrm.zone_id = ?
                    ", [$employee->zone]);

                        $territory_list = collect($mapping)
                            ->pluck('territory_name', 'territory_id')
                            ->unique()
                            ->filter()
                            ->toArray();

                        if (count($territory_list) === 1) {
                            $preselected['territory'] = array_key_first($territory_list);
                        }
                    } elseif ($employee->territory == 0 && $employee->region > 0) {
                        $mapping = DB::select("
                        SELECT 
                            r.id as region_id,
                            r.region_name,
                            zrm.zone_id,
                            z.zone_name,
                            rtm.territory_id,
                            t.territory_name
                        FROM 
                            core_region r
                        LEFT JOIN 
                            core_zone_region_mapping zrm ON r.id = zrm.region_id
                        LEFT JOIN 
                            core_zone z ON zrm.zone_id = z.id
                        LEFT JOIN 
                            core_region_territory_mapping rtm ON r.id = rtm.region_id
                        LEFT JOIN 
                            core_territory t ON rtm.territory_id = t.id
                        WHERE 
                            r.id = ?
                    ", [$employee->region]);

                        $territory_list = collect($mapping)
                            ->pluck('territory_name', 'territory_id')
                            ->unique()
                            ->filter()
                            ->toArray();

                        if (count($territory_list) === 1) {
                            $preselected['territory'] = array_key_first($territory_list);
                        }
                    } elseif ($employee->territory > 0) {
                        $territory = DB::table('core_territory')
                            ->where('id', $employee->territory)
                            ->first();

                        if ($territory) {
                            $territory_list = [$territory->id => $territory->territory_name];
                            $preselected['territory'] = $territory->id;
                        }
                    }
                }

                if (isset($preselected['territory'])) {
                    $territoryData = $this->getTerritoryData($preselected['territory']);
                    $region_list = $territoryData['regions'] ?? [];
                    $zone_list = $territoryData['zones'] ?? [];
                    $bu_list = $territoryData['businessUnits'] ?? [];
                    $vertical_list = $territoryData['verticals'] ?? [];

                    if (!empty($region_list)) {
                        $preselected['region'] = array_key_first($region_list);
                    }
                    if (!empty($zone_list)) {
                        $preselected['zone'] = array_key_first($zone_list);
                    }
                    if (!empty($bu_list)) {
                        $preselected['bu'] = array_key_first($bu_list);
                    }
                    if (!empty($vertical_list)) {
                        $preselected['crop_vertical'] = array_key_first($vertical_list);
                    }
                }


                $verticalId = array_key_first($vertical_list);
                $preselected['crop_vertical'] = (string) $verticalId;

                $cropsQuery = DB::table('core_crop')
                    ->where('is_active', 1)
                    ->select('id', 'crop_name')
                    ->orderBy('crop_name');

                if ($verticalId != 5) {
                    $cropsQuery->where('vertical_id', $verticalId);
                }

                $crops = $cropsQuery->get();
            }

            $currentYear = now()->month >= 4 ? now()->year . '-' . (now()->year + 1)
                : (now()->year - 1) . '-' . now()->year;

            $financialYears = Year::where('status', 'active')
                ->where('period', '<', $currentYear)
                ->orderBy('start_year', 'desc')
                ->take(3)
                ->get();

            // Define completedStepsData for create view
            $completedStepsData = [
                1 => !empty($application->territory) && !empty($application->crop_vertical) && !empty($application->region) && !empty($application->zone) && !empty($application->business_unit),
                2 => !empty($application->entityDetails) && !empty($application->entityDetails->establishment_name) && !empty($application->entityDetails->pan_number),
                3 => !empty($application->distributionDetail) && !empty($application->distributionDetail->area_covered) && is_array(json_decode($application->distributionDetail->area_covered, true)) && count(json_decode($application->distributionDetail->area_covered, true)) > 0,
                4 => $application->businessPlans->isNotEmpty(),
                5 => !empty($application->financialInfo) && !empty($application->financialInfo->net_worth),
                6 => !empty($application->bankDetail) && !empty($application->bankDetail->bank_name) && !empty($application->bankDetail->account_number),
                7 => $application->declarations->isNotEmpty(),
                8 => in_array($application->status, ['initiated', 'approved']),
            ];

            $currentStep = $application && $application->current_progress_step ? $application->current_progress_step : $step;
            //dd($preselected['zone']);
            return view('applications.create', compact(
                'application',
                'application_id',
                'bu_list',
                'zone_list',
                'region_list',
                'territory_list',
                'preselected',
                'crop_type',
                'states',
                'currentStep',
                'crops',
                'financialYears',
                'completedStepsData'
            ));
        }
    }

    private function getTerritoryData($territoryId)
    {
        $result = DB::select("
        SELECT 
            rtm.region_id,
            r.region_name,
            zrm.zone_id,
            z.zone_name,
            bzm.business_unit_id,
            b.business_unit_name,
            v.id as vertical_id,
            v.vertical_name
        FROM 
            core_region_territory_mapping rtm
        JOIN 
            core_region r ON rtm.region_id = r.id
        LEFT JOIN 
            core_zone_region_mapping zrm ON r.id = zrm.region_id
        LEFT JOIN 
            core_zone z ON zrm.zone_id = z.id
        LEFT JOIN 
            core_bu_zone_mapping bzm ON z.id = bzm.zone_id
        LEFT JOIN 
            core_business_unit b ON bzm.business_unit_id = b.id
        LEFT JOIN 
            core_vertical v ON r.vertical_id = v.id
        WHERE 
            rtm.territory_id = ?
    ", [$territoryId]);

        $data = [
            'regions' => [],
            'zones' => [],
            'businessUnits' => [],
            'verticals' => []
        ];

        foreach ($result as $row) {
            if ($row->region_id && !array_key_exists($row->region_id, $data['regions'])) {
                $data['regions'][$row->region_id] = $row->region_name;
            }
            if ($row->zone_id && !array_key_exists($row->zone_id, $data['zones'])) {
                $data['zones'][$row->zone_id] = $row->zone_name;
            }
            if ($row->business_unit_id && !array_key_exists($row->business_unit_id, $data['businessUnits'])) {
                $data['businessUnits'][$row->business_unit_id] = $row->business_unit_name;
            }
            if ($row->vertical_id && !array_key_exists($row->vertical_id, $data['verticals'])) {
                $data['verticals'][$row->vertical_id] = $row->vertical_name;
            }
        }

        return $data;
    }

    public function show(Onboarding $application)
    {
        // $this->authorize('view', $application);

        $application->load([
            'entityDetails',
            'distributionDetail',
            'bankDetail',
            'businessPlans', // Changed from businessPlan to businessPlans
            'financialInfo',
            'existingDistributorships',
            'declarations',
            'approvalLogs.user',
        ]);
        //dd($application->entityDetails->documents_data);
        // Pass additional data (e.g., territory_list, region_list) if needed
        return view('applications.show', compact('application'));
    }

    public function edit(Onboarding $application, $step = 1)
    {
        $user = Auth::user();
        // Manual authorization: Check if user's emp_id matches application's created_by
        if (
            !$user->emp_id || ($user->emp_id !== $application->created_by
                && !$user->hasAnyRole(['Admin', 'Mis Admin', 'Super Admin']))
        ) {
            abort(403, 'You are not authorized to edit this application.');
        }

        if (!in_array($application->status, ['draft', 'reverted'])) {
            return redirect()->route('applications.show', $application)
                ->with('error', 'You can only edit draft or reverted applications');
        }

        $initialFrontendStep = $application->current_progress_step ?? 1;
        if ($step && $step <= $initialFrontendStep) {
            $initialFrontendStep = (int) $step; // Cast to int to be safe
        }
        $totalSteps = 8; // Define your total number of steps
        if ($initialFrontendStep > $totalSteps) {
            $initialFrontendStep = $totalSteps;
        }

        // Ensure all relationships are loaded for editing
        $application->load([
            'territoryDetail',
            'regionDetail',
            'zoneDetail',
            'businessUnit',
            'entityDetails',
            'distributionDetail',
            'businessPlans',
            'financialInfo',
            'existingDistributorships',
            'bankDetail',
            'declarations'
        ]);

        $territory_list = [];
        $zone_list = [];
        $region_list = [];
        $bu_list = [];
        $preselected = [];
        $hasAddDistributorPermission = $user->hasAnyRole(['Mis User', 'Admin', 'Super Admin', 'Mis Admin']);


        if ($user->emp_id) {
            $employee = DB::table('core_employee')->where('employee_id', $user->emp_id)->first();

            if ($employee) {
                // Convert NULLs to 0 for consistency
                $employee->territory = $employee->territory ?? 0;
                $employee->region = $employee->region ?? 0;
                $employee->zone = $employee->zone ?? 0;
                $employee->bu = $employee->bu ?? 0;
                // Populate vertical_list for pre-selected crop_vertical
                $vertical_list = DB::table('core_vertical')
                    ->pluck('vertical_name', 'id')
                    ->toArray();

                if ($hasAddDistributorPermission) {
                    // If user has 'add-distributor' permission, fetch all active territories
                    $territory_list = DB::table('core_territory')
                        ->where('is_active', 1)
                        ->pluck('territory_name', 'id')
                        ->toArray();
                } else {
                    // Case 1: territory = 0, region = 0, zone = 0, business unit > 0
                    if ($employee->territory == 0 && $employee->region == 0 && $employee->zone == 0 && $employee->bu > 0) {
                        $mapping = DB::select("
                        SELECT 
                            bzm.zone_id,
                            z.zone_name,
                            zrm.region_id,
                            r.region_name,
                            rtm.territory_id,
                            t.territory_name
                        FROM 
                            core_bu_zone_mapping bzm
                        INNER JOIN 
                            core_zone z ON bzm.zone_id = z.id
                        INNER JOIN 
                            core_zone_region_mapping zrm ON bzm.zone_id = zrm.zone_id
                        INNER JOIN 
                            core_region r ON zrm.region_id = r.id
                        LEFT JOIN 
                            core_region_territory_mapping rtm ON zrm.region_id = rtm.region_id
                        LEFT JOIN 
                            core_territory t ON rtm.territory_id = t.id
                        WHERE 
                            bzm.business_unit_id = ?
                    ", [$employee->bu]);

                        $zone_list = collect($mapping)
                            ->pluck('zone_name', 'zone_id')
                            ->unique()
                            ->filter()
                            ->toArray();

                        $region_list = collect($mapping)
                            ->pluck('region_name', 'region_id')
                            ->unique()
                            ->filter()
                            ->toArray();

                        $territory_list = collect($mapping)
                            ->pluck('territory_name', 'territory_id')
                            ->unique()
                            ->filter()
                            ->toArray();

                        if (count($zone_list) === 1) {
                            $preselected['zone'] = array_key_first($zone_list);
                        }
                        if (count($region_list) === 1) {
                            $preselected['region'] = array_key_first($region_list);
                        }
                        if (count($territory_list) === 1) {
                            $preselected['territory'] = array_key_first($territory_list);
                        }
                    } elseif ($employee->territory == 0 && $employee->region == 0 && $employee->zone > 0) {
                        $mapping = DB::select("
                        SELECT 
                            zrm.zone_id,
                            z.zone_name,
                            zrm.region_id,
                            r.region_name,
                            rtm.territory_id,
                            t.territory_name
                        FROM 
                            core_zone_region_mapping zrm
                        INNER JOIN 
                            core_zone z ON zrm.zone_id = z.id
                        INNER JOIN 
                            core_region r ON zrm.region_id = r.id
                        LEFT JOIN 
                            core_region_territory_mapping rtm ON zrm.region_id = rtm.region_id
                        LEFT JOIN 
                            core_territory t ON rtm.territory_id = t.id
                        WHERE 
                            zrm.zone_id = ?
                    ", [$employee->zone]);

                        $territory_list = collect($mapping)
                            ->pluck('territory_name', 'territory_id')
                            ->unique()
                            ->filter()
                            ->toArray();

                        if (count($territory_list) === 1) {
                            $preselected['territory'] = array_key_first($territory_list);
                        }
                    } elseif ($employee->territory == 0 && $employee->region > 0) {
                        $mapping = DB::select("
                        SELECT 
                            r.id as region_id,
                            r.region_name,
                            zrm.zone_id,
                            z.zone_name,
                            rtm.territory_id,
                            t.territory_name
                        FROM 
                            core_region r
                        LEFT JOIN 
                            core_zone_region_mapping zrm ON r.id = zrm.region_id
                        LEFT JOIN 
                            core_zone z ON zrm.zone_id = z.id
                        LEFT JOIN 
                            core_region_territory_mapping rtm ON r.id = rtm.region_id
                        LEFT JOIN 
                            core_territory t ON rtm.territory_id = t.id
                        WHERE 
                            r.id = ?
                    ", [$employee->region]);

                        $territory_list = collect($mapping)
                            ->pluck('territory_name', 'territory_id')
                            ->unique()
                            ->filter()
                            ->toArray();

                        if (count($territory_list) === 1) {
                            $preselected['territory'] = array_key_first($territory_list);
                        }
                    } elseif ($employee->territory > 0) {
                        $territory = DB::table('core_territory')
                            ->where('id', $employee->territory)
                            ->first();

                        if ($territory) {
                            $territory_list = [$territory->id => $territory->territory_name];
                            $preselected['territory'] = $application->territory ?? $territory->id;
                        }
                    }
                }

                // Fetch regions and zones
                if (isset($preselected['territory']) || $application->territory) {
                    $territoryData = $this->getTerritoryData($application->territory ?? $preselected['territory']);
                    $region_list = $territoryData['regions'] ?? [];
                    $zone_list = $territoryData['zones'] ?? [];
                    $bu_list = $territoryData['businessUnits'] ?? [];
                    $vertical_list = $territoryData['verticals'] ?? [];

                    if (!empty($region_list)) {
                        $preselected['region'] = $application->region ?? array_key_first($region_list);
                    }
                    if (!empty($zone_list)) {
                        $preselected['zone'] = $application->zone ?? array_key_first($zone_list);
                    }
                    if (!empty($bu_list)) {
                        $preselected['bu'] = $application->business_unit ?? array_key_first($bu_list);
                    }
                    if (!empty($vertical_list)) {
                        $preselected['crop_vertical'] = $application->crop_vertical ?? array_key_first($vertical_list);
                    }
                }

                $crop_type = [
                    '1' => 'Field Crop',
                    '2' => 'Veg Crop',
                    '3' => 'Root Stock',
                    '4' => 'Fruit Crop',
                    '5' => 'Common'
                ];

                // Assuming $vertical_list has only one value like: [2 => "Veg Crop"]
                $verticalId = array_key_first($vertical_list);
                $preselected['crop_vertical'] = (string) $verticalId;

                // Fetch crops for selected vertical + always include Common (id=5)
                $cropsQuery = DB::table('core_crop')
                    ->where('is_active', 1)
                    ->select('id', 'crop_name')
                    ->orderBy('crop_name');

                // If vertical is NOT common (5), filter by vertical_id
                if ($verticalId != 5) {
                    $cropsQuery->where('vertical_id', $verticalId);
                }

                $crops = $cropsQuery->get();

                // Fetch states
                $states = Cache::remember('active_states', 60 * 60, function () {
                    return DB::table('core_state')
                        ->where('is_active', 1)
                        ->orderBy('state_name')
                        ->get(['id', 'state_name']);
                });
            }
        }

        // Load `Year` models for business plan display
        $years = Year::all()->keyBy('id');

        $currentYear = '2025-26';
        $financialYears = Year::where('status', 'active')
            ->where('period', '<', $currentYear)
            ->orderBy('start_year', 'desc')
            ->take(3)
            ->get();

        // Define completedStepsData
        $completedStepsData = [
            1 => !empty($application->territory) && !empty($application->crop_vertical) && !empty($application->region) && !empty($application->zone) && !empty($application->business_unit), // Removed district and state
            2 => !empty($application->entityDetails) && !empty($application->entityDetails->establishment_name) && !empty($application->entityDetails->pan_number),
            3 => !empty($application->distributionDetail) && !empty($application->distributionDetail->area_covered) && is_array(json_decode($application->distributionDetail->area_covered, true)) && count(json_decode($application->distributionDetail->area_covered, true)) > 0,
            4 => $application->businessPlans->isNotEmpty(),
            5 => !empty($application->financialInfo) && !empty($application->financialInfo->net_worth),
            6 => !empty($application->bankDetail) && !empty($application->bankDetail->bank_name) && !empty($application->bankDetail->account_number),
            7 => $application->declarations->isNotEmpty(),
            8 => in_array($application->status, ['initiated', 'approved']),
        ];

        if ($step == 8) {
            return view('applications.review-submit', compact('application', 'years'));
        }

        // Pass $initialFrontendStep as $currentStep
        return view('applications.edit', compact(
            'application',
            'bu_list',
            'vertical_list',
            'zone_list',
            'region_list',
            'territory_list',
            'preselected',
            'crop_type',
            'states',
            'step',
            'initialFrontendStep',
            'crops',
            'financialYears',
            'completedStepsData'
        ))->with('currentStep', $initialFrontendStep);
    }

    // Main save step function that routes to specific step handlers
    public function saveStep(Request $request, $stepNumber)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'error' => 'Authentication required.'], 401);
            }

            $application_id = $request->input('application_id');
            $application = $application_id ? Onboarding::find($application_id) : new Onboarding();

            if (!$application && $stepNumber != 1) {
                return response()->json(['success' => false, 'error' => 'Application not found.'], 404);
            }

            // Set created_by for new applications
            if (!$application->exists) {
                $application->created_by = $user->emp_id;
            }

            // **EDIT MODE CHECK**: Allow editing for documents_pending status
            $isEditMode = $application->status === 'documents_pending';
            if ($isEditMode && $user->emp_id !== $application->created_by) {
                return response()->json(['success' => false, 'error' => 'Only application owner can edit.'], 403);
            }

            // Route to specific step handler
            $result = ['success' => false, 'error' => 'Invalid step number.'];
            switch ($stepNumber) {
                case 1:
                    $result = $this->saveStep1($request, $user, $application_id);
                    break;
                case 2:
                    $result = $this->saveStep2($request, $user, $application_id);
                    break;
                case 3:
                    $result = $this->saveStep3($request, $user, $application_id);
                    break;
                case 4:
                    $result = $this->saveStep4($request, $user, $application_id);
                    break;
                case 5:
                    $result = $this->saveStep5($request, $user, $application_id);
                    break;
                case 6:
                    $result = $this->saveStep6($request, $user, $application_id);
                    break;
                case 7:
                    $result = $this->saveStep7($request, $user, $application_id);
                    break;
                case 8:
                    $result = $this->saveStep8($request, $user, $application_id);
                    break;
                default:
                    return response()->json(['success' => false, 'error' => 'Invalid step number.'], 400);
            }

            // Ensure application is saved
            if ($result['success']) {
                $application = Onboarding::find($result['application_id'] ?? $application_id);
                if (!$application) {
                    return response()->json(['success' => false, 'error' => 'Failed to retrieve application.'], 500);
                }

                // Update current_progress_step
                $application->current_progress_step = $stepNumber;
                $application->save();

                // Calculate completedStepsData
                $completedStepsData = [
                    1 => !empty($application->territory) && !empty($application->crop_vertical) && !empty($application->region) && !empty($application->zone) && !empty($application->business_unit),
                    2 => !empty($application->entityDetails) && !empty($application->entityDetails->establishment_name) && !empty($application->entityDetails->pan_number),
                    3 => !empty($application->distributionDetail) && !empty($application->distributionDetail->area_covered) && is_array(json_decode($application->distributionDetail->area_covered, true)) && count(json_decode($application->distributionDetail->area_covered, true)) > 0,
                    4 => $application->businessPlans->isNotEmpty(),
                    5 => !empty($application->financialInfo) && !empty($application->financialInfo->net_worth),
                    6 => !empty($application->bankDetail) && !empty($application->bankDetail->bank_name) && !empty($application->bankDetail->account_number),
                    7 => $application->declarations->isNotEmpty(),
                    8 => in_array($application->status, ['initiated', 'approved', 'mis_processing', 'documents_pending']),
                ];

                // For Step 8, validate all previous steps
                if ($stepNumber == 8 && in_array(false, array_slice($completedStepsData, 1, 7))) {
                    $missingSteps = array_keys(array_filter(array_slice($completedStepsData, 1, 7), fn($completed) => !$completed));
                    return response()->json([
                        'success' => false,
                        'error' => 'Please complete all previous steps.',
                        'missing_steps' => $missingSteps
                    ], 422);
                }

                $response = [
                    'success' => true,
                    'message' => $result['message'] ?? 'Step saved successfully!',
                    'application_id' => $application->id,
                    'current_step' => $stepNumber,
                    'completedStepsData' => $completedStepsData
                ];

                if ($stepNumber == 8 && isset($result['redirect'])) {
                    $response['redirect'] = $result['redirect'];
                }

                return response()->json($response);
            }

            return response()->json($result, $result['status'] ?? 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'An unexpected error occurred.'], 500);
        }
    }

    // Step 1: Distributor Application Details
    private function saveStep1(Request $request, $user, $application_id)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'territory' => 'required|string',
                'crop_vertical' => 'required|string',
                'zone' => 'required|string',
            ], [
                'territory.required' => 'The territory field is required',
                'crop_vertical.required' => 'Please select a crop vertical',
                'zone.required' => 'Please select a zone',
            ]);

            if ($validator->fails()) {
                return ['success' => false, 'error' => $validator->errors()->toArray(), 'status' => 422];
            }

            $data = $validator->validated();
            $data['region'] = $request->input('region');
            $data['business_unit'] = $request->input('business_unit');
            $data['zone'] = $request->input('zone');
            //dd($data);
            $now = now();
            if ($application_id) {
                $application = DB::table('onboardings')
                    ->where('id', $application_id)
                    ->first();
                if (!$application) {
                    DB::rollBack();
                    return ['success' => false, 'error' => 'Application not found', 'status' => 404];
                }

                DB::table('onboardings')
                    ->where('id', $application_id)
                    ->update(array_merge($data, [
                        'current_progress_step' => 2,
                        'updated_at' => $now,
                    ]));
            } else {
                $data['application_code'] = $this->generateUniqueApplicationCode();
                $data['created_by'] = $user->emp_id;
                $data['status'] = 'draft';
                $data['created_at'] = $now;
                $data['updated_at'] = $now;
                //dd($data);

                $application_id = DB::table('onboardings')
                    ->insertGetId($data);
            }

            DB::commit();
            return [
                'success' => true,
                'message' => 'Step 1 saved successfully!',
                'application_id' => $application_id,
                'current_step' => 2
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error' => 'Failed to save step 1: ' . $e->getMessage()
            ];
        }
    }

    // Step 2: Entity Details
    private function saveStep2(Request $request, $user, $application_id)
    {
        if (!$application_id) {
            return ['success' => false, 'error' => 'Application ID is missing.', 'status' => 400];
        }
        DB::beginTransaction();
        try {
            // Fetch existing entity details
            $entityDetails = EntityDetails::where('application_id', $application_id)->first();
            $existingDocuments = $entityDetails && $entityDetails->documents_data
                ? json_decode($entityDetails->documents_data, true)
                : [];
            $existingAuthPersons = $entityDetails && isset($entityDetails->additional_data['authorized_persons'])
                ? $entityDetails->additional_data['authorized_persons']
                : [];

            // Define validation rules
            $rules = [
                // Common fields
                'establishment_name' => 'required|string|max:255',
                'entity_type' => 'required|string|in:individual_person,sole_proprietorship,partnership,llp,private_company,public_company,cooperative_society,trust',
                'business_address' => 'required|string',
                'house_no' => 'nullable|string|max:255',
                'landmark' => 'nullable|string|max:255',
                'city' => 'required|string|max:255',
                'state_id' => 'required|exists:core_state,id',
                'district_id' => 'required|exists:core_district,id',
                'country_id' => 'required|exists:core_country,id',
                'pincode' => 'required|string|max:10',
                'mobile' => 'required|string|max:20',
                'email' => $request->has('no_email') ? 'nullable|email' : 'required|email',
                'pan_number' => 'required|string|max:20',
                'pan_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'existing_pan_file' => 'nullable|string',
                'removed_pan_file' => 'nullable|integer',
                'pan_verified' => 'nullable|boolean',
                'gst_applicable' => 'required|in:yes,no',
                'gst_number' => 'nullable|string|max:20',
                'gst_validity' => 'nullable|date_format:Y-m-d',
                'gst_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'existing_gst_file' => 'nullable|string',
                'removed_gst_file' => 'nullable|integer',
                'seed_license' => 'required|string|max:255',
                'seed_license_validity' => 'required|date_format:Y-m-d',
                'seed_license_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'existing_seed_license_file' => 'nullable|string',
                'removed_seed_license_file' => 'nullable|integer',
                'seed_license_verified' => 'nullable|boolean',
                'bank_name' => 'required|string|max:255',
                'account_holder' => 'required|string|max:255',
                'account_number' => 'required|string|max:20',
                'ifsc_code' => 'required|string|max:11',
                'bank_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'existing_bank_file' => 'nullable|string',
                'removed_bank_file' => 'nullable|integer',
                'tan_number' => 'nullable|string|max:20',
                'has_authorized_persons' => 'required|in:yes,no',

                // Individual Person
                'individual_name' => $request->input('entity_type') === 'individual_person' ? 'required|string|max:255' : 'nullable|string|max:255',
                'individual_dob' => $request->input('entity_type') === 'individual_person' ? 'required|date_format:Y-m-d' : 'nullable|date_format:Y-m-d',
                'individual_father_name' => $request->input('entity_type') === 'individual_person' ? 'required|string|max:255' : 'nullable|string|max:255',
                'individual_age' => $request->input('entity_type') === 'individual_person' ? 'required|integer|min:18|max:100' : 'nullable|integer|min:18|max:100',

                // Sole Proprietorship
                'proprietor_name' => $request->input('entity_type') === 'sole_proprietorship' ? 'required|string|max:255' : 'nullable|string|max:255',
                'proprietor_dob' => $request->input('entity_type') === 'sole_proprietorship' ? 'required|date_format:Y-m-d' : 'nullable|date_format:Y-m-d',
                'proprietor_father_name' => $request->input('entity_type') === 'sole_proprietorship' ? 'required|string|max:255' : 'nullable|string|max:255',
                'proprietor_age' => $request->input('entity_type') === 'sole_proprietorship' ? 'required|integer|min:18|max:100' : 'nullable|integer|min:18|max:100',

                // Partnership
                'partner_name.*' => $request->input('entity_type') === 'partnership' ? 'required|string|max:255' : 'nullable|string|max:255',
                'partner_pan.*' => $request->input('entity_type') === 'partnership' ? 'required|string|max:20|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/' : 'nullable|string|max:20',
                'partner_contact.*' => $request->input('entity_type') === 'partnership' ? 'required|string|max:20|regex:/^[0-9]{10}$/' : 'nullable|string|max:20',
                'signatory_name.*' => $request->input('entity_type') === 'partnership' ? 'nullable|string|max:255' : 'nullable|string|max:255',
                'signatory_designation.*' => $request->input('entity_type') === 'partnership' ? 'required_with:signatory_contact.*|string|max:255' : 'nullable|string|max:255',
                'signatory_contact.*' => $request->input('entity_type') === 'partnership' ? 'required_with:signatory_designation.*|string|max:20|regex:/^[0-9]{10}$/' : 'nullable|string|max:20',

                // LLP
                'llpin_number' => $request->input('entity_type') === 'llp' ? 'required|string|max:255' : 'nullable|string|max:255',
                'llp_incorporation_date' => $request->input('entity_type') === 'llp' ? 'required|date_format:Y-m-d' : 'nullable|date_format:Y-m-d',
                'llp_partner_name.*' => $request->input('entity_type') === 'llp' ? 'required|string|max:255' : 'nullable|string|max:255',
                'llp_partner_dpin.*' => $request->input('entity_type') === 'llp' ? 'required|string|max:255' : 'nullable|string|max:255',
                'llp_partner_contact.*' => $request->input('entity_type') === 'llp' ? 'required|string|max:20' : 'nullable|string|max:20',
                'llp_partner_address.*' => $request->input('entity_type') === 'llp' ? 'required|string' : 'nullable|string',

                // Company
                'cin_number' => in_array($request->input('entity_type'), ['private_company', 'public_company']) ? 'required|string|max:255' : 'nullable|string|max:255',
                'incorporation_date' => in_array($request->input('entity_type'), ['private_company', 'public_company']) ? 'required|date_format:Y-m-d' : 'nullable|date_format:Y-m-d',
                'director_name.*' => in_array($request->input('entity_type'), ['private_company', 'public_company']) ? 'required|string|max:255' : 'nullable|string|max:255',
                'director_din.*' => in_array($request->input('entity_type'), ['private_company', 'public_company']) ? 'required|string|max:255' : 'nullable|string|max:255',
                'director_contact.*' => in_array($request->input('entity_type'), ['private_company', 'public_company']) ? 'required|string|max:20' : 'nullable|string|max:20',
                'director_address.*' => in_array($request->input('entity_type'), ['private_company', 'public_company']) ? 'required|string' : 'nullable|string',

                // Cooperative
                'cooperative_reg_number' => $request->input('entity_type') === 'cooperative_society' ? 'required|string|max:255' : 'nullable|string|max:255',
                'cooperative_reg_date' => $request->input('entity_type') === 'cooperative_society' ? 'required|date_format:Y-m-d' : 'nullable|date_format:Y-m-d',
                'committee_name.*' => $request->input('entity_type') === 'cooperative_society' ? 'required|string|max:255' : 'nullable|string|max:255',
                'committee_designation.*' => $request->input('entity_type') === 'cooperative_society' ? 'required|string|max:255' : 'nullable|string|max:255',
                'committee_contact.*' => $request->input('entity_type') === 'cooperative_society' ? 'required|string|max:20' : 'nullable|string|max:20',
                'committee_address.*' => $request->input('entity_type') === 'cooperative_society' ? 'required|string' : 'nullable|string',

                // Trust
                'trust_reg_number' => $request->input('entity_type') === 'trust' ? 'required|string|max:255' : 'nullable|string|max:255',
                'trust_reg_date' => $request->input('entity_type') === 'trust' ? 'required|date_format:Y-m-d' : 'nullable|date_format:Y-m-d',
                'trustee_name.*' => $request->input('entity_type') === 'trust' ? 'required|string|max:255' : 'nullable|string|max:255',
                'trustee_designation.*' => $request->input('entity_type') === 'trust' ? 'required|string|max:255' : 'nullable|string|max:255',
                'trustee_contact.*' => $request->input('entity_type') === 'trust' ? 'required|string|max:20' : 'nullable|string|max:20',
                'trustee_address.*' => $request->input('entity_type') === 'trust' ? 'required|string' : 'nullable|string',

                // Authorized Persons
                'auth_person_name.*' => 'nullable|string|max:255',
                'auth_person_contact.*' => 'nullable|string|max:20',
                'auth_person_email.*' => 'nullable|email|max:255',
                'auth_person_address.*' => 'nullable|string',
                'auth_person_relation.*' => 'nullable|string|max:255',
                'auth_person_aadhar_number.*' => 'nullable|string|max:12|regex:/^[0-9]{12}$/',
                'auth_person_letter.*' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
                'auth_person_aadhar.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'existing_auth_person_letter.*' => 'nullable|string',
                'existing_auth_person_aadhar.*' => 'nullable|string',
                'existing_auth_person_letter_original.*' => 'nullable|string',
                'existing_auth_person_aadhar_original.*' => 'nullable|string',
                'removed_auth_person_letter.*' => 'nullable|integer',
                'removed_auth_person_aadhar.*' => 'nullable|integer',
            ];

            // Custom validation for file fields and authorized persons
            $validator = Validator::make($request->all(), $rules);
            $validator->after(function ($validator) use ($request, $existingDocuments, $existingAuthPersons) {
                // Validate file fields
                $fileFields = [
                    'bank_file' => 'A bank document is required.',
                    'seed_license_file' => 'A seed license document is required.',
                    'pan_file' => 'A PAN document is required.',
                    'gst_file' => 'A GST document is required when GST is applicable.'
                ];
                foreach ($fileFields as $field => $errorMessage) {
                    if ($field === 'gst_file' && $request->input('gst_applicable') !== 'yes') {
                        continue;
                    }
                    $hasNewFile = $request->hasFile($field);
                    $hasExistingFile = !empty($request->input("existing_$field")) && !$request->input("removed_$field");
                    if (!$hasNewFile && !$hasExistingFile) {
                        $validator->errors()->add($field, $errorMessage);
                    }
                }

                // Validate authorized persons only if has_authorized_persons is 'yes'
                if ($request->input('has_authorized_persons') === 'yes') {
                    if (empty(array_filter($request->input('auth_person_name', [])))) {
                        $validator->errors()->add('auth_person_name', 'At least one authorized person is required when authorized persons are selected.');
                    } else {
                        $removedLetters = $request->input('removed_auth_person_letter', []);
                        $removedAadhars = $request->input('removed_auth_person_aadhar', []);

                        foreach ($request->input('auth_person_name', []) as $index => $name) {
                            if (!empty($name)) {
                                // Validate required fields
                                if (empty($request->input('auth_person_contact', [])[$index])) {
                                    $validator->errors()->add("auth_person_contact.$index", 'Contact number is required for each authorized person.');
                                }
                                if (empty($request->input('auth_person_address', [])[$index])) {
                                    $validator->errors()->add("auth_person_address.$index", 'Address is required for each authorized person.');
                                }
                                if (empty($request->input('auth_person_relation', [])[$index])) {
                                    $validator->errors()->add("auth_person_relation.$index", 'Relation is required for each authorized person.');
                                }
                                if (empty($request->input('auth_person_aadhar_number', [])[$index])) {
                                    $validator->errors()->add("auth_person_aadhar_number.$index", 'Aadhar number is required for each authorized person.');
                                }

                                // Validate Letter of Authorization
                                $hasNewLetter = $request->hasFile("auth_person_letter.$index");
                                $hasExistingLetter = !empty($request->input("existing_auth_person_letter.$index")) ||
                                    (isset($existingAuthPersons[$index]['letter']) && !in_array($index, $removedLetters));
                                if (!$hasNewLetter && !$hasExistingLetter) {
                                    $validator->errors()->add("auth_person_letter.$index", 'A Letter of Authorization is required for each authorized person.');
                                }

                                // Validate Aadhar
                                $hasNewAadhar = $request->hasFile("auth_person_aadhar.$index");
                                $hasExistingAadhar = !empty($request->input("existing_auth_person_aadhar.$index")) ||
                                    (isset($existingAuthPersons[$index]['aadhar']) && !in_array($index, $removedAadhars));
                                if (!$hasNewAadhar && !$hasExistingAadhar) {
                                    $validator->errors()->add("auth_person_aadhar.$index", 'An Aadhar document is required for each authorized person.');
                                }
                            }
                        }
                    }
                }
            });

            if ($validator->fails()) {
                return ['success' => false, 'error' => $validator->errors()->toArray(), 'status' => 422];
            }

            $data = $validator->validated();
            $entity_type = $data['entity_type'];

            // Initialize documents_data with existing documents
            $documents_data = $existingDocuments ?: [];

            // Process new or updated documents
            $documentTypes = [
                'pan' => [
                    'file_field' => 'pan_file',
                    'existing_field' => 'existing_pan_file',
                    'existing_field_original' => 'existing_pan_file_original',
                    'details' => ['pan_number' => $data['pan_number']],
                    'verified_field' => 'pan_verified',
                    'prefix' => 'pandoc',
                    's3_folder' => 'pan',
                ],
                'seed_license' => [
                    'file_field' => 'seed_license_file',
                    'existing_field' => 'existing_seed_license_file',
                    'existing_field_original' => 'existing_seed_license_file_original',
                    'details' => [
                        'seed_license_number' => $data['seed_license'],
                        'seed_license_validity' => $data['seed_license_validity'],
                    ],
                    'verified_field' => 'seed_license_verified',
                    'prefix' => 'seeddoc',
                    's3_folder' => 'seed_license',
                ],
                'bank' => [
                    'file_field' => 'bank_file',
                    'existing_field' => 'existing_bank_file',
                    'existing_field_original' => 'existing_bank_file_original',
                    'details' => [
                        'bank_name' => $data['bank_name'],
                        'account_holder' => $data['account_holder'],
                        'account_number' => $data['account_number'],
                        'ifsc_code' => $data['ifsc_code'],
                    ],
                    'prefix' => 'bankdoc',
                    's3_folder' => 'bank',
                ],
                'gst' => [
                    'file_field' => 'gst_file',
                    'existing_field' => 'existing_gst_file',
                    'existing_field_original' => 'existing_gst_file_original',
                    'details' => [
                        'gst_number' => $data['gst_applicable'] === 'yes' ? $data['gst_number'] : null,
                        'gst_validity' => $data['gst_applicable'] === 'yes' ? $data['gst_validity'] : null,
                    ],
                    'condition' => $data['gst_applicable'] === 'yes',
                    'prefix' => 'gstdoc',
                    's3_folder' => 'gst',
                ],
            ];

            foreach ($documentTypes as $type => $config) {
                if ($type === 'gst' && !$config['condition']) {
                    $documents_data = array_filter($documents_data, fn($doc) => $doc['type'] !== $type);
                    continue;
                }

                if ($request->input($config['existing_field']) && !$request->input("removed_{$type}_file")) {
                    $existingDoc = collect($documents_data)->firstWhere('type', $type);
                    $filename = $request->input($config['existing_field']);
                    $originalFilename = $request->input($config['existing_field_original']) ?: ($existingDoc['original_filename'] ?? 'Unknown');

                    $s3Path = "Connect/Distributor/{$config['s3_folder']}/{$filename}";
                    if (!Storage::disk('s3')->exists($s3Path)) {
                        Log::warning("File not found in S3, retaining metadata without verification", ['s3Path' => $s3Path]);
                    }

                    $documents_data = array_filter($documents_data, fn($doc) => $doc['type'] !== $type);
                    $documents_data[] = [
                        'type' => $type,
                        'path' => $filename,
                        'details' => array_filter($config['details'], fn($value) => !is_null($value)),
                        'status' => $existingDoc ? ($existingDoc['status'] ?? 'pending') : 'pending',
                        'remarks' => $existingDoc ? ($existingDoc['remarks'] ?? 'Existing file retained on ' . now()->toDateString()) : 'Existing file retained on ' . now()->toDateString(),
                        'verified' => isset($config['verified_field']) ? ($request->input($config['verified_field']) ? true : ($existingDoc['verified'] ?? false)) : ($existingDoc['verified'] ?? false),
                        'original_filename' => $originalFilename,
                    ];
                }
            }

            // Common entity details
            $entityData = [
                'application_id' => $application_id,
                'establishment_name' => $data['establishment_name'],
                'entity_type' => $entity_type,
                'business_address' => $data['business_address'],
                'house_no' => $data['house_no'],
                'landmark' => $data['landmark'],
                'city' => $data['city'],
                'state_id' => $data['state_id'],
                'district_id' => $data['district_id'],
                'country_id' => $data['country_id'],
                'pincode' => $data['pincode'],
                'mobile' => $data['mobile'],
                'email' => $data['email'] ?? null,
                'pan_number' => $data['pan_number'],
                'gst_applicable' => $data['gst_applicable'],
                'gst_number' => $data['gst_applicable'] === 'yes' ? $data['gst_number'] : null,
                'seed_license' => $data['seed_license'],
                'documents_data' => json_encode(array_values($documents_data)),
                'additional_data' => [],
                'updated_at' => now(),
            ];

            // Entity-specific and additional data
            $additionalData = [
                'tan_number' => $data['tan_number'] ?? null,
                'gst_validity' => $data['gst_applicable'] === 'yes' ? $data['gst_validity'] : null,
                'seed_license_validity' => $data['seed_license_validity'],
                'bank_details' => [
                    'bank_name' => $data['bank_name'],
                    'account_holder' => $data['account_holder'],
                    'account_number' => $data['account_number'],
                    'ifsc_code' => $data['ifsc_code'],
                ],
                'partners' => [],
                'authorized_persons' => [],
            ];

            // Process entity-specific data
            if ($entity_type === 'individual_person') {
                $additionalData['individual'] = [
                    'name' => $data['individual_name'],
                    'dob' => $data['individual_dob'],
                    'father_name' => $data['individual_father_name'],
                    'age' => $data['individual_age'],
                ];
            } elseif ($entity_type === 'sole_proprietorship') {
                $additionalData['proprietor'] = [
                    'name' => $data['proprietor_name'],
                    'dob' => $data['proprietor_dob'],
                    'father_name' => $data['proprietor_father_name'],
                    'age' => $data['proprietor_age'],
                ];
            } elseif ($entity_type === 'partnership') {
                $partners = [];
                if ($request->has('partner_name') && is_array($request->input('partner_name'))) {
                    foreach ($request->input('partner_name', []) as $index => $name) {
                        if (!empty($name)) {
                            $partners[] = [
                                'name' => $name,
                                'pan' => $request->input('partner_pan', [])[$index],
                                'contact' => $request->input('partner_contact', [])[$index],
                            ];
                        }
                    }
                }
                $additionalData['partners'] = $partners;

                // Process signatories
                $signatories = [];
                if ($request->has('signatory_name') && is_array($request->input('signatory_name'))) {
                    foreach ($request->input('signatory_name', []) as $index => $name) {
                        if (!empty($name) || !empty($request->input('signatory_designation', [])[$index])) {
                            $signatories[] = [
                                'name' => $name,
                                'designation' => $request->input('signatory_designation', [])[$index],
                                'contact' => $request->input('signatory_contact', [])[$index],
                            ];
                        }
                    }
                }
                $additionalData['signatories'] = $signatories;
            } elseif ($entity_type === 'llp') {
                $additionalData['llp'] = [
                    'llpin_number' => $data['llpin_number'],
                    'incorporation_date' => $data['llp_incorporation_date'],
                ];
                $partners = [];
                if ($request->has('llp_partner_name') && is_array($request->input('llp_partner_name'))) {
                    foreach ($request->input('llp_partner_name', []) as $index => $name) {
                        if (!empty($name)) {
                            $partners[] = [
                                'name' => $name,
                                'dpin_number' => $request->input('llp_partner_dpin', [])[$index],
                                'contact' => $request->input('llp_partner_contact', [])[$index],
                                'address' => $request->input('llp_partner_address', [])[$index],
                            ];
                        }
                    }
                }
                $additionalData['partners'] = $partners;
            } elseif (in_array($entity_type, ['private_company', 'public_company'])) {
                $additionalData['company'] = [
                    'cin_number' => $data['cin_number'],
                    'incorporation_date' => $data['incorporation_date'],
                ];
                $partners = [];
                if ($request->has('director_name') && is_array($request->input('director_name'))) {
                    foreach ($request->input('director_name', []) as $index => $name) {
                        if (!empty($name)) {
                            $partners[] = [
                                'name' => $name,
                                'din_number' => $request->input('director_din', [])[$index],
                                'contact' => $request->input('director_contact', [])[$index],
                                'address' => $request->input('director_address', [])[$index],
                            ];
                        }
                    }
                }
                $additionalData['partners'] = $partners;
            } elseif ($entity_type === 'cooperative_society') {
                $additionalData['cooperative'] = [
                    'reg_number' => $data['cooperative_reg_number'],
                    'reg_date' => $data['cooperative_reg_date'],
                ];
                $partners = [];
                if ($request->has('committee_name') && is_array($request->input('committee_name'))) {
                    foreach ($request->input('committee_name', []) as $index => $name) {
                        if (!empty($name)) {
                            $partners[] = [
                                'name' => $name,
                                'designation' => $request->input('committee_designation', [])[$index],
                                'contact' => $request->input('committee_contact', [])[$index],
                                'address' => $request->input('committee_address', [])[$index],
                            ];
                        }
                    }
                }
                $additionalData['partners'] = $partners;
            } elseif ($entity_type === 'trust') {
                $additionalData['trust'] = [
                    'reg_number' => $data['trust_reg_number'],
                    'reg_date' => $data['trust_reg_date'],
                ];
                $partners = [];
                if ($request->has('trustee_name') && is_array($request->input('trustee_name'))) {
                    foreach ($request->input('trustee_name', []) as $index => $name) {
                        if (!empty($name)) {
                            $partners[] = [
                                'name' => $name,
                                'designation' => $request->input('trustee_designation', [])[$index],
                                'contact' => $request->input('trustee_contact', [])[$index],
                                'address' => $request->input('trustee_address', [])[$index],
                            ];
                        }
                    }
                }
                $additionalData['partners'] = $partners;
            }

            // Process authorized persons
            $authorizedPersons = [];
            $removedLetters = $request->input('removed_auth_person_letter', []);
            $removedAadhars = $request->input('removed_auth_person_aadhar', []);
            if ($request->has('auth_person_name') && is_array($request->input('auth_person_name'))) {
                foreach ($request->input('auth_person_name', []) as $index => $name) {
                    if (!empty($name) && !in_array($index, $removedLetters) && !in_array($index, $removedAadhars)) {
                        $personData = [
                            'name' => $name,
                            'contact' => $request->input('auth_person_contact', [])[$index] ?? null,
                            'email' => $request->input('auth_person_email', [])[$index] ?? null,
                            'address' => $request->input('auth_person_address', [])[$index] ?? null,
                            'relation' => $request->input('auth_person_relation', [])[$index] ?? null,
                            'aadhar_number' => $request->input('auth_person_aadhar_number', [])[$index] ?? null,
                        ];

                        // Handle Letter
                        if ($request->hasFile("auth_person_letter.$index")) {
                            $letterFile = $request->file("auth_person_letter.$index");
                            $letterPath = $letterFile->store('documents/' . $application_id . '/authorized_persons', 'public');
                            $personData['letter'] = [
                                'path' => $letterPath,
                                'original_filename' => $letterFile->getClientOriginalName()
                            ];
                        } elseif ($request->input("existing_auth_person_letter.$index") && !in_array($index, $removedLetters)) {
                            $personData['letter'] = [
                                'path' => $request->input("existing_auth_person_letter.$index"),
                                'original_filename' => $request->input("existing_auth_person_letter_original.$index") ?? 'Unknown'
                            ];
                        } elseif (isset($existingAuthPersons[$index]['letter']) && !in_array($index, $removedLetters)) {
                            $personData['letter'] = [
                                'path' => $existingAuthPersons[$index]['letter']['path'] ?? $existingAuthPersons[$index]['letter'],
                                'original_filename' => $existingAuthPersons[$index]['letter']['original_filename'] ?? 'Unknown'
                            ];
                        }

                        // Handle Aadhar
                        if ($request->hasFile("auth_person_aadhar.$index")) {
                            $aadharFile = $request->file("auth_person_aadhar.$index");
                            $aadharPath = $aadharFile->store('documents/' . $application_id . '/authorized_persons', 'public');
                            $personData['aadhar'] = [
                                'path' => $aadharPath,
                                'original_filename' => $aadharFile->getClientOriginalName()
                            ];
                        } elseif ($request->input("existing_auth_person_aadhar.$index") && !in_array($index, $removedAadhars)) {
                            $personData['aadhar'] = [
                                'path' => $request->input("existing_auth_person_aadhar.$index"),
                                'original_filename' => $request->input("existing_auth_person_aadhar_original.$index") ?? 'Unknown'
                            ];
                        } elseif (isset($existingAuthPersons[$index]['aadhar']) && !in_array($index, $removedAadhars)) {
                            $personData['aadhar'] = [
                                'path' => $existingAuthPersons[$index]['aadhar']['path'] ?? $existingAuthPersons[$index]['aadhar'],
                                'original_filename' => $existingAuthPersons[$index]['aadhar']['original_filename'] ?? 'Unknown'
                            ];
                        }

                        if (!empty($personData['letter']) && !empty($personData['aadhar'])) {
                            $authorizedPersons[] = $personData;
                        }
                    }
                }
            }

            $additionalData['authorized_persons'] = $authorizedPersons;

            // Remove empty arrays or null values from additional_data
            $additionalData = array_filter($additionalData, fn($value) => is_array($value) ? !empty(array_filter($value, fn($subValue) => !is_null($subValue) && !(is_array($subValue) && empty($subValue)))) : !is_null($value));

            // Set additional_data in entityData
            $entityData['additional_data'] = $additionalData;

            // Log entityData before saving
            //Log::info('Saving entity details', ['entityData' => $entityData]);

            // Update or create EntityDetails
            EntityDetails::updateOrCreate(
                ['application_id' => $application_id],
                $entityData
            );

            // Update application progress
            $application = Onboarding::find($application_id);
            if ($application && $application->current_progress_step < 3) {
                $application->update(['current_progress_step' => 3]);
            }

            DB::commit();
            return ['success' => true, 'message' => 'Entity details and documents saved successfully', 'application_id' => $application_id, 'current_step' => 3];
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error saving entity details: ' . $e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return ['success' => false, 'error' => 'An error occurred while saving entity details and documents: ' . $e->getMessage(), 'status' => 500];
        }
    }
    // Step 3: Distribution Details
    private function saveStep3(Request $request, $user, $application_id)
    {

        if (!$application_id) {
            return ['success' => false, 'error' => 'Application ID is missing.', 'status' => 400];
        }

        DB::beginTransaction();

        try {
            // Validation rules
            $rules = [
                'area_covered' => 'required|array|min:1',
                'area_covered.*' => 'string|max:255|exists:core_district,district_name',
                'appointment_type' => 'required|in:new_area,replacement,addition',
            ];

            if ($request->appointment_type === 'replacement') {
                $rules = array_merge($rules, [
                    'replacement_reason' => 'required|string|max:1000',
                    'outstanding_recovery' => 'required|string|max:1000',
                    'previous_firm_name' => 'required|string|max:255',
                    'previous_firm_code' => 'required|string|max:100',
                ]);
            } elseif ($request->appointment_type === 'new_area') {
                $rules['earlier_distributor'] = 'required|string|max:255';
            }

            // Handle comma-separated string as a fallback
            $input = $request->all();
            // if (isset($input['area_covered']) && !is_array($input['area_covered'])) {
            //     $input['area_covered'] = array_map('trim', explode(',', $input['area_covered']));
            // }

            if (!isset($input['area_covered']) || $input['area_covered'] === null || $input['area_covered'] === '') {
                $input['area_covered'] = [];
            } elseif (!is_array($input['area_covered'])) {
                $input['area_covered'] = array_map('trim', explode(',', $input['area_covered']));
            } else {
                $processed = [];
                foreach ($input['area_covered'] as $area) {
                    if (strpos($area, ',') !== false) {
                        $processed = array_merge($processed, array_map('trim', explode(',', $area)));
                    } else {
                        $processed[] = trim($area);
                    }
                }
                $input['area_covered'] = $processed;
            }

            $validator = Validator::make($input, $rules, [
                'area_covered.*.exists' => 'The selected district ":input" is not a valid district.',
            ]);

            if ($validator->fails()) {
                return ['success' => false, 'error' => $validator->errors()->toArray(), 'status' => 422];
            }

            // Prepare data
            $data = [
                'application_id' => $application_id,
                'area_covered' => json_encode($input['area_covered']),
                'appointment_type' => $request->appointment_type,
                'replacement_reason' => $request->appointment_type === 'replacement' ? $request->replacement_reason : null,
                'outstanding_recovery' => $request->appointment_type === 'replacement' ? $request->outstanding_recovery : null,
                'previous_firm_name' => $request->appointment_type === 'replacement' ? $request->previous_firm_name : null,
                'previous_firm_code' => $request->appointment_type === 'replacement' ? $request->previous_firm_code : null,
                'earlier_distributor' => $request->appointment_type === 'new_area' ? $request->earlier_distributor : '',
            ];

            // Update or create record
            DistributionDetail::updateOrCreate(
                ['application_id' => $application_id],
                $data
            );
            $application = Onboarding::find($application_id);
            if ($application) {
                if ($application->current_progress_step < 4) {
                    $application->update(['current_progress_step' => 4]);
                }
            }
            DB::commit();
            return [
                'success' => true,
                'message' => 'Distribution details saved successfully!',
                'application_id' => $application_id,
                'current_step' => 4
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'error' => 'Failed to save distribution details: ' . $e->getMessage()];
        }
    }

    // Step 4: Business Plans
    private function saveStep4(Request $request, $user, $application_id)
    {
        if (!$application_id) {
            return ['success' => false, 'error' => 'Application ID is missing.', 'status' => 400];
        }

        // --- 1. VALIDATION ---
        // Validate the incoming array of business plans.
        $validator = Validator::make($request->all(), [
            'business_plans' => 'required|array|min:1',
            'business_plans.*.crop' => 'required|string|max:255',
            'business_plans.*.fy2025_26' => 'required|numeric|min:0',
            'business_plans.*.fy2026_27' => 'required|numeric|min:0',
        ], [
            'business_plans.required' => 'At least one business plan is required.',
            'business_plans.min' => 'At least one business plan is required.',
            'business_plans.*.crop.required' => 'The crop field is required for all plans.',
            'business_plans.*.fy2025_26.required' => 'The FY 2025-26 field is required for all plans.',
            'business_plans.*.fy2026_27.required' => 'The FY 2026-27 field is required for all plans.',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'error' => $validator->errors()->toArray(), 'status' => 422];
        }

        DB::beginTransaction();

        try {
            $validatedData = $validator->validated();
            $plansToInsert = [];
            $year2025 = Year::where('period', '2025-26')->firstOrFail();
            $year2026 = Year::where('period', '2026-27')->firstOrFail();
            // Loop through each submitted plan and format it for the database.
            foreach ($validatedData['business_plans'] as $planData) {
                $plansToInsert[] = [
                    'application_id' => $application_id,
                    'crop' => $planData['crop'],
                    'yearly_targets' => json_encode([
                        $year2025->id => $planData['fy2025_26'],
                        $year2026->id => $planData['fy2026_27'],
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('business_plans')->where('application_id', $application_id)->delete();
            if (!empty($plansToInsert)) {
                DB::table('business_plans')->insert($plansToInsert);
            }
            $application = Onboarding::find($application_id);
            if ($application) {
                if ($application->current_progress_step < 5) {
                    $application->update(['current_progress_step' => 5]);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Step 4 saved successfully!', // Corrected step number
                'application_id' => $application_id,
                'current_step' => 5
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'error' => 'An unexpected error occurred while saving step 4.'];
        }
    }

    // Step 5: Financial Info
    private function saveStep5(Request $request, $user, $application_id)
    {
        if (!$application_id) {
            return ['success' => false, 'error' => 'Application ID is missing.', 'status' => 400];
        }

        DB::beginTransaction();

        try {
            $currentYear = date('Y');
            $defaultYears = [
                ($currentYear - 3) . '-' . substr($currentYear - 2, -2), // 2022-23 format
                ($currentYear - 2) . '-' . substr($currentYear - 1, -2), // 2023-24 format
                ($currentYear - 1) . '-' . substr($currentYear, -2)       // 2024-25 format
            ];

            $rules = [
                'net_worth' => 'required|numeric|min:0',
                'shop_ownership' => 'required|string|in:owned,rented,lease',
                'godown_area' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s]+$/',
                'years_in_business' => 'required|integer|min:0',
                'annual_turnover.year' => 'required|array|min:1|max:3',
                'annual_turnover.year.*' => ['required', 'string', Rule::in($defaultYears)], // Use Rule::in for dynamic values
                'annual_turnover.amount' => [
                    'required',
                    'array',
                    'size:3',
                    function ($attribute, $value, $fail) {
                        $nonEmpty = array_filter($value, fn($v) => !is_null($v) && $v !== '');
                        if (empty($nonEmpty)) {
                            $fail('At least one financial year must have a non-empty turnover amount.');
                        }
                    },
                ],
                'annual_turnover.amount.*' => 'nullable|numeric|min:0',
                'existing_distributorships' => 'sometimes|array',
                'existing_distributorships.*.company_name' => 'nullable|string|max:255',
            ];

            $validator = Validator::make($request->all(), $rules, [
                'annual_turnover.year.*.in' => 'The financial year must be one of: ' . implode(', ', $defaultYears) . '.',
            ]);

            if ($validator->fails()) {
                return ['success' => false, 'error' => $validator->errors()->toArray(), 'status' => 422];
            }

            $turnover = array_combine(
                $request->annual_turnover['year'],
                array_map(fn($amount) => $amount !== '' ? $amount : null, $request->annual_turnover['amount'])
            );
            $turnover = array_filter($turnover, fn($value) => !is_null($value));

            $data = [
                'application_id' => $application_id,
                'net_worth' => $request->net_worth,
                'shop_ownership' => $request->shop_ownership,
                'godown_area' => trim($request->godown_area),
                'years_in_business' => $request->years_in_business,
                'annual_turnover' => json_encode($turnover),
            ];

            FinancialInfo::updateOrCreate(
                ['application_id' => $application_id],
                $data
            );
            // Process existing distributorships if present
            if ($request->has('existing_distributorships')) {
                $submittedCompanies = $request->input('existing_distributorships', []);
                $validCompanies = array_filter($submittedCompanies, function ($company) {
                    return isset($company['id']) || !empty(trim($company['company_name'] ?? ''));
                });

                $existingIds = collect($validCompanies)->pluck('id')->filter()->toArray();

                // Delete records not present in the submitted data
                ExistingDistributorship::where('application_id', $application_id)
                    ->whereNotIn('id', $existingIds)
                    ->delete();

                // Create/update entries
                foreach ($validCompanies as $companyData) {
                    ExistingDistributorship::updateOrCreate(
                        ['id' => $companyData['id'] ?? null, 'application_id' => $application_id],
                        ['company_name' => isset($companyData['company_name']) ? trim($companyData['company_name']) : null]
                    );
                }
            }
            $application = Onboarding::find($application_id);
            if ($application) {
                if ($application->current_progress_step < 6) {
                    $application->update(['current_progress_step' => 6]);
                }
            }
            DB::commit();
            return [
                'success' => true,
                'message' => 'Financial info saved successfully!',
                'application_id' => $application_id,
                'current_step' => 6
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error' => 'Failed to save financial info: ' . $e->getMessage(),
                'application_id' => $application_id
            ];
        }
    }

    // Step 7: Bank Details
    private function saveStep6(Request $request, $user, $application_id)
    {
        if (!$application_id) {
            return ['success' => false, 'error' => 'Application ID is missing.', 'status' => 400];
        }

        DB::beginTransaction();

        try {
            $validator = Validator::make($request->all(), [
                'financial_status' => 'required|in:Good,Very Good,Excellent,Average',
                'retailer_count' => 'required|numeric|min:0',
                'bank_name' => 'required|string|max:255',
                'account_holder' => 'required|string|max:255',
                'account_number' => 'required|string|max:255',
                'ifsc_code' => 'required|string|max:20',
                'account_type' => 'required|in:current,savings',
                'relationship_duration' => 'required|numeric|min:0',
                'od_limit' => 'nullable|string|max:255',
                'od_security' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return ['success' => false, 'error' => $validator->errors()->toArray(), 'status' => 422];
            }

            $data = $validator->validated();
            $data['application_id'] = $application_id;

            BankDetail::updateOrCreate(
                ['application_id' => $application_id],
                $data
            );
            $application = Onboarding::find($application_id);
            if ($application) {
                if ($application->current_progress_step < 7) {
                    $application->update(['current_progress_step' => 7]);
                }
            }
            DB::commit();
            return [
                'success' => true,
                'message' => 'Bank details saved successfully!',
                'application_id' => $application_id,
                'current_step' => 7
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'error' => 'Failed to save bank details.'];
        }
    }

    // Step 8: Declarations
    private function saveStep7(Request $request, $user, $application_id)
    {
        if (!$application_id) {
            return ['success' => false, 'error' => 'Application ID is missing.', 'status' => 400];
        }

        DB::beginTransaction();

        try {
            // Define all questions and their validation rules
            $questions = [
                'is_other_distributor' => [
                    'details_field' => 'other_distributor_details',
                    'label' => 'Other Distributor Details'
                ],
                'has_sister_concern' => [
                    'details_field' => 'sister_concern_details',
                    'label' => 'Sister Concern Details'
                ],
                'has_question_c' => [
                    'details_field' => 'question_c_details',
                    'label' => 'Similar Crops Distributor Details'
                ],
                'has_question_d' => [
                    'details_field' => 'question_d_details',
                    'label' => 'Agro Inputs Association Details'
                ],
                'has_question_e' => [
                    'details_field' => 'question_e_details',
                    'label' => 'Previous VNR Seeds Distributorship Details'
                ],
                'has_disputed_dues' => [
                    'details_fields' => [
                        'disputed_amount' => 'Disputed Amount',
                        'dispute_nature' => 'Nature of Dispute',
                        'dispute_year' => 'Year of Dispute',
                        'dispute_status' => 'Present Position',
                        'dispute_reason' => 'Reason for Default'
                    ],
                    'label' => 'Disputed Dues Details'
                ],
                'has_question_g' => [
                    'details_field' => 'question_g_details',
                    'label' => 'Ceased Agent/Distributor Details'
                ],
                'has_question_h' => [
                    'details_field' => 'question_h_details',
                    'label' => 'Relative Connection Details'
                ],
                'has_question_i' => [
                    'details_field' => 'question_i_details',
                    'label' => 'Other Company Involvement Details'
                ],
                'has_question_j' => [
                    'details_fields' => [
                        'referrer_1' => 'Referrer I',
                        'referrer_2' => 'Referrer II',
                        'referrer_3' => 'Referrer III',
                        'referrer_4' => 'Referrer IV'
                    ],
                    'label' => 'Referrer Details'
                ],
                'has_question_k' => [
                    'details_field' => 'question_k_details',
                    'label' => 'Own Brand Marketing Details'
                ],
                'has_question_l' => [
                    'details_field' => 'question_l_details',
                    'label' => 'Agro-Input Industry Employment Details'
                ],
                'declaration_truthful' => [
                    'label' => 'Declaration Truthful'
                ],
                'declaration_update' => [
                    'label' => 'Declaration Update'
                ],
            ];

            // Build validation rules
            $rules = [];
            foreach ($questions as $question_key => $config) {
                $rules[$question_key] = ['required', 'in:0,1'];
                if (isset($config['details_field'])) {
                    $rules[$config['details_field']] = [
                        'nullable',
                        'string',
                        Rule::requiredIf(function () use ($request, $question_key, $config) {
                            return $request->input($question_key) == 1 || !empty($request->input($config['details_field']));
                        })
                    ];
                } elseif (isset($config['details_fields'])) {
                    foreach ($config['details_fields'] as $field => $label) {
                        $rules[$field] = [
                            'nullable',
                            'string',
                            Rule::requiredIf(function () use ($request, $question_key, $field) {
                                return $request->input($question_key) == 1 || !empty($request->input($field));
                            })
                        ];
                    }
                }
            }

            $validator = Validator::make($request->all(), $rules, [
                'declaration_truthful.required' => 'You must affirm the truthfulness of the information.',
                'declaration_truthful.in' => 'You must affirm the truthfulness of the information.',
                'declaration_update.required' => 'You must agree to inform the company of any changes.',
                'declaration_update.in' => 'You must agree to inform the company of any changes.',
                'has_question_j.required' => 'Please answer whether the Distributor has been referred.'
            ]);

            if ($validator->fails()) {
                return ['success' => false, 'error' => $validator->errors()->toArray(), 'status' => 422];
            }

            // Prepare data for saving
            $data = [];
            foreach ($questions as $question_key => $config) {
                $has_issue = $request->input($question_key, 0) == 1;
                $details = [];

                // Collect details for questions with detail fields
                if (isset($config['details_field'])) {
                    $details[$config['details_field']] = $request->input($config['details_field'], '');
                    // Set has_issue to true if details are non-empty
                    if (!empty($details[$config['details_field']])) {
                        $has_issue = true;
                    }
                } elseif (isset($config['details_fields'])) {
                    foreach ($config['details_fields'] as $field => $label) {
                        $details[$field] = $request->input($field, '');
                        // Set has_issue to true if any details field is non-empty
                        if (!empty($details[$field])) {
                            $has_issue = true;
                        }
                    }
                }

                // Only save non-empty details
                $details = array_filter($details, fn($value) => $value !== '');
                $details_json = !empty($details) ? json_encode($details) : null;

                // If has_issue is false, ensure details are null
                if (!$has_issue) {
                    $details_json = null;
                }

                $data[] = [
                    'application_id' => $application_id,
                    'question_key' => $question_key,
                    'has_issue' => $has_issue,
                    'details' => $details_json,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Delete existing declarations for this application
            DB::table('declarations')->where('application_id', $application_id)->delete();

            // Insert new declarations
            DB::table('declarations')->insert($data);

            DB::table('onboardings')
                ->where('id', $application_id)
                ->update([
                    'updated_at' => now()
                ]);

            $application = Onboarding::find($application_id);
            if ($application) {
                if ($application->current_progress_step < 8) {
                    $application->update(['current_progress_step' => 8]);
                }
            }

            DB::commit();
            return [
                'success' => true,
                'message' => 'Step 7 saved successfully!',
                'application_id' => $application_id,
                'current_step' => 8
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'error' => 'Failed to save step 7.'];
        }
    }

    private function saveStep8(Request $request, $user, $application_id)
    {
        // dd($request->all());
        $application = Onboarding::with([
            'territoryDetail',
            'regionDetail',
            'zoneDetail',
            'businessUnit',
            'entityDetails',
            'distributionDetail',
            'businessPlans',
            'financialInfo',
            'existingDistributorships',
            'bankDetail',
            'declarations'
        ])->findOrFail($application_id);

        // Verify ownership
        if ($user->emp_id !== $application->created_by) {
            return ['success' => false, 'error' => 'Unauthorized action.', 'status' => 403];
        }

        // Validate all required steps are completed (make existingDistributorships optional)
        $requiredSteps = [
            'territory' => !$application->territory,
            'entityDetails' => !$application->entityDetails,
            'distributionDetail' => !$application->distributionDetail,
            'businessPlans' => $application->businessPlans->isEmpty(),
            'financialInfo' => !$application->financialInfo,
            'bankDetail' => !$application->bankDetail,
            'declarations' => $application->declarations->isEmpty()
        ];

        $missingSteps = array_keys(array_filter($requiredSteps));

        if (!empty($missingSteps)) {
            return [
                'success' => false,
                'error' => 'Please complete all required steps before submitting.',
                'missing_steps' => $missingSteps,
                'status' => 422
            ];
        }

        DB::beginTransaction();

        try {
            if ($user->hasAnyRole(['Mis User', 'Admin', 'Super Admin', 'Mis Admin'])) {
                $application->update([
                    'status' => 'approved',
                    'current_approver_id' => $user->emp_id,
                    'approval_level' => 'MIS Auto Approval',
                    'updated_at' => now()
                ]);

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'Application auto-approved for MIS user!',
                    'application_id' => $application_id,
                    'current_step' => 8,
                    'redirect' => route('applications.show', $application_id)
                ];
            }

            if ($application->status === 'documents_pending' && $application->is_hierarchy_approved) {
                // This is a resubmission after MIS rejection - skip hierarchy, go directly to MIS
                $application->update([
                    'status' => 'mis_processing',
                    'current_approver_id' => null, // No approver needed for MIS processing
                    'approval_level' => 'MIS Resubmission',
                    'resubmitted_at' => now(),
                    'updated_at' => now()
                ]);

                // Clear previous MIS feedback since documents are updated
                $application->checkpoints()->delete();
                ApplicationAdditionalDocument::where('application_id', $application->id)->delete();

                $application->update(['mis_rejected_at' => null]);

                // Notify MIS team about resubmission
                $this->notifyMisTeamOfResubmission($application, $user);

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'Application resubmitted successfully! Documents sent back to MIS for verification.',
                    'application_id' => $application_id,
                    'current_step' => 8,
                    'redirect' => route('applications.show', $application_id),
                    'resubmission' => true,
                    'new_status' => 'mis_processing'
                ];
            }
            // Get approver information
            $creator = Employee::where('employee_id', $user->emp_id)->firstOrFail();

            // Get first approver (creator's reporting manager)
            $firstApprover = $creator->reportingManager;

            if (!$firstApprover) {
                throw new \Exception('No reporting manager assigned for this employee.');
            }

            // Set initial approval level based on first approver's designation
            // $approvalLevel = $this->getApprovalLevelFromDesignation($firstApprover->emp_designation);

            $application->update([
                'status' => 'initiated',
                'current_approver_id' => $firstApprover->employee_id,
                'approval_level' => $firstApprover->emp_designation,
                'is_hierarchy_approved' => false,
                'updated_at' => now()
            ]);

            try {
                Mail::to($firstApprover->emp_email)->send(new ApplicationSubmitted($application, $user, $firstApprover));
                // Log::info('Email notification sent to approver', [
                //     'application_id' => $application_id,
                //     'approver_email' => $firstApprover->emp_email
                // ]);
            } catch (\Exception $e) {
                // Log email failure but don't rollback the transaction
                // Log::error('Failed to send email notification', [
                //     'application_id' => $application_id,
                //     'approver_email' => $firstApprover->emp_email,
                //     'error' => $e->getMessage()
                // ]);
            }

            DB::commit();

            // TODO: Send notification to first approver
            return [
                'success' => true,
                'message' => 'Application submitted successfully!',
                'application_id' => $application_id,
                'current_step' => 8,
                'redirect' => route('applications.show', $application_id)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error("Error in saveStep8: " . $e->getMessage(), [
            //     'application_id' => $application_id,
            //     'user_id' => $user->id
            // ]);
            return [
                'success' => false,
                'error' => 'Failed to submit application. ' . $e->getMessage()
            ];
        }
    }

    private function notifyMisTeamOfResubmission($application, $user)
    {
        try {
            // Get MIS team members
            $misTeam = Employee::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Mis User', 'Mis Admin']);
            })->where('is_active', 1)->get();

            foreach ($misTeam as $misMember) {
                if ($misMember->emp_email) {
                    Mail::to($misMember->emp_email)->send(new DocumentResubmission($application, $user));
                }
            }

            \Log::info('MIS resubmission notification sent', [
                'application_id' => $application->id,
                'user_id' => $user->id,
                'mis_team_count' => $misTeam->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send MIS resubmission notification', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function getApprovalLevelFromDesignation(string $designation): string
    {
        $designation = strtolower($designation);

        if (str_contains($designation, 'regional')) return 'rbm';
        if (str_contains($designation, 'zonal')) return 'zbm';
        if (str_contains($designation, 'general')) return 'gm';

        return 'unknown';
    }



    public function destroy(Onboarding $application)
    {
        $user = Auth::user();
        if ($application->status !== 'draft' || (
            $application->created_by !== $user->emp_id &&
            !$user->hasAnyRole(['Admin', 'Mis Admin', 'Super Admin'])
        )) {
            abort(403, 'Unauthorized action');
        }

        // Load relationships to log existing data
        $application->load([
            'entityDetails',
            'distributionDetail',
            'businessPlans',
            'financialInfo',
            'existingDistributorships',
            'bankDetail',
            'declarations',
        ]);

        // Delete the application (cascading deletes handled in Onboarding model)
        $application->delete();

        return redirect()->route('applications.index')
            ->with('success', 'Application and related data deleted successfully');
    }

    // private function sendNotification($application_id, $approver_id, $action)
    // {
    //     $application = Onboarding::find($application_id);
    //     $recipient = Employee::find($approver_id);

    //     if ($recipient) {
    //         Mail::to($recipient->emp_email)->send(new ApplicationActionNotification($application, $action));
    //     }

    //     // CC Business Head for GM approval
    //     if ($application->approval_level === 3 && $action === 'submitted') {
    //         $businessHead = Employee::where('emp_designation', 'Business Head')->first();
    //         if ($businessHead) {
    //             Mail::to($businessHead->emp_email)->send(new ApplicationActionNotification($application, $action));
    //         }
    //     }
    // }

    private function generateUniqueApplicationCode()
    {
        // Implement your unique application code generation logic here
        return 'APP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    public function getDistricts($state_id)
    {
        $districts = DB::table('core_district')
            ->where('state_id', $state_id)
            ->where('is_active', 1)
            ->orderBy('district_name')
            ->get(['id', 'district_name']);

        return response()->json($districts);
    }


    public function removeDocument(Request $request, $applicationId)
    {
        DB::beginTransaction();
        try {
            $type = $request->input('type');
            if (!$type) {
                return response()->json(['success' => false, 'error' => 'Document type is missing.'], 400);
            }

            $validTypes = [
                'business_entity',
                'ownership',
                'pan',
                'address',
                'bank',
                'photo',
                'shop_photo',
                'gst',
                'seed_license',
                'other'
            ];
            if (!in_array($type, $validTypes)) {
                return response()->json(['success' => false, 'error' => 'Invalid document type.'], 400);
            }

            $application = DB::table('onboardings')->where('id', $applicationId)->first();
            if (!$application) {
                return response()->json(['success' => false, 'error' => 'Application not found.'], 404);
            }

            $document = Document::where('application_id', $applicationId)
                ->where('type', $type)
                ->first();

            if ($document) {
                Storage::disk('public')->delete($document->path);
                $document->delete();
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Document removed successfully']);
            }

            DB::commit();
            return response()->json(['success' => false, 'error' => 'Document not found']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => 'Failed to remove document: ' . $e->getMessage()], 500);
        }
    }

    public function preview($id)
    {

        try {
            // Load application with all necessary columns
            $application = Onboarding::select([
                'id',
                'application_code',
                'territory',
                'crop_vertical',
                'region',
                'zone',
                'district',
                'state',
                'status',
                'business_unit'
            ])->with([
                'businessUnit:id,business_unit_name',
                'zoneDetail:id,zone_name',
                'regionDetail:id,region_name',
                'territoryDetail:id,territory_name',
                'entityDetails' => function ($query) {
                    $query->select([
                        'id',
                        'application_id',
                        'establishment_name',
                        'entity_type',
                        'business_address',
                        'house_no',
                        'landmark',
                        'city',
                        'state_id',
                        'district_id',
                        'country_id',
                        'pincode',
                        'mobile',
                        'email',
                        'pan_number',
                        'gst_applicable',
                        'gst_number',
                        'seed_license',
                        'additional_data',
                        'documents_data'
                    ]);
                },
                'distributionDetail' => function ($query) {
                    $query->select([
                        'id',
                        'application_id',
                        'area_covered',
                        'appointment_type',
                        'replacement_reason',
                        'outstanding_recovery',
                        'previous_firm_name',
                        'previous_firm_code',
                        'earlier_distributor'
                    ]);
                },
                'bankDetail' => function ($query) {
                    $query->select([
                        'id',
                        'application_id',
                        'financial_status',
                        'retailer_count',
                        'bank_name',
                        'account_holder',
                        'account_number',
                        'ifsc_code',
                        'account_type',
                        'relationship_duration',
                        'od_limit',
                        'od_security'
                    ]);
                },
                'financialInfo' => function ($query) {
                    $query->select([
                        'id',
                        'application_id',
                        'net_worth',
                        'shop_ownership',
                        'godown_area',
                        'years_in_business',
                        'annual_turnover'
                    ]);
                },
                'businessPlans' => function ($query) {
                    $query->select('id', 'application_id', 'crop', 'yearly_targets')->limit(5);
                },
                'existingDistributorships' => function ($query) {
                    $query->select('id', 'application_id', 'company_name');
                },
                'declarations' => function ($query) {
                    $query->select('id', 'application_id', 'question_key', 'has_issue', 'details');
                }
            ])->findOrFail($id);

            // Pre-fetch lookup data
            $states = DB::table('core_state')->select('id', 'state_name')->get()->keyBy('id');
            $districts = DB::table('core_district')->select('id', 'district_name')->get()->keyBy('id');
            $countries = DB::table('core_country')->select('id', 'country_name')->get()->keyBy('id');
            $years = Year::select('id', 'period')->get()->keyBy('id');

            return response()->view('components.form-sections.preview-pdf', [
                'application' => $application,
                'years' => $years,
                'states' => $states,
                'districts' => $districts,
                'countries' => $countries
            ])->header('Content-Security-Policy', "frame-ancestors 'self'");
        } catch (\Exception $e) {
            return response("Error generating preview", 500);
        }
    }

    public function downloadApplicationPdf($id)
    {
        try {
            $application = Onboarding::select([
                'id',
                'application_code',
                'territory',
                'crop_vertical',
                'region',
                'zone',
                'district',
                'state',
                'status',
                'business_unit'
            ])->with([
                'businessUnit:id,business_unit_name',
                'zoneDetail:id,zone_name',
                'regionDetail:id,region_name',
                'territoryDetail:id,territory_name',
                'entityDetails' => function ($query) {
                    $query->select([
                        'id',
                        'application_id',
                        'establishment_name',
                        'entity_type',
                        'business_address',
                        'house_no',
                        'landmark',
                        'city',
                        'state_id',
                        'district_id',
                        'country_id',
                        'pincode',
                        'mobile',
                        'email',
                        'pan_number',
                        'gst_applicable',
                        'gst_number',
                        'seed_license',
                        'additional_data',
                        'documents_data'
                    ]);
                },
                'distributionDetail' => function ($query) {
                    $query->select([
                        'id',
                        'application_id',
                        'area_covered',
                        'appointment_type',
                        'replacement_reason',
                        'outstanding_recovery',
                        'previous_firm_name',
                        'previous_firm_code',
                        'earlier_distributor'
                    ]);
                },
                'bankDetail' => function ($query) {
                    $query->select([
                        'id',
                        'application_id',
                        'financial_status',
                        'retailer_count',
                        'bank_name',
                        'account_holder',
                        'account_number',
                        'ifsc_code',
                        'account_type',
                        'relationship_duration',
                        'od_limit',
                        'od_security'
                    ]);
                },
                'financialInfo' => function ($query) {
                    $query->select([
                        'id',
                        'application_id',
                        'net_worth',
                        'shop_ownership',
                        'godown_area',
                        'years_in_business',
                        'annual_turnover'
                    ]);
                },
                'businessPlans' => function ($query) {
                    $query->select('id', 'application_id', 'crop', 'yearly_targets')->limit(5);
                },
                'existingDistributorships' => function ($query) {
                    $query->select('id', 'application_id', 'company_name');
                },
                'declarations' => function ($query) {
                    $query->select('id', 'application_id', 'question_key', 'has_issue', 'details');
                }
            ])->findOrFail($id);

            $states = DB::table('core_state')->select('id', 'state_name')->get()->keyBy('id');
            $districts = DB::table('core_district')->select('id', 'district_name')->get()->keyBy('id');
            $countries = DB::table('core_country')->select('id', 'country_name')->get()->keyBy('id');
            $years = Year::select('id', 'period')->get()->keyBy('id');

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('components.form-sections.preview-pdf', [
                'application' => $application,
                'years' => $years,
                'states' => $states,
                'districts' => $districts,
                'countries' => $countries
            ])->setPaper('a4', 'portrait');

            return $pdf->download("Distributor_Application_{$application->application_code}.pdf");
        } catch (\Exception $e) {
            return response("Error generating PDF", 500);
        }
    }

    public function pendingDocuments()
    {
        $user = Auth::user();

        // Only show for user's own pending applications
        $applications = Onboarding::where('status', 'documents_pending')
            ->where('created_by', $user->emp_id)
            ->with(['entityDetails', 'checkpoints', 'additionalDocs' => function ($query) {
                $query->where('status', 'pending');
            }, 'authorizedPersons'])
            ->orderBy('mis_rejected_at', 'desc')
            ->get();

        if ($applications->isEmpty()) {
            return view('applications.pending-documents', compact('applications'))
                ->with('message', 'No pending documents at the moment. All your applications are up to date!');
        }

        return view('applications.pending-documents', compact('applications'));
    }

    public function uploadPendingDocuments(Request $request, Onboarding $application)
    {
        $user = Auth::user();

        if ($user->emp_id !== $application->created_by) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($application->status !== 'documents_pending') {
            return response()->json(['success' => false, 'message' => 'Not pending'], 400);
        }

        Log::info('Pending upload request', $request->all());

        // Extract only documents that actually have files
        $documentsWithFiles = [];
        if (isset($request->documents) && is_array($request->documents)) {
            foreach ($request->documents as $index => $doc) {
                if ($request->hasFile("documents.{$index}.file")) {
                    $documentsWithFiles[] = [
                        'index' => $index,
                        'type' => $doc['type'],
                        'item_name' => $doc['item_name'],
                        'checkpoint_name' => $doc['checkpoint_name'] ?? null,
                        'file' => $request->file("documents.{$index}.file")
                    ];
                }
            }
        }

        if (empty($documentsWithFiles)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid files found. Please select files and try again.'
            ], 422);
        }

        // Validate request
        $request->validate([
            'documents' => 'required|array',
            'documents.*.type' => 'required|string|in:main_documents,authorized_persons,additional_documents',
            'documents.*.item_name' => 'required|string|max:255',
            'documents.*.checkpoint_name' => 'required|string|max:255',
            'documents.*.file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $entityDetails = $application->entityDetails;
            if (!$entityDetails) {
                return response()->json(['success' => false, 'message' => 'Entity not found'], 404);
            }

            // Count pending checkpoints for logging
            $pendingCheckpoints = $application->checkpoints()
                ->where('status', 'not_verified')
                ->pluck('checkpoint_name')
                ->toArray();
            $pendingAdditionalDocs = $application->additionalDocs()
                ->where('status', 'pending')
                ->pluck('id')
                ->map(fn($id) => "additional_doc_{$id}")
                ->toArray();
            $totalPending = count($pendingCheckpoints) + count($pendingAdditionalDocs);

            if (count($documentsWithFiles) < $totalPending) {
                Log::warning('Fewer documents uploaded than pending', [
                    'application_id' => $application->id,
                    'uploaded_count' => count($documentsWithFiles),
                    'pending_count' => $totalPending,
                    'missing_checkpoints' => array_diff(
                        array_merge($pendingCheckpoints, $pendingAdditionalDocs),
                        array_column($documentsWithFiles, 'checkpoint_name')
                    ),
                ]);
            }

            $uploadedCount = 0;
            $updatedFields = [];

            // Define document types configuration
            $documentTypes = [
                'main_document_pan' => [
                    'column' => 'pan_path',
                    'prefix' => 'pandoc',
                    's3_folder' => 'pan',
                    'table' => 'entity_details',
                ],
                'main_document_gst' => [
                    'column' => 'gst_path',
                    'prefix' => 'gstdoc',
                    's3_folder' => 'gst',
                    'table' => 'entity_details',
                ],
                'main_document_seed_license' => [
                    'column' => 'seed_license_path',
                    'prefix' => 'seeddoc',
                    's3_folder' => 'seed_license',
                    'table' => 'entity_details',
                ],
                'main_document_bank' => [
                    'column' => 'bank_document_path',
                    'prefix' => 'bankdoc',
                    's3_folder' => 'bank',
                    'table' => 'entity_details',
                ],
                'authorized_letter' => [
                    'column' => 'letter_path',
                    'prefix' => 'auth_letter',
                    's3_folder' => 'authorized_persons',
                    'table' => 'authorized_persons',
                ],
                'authorized_aadhar' => [
                    'column' => 'aadhar_path',
                    'prefix' => 'auth_aadhar',
                    's3_folder' => 'authorized_persons',
                    'table' => 'authorized_persons',
                ],
                'additional_document' => [
                    'column' => 'path',
                    'prefix' => 'additionaldoc',
                    's3_folder' => 'additional_documents',
                    'table' => 'application_additional_documents',
                ],
            ];

            foreach ($documentsWithFiles as $docInfo) {
                $frontendType = $docInfo['type'];
                $itemName = $docInfo['item_name'];
                $checkpointName = $docInfo['checkpoint_name'];
                $file = $docInfo['file'];

                // Extract document type
                $docType = $this->extractDocumentType($checkpointName, $frontendType);
                $config = $documentTypes[$docType] ?? null;

                if (!$config) {
                    Log::warning('Invalid document type or checkpoint', [
                        'checkpoint_name' => $checkpointName,
                        'frontend_type' => $frontendType,
                        'doc_type' => $docType,
                    ]);
                    continue;
                }

                // Check GST applicability for GST document
                if ($checkpointName === 'main_document_gst' && $entityDetails->gst_applicable === 'no') {
                    Log::warning('GST document upload attempted when GST is not applicable', [
                        'application_id' => $application->id,
                        'checkpoint_name' => $checkpointName,
                    ]);
                    continue;
                }

                // Generate filename
                $filename = "{$config['prefix']}_" . time() . "_{$application->id}.{$file->getClientOriginalExtension()}";
                $s3Path = "Connect/Distributor/{$config['s3_folder']}/{$filename}";

                // Delete old file if it exists
                if ($config['table'] === 'entity_details' && $entityDetails->{$config['column']}) {
                    $oldPath = "Connect/Distributor/{$config['s3_folder']}/{$entityDetails->{$config['column']}}";
                    if (Storage::disk('s3')->exists($oldPath)) {
                        Storage::disk('s3')->delete($oldPath);
                        Log::info('Deleted old file from S3', ['old_path' => $oldPath]);
                    }
                } elseif ($config['table'] === 'authorized_persons') {
                    if (preg_match('/authorized_(letter|aadhar)_(\d+)/', $checkpointName, $matches)) {
                        $personIndex = $matches[2];
                        $person = $application->authorizedPersons()->skip($personIndex)->first();
                        if ($person && $person->{$config['column']}) {
                            $oldPath = "Connect/Distributor/{$config['s3_folder']}/{$person->{$config['column']}}";
                            if (Storage::disk('s3')->exists($oldPath)) {
                                Storage::disk('s3')->delete($oldPath);
                                Log::info('Deleted old file from S3', ['old_path' => $oldPath]);
                            }
                        }
                    }
                } elseif ($config['table'] === 'application_additional_documents') {
                    if ($frontendType === 'additional_documents' && str_starts_with($checkpointName, 'additional_doc_')) {
                        $docId = str_replace('additional_doc_', '', $checkpointName);
                        $oldDoc = ApplicationAdditionalDocument::find($docId);
                        if ($oldDoc && $oldDoc->path) {
                            $oldPath = "Connect/Distributor/{$config['s3_folder']}/{$oldDoc->path}";
                            if (Storage::disk('s3')->exists($oldPath)) {
                                Storage::disk('s3')->delete($oldPath);
                                Log::info('Deleted old file from S3', ['old_path' => $oldPath]);
                            }
                            $oldDoc->delete();
                        }
                    }
                }

                // Upload new file to S3
                Storage::disk('s3')->put($s3Path, file_get_contents($file->getRealPath()), 'public');
                Log::info('File uploaded successfully', [
                    'filename' => $filename,
                    's3_path' => $s3Path,
                    'doc_type' => $docType,
                    'item_name' => $itemName,
                    'checkpoint_name' => $checkpointName
                ]);

                // Update the relevant table
                if ($config['table'] === 'entity_details') {
                    $entityDetails->{$config['column']} = $filename;
                    $updatedFields[] = $itemName;
                    $uploadedCount++;
                } elseif ($config['table'] === 'authorized_persons') {
                    if (preg_match('/authorized_(letter|aadhar)_(\d+)/', $checkpointName, $matches)) {
                        $personIndex = $matches[2];
                        $person = $application->authorizedPersons()->skip($personIndex)->first();
                        if ($person) {
                            $person->{$config['column']} = $filename;
                            $person->save();
                            $updatedFields[] = $itemName;
                            $uploadedCount++;
                        } else {
                            Log::warning('Authorized person not found', [
                                'checkpoint_name' => $checkpointName,
                                'person_index' => $personIndex,
                            ]);
                            continue;
                        }
                    }
                } elseif ($config['table'] === 'application_additional_documents') {
                    $docModel = ApplicationAdditionalDocument::create([
                        'application_id' => $application->id,
                        'document_name' => $itemName,
                        'remark' => "Re-uploaded: {$itemName} - " . now()->toDateTimeString(),
                        'path' => $filename,
                        'submitted_by' => $user->emp_id,
                        'created_at' => now(),
                        'status' => 'pending'
                    ]);
                    $updatedFields[] = $itemName;
                    $uploadedCount++;
                }
            }

            // Save entity_details changes
            $entityDetails->save();

            // Update application status and timestamps
            $newStatus = $uploadedCount > 0 ? 'documents_resubmitted' : 'documents_pending';
            $application->update([
                'status' => $newStatus,
                'resubmitted_at' => $uploadedCount > 0 ? now() : $application->resubmitted_at,
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully uploaded {$uploadedCount} document(s)! MIS will review your updates.",
                'uploaded_count' => $uploadedCount,
                'files_uploaded' => $updatedFields,
                'status_updated_to' => $newStatus,
                'redirect' => route('applications.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Upload failed: ' . $e->getMessage(), [
                'application_id' => $application->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function extractDocumentType($checkpointName, $frontendType)
    {
        if ($frontendType === 'main_documents' && $checkpointName) {
            return $checkpointName; // e.g., main_document_gst
        }

        if ($frontendType === 'authorized_persons' && $checkpointName) {
            if (preg_match('/authorized_(letter|aadhar)_(\d+)/', $checkpointName, $matches)) {
                return "authorized_{$matches[1]}"; // e.g., authorized_letter, authorized_aadhar
            }
        }

        if ($frontendType === 'additional_documents' && $checkpointName) {
            return 'additional_document';
        }

        return $frontendType;
    }

    private function getDocumentFolder($typeOrDocType)
    {
        return match ($typeOrDocType) {
            'main_document_pan', 'pan' => 'pan',
            'main_document_gst', 'gst' => 'gst',
            'main_document_seed_license', 'seed_license' => 'seed_license',
            'main_document_bank', 'bank' => 'bank',
            'authorized_letter', 'authorized_aadhar', 'auth_letter', 'auth_aadhar' => 'authorized_persons',
            'additional_document', 'additional_documents' => 'additional_documents',
            default => 'documents'
        };
    }

    private function getDocumentDetails($entityDetails, $docType)
    {
        return match ($docType) {
            'main_document_pan' => ['pan_number' => $entityDetails->pan_number],
            'main_document_gst' => ['gst_number' => $entityDetails->gst_number, 'gst_validity' => $entityDetails->gst_validity],
            'main_document_seed_license' => [
                'seed_license_number' => $entityDetails->seed_license,
                'seed_license_validity' => $entityDetails->seed_license_validity
            ],
            'main_document_bank' => [
                'bank_name' => $entityDetails->bank_name,
                'account_holder' => $entityDetails->account_holder,
                'account_number' => $entityDetails->account_number,
                'ifsc_code' => $entityDetails->ifsc_code
            ],
            'authorized_letter', 'authorized_aadhar' => [],
            default => ['document_name' => 'Document']
        };
    }








}
