<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\CoreState;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class VendorController extends Controller
{
    public function index()
    {
        $vendor_list = Vendor::paginate(10); // or any number you prefer per page

        return view('vendors.index', compact('vendor_list'));
    }
    public function create()
    {
        $states = CoreState::where('is_active', 1)->get();
        $departments = DB::table('core_department')
            ->where('is_active', '1')
            ->orderBy('department_name')
            ->get();

        return view('vendors.create', [
            'states' => $states,
            'departments' => $departments,
            'current_step' => 1,
            'vendor' => new Vendor(), // Create empty vendor model
            'employees' => collect() // Empty collection for employees
        ]);
    }

    public function store(Request $request)
    {
        $step = $request->input('current_step', 1);
        $vendorId = $request->input('vendor_id');

        $rules = $this->getValidationRules($step);
        // Handle unique email validation for updates
        if ($vendorId) {
            $rules['vendor_email'] = 'required|email|unique:vendors,vendor_email,' . $vendorId;
        }

        $validated = $request->validate($rules);

        if ($vendorId) {
            $vendor = Vendor::findOrFail($vendorId);
            $vendor->update($validated);
        } else {
            $vendor = Vendor::create($validated);
        }

        // Handle file uploads
        $this->handleFileUploads($request, $vendor, $step);

        // Check if this is final submit (step 3)
        $isFinalSubmit = $step == 3;

        if ($isFinalSubmit) {
            $vendor->update(['is_completed' => true, 'current_step' => 3]);
            return response()->json([
                'success' => true,
                'redirect' => route('vendors.success', $vendor->id)
            ]);
        } else {
            $vendor->update(['current_step' => $step + 1]);
            return response()->json([
                'success' => true,
                'vendor_id' => $vendor->id,
                'next_step' => $step + 1
            ]);
        }
    }

    public function edit($id)
    {

        $vendor = Vendor::findOrFail($id);
        $states = CoreState::where('is_active', 1)->get();
        $departments = DB::table('core_department')
            ->where('is_active', '1')
            ->orderBy('department_name')
            ->get();

        // Get employees for the vendor's department
        $employees = [];
        if ($vendor->vnr_contact_department_id) {
            $employees = Employee::where('emp_dept_id', $vendor->vnr_contact_department_id)
                ->where('status', 'A')
                ->orderBy('emp_name')
                ->get(['id', 'emp_name', 'emp_contact', 'emp_department']);
        }
        // /dd($vendor->vnr_contact_person_id);
        return view('vendors.create', [
            'vendor' => $vendor,
            'states' => $states,
            'departments' => $departments,
            'employees' => $employees,
            'current_step' => $vendor->current_step
        ]);
    }

    private function getValidationRules($step)
    {
        $rules = [
            'current_step' => 'required|numeric',
        ];

        switch ($step) {
            case 1: // Company Information
                $rules += [
                    'company_name' => 'required|string|max:255',
                    'nature_of_business' => 'required|string',
                    'purpose_of_transaction' => 'required|string',
                    'company_address' => 'required|string',
                    'company_state_id' => 'required|exists:core_state,id',
                    'company_city' => 'required|string',
                    'pincode' => 'required|digits:6',
                    'vendor_email' => 'required|email|unique:vendors,vendor_email',
                    'contact_person_name' => 'required|string',
                    'contact_number' => 'required|digits:10',
                    'vnr_contact_department_id' => 'required',
                    'vnr_contact_person_id' => 'required',
                    'payment_terms' => 'required|string',
                ];
                break;

            case 2: // Legal Information
                $rules += [
                    'legal_status' => 'required|string',
                    'pan_number' => 'required|regex:/[A-Z]{5}[0-9]{4}[A-Z]{1}/',
                    'pan_card_copy' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                    'aadhar_number' => 'required|digits:12',
                    'aadhar_card_copy' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                    'gst_number' => 'required|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
                    'gst_certificate_copy' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                    'msme_number' => 'required|string',
                    'msme_certificate_copy' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                ];
                break;

            case 3: // Banking Information
                $rules += [
                    'bank_account_holder_name' => 'required|string',
                    'bank_account_number' => 'required|numeric',
                    'ifsc_code' => 'required|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
                    'bank_branch' => 'required|string',
                    'cancelled_cheque_copy' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                ];
                break;
        }

        return $rules;
    }

    private function handleFileUploads($request, $vendor, $step)
    {
        $uploadFields = [];

        if ($step == 2) {
            $uploadFields = [
                'pan_card_copy' => 'pan_card_copy_path',
                'aadhar_card_copy' => 'aadhar_card_copy_path',
                'gst_certificate_copy' => 'gst_certificate_copy_path',
                'msme_certificate_copy' => 'msme_certificate_copy_path',
            ];
        } elseif ($step == 3) {
            $uploadFields = [
                'cancelled_cheque_copy' => 'cancelled_cheque_copy_path',
            ];
        }

        foreach ($uploadFields as $requestField => $dbField) {
            if ($request->hasFile($requestField)) {
                // Delete old file if exists
                if ($vendor->$dbField && Storage::disk('public')->exists($vendor->$dbField)) {
                    Storage::disk('public')->delete($vendor->$dbField);
                }

                // Store to public disk with original filename
                $path = $request->file($requestField)->storeAs(
                    'vendor_documents',
                    $request->file($requestField)->getClientOriginalName(),
                    'public'
                );

                $vendor->update([$dbField => $path]);
            }
        }
    }
    public function success($id)
    {
        $vendor = Vendor::findOrFail($id);
        return view('vendors.success', compact('vendor'));
    }

    public function show($id)
    {
        $vendor = Vendor::with(['state', 'vnrContactPerson'])->findOrFail($id);
        return view('vendors.show', compact('vendor'));
    }

    public function getEmployee($departmentId)
    {
        return Employee::where('status', 'A')
            ->where('emp_dept_id', $departmentId)
            ->orderBy('emp_name')
            ->get(['id', 'emp_name as text', 'emp_contact', 'emp_department']);
    }

    public function destroy($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->delete();

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor deleted successfully');
    }

   public function showDocument($id, $type)
{
    $vendor = Vendor::findOrFail($id);
    $validTypes = [
        'pan_card', 
        'aadhar_card', 
        'gst_certificate', 
        'msme_certificate', 
        'cancelled_cheque'
    ];
    
    if (!in_array($type, $validTypes)) {
        abort(404);
    }
    
    $path = $vendor->{$type.'_copy_path'};
    
    if (!$path || !Storage::disk('public')->exists($path)) {
        abort(404);
    }

    $file = Storage::disk('public')->get($path);
    $mimeType = Storage::disk('public')->mimeType($path);
    $fileName = basename($path);

    return response($file, 200)
        ->header('Content-Type', $mimeType)
        ->header('Content-Disposition', 'inline; filename="'.$fileName.'"');
}
}
