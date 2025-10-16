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
use App\Models\ApplicationCheckpoint;
use App\Models\ApplicationAdditionalDocument;
use App\Models\ApplicationAdditionalUpload;
use App\Models\Document;
use App\Models\EntityDetails;
use App\Models\EntityDetailsAuditLog;
use App\Models\AuthorizedPerson;
use App\Models\PhysicalDocumentCheck;
use App\Models\PhysicalDispatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Status;

use App\Models\SecurityChequeDetail;
use App\Models\SecurityDepositDetail;

class ApprovalController extends Controller
{
    public function show(Onboarding $application)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('Admin') || $user->hasRole('Super Admin');
        // Authorization checks
        $isCreator = $application->created_by === $user->emp_id;
        $isCurrentApprover = $application->current_approver_id === $user->emp_id;
        $isManager = $application->createdBy && $application->createdBy->emp_reporting === $user->emp_id;
        $isHigherLevelApprover = $this->isHigherLevelApprover($user, $application);
        $isMIS = $user->hasRole('Mis User');
        //$isMIS = $user->hasPermissionTo('process-mis'); // Updated to use Spatie permission
        $isBusinessHead = str_contains(strtolower($user->emp_designation), 'business head');
        $isPastApprover = $application->approvalLogs->contains('user_id', $user->emp_id);
        $hasDistributorApprovalPermission = $user->hasPermissionTo('distributor_approval');

        if (!$isAdmin && !$isCreator && !$isCurrentApprover && !$isManager && !$isHigherLevelApprover && !$isMIS && !$isBusinessHead && !$isPastApprover && !$hasDistributorApprovalPermission) {

            abort(403, 'Unauthorized access to this application');
        }
        // Load filled by (created_by user)
        $createdBy = Employee::where('employee_id', $application->created_by)->first();


        // Load verification logs (documents_verified and distributor_confirmed)
        $verifications = ApprovalLog::where('application_id', $application->id)
            ->whereIn('action', ['approved'])
            ->with('employee')
            ->orderBy('created_at', 'asc')
            ->get();

        // Latest approval log
        $approvals = ApprovalLog::where('application_id', $application->id)
            ->where('action', 'approved')
            ->with('employee')
            ->orderBy('created_at', 'desc')
            ->first();

        $application->load([
            'entityDetails',
            'distributionDetail',
            'businessPlans',
            'financialInfo',
            'bankDetail',
            'declarations',
            'approvalLogs.user',
            'currentApprover',
            'finalApprover',
            'createdBy'
        ]);

        return view('approvals.show', compact('application', 'createdBy', 'verifications', 'approvals'));
    }

    public function approve(Request $request, Onboarding $application)
    {
        $user = Auth::user();

        try {
            $this->authorizeApproval($application, $user->emp_id);

            // Validate application status
            $allowedStatuses = ['under_level1_review', 'under_level2_review', 'under_level3_review', 'on_hold'];
            if (!in_array($application->status, $allowedStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot approve an application with status: ' . $application->status
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'remarks' => 'nullable|string|min:5',
                'application_id' => 'required|exists:onboardings,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->input('application_id') != $application->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid application ID'
                ], 422);
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

                $message = 'Application approved and sent to MIS processing!';
            } else {
                $this->moveToNextApprover($application, $currentApprover, $nextApprover);
                $this->notifyNextApprover($nextApprover, $application, $request->input('remarks'));
                $this->scheduleReminder($nextApprover, $application);

                $message = 'Application approved and forwarded to next level!';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'new_status' => $application->status,
                    'approval_level' => $application->approval_level
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving application: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, Onboarding $application)
    {
        $user = Auth::user();

        try {
            $this->authorizeApproval($application, $user->emp_id);

            // Validate application status
            $allowedStatuses = ['under_level1_review', 'under_level2_review', 'under_level3_review', 'on_hold'];
            if (!in_array($application->status, $allowedStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot reject an application with status: ' . $application->status
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'remarks' => 'required|string|min:5',
                'application_id' => 'required|exists:onboardings,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->input('application_id') != $application->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid application ID'
                ], 422);
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

            return response()->json([
                'success' => true,
                'message' => 'Application rejected successfully',
                'data' => [
                    'new_status' => 'rejected'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting application: ' . $e->getMessage()
            ], 500);
        }
    }

    public function revert(Request $request, Onboarding $application)
    {
        $user = Auth::user();

        try {
            $this->authorizeApproval($application, $user->emp_id);

            // Validate application status
            $allowedStatuses = ['under_level1_review', 'under_level2_review', 'under_level3_review', 'on_hold'];
            if (!in_array($application->status, $allowedStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot revert an application with status: ' . $application->status
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'remarks' => 'required|string|min:5',
                'application_id' => 'required|exists:onboardings,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->input('application_id') != $application->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid application ID'
                ], 422);
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

            return response()->json([
                'success' => true,
                'message' => 'Application reverted successfully',
                'data' => [
                    'new_status' => 'reverted'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error reverting application: ' . $e->getMessage()
            ], 500);
        }
    }

    public function hold(Request $request, Onboarding $application)
    {
        $user = Auth::user();

        try {
            $this->authorizeApproval($application, $user->emp_id);

            $validator = Validator::make($request->all(), [
                'remarks' => 'required|string|min:4',
                'follow_up_date' => 'required|date|after:now',
                'application_id' => 'required|exists:onboardings,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->input('application_id') != $application->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid application ID'
                ], 422);
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

            return response()->json([
                'success' => true,
                'message' => 'Application put on hold successfully',
                'data' => [
                    'new_status' => 'on_hold',
                    'follow_up_date' => $request->input('follow_up_date')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error putting application on hold: ' . $e->getMessage()
            ], 500);
        }
    }


    // ==================== PRIVATE METHODS ====================

    private function authorizeApproval($application, $empId)
    {
        $isCurrentApprover = $application->current_approver_id === $empId;

        // Resolve creator’s manager properly
        $creator = $application->createdBy;
        $creatorManagerId = $creator?->manager?->employee_id;
        $isManager = $creatorManagerId === $empId;

        if (!$isCurrentApprover && !$isManager) {

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
            'is_hierarchy_approved' => true,
            'current_approver_id' => null,
            'final_approver_id' => Auth::user()->emp_id,
            'approval_level' => 'Hierarchy Completed',
        ]);
    }

    private function moveToNextApprover($application, $currentApprover, $nextApprover)
    {
        $nextStatusInfo = $this->getNextStatusAndLevel($application->status, $currentApprover->emp_designation, $nextApprover);

        $application->update([
            'status' => $nextStatusInfo['status'],
            'current_approver_id' => $nextApprover ? $nextApprover->employee_id : null,
            'approval_level' => $nextStatusInfo['level'],
            'updated_at' => now()
        ]);
    }

    /**
     * Determine next status and level based on current status and next approver
     */
    private function getNextStatusAndLevel($currentStatus, $currentDesignation, $nextApprover)
    {
        // If no next approver, go to MIS processing
        if (!$nextApprover) {
            return [
                'status' => 'mis_processing',
                'level' => 'MIS Processing'
            ];
        }

        $nextDesignation = $nextApprover->emp_designation;

        // Map designations to levels and statuses
        $designationMapping = [
            'Regional Business Manager' => [
                'level' => 'Level 1',
                'status' => 'under_level1_review'
            ],
            'Zonal Business Manager' => [
                'level' => 'Level 2',
                'status' => 'under_level2_review'
            ],
            'GM Sales' => [
                'level' => 'Level 3',
                'status' => 'under_level3_review'
            ]
        ];

        // Check if next approver's designation is mapped
        if (isset($designationMapping[$nextDesignation])) {
            return $designationMapping[$nextDesignation];
        }

        // If next approver is not in hierarchy, check if they should be treated as higher level
        $currentLevel = $this->getCurrentLevelFromStatus($currentStatus);

        // If current is Level 1 and next approver is not RBM, assume they are Level 2 or higher
        if ($currentLevel === 1 && !isset($designationMapping[$nextDesignation])) {
            // Check if next approver has a higher designation
            if ($this->isHigherDesignation($nextDesignation, 'Regional Business Manager')) {
                return [
                    'status' => 'under_level2_review',
                    'level' => 'Level 2'
                ];
            }
        }

        // If current is Level 2 and next approver is not ZBM
        if ($currentLevel === 2 && !isset($designationMapping[$nextDesignation])) {
            if ($this->isHigherDesignation($nextDesignation, 'Zonal Business Manager')) {
                return [
                    'status' => 'under_level3_review',
                    'level' => 'Level 3'
                ];
            }
        }

        // Default fallback - go to MIS processing
        return [
            'status' => 'mis_processing',
            'level' => 'MIS Processing'
        ];
    }

    /**
     * Get current level from status
     */
    private function getCurrentLevelFromStatus($status)
    {
        $levelMap = [
            'under_level1_review' => 1,
            'under_level2_review' => 2,
            'under_level3_review' => 3
        ];

        return $levelMap[$status] ?? 0;
    }

    /**
     * Check if designation is higher in hierarchy
     */
    private function isHigherDesignation($designation, $compareTo)
    {
        $hierarchy = [
            'Regional Business Manager' => 1,
            'Zonal Business Manager' => 2,
            'General Manager' => 3
        ];

        $designationLevel = $hierarchy[$designation] ?? 0;
        $compareLevel = $hierarchy[$compareTo] ?? 0;

        return $designationLevel > $compareLevel;
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
        } else {
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

    private function notifyNextApprover($nextApprover, $application, $remarks)
    {
        if ($nextApprover && $nextApprover->emp_email) {
            try {
                Mail::to($nextApprover->emp_email)->queue(
                    new ApplicationNotification(
                        application: $application,
                        mailSubject: 'Approval Required',
                        remarks: $remarks
                    )
                );
            } catch (\Exception $e) {
            }
        } else {
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
                    mailSubject: 'Application On Hold',
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
        if ($user->hasRole('Mis User')) {
            // Get MIS processing statuses from the statuses table
            $misStatuses = Status::where('category', 'mis_processing')
                ->where('is_active', 1)
                ->pluck('name')
                ->toArray();

            // Also include completion status for MIS to see completed applications
            $completionStatuses = Status::where('category', 'completion')
                ->where('is_active', 1)
                ->pluck('name')
                ->toArray();

            $query->whereIn('status', array_merge($misStatuses, $completionStatuses));
        }

        $applications = $query->with([
            'createdBy',
            'currentApprover',
            'entityDetails',
            'territoryDetail'
        ])->orderBy('created_at', 'desc')->paginate(10);

        return view('approvals.dashboard', compact('applications'));
    }


    public function misVerificationList()
    {
        if (!Auth::user()->employee || !Auth::user()->employee->isMisTeam()) {
            abort(403, 'Unauthorized');
        }

        $misStatuses = Status::getMisProcessingStatuses();

        $misApplications = Onboarding::with(['entityDetails', 'territoryDetail', 'additionalDocs'])
            ->whereIn('status', $misStatuses)
            ->latest()
            ->paginate(20);

        return view('mis.verification-list', compact('misApplications'));
    }

    public function verifyDocuments(Onboarding $application)
    {
        if (!Auth::user()->employee->isMisTeam() || !in_array($application->status, ['mis_processing', 'documents_resubmitted', 'documents_pending'])) {
            abort(403, 'Unauthorized');
        }

        // Fetch main documents from entityDetails
        $mainDocuments = [];
        $checkpoints = ApplicationCheckpoint::where('application_id', $application->id)
            ->pluck('status', 'checkpoint_name')
            ->toArray();
        $entityCheckpoint = ApplicationCheckpoint::where('application_id', $application->id)
            ->where('checkpoint_name', 'entity_details')
            ->first();

        if ($application->entityDetails->pan_path) {
            $mainDocuments[] = [
                'type' => 'PAN',
                'details' => ['pan_number' => $application->entityDetails->pan_number ?? 'N/A'],
                'path' => $application->entityDetails->pan_path,
                's3_folder' => 'pan',
                'checkpoint_name' => 'main_document_pan',
                'resubmitted' => $application->status === 'documents_resubmitted' && isset($checkpoints['main_document_pan']) && $checkpoints['main_document_pan'] === 'not_verified'
            ];
        }
        if ($application->entityDetails->gst_applicable === 'yes' && $application->entityDetails->gst_path) {
            $mainDocuments[] = [
                'type' => 'GST',
                'details' => ['gst_number' => $application->entityDetails->gst_number ?? 'N/A'],
                'path' => $application->entityDetails->gst_path,
                's3_folder' => 'gst',
                'checkpoint_name' => 'main_document_gst',
                'resubmitted' => $application->status === 'documents_resubmitted' && isset($checkpoints['main_document_gst']) && $checkpoints['main_document_gst'] === 'not_verified'
            ];
        }
        if ($application->entityDetails->seed_license_path) {
            $mainDocuments[] = [
                'type' => 'Seed License',
                'details' => ['seed_license_number' => $application->entityDetails->seed_license ?? 'N/A'],
                'path' => $application->entityDetails->seed_license_path,
                's3_folder' => 'seed_license',
                'checkpoint_name' => 'main_document_seed_license',
                'resubmitted' => $application->status === 'documents_resubmitted' && isset($checkpoints['main_document_seed_license']) && $checkpoints['main_document_seed_license'] === 'not_verified'
            ];
        }
        if ($application->entityDetails->bank_document_path) {
            $mainDocuments[] = [
                'type' => 'Bank Document',
                'details' => [
                    'bank_name' => $application->entityDetails->bank_name ?? 'N/A',
                    'account_number' => $application->entityDetails->account_number ?? 'N/A'
                ],
                'path' => $application->entityDetails->bank_document_path,
                's3_folder' => 'bank',
                'checkpoint_name' => 'main_document_bank',
                'resubmitted' => $application->status === 'documents_resubmitted' && isset($checkpoints['main_document_bank']) && $checkpoints['main_document_bank'] === 'not_verified'
            ];
        }

        // Handle Entity Proof
        if ($application->entityDetails->entity_proof_path) {
            $mainDocuments[] = [
                'type' => 'Entity Proof',
                'details' => [], // No specific details; can add if needed
                'path' => $application->entityDetails->entity_proof_path,
                's3_folder' => 'entity_proof',
                'checkpoint_name' => 'main_document_entity_proof',
                'resubmitted' => $application->status === 'documents_resubmitted' && isset($checkpoints['main_document_entity_proof']) && $checkpoints['main_document_entity_proof'] === 'not_verified'
            ];
        }

        // Handle Ownership Info (fixed s3_folder from 'bank' to 'ownership_info')
        if ($application->entityDetails->ownership_info_path) {
            $mainDocuments[] = [
                'type' => 'Ownership Info',
                'details' => [], // No specific details; can add if needed
                'path' => $application->entityDetails->ownership_info_path,
                's3_folder' => 'ownership_info', // Corrected folder
                'checkpoint_name' => 'main_document_ownership',
                'resubmitted' => $application->status === 'documents_resubmitted' && isset($checkpoints['main_document_ownership']) && $checkpoints['main_document_ownership'] === 'not_verified'
            ];
        }

        // Handle Bank Statement
        if ($application->entityDetails->bank_statement_path) {
            $mainDocuments[] = [
                'type' => 'Bank Statement',
                'details' => [], // No specific details; can add if needed
                'path' => $application->entityDetails->bank_statement_path,
                's3_folder' => 'bank_statement',
                'checkpoint_name' => 'main_document_bank_statement',
                'resubmitted' => $application->status === 'documents_resubmitted' && isset($checkpoints['main_document_bank_statement']) && $checkpoints['main_document_bank_statement'] === 'not_verified'
            ];
        }

        // Handle ITR Acknowledgement
        if ($application->entityDetails->itr_acknowledgement_path) {
            $mainDocuments[] = [
                'type' => 'ITR Acknowledgement',
                'details' => [], // No specific details; can add if needed
                'path' => $application->entityDetails->itr_acknowledgement_path,
                's3_folder' => 'itr_acknowledgement',
                'checkpoint_name' => 'main_document_itr_acknowledgement',
                'resubmitted' => $application->status === 'documents_resubmitted' && isset($checkpoints['main_document_itr_acknowledgement']) && $checkpoints['main_document_itr_acknowledgement'] === 'not_verified'
            ];
        }

        // Handle Balance Sheet
        if ($application->entityDetails->balance_sheet_path) {
            $mainDocuments[] = [
                'type' => 'Balance Sheet',
                'details' => [], // No specific details; can add if needed
                'path' => $application->entityDetails->balance_sheet_path,
                's3_folder' => 'balance_sheet',
                'checkpoint_name' => 'main_document_balance_sheet',
                'resubmitted' => $application->status === 'documents_resubmitted' && isset($checkpoints['main_document_balance_sheet']) && $checkpoints['main_document_balance_sheet'] === 'not_verified'
            ];
        }

        // Fetch authorized person documents
        $authorizedDocs = [];
        if ($application->entityDetails->has_authorized_persons === 'yes' && $application->authorizedPersons->isNotEmpty()) {
            foreach ($application->authorizedPersons as $personIndex => $person) {
                if ($person->letter_path) {
                    $authorizedDocs[] = [
                        'type' => 'Authorization Letter',
                        'person_index' => $personIndex,
                        'person_name' => $person->name ?? 'Person ' . ($personIndex + 1),
                        'person_relation' => $person->relation ?? 'N/A',
                        'doc_type' => 'letter',
                        'path' => $person->letter_path,
                        's3_folder' => 'authorized_persons',
                        'checkpoint_name' => "authorized_letter_{$personIndex}",
                        'resubmitted' => $application->status === 'documents_resubmitted' && isset($checkpoints["authorized_letter_{$personIndex}"]) && $checkpoints["authorized_letter_{$personIndex}"] === 'not_verified'
                    ];
                }
                if ($person->aadhar_path) {
                    $authorizedDocs[] = [
                        'type' => 'Aadhar Card',
                        'person_index' => $personIndex,
                        'person_name' => $person->name ?? 'Person ' . ($personIndex + 1),
                        'person_relation' => $person->relation ?? 'N/A',
                        'doc_type' => 'aadhar',
                        'path' => $person->aadhar_path,
                        's3_folder' => 'authorized_persons',
                        'checkpoint_name' => "authorized_aadhar_{$personIndex}",
                        'resubmitted' => $application->status === 'documents_resubmitted' && isset($checkpoints["authorized_aadhar_{$personIndex}"]) && $checkpoints["authorized_aadhar_{$personIndex}"] === 'not_verified'
                    ];
                }
            }
        }

        // Fetch additional documents with upload path
        $additionalDocs = ApplicationAdditionalDocument::where('application_id', $application->id)
            ->where('status', 'pending')
            ->with(['upload' => function ($query) {
                $query->select('id', 'additional_doc_id', 'path', 'status');
            }])
            ->get()
            ->map(function ($doc) use ($checkpoints, $application) {
                $doc->upload_path = $doc->upload ? $doc->upload->path : null;
                $doc->resubmitted = $application->status === 'documents_resubmitted' &&
                    isset($checkpoints["additional_doc_{$doc->id}"]) &&
                    $checkpoints["additional_doc_{$doc->id}"] === 'not_verified';
                return $doc;
            });

        // Sync verifications with checkpoints
        $verifications = ['entity_details' => $entityCheckpoint ? $entityCheckpoint->status : '', 'main' => [], 'authorized' => [], 'additional' => []];
        $verificationNotes = ['entity_details' => $entityCheckpoint ? $entityCheckpoint->reason : '', 'main' => [], 'authorized' => [], 'additional' => []];
        $isSubmitted = !is_null($application->mis_verified_at);

        foreach ($mainDocuments as $index => $doc) {
            $checkpoint = ApplicationCheckpoint::where('application_id', $application->id)
                ->where('checkpoint_name', $doc['checkpoint_name'])
                ->first();
            $verifications['main'][$index] = $checkpoint && $checkpoint->status === 'verified';
            $verificationNotes['main'][$index] = $checkpoint ? $checkpoint->reason : '';
        }

        foreach ($authorizedDocs as $index => $doc) {
            $checkpoint = ApplicationCheckpoint::where('application_id', $application->id)
                ->where('checkpoint_name', $doc['checkpoint_name'])
                ->first();
            $verifications['authorized'][$index] = $checkpoint && $checkpoint->status === 'verified';
            $verificationNotes['authorized'][$index] = $checkpoint ? $checkpoint->reason : '';
        }

        foreach ($additionalDocs as $index => $doc) {
            $checkpoint = ApplicationCheckpoint::where('application_id', $application->id)
                ->where('checkpoint_name', "additional_doc_{$doc->id}")
                ->first();
            $verifications['additional'][$index] = $checkpoint && $checkpoint->status === 'verified';
            $verificationNotes['additional'][$index] = $checkpoint ? $checkpoint->reason : '';
        }

        return view('approvals.verify-documents', compact(
            'application',
            'mainDocuments',
            'authorizedDocs',
            'additionalDocs',
            'isSubmitted',
            'verifications',
            'verificationNotes'
        ));
    }


    public function updateDocuments(Request $request, Onboarding $application)
    {
        if (!Auth::user()->employee->isMisTeam() || !in_array($application->status, ['mis_processing', 'documents_resubmitted', 'documents_pending'])) {
            abort(403, 'Unauthorized');
        }

        try {
            $request->validate([
                'document_verifications.main.*' => 'in:verified,not_verified',
                'document_verifications.authorized.*' => 'in:verified,not_verified',
                'document_verifications.additional.*' => 'in:verified,not_verified',
                'verification_notes.main.*' => 'required_if:document_verifications.main.*,not_verified|nullable|string|max:255',
                'verification_notes.authorized.*' => 'required_if:document_verifications.authorized.*,not_verified|nullable|string|max:255',
                'verification_notes.additional.*' => 'required_if:document_verifications.additional.*,not_verified|nullable|string|max:255',
                'additional_documents.*.name' => 'required_without:additional_documents.*.id|string|max:100',
                'additional_documents.*.remark' => 'nullable|string|max:255',
                'additional_documents.*.id' => 'nullable|exists:application_additional_documents,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        }

        $verifications = $request->input('document_verifications', ['main' => [], 'authorized' => [], 'additional' => []]);
        $verificationNotes = $request->input('verification_notes', ['main' => [], 'authorized' => [], 'additional' => []]);
        $additionalDocuments = $request->input('additional_documents', []);

        // Build mainDocuments array matching verifyDocuments method
        $mainDocuments = [];
        if ($application->entityDetails->pan_path) {
            $mainDocuments[] = ['checkpoint_name' => 'main_document_pan'];
        }
        if ($application->entityDetails->gst_applicable === 'yes' && $application->entityDetails->gst_path) {
            $mainDocuments[] = ['checkpoint_name' => 'main_document_gst'];
        }
        if ($application->entityDetails->seed_license_path) {
            $mainDocuments[] = ['checkpoint_name' => 'main_document_seed_license'];
        }
        if ($application->entityDetails->bank_document_path) {
            $mainDocuments[] = ['checkpoint_name' => 'main_document_bank'];
        }
        // Additional main documents
        if ($application->entityDetails->entity_proof_path) {
            $mainDocuments[] = ['checkpoint_name' => 'main_document_entity_proof'];
        }
        if ($application->entityDetails->ownership_info_path) {
            $mainDocuments[] = ['checkpoint_name' => 'main_document_ownership'];
        }
        if ($application->entityDetails->bank_statement_path) {
            $mainDocuments[] = ['checkpoint_name' => 'main_document_bank_statement'];
        }
        if ($application->entityDetails->itr_acknowledgement_path) {
            $mainDocuments[] = ['checkpoint_name' => 'main_document_itr_acknowledgement'];
        }
        if ($application->entityDetails->balance_sheet_path) {
            $mainDocuments[] = ['checkpoint_name' => 'main_document_balance_sheet'];
        }

        $authorizedDocs = [];
        if ($application->entityDetails->has_authorized_persons === 'yes') {
            foreach ($application->authorizedPersons as $personIndex => $person) {
                if ($person->letter_path) {
                    $authorizedDocs[] = ['checkpoint_name' => "authorized_letter_{$personIndex}"];
                }
                if ($person->aadhar_path) {
                    $authorizedDocs[] = ['checkpoint_name' => "authorized_aadhar_{$personIndex}"];
                }
            }
        }

        $additionalDocs = ApplicationAdditionalDocument::where('application_id', $application->id)
            ->with(['upload'])
            ->get();

        $checkpointsToUpdate = [];
        $submittedBy = Auth::user()->emp_id;
        if ($application->status === 'documents_resubmitted') {
            foreach ($mainDocuments as $index => $doc) {
                if (
                    isset($verifications['main'][$index]) && ApplicationCheckpoint::where('application_id', $application->id)
                    ->where('checkpoint_name', $doc['checkpoint_name'])
                    ->where('status', 'not_verified')
                    ->exists()
                ) {
                    $checkpointsToUpdate[] = [
                        'application_id' => $application->id,
                        'checkpoint_name' => $doc['checkpoint_name'],
                        'status' => $verifications['main'][$index],
                        'reason' => $verifications['main'][$index] === 'verified' ? null : ($verificationNotes['main'][$index] ?? ''),
                        'submitted_by' => $submittedBy,
                        'updated_at' => now()
                    ];
                }
            }

            foreach ($authorizedDocs as $index => $doc) {
                if (
                    isset($verifications['authorized'][$index]) && ApplicationCheckpoint::where('application_id', $application->id)
                    ->where('checkpoint_name', $doc['checkpoint_name'])
                    ->where('status', 'not_verified')
                    ->exists()
                ) {
                    $checkpointsToUpdate[] = [
                        'application_id' => $application->id,
                        'checkpoint_name' => $doc['checkpoint_name'],
                        'status' => $verifications['authorized'][$index],
                        'reason' => $verifications['authorized'][$index] === 'verified' ? null : ($verificationNotes['authorized'][$index] ?? ''),
                        'submitted_by' => $submittedBy,
                        'updated_at' => now()
                    ];
                }
            }

            foreach ($additionalDocs as $index => $doc) {
                if (isset($verifications['additional'][$index])) {
                    $checkpointsToUpdate[] = [
                        'application_id' => $application->id,
                        'checkpoint_name' => "additional_doc_{$doc->id}",
                        'status' => $verifications['additional'][$index],
                        'reason' => $verifications['additional'][$index] === 'verified' ? null : ($verificationNotes['additional'][$index] ?? ''),
                        'submitted_by' => $submittedBy,
                        'updated_at' => now()
                    ];
                    $doc->update([
                        'status' => $verifications['additional'][$index],
                        'updated_at' => now()
                    ]);
                    if ($doc->upload) {
                        $doc->upload->update([
                            'status' => $verifications['additional'][$index],
                            'updated_at' => now()
                        ]);
                    }
                }
            }
        } else {
            foreach ($mainDocuments as $index => $doc) {
                if (isset($verifications['main'][$index])) {
                    $checkpointsToUpdate[] = [
                        'application_id' => $application->id,
                        'checkpoint_name' => $doc['checkpoint_name'],
                        'status' => $verifications['main'][$index],
                        'reason' => $verifications['main'][$index] === 'verified' ? null : ($verificationNotes['main'][$index] ?? ''),
                        'submitted_by' => $submittedBy,
                        'updated_at' => now()
                    ];
                }
            }

            foreach ($authorizedDocs as $index => $doc) {
                if (isset($verifications['authorized'][$index])) {
                    $checkpointsToUpdate[] = [
                        'application_id' => $application->id,
                        'checkpoint_name' => $doc['checkpoint_name'],
                        'status' => $verifications['authorized'][$index],
                        'reason' => $verifications['authorized'][$index] === 'verified' ? null : ($verificationNotes['authorized'][$index] ?? ''),
                        'submitted_by' => $submittedBy,
                        'updated_at' => now()
                    ];
                }
            }

            foreach ($additionalDocs as $index => $doc) {
                if (isset($verifications['additional'][$index])) {
                    $checkpointsToUpdate[] = [
                        'application_id' => $application->id,
                        'checkpoint_name' => "additional_doc_{$doc->id}",
                        'status' => $verifications['additional'][$index],
                        'reason' => $verifications['additional'][$index] === 'verified' ? null : ($verificationNotes['additional'][$index] ?? ''),
                        'submitted_by' => $submittedBy,
                        'updated_at' => now()
                    ];
                    $doc->update([
                        'status' => $verifications['additional'][$index],
                        'updated_at' => now()
                    ]);
                    if ($doc->upload) {
                        $doc->upload->update([
                            'status' => $verifications['additional'][$index],
                            'updated_at' => now()
                        ]);
                    }
                }
            }
        }

        // Insert or update additional documents
        $hasNewAdditionalDocs = false;
        if (in_array($application->status, ['mis_processing', 'documents_resubmitted', 'documents_pending'])) {
            foreach ($additionalDocuments as $index => $doc) {
                if (!empty($doc['name']) && trim($doc['name']) !== '') {
                    try {
                        $docStatus = isset($verifications['additional'][$index]) && in_array($verifications['additional'][$index], ['verified', 'not_verified'])
                            ? $verifications['additional'][$index]
                            : 'pending';

                        $additionalDoc = ApplicationAdditionalDocument::updateOrCreate(
                            ['id' => $doc['id'] ?? null, 'application_id' => $application->id],
                            [
                                'document_name' => trim($doc['name']),
                                'remark' => isset($doc['remark']) ? trim($doc['remark']) : null,
                                'status' => $docStatus,
                                'submitted_by' => $submittedBy,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]
                        );

                        // Update corresponding application_additional_uploads
                        if (isset($verifications['additional'][$index])) {
                            ApplicationAdditionalUpload::where('additional_doc_id', $additionalDoc->id)
                                ->where('application_id', $application->id)
                                ->update([
                                    'status' => $verifications['additional'][$index],
                                    'updated_at' => now()
                                ]);
                        }

                        // Set hasNewAdditionalDocs only for new documents
                        if (!isset($doc['id'])) {
                            $hasNewAdditionalDocs = true;
                        }
                    } catch (\Illuminate\Database\QueryException $e) {
                        throw $e;
                    }
                }
            }
        }

        // Update checkpoints
        foreach ($checkpointsToUpdate as $checkpointData) {
            ApplicationCheckpoint::updateOrCreate(
                [
                    'application_id' => $checkpointData['application_id'],
                    'checkpoint_name' => $checkpointData['checkpoint_name']
                ],
                [
                    'status' => $checkpointData['status'],
                    'reason' => $checkpointData['reason'],
                    'submitted_by' => $checkpointData['submitted_by'],
                    'updated_at' => $checkpointData['updated_at']
                ]
            );
        }

        // Check if any documents are not verified
        $hasNotVerifiedDocs = false;
        foreach ($checkpointsToUpdate as $checkpoint) {
            if ($checkpoint['status'] === 'not_verified') {
                $hasNotVerifiedDocs = true;
                break;
            }
        }

        // Update Onboarding status and timestamps
        $allCheckpoints = ApplicationCheckpoint::where('application_id', $application->id)->get();
        $allVerified = $allCheckpoints->every(fn($checkpoint) => $checkpoint->status === 'verified');
        $hasPendingAdditionalDocs = ApplicationAdditionalDocument::where('application_id', $application->id)
            ->where('status', 'pending')
            ->exists();

        $previousStatus = $application->status;

        if ($allVerified && !$hasPendingAdditionalDocs) {
            $application->status = 'documents_verified';
            $application->doc_verification_status = 'documents_verified';
            $application->mis_verified_at = now();
            $application->mis_rejected_at = null;
        } else {
            $application->status = 'documents_pending';
            if ($hasNotVerifiedDocs || $hasNewAdditionalDocs) {
                $application->mis_rejected_at = now();
            }
        }
        $application->save();

        // Create single approval log only when status changes to 'documents_verified'
        if ($application->status === 'documents_verified' && $previousStatus !== 'documents_verified') {
            $this->createApprovalLog(
                $application->id,
                $submittedBy,
                Auth::user()->employee->emp_designation,
                'documents_verified',
                'All documents verified by ' . Auth::user()->employee->emp_name
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Verification updated successfully.',
            'redirect' => route('dashboard')
        ], 200);
    }

    public function updateEntityDetails(Request $request, Onboarding $application)
    {
        if (!Auth::user()->employee->isMisTeam() || !in_array($application->status, ['mis_processing', 'documents_resubmitted', 'documents_pending'])) {
            Log::error('Unauthorized access or invalid status', ['user_id' => Auth::user()->emp_id, 'status' => $application->status]);
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'entity_verification' => 'required|in:verified,not_verified',
            'entity_note' => 'nullable|string|max:255',
            'entity_fields.establishment_name' => 'required|string|max:255',
            //'entity_fields.entity_type' => 'required|string|max:100',
            'entity_fields.business_address' => 'nullable|string|max:255',
            'entity_fields.house_no' => 'nullable|string|max:100',
            'entity_fields.landmark' => 'nullable|string|max:100',
            'entity_fields.pan_number' => 'nullable|string|max:20',
            'entity_fields.seed_license' => 'nullable|string|max:50',
            'entity_fields.seed_license_validity' => 'nullable|date',
            'entity_fields.bank_name' => 'nullable|string|max:100',
            'entity_fields.account_holder_name' => 'nullable|string|max:255',
            'entity_fields.account_number' => 'nullable|string|max:50',
            'entity_fields.ifsc_code' => 'nullable|string|max:20',
            'authorized_persons.*.id' => 'nullable|integer|exists:authorized_persons,id',
            'authorized_persons.*.name' => 'nullable|string|max:255',
            'authorized_persons.*.contact' => 'nullable|string|max:20',
            'authorized_persons.*.address' => 'nullable|string|max:255',
            'authorized_persons.*.aadhar_number' => 'nullable|string|max:20',
        ]);

        $entityVerification = $request->input('entity_verification');
        $entityNote = $request->input('entity_note');
        $entityFields = $request->input('entity_fields', []);
        $authorizedPersons = $request->input('authorized_persons', []);

        try {
            // Update Entity Details
            $entityDetails = EntityDetails::where('application_id', $application->id)->firstOrFail();
            $entityUpdates = [];
            $entityLogs = [];

            $entityFieldsList = [
                'establishment_name',
                //'entity_type',
                'business_address',
                'house_no',
                'landmark',
                'pan_number',
                'seed_license',
                'seed_license_validity',
                'bank_name',
                'account_holder_name',
                'account_number',
                'ifsc_code',
            ];

            foreach ($entityFieldsList as $field) {
                if (isset($entityFields[$field])) {
                    $oldValue = $entityDetails->$field;

                    // Handle dates
                    if ($field === 'seed_license_validity' && $oldValue) {
                        $oldValue = $oldValue instanceof \Carbon\Carbon
                            ? $oldValue->format('Y-m-d')
                            : $oldValue;
                    }

                    if ($entityFields[$field] != $oldValue) { // safe comparison
                        $entityLogs[] = [
                            'application_id' => $application->id,
                            'entity_type' => 'entity_details',
                            'field_name' => $field,
                            'old_value' => $oldValue,
                            'new_value' => $entityFields[$field],
                            'updated_by' => Auth::user()->emp_id,
                            'updated_at' => now()
                        ];
                        $entityUpdates[$field] = $entityFields[$field];
                    }
                }
            }

            if (!empty($entityLogs)) {
                EntityDetailsAuditLog::insert($entityLogs);
            }

            if (!empty($entityUpdates)) {
                $entityDetails->update($entityUpdates);
                Log::info('Entity details updated', ['application_id' => $application->id, 'updates' => $entityUpdates]);
            }

            // Update Authorized Persons (if applicable)
            $authLogs = [];
            if (!empty($authorizedPersons)) {
                foreach ($authorizedPersons as $index => $person) {
                    $authPerson = AuthorizedPerson::where('id', $person['id'])
                        ->where('application_id', $application->id)
                        ->first();

                    if ($authPerson) {
                        $authFields = ['name', 'contact', 'address', 'aadhar_number'];
                        $authUpdates = [];

                        foreach ($authFields as $field) {
                            if (isset($person[$field]) && $person[$field] !== $authPerson->$field) {
                                $authLogs[] = [
                                    'application_id' => $application->id,
                                    'entity_type' => 'authorized_person',
                                    'field_name' => $field,
                                    'old_value' => $authPerson->$field,
                                    'new_value' => $person[$field],
                                    'updated_by' => Auth::user()->emp_id,
                                    'updated_at' => now()
                                ];
                                $authUpdates[$field] = $person[$field];
                            }
                        }

                        if (!empty($authUpdates)) {
                            $authPerson->update($authUpdates);
                            Log::info('Authorized person updated', ['application_id' => $application->id, 'person_id' => $authPerson->id, 'updates' => $authUpdates]);
                        }
                    }
                }
            }

            if (!empty($authLogs)) {
                EntityDetailsAuditLog::insert($authLogs);
            }

            // Update checkpoint
            ApplicationCheckpoint::updateOrCreate(
                [
                    'application_id' => $application->id,
                    'checkpoint_name' => 'entity_details'
                ],
                [
                    'status' => $entityVerification,
                    'reason' => $entityVerification === 'verified' ? null : $entityNote,
                    'submitted_by' => Auth::user()->emp_id,
                    'updated_at' => now()
                ]
            );

            // Update onboarding status
            $allCheckpoints = ApplicationCheckpoint::where('application_id', $application->id)->get();
            // $allVerified = $allCheckpoints->every(fn($checkpoint) => $checkpoint->status === 'verified');
            // $hasPendingAdditionalDocs = ApplicationAdditionalDocument::where('application_id', $application->id)
            //     ->where('status', 'pending')
            //     ->exists();

            // if ($allVerified && !$hasPendingAdditionalDocs) {
            //     $application->status = 'documents_verified';
            //     $application->mis_verified_at = now();
            //     $application->mis_rejected_at = null;
            // } else {
            //     $application->status = 'documents_pending';
            //     $application->mis_rejected_at = now();
            // }
            $application->save();

            // Determine success message based on verification status
            $message = $entityVerification === 'verified'
                ? 'Entity details verified successfully.'
                : 'Entity details marked as not verified. Application sent back for corrections.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $entityVerification // Include status for UI updates if needed
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to update entity details', [
                'application_id' => $application->id,
                'user_id' => Auth::user()->emp_id,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['success' => false, 'message' => 'Error updating entity details'], 500);
        }
    }

    /**
     * Notify user about document rejection
     */
    private function notifyUserOfRejection($application, $feedback)
    {
        try {
            $user = \App\Models\Employee::where('employee_id', $application->created_by)->first();
            if ($user && $user->emp_email) {
                \Mail::to($user->emp_email)->send(new \App\Mail\DocumentsRejected($application, $feedback));
            }
        } catch (\Exception $e) {
        }
    }


    // In ApprovalController.php
    public function showPhysicalDocuments(Onboarding $application)
    {
        if (!Auth::user()->employee->isMisTeam() && !in_array(Auth::user()->role, ['TM', 'GM'])) {
            abort(403, 'Unauthorized');
        }

        // Fetch all physical document checks for the application, including original_filename if column exists
        $physicalDocumentChecks = PhysicalDocumentCheck::where('application_id', $application->id)
            ->with(['securityChequeDetails', 'securityDepositDetail'])
            ->select(['*']) // Assuming original_filename is added to the table
            ->get();

        // Fetch physical dispatch record
        $physicalDispatch = PhysicalDispatch::where('application_id', $application->id)->first() ?? new PhysicalDispatch();

        // Eager load checkpoints to display in "List of Documents"
        $application->load('checkpoints');

        // Load entityDetails with supporting document paths
        $application->load('entityDetails');

        // Dynamically collect supporting documents if paths exist
        $supportingDocuments = collect();
        $entityDetails = $application->entityDetails;

        // Check for ownership_info_path
        if ($entityDetails->ownership_info_path) {
            $supportingDocuments->push([
                'type' => 'ownership_info',
                'label' => 'Ownership Information',
                'path' => $entityDetails->ownership_info_path,
                'existing_file' => basename($entityDetails->ownership_info_path)
            ]);
        }

        // Check for itr_acknowledgement_path
        if ($entityDetails->itr_acknowledgement_path) {
            $supportingDocuments->push([
                'type' => 'itr_acknowledgement',
                'label' => 'ITR Acknowledgement',
                'path' => $entityDetails->itr_acknowledgement_path,
                'existing_file' => basename($entityDetails->itr_acknowledgement_path)
            ]);
        }

        // Check for balance_sheet_path
        if ($entityDetails->balance_sheet_path) {
            $supportingDocuments->push([
                'type' => 'balance_sheet',
                'label' => 'Balance Sheet',
                'path' => $entityDetails->balance_sheet_path,
                'existing_file' => basename($entityDetails->balance_sheet_path)
            ]);
        }

        return view('approvals.physical-documents', compact('application', 'physicalDocumentChecks', 'physicalDispatch', 'supportingDocuments'));
    }

    public function updatePhysicalDocuments(Request $request, Onboarding $application)
    {
        //dd($request->all());
        // Check if any document is marked as received
        $anyReceived = false;
        $documents = $request->input('documents', []);
        foreach ($documents as $data) {
            if (is_array($data) && isset($data['received']) && ($data['received'] ?? false) == true) {
                $anyReceived = true;
                break;
            }
        }

        // Define validation rules with custom messages
        $rules = [
            'receive_date' => ['required_if:any_received,true', 'nullable', 'date', 'before_or_equal:today'],
            'verified_date' => 'required|date|before_or_equal:today',
            'documents' => 'required|array|min:1',
            'documents.*.received' => 'required|in:0,1',
            'documents.*.status' => 'required|in:verified,not_verified',
            'documents.*.reason' => 'required_if:documents.*.status,not_verified|string|max:500|nullable',
            // Standard docs
            'existing_agreement_copy_file' => 'nullable|string',
            'existing_agreement_copy_file_original' => 'nullable|string',
            'existing_security_cheques_file' => 'nullable|array',
            'existing_security_cheques_file.*' => 'string',
            'existing_security_cheques_file_original' => 'nullable|array',
            'existing_security_cheques_file_original.*' => 'string',
            'existing_security_deposit_file' => 'nullable|string',
            'existing_security_deposit_file_original' => 'nullable|string',
            // Security Cheques details - required if verified and files present
            'security_cheques_details' => 'required_if:documents.security_cheques.received,1|nullable|array',
            'security_cheques_details.*.date_obtained' => 'required_if:documents.security_cheques.status,verified|nullable|date|before_or_equal:today',
            'security_cheques_details.*.cheque_no' => 'required_if:documents.security_cheques.status,verified|nullable|string|max:50',
            'security_cheques_details.*.date_use' => 'nullable|date|before_or_equal:today',
            'security_cheques_details.*.purpose' => 'nullable|string|max:200',
            'security_cheques_details.*.date_return' => 'nullable|date|before_or_equal:today',
            'security_cheques_details.*.remark_return' => 'nullable|string|max:500',
            // Supporting docs
            'existing_ownership_info_file' => 'nullable|string',
            'existing_itr_acknowledgement_file' => 'nullable|string',
            'existing_balance_sheet_file' => 'nullable|string',
            // Security Deposit details - required if verified
            'deposit_date' => ['required_if:documents.security_deposit.status,verified', 'nullable', 'date', 'before_or_equal:today'],
            'deposit_mode' => ['required_if:documents.security_deposit.status,verified', 'nullable', 'string', 'in:Cash,Cheque,NEFT/Online'],
            'deposit_reference' => ['required_if:deposit_mode,NEFT/Online', 'nullable', 'string', 'max:100'],
            'security_deposit_amount' => ['required_if:documents.security_deposit.status,verified', 'nullable', 'numeric', 'min:0'],
        ];

        $messages = [
            'receive_date.required_if' => 'The date of receiving documents is required when any document is marked as received.',
            'receive_date.date' => 'The date of receiving documents must be a valid date.',
            'receive_date.before_or_equal' => 'The date of receiving documents cannot be in the future.',
            'verified_date.required' => 'The verified date is required.',
            'verified_date.date' => 'The verified date must be a valid date.',
            'verified_date.before_or_equal' => 'The verified date cannot be in the future.',
            'documents.required' => 'Document data is required.',
            'documents.min' => 'At least one document must be processed.',
            'documents.*.received.required' => 'Please select received status for :attribute.',
            'documents.*.received.in' => 'Received status must be either Received or Not Received.',
            'documents.*.status.required' => 'Please select a verification status for :attribute.',
            'documents.*.status.in' => 'Verification status must be either Verified or Not Verified.',
            'documents.*.reason.required_if' => 'Remarks are required when the document is not verified.',
            'documents.*.reason.max' => 'Remarks cannot exceed 500 characters.',
            // Security Cheques details messages
            'security_cheques_details.required_if' => 'Security Cheque details are required when verified.',
            'security_cheques_details.*.date_obtained.required_if' => 'Date of obtained is required when verified.',
            'security_cheques_details.*.date_obtained.date' => 'Date of obtained must be a valid date.',
            'security_cheques_details.*.date_obtained.before_or_equal' => 'Date of obtained cannot be in the future.',
            'security_cheques_details.*.cheque_no.required_if' => 'Cheque No is required when verified.',
            'security_cheques_details.*.cheque_no.max' => 'Cheque No cannot exceed 50 characters.',
            'security_cheques_details.*.date_use.date' => 'Date of Use must be a valid date.',
            'security_cheques_details.*.date_use.before_or_equal' => 'Date of Use cannot be in the future.',
            'security_cheques_details.*.purpose.max' => 'Purpose of use cannot exceed 200 characters.',
            'security_cheques_details.*.date_return.date' => 'Date of Return must be a valid date.',
            'security_cheques_details.*.date_return.before_or_equal' => 'Date of Return cannot be in the future.',
            'security_cheques_details.*.remark_return.max' => 'Remark/reason of return cannot exceed 500 characters.',
            // Security Deposit details messages
            'deposit_date.required_if' => 'Deposit Date is required when Security Deposit is verified.',
            'deposit_date.date' => 'Deposit Date must be a valid date.',
            'deposit_date.before_or_equal' => 'Deposit Date cannot be in the future.',
            'deposit_mode.required_if' => 'Mode of payment is required when Security Deposit is verified.',
            'deposit_mode.in' => 'Mode of payment must be Cash, Cheque, or NEFT/Online.',
            'deposit_reference.required_if' => 'Reference No. is required when mode is NEFT/Online.',
            'deposit_reference.max' => 'Reference No. cannot exceed 100 characters.',
            'security_deposit_amount.required_if' => 'Security Deposit Amount is required when Security Deposit is verified.',
            'security_deposit_amount.numeric' => 'Security Deposit Amount must be a valid number.',
            'security_deposit_amount.min' => 'Security Deposit Amount cannot be negative.',
            // File rules (keep, remove ack)
            'existing_agreement_copy_file.string' => 'Agreement Copy file is invalid.',
            'existing_agreement_copy_file_original.string' => 'Agreement Copy original name is invalid.',
            'existing_security_cheques_file.array' => 'Security Cheques files must be an array.',
            'existing_security_cheques_file.*.string' => 'Each Security Cheques file is invalid.',
            'existing_security_cheques_file_original.array' => 'Security Cheques original names must be an array.',
            'existing_security_cheques_file_original.*.string' => 'Each Security Cheques original name is invalid.',
            'existing_security_deposit_file.string' => 'Security Deposit file is invalid.',
            'existing_security_deposit_file_original.string' => 'Security Deposit original name is invalid.',
            'existing_ownership_info_file.string' => 'Ownership Information file is invalid.',
            'existing_itr_acknowledgement_file.string' => 'ITR Acknowledgement file is invalid.',
            'existing_balance_sheet_file.string' => 'Balance Sheet file is invalid.',
        ];

        $validator = Validator::make(array_merge($request->all(), ['any_received' => $anyReceived ? 'true' : 'false']), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check the form for errors.',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Delete existing physical document checks and related details
            $application->physicalDocumentChecks()->each(function ($check) {
                SecurityChequeDetail::where('physical_document_check_id', $check->id)->delete();
                SecurityDepositDetail::where('physical_document_check_id', $check->id)->delete();
            });
            $application->physicalDocumentChecks()->delete();

            // Update or create physical_dispatch record
            PhysicalDispatch::updateOrCreate(
                ['application_id' => $application->id],
                [
                    'receive_date' => $anyReceived ? $request->input('receive_date') : null,
                    'updated_by' => Auth::id(),
                ]
            );
            // Track documents that need notification and overall status
            $documentsNeedingNotification = [];
            $hasUnreceivedDocuments = false;
            $hasUnverifiedDocuments = false;

            $savedCount = 0;
            foreach ($documents as $type => $data) {
                $received = ($data['received'] ?? '0') == '1'; // Now it's string '1' or '0'
                $status = $data['status'] ?? 'pending';
                $reason = $data['reason'] ?? null;
                $amount = null;

                // Check if document needs notification (Not Received OR Not Verified)
                if (!$received || $status === 'not_verified') {
                    $documentsNeedingNotification[] = [
                        'type' => $type,
                        'received' => $received,
                        'status' => $status,
                        'reason' => $reason
                    ];

                    if (!$received) {
                        $hasUnreceivedDocuments = true;
                    }
                    if ($status === 'not_verified') {
                        $hasUnverifiedDocuments = true;
                    }
                }

                if ($type === 'security_deposit') {
                    $amount = $request->input('security_deposit_amount');
                    $deposit_date = $request->input('deposit_date');
                    $mode = $request->input('deposit_mode');
                    $reference = $request->input('deposit_reference');
                    // If verified and amount provided, use amount; otherwise, use reason for non-verified cases
                    if ($status === 'verified' && $amount) {
                        // Amount is stored in 'amount' column; reason can be null or additional notes
                        $reason = null; // Store details in separate table
                    } else if ($status === 'not_verified') {
                        // Reason is required for not verified
                        $amount = null;
                    }
                }

                if ($type === 'security_cheques') {
                    $files = $request->input("existing_security_cheques_file", []);
                    $originalNames = $request->input("existing_security_cheques_file_original", []);
                    $details = $request->input("security_cheques_details", []);

                    // If received is true, we must create at least one record even if no files
                    if ($received) {
                        // If no files but received is true, create one record without file
                        if (empty($files)) {
                            $check = PhysicalDocumentCheck::create([
                                'application_id' => $application->id,
                                'document_type' => $type,
                                'received' => $received,
                                'status' => $status,
                                'reason' => $reason,
                                'amount' => $amount,
                                'file_path' => null, // No file
                                'original_filename' => null,
                                'submitted_by' => Auth::user()->emp_id,
                                'verified_date' => ($status === 'verified') ? $request->input('verified_date') : null,
                            ]);

                            // Create details if we have data (even without file)
                            if ($status === 'verified' && isset($details[0]) && (!empty($details[0]['cheque_no']) || !empty($details[0]['date_obtained']))) {
                                $check->securityChequeDetails()->create($details[0]);
                            }
                            $savedCount++;
                        } else {
                            // Create records for files that actually exist
                            foreach ($files as $index => $fileName) {
                                if (!empty($fileName)) {
                                    $originalName = $originalNames[$index] ?? $fileName;
                                    $detailData = $details[$index] ?? [];

                                    $check = PhysicalDocumentCheck::create([
                                        'application_id' => $application->id,
                                        'document_type' => $type,
                                        'received' => $received,
                                        'status' => $status,
                                        'reason' => $reason,
                                        'amount' => $amount,
                                        'file_path' => $fileName,
                                        'original_filename' => $originalName,
                                        'submitted_by' => Auth::user()->emp_id,
                                        'verified_date' => ($status === 'verified') ? $request->input('verified_date') : null,
                                    ]);

                                    // Create details only if we have data
                                    if ($status === 'verified' && (!empty($detailData['cheque_no']) || !empty($detailData['date_obtained']))) {
                                        $check->securityChequeDetails()->create($detailData);
                                    }
                                    $savedCount++;
                                }
                            }
                        }
                    } else {
                        // Not received - create one record without file
                        $check = PhysicalDocumentCheck::create([
                            'application_id' => $application->id,
                            'document_type' => $type,
                            'received' => $received,
                            'status' => $status,
                            'reason' => $reason,
                            'amount' => $amount,
                            'file_path' => null,
                            'original_filename' => null,
                            'submitted_by' => Auth::user()->emp_id,
                            'verified_date' => ($status === 'verified') ? $request->input('verified_date') : null,
                        ]);
                        $savedCount++;
                    }
                } else {
                    // Single file for other types
                    $fileName = null;
                    $originalName = null;
                    if (in_array($type, ['agreement_copy', 'security_deposit'])) {
                        $fileName = $request->input("existing_{$type}_file");
                        $originalName = $request->input("existing_{$type}_file_original", $fileName);
                    }

                    // Skip if no data (but always save if there's an issue for notification)
                    if ($received || $status !== 'pending' || $fileName || !$received || $status === 'not_verified') {
                        $check = PhysicalDocumentCheck::create([
                            'application_id' => $application->id,
                            'document_type' => $type,
                            'received' => $received,
                            'status' => $status,
                            'reason' => $reason,
                            'amount' => $amount,
                            'file_path' => $fileName ?: null,
                            'original_filename' => $originalName ?: null,
                            'submitted_by' => Auth::user()->emp_id,
                            'verified_date' => ($status === 'verified') ? $request->input('verified_date') : null,
                        ]);
                        if ($type === 'security_deposit' && $status === 'verified') {
                            Log::info('Deposit Debug', ['deposit_date' => $request->input('deposit_date'), 'amount' => $amount]); // Debug log
                            $check->securityDepositDetail()->create([
                                'deposit_date' => $request->input('deposit_date'),
                                'amount' => $amount,
                                'mode_of_payment' => $request->input('deposit_mode'),
                                'reference_no' => $request->input('deposit_reference'),
                            ]);
                        }
                        $savedCount++;
                    }
                }
            }
            // Determine application status based on document conditions
            $allDocumentsReceivedAndVerified = !$hasUnreceivedDocuments && !$hasUnverifiedDocuments;

            if ($allDocumentsReceivedAndVerified) {
                // ALL documents are received AND verified
                $application->physical_docs_status = 'verified';
                $application->status = 'physical_docs_verified';
                $application->save();

                Log::info('All physical documents verified', [
                    'application_id' => $application->id,
                    'status' => 'physical_docs_verified'
                ]);
            } else {
                // Some documents are not received or not verified
                $application->physical_docs_status = 'pending';
                if ($hasUnreceivedDocuments) {
                    $application->status = 'physical_docs_pending';
                } else if ($hasUnverifiedDocuments) {
                    $application->status = 'physical_docs_pending';
                }
                $application->save();
            }

            // Send notification if any documents need attention
            if (!empty($documentsNeedingNotification)) {
                $this->sendDocumentNotification($application, $documentsNeedingNotification, $allDocumentsReceivedAndVerified);
            }


            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Physical document status updated successfully. Saved {$savedCount} records." .
                    ($allDocumentsReceivedAndVerified ?
                        ' All documents verified successfully.' :
                        ' Some documents need attention. Notification sent.'),
                'saved_count' => $savedCount,
                'all_verified' => $allDocumentsReceivedAndVerified
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving physical documents', ['error' => $e->getMessage(), 'application_id' => $application->id]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save physical document status: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function sendDocumentNotification(Onboarding $application, array $documentsNeedingAttention, bool $allVerified = false)
    {
        try {
            // Get the creator of the application
            $creatorId = $application->created_by;
            $establishmentName = $application->entityDetails->establishment_name ?? 'Unknown Establishment';
            $applicationId = $application->id;

            if (!$creatorId) {
                Log::warning('No creator found for application', ['application_id' => $applicationId]);
                return;
            }

            if ($allVerified) {
                // Success notification - all documents verified
                $title = "All Physical Documents Verified - {$establishmentName}";
                $description = "All physical documents for {$establishmentName} have been successfully verified.\n\n";
                $description .= "Application ID: {$applicationId}\n";
                $description .= "Status: Ready for next steps.";
            } else {
                // Documents need attention
                $title = "Physical Documents Need Attention - {$establishmentName}";

                // Build detailed description
                $description = "Physical document verification completed for {$establishmentName}.\n\n";
                $description .= "The following documents require your attention:\n\n";

                foreach ($documentsNeedingAttention as $doc) {
                    $documentLabel = $this->getDocumentLabel($doc['type']);
                    $description .= "• {$documentLabel}\n";

                    if (!$doc['received']) {
                        $description .= "  - Status: ❌ Not Received\n";
                    }

                    if ($doc['status'] === 'not_verified') {
                        $description .= "  - Status: ⚠️ Not Verified\n";
                    }

                    if ($doc['reason']) {
                        $description .= "  - Reason: {$doc['reason']}\n";
                    }
                    $description .= "\n";
                }

                $description .= "Application ID: {$applicationId}\n";
                $description .= "Please review and take appropriate action.";
            }

            // Create notification
            DB::table('notification')->insert([
                'userid' => $creatorId,
                'notification_read' => 0,
                'title' => $title,
                'description' => $description,
                'created_at' => now(),
            ]);

            Log::info('Document notification sent', [
                'application_id' => $applicationId,
                'creator_id' => Auth::user()->id,
                'all_verified' => $allVerified,
                'documents_count' => count($documentsNeedingAttention)
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send document notification', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get human-readable document label
     */
    private function getDocumentLabel($documentType)
    {
        $labels = [
            'agreement_copy' => 'Agreement Copy',
            'security_cheques' => 'Security Cheques',
            'security_deposit' => 'Security Deposit',
            'ownership_info' => 'Ownership Information',
            'itr_acknowledgement' => 'ITR Acknowledgement',
            'balance_sheet' => 'Balance Sheet',
            'entity_details' => 'Entity Details',
            'main_document_pan' => 'PAN Card',
            'main_document_seed_license' => 'Seed License',
            'main_document_bank' => 'Bank Document',
            'main_document_entity_proof' => 'Entity Proof',
            'main_document_bank_statement' => 'Bank Statement',
            'main_document_itr_acknowledgement' => 'ITR Acknowledgement',
            'authorized_letter_0' => 'Authorized Letter',
            'authorized_aadhar_0' => 'Authorized Aadhar',
        ];

        return $labels[$documentType] ?? str_replace('_', ' ', ucfirst($documentType));
    }


    public function viewDocVerification(Onboarding $application)
    {
        if (!Auth::user()->employee->isMisTeam()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        return view('dashboard.partials.doc_verification_content', compact('application'));
    }

    public function viewPhysicalDocVerification(Onboarding $application)
    {
        if (!Auth::user()->employee->isMisTeam()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Get physical document checks only
        $checks = $application->physicalDocumentChecks()->get();
        $groupedChecks = $checks->groupBy('document_type');

        // Load checkpoints separately
        $application->load('checkpoints');

        // Define types
        $coreTypes = collect(['agreement_copy', 'security_cheques', 'security_deposit']);
        $supportingTypes = collect(['ownership_info', 'itr_acknowledgement', 'balance_sheet']);

        // Base checkpoint types
        $baseCheckpointTypes = collect([
            'entity_details',
            'main_document_pan',
            'main_document_gst',
            'main_document_seed_license',
            'main_document_bank',
            'main_document_ownership',
            'main_document_bank_statement',
            'main_document_itr_acknowledgement',
            'main_document_balance_sheet',
        ]);

        // Dynamically add authorized person types
        $authorizedPersons = $application->loadMissing('authorizedPersons')->authorizedPersons ?? collect();
        $authTypes = collect();
        foreach ($authorizedPersons as $index => $person) {
            $personLabel = $person->name ? " ({$person->name})" : " (Person " . ($index + 1) . ")";
            $letterType = "authorized_letter_{$index}";
            $aadharType = "authorized_aadhar_{$index}";
            $authTypes->push([
                'type' => $letterType,
                'label' => "Authorized Letter" . $personLabel
            ]);
            $authTypes->push([
                'type' => $aadharType,
                'label' => "Authorized Aadhar" . $personLabel
            ]);
        }

        $checkpointTypes = $baseCheckpointTypes->merge($authTypes->pluck('type'));
        $customLabels = $authTypes->pluck('label', 'type');

        // Add supporting documents collection
        $supportingDocuments = $application->supportingDocuments ?? collect([]);

        return view('dashboard.partials.physical_doc_verification_content', compact(
            'application',
            'checks',
            'groupedChecks',
            'coreTypes',
            'supportingTypes',
            'checkpointTypes',
            'supportingDocuments',
            'customLabels'
        ));
    }

    public function confirmDistributor(Request $request, Onboarding $application)
    {
        $user = Auth::user();

        // Check authorization (MIS team or distributor_approval permission)
        if (!$user->employee->isMisTeam() && !$user->hasPermissionTo('distributor_approval')) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        // Validate application status - PREVENT DUPLICATE CONFIRMATION
        if (!in_array($application->status, ['agreement_created', 'physical_docs_verified'])) {
            $message = 'Cannot confirm distributor for application with status: ' . $application->status;
            if ($application->status === 'distributorship_created') {
                $message = 'Distributor has already been confirmed for this application.';
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 403);
            }
            return redirect()->back()->with('error', $message);
        }

        // Validate request - FIXED UNIQUE VALIDATION
        $validator = Validator::make($request->all(), [
            'date_of_appointment' => 'required|date|before_or_equal:today',
            'distributor_code' => 'required|string|max:50|unique:onboardings,distributor_code,' . $application->id, // FIXED
            'remarks' => 'nullable|string|min:5|max:255',
            'authorized_person_name' => 'required|string|max:255',
            'authorized_person_designation' => 'required|string|max:255'
        ], [
            'date_of_appointment.required' => 'Appointment date is required.',
            'date_of_appointment.before_or_equal' => 'Appointment date cannot be in the future.',
            'distributor_code.required' => 'Distributor code is required.',
            'distributor_code.unique' => 'This distributor code is already in use.',
            'remarks.min' => 'Remarks, if provided, must be at least 5 characters.',
            'remarks.max' => 'Remarks cannot exceed 255 characters.',
            'authorized_person_name.required' => 'Authorized person name is required.',
            'authorized_person_designation.required' => 'Authorized person designation is required.'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Validation failed.');
        }

        try {
            // Begin transaction
            DB::beginTransaction();

            // Update application with distributor details
            $application->update([
                'status' => 'distributorship_created',
                'distributor_code' => $request->distributor_code,
                'date_of_appointment' => $request->date_of_appointment,
                'authorized_person_name' => $request->authorized_person_name,
                'authorized_person_designation' => $request->authorized_person_designation,
                'distributorship_confirmed_at' => now(),
                'final_approver_id' => $user->emp_id
            ]);

            // Log the action
            $this->createApprovalLog(
                $application->id,
                $user->emp_id,
                $user->employee->emp_designation,
                'distributor_confirmed',
                $request->input('remarks', 'Distributor confirmed with code: ' . $request->distributor_code .
                    ' and appointment date: ' . $request->date_of_appointment .
                    ' by authorized person: ' . $request->authorized_person_name)
            );

            // Notify creator and sales hierarchy
            $this->notifyCreator(
                $application,
                'Distributorship Confirmed',
                "Your application has been confirmed as a distributor.\n" .
                    "Distributor Code: {$request->distributor_code}\n" .
                    "Appointment Date: {$request->date_of_appointment}\n" .
                    "Authorized By: {$request->authorized_person_name} ({$request->authorized_person_designation})"
            );

            $this->notifySalesHierarchy($application, 'Distributorship Confirmed');

            // Commit transaction
            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Distributor confirmed successfully!',
                    'redirect' => route('dashboard')
                ]);
            }
            return redirect()->route('dashboard')->with('success', 'Distributor confirmed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }



    public function applications(Request $request)
    {
        // Get filters from request
        $filters = [
            'status' => $request->get('status', ''),
            'kpi_filter' => $request->get('kpi_filter', '')
        ];
        // Get current user
        $currentUser = Auth::user();
        $currentUserId = $currentUser->emp_id;

        // Query applications based on filters
        $query = Onboarding::with([
            'entityDetails',
            'territoryDetail',
            'regionDetail',
            'zoneDetail',
            'createdBy',
            'approvalLogs' => function ($q) use ($currentUserId) {
                $q->where('user_id', $currentUserId); // Only load current user's approval logs
            },
            'currentApprover'
        ]);

        // Get user type
        $isMisUser = $currentUser->hasAnyRole(['Mis Admin', 'Mis User']);
        $isAdminUser = $currentUser->hasAnyRole(['Super Admin', 'Admin']) || $currentUser->hasPermissionTo('distributor_approval');

        // Fetch all active statuses dynamically
        $allStatuses = Status::where('is_active', 1)->orderBy('sort_order', 'asc')->get();

        // Define custom status groups for filters
        $statusGroups = [
            '' => [
                'label' => 'All',
                'slugs' => ''
            ],
            'pending' => [
                'label' => 'Pending',
                'slugs' => $allStatuses->whereIn('name', ['under_level1_review', 'under_level2_review', 'under_level3_review', 'reverted', 'on_hold'])->pluck('name')->implode(',')
            ],
            'mis' => [
                'label' => 'MIS Processing',
                'slugs' => $allStatuses->where('category', 'mis_processing')->pluck('name')->implode(',')
            ],
            'completed' => [
                'label' => 'Completed',
                'slugs' => $allStatuses->where('category', 'completion')->pluck('name')->implode(',')
            ],
            'rejected' => [
                'label' => 'Rejected',
                'slugs' => $allStatuses->where('category', 'rejection')->pluck('name')->implode(',')
            ],
            'approved_by_you' => [
                'label' => 'Approved by You',
                'slugs' => 'approved_by_you' // Special case
            ]
        ];

        // Actionable statuses for modal
        $actionableStatuses = $allStatuses->whereIn('name', ['approved', 'rejected', 'on_hold']);

        // Apply status filter from KPI click
        if (!empty($filters['status'])) {

            // SPECIAL CASE: Handle "approved_by_you" filter
            if ($filters['kpi_filter'] === 'approved_by_you' || $filters['status'] === 'approved_by_you') {
                $query->whereExists(function ($subQuery) use ($currentUserId) {
                    $subQuery->select(DB::raw(1))
                        ->from('approval_logs')
                        ->whereColumn('approval_logs.application_id', 'onboardings.id')
                        ->where('approval_logs.user_id', $currentUserId)
                        ->where('approval_logs.action', 'approved');
                });
            }
            // Handle regular status filters
            else {
                $statuses = explode(',', $filters['status']);
                $query->whereIn('status', $statuses);

                // SPECIAL HANDLING FOR PENDING VIEW
                if ($filters['status'] === $statusGroups['pending']['slugs']) {
                    if (!$isAdminUser && !$isMisUser) {
                        $query->where('current_approver_id', $currentUserId)
                            ->whereNotExists(function ($subQuery) use ($currentUserId) {
                                $subQuery->select(DB::raw(1))
                                    ->from('approval_logs')
                                    ->whereColumn('approval_logs.application_id', 'onboardings.id')
                                    ->where('approval_logs.user_id', $currentUserId)
                                    ->whereIn('approval_logs.action', ['approved', 'rejected']);
                            });
                    }
                }
            }
        } else {
            // Default behavior - show applications relevant to current user
            if (!$isAdminUser && !$isMisUser) {
                $query->where(function ($q) use ($currentUserId) {
                    $q->where(function ($subQ) use ($currentUserId) {
                        $subQ->where('current_approver_id', $currentUserId)
                            ->whereNotExists(function ($subQuery) use ($currentUserId) {
                                $subQuery->select(DB::raw(1))
                                    ->from('approval_logs')
                                    ->whereColumn('approval_logs.application_id', 'onboardings.id')
                                    ->where('approval_logs.user_id', $currentUserId)
                                    ->whereIn('approval_logs.action', ['approved', 'rejected']);
                            });
                    })
                        ->orWhere('created_by', $currentUserId)
                        ->orWhereExists(function ($subQuery) use ($currentUserId) {
                            $subQuery->select(DB::raw(1))
                                ->from('approval_logs')
                                ->whereColumn('approval_logs.application_id', 'onboardings.id')
                                ->where('approval_logs.user_id', $currentUserId);
                        });
                });
            }
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(20);

        // Prepare data for view
        $viewData = compact('applications', 'filters', 'statusGroups', 'actionableStatuses', 'allStatuses');
        if ($isMisUser) {
            return view('mis.applications', $viewData);
        } else {
            return view('approver.applications', $viewData);
        }
    }

    // App\Http\Controllers\ApprovalController.php
    public function showDraftAgreement($id)
    {
        $application = Onboarding::with(['entityDetails', 'createdBy'])->findOrFail($id);

        // Check if application is in correct status
        if ($application->status != 'documents_verified') {
            abort(403, 'Draft agreement is only available for verified documents.');
        }

        return view('approvals.draft-agreement', compact('application'));
    }
}
