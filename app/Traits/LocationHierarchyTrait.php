<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait LocationHierarchyTrait
{
    protected function getLocationHierarchy($emp_id)
    {
        $employee = DB::table('core_employee')->where('id', $emp_id)->first();
        $territory_list = [];
        $zone_list = [];
        $region_list = [];
        $bu_list = [];
        $preselected = [];

        if (!$employee) {
            return compact('territory_list', 'zone_list', 'region_list', 'bu_list', 'preselected');
        }

        // Fetch business units based on emp_vertical
        $bu_list = DB::table('core_business_unit')
            ->where('vertical_id', $employee->emp_vertical)
            ->where('is_active', 1)
            ->pluck('business_unit_name', 'id')
            ->toArray();
        if ($employee->bu > 0) {
            $preselected['bu'] = $employee->bu;
        }

        // Case 1: territory = 0, region = 0, zone = 0, bu > 0
        if ($employee->territory == 0 && $employee->region == 0 && $employee->zone == 0 && $employee->bu > 0) {
            $mapping = DB::select("
                SELECT 
                    bzm.zone_id,
                    z.zone_name,
                    zrm.region_id,
                    r.region_name,
                    rtm.territory_id,
                    t.territory_name
                FROM 
                    core_bu_zone_mapping bzm
                INNER JOIN 
                    core_zone z ON bzm.zone_id = z.id
                INNER JOIN 
                    core_zone_region_mapping zrm ON bzm.zone_id = zrm.zone_id
                INNER JOIN 
                    core_region r ON zrm.region_id = r.id
                LEFT JOIN 
                    core_region_territory_mapping rtm ON zrm.region_id = rtm.region_id
                LEFT JOIN 
                    core_territory t ON rtm.territory_id = t.id
                WHERE 
                    bzm.business_unit_id = ?
            ", [$employee->bu]);

            $zone_list = collect($mapping)->pluck('zone_name', 'zone_id')->unique()->filter()->toArray();
            $region_list = collect($mapping)->pluck('region_name', 'region_id')->unique()->filter()->toArray();
            $territory_list = collect($mapping)->pluck('territory_name', 'territory_id')->unique()->filter()->toArray();

            if (count($zone_list) === 1) {
                $preselected['zone'] = array_key_first($zone_list);
            }
            if (count($region_list) === 1) {
                $preselected['region'] = array_key_first($region_list);
            }
            if (count($territory_list) === 1) {
                $preselected['territory'] = array_key_first($territory_list);
            }
        }
        // Case 2: territory = 0, region = 0, zone > 0
        elseif ($employee->territory == 0 && $employee->region == 0 && $employee->zone > 0) {
            $mapping = DB::select("
                SELECT 
                    zrm.zone_id,
                    z.zone_name,
                    zrm.region_id,
                    r.region_name,
                    rtm.territory_id,
                    t.territory_name
                FROM 
                    core_zone_region_mapping zrm
                INNER JOIN 
                    core_zone z ON zrm.zone_id = z.id
                INNER JOIN 
                    core_region r ON zrm.region_id = r.id
                LEFT JOIN 
                    core_region_territory_mapping rtm ON zrm.region_id = rtm.region_id
                LEFT JOIN 
                    core_territory t ON rtm.territory_id = t.id
                WHERE 
                    zrm.zone_id = ?
            ", [$employee->zone]);

            $zone_list = collect($mapping)->pluck('zone_name', 'zone_id')->unique()->filter()->toArray();
            $region_list = collect($mapping)->pluck('region_name', 'region_id')->unique()->filter()->toArray();
            $territory_list = collect($mapping)->pluck('territory_name', 'territory_id')->unique()->filter()->toArray();

            if (count($region_list) === 1) {
                $preselected['region'] = array_key_first($region_list);
            }
            if (count($territory_list) === 1) {
                $preselected['territory'] = array_key_first($territory_list);
            }
        }
        // Case 3: territory = 0, region > 0
        elseif ($employee->territory == 0 && $employee->region > 0) {
            $mapping = DB::select("
                SELECT 
                    r.id as region_id,
                    r.region_name,
                    zrm.zone_id,
                    z.zone_name,
                    rtm.territory_id,
                    t.territory_name
                FROM 
                    core_region r
                LEFT JOIN 
                    core_zone_region_mapping zrm ON r.id = zrm.region_id
                LEFT JOIN 
                    core_zone z ON zrm.zone_id = z.id
                LEFT JOIN 
                    core_region_territory_mapping rtm ON r.id = rtm.region_id
                LEFT JOIN 
                    core_territory t ON rtm.territory_id = t.id
                WHERE 
                    r.id = ?
            ", [$employee->region]);

            $zone_list = collect($mapping)->pluck('zone_name', 'zone_id')->unique()->filter()->toArray();
            $region_list = collect($mapping)->pluck('region_name', 'region_id')->unique()->filter()->toArray();
            $territory_list = collect($mapping)->pluck('territory_name', 'territory_id')->unique()->filter()->toArray();

            if (count($territory_list) === 1) {
                $preselected['territory'] = array_key_first($territory_list);
            }
        }
        // Case 4: territory > 0
        elseif ($employee->territory > 0) {
            $territory = DB::table('core_territory')
                ->where('id', $employee->territory)
                ->first();

            if ($territory) {
                $territory_list = [$territory->id => $territory->territory_name];
                $preselected['territory'] = $territory->id;

                $mapping = DB::select("
                    SELECT 
                        r.id as region_id,
                        r.region_name,
                        z.id as zone_id,
                        z.zone_name,
                        bu.id as business_unit_id,
                        bu.business_unit_name
                    FROM 
                        core_territory t
                    LEFT JOIN 
                        core_region_territory_mapping rtm ON t.id = rtm.territory_id
                    LEFT JOIN 
                        core_region r ON rtm.region_id = r.id
                    LEFT JOIN 
                        core_zone_region_mapping zrm ON r.id = zrm.region_id
                    LEFT JOIN 
                        core_zone z ON zrm.zone_id = z.id
                    LEFT JOIN 
                        core_bu_zone_mapping bzm ON z.id = bzm.zone_id
                    LEFT JOIN 
                        core_business_unit bu ON bzm.business_unit_id = bu.id
                    WHERE 
                        t.id = ? AND bu.vertical_id = ?
                ", [$employee->territory, $employee->emp_vertical]);

                $region_list = collect($mapping)->pluck('region_name', 'region_id')->unique()->filter()->toArray();
                $zone_list = collect($mapping)->pluck('zone_name', 'zone_id')->unique()->filter()->toArray();
                $bu_list = collect($mapping)->pluck('business_unit_name', 'business_unit_id')->unique()->filter()->toArray();

                if (!empty($region_list)) {
                    $preselected['region'] = array_key_first($region_list);
                }
                if (!empty($zone_list)) {
                    $preselected['zone'] = array_key_first($zone_list);
                }
                if (!empty($bu_list)) {
                    $preselected['bu'] = array_key_first($bu_list);
                }
            }
        }

        return compact('territory_list', 'zone_list', 'region_list', 'bu_list', 'preselected');
    }
}