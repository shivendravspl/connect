<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorTempEdit;
use App\Helpers\user_notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File; 
use Illuminate\Support\Facades\Log;

class VendorApprovalController extends Controller
{
	public function tempEdits()
	{
		$user = Auth::user();
		if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
			abort(403, 'Unauthorized action.');
		}

		$tempEdits = VendorTempEdit::with(['vendor', 'submittedBy'])
			->where('approval_status', 'pending')
			->paginate(10);

		return view('vendors.temp-edits', compact('tempEdits'));
	}

	public function showTempEdit($id)
	{
		$user = Auth::user();
		if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
			abort(403, 'Unauthorized action.');
		}

		$tempEdit = VendorTempEdit::with(['vendor', 'submittedBy'])->findOrFail($id);
		return view('vendors.temp-edit-show', compact('tempEdit'));
	}

	 public function approveTempEdit(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $tempEdit = VendorTempEdit::findOrFail($id);
        $vendor = Vendor::findOrFail($tempEdit->vendor_id);

        $fileFields = [
            'pan_card_copy_path',
            'aadhar_card_copy_path',
            'gst_certificate_copy_path',
            'msme_certificate_copy_path',
            'cancelled_cheque_copy_path',
            'agreement_copy_path',
        ];

        // Handle file updates similar to VendorController
        foreach ($fileFields as $field) {
            if ($tempEdit->$field) {
                $tempPath = ltrim($tempEdit->$field, '/\\');
                
                if (!Storage::disk('public')->exists($tempPath)) {
                    Log::error("File does not exist: storage/app/public/{$tempPath} for VendorTempEdit ID: {$id}, Field: {$field}");
                    continue;
                }

                // Delete old vendor file if it exists
                if ($vendor->$field && Storage::disk('public')->exists($vendor->$field)) {
                    Storage::disk('public')->delete($vendor->$field);
                }

                // Get the original filename
                $filename = basename($tempPath);
                
                // Store in vendor_documents with the original filename
                $newPath = Storage::disk('public')->putFileAs(
                    'vendor_documents',
                    new File(storage_path("app/public/{$tempPath}")),
                    $filename
                );

                Log::info("Moved file from {$tempPath} to {$newPath} for Vendor ID: {$vendor->id}, Field: {$field}");
                
                // Update vendor with the new path
                $vendor->$field = $newPath;
            }
        }

        // Rest of your method remains the same...
        $vendor->save();

        // Update non-file fields from tempEdit
        $updateData = array_filter($tempEdit->toArray(), function ($value, $key) use ($fileFields) {
            return !is_null($value) && !in_array($key, [
                'id', 'vendor_id', 'submitted_by', 'approval_status', 
                'is_active', 'is_completed', 'current_step', 
                'rejection_reason', 'approved_by', 'approved_at', 
                'created_at', 'updated_at'
            ] + $fileFields);
        }, ARRAY_FILTER_USE_BOTH);

        $updateData['approved_by'] = $user->id;
        $updateData['approved_at'] = now();
        $updateData['rejection_reason'] = null;

        $vendor->update($updateData);

        $tempEdit->update([
            'approval_status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return redirect()->route('temp-edits')
            ->with('success', 'Vendor edit approved successfully.');
    }

	public function rejectTempEdit(Request $request, $id)
	{
		$user = Auth::user();
		if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
			abort(403, 'Unauthorized action.');
		}

		$request->validate([
			'rejection_reason' => 'required|string'
		]);

		$tempEdit = VendorTempEdit::findOrFail($id);
		$tempEdit->update([
			'approval_status' => 'rejected',
			'approved_by' => $user->id,
			'approved_at' => now(),
			'rejection_reason' => $request->rejection_reason
		]);

		return redirect()->route('vendors.temp-edits')
			->with('success', 'Vendor edit rejected successfully.');
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

		if ($vendor->submitted_by) {
			User::where('id', $vendor->submitted_by)
				->where('status', 'P') // only if status is Pending
				->update(['status' => 'A']);
		}

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

	// Add to VendorApprovalController.php
	public function showTempDocument($id, $type)
	{
		$user = Auth::user();
		if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
			abort(403, 'Unauthorized action.');
		}

		$tempEdit = VendorTempEdit::findOrFail($id);
		$validTypes = [
			'pan_card',
			'aadhar_card',
			'gst_certificate',
			'msme_certificate',
			'cancelled_cheque',
			'agreement',
			'other_documents'
		];

		if (!in_array($type, $validTypes)) {
			abort(404);
		}

		$path = $tempEdit->{$type . '_copy_path'};
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
					'vnr_contact_department_id' => 'required|exists:core_department,id',
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
			case 'additional':
				$rules = [];
				break;
		}
		return $rules;
	}
}
