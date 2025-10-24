<?php

namespace App\Helpers;

use App\Models\Employee;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class helpers
{

    function getAssociatedBusinessUnitList($employeeId)
    {
        $user = Auth::user();
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'SP Admin', 'Management', 'Mis Admin', 'Mis User'])) {
            return DB::table('core_business_unit')
                ->where('is_active', '1')
                ->where('business_type', '1')
                ->pluck('business_unit_name', 'id')
                ->prepend('All BU', 'All')->toArray();;
        }

        $buId = DB::table('core_employee')
            ->where('employee_id', $employeeId)
            ->where('zone', 0)
            ->value('bu');

        if ($buId > 0) {
            return DB::table('core_business_unit')
                ->where('id', $buId)
                ->where('is_active', '1')
                ->where('business_type', '1')
                ->pluck('business_unit_name', 'id')
                ->prepend('Select BU', '')->toArray();;
        }

        return [];
    }
    function getAssociatedZoneList($employeeId)
    {
        $user = Auth::user();
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'SP Admin', 'Management', 'Mis Admin', 'Mis User'])) {
            return DB::table('core_zone')
                ->where('is_active', '1')
                ->where('business_type', '1')
                ->pluck('zone_name', 'id')
                ->prepend('All Zone', 'All')->toArray();;
        }

        $zoneId = DB::table('core_employee')
            ->where('employee_id', $employeeId)
            ->where('region', 0)
            ->value('zone');
        if ($zoneId > 0) {
            return DB::table('core_zone')
                ->where('id', $zoneId)
                ->where('is_active', '1')
                ->where('business_type', '1')
                ->pluck('zone_name', 'id')
                ->prepend('Select Zone', '')->toArray();;
        }

        return [];
    }

    function getAssociatedRegionList($employeeId)
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['Super Admin', 'Admin', 'SP Admin', 'Management', 'Mis Admin', 'Mis User'])) {
            return DB::table('core_region')
                ->where('is_active', '1')
                ->where('business_type', '1')
                ->pluck('region_name', 'id')
                ->prepend('All Region', 'All')->toArray();;
        }

        $regionId = DB::table('core_employee')
            ->where('employee_id', $employeeId)
            ->where('territory', 0)
            ->value('region');

        if ($regionId > 0) {
            return DB::table('core_region')
                ->where('id', $regionId)
                ->pluck('region_name', 'id')->prepend('Select Region', '')->toArray();;
        }

        return [];
    }

    function getAssociatedTerritoryList($employeeId)
    {
        $user = Auth::user();

        if ($user->hasAnyRole(['Super Admin', 'Admin', 'SP Admin', 'Management'])) {
            return DB::table('core_territory')
                ->where('is_active', '1')
                ->where('business_type', '1')
                ->pluck('territory_name', 'id')
                ->prepend('All Territory', 'All')->toArray();
        }
        
        $territoryId = DB::table('core_employee')->where('employee_id', $employeeId)->value('territory');

        if ($territoryId > 0) {
            return DB::table('core_territory')
                ->where('id', $territoryId)
                ->pluck('territory_name', 'id')->toArray();
        }

        return [];
    }

      public static function getStatusBadgeColor($status)
    {
        return match($status) {
            'approved' => 'success',
            'rejected' => 'danger',
            'pending' => 'warning',
            'draft' => 'secondary',
            default => 'info'
        };
    }

    public static function getVerificationBadgeColor($status)
    {
        return match($status) {
            'verified', 'documents_verified', 'physical_docs_verified' => 'success',
            'pending', 'documents_pending', 'physical_docs_pending' => 'warning',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    // TAT Calculation Static Methods (moved from global functions)
    public static function calculateApprovalTat($distributor, $role)
    {
        $approvalLogs = $distributor->approvalLogs->where('role', $role);
        
        if ($approvalLogs->isEmpty()) {
            return '<span class="text-warning">Pending</span>';
        }

        $approvalLog = $approvalLogs->sortBy('created_at')->first();
        $previousLog = $distributor->approvalLogs
            ->where('created_at', '<', $approvalLog->created_at)
            ->sortByDesc('created_at')
            ->first();

        $startDate = $previousLog ? $previousLog->created_at : $distributor->created_at;
        $endDate = $approvalLog->created_at;
        $tat = $startDate->diffInDays($endDate);
        
        $badge = $tat <= 2 ? 'success' : ($tat <= 5 ? 'warning' : 'danger');
        return '<span class="badge bg-'.$badge.'">'.($tat > 0 ? $tat . ' days' : 'Same day').'</span>'; // Note: Use 'bg-' for Bootstrap 5 badges
    }

    public static function calculateMisTat($distributor)
    {
        if (!$distributor->mis_verified_at) {
            return '<span class="text-warning">Pending</span>';
        }

        $finalApproval = $distributor->approvalLogs->where('action', 'approved')->sortByDesc('created_at')->first();
        if (!$finalApproval) return 'N/A';

        $startDate = $finalApproval->created_at;
        $endDate = $distributor->mis_verified_at;
        $tat = $startDate->diffInDays($endDate);
        
        $badge = $tat <= 2 ? 'success' : ($tat <= 5 ? 'warning' : 'danger');
        return '<span class="badge bg-'.$badge.'">'.($tat > 0 ? $tat . ' days' : 'Same day').'</span>';
    }

    public static function calculatePhysicalDocsTat($distributor)
    {
        $dispatch = $distributor->physicalDispatch;
        if (!$dispatch || !$dispatch->dispatch_date) {
            return '<span class="text-warning">Pending</span>';
        }

        $startDate = $distributor->mis_verified_at;
        if (!$startDate) return 'N/A';

        $endDate = $dispatch->dispatch_date;
        $tat = $startDate->diffInDays($endDate);
        
        $badge = $tat <= 2 ? 'success' : ($tat <= 5 ? 'warning' : 'danger');
        return '<span class="badge bg-'.$badge.'">'.($tat > 0 ? $tat . ' days' : 'Same day').'</span>';
    }

    public static function calculateTotalTat($distributor)
    {
        $startDate = $distributor->created_at;
        
        if (in_array($distributor->status, ['completed', 'distributorships_created'])) {
            $endDate = $distributor->physicalDispatch?->dispatch_date ?? $distributor->updated_at;
        } elseif ($distributor->mis_verified_at) {
            $endDate = $distributor->mis_verified_at;
        } elseif ($distributor->approvalLogs->isNotEmpty()) {
            $endDate = $distributor->approvalLogs->last()->created_at;
        } else {
            $endDate = now();
        }

        return $startDate->diffInDays($endDate) . ' days';
    }

    public static function getTatStatus($totalTat)
    {
        $days = (int) $totalTat;
        if ($days <= 7) return 'Within SLA';
        if ($days <= 14) return 'Moderate Delay';
        return 'Critical Delay';
    }

}
