<?php

namespace App\Http\Controllers;

use App\Models\Onboarding;
use App\Models\ApprovalLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;
use Illuminate\Support\Facades\Mail;
use App\Mail\ApplicationNotification;

class ApprovalController extends Controller
{

    public function show(Onboarding $application)
    {
        dd($application->current_approver_id);
        $user = Auth::user();
        // Authorization check - only approver or creator can view
        if ($application->current_approver_id !== $user->emp_id && $application->created_by !== $user->emp_id) {
            abort(403);
        }

        $application->load([
            'entityDetails',
            'distributionDetail',
            'businessPlans',
            'financialInfo',
            'existingDistributorships',
            'bankDetail',
            'declarations',
            'approvalLogs.user'
        ]);

        return view('approvals.show', compact('application'));
    }
    public function approve(Request $request, Onboarding $application)
    {
        $user = Auth::user();
        $this->authorizeApproval($application, $user->emp_id);

        $currentApprover = Employee::findOrFail($user->emp_id);

        // Log approval action
        $this->createApprovalLog($application->id, $user->emp_id, $currentApprover->emp_designation, 'approved', $request->remarks);

        $nextApprover = $currentApprover->reportingManager;

        if ($this->isFinalApproval($currentApprover, $nextApprover)) {
            $this->finalizeApproval($application);
            $this->notifyMISTeam($application);
            $this->notifyBusinessHead($application);
        } else {
            $this->moveToNextApprover($application, $nextApprover);
            $this->notifyNextApprover($nextApprover, $application);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Application approved successfully');
    }

    public function reject(Request $request, Onboarding $application)
    {
        $user = Auth::user();
        $this->authorizeApproval($application, $user->emp_id);
        $request->validate(['remarks' => 'required|string']);

        $currentApprover = Employee::findOrFail($user->emp_id);
        $this->createApprovalLog($application->id, $user->emp_id, $currentApprover->emp_designation, 'rejected', $request->remarks);

        $application->update(['status' => 'rejected','current_approver_id' => $user->emp_id]);
        $this->notifyCreator($application, 'Application Rejected', $request->remarks);

        return redirect()->route('dashboard')
            ->with('success', 'Application rejected successfully');
    }

    public function revert(Request $request, Onboarding $application)
    {
        $user = Auth::user();
        $this->authorizeApproval($application, $user->emp_id);
        $request->validate(['remarks' => 'required|string']);

        $currentApprover = Employee::findOrFail($user->emp_id);
        $this->createApprovalLog($application->id, $user->emp_id, $currentApprover->emp_designation, 'reverted', $request->remarks);

        $application->update(['status' => 'reverted']);
        $this->notifyCreator($application, 'Application Reverted', $request->remarks);

        return redirect()->route('dashboard')
            ->with('success', 'Application reverted successfully');
    }

    public function hold(Request $request, Onboarding $application)
    {
        $user = Auth::user();
        $this->authorizeApproval($application, $user->emp_id);
        $request->validate([
            'remarks' => 'required|string',
            'follow_up_date' => 'required|date|after:now'
        ]);

        $currentApprover = Employee::findOrFail($user->emp_id);
        $this->createApprovalLog(
            $application->id,
            $user->emp_id,
            $currentApprover->emp_designation,
            'hold',
            $request->remarks,
            $request->follow_up_date
        );

        $application->update(['status' => 'on_hold']);
        $this->scheduleFollowUp($application, $request->follow_up_date);

        return redirect()->route('dashboard')
            ->with('success', 'Application put on hold successfully');
    }

    // ==================== PRIVATE METHODS ====================

    private function authorizeApproval($application, $empId)
    {
        if ($application->current_approver_id !== $empId) {
            abort(403, 'Unauthorized action');
        }
    }

    private function createApprovalLog($appId, $userId, $role, $action, $remarks = null, $followUpDate = null)
    {
        ApprovalLog::create([
            'application_id' => $appId,
            'user_id' => $userId,
            'role' => $role,
            'action' => $action,
            'remarks' => $remarks,
            'follow_up_date' => $followUpDate
        ]);
    }

    private function isFinalApproval($currentApprover, $nextApprover)
    {
        return !$nextApprover || $currentApprover->isGeneralManager();
    }

    private function finalizeApproval($application)
    {
        $application->update([
            'status' => 'mis_processing',
            'current_approver_id' => Auth::user()->emp_id,
            'approval_level' => 'completed'
        ]);
    }

    private function moveToNextApprover($application, $nextApprover)
    {
        $application->update([
            'status' => 'under_review',
            'current_approver_id' => $nextApprover->id,
            'approval_level' => $this->getApprovalLevel($nextApprover->emp_designation)
        ]);
    }

    private function getApprovalLevel($designation)
    {
        $designation = strtolower($designation);
        if (str_contains($designation, 'regional')) return 'rbm';
        if (str_contains($designation, 'zonal')) return 'zbm';
        if (str_contains($designation, 'general')) return 'gm';
        return 'unknown';
    }

    // ==================== NOTIFICATION METHODS ====================

    private function notifyMISTeam($application)
    {
        $misMembers = Employee::where('emp_department', 'MIS')->get();
        foreach ($misMembers as $member) {
            Mail::to($member->emp_email)->queue(
                new ApplicationNotification($application, 'MIS Processing Required')
            );
        }
    }

    private function notifyBusinessHead($application)
    {
        $businessHead = Employee::where('emp_designation', 'like', '%Business Head%')->first();
        if ($businessHead) {
            Mail::to($businessHead->emp_email)->queue(
                new ApplicationNotification($application, 'FYI: Application Approved')
            );
        }
    }

    private function notifyNextApprover($nextApprover, $application)
    {
        Mail::to($nextApprover->emp_email)->queue(
            new ApplicationNotification($application, 'Approval Required')
        );
    }

    private function notifyCreator($application, $subject, $remarks)
    {
        $creator = Employee::find($application->created_by);
        if ($creator) {
            Mail::to($creator->emp_email)->queue(
                new ApplicationNotification($application, $subject, $remarks)
            );
        }
    }

    private function scheduleFollowUp($application, $followUpDate)
    {
        // Implementation for scheduling follow-up notifications
        // Could use Laravel jobs with delay or a scheduler
    }
}
