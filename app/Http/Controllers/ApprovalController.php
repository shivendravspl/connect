<?php

namespace App\Http\Controllers;

use App\Models\Onboarding;
use App\Models\ApprovalLog;
use App\Models\Employee;
use App\Jobs\ReminderJob;
use App\Jobs\FollowUpJob;
use App\Mail\ApplicationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class ApprovalController extends Controller
{
    public function show(Onboarding $application)
    {
        $user = Auth::user();
        // Authorization checks
        $isCreator = $application->created_by === $user->emp_id;
        $isCurrentApprover = $application->current_approver_id === $user->emp_id;
        $isManager = $application->createdBy && $application->createdBy->emp_reporting === $user->emp_id;
        $isHigherLevelApprover = $this->isHigherLevelApprover($user, $application);
        $isMIS = $user->hasRole('Mis User');
        //$isMIS = $user->hasPermissionTo('process-mis'); // Updated to use Spatie permission
        $isBusinessHead = str_contains(strtolower($user->emp_designation), 'business head');
        $isPastApprover = $application->approvalLogs->contains('user_id', $user->emp_id);
        // Debug authorization conditions
        Log::debug("Authorization check for application_id: {$application->id}, user_id: {$user->emp_id}", [
            'isCreator' => $isCreator,
            'isCurrentApprover' => $isCurrentApprover,
            'isManager' => $isManager,
            'isHigherLevelApprover' => $isHigherLevelApprover,
            'isMIS' => $isMIS,
            'isBusinessHead' => $isBusinessHead,
            'isPastApprover' => $isPastApprover,
            'created_by' => $application->created_by,
            'current_approver_id' => $application->current_approver_id,
            'emp_reporting' => optional($application->createdBy)->emp_reporting,
            'user_emp_department' => $user->employee->emp_department ?? '',
            'user_emp_designation' => $user->employee->emp_designation ?? '',
        ]);

        if (!$isCreator && !$isCurrentApprover && !$isManager && !$isHigherLevelApprover && !$isMIS && !$isBusinessHead && !$isPastApprover) {
            Log::warning("Unauthorized access attempt by emp_id: {$user->emp_id} for application_id: {$application->id}");
            abort(403, 'Unauthorized access to this application');
        }

        $application->load([
            'entityDetails',
            'distributionDetail',
            'businessPlans',
            'financialInfo',
            'existingDistributorships',
            'bankDetail',
            'declarations',
            'approvalLogs.user',
            'currentApprover',
            'finalApprover',
            'createdBy'
        ]);

        return view('approvals.show', compact('application'));
    }

    public function approve(Request $request, Onboarding $application)
    {
        $user = Auth::user();
        Log::info('Approve request received', [
            'application_id' => $application->id,
            'request_data' => $request->all(),
            'form_action' => $request->url()
        ]);

        $this->authorizeApproval($application, $user->emp_id);

        // Validate application status
        if (!in_array($application->status, ['under_review', 'initiated', 'on_hold'])) {
            Log::error('Invalid status for approve action', [
                'application_id' => $application->id,
                'status' => $application->status
            ]);
            return redirect()->route('dashboard')->with('error', 'Cannot approve an application with status: ' . $application->status);
        }

        $validator = Validator::make($request->all(), [
            'remarks' => 'nullable|string|min:5',
            'application_id' => 'required|exists:onboardings,id'
        ], [
            'remarks.min' => 'The remarks, if provided, must be at least 5 characters.',
            'application_id.exists' => 'The selected application is invalid.'
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed for approve request', [
                'application_id' => $application->id,
                'errors' => $validator->errors()->all()
            ]);
            return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Please correct the errors in the form.');
        }

        if ($request->input('application_id') != $application->id) {
            Log::error('Mismatched application ID', [
                'url_application_id' => $application->id,
                'form_application_id' => $request->input('application_id')
            ]);
            return redirect()->route('dashboard')->withErrors(['application_id' => 'Invalid application ID']);
        }

        $currentApprover = Employee::where('employee_id', $user->emp_id)->firstOrFail();
        $nextApprover = $currentApprover->manager;

        $this->createApprovalLog(
            $application->id,
            $user->emp_id,
            $currentApprover->emp_designation,
            'approved',
            $request->input('remarks')
        );

        if ($this->isFinalApproval($currentApprover, $nextApprover)) {
            $this->finalizeApproval($application);
            $this->notifyMISTeam($application);
            $this->notifyBusinessHead($application);
            $this->notifySalesHierarchy($application);
        } else {
            $this->moveToNextApprover($application, $nextApprover);
            $this->notifyNextApprover($nextApprover, $application);
            $this->scheduleReminder($nextApprover, $application);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Application approved successfully');
    }

    public function reject(Request $request, Onboarding $application)
    {
        $user = Auth::user();
        Log::info('Reject request received', [
            'application_id' => $application->id,
            'request_data' => $request->all(),
            'form_action' => $request->url()
        ]);

        $this->authorizeApproval($application, $user->emp_id);

        // Validate application status
        if (!in_array($application->status, ['under_review', 'initiated', 'on_hold'])) {
            Log::error('Invalid status for reject action', [
                'application_id' => $application->id,
                'status' => $application->status
            ]);
            return redirect()->route('dashboard')->with('error', 'Cannot reject an application with status: ' . $application->status);
        }

        $validator = Validator::make($request->all(), [
            'remarks' => 'required|string|min:5',
            'application_id' => 'required|exists:onboardings,id'
        ], [
            'remarks.min' => 'The reason for rejecting the application must be at least 5 characters.',
            'application_id.exists' => 'The selected application is invalid.'
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed for reject request', [
                'application_id' => $application->id,
                'errors' => $validator->errors()->all()
            ]);
            return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Please correct the errors in the form.');
        }

        if ($request->input('application_id') != $application->id) {
            Log::error('Mismatched application ID', [
                'url_application_id' => $application->id,
                'form_application_id' => $request->input('application_id')
            ]);
            return redirect()->route('dashboard')->withErrors(['application_id' => 'Invalid application ID']);
        }

        $currentApprover = Employee::where('employee_id', $user->emp_id)->firstOrFail();
        $this->createApprovalLog(
            $application->id,
            $user->emp_id,
            $currentApprover->emp_designation,
            'rejected',
            $request->input('remarks')
        );

        $application->update([
            'status' => 'rejected',
            'current_approver_id' => null,
            'final_approver_id' => $user->emp_id
        ]);

        $this->notifyCreator($application, 'Application Rejected', $request->input('remarks'));
        $this->notifySalesHierarchy($application, 'Application Rejected');

        return redirect()->route('dashboard')
            ->with('success', 'Application rejected successfully');
    }
    public function revert(Request $request, Onboarding $application)
    {
        $user = Auth::user();
        Log::info('Revert request received', [
            'application_id' => $application->id,
            'request_data' => $request->all(),
            'form_action' => $request->url()
        ]);
        $this->authorizeApproval($application, $user->emp_id);

        // Validate application status
        if (!in_array($application->status, ['under_review', 'initiated', 'on_hold'])) {
            Log::error('Invalid status for revert action', [
                'application_id' => $application->id,
                'status' => $application->status
            ]);
            return redirect()->route('dashboard')->with('error', 'Cannot revert an application with status: ' . $application->status);
        }

        $validator = Validator::make($request->all(), [
            'remarks' => 'required|string|min:5',
            'application_id' => 'required|exists:onboardings,id'
        ], [
            'remarks.min' => 'The reason for reverting the application must be at least 5 characters.',
            'application_id.exists' => 'The selected application is invalid.'
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed for revert request', [
                'application_id' => $application->id,
                'errors' => $validator->errors()->all()
            ]);
            return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Please correct the errors in the form.');
        }

        if ($request->input('application_id') != $application->id) {
            Log::error('Mismatched application ID', [
                'url_application_id' => $application->id,
                'form_application_id' => $request->input('application_id')
            ]);
            return redirect()->route('dashboard')->withErrors(['application_id' => 'Invalid application ID']);
        }

        $currentApprover = Employee::where('employee_id', $user->emp_id)->firstOrFail();
        $this->createApprovalLog(
            $application->id,
            $user->emp_id,
            $currentApprover->emp_designation,
            'reverted',
            $request->input('remarks')
        );

        $application->update([
            'status' => 'reverted',
            'current_approver_id' => $application->created_by,
            'approval_level' => $currentApprover->emp_designation
        ]);

        $this->notifyCreator($application, 'Application Reverted', $request->input('remarks'));

        return redirect()->route('dashboard')
            ->with('success', 'Application reverted successfully');
    }

    public function hold(Request $request, Onboarding $application)
    {
        $user = Auth::user();
        Log::info('Hold request received', [
            'application_id' => $application->id,
            'request_data' => $request->all(),
            'form_action' => $request->url()
        ]);

        $this->authorizeApproval($application, $user->emp_id);

        $validator = Validator::make($request->all(), [
            'remarks' => 'required|string|min:5',
            'follow_up_date' => 'required|date|after:now',
            'application_id' => 'required|exists:onboardings,id'
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed for hold request', [
                'application_id' => $application->id,
                'errors' => $validator->errors()->all()
            ]);
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($request->input('application_id') != $application->id) {
            Log::error('Mismatched application ID', [
                'url_application_id' => $application->id,
                'form_application_id' => $request->input('application_id')
            ]);
            return redirect()->route('dashboard')->withErrors(['application_id' => 'Invalid application ID']);
        }

        $currentApprover = Employee::where('employee_id', $user->emp_id)->firstOrFail();
        $this->createApprovalLog(
            $application->id,
            $user->emp_id,
            $currentApprover->emp_designation,
            'hold',
            $request->input('remarks'),
            $request->input('follow_up_date')
        );

        $application->update([
            'status' => 'on_hold',
            'follow_up_date' => $request->input('follow_up_date')
        ]);

        $this->notifyCreator($application, 'Application On Hold', $request->input('remarks'));
        $this->scheduleFollowUp($application, $request->input('follow_up_date'));

        return redirect()->route('dashboard')
            ->with('success', 'Application put on hold successfully');
    }


    // ==================== PRIVATE METHODS ====================

    private function authorizeApproval($application, $empId)
    {
        $isCurrentApprover = $application->current_approver_id === $empId;
        $isManager = $application->createdBy && $application->createdBy->emp_reporting === $empId;

        if (!$isCurrentApprover && !$isManager) {
            Log::warning("Unauthorized approval attempt by emp_id: {$empId} for application_id: {$application->id}");
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

    private function isHigherLevelApprover($user, $application)
    {
        if (!$application->current_approver_id) {
            return false;
        }

        $currentApprover = Employee::where('employee_id', $application->current_approver_id)->first();
        if (!$currentApprover) {
            return false;
        }

        $higherApprovers = $this->getHigherApprovers($currentApprover);
        return in_array($user->emp_id, $higherApprovers);
    }

    private function getHigherApprovers($employee)
    {
        $higherApprovers = [];
        $current = $employee;

        while ($current->manager) {
            $higherApprovers[] = $current->manager->emp_id;
            $current = $current->manager;
        }

        return $higherApprovers;
    }

    private function isFinalApproval($currentApprover, $nextApprover)
    {
        return !$nextApprover ||
            str_contains(strtolower($currentApprover->emp_designation), 'general manager') ||
            str_contains(strtolower($currentApprover->emp_designation), 'business head');
    }

    private function finalizeApproval($application)
    {
        $application->update([
            'status' => 'mis_processing',
            'current_approver_id' => null,
            'final_approver_id' => Auth::user()->emp_id,
            'approval_level' => 'mis'
        ]);
    }

    private function moveToNextApprover($application, $nextApprover)
    {
        $application->update([
            'status' => 'under_review',
            'current_approver_id' => $nextApprover ? $nextApprover->id : null,
            'approval_level' => $nextApprover ? $nextApprover->emp_designation : 'mis'
        ]);
    }

    // private function getApprovalLevel($designation)
    // {
    //     $designation = strtolower($designation);
    //     if (str_contains($designation, 'regional')) return 'RBM';
    //     if (str_contains($designation, 'zonal')) return 'ZBM';
    //     if (str_contains($designation, 'general')) return 'GM';
    //     return 'mis';
    // }

    // ==================== NOTIFICATION METHODS ====================

    private function notifyMISTeam($application)
    {
        //$misMembers = User::permission('process-mis')->get();
        $misMembers = User::role('Mis User')->get();
        foreach ($misMembers as $member) {
            if (!empty($member->email)) {
                Mail::to($member->email)
                    ->queue(new ApplicationNotification(
                        application: $application,
                        mailSubject: 'MIS Processing Required',
                        remarks: 'Please process this application in the MIS system.'
                    ));

                Log::channel('emails')->info('MIS processing notification queued', [
                    'application_id' => $application->id,
                    'recipient' => $member->email,
                    'type' => 'mis_processing'
                ]);
            }
        }
    }

    private function notifyBusinessHead($application)
    {
        $businessHead = Employee::where('emp_designation', 'like', '%Business Head%')->first();
        if ($businessHead && $businessHead->emp_email) {
            Mail::to($businessHead->emp_email)->queue(
                new ApplicationNotification(
                    application: $application,
                    mailSubject: 'Application Approved',
                    remarks: 'Fyi'
                )
            );
            Log::info("Business Head notified for application_id: {$application->id}", ['recipient' => $businessHead->emp_email]);
        } else {
            Log::warning("No Business Head found or invalid email for application_id: {$application->id}");
        }
    }

    private function notifySalesHierarchy($application, $mailSubject = 'Application Approved')
    {
        $approvers = collect();
      
        $current = Employee::where('employee_id', $application->created_by)->first();


        while ($current) {
            $approvers->push($current);
            $current = $current->manager;
        }

        foreach ($approvers as $approver) {
            if ($approver->emp_email) {
                Mail::to($approver->emp_email)->queue(
                    new ApplicationNotification(
                        application: $application,
                        mailSubject: $mailSubject
                    )
                );
            }
        }
    }

    private function notifyNextApprover($nextApprover, $application)
    {
        if ($nextApprover && $nextApprover->emp_email) {
            Mail::to($nextApprover->emp_email)->queue(
                new ApplicationNotification(
                    application: $application,
                    mailSubject: 'Approval Required'
                )
            );
        }
    }

    private function notifyCreator($application, $mailSubject, $remarks)
    {
        $creator = Employee::where('employee_id', $application->created_by)->first();

        if ($creator && $creator->emp_email) {
            Mail::to($creator->emp_email)->queue(
                new ApplicationNotification(
                    application: $application,
                    mailSubject: $mailSubject,
                    remarks: $remarks
                )
            );
        }
    }

    private function scheduleFollowUp($application, $followUpDate)
    {
        $creator = Employee::where('employee_id', $application->created_by)->first();
        if ($creator && $creator->emp_email) {
            Mail::to($creator->emp_email)->queue(
                new ApplicationNotification(
                    application: $application,
                    mailSubject: 'Application On Hold'
                )
            );
        }
        // FollowUpJob::dispatch($application)
        //     ->delay(Carbon::parse($followUpDate));
    }

    private function scheduleReminder($approver, $application)
    {
        if ($approver && $approver->emp_email) {
            Mail::to($approver->emp_email)->queue(
                new ApplicationNotification(
                    application: $application,
                    mailSubject: 'Reminder: Approval Required'
                )
            );
        }
        // if ($approver) {
        //     ReminderJob::dispatch($approver, $application)
        //         ->delay(now()->addHours(48));
        // }
    }

    public function dashboard()
    {
        $user = Auth::user();
        $query = Onboarding::query();

        // For sales team: Show applications they created or need to approve
       // if (!$user->hasPermissionTo('process-mis')) {
       if (!$user->hasRole('Mis User')) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->emp_id)
                    ->orWhere('current_approver_id', $user->emp_id)
                    ->orWhereIn('created_by', function ($subQuery) use ($user) {
                        $subQuery->select('id')
                            ->from('core_employee')
                            ->where('emp_reporting', $user->emp_id);
                    });
            });
        }

        // For MIS team: Show applications in MIS processing stages
        //if ($user->hasPermissionTo('process-mis')) {
        if ($user->hasRole('Mis User')) {
            $query->whereIn('status', [
                'mis_processing',
                'document_verified',
                'agreement_created',
                'documents_received',
                'documents_pending',
                'distributorship_created'
            ]);
        }

        $applications = $query->with([
            'createdBy',
            'currentApprover',
            'entityDetails',
            'territoryDetail'
        ])->orderBy('created_at', 'desc')->paginate(10);

        return view('approvals.dashboard', compact('applications'));
    }
}
