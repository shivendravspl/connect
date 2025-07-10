<?php

namespace App\Http\Controllers;

use App\Models\DistributorOnboarding;
use App\Models\EntityDetails;
use App\Models\DistributionDetail;
use App\Models\BankDetail;
use App\Models\BusinessPlan;
use App\Models\FinancialInfo;
use App\Models\ExistingDistributorship;
use App\Models\Document;
use App\Models\Employee;
use App\Models\ApprovalLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Notifications\ApplicationSubmitted;
use App\Notifications\ApplicationApprovalRequired;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use App\Models\Year;
use Illuminate\Validation\Rule;

class DistributorOnboardingController extends Controller
{


    public function index()
    {
        $empId = Auth::user()->emp_id;

        $applications = DistributorOnboarding::where('created_by', $empId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('applications.index', compact('applications'));
    }

    function getAssociatedBusinessUnitList($employeeId)
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['Super Admin', 'Admin', 'SP Admin', 'Management'])) {
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

    public function create($application_id = null, $step = 1)
    {
        $user = Auth::user();
        $territory_list = [];
        $zone_list = [];
        $region_list = [];
        $preselected = [];
        $bu_list = [];

        $application = $application_id ? DistributorOnboarding::with([
            'territoryDetail',
            'regionDetail',
            'zoneDetail',
            'businessUnit',
            'entityDetails',
            'distributionDetail',
            'businessPlan',
            'financialInfo',
            'existingDistributorships',
            'bankDetail',
            'declarations'
        ])->findOrFail($application_id) : new DistributorOnboarding();

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
                5 => !$application->businessPlan,
                6 => !$application->financialInfo,
                7 => !$application->existingDistributorships->count(),
                8 => !$application->bankDetail,
                9 => !$application->declarations->count()
            ];
            for ($i = 2; $i <= $step; $i++) {
                if (isset($requiredSteps[$i]) && $requiredSteps[$i]) {
                    return redirect()->route('applications.create', ['step' => $i - 1, 'application_id' => $application_id])
                        ->with('error', 'Please complete all previous steps.');
                }
            }
        }

        if ($user->emp_id) {
            $employee = DB::table('core_employee')->where('id', $user->emp_id)->first();

            if ($employee) {
                $bu_list = $this->getAssociatedBusinessUnitList($user->emp_id);
                // Preselect user's business unit
                if ($employee->bu > 0) {
                    $preselected['bu'] = $employee->bu;
                }
                if ($employee->emp_vertical == '2') {
                    $crop_type = ['2' => 'Veg Crop'];
                    $preselected['crop_vertical'] = '2'; // Preselect Veg Crop
                } elseif ($employee->emp_vertical == '1') {
                    $crop_type = ['1' => 'Field Crop'];
                    $preselected['crop_vertical'] = '1'; // Preselect Field Crop
                } else {
                    $crop_type = [];
                }
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
                }
                // Case 2: territory = 0, region = 0, zone > 0
                elseif ($employee->territory == 0 && $employee->region == 0 && $employee->zone > 0) {
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

                    // Preselect the first available territory if only one exists
                    if (count($territory_list) === 1) {
                        $preselected['territory'] = array_key_first($territory_list);
                    }
                }
                // Case 3: territory = 0, region > 0
                elseif ($employee->territory == 0 && $employee->region > 0) {
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

                    // Preselect the first available territory if only one exists
                    if (count($territory_list) === 1) {
                        $preselected['territory'] = array_key_first($territory_list);
                    }
                }
                // Case 4: territory > 0
                elseif ($employee->territory > 0) {
                    $territory = DB::table('core_territory')
                        ->where('id', $employee->territory)
                        ->first();

                    if ($territory) {
                        $territory_list = [$territory->id => $territory->territory_name];
                        $preselected['territory'] = $territory->id;
                    }
                }

                // If we have a preselected territory, get its regions and zones
                if (isset($preselected['territory'])) {
                    $territoryData = $this->getTerritoryData($preselected['territory']);
                    $region_list = $territoryData['regions'] ?? [];
                    $zone_list = $territoryData['zones'] ?? [];
                    $bu_list = $territoryData['businessUnits'] ?? [];
                    //dd($bu_list); 
                    // Preselect first region/zone if available
                    if (!empty($region_list)) {
                        $preselected['region'] = array_key_first($region_list);
                    }
                    if (!empty($zone_list)) {
                        $preselected['zone'] = array_key_first($zone_list);
                    }
                    if (!empty($bu_list)) {
                        $preselected['bu'] = array_key_first($bu_list);
                    }
                }
            }

            $states = DB::table('core_state')
                ->where('is_active', 1)
                ->orderBy('state_name')
                ->get();

            $currentStep = $step;
        }
        //dd($zone_list);
        return view('applications.create', compact(
            'application',
            'bu_list',
            'zone_list',
            'region_list',
            'territory_list',
            'preselected',
            'crop_type',
            'states',
            'currentStep'
        ));
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
            b.business_unit_name
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
        WHERE 
            rtm.territory_id = ?
    ", [$territoryId]);

        $data = [
            'regions' => [],
            'zones' => [],
            'businessUnits' => []
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
        }

        return $data;
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $this->validateApplication($request);

            // Create main application
            $application = Onboarding::create([
                'application_code' => 'DIST-' . strtoupper(uniqid()),
                'territory' => $validated['territory'],
                'crop_vertical' => $validated['crop_vertical'],
                'zone' => $validated['zone'],
                'district' => $validated['district'],
                'state' => $validated['state'],
                'status' => 'submitted',
                'created_by' => Auth::id(),
            ]);

            // Create related records
            $this->createRelatedRecords($application, $validated);

            // Upload documents
            $this->handleDocumentUploads($application, $request);

            // Route to first approver (RBM)
            $this->routeToApprover($application, 'rbm');

            DB::commit();

            Auth::user()->notify(new ApplicationSubmitted($application));

            return redirect()->route('applications.show', $application)
                ->with('success', 'Application submitted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error submitting application: ' . $e->getMessage());
        }
    }

    public function show(Onboarding $application)
    {
        // $this->authorize('view', $application);

        $application->load([
            'entityDetails',
            'distributionDetail',
            'bankDetail',
            'businessPlan',
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
        if (!$user->emp_id || $user->emp_id !== $application->created_by) {
            abort(403, 'You are not authorized to edit this application.');
        }

        if (!in_array($application->status, ['draft', 'reverted'])) {
            return redirect()->route('applications.show', $application)
                ->with('error', 'You can only edit draft or reverted applications');
        }

        // $application->load([
        //     'entityDetails',
        //     'DistributionDetail',
        //     'bankDetail',
        //     'BusinessPlan',
        //     'financialInfo',
        //     'existingDistributorships',
        //     'declarations',
        //     'documents'
        // ]);
        $application->load('documents');

        $territory_list = [];
        $zone_list = [];
        $region_list = [];
        $bu_list = [];
        $preselected = [];

        if ($user->emp_id) {
            $employee = DB::table('core_employee')->where('id', $user->emp_id)->first();

            if ($employee) {
                // Crop vertical logic
                if ($employee->emp_vertical == '2') {
                    $crop_type = ['2' => 'Veg Crop'];
                    $preselected['crop_vertical'] = $application->crop_vertical ?? '2';
                } elseif ($employee->emp_vertical == '1') {
                    $crop_type = ['1' => 'Field Crop'];
                    $preselected['crop_vertical'] = $application->crop_vertical ?? '1';
                } else {
                    $crop_type = [];
                }

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
                }

                // Territory/region/zone logic
                elseif ($employee->territory == 0 && $employee->region == 0 && $employee->zone > 0) {
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

                // Fetch regions and zones
                if (isset($preselected['territory']) || $application->territory) {
                    $territoryData = $this->getTerritoryData($application->territory ?? $preselected['territory']);
                    $region_list = $territoryData['regions'] ?? [];
                    $zone_list = $territoryData['zones'] ?? [];
                    $bu_list = $territoryData['businessUnits'] ?? [];

                    if (!empty($region_list)) {
                        $preselected['region'] = $application->region ?? array_key_first($region_list);
                    }
                    if (!empty($zone_list)) {
                        $preselected['zone'] = $application->zone ?? array_key_first($zone_list);
                    }
                    if (!empty($bu_list)) {
                        $preselected['bu'] = $application->bu ?? array_key_first($bu_list);
                    }
                }

                // Fetch states
                $states = Cache::remember('active_states', 60 * 60, function () {
                    return DB::table('core_state')
                        ->where('is_active', 1)
                        ->orderBy('state_name')
                        ->get(['id', 'state_name']);
                });
            }
        }
        //dd($preselected);
        if ($step == 9) {
            return view('applications.review-submit', compact('application'));
        }
        return view('applications.edit', compact(
            'application',
            'bu_list',
            'zone_list',
            'region_list',
            'territory_list',
            'preselected',
            'crop_type',
            'states'
        ));
    }

    public function update(Request $request, DistributorOnboarding $application)
    {
        // $this->authorize('update', $application);

        DB::beginTransaction();

        try {
            $validated = $this->validateApplication($request);

            // Update main application
            $application->update([
                'territory' => $validated['territory'],
                'crop_vertical' => $validated['crop_vertical'],
                'zone' => $validated['zone'],
                'district' => $validated['district'],
                'state' => $validated['state'],
                'status' => $request->has('submit') ? 'submitted' : 'draft',
            ]);

            // Update related records
            $this->updateRelatedRecords($application, $validated);

            // Handle document updates
            $this->handleDocumentUploads($application, $request);

            if ($request->has('submit')) {
                $this->routeToApprover($application, 'rbm');
                Auth::user()->notify(new ApplicationSubmitted($application));
            }

            DB::commit();

            return redirect()->route('applications.show', $application)
                ->with('success', 'Application updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error updating application: " . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Error updating application: ' . $e->getMessage());
        }
    }

    protected function updateRelatedRecords($application, $data)
    {
        // Entity Details
        $entity_type = $data['entity_type'];
        $additionalData = [
            'state' => $data['state'] ?? null,
            'tan_number' => $data['tan_number'] ?? null,
            'partners' => [], // Initialize partners
            'authorized_persons' => [],
        ];

        if ($entity_type === 'sole_proprietorship') {
            $additionalData['proprietor'] = [
                'name' => $data['proprietor_name'] ?? null,
                'dob' => $data['proprietor_dob'] ?? null,
                'father_name' => $data['proprietor_father_name'] ?? null,
                'address' => $data['proprietor_address'] ?? null,
                'pincode' => $data['proprietor_pincode'] ?? null,
                'country' => $data['proprietor_country'] ?? null,
            ];
        } elseif ($entity_type === 'partnership') {
            $partners = [];
            if (isset($data['partner_name']) && is_array($data['partner_name'])) {
                foreach ($data['partner_name'] as $index => $name) {
                    if (!empty($name)) {
                        $partners[] = [
                            'name' => $name,
                            'father_name' => $data['partner_father_name'][$index] ?? null,
                            'contact' => $data['partner_contact'][$index] ?? null,
                            'email' => $data['partner_email'][$index] ?? null,
                            'address' => $data['partner_address'][$index] ?? null,
                        ];
                    }
                }
            }
            $additionalData['partners'] = $partners;
        } elseif ($entity_type === 'llp') {
            $additionalData['llp'] = [
                'llpin_number' => $data['llpin_number'] ?? null,
                'incorporation_date' => $data['llp_incorporation_date'] ?? null,
            ];
            $partners = [];
            if (isset($data['llp_partner_name']) && is_array($data['llp_partner_name'])) {
                foreach ($data['llp_partner_name'] as $index => $name) {
                    if (!empty($name)) {
                        $partners[] = [
                            'name' => $name,
                            'dpin_number' => $data['llp_partner_dpin'][$index] ?? null,
                            'contact' => $data['llp_partner_contact'][$index] ?? null,
                            'address' => $data['llp_partner_address'][$index] ?? null,
                        ];
                    }
                }
            }
            $additionalData['partners'] = $partners;
        } elseif (in_array($entity_type, ['private_company', 'public_company'])) {
            $additionalData['company'] = [
                'cin_number' => $data['cin_number'] ?? null,
                'incorporation_date' => $data['incorporation_date'] ?? null,
            ];
            $partners = [];
            if (isset($data['director_name']) && is_array($data['director_name'])) {
                foreach ($data['director_name'] as $index => $name) {
                    if (!empty($name)) {
                        $partners[] = [
                            'name' => $name,
                            'din_number' => $data['director_din'][$index] ?? null,
                            'contact' => $data['director_contact'][$index] ?? null,
                            'address' => $data['director_address'][$index] ?? null,
                        ];
                    }
                }
            }
            $additionalData['partners'] = $partners;
        } elseif ($entity_type === 'cooperative_society') {
            $additionalData['cooperative'] = [
                'reg_number' => $data['cooperative_reg_number'] ?? null,
                'reg_date' => $data['cooperative_reg_date'] ?? null,
            ];
            $partners = [];
            if (isset($data['committee_name']) && is_array($data['committee_name'])) {
                foreach ($data['committee_name'] as $index => $name) {
                    if (!empty($name)) {
                        $partners[] = [
                            'name' => $name,
                            'designation' => $data['committee_designation'][$index] ?? null,
                            'contact' => $data['committee_contact'][$index] ?? null,
                            'address' => $data['committee_address'][$index] ?? null,
                        ];
                    }
                }
            }
            $additionalData['partners'] = $partners;
        } elseif ($entity_type === 'trust') {
            $additionalData['trust'] = [
                'reg_number' => $data['trust_reg_number'] ?? null,
                'reg_date' => $data['trust_reg_date'] ?? null,
            ];
            $partners = [];
            if (isset($data['trustee_name']) && is_array($data['trustee_name'])) {
                foreach ($data['trustee_name'] as $index => $name) {
                    if (!empty($name)) {
                        $partners[] = [
                            'name' => $name,
                            'designation' => $data['trustee_designation'][$index] ?? null,
                            'contact' => $data['trustee_contact'][$index] ?? null,
                            'address' => $data['trustee_address'][$index] ?? null,
                        ];
                    }
                }
            }
            $additionalData['partners'] = $partners;
        }

        // Authorized persons
        if (isset($data['auth_person_name']) && is_array($data['auth_person_name'])) {
            foreach ($data['auth_person_name'] as $index => $name) {
                if (!empty($name)) {
                    $additionalData['authorized_persons'][] = [
                        'name' => $name,
                        'contact' => $data['auth_person_contact'][$index] ?? null,
                        'email' => $data['auth_person_email'][$index] ?? null,
                        'address' => $data['auth_person_address'][$index] ?? null,
                        'relation' => $data['auth_person_relation'][$index] ?? null,
                    ];
                }
            }
        }

        // Clean up additional_data by removing irrelevant fields
        $validKeys = ['state', 'tan_number', 'authorized_persons'];
        if ($entity_type === 'sole_proprietorship') {
            $validKeys[] = 'proprietor';
        } elseif ($entity_type === 'partnership') {
            $validKeys[] = 'partners';
        } elseif ($entity_type === 'llp') {
            $validKeys[] = 'llp';
            $validKeys[] = 'partners';
        } elseif (in_array($entity_type, ['private_company', 'public_company'])) {
            $validKeys[] = 'company';
            $validKeys[] = 'partners';
        } elseif ($entity_type === 'cooperative_society') {
            $validKeys[] = 'cooperative';
            $validKeys[] = 'partners';
        } elseif ($entity_type === 'trust') {
            $validKeys[] = 'trust';
            $validKeys[] = 'partners';
        }
        $additionalData = array_intersect_key($additionalData, array_flip($validKeys));

        // Update entity details
        $application->entityDetails()->update([
            'establishment_name' => $data['establishment_name'],
            'entity_type' => $data['entity_type'],
            'business_address' => $data['business_address'],
            'city' => $data['city'],
            'house_no' => $data['house_no'],
            'landmark' => $data['landmark'],
            'state_id' => $data['state_id'],
            'district_id' => $data['district_id'],
            'country_id' => $data['country_id'],
            'pincode' => $data['pincode'],
            'mobile' => $data['mobile'],
            'email' => $data['email'],
            'pan_number' => $data['pan_number'],
            'seed_license' => $data['seed_license'] ?? null,
            'additional_data' => $additionalData,
        ]);

        // Bank Details
        $application->bankDetails()->update([
            'financial_status' => ucwords(strtolower($data['financial_status'])),
            'retailer_count' => $data['retailer_count'],
            'bank_name' => $data['bank_name'],
            'account_holder' => $data['account_holder'],
            'account_number' => $data['account_number'],
            'ifsc_code' => $data['ifsc_code'],
            'account_type' => $data['account_type'],
            'relationship_duration' => $data['relationship_duration'],
            'od_limit' => $data['od_limit'] ?? null,
            'od_security' => $data['od_security'] ?? null,
        ]);

        // Business Plans - delete existing and create new
        $application->businessPlans()->delete();
        foreach ($data['business_plans'] as $plan) {
            $application->businessPlans()->create([
                'crop' => $plan['crop'],
                'fy2025_26_MT' => $plan['fy2025_26_MT'],
                'fy2026_27_MT' => $plan['fy2026_27_MT'],
            ]);
        }

        // Financial Info
        $currentYear = date('Y');
        $defaultYears = [
            ($currentYear - 3) . '-' . ($currentYear - 2),
            ($currentYear - 2) . '-' . ($currentYear - 1),
            ($currentYear - 1) . '-' . $currentYear
        ];

        // Build turnover array, filtering out empty or null amounts
        $turnover = [];
        foreach ($defaultYears as $year) {
            $key = "fy" . str_replace('-', '_', $year) . "_turnover";
            if (isset($data[$key]) && !is_null($data[$key]) && $data[$key] !== '') {
                $turnover[$year] = $data[$key];
            }
        }

        $application->financialInfo()->updateOrCreate(
            ['application_id' => $application->id],
            [
                'net_worth' => $data['net_worth'],
                'shop_ownership' => $data['shop_ownership'],
                'godown_area' => trim($data['godown_area']),
                'years_in_business' => $data['years_in_business'],
                'annual_turnover' => json_encode($turnover),
            ]
        );

        // Existing Distributorships - delete existing and create new
        $application->existingDistributorships()->delete();
        if (!empty($data['existing_distributorships'])) {
            foreach ($data['existing_distributorships'] as $distributorship) {
                if (!empty($distributorship['company_name'])) {
                    $application->existingDistributorships()->create([
                        'company_name' => $distributorship['company_name'],
                    ]);
                }
            }
        }

        // Declarations
        $application->declarations()->update([
            'is_other_distributor' => $data['is_other_distributor'],
            'other_distributor_details' => $data['other_distributor_details'] ?? null,
            'has_sister_concern' => $data['has_sister_concern'],
            'sister_concern_details' => $data['sister_concern_details'] ?? null,
            'is_relative_associated' => $data['is_relative_associated'],
            'relative_associated_details' => $data['relative_associated_details'] ?? null,
            'has_disputed_dues' => $data['has_disputed_dues'],
            'disputed_dues_details' => $data['disputed_dues_details'] ?? null,
        ]);
    }
    protected function validateApplication(Request $request)
    {
        return $request->validate([
            'territory' => 'required|string|max:255',
            'crop_vertical' => 'required|string|max:255',
            'zone' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'state' => 'required|string|max:255',

            // Entity details
            'establishment_name' => 'required|string|max:255',
            'entity_type' => 'required|string|in:individual,sole_proprietorship,partnership,llp,private_company,public_company',
            'business_address' => 'required|string|max:500',
            'house_no' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:255',
            'state_id' => 'required|exists:core_state,id',
            'district_id' => 'required|exists:core_district,id',
            'country_id' => 'required|exists:core_country,id',
            'city' => 'required|string|max:255',
            'pincode' => 'required|string|max:10',
            'mobile' => 'required|string|max:15',
            'email' => 'required|email|max:255',
            'pan_number' => 'required|string|max:20',
            'gst_number' => 'nullable|string|max:20',
            'seed_license' => 'nullable|string|max:50',

            // Ownership details (dynamic based on entity_type)
            'owner_name' => 'required_if:entity_type,individual,sole_proprietorship|string|max:255',
            'dob' => 'required_if:entity_type,individual,sole_proprietorship|nullable|date',
            'father_husband_name' => 'nullable|string|max:255',
            'permanent_address' => 'required_if:entity_type,individual,sole_proprietorship|nullable|string|max:500',

            // Bank details
            'financial_status' => 'required|in:Good,Very Good,Excellent,Average',
            'retailer_count' => 'required|numeric|min:0',
            'bank_name' => 'required|string|max:255',
            'account_holder' => 'required|string|max:255',
            'account_number' => 'required|string|max:30',
            'ifsc_code' => 'required|string|max:20',
            'account_type' => 'required|in:current,savings',
            'relationship_duration' => 'required|numeric|min:0',
            'od_limit' => 'nullable|string|max:50',
            'od_security' => 'nullable|string|max:255',

            // Business plans
            'business_plans' => 'required|array',
            'business_plans.*.crop' => 'required|string|max:255',
            'business_plans.*.fy2025_26_MT' => 'required|numeric|min:0',
            'business_plans.*.fy2026_27_MT' => 'required|numeric|min:0',

            // Financial info
            'net_worth' => 'required|numeric|min:0',
            'shop_ownership' => 'required|string|in:owned,rented',
            'godown_area' => 'required|string|max:255',
            'years_in_business' => 'required|integer|min:0',
            'fy2022_23_turnover' => 'required|numeric|min:0',
            'fy2023_24_turnover' => 'required|numeric|min:0',
            'fy2024_25_turnover' => 'required|numeric|min:0',

            // Existing distributorships
            'existing_distributorships' => 'nullable|array',
            'existing_distributorships.*.company_name' => 'nullable|string|max:255',

            // Declarations
            'is_other_distributor' => 'required|boolean',
            'other_distributor_details' => 'nullable|string|max:500',
            'has_sister_concern' => 'required|boolean',
            'sister_concern_details' => 'nullable|string|max:500',
            'is_relative_associated' => 'required|boolean',
            'relative_associated_details' => 'nullable|string|max:500',
            'has_disputed_dues' => 'required|boolean',
            'disputed_dues_details' => 'nullable|string|max:500',

            // Documents
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);
    }

    protected function createRelatedRecords($application, $data)
    {
        // Entity Details
        $application->entityDetails()->create([
            'establishment_name' => $data['establishment_name'],
            'entity_type' => $data['entity_type'],
            'business_address' => $data['business_address'],
            'city' => $data['city'],
            'pincode' => $data['pincode'],
            'mobile' => $data['mobile'],
            'email' => $data['email'],
            'pan_number' => $data['pan_number'],
        ]);

        // Ownership Details
        if (in_array($data['entity_type'], ['individual', 'sole_proprietorship'])) {
            $application->ownershipDetails()->create([
                'owner_name' => $data['owner_name'],
                'relation_type' => 'proprietor',
                'dob' => $data['dob'],
                'father_husband_name' => $data['father_husband_name'] ?? null,
                'permanent_address' => $data['permanent_address'],
            ]);
        }

        // Bank Details
        $application->bankDetails()->create([
            'financial_status' => ucwords(strtolower($data['financial_status'])),
            'retailer_count' => $data['retailer_count'],
            'bank_name' => $data['bank_name'],
            'account_holder' => $data['account_holder'],
            'account_number' => $data['account_number'],
            'ifsc_code' => $data['ifsc_code'],
            'account_type' => $data['account_type'],
            'relationship_duration' => $data['relationship_duration'],
            'od_limit' => $data['od_limit'] ?? null,
            'od_security' => $data['od_security'] ?? null,
        ]);

        // Business Plans
        foreach ($data['business_plans'] as $plan) {
            $application->businessPlans()->create([
                'crop' => $plan['crop'],
                'fy2025_26_MT' => $plan['fy2025_26_MT'],
                'fy2026_27_MT' => $plan['fy2026_27_MT'],
            ]);
        }

        // Financial Info
        $application->financialInfo()->create([
            'net_worth' => $data['net_worth'],
            'shop_ownership' => $data['shop_ownership'],
            'godown_area' => $data['godown_area'],
            'years_in_business' => $data['years_in_business'],
            'fy2022_23_turnover' => $data['fy2022_23_turnover'],
            'fy2023_24_turnover' => $data['fy2023_24_turnover'],
            'fy2024_25_turnover' => $data['fy2024_25_turnover'],
        ]);

        // Existing Distributorships
        if (!empty($data['existing_distributorships'])) {
            foreach ($data['existing_distributorships'] as $distributorship) {
                if (!empty($distributorship['company_name'])) {
                    $application->existingDistributorships()->create([
                        'company_name' => $distributorship['company_name'],
                    ]);
                }
            }
        }

        // Declarations
        $application->declarations()->create([
            'is_other_distributor' => $data['is_other_distributor'],
            'other_distributor_details' => $data['other_distributor_details'] ?? null,
            'has_sister_concern' => $data['has_sister_concern'],
            'sister_concern_details' => $data['sister_concern_details'] ?? null,
            'is_relative_associated' => $data['is_relative_associated'],
            'relative_associated_details' => $data['relative_associated_details'] ?? null,
            'has_disputed_dues' => $data['has_disputed_dues'],
            'disputed_dues_details' => $data['disputed_dues_details'] ?? null,
        ]);
    }



    protected function handleDocumentUploads($application, $request)
    {
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $type => $file) {
                $path = $file->store("applications/{$application->id}/documents");

                // Update existing or create new document
                $document = $application->documents()->where('type', $type)->first();

                if ($document) {
                    // Delete old file
                    Storage::delete($document->path);
                    $document->update([
                        'path' => $path,
                        'status' => 'pending',
                        'remarks' => null,
                    ]);
                } else {
                    $application->documents()->create([
                        'type' => $type,
                        'path' => $path,
                        'status' => 'pending',
                    ]);
                }
            }
        }
    }

    protected function routeToApprover($application, $role)
    {
        $approvers = User::role($role)
            ->where('region', $application->region)
            ->get();

        foreach ($approvers as $approver) {
            $approver->notify(new ApplicationApprovalRequired($application));
        }

        $application->approvalLogs()->create([
            'user_id' => Auth::id(),
            'role' => 'initiator',
            'action' => 'submit',
            'remarks' => 'Application submitted for approval',
        ]);
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
            $current_step = $stepNumber;
            // Route to specific step handler
            switch ($stepNumber) {
                case 1:
                    return $this->saveStep1($request, $user, $application_id);
                case 2:
                    return $this->saveStep2($request, $user, $application_id);
                case 3:
                    return $this->saveStep3($request, $user, $application_id);
                case 4:
                    return $this->saveStep4($request, $user, $application_id);
                case 5:
                    return $this->saveStep5($request, $user, $application_id);
                case 6:
                    return $this->saveStep6($request, $user, $application_id);
                case 7:
                    return $this->saveStep7($request, $user, $application_id);
                case 8:
                    return $this->saveStep8($request, $user, $application_id);
                case 9:
                    return $this->saveStep9($request, $user, $application_id);
                default:
                    return response()->json(['success' => false, 'error' => 'Invalid step number.'], 400);
            }
            // Ensure all step handlers return the application ID
            if ($result['success'] && isset($result['application_id'])) {
                return response()->json([
                    'success' => true,
                    'application_id' => $result['application_id'],
                    'current_step' => $current_step
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error("Error in saveStep: " . $e->getMessage(), ['step' => $stepNumber, 'trace' => $e->getTraceAsString()]);
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
                'dis_state' => 'required|string',
                'district' => 'required|string',
            ], [
                'territory.required' => 'The territory field is required',
                'crop_vertical.required' => 'Please select a crop vertical',
                'zone.required' => 'Please select a zone',
                'dis_state.required' => 'Please select a state',
                'district.required' => 'Please select a district',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->errors()->first()
                ], 422);
            }

            $data = $validator->validated();
            $data['state'] = $data['dis_state'];
            $data['region'] = $request->input('region');
            $data['business_unit'] = $request->input('business_unit');
            $data['zone'] = $request->input('zone');
            unset($data['dis_state']);
            //dd($data);
            $now = now();
            if ($application_id) {
                $application = DB::table('distributor_applications')
                    ->where('id', $application_id)
                    ->first();

                if (!$application) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error' => 'Application not found'
                    ], 404);
                }

                DB::table('distributor_applications')
                    ->where('id', $application_id)
                    ->update(array_merge($data, [
                        'updated_at' => $now
                    ]));
            } else {
                $data['application_code'] = $this->generateUniqueApplicationCode();
                $data['created_by'] = $user->emp_id;
                $data['status'] = 'draft';
                $data['created_at'] = $now;
                $data['updated_at'] = $now;
                $application_id = DB::table('distributor_applications')
                    ->insertGetId($data);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Step 1 saved successfully!',
                'application_id' => $application_id,
                'current_step' => 1
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saving step 1: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to save step 1: ' . $e->getMessage()
            ], 500);
        }
    }

    // Step 2: Entity Details
    private function saveStep2(Request $request, $user, $application_id)
    {
        //dd($request->all());
        if (!$application_id) {
            return response()->json(['success' => false, 'error' => 'Application ID is missing.'], 400);
        }

        DB::beginTransaction();
        try {
            // Fetch existing entity details
            $entityDetails = \App\Models\EntityDetails::where('application_id', $application_id)->first();
            $existingDocuments = $entityDetails && $entityDetails->documents_data
                ? json_decode($entityDetails->documents_data, true)
                : [];

            // Define validation rules
            $rules = [
                // Common fields
                'establishment_name' => 'required|string|max:255',
                'entity_type' => 'required|string|in:sole_proprietorship,partnership,llp,private_company,public_company,cooperative_society,trust',
                'business_address' => 'required|string',
                'house_no' => 'nullable|string|max:255',
                'landmark' => 'nullable|string|max:255',
                'city' => 'required|string|max:255',
                'state_id' => 'required|exists:core_state,id',
                'district_id' => 'required|exists:core_district,id',
                'country_id' => 'required|exists:core_country,id',
                'pincode' => 'required|string|max:10',
                'mobile' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'pan_number' => 'required|string|max:20',
                'pan_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'existing_pan_file' => 'nullable|string',
                'pan_verified' => 'nullable|boolean',
                'gst_applicable' => 'required|in:yes,no',
                'gst_number' => 'nullable|string|max:20',
                'gst_validity' => 'nullable|date_format:Y-m-d',
                'gst_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'existing_gst_file' => 'nullable|string',
                'seed_license' => 'required|string|max:255',
                'seed_license_validity' => 'required|date_format:Y-m-d',
                'seed_license_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'existing_seed_license_file' => 'nullable|string',
                'seed_license_verified' => 'nullable|boolean',
                'bank_name' => 'required|string|max:255',
                'account_holder' => 'required|string|max:255',
                'account_number' => 'required|string|max:20',
                'ifsc_code' => 'required|string|max:11',
                'bank_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'existing_bank_file' => 'nullable|string',
                'tan_number' => 'nullable|string|max:20',

                // Sole Proprietorship
                'proprietor_name' => $request->input('entity_type') === 'sole_proprietorship' ? 'required|string|max:255' : 'nullable|string|max:255',
                'proprietor_dob' => $request->input('entity_type') === 'sole_proprietorship' ? 'required|date_format:Y-m-d' : 'nullable|date_format:Y-m-d',
                'proprietor_father_name' => $request->input('entity_type') === 'sole_proprietorship' ? 'required|string|max:255' : 'nullable|string|max:255',
                'proprietor_address' => $request->input('entity_type') === 'sole_proprietorship' ? 'required|string' : 'nullable|string',
                'proprietor_pincode' => $request->input('entity_type') === 'sole_proprietorship' ? 'required|string|max:10' : 'nullable|string|max:10',
                'proprietor_country' => $request->input('entity_type') === 'sole_proprietorship' ? 'required|string|max:255' : 'nullable|string|max:255',

                // Partnership
                'partner_name.*' => $request->input('entity_type') === 'partnership' ? 'required|string|max:255' : 'nullable|string|max:255',
                'partner_father_name.*' => $request->input('entity_type') === 'partnership' ? 'required|string|max:255' : 'nullable|string|max:255',
                'partner_contact.*' => $request->input('entity_type') === 'partnership' ? 'required|string|max:20' : 'nullable|string|max:20',
                'partner_email.*' => $request->input('entity_type') === 'partnership' ? 'required|email|max:50' : 'nullable|email|max:50',
                'partner_address.*' => $request->input('entity_type') === 'partnership' ? 'required|string' : 'nullable|string',

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
                'auth_person_letter.*' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
                'auth_person_aadhar.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
                'existing_auth_person_letter.*' => 'nullable|string',
                'existing_auth_person_aadhar.*' => 'nullable|string',
            ];
               // Get existing authorized persons data
            $existingAuthPersons = $entityDetails && isset($entityDetails->additional_data['authorized_persons'])
                ? $entityDetails->additional_data['authorized_persons']
                : [];
            // Custom validation for file fields and GST
            $validator = Validator::make($request->all(), $rules);
            $validator->after(function ($validator) use ($request, $existingDocuments) {
                // Validate PAN file
                if (!$request->hasFile('pan_file') && !$request->input('existing_pan_file') && !collect($existingDocuments)->firstWhere('type', 'pan')) {
                    $validator->errors()->add('pan_file', 'The PAN file is required.');
                }

                // Validate PAN verification if a new file is uploaded
                if ($request->hasFile('pan_file') && !$request->input('pan_verified')) {
                    $validator->errors()->add('pan_verified', 'You must confirm that the PAN number matches the uploaded document.');
                }

                // Validate Seed License file
                if (!$request->hasFile('seed_license_file') && !$request->input('existing_seed_license_file') && !collect($existingDocuments)->firstWhere('type', 'seed_license')) {
                    $validator->errors()->add('seed_license_file', 'The seed license file is required.');
                }

                // Validate Seed License verification if a new file is uploaded
                if ($request->hasFile('seed_license_file') && !$request->input('seed_license_verified')) {
                    $validator->errors()->add('seed_license_verified', 'You must confirm that the seed license number matches the uploaded document.');
                }

                // Validate Bank file
                if (!$request->hasFile('bank_file') && !$request->input('existing_bank_file') && !collect($existingDocuments)->firstWhere('type', 'bank')) {
                    $validator->errors()->add('bank_file', 'The bank file is required.');
                }

                // Validate GST fields if applicable
                if ($request->input('gst_applicable') === 'yes') {
                    if (!$request->filled('gst_number')) {
                        $validator->errors()->add('gst_number', 'The GST number is required when GST is applicable.');
                    }
                    if (!$request->filled('gst_validity')) {
                        $validator->errors()->add('gst_validity', 'The GST validity date is required when GST is applicable.');
                    }
                    if (!$request->hasFile('gst_file') && !$request->input('existing_gst_file') && !collect($existingDocuments)->firstWhere('type', 'gst')) {
                        $validator->errors()->add('gst_file', 'The GST document is required when GST is applicable.');
                    }
                }
            });

            // Custom validation for entity-specific fields
            $entity_type = $request->input('entity_type');
            if ($entity_type === 'sole_proprietorship') {
                if (!$request->filled('proprietor_name')) {
                    $validator->errors()->add('proprietor_name', 'The proprietor name is required for sole proprietorship.');
                }
                if (!$request->filled('proprietor_dob')) {
                    $validator->errors()->add('proprietor_dob', 'The proprietor date of birth is required for sole proprietorship.');
                }
                if (!$request->filled('proprietor_father_name')) {
                    $validator->errors()->add('proprietor_father_name', 'The proprietor father\'s name is required for sole proprietorship.');
                }
                if (!$request->filled('proprietor_address')) {
                    $validator->errors()->add('proprietor_address', 'The proprietor address is required for sole proprietorship.');
                }
                if (!$request->filled('proprietor_pincode')) {
                    $validator->errors()->add('proprietor_pincode', 'The proprietor pincode is required for sole proprietorship.');
                }
                if (!$request->filled('proprietor_country')) {
                    $validator->errors()->add('proprietor_country', 'The proprietor country is required for sole proprietorship.');
                }
            } elseif ($entity_type === 'partnership') {
                if (empty(array_filter($request->input('partner_name', [])))) {
                    $validator->errors()->add('partner_name', 'At least one partner is required for partnership.');
                } else {
                    foreach ($request->input('partner_name', []) as $index => $name) {
                        if (!empty($name)) {
                            if (empty($request->input('partner_father_name', [])[$index])) {
                                $validator->errors()->add("partner_father_name.$index", 'Father\'s name is required for each partner.');
                            }
                            if (empty($request->input('partner_contact', [])[$index])) {
                                $validator->errors()->add("partner_contact.$index", 'Contact number is required for each partner.');
                            }
                            if (empty($request->input('partner_email', [])[$index])) {
                                $validator->errors()->add("partner_email.$index", 'Email is required for each partner.');
                            }
                            if (empty($request->input('partner_address', [])[$index])) {
                                $validator->errors()->add("partner_address.$index", 'Address is required for each partner.');
                            }
                        }
                    }
                }
            } elseif ($entity_type === 'llp') {
                if (!$request->filled('llpin_number')) {
                    $validator->errors()->add('llpin_number', 'The LLPIN number is required for LLP.');
                }
                if (!$request->filled('llp_incorporation_date')) {
                    $validator->errors()->add('llp_incorporation_date', 'The LLP incorporation date is required for LLP.');
                }
                if (empty(array_filter($request->input('llp_partner_name', [])))) {
                    $validator->errors()->add('llp_partner_name', 'At least one designated partner is required for LLP.');
                } else {
                    foreach ($request->input('llp_partner_name', []) as $index => $name) {
                        if (!empty($name)) {
                            if (empty($request->input('llp_partner_dpin', [])[$index])) {
                                $validator->errors()->add("llp_partner_dpin.$index", 'DPIN number is required for each partner.');
                            }
                            if (empty($request->input('llp_partner_contact', [])[$index])) {
                                $validator->errors()->add("llp_partner_contact.$index", 'Contact number is required for each partner.');
                            }
                            if (empty($request->input('llp_partner_address', [])[$index])) {
                                $validator->errors()->add("llp_partner_address.$index", 'Address is required for each partner.');
                            }
                        }
                    }
                }
            } elseif (in_array($entity_type, ['private_company', 'public_company'])) {
                if (!$request->filled('cin_number')) {
                    $validator->errors()->add('cin_number', 'The CIN number is required for companies.');
                }
                if (!$request->filled('incorporation_date')) {
                    $validator->errors()->add('incorporation_date', 'The incorporation date is required for companies.');
                }
                if (empty(array_filter($request->input('director_name', [])))) {
                    $validator->errors()->add('director_name', 'At least one director is required for companies.');
                } else {
                    foreach ($request->input('director_name', []) as $index => $name) {
                        if (!empty($name)) {
                            if (empty($request->input('director_din', [])[$index])) {
                                $validator->errors()->add("director_din.$index", 'DIN number is required for each director.');
                            }
                            if (empty($request->input('director_contact', [])[$index])) {
                                $validator->errors()->add("director_contact.$index", 'Contact number is required for each director.');
                            }
                            if (empty($request->input('director_address', [])[$index])) {
                                $validator->errors()->add("director_address.$index", 'Address is required for each director.');
                            }
                        }
                    }
                }
            } elseif ($entity_type === 'cooperative_society') {
                if (!$request->filled('cooperative_reg_number')) {
                    $validator->errors()->add('cooperative_reg_number', 'The registration number is required for cooperative societies.');
                }
                if (!$request->filled('cooperative_reg_date')) {
                    $validator->errors()->add('cooperative_reg_date', 'The registration date is required for cooperative societies.');
                }
                if (empty(array_filter($request->input('committee_name', [])))) {
                    $validator->errors()->add('committee_name', 'At least one committee member is required for cooperative societies.');
                } else {
                    foreach ($request->input('committee_name', []) as $index => $name) {
                        if (!empty($name)) {
                            if (empty($request->input('committee_designation', [])[$index])) {
                                $validator->errors()->add("committee_designation.$index", 'Designation is required for each committee member.');
                            }
                            if (empty($request->input('committee_contact', [])[$index])) {
                                $validator->errors()->add("committee_contact.$index", 'Contact number is required for each committee member.');
                            }
                            if (empty($request->input('committee_address', [])[$index])) {
                                $validator->errors()->add("committee_address.$index", 'Address is required for each committee member.');
                            }
                        }
                    }
                }
            } elseif ($entity_type === 'trust') {
                if (!$request->filled('trust_reg_number')) {
                    $validator->errors()->add('trust_reg_number', 'The registration number is required for trusts.');
                }
                if (!$request->filled('trust_reg_date')) {
                    $validator->errors()->add('trust_reg_date', 'The registration date is required for trusts.');
                }
                if (empty(array_filter($request->input('trustee_name', [])))) {
                    $validator->errors()->add('trustee_name', 'At least one trustee is required for trusts.');
                } else {
                    foreach ($request->input('trustee_name', []) as $index => $name) {
                        if (!empty($name)) {
                            if (empty($request->input('trustee_designation', [])[$index])) {
                                $validator->errors()->add("trustee_designation.$index", 'Designation is required for each trustee.');
                            }
                            if (empty($request->input('trustee_contact', [])[$index])) {
                                $validator->errors()->add("trustee_contact.$index", 'Contact number is required for each trustee.');
                            }
                            if (empty($request->input('trustee_address', [])[$index])) {
                                $validator->errors()->add("trustee_address.$index", 'Address is required for each trustee.');
                            }
                        }
                    }
                }
            }

            // Validate authorized persons
                 if ($request->has('auth_person_name') && !empty(array_filter($request->input('auth_person_name', [])))) {
        foreach ($request->input('auth_person_name', []) as $index => $name) {
            if (!empty($name)) {
                // Check Letter of Authorization
                $hasNewLetter = $request->hasFile("auth_person_letter.$index");
                $hasExistingLetter = !empty($request->input("existing_auth_person_letter.$index")) || 
                                    (isset($existingAuthPersons[$index]['letter']) && !empty($existingAuthPersons[$index]['letter']));
                
                if (!$hasNewLetter && !$hasExistingLetter) {
                    $validator->errors()->add("auth_person_letter.$index", 'Either upload a new Letter of Authorization or keep the existing one');
                }
                
                // Check Aadhar
                $hasNewAadhar = $request->hasFile("auth_person_aadhar.$index");
                $hasExistingAadhar = !empty($request->input("existing_auth_person_aadhar.$index")) || 
                                     (isset($existingAuthPersons[$index]['aadhar']) && !empty($existingAuthPersons[$index]['aadhar']));
                
                if (!$hasNewAadhar && !$hasExistingAadhar) {
                    $validator->errors()->add("auth_person_aadhar.$index", 'Either upload a new Aadhar document or keep the existing one');
                }
            }
        }
    }
         

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $data = $validator->validated();
            $entity_type = $data['entity_type'];

            // Initialize documents_data with existing documents
            $documents_data = $existingDocuments ?: []; // Ensure initialization

            // Process new or updated documents
            $documentTypes = [
                'pan' => [
                    'file_field' => 'pan_file',
                    'existing_field' => 'existing_pan_file',
                    'details' => ['pan_number' => $data['pan_number']],
                    'verified_field' => 'pan_verified',
                ],
                'seed_license' => [
                    'file_field' => 'seed_license_file',
                    'existing_field' => 'existing_seed_license_file',
                    'details' => [
                        'seed_license_number' => $data['seed_license'],
                        'seed_license_validity' => $data['seed_license_validity'],
                    ],
                    'verified_field' => 'seed_license_verified',
                ],
                'bank' => [
                    'file_field' => 'bank_file',
                    'existing_field' => 'existing_bank_file',
                    'details' => [
                        'bank_name' => $data['bank_name'],
                        'account_holder' => $data['account_holder'],
                        'account_number' => $data['account_number'],
                        'ifsc_code' => $data['ifsc_code'],
                    ],
                ],
                'gst' => [
                    'file_field' => 'gst_file',
                    'existing_field' => 'existing_gst_file',
                    'details' => [
                        'gst_number' => $data['gst_applicable'] === 'yes' ? $data['gst_number'] : null,
                        'gst_validity' => $data['gst_applicable'] === 'yes' ? $data['gst_validity'] : null,
                    ],
                    'condition' => $data['gst_applicable'] === 'yes',
                ],
            ];

            foreach ($documentTypes as $type => $config) {
                // Skip GST if not applicable
                if ($type === 'gst' && !$config['condition']) {
                    $documents_data = array_filter($documents_data, function ($doc) use ($type) {
                        return $doc['type'] !== $type;
                    });
                    continue;
                }

                // If a new file is uploaded, replace or add the document
                if ($request->hasFile($config['file_field'])) {
                    $file = $request->file($config['file_field']);
                    $path = $file->store('documents/' . $application_id, 'public');
                    $documents_data = array_filter($documents_data, function ($doc) use ($type) {
                        return $doc['type'] !== $type;
                    });
                    $documents_data[] = [
                        'type' => $type,
                        'path' => $path,
                        'details' => array_filter($config['details'], fn($value) => !is_null($value)),
                        'status' => 'pending',
                        'remarks' => 'Uploaded on ' . now()->toDateString(),
                        'verified' => isset($config['verified_field']) ? ($request->input($config['verified_field']) ? true : false) : false,
                    ];
                } elseif ($request->input($config['existing_field'])) {
                    // Keep existing document if provided
                    $existingDoc = collect($existingDocuments)->firstWhere('type', $type);
                    if ($existingDoc) {
                        $documents_data = array_filter($documents_data, function ($doc) use ($type) {
                            return $doc['type'] !== $type;
                        });
                        $documents_data[] = array_merge($existingDoc, [
                            'details' => array_filter($config['details'], fn($value) => !is_null($value)),
                            'verified' => isset($config['verified_field']) ? ($request->input($config['verified_field']) ? true : ($existingDoc['verified'] ?? false)) : ($existingDoc['verified'] ?? false),
                        ]);
                    }
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
                'email' => $data['email'],
                'pan_number' => $data['pan_number'],
                'gst_applicable' => $data['gst_applicable'],
                'gst_number' => $data['gst_applicable'] === 'yes' ? $data['gst_number'] : null,
                'seed_license' => $data['seed_license'],
                'documents_data' => json_encode(array_values($documents_data)), // Ensure array is re-indexed
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
            if ($entity_type === 'sole_proprietorship') {
                $additionalData['proprietor'] = [
                    'name' => $data['proprietor_name'],
                    'dob' => $data['proprietor_dob'],
                    'father_name' => $data['proprietor_father_name'],
                    'address' => $data['proprietor_address'],
                    'pincode' => $data['proprietor_pincode'],
                    'country' => $data['proprietor_country'],
                ];
            } elseif ($entity_type === 'partnership') {
                $partners = [];
                if ($request->has('partner_name') && is_array($request->input('partner_name'))) {
                    foreach ($request->input('partner_name', []) as $index => $name) {
                        if (!empty($name)) {
                            $partners[] = [
                                'name' => $name,
                                'father_name' => $request->input('partner_father_name', [])[$index],
                                'contact' => $request->input('partner_contact', [])[$index],
                                'email' => $request->input('partner_email', [])[$index],
                                'address' => $request->input('partner_address', [])[$index],
                            ];
                        }
                    }
                }
                $additionalData['partners'] = $partners;
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
if ($request->has('auth_person_name') && is_array($request->input('auth_person_name'))) {
    foreach ($request->input('auth_person_name', []) as $index => $name) {
        if (!empty($name)) {
            $personData = [
                'name' => $name,
                'contact' => $request->input('auth_person_contact', [])[$index],
                'email' => $request->input('auth_person_email', [])[$index] ?? null,
                'address' => $request->input('auth_person_address', [])[$index],
                'relation' => $request->input('auth_person_relation', [])[$index],
            ];

            // Handle Letter
            if ($request->hasFile("auth_person_letter.$index")) {
                $letterFile = $request->file("auth_person_letter.$index");
                $letterPath = $letterFile->store('documents/' . $application_id . '/authorized_persons', 'public');
                $personData['letter'] = $letterPath;
            } elseif ($request->input("existing_auth_person_letter.$index")) {
                $personData['letter'] = $request->input("existing_auth_person_letter.$index");
            } elseif (isset($existingAuthPersons[$index]['letter'])) {
                $personData['letter'] = $existingAuthPersons[$index]['letter'];
            }

            // Handle Aadhar
            if ($request->hasFile("auth_person_aadhar.$index")) {
                $aadharFile = $request->file("auth_person_aadhar.$index");
                $aadharPath = $aadharFile->store('documents/' . $application_id . '/authorized_persons', 'public');
                $personData['aadhar'] = $aadharPath;
            } elseif ($request->input("existing_auth_person_aadhar.$index")) {
                $personData['aadhar'] = $request->input("existing_auth_person_aadhar.$index");
            } elseif (isset($existingAuthPersons[$index]['aadhar'])) {
                $personData['aadhar'] = $existingAuthPersons[$index]['aadhar'];
            }

            $authorizedPersons[] = $personData;
        }
    }
}

            // Add to additional_data
            $additionalData['authorized_persons'] = $authorizedPersons;

            // Remove empty arrays or null values from additional_data
            $additionalData = array_filter($additionalData, function ($value) {
                if (is_array($value)) {
                    return !empty(array_filter($value, function ($subValue) {
                        return !is_null($subValue) && !(is_array($subValue) && empty($subValue));
                    }));
                }
                return !is_null($value);
            });

            // Set additional_data in entityData
            $entityData['additional_data'] = $additionalData;
           // dd($entityData);
            // Update or create EntityDetails
            \App\Models\EntityDetails::updateOrCreate(
                ['application_id' => $application_id],
                $entityData
            );

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Entity details and documents saved successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving entity details: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'An error occurred while saving entity details and documents.'], 500);
        }
    }
    // Step 3: Distribution Details
    private function saveStep3(Request $request, $user, $application_id)
    {
        if (!$application_id) {
            return response()->json(['success' => false, 'error' => 'Application ID is missing.'], 400);
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


            // Log input for debugging
            Log::info('Request payload:', $request->all());
            Log::info('Processed area_covered:', $input['area_covered']);

            $validator = Validator::make($input, $rules, [
                'area_covered.*.exists' => 'The selected district ":input" is not a valid district.',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'error' => $validator->errors()], 422);
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

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Distribution details saved successfully!',
                'application_id' => $application_id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saving distribution details: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to save distribution details: ' . $e->getMessage()], 500);
        }
    }

    // Step 4: Business Plans
    private function saveStep4(Request $request, $user, $application_id)
    {
        if (!$application_id) {
            return response()->json(['success' => false, 'error' => 'Application ID is missing.'], 400);
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
            return response()->json(['success' => false, 'error' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $validatedData = $validator->validated();
            $plansToInsert = [];
            //dd($validatedData);

            // --- 2. DATA TRANSFORMATION ---
            // Fetch the Year models to use their IDs as keys in the JSON object.
            // This ensures data consistency between saving and loading.
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

            // --- 3. DATABASE INSERTION ---
            // First, clear all existing plans for this application to handle updates/deletions.
            DB::table('business_plans')->where('application_id', $application_id)->delete();

            // Now, insert the new set of plans.
            if (!empty($plansToInsert)) {
                DB::table('business_plans')->insert($plansToInsert);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Step 5 saved successfully!',
                'application_id' => $application_id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saving step 5 for application ID {$application_id}: " . $e->getMessage());
            // Add more detailed error logging for debugging if needed
            Log::error($e->getTraceAsString());
            return response()->json(['success' => false, 'error' => 'An unexpected error occurred while saving step 5.'], 500);
        }
    }

    // Step 5: Financial Info
    private function saveStep5(Request $request, $user, $application_id)
    {
        if (!$application_id) {
            return response()->json(['success' => false, 'error' => 'Application ID is missing.', 'application_id' => null], 400);
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
                'annual_turnover.year.*' => ['required', 'string', 'in:' . implode(',', $defaultYears)],
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
            ];

            $validator = Validator::make($request->all(), $rules, [
                'annual_turnover.year.*.in' => 'The financial year must be one of: ' . implode(', ', $defaultYears) . '.',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'error' => $validator->errors(), 'application_id' => $application_id], 422);
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

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Financial info saved successfully!',
                'application_id' => $application_id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saving financial info: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to save financial info: ' . $e->getMessage(),
                'application_id' => $application_id
            ], 500);
        }
    }
    // Step 6: Existing Distributorships
    private function saveStep6(Request $request, $user, $application_id)
    {
        if (!$application_id) {
            return response()->json(['success' => false, 'error' => 'Application ID is missing.'], 400);
        }

        DB::beginTransaction();

        try {
            // Get all submitted companies (including empty ones)
            $submittedCompanies = $request->input('existing_distributorships', []);

            // Filter out completely empty entries (no company_name and no id)
            $validCompanies = array_filter($submittedCompanies, function ($company) {
                return !empty(trim($company['company_name'] ?? '')) || isset($company['id']);
            });

            // Validate only non-empty entries
            $validator = Validator::make(
                ['existing_distributorships' => $validCompanies],
                [
                    'existing_distributorships' => 'array',
                    'existing_distributorships.*.company_name' => [
                        'required_without:existing_distributorships.*.id',
                        'string',
                        'max:255'
                    ],
                    'existing_distributorships.*.id' => [
                        'sometimes',
                        'integer',
                        'exists:existing_distributorships,id,application_id,' . $application_id
                    ]
                ],
                [
                    'existing_distributorships.*.company_name.required_without' => 'Company name is required for new entries.',
                    'existing_distributorships.*.company_name.max' => 'Company name must not exceed 255 characters.'
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()->toArray(),
                    'message' => 'Please correct the validation errors.'
                ], 422);
            }

            $existingIds = collect($validCompanies)->pluck('id')->filter()->toArray();

            // Delete records not present in the submitted data
            ExistingDistributorship::where('application_id', $application_id)
                ->whereNotIn('id', $existingIds)
                ->delete();

            // Create/update only valid entries
            foreach ($validCompanies as $companyData) {
                if (!empty(trim($companyData['company_name'] ?? ''))) {
                    ExistingDistributorship::updateOrCreate(
                        ['id' => $companyData['id'] ?? null],
                        [
                            'application_id' => $application_id,
                            'company_name' => trim($companyData['company_name'])
                        ]
                    );
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => empty($validCompanies)
                    ? 'No distributorships saved (empty entries)'
                    : 'Distributorships saved successfully',
                'application_id' => $application_id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saving distributorships: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to save: ' . $e->getMessage(),
                'message' => 'An unexpected error occurred. Please try again.'
            ], 500);
        }
    }


    // Step 7: Bank Details
    private function saveStep7(Request $request, $user, $application_id)
    {
        if (!$application_id) {
            return response()->json(['success' => false, 'error' => 'Application ID is missing.'], 400);
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
                return response()->json(['success' => false, 'error' => $validator->errors()], 422);
            }

            $data = $validator->validated();
            $data['application_id'] = $application_id;

            \App\Models\BankDetail::updateOrCreate(
                ['application_id' => $application_id],
                $data
            );

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Bank details saved successfully!',
                'application_id' => $application_id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saving bank details: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to save bank details.'], 500);
        }
    }

    // Step 8: Declarations
    private function saveStep8(Request $request, $user, $application_id)
    {
        if (!$application_id) {
            Log::error('saveStep8: Application ID is missing.');
            return response()->json(['success' => false, 'error' => 'Application ID is missing.'], 400);
        }
        // Log incoming request data for debugging
        Log::debug('saveStep8 request data:', $request->all());
        Log::debug('has_question_j specific:', [
            'has_question_j' => $request->input('has_question_j'),
            'referrer_1' => $request->input('referrer_1'),
            'referrer_2' => $request->input('referrer_2'),
            'referrer_3' => $request->input('referrer_3'),
            'referrer_4' => $request->input('referrer_4'),
        ]);
        Log::debug('declaration fields:', [
            'declaration_truthful' => $request->input('declaration_truthful'),
            'declaration_update' => $request->input('declaration_update'),
        ]);

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
                Log::warning('Validation failed for saveStep8:', ['errors' => $validator->errors()]);
                return response()->json(['success' => false, 'error' => $validator->errors()], 422);
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

                Log::debug("Saving {$question_key}:", [
                    'has_issue' => $has_issue,
                    'details' => $details_json,
                ]);
            }

            // Delete existing declarations for this application
            DB::table('declarations')->where('application_id', $application_id)->delete();

            // Insert new declarations
            DB::table('declarations')->insert($data);

            // Get the current user from core_employee
            // $currentUser = Employee::where('id', $user->emp_id)->first();
            // if (!$currentUser) {
            //     Log::error('saveStep8: Current user not found in core_employee.', ['user_id' => $user->id]);
            //     return response()->json(['success' => false, 'error' => 'User not found.'], 404);
            // }
            // Determine approval level and approver based on territory, region, zone, and bu
            //  $approverData = $this->getApproverIdAndLevel($currentUser);
            // if (!$approverData) {
            //     Log::error('saveStep8: Approver not found or invalid designation.', [
            //         'user_id' => $user->id,
            //         'emp_reporting' => $currentUser->emp_reporting
            //     ]);
            //     return response()->json(['success' => false, 'error' => 'Approver not assigned or invalid designation.'], 404);
            // }

            // $approverId = $approverData['approverId'];
            // $approvalLevel = $approverData['approvalLevel'];

            //Update application status (uncomment if needed)
            // DB::table('distributor_applications')
            //     ->where('id', $application_id)
            //     ->update([
            //         'status' => 'submitted',
            //         'current_approver_id' => $approverId,
            //         'approval_level' => $approvalLevel,
            //         'updated_at' => now()
            //      ]);

            DB::table('distributor_applications')
                ->where('id', $application_id)
                ->update([
                    'updated_at' => now()
                ]);

            DB::commit();
            //$this->sendNotification($application_id, $approverId, 'submitted');
            Log::info('saveStep8: Declarations saved successfully for application_id: ' . $application_id);
            return response()->json([
                'success' => true,
                // 'redirect' => route('applications.index'),
                'message' => 'Step 8 saved successfully!',
                'application_id' => $application_id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saving step 8: " . $e->getMessage(), ['request_data' => $request->all()]);
            return response()->json(['success' => false, 'error' => 'Failed to save step 8.'], 500);
        }
    }

    // public function submit(Request $request, $application_id)
    // {
    //     $user = Auth::user();
    //     return $this->saveStep8($request, $user, $application_id);
    // }


    private function saveStep9(Request $request, $user, $application_id)
    {
        $application = DistributorOnboarding::with([
            'territoryDetail',
            'regionDetail',
            'zoneDetail',
            'businessUnit',
            'entityDetails',
            'distributionDetail',
            'businessPlan',
            'financialInfo',
            'existingDistributorships',
            'bankDetail',
            'declarations'
        ])->findOrFail($application_id);
        dd($application);
        // Verify ownership
        if ($user->emp_id !== $application->created_by) {
            return response()->json(['success' => false, 'error' => 'Unauthorized action.'], 403);
        }

        // Validate all required steps are completed
        $requiredSteps = [
            'territory' => !$application->territory,
            'entityDetails' => !$application->entityDetails,
            'distributionDetail' => !$application->distributionDetail,
            'businessPlan' => !$application->businessPlan,
            'financialInfo' => !$application->financialInfo,
            'existingDistributorships' => !$application->existingDistributorships->count(),
            'bankDetail' => !$application->bankDetail,
            'declarations' => !$application->declarations->count()
        ];

        if (in_array(true, $requiredSteps)) {
            $missingSteps = array_keys(array_filter($requiredSteps));
            Log::warning('Submission blocked - missing steps:', ['missing_steps' => $missingSteps]);
            return response()->json([
                'success' => false,
                'error' => 'Please complete all steps before submitting.',
                'missing_steps' => $missingSteps
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Get approver information
            $currentUser = Employee::where('id', $user->emp_id)->first();
            if (!$currentUser) {
                Log::error('saveStep9: Current user not found in core_employee.', ['user_id' => $user->id]);
                throw new \Exception('User not found.');
            }

            $approverData = $this->getApproverIdAndLevel($currentUser);
            if (!$approverData) {
                Log::error('saveStep9: Approver not found or invalid designation.', [
                    'user_id' => $user->id,
                    'emp_reporting' => $currentUser->emp_reporting
                ]);
                throw new \Exception('Approver not assigned or invalid designation.');
            }

            // Final submission updates
            $application->status = 'submitted';
            $application->current_approver_id = $approverData['approverId'];
            $application->approval_level = $approverData['approvalLevel'];
            $application->submitted_at = now();
            $application->save();

            DB::commit();

            // Trigger notifications
            $this->sendNotification($application_id, $approverData['approverId'], 'submitted');

            Log::info('Application submitted successfully via saveStep9', ['application_id' => $application_id]);

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully!',
                'redirect' => route('applications.show', $application_id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error in saveStep9: " . $e->getMessage(), [
                'application_id' => $application_id,
                'user_id' => $user->id
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to submit application. ' . $e->getMessage()
            ], 500);
        }
    }

    private function getApproverIdAndLevel($employee)
    {
        // Map designations to integer approval levels
        $designationMap = [
            'Regional Business Manager' => 'rbm',
            'Zonal Business Manager' => 'zbm',
            'General Manager' => 'gm'
        ];

        // Fetch the manager directly using emp_reporting
        $approverId = $employee->emp_reporting;
        if (!$approverId) {
            Log::error('No manager found for employee.', ['employee_id' => $employee->id]);
            return null;
        }

        // Fetch the manager's details
        $manager = Employee::where('id', $approverId)->first();
        if (!$manager) {
            Log::error('Manager not found in core_employee.', ['manager_id' => $approverId]);
            return null;
        }

        // Determine approval level based on manager's designation
        $approvalLevel = $designationMap[$manager->emp_designation] ?? null;
        if (!$approvalLevel) {
            Log::warning('Manager designation not mapped to an approval level.', [
                'employee_id' => $employee->id,
                'manager_id' => $manager->id,
                'manager_designation' => $manager->emp_designation
            ]);
            return null;
        }

        return [
            'approverId' => $approverId,
            'approvalLevel' => $approvalLevel
        ];
    }

    // private function sendNotification($application_id, $approver_id, $action)
    // {
    //     $application = DistributorOnboarding::find($application_id);
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


    // private function saveStep9(Request $request, $user, $application_id)
    // {
    //     if (!$application_id) {
    //         return response()->json(['success' => false, 'error' => 'Application ID is missing.'], 400);
    //     }

    //     DB::beginTransaction();

    //     try {
    //         // Verify application exists
    //         $application = DB::table('distributor_applications')->where('id', $application_id)->first();
    //         if (!$application) {
    //             throw new \Exception("Application with ID {$application_id} not found.");
    //         }

    //         // Get existing documents from the database
    //         $existingDocuments = Document::where('application_id', $application_id)
    //             ->pluck('path', 'type')
    //             ->toArray();

    //         // Log existing documents for debugging
    //         Log::info("Existing documents for application {$application_id}: ", $existingDocuments);

    //         // Log request payload for debugging
    //         Log::info("Request payload for step 9: ", $request->all());

    //         // Prepare document mappings
    //         $requiredDocuments = [
    //             'business_entity' => 'business_entity_proof',
    //             'ownership' => 'ownership_proof',
    //             'pan' => 'pan_card',
    //             'address' => 'address_proof',
    //             'bank' => 'bank_proof',
    //             'photo' => 'photo',
    //             'shop_photo' => 'shop_photo'
    //         ];
    //         $optionalDocuments = [
    //             'gst' => 'gst_certificate',
    //             'seed_license' => 'seed_license',
    //             'other' => 'other_document'
    //         ];
    //         $allDocuments = array_merge($requiredDocuments, $optionalDocuments);

    //         // Simulate effective documents for validation (account for removals)
    //         $effectiveDocuments = $existingDocuments;
    //         if ($request->has('remove_documents')) {
    //             $removeDocuments = $request->input('remove_documents', []);
    //             foreach ($removeDocuments as $type => $value) {
    //                 if ($value) {
    //                     unset($effectiveDocuments[$type]);
    //                 }
    //             }
    //         }

    //         // Prepare validation rules
    //         $rules = [];
    //         $messages = [];

    //         // Validate required documents
    //         foreach ($requiredDocuments as $dbType => $inputName) {
    //             $hasNewFile = $request->hasFile("documents.{$inputName}");
    //             $hasExistingFile = $request->has("documents.{$inputName}_existing") && !empty($request->input("documents.{$inputName}_existing"));
    //             $isInDatabase = isset($effectiveDocuments[$dbType]);

    //             if (!$hasNewFile && !$hasExistingFile && !$isInDatabase) {
    //                 $rules["documents.{$inputName}"] = 'required|file|mimes:' .
    //                     (in_array($inputName, ['photo', 'shop_photo']) ? 'jpg,jpeg,png' : 'pdf,jpg,jpeg,png') .
    //                     '|max:2048';
    //                 $messages["documents.{$inputName}.required"] = "Please upload a {$inputName} document.";
    //             } elseif ($hasNewFile) {
    //                 $rules["documents.{$inputName}"] = 'file|mimes:' .
    //                     (in_array($inputName, ['photo', 'shop_photo']) ? 'jpg,jpeg,png' : 'pdf,jpg,jpeg,png') .
    //                     '|max:2048';
    //             }
    //         }

    //         // Validate optional documents
    //         $optionalRules = [
    //             'documents.gst_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
    //             'documents.seed_license' => 'nullable|string|url', // Allow URL or file
    //             'documents.other_document' => 'nullable|string|url',
    //         ];
    //         $rules = array_merge($rules, $optionalRules);

    //         // Run validation
    //         $validator = Validator::make($request->all(), $rules, $messages);
    //         if ($validator->fails()) {
    //             DB::rollBack();
    //             return response()->json([
    //                 'success' => false,
    //                 'error' => $validator->errors()
    //             ], 422);
    //         }

    //         // Handle document removals
    //         if ($request->has('remove_documents')) {
    //             $removeDocuments = $request->input('remove_documents', []);
    //             foreach ($removeDocuments as $type => $value) {
    //                 if ($value) {
    //                     $document = Document::where('application_id', $application_id)
    //                         ->where('type', $type)
    //                         ->first();
    //                     if ($document) {
    //                         Storage::disk('public')->delete($document->path);
    //                         $document->delete();
    //                         unset($existingDocuments[$type]);
    //                     }
    //                 }
    //             }
    //         }

    //         // Handle new file uploads
    //         if ($request->hasFile('documents')) {
    //             foreach ($request->file('documents') as $inputName => $file) {
    //                 if ($file) {
    //                     $docType = array_search($inputName, $allDocuments) ?? $inputName;
    //                     $path = $file->store("documents/applications/{$application_id}", 'public');
    //                     Document::updateOrCreate(
    //                         ['application_id' => $application_id, 'type' => $docType],
    //                         ['path' => $path, 'status' => 'pending']
    //                     );
    //                 }
    //             }
    //         }

    //         // Handle existing files
    //         if ($request->has('documents')) {
    //             foreach ($allDocuments as $dbType => $inputName) {
    //                 $existingFileKey = "documents.{$inputName}_existing";
    //                 $documentKey = "documents.{$inputName}";

    //                 // Handle existing file
    //                 if ($request->has($existingFileKey) && !empty($request->input($existingFileKey))) {
    //                     $existingPath = $request->input($existingFileKey);
    //                     $relativePath = str_replace(url('/storage/'), 'storage/', $existingPath);
    //                     Document::updateOrCreate(
    //                         ['application_id' => $application_id, 'type' => $dbType],
    //                         ['path' => $relativePath, 'status' => 'pending']
    //                     );
    //                 }
    //                 // Handle new file upload
    //                 elseif ($request->hasFile($documentKey)) {
    //                     $file = $request->file($documentKey);
    //                     $path = $file->store("documents/applications/{$application_id}", 'public');
    //                     Document::updateOrCreate(
    //                         ['application_id' => $application_id, 'type' => $dbType],
    //                         ['path' => $path, 'status' => 'pending']
    //                     );
    //                 }
    //                 // Handle URL (for optional documents)
    //                 elseif (in_array($inputName, ['seed_license', 'other_document']) && $request->has($documentKey) && filter_var($request->input($documentKey), FILTER_VALIDATE_URL)) {
    //                     $existingPath = $request->input($documentKey);
    //                     $relativePath = str_replace(url('/storage/'), 'storage/', $existingPath);
    //                     Document::updateOrCreate(
    //                         ['application_id' => $application_id, 'type' => $dbType],
    //                         ['path' => $relativePath, 'status' => 'pending']
    //                     );
    //                 }
    //             }
    //         }

    //         // Log final document state
    //         $updatedDocuments = Document::where('application_id', $application_id)
    //             ->pluck('path', 'type')
    //             ->toArray();
    //         Log::info("Updated documents for application {$application_id}: ", $updatedDocuments);

    //         // Update application status (uncomment if needed)
    //         // DB::table('distributor_applications')
    //         //     ->where('id', $application_id)
    //         //     ->update(['status' => 'submitted']);

    //         DB::commit();
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Documents saved successfully!',
    //             'application_id' => $application_id
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error("Error saving documents for application {$application_id}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    //         return response()->json([
    //             'success' => false,
    //             'error' => 'Failed to save documents: ' . $e->getMessage()
    //         ], 500);
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

            $application = DB::table('distributor_applications')->where('id', $applicationId)->first();
            if (!$application) {
                return response()->json(['success' => false, 'error' => 'Application not found.'], 404);
            }

            $document = Document::where('application_id', $applicationId)
                ->where('type', $type)
                ->first();

            if ($document) {
                Log::info("Removing document type {$type} for application {$applicationId}: {$document->path}");
                Storage::disk('public')->delete($document->path);
                $document->delete();
                DB::commit();
                return response()->json(['success' => true, 'message' => 'Document removed successfully']);
            }

            DB::commit();
            return response()->json(['success' => false, 'error' => 'Document not found']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error removing document for application {$applicationId}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['success' => false, 'error' => 'Failed to remove document: ' . $e->getMessage()], 500);
        }
    }
}
