<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\CoreState;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $query = Vendor::query();

        // Non-admin users only see their own submissions
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            $query->where('submitted_by', $user->id);
        }

        $vendor_list = $query->paginate(10);

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
            'vendor' => new Vendor(),
            'employees' => collect()
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $step = $request->input('current_step', 1);
        $vendorId = $request->input('vendor_id');

        $rules = $this->getValidationRules($step, $vendorId);
        if ($vendorId) {
            $rules['vendor_email'] = 'required|email|unique:vendors,vendor_email,' . $vendorId;
        }

        $validated = $request->validate($rules);

        if ($vendorId) {
            $vendor = Vendor::findOrFail($vendorId);
            $vendor->update($validated);
        } else {
            $validated['approval_status'] = 'pending';
            $validated['submitted_by'] = $user->id;
            $validated['is_active'] = false;
            $vendor = Vendor::create($validated);
        }

        $this->handleFileUploads($request, $vendor, $step);

        $isFinalSubmit = $step == 3;

        if ($isFinalSubmit) {
            if ($user->hasAnyRole(['Super Admin', 'Admin'])) {
                $vendor->update([
                    'is_completed' => true,
                    'current_step' => 3,
                    'approval_status' => 'approved',
                    'is_active' => true,
                    'approved_by' => $user->id,
                    'approved_at' => now()
                ]);
                return response()->json([
                    'success' => true,
                    'redirect' => route('vendors.success', $vendor->id)
                ]);
            } else {
                $vendor->update([
                    'is_completed' => true,
                    'current_step' => 3,
                    'approval_status' => 'pending',
                    'is_active' => false
                ]);
                return response()->json([
                    'success' => true,
                    'redirect' => route('vendors.submitted', $vendor->id)
                ]);
            }
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
        $user = Auth::user();
        // Check if user has permission to edit
        if (!$user->hasAnyRole(['Super Admin', 'Admin']) && $vendor->submitted_by != $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $states = CoreState::where('is_active', 1)->get();

        $departments = DB::table('core_department')
            ->where('is_active', '1')
            ->orderBy('department_name')
            ->get();

        $employees = [];
        if ($vendor->vnr_contact_department_id) {
            $employees = Employee::where('emp_dept_id', $vendor->vnr_contact_department_id)
                ->where('status', 'A')
                ->orderBy('emp_name')
                ->get(['id', 'emp_name', 'emp_contact', 'emp_department']);
        }

        return view('vendors.create', [
            'vendor' => $vendor,
            'states' => $states,
            'departments' => $departments,
            'employees' => $employees,
            'current_step' => $vendor->current_step
        ]);
    }

    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $vendor = Vendor::findOrFail($id);
        $vendor->update([
            'approval_status' => 'approved',
            'is_active' => true,
            'approved_by' => $user->id,
            'approved_at' => now(),
            'rejection_reason' => null
        ]);

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor approved successfully');
    }

    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'rejection_reason' => 'required|string'
        ]);

        $vendor = Vendor::findOrFail($id);
        $vendor->update([
            'approval_status' => 'rejected',
            'is_active' => false,
            'approved_by' => $user->id,
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason
        ]);

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor rejected successfully');
    }

    private function getValidationRules($step, $vendorId = null)
    {
        $rules = [
            'current_step' => 'required|numeric',
        ];
        $vendor = $vendorId ? Vendor::find($vendorId) : null;

        switch ($step) {
            case 1:
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

            case 2:
                $rules += [
                    'legal_status' => 'required|string',
                    'pan_number' => 'required|regex:/[A-Z]{5}[0-9]{4}[A-Z]{1}/',
                    'pan_card_copy' => ($vendor && $vendor->pan_card_copy_path) ? 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048' : 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                    'aadhar_number' => 'required|digits:12',
                    'aadhar_card_copy' => ($vendor && $vendor->aadhar_card_copy_path) ? 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048' : 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                    'gst_number' => 'required|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/',
                    'gst_certificate_copy' => ($vendor && $vendor->gst_certificate_copy_path) ? 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048' : 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                    'msme_number' => 'required|string',
                    'msme_certificate_copy' => ($vendor && $vendor->msme_certificate_copy_path) ? 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048' : 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
                ];
                break;

            case 3:
                $rules += [
                    'bank_account_holder_name' => 'required|string',
                    'bank_account_number' => 'required|numeric',
                    'ifsc_code' => 'required|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
                    'bank_branch' => 'required|string',
                    'cancelled_cheque_copy' => ($vendor && $vendor->cancelled_cheque_copy_path) ? 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048' : 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
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
                if ($vendor->$dbField && Storage::disk('public')->exists($vendor->$dbField)) {
                    Storage::disk('public')->delete($vendor->$dbField);
                }

                $path = $request->file($requestField)->storeAs(
                    'vendor_documents',
                    $request->file($requestField)->getClientOriginalName(),
                    'public'
                );

                $vendor->update([$dbField => $path]);
            }
        }
    }

    public function submitted($id)
    {
        $vendor = Vendor::findOrFail($id);
        return view('vendors.submitted', compact('vendor'));
    }

    public function success($id)
    {
        $vendor = Vendor::findOrFail($id);
        return view('vendors.success', compact('vendor'));
    }

    public function show($id)
    {
        $vendor = Vendor::with(['state', 'vnrContactPerson'])->findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['Super Admin', 'Admin']) && $vendor->submitted_by != $user->id) {
            abort(403, 'Unauthorized action.');
        }

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
        $user = Auth::user();

        if (!$user->hasAnyRole(['Super Admin', 'Admin']) && $vendor->submitted_by != $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $vendor->delete();

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor deleted successfully');
    }

    public function showDocument($id, $type)
    {
        $vendor = Vendor::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['Super Admin', 'Admin']) && $vendor->submitted_by != $user->id) {
            abort(403, 'Unauthorized action.');
        }

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

        $path = $vendor->{$type . '_copy_path'};

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $file = Storage::disk('public')->get($path);
        $mimeType = Storage::disk('public')->mimeType($path);
        $fileName = basename($path);

        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
    }

    public function toggleActive(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $vendor = Vendor::findOrFail($id);
        $vendor->update([
            'is_active' => !$vendor->is_active,
            'approved_by' => $user->id,
            'approved_at' => now()
        ]);

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor ' . ($vendor->is_active ? 'activated' : 'deactivated') . ' successfully');
    }
}
