<?php

namespace App\Http\Controllers;
use App\Models\ApprovalLog;

use App\Models\DistributorApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();

        // Fetch pending applications (where the current user is the approver)
        $pendingApplications = DistributorApplication::where('current_approver_id', $user->emp_id)
                ->whereIn('status', ['submitted', 'on_hold'])
                ->with(['territoryDetail', 'regionDetail', 'zoneDetail', 'businessUnit'])
                ->orderByRaw("CASE WHEN status = 'on_hold' THEN 0 ELSE 1 END")
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        //dd(!empty($pendingApplications));
        // Fetch all applications the user has interacted with via approval_logs
        $myApplications = DistributorApplication::whereHas('approvalLogs', function ($query) use ($user) {
            $query->where('user_id', $user->emp_id);
        })
            ->with([
                'territoryDetail',
                'regionDetail',
                'zoneDetail',
                'businessUnit',
                'currentApprover',
                'approvalLogs' => function ($query) {
                    $query->with('user')->orderBy('created_at', 'desc');
                }
            ])
            ->orderByRaw("
                CASE 
                    WHEN status = 'reverted' THEN 0
                    WHEN status = 'on_hold' THEN 1
                    WHEN status = 'submitted' THEN 2
                    WHEN status = 'approved' THEN 3
                    WHEN status = 'rejected' THEN 4
                    ELSE 5
                END
            ")
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

            // Summarize actions per user from approval_logs
        $actionSummary = ApprovalLog::select('user_id', 'action')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('user_id', 'action')
            ->with('user')
            ->get()
            ->groupBy('user_id')
            ->map(function ($logs, $userId) {
                $user = $logs->first()->user;
                $actions = $logs->pluck('count', 'action')->toArray();
                return [
                    'user_name' => $user ? $user->name : 'Unknown',
                    'actions' => $actions
                ];
            });

        $data = [
            'user' => $user,
            'pendingApplications' => $pendingApplications,
            'myApplications' => $myApplications,
            'actionSummary' => $actionSummary
        ];

        return view('dashboard.dashboard', $data);
    }

    public function statusCounts()
    {
        $user = Auth::user();

        return response()->json([
            'pending' => DistributorApplication::where('current_approver_id', $user->emp_id)
                ->whereIn('status', ['submitted', 'on_hold'])
                ->count(),
            'my' => DistributorApplication::whereHas('approvalLogs', function ($query) use ($user) {
                $query->where('user_id', $user->emp_id);
            })->count()
        ]);
    }
}
