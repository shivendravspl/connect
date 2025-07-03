<?php

namespace App\Http\Controllers;

use App\Models\DistributorApplication;
use App\Models\ApprovalLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\ApplicationApproved;
use App\Notifications\ApplicationRejected;
use App\Notifications\ApplicationReverted;
use App\Notifications\ApplicationOnHold;

class ApprovalController extends Controller
{
    public function approve(Request $request, DistributorApplication $application)
    {
        $this->authorize('approve', $application);
        
        $validated = $request->validate([
            'remarks' => 'nullable|string|max:500',
        ]);
        
        $nextStatus = $this->getNextStatus($application->status);
        
        $approvalLog = $application->approvalLogs()->create([
            'user_id' => Auth::id(),
            'role' => $application->currentApproverRole(),
            'action' => 'approve',
            'remarks' => $validated['remarks'] ?? null,
        ]);
        
        $application->update(['status' => $nextStatus]);
        
        // Notify next approver or applicant if final approval
        if ($nextStatus === 'gm_approved') {
            $this->notifyMIS($application);
        } elseif ($nextStatus === 'completed') {
            $application->creator->notify(new ApplicationApproved($application));
        } else {
            $this->notifyNextApprover($application);
        }
        
        return redirect()->back()
            ->with('success', 'Application approved successfully!');
    }
    
    public function reject(Request $request, DistributorApplication $application)
    {
        $this->authorize('approve', $application);
        
        $validated = $request->validate([
            'remarks' => 'required|string|max:500',
        ]);
        
        $approvalLog = $application->approvalLogs()->create([
            'user_id' => Auth::id(),
            'role' => $application->currentApproverRole(),
            'action' => 'reject',
            'remarks' => $validated['remarks'],
        ]);
        
        $application->update(['status' => 'rejected']);
        
        $application->creator->notify(new ApplicationRejected($application));
        
        return redirect()->back()
            ->with('success', 'Application rejected successfully!');
    }
    
    public function revert(Request $request, DistributorApplication $application)
    {
        $this->authorize('approve', $application);
        
        $validated = $request->validate([
            'remarks' => 'required|string|max:500',
        ]);
        
        $approvalLog = $application->approvalLogs()->create([
            'user_id' => Auth::id(),
            'role' => $application->currentApproverRole(),
            'action' => 'revert',
            'remarks' => $validated['remarks'],
        ]);
        
        $application->update(['status' => 'reverted']);
        
        $application->creator->notify(new ApplicationReverted($application));
        
        return redirect()->back()
            ->with('success', 'Application reverted to applicant successfully!');
    }
    
    public function hold(Request $request, DistributorApplication $application)
    {
        $this->authorize('approve', $application);
        
        $validated = $request->validate([
            'remarks' => 'required|string|max:500',
            'follow_up_date' => 'required|date|after:today',
        ]);
        
        $approvalLog = $application->approvalLogs()->create([
            'user_id' => Auth::id(),
            'role' => $application->currentApproverRole(),
            'action' => 'hold',
            'remarks' => $validated['remarks'],
            'follow_up_date' => $validated['follow_up_date'],
        ]);
        
        $application->update(['status' => 'on_hold']);
        
        $application->creator->notify(new ApplicationOnHold($application));
        
        return redirect()->back()
            ->with('success', 'Application put on hold successfully!');
    }
    
    protected function getNextStatus($currentStatus)
    {
        $statusFlow = [
            'submitted' => 'rbm_approved',
            'rbm_approved' => 'zbm_approved',
            'zbm_approved' => 'gm_approved',
            'gm_approved' => 'completed',
        ];
        
        return $statusFlow[$currentStatus] ?? $currentStatus;
    }
    
    protected function notifyNextApprover($application)
    {
        $nextRole = $application->currentApproverRole();
        $approvers = User::role($nextRole)
            ->where('region', $application->region)
            ->get();
            
        foreach ($approvers as $approver) {
            $approver->notify(new ApplicationApprovalRequired($application));
        }
    }
    
    protected function notifyMIS($application)
    {
        $misUsers = User::role('mis')->get();
        
        foreach ($misUsers as $user) {
            $user->notify(new ApplicationReadyForProcessing($application));
        }
    }
}