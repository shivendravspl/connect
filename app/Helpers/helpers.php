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
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'SP Admin', 'Management'])) {
            return DB::table('core_business_unit')
                ->where('is_active', '1')
                ->where('business_type', '1')
                ->pluck('business_unit_name', 'id')
                ->prepend('All BU', 'All')->toArray();;
        }

        $buId = DB::table('core_employee')
            ->where('id', $employeeId)
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
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'SP Admin', 'Management'])) {
            return DB::table('core_zone')
                ->where('is_active', '1')
                ->where('business_type', '1')
                ->pluck('zone_name', 'id')
                ->prepend('All Zone', 'All')->toArray();;
        }

        $zoneId = DB::table('core_employee')
            ->where('id', $employeeId)
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

        if ($user->hasAnyRole(['Super Admin', 'Admin', 'SP Admin', 'Management'])) {
            return DB::table('core_region')
                ->where('is_active', '1')
                ->where('business_type', '1')
                ->pluck('region_name', 'id')
                ->prepend('All Region', 'All')->toArray();;
        }

        $regionId = DB::table('core_employee')
            ->where('id', $employeeId)
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
        
        $territoryId = DB::table('core_employee')->where('id', $employeeId)->value('territory');

        if ($territoryId > 0) {
            return DB::table('core_territory')
                ->where('id', $territoryId)
                ->pluck('territory_name', 'id')->toArray();
        }

        return [];
    }

}
