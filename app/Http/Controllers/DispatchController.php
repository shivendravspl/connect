<?php

namespace App\Http\Controllers;

use App\Models\Onboarding;
use App\Models\PhysicalDispatch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DispatchController extends Controller
{
    public function show($id)
    {
        $application = Onboarding::findOrFail($id);
        if ($application->created_by !== Auth::user()->emp_id) {
            return redirect()->route('applications.index')->with('error', 'Unauthorized to fill dispatch details.');
        }

        $allDispatches = PhysicalDispatch::where('application_id', $application->id)
            ->orderBy('created_at', 'desc')
            ->get();
        $latestDispatch = $allDispatches->first();
        $previousDispatches = $allDispatches->slice(1);

        // Check if we should show create form or view
        $canRedispatch = $this->canRedispatch($application);
        $dispatch = new PhysicalDispatch(); // Always create new for form

        return view('applications.dispatch.show', compact(
            'application',
            'dispatch',
            'latestDispatch',
            'previousDispatches',
            'canRedispatch'
        ));
    }

    public function store(Request $request, $id)
    {
        try {
            $application = Onboarding::findOrFail($id);

            if ($application->created_by !== Auth::user()->emp_id) {
                return redirect()->route('applications.index')->with('error', 'Unauthorized to fill dispatch details.');
            }

            // Check if redispatch is allowed
            if (!$this->canRedispatch($application)) {
                return redirect()->route('applications.index')->with('error', 'Cannot dispatch documents in current status.');
            }

            // Log request data for debugging
            Log::info('Dispatch form submission data: ', $request->all());

            $validated = $request->validate([
                'mode' => 'required|in:transport,courier,by_hand',
                'dispatch_date' => 'required|date|before_or_equal:today',
                'transport_name' => 'nullable|required_if:mode,transport|string|max:255',
                'driver_name' => 'nullable|required_if:mode,transport|string|max:255',
                'driver_contact' => 'nullable|required_if:mode,transport|string|max:255',
                'docket_number' => 'nullable|required_if:mode,courier|string|max:255',
                'courier_company_name' => 'nullable|required_if:mode,courier|string|max:255',
                'person_name' => 'nullable|required_if:mode,by_hand|string|max:255',
                'person_contact' => 'nullable|required_if:mode,by_hand|string|max:255',
            ]);

            DB::beginTransaction();

            // Get previous dispatches count to determine if this is first time or redispatch
            $previousDispatchesCount = PhysicalDispatch::where('application_id', $application->id)->count();
            $isRedispatch = $previousDispatchesCount > 0;

            // Create new dispatch record (always create new, never update)
            $dispatch = new PhysicalDispatch();
            $dispatch->application_id = $application->id;
            $dispatch->fill($validated);
            $dispatch->created_by = Auth::user()->emp_id;
            $dispatch->save();

            // Update application status based on whether it's first dispatch or redispatch
            if ($isRedispatch) {
                // This is a redispatch - use the new status
                $application->status = 'physical_docs_redispatched';
                $application->physical_docs_status = 'redispatched';
                $application->save();

                Log::info('Application status changed to physical_docs_redispatched', [
                    'application_id' => $application->id,
                    'dispatch_id' => $dispatch->id,
                    'previous_dispatches_count' => $previousDispatchesCount
                ]);

                // Send notification to MIS about redispatch
                $this->sendRedispatchNotification($application, $dispatch);
            } else {
                // First time dispatch
                // $application->status = 'documents_pending';
                // $application->physical_docs_status = 'pending';
                // $application->save();    
                 $application->physical_docs_status = 'dispatched';
                 $application->save();            
                // Send initial dispatch notification
                $this->sendDispatchNotification($application, $dispatch);
            }

            DB::commit();

            $message = $isRedispatch
                ? 'Documents redispatched successfully.'
                : 'Dispatch details submitted successfully.';

            return redirect()->route('applications.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving dispatch details: ' . $e->getMessage(), ['request' => $request->all()]);
            return redirect()->back()->withInput()->with('error', 'Failed to save dispatch details: ' . $e->getMessage());
        }
    }

    private function canRedispatch($application)
    {
        // Allow dispatch in these statuses - updated to include the new status
        $allowedStatuses = [
            'documents_verified',      // First time dispatch after verification
            'mis_processing',          // During MIS processing
            'physical_docs_pending',   // Redispatch after issues
            'physical_docs_redispatched' // Allow multiple redispatches if needed
        ];

        return in_array($application->status, $allowedStatuses);
    }

    private function sendDispatchNotification($application, $dispatch)
    {
        try {
            $misUsers = User::where('role', 'mis')->get();
            $establishmentName = $application->entityDetails->establishment_name ?? 'Unknown';
            $applicationId = $application->id;

            $title = "Documents Dispatched - {$establishmentName}";
            $description = "Physical documents have been dispatched for {$establishmentName}.\n\n";
            $description .= "Application ID: {$applicationId}\n";
            $description .= "Dispatch Mode: " . ucfirst($dispatch->mode) . "\n";
            $description .= "Dispatch Date: {$dispatch->dispatch_date}\n\n";
            $description .= "Please verify the documents upon receipt.";

            foreach ($misUsers as $user) {
                DB::table('notification')->insert([
                    'userid' => $user->id,
                    'notification_read' => 0,
                    'title' => $title,
                    'description' => $description,
                    'created_at' => now(),
                ]);
            }

            Log::info("Dispatch notification sent", [
                'application_id' => $applicationId,
                'dispatch_id' => $dispatch->id,
                'mis_users_count' => $misUsers->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send dispatch notification', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    private function sendRedispatchNotification($application, $dispatch)
    {
        try {
            $misUsers = collect();

            // Method 1: First check current_approver_id (specific MIS user assigned to this application)
            if ($application->current_approver_id) {
                $misUsers = User::where('emp_id', $application->current_approver_id)->where('status', 'A')->get();
            }

            // Method 2: If no current approver found, get users by email pattern (MIS users)
            if ($misUsers->isEmpty()) {
                $misUsers = User::where(function ($query) {
                    $query->where('email', 'like', '%mis%')
                        ->orWhere('email', 'like', '%@vnrseeds.in')
                        ->orWhere('email', 'like', 'dm1.mis%');
                })->where('status', 'A')->get();
            }

            // Method 3: If still no users, get from known MIS emp_ids
            if ($misUsers->isEmpty()) {
                $knownMisEmpIds = [1971]; // Anubrat Diwan and Ansuman Patnaik
                $misUsers = User::whereIn('emp_id', $knownMisEmpIds)->where('status', 'A')->get();
            }

            // Method 4: Final fallback - specific user IDs
            if ($misUsers->isEmpty()) {
                $knownMisUserIds = [9]; // User IDs from your table
                $misUsers = User::whereIn('id', $knownMisUserIds)->where('status', 'A')->get();
            }

            if ($misUsers->isEmpty()) {
                Log::warning('No MIS users found for redispatch notification', [
                    'application_id' => $application->id,
                    'current_approver_id' => $application->current_approver_id
                ]);
                return;
            }

            $establishmentName = $application->entityDetails->establishment_name ?? 'Unknown';
            $applicationId = $application->id;

            $title = "Documents Redispatched - {$establishmentName}";
            $description = "Physical documents have been redispatched for {$establishmentName}.\n\n";
            $description .= "Application ID: {$applicationId}\n";
            $description .= "Dispatch Mode: " . ucfirst($dispatch->mode) . "\n";
            $description .= "Dispatch Date: {$dispatch->dispatch_date}\n\n";
            $description .= "Previous documents had issues. Please check and verify the new documents when received.";

            foreach ($misUsers as $user) {
                DB::table('notification')->insert([
                    'userid' => $user->id,
                    'notification_read' => 0,
                    'title' => $title,
                    'description' => $description,
                    'created_at' => now(),
                ]);
            }

            Log::info("Redispatch notification sent", [
                'application_id' => $applicationId,
                'dispatch_id' => $dispatch->id,
                'mis_users_count' => $misUsers->count(),
                'mis_users' => $misUsers->pluck('name')->toArray(),
                'current_approver_id' => $application->current_approver_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send redispatch notification', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
