<?php

namespace App\Http\Controllers;

use App\Models\Onboarding;
use App\Models\ApprovalLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Employee;

class ApprovalController extends Controller
{
   
    public function show(Onboarding $application)
    {
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
        // Authorization check
        $user = Auth::user();
        if ($application->current_approver_id !== $user->emp_id) {
            abort(403);
        }

        // Get next approval level based on current level
        $nextLevel = $this->getNextApprovalLevel($application->approval_level);

          // Create approval log
        ApprovalLog::create([
            'application_id' => $application->id,
            'user_id' => $user->emp_id,
            'role' => $application->approval_level,
            'action' => 'approve',
            'remarks' => $request->remarks ?? null
        ]);
        // Update application status
        if ($nextLevel === 'completed') {
            $application->update([
                'status' => 'approved',
                'current_approver_id' => $user->emp_id,
                'approval_level' => 'completed'
            ]);
        } else {
            $nextApproverId = $this->getNextApproverId($nextLevel, $user->emp_id);
            $application->update([
                'status' => 'submitted',
                'current_approver_id' => $nextApproverId,
                'approval_level' => $nextLevel
            ]);
        }
      

        // TODO: Send notification to next approver or creator

        return redirect()->route('dashboard')
            ->with('success', 'Application approved successfully');
    }

    public function reject(Request $request, Onboarding $application)
    {   
        // Authorization check
        $user = Auth::user();
        if ($application->current_approver_id !== $user->emp_id) {
            abort(403);
        }

        $request->validate([
            'remarks' => 'required|string'
        ]);

            // Create approval log
        ApprovalLog::create([
            'application_id' => $application->id,
            'user_id' => $user->emp_id,
            'role' => $application->approval_level,
            'action' => 'reject',
            'remarks' => $request->remarks
        ]);
        // Update application status
        $application->update([
            'status' => 'rejected',
            'current_approver_id' => $user->emp_id
        ]);

        // TODO: Send notification to creator

        return redirect()->route('dashboard')
            ->with('success', 'Application rejected successfully');
    }

    public function revert(Request $request, Onboarding $application)
    {
        // Authorization check
        $user = Auth::user();
        if ($application->current_approver_id !== $user->emp_id) {
            abort(403);
        }

        $request->validate([
            'remarks' => 'required|string'
        ]);

        // Create approval log
        ApprovalLog::create([
            'application_id' => $application->id,
            'user_id' => $user->emp_id,
            'role' => $application->approval_level,
            'action' => 'revert',
            'remarks' => $request->remarks
        ]);
        // Update application status
        $application->update([
            'status' => 'reverted',
            'current_approver_id' => $user->emp_id
        ]);

     

        // TODO: Send notification to creator
        return redirect()->route('dashboard')
            ->with('success', 'Application reverted successfully');
    }

    public function hold(Request $request, Onboarding $application)
    {
        // Authorization check
        $user = Auth::user();
        if ($application->current_approver_id !== $user->emp_id) {
            abort(403);
        }

        $request->validate([
            'remarks' => 'required|string',
            'follow_up_date' => 'required|date'
        ]);

         // Create approval log
        ApprovalLog::create([
            'application_id' => $application->id,
            'user_id' => $user->emp_id,
            'role' => $application->approval_level,
            'action' => 'hold',
            'remarks' => $request->remarks,
            'follow_up_date' => $request->follow_up_date
        ]);
        // Update application status
        $application->update([
            'status' => 'on_hold',
            'current_approver_id' => $user->emp_id // Keep with current approver
        ]);

        // TODO: Set up reminder notification for follow-up date

        return redirect()->route('dashboard')
             ->with('success', 'Application put on hold successfully');
    }

    private function getNextApprovalLevel($currentLevel)
    {
        $currentLevel = strtolower($currentLevel); // Normalize input
        $levels = [
            null => 'rbm', // Initial submission goes to RBM
            'rbm' => 'zbm',
            'zbm' => 'gm',
            'gm' => 'completed'
        ];

        return $levels[$currentLevel] ?? 'completed';
    }

    private function getNextApproverId($nextLevel, $currentUserEmpId)
    {
        // This is a simplified version - you'll need to implement your own logic
        // based on your organizational hierarchy
        switch ($nextLevel) {
            case 'rbm':
                // Get RBM for the application's region
                return Employee::where('id', $currentUserEmpId)
                    ->value('emp_reporting');
                
            case 'zbm':
                // Get ZBM for the application's zone
                return Employee::where('id', $currentUserEmpId)
                    ->value('emp_reporting');
                    
            case 'gm_sales':
                // Get GM Sales
                return Employee::where('id', $currentUserEmpId)
                    ->value('id');
                    
            default:
                return null;
        }
    }
}