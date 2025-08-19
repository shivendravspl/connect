<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\CoreState;
use App\Models\Employee;
use App\Models\VendorTempEdit;
use App\Helpers\user_notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;


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
        // Check if user already has an incomplete registration
        $existingVendor = Vendor::where('submitted_by', auth()->id())
            ->where('is_completed', false)
            ->first();

        if ($existingVendor) {
            // Redirect to edit page of incomplete registration
            return redirect()->route('vendors.edit', $existingVendor->id);
        }

        // For non-admin users, check if they've already completed a registration
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Admin', 'Mis User'])) {
            $completedVendor = Vendor::where('submitted_by', auth()->id())
                ->where('is_completed', true)
                ->exists();

            if ($completedVendor) {
                return redirect()->route('vendors.index')
                    ->with('info', 'You have already completed your vendor registration.');
            }
        }
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

        if ($step == 1) {
            $rules['contact_number'] = 'required|digits:10|unique:users,phone';
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

         if ($step == 1) {
        $contactNumber = $validated['contact_number'];

        $existingUser = User::where('phone', $contactNumber)->first();
            if (!$existingUser) {
                User::create([
                    'name' => $validated['contact_person_name'],
                    'email' => $validated['vendor_email'],
                    'phone' => $contactNumber,
                    'password' =>  Hash::make($contactNumber),
                    'status' => 'P', // default pending
                    'type' => 'vendor',
                ]);
            }else{
            return response()->json([
                'success' => false,
                'message' => 'The phone number is already registered.'
            ], 422);
            }
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

    // public function approve(Request $request, $id)
    // {
    //     $user = Auth::user();
    //     if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $vendor = Vendor::findOrFail($id);
    //     $vendor->update([
    //         'approval_status' => 'approved',
    //         'is_active' => true,
    //         'approved_by' => $user->id,
    //         'approved_at' => now(),
    //         'rejection_reason' => null
    //     ]);

    //     if ($vendor->submitted_by) {
    //         User::where('id', $vendor->submitted_by)
    //             ->where('status', 'P') // only if status is Pending
    //             ->update(['status' => 'A']);
    //     }

    //     return redirect()->route('vendors.index')
    //         ->with('success', 'Vendor approved successfully');
    // }

    // public function reject(Request $request, $id)
    // {
    //     $user = Auth::user();
    //     if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $request->validate([
    //         'rejection_reason' => 'required|string'
    //     ]);

    //     $vendor = Vendor::findOrFail($id);
    //     $vendor->update([
    //         'approval_status' => 'rejected',
    //         'is_active' => false,
    //         'approved_by' => $user->id,
    //         'approved_at' => now(),
    //         'rejection_reason' => $request->rejection_reason
    //     ]);

    //     return redirect()->route('vendors.index')
    //         ->with('success', 'Vendor rejected successfully');
    // }

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

        $fileFields = [
            'pan_card' => 'pan_card_copy_path',
            'aadhar_card' => 'aadhar_card_copy_path',
            'gst_certificate' => 'gst_certificate_copy_path',
            'msme_certificate' => 'msme_certificate_copy_path',
            'cancelled_cheque' => 'cancelled_cheque_copy_path',
            'agreement' => 'agreement_copy_path',
        ];

        if (!array_key_exists($type, $fileFields)) {
            abort(404, 'Invalid document type.');
        }

        $field = $fileFields[$type];
        $filePath = $vendor->$field;

        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            Log::error("Document not found: storage/app/public/{$filePath} for Vendor ID: {$id}, Type: {$type}");
            abort(404, 'Document not found.');
        }

        $fullPath = storage_path('app/public/' . $filePath);
        $mimeType = Storage::disk('public')->mimeType($filePath);

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
        ]);
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

    public function profile()
    {
        $user = Auth::user();
        if ($user->type !== 'vendor' || !$user->vendor || $user->vendor->approval_status !== 'approved') {
            abort(403, 'Unauthorized action.');
        }

        $vendor = Vendor::with(['state', 'vnrContactPerson'])->findOrFail($user->vendor->id);
        $states = CoreState::where('is_active', 1)->get();
        $departments = DB::table('core_department')
            ->where('is_active', '1')
            ->orderBy('department_name')
            ->get();
        $employees = $vendor->vnr_contact_department_id
            ? Employee::where('emp_dept_id', $vendor->vnr_contact_department_id)
            ->where('status', 'A')
            ->orderBy('emp_name')
            ->get(['id', 'emp_name', 'emp_contact', 'emp_department'])
            : collect();

        return view('vendors.profile', compact('vendor', 'states', 'departments', 'employees'));
    }

    public function storeSection(Request $request, $vendor)
    {
        $user = Auth::user();
        $vendor = Vendor::findOrFail($vendor);

        if ($user->type !== 'vendor' || $vendor->submitted_by != $user->id || $vendor->approval_status !== 'approved') {
            abort(403, 'Unauthorized action.');
        }

        $section = $request->input('section');
        $validSections = [
            'company',
            'contact',
            'legal',
            'banking',
            'pan_card',
            'aadhar_card',
            'gst_certificate',
            'msme_certificate',
            'cancelled_cheque',
            'agreement'
        ];

        if (!in_array($section, $validSections)) {
            abort(404, 'Invalid section.');
        }

        $rules = $this->getSectionValidationRules($section, $vendor->id);

        try {
            $validated = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('vendors.profile')->withErrors($e->errors())->withInput();
        }

        // Check for existing pending temp edit for this vendor
        $tempEdit = VendorTempEdit::where('vendor_id', $vendor->id)
            ->where('approval_status', 'pending')
            ->first();

        $tempData = [
            'vendor_id' => $vendor->id,
            'submitted_by' => $user->id,
            'approval_status' => 'pending',
            'is_active' => false,
            'is_completed' => false,
            'current_step' => 1,
        ];

        // Handle file uploads for document-specific sections
        $fileFields = [
            'pan_card' => 'pan_card_copy_path',
            'aadhar_card' => 'aadhar_card_copy_path',
            'gst_certificate' => 'gst_certificate_copy_path',
            'msme_certificate' => 'msme_certificate_copy_path',
            'cancelled_cheque' => 'cancelled_cheque_copy_path',
            'agreement' => 'agreement_copy_path',
        ];

        $changedData = [];

        if (array_key_exists($section, $fileFields)) {
            $fileField = $fileFields[$section];
            $inputName = $section . '_copy';
            if ($request->hasFile($inputName)) {
                $file = $request->file($inputName);
                $path = $file->store('vendor_temp_documents', 'public');
                if ($path !== ($vendor->$fileField ?? null)) {
                    $changedData[$fileField] = $path;
                }
            } else {
                return redirect()->route('vendors.profile')->withErrors([$inputName => 'No file uploaded.'])->withInput();
            }
        } else {
            foreach ($validated as $key => $value) {
                $vendorValue = $vendor->$key;
                if ($value !== null && $value !== '' && $value != $vendorValue) {
                    $changedData[$key] = $value;
                }
            }
        }

        if (!empty($changedData)) {
            $tempData = array_merge($tempData, $changedData);

            if ($tempEdit) {
                $tempEdit->update($tempData);
            } else {
                $tempEdit = VendorTempEdit::create($tempData); // Assign created record to $tempEdit
            }

            $adminUsers = User::role('Admin')->get();
            foreach ($adminUsers as $admin) {
                $notificationMessage = "Vendor {$vendor->company_name} has submitted changes for approval (ID: {$tempEdit->id})";
                user_notification::notifyUser($admin->id, 'Vendor Edit Approval Request', $notificationMessage);
            }

            return redirect()->route('vendors.profile')->with('success', 'Changes submitted for approval.');
        }

        return redirect()->route('vendors.profile')->with('info', 'No changes detected.');
    }

    private function getSectionValidationRules($section, $vendorId)
    {
        $rules = [];
        switch ($section) {
            case 'company':
                $rules = [
                    'company_name' => 'required|string|max:255',
                    'nature_of_business' => 'required|string|max:255',
                    'purpose_of_transaction' => 'required|string',
                    'company_address' => 'required|string',
                    'company_state_id' => 'required|exists:core_state,id',
                    'company_city' => 'required|string|max:100',
                    'pincode' => 'required|string|size:6',
                    'gst_number' => 'nullable|string|size:15',
                ];
                break;
            case 'contact':
                $rules = [
                    'vendor_email' => 'required|email|max:255',
                    'contact_person_name' => 'required|string|max:255',
                    'contact_number' => 'required|string|size:10',
                    'payment_terms' => 'required|string|max:255',
                ];
                break;
            case 'legal':
                $rules = [
                    'legal_status' => 'required|string|max:255',
                    'pan_number' => 'required|string|size:10',
                    'aadhar_number' => 'required|string|size:12',
                    'gst_number' => 'nullable|string|size:15',
                    'msme_number' => 'nullable|string|max:255',
                ];
                break;
            case 'banking':
                $rules = [
                    'bank_account_holder_name' => 'required|string|max:255',
                    'bank_account_number' => 'required|string|max:255',
                    'ifsc_code' => 'required|string|size:11',
                    'bank_branch' => 'required|string|max:255',
                ];
                break;
            case 'pan_card':
                $rules = [
                    'pan_card_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                ];
                break;
            case 'aadhar_card':
                $rules = [
                    'aadhar_card_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                ];
                break;
            case 'gst_certificate':
                $rules = [
                    'gst_certificate_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                ];
                break;
            case 'msme_certificate':
                $rules = [
                    'msme_certificate_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                ];
                break;
            case 'cancelled_cheque':
                $rules = [
                    'cancelled_cheque_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                ];
                break;
            case 'agreement':
                $rules = [
                    'agreement_copy' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                ];
                break;
            case 'other_documents':
                $rules = [
                    'other_documents' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
                ];
                break;
        }
        return $rules;
    }


    // public function approveTempEdit(Request $request, $id)
    // {
    //     $user = Auth::user();
    //     if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $tempEdit = VendorTempEdit::findOrFail($id);
    //     $vendor = Vendor::findOrFail($tempEdit->vendor_id);

    //     $fileFields = [
    //         'pan_card_copy_path',
    //         'aadhar_card_copy_path',
    //         'gst_certificate_copy_path',
    //         'msme_certificate_copy_path',
    //         'cancelled_cheque_copy_path',
    //         'agreement_copy_path',
    //         'other_documents_path',
    //     ];

    //     // Move files from vendor_temp_documents to vendor_documents
    //     foreach ($fileFields as $field) {
    //         if ($tempEdit->$field && Storage::disk('public')->exists($tempEdit->$field)) {
    //             if ($vendor->$field && Storage::disk('public')->exists($vendor->$field)) {
    //                 Storage::disk('public')->delete($vendor->$field);
    //             }
    //             $newPath = str_replace('vendor_temp_documents', 'vendor_documents', $tempEdit->$field);
    //             Storage::disk('public')->move($tempEdit->$field, $newPath);
    //             $vendor->$field = $newPath;
    //         }
    //     }

    //     // Update only non-null fields from tempEdit
    //     $updateData = array_filter($tempEdit->toArray(), function ($value, $key) use ($fileFields) {
    //         return !is_null($value) && !in_array($key, ['id', 'vendor_id', 'submitted_by', 'approval_status', 'is_active', 'is_completed', 'current_step', 'rejection_reason', 'approved_by', 'approved_at', 'created_at', 'updated_at']);
    //     }, ARRAY_FILTER_USE_BOTH);

    //     $updateData['approved_by'] = $user->id;
    //     $updateData['approved_at'] = now();
    //     $updateData['rejection_reason'] = null;

    //     $vendor->update($updateData);

    //     $tempEdit->update([
    //         'approval_status' => 'approved',
    //         'approved_by' => $user->id,
    //         'approved_at' => now(),
    //     ]);

    //     return redirect()->route('vendors.temp-edits')
    //         ->with('success', 'Vendor edit approved successfully.');
    // }

    // public function rejectTempEdit(Request $request, $id)
    // {
    //     $user = Auth::user();
    //     if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $request->validate([
    //         'rejection_reason' => 'required|string'
    //     ]);

    //     $tempEdit = VendorTempEdit::findOrFail($id);
    //     $tempEdit->update([
    //         'approval_status' => 'rejected',
    //         'approved_by' => $user->id,
    //         'approved_at' => now(),
    //         'rejection_reason' => $request->rejection_reason
    //     ]);

    //     return redirect()->route('vendors.temp-edits')
    //         ->with('success', 'Vendor edit rejected successfully.');
    // }

    //  public function tempEdits()
    // {
    //     $user = Auth::user();
    //     if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $tempEdits = VendorTempEdit::with(['vendor', 'submittedBy'])
    //         ->where('approval_status', 'pending')
    //         ->paginate(10);

    //     return view('vendors.temp-edits', compact('tempEdits'));
    // }

    // public function showTempEdit($id)
    // {
    //     $user = Auth::user();
    //     if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     $tempEdit = VendorTempEdit::with(['vendor', 'submittedBy'])->findOrFail($id);
    //     return view('vendors.temp-edit-show', compact('tempEdit'));
    // }
}
