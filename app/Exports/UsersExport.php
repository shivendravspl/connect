<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsersExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function title(): string
    {
        return 'User List';
    }

    public function query()
    {
        $query = User::select([
            'users.id',
            'users.name',
            'users.email',
            'users.phone',
            'users.created_at',
            'users.emp_id',
            DB::raw('COALESCE(core_territory.territory_name, "-") AS territory_name'),
            DB::raw('COALESCE(core_region.region_name, "-") AS region_name'),
            DB::raw('COALESCE(core_zone.zone_name, "-") AS zone_name'),
            DB::raw('CASE 
                WHEN core_employee.emp_vertical = 1 THEN "Field Crop" 
                WHEN core_employee.emp_vertical = 2 THEN "Veg Crop" 
                ELSE "-" 
            END AS crop_vertical_name'),
            DB::raw('GROUP_CONCAT(roles.name) AS roles')
        ])
        ->leftJoin('core_employee', 'users.emp_id', '=', 'core_employee.id')
        ->leftJoin('core_territory', 'core_employee.territory', '=', 'core_territory.id')
        ->leftJoin('core_region', 'core_employee.region', '=', 'core_region.id')
        ->leftJoin('core_zone', 'core_employee.zone', '=', 'core_zone.id')
        ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
        ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->groupBy('users.id', 'users.name', 'users.email', 'users.phone', 'users.created_at', 'users.emp_id', 
                 'core_territory.territory_name', 'core_region.region_name', 'core_zone.zone_name', 'core_employee.emp_vertical');

        // Apply filters
        if ($this->request->has('bu_id') && $this->request->bu_id && $this->request->bu_id !== 'All') {
            $query->where('core_employee.bu', $this->request->bu_id);
        }

        if ($this->request->has('territory_id') && $this->request->territory_id) {
            $query->where('core_employee.territory', $this->request->territory_id);
        }

        if ($this->request->has('region_id') && $this->request->region_id) {
            $query->where('core_employee.region', $this->request->region_id);
        }

        if ($this->request->has('zone_id') && $this->request->zone_id) {
            $query->where('core_employee.zone', $this->request->zone_id);
        }

        if ($this->request->has('crop_vertical') && $this->request->crop_vertical) {
            $query->where('core_employee.emp_vertical', $this->request->crop_vertical);
        }

        // Apply search filter
        if ($this->request->has('search') && !empty($this->request->input('search'))) {
            $search = strtolower($this->request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(users.id) LIKE ?', ["%$search%"])
                  ->orWhereRaw('LOWER(users.name) LIKE ?', ["%$search%"])
                  ->orWhereRaw('LOWER(users.email) LIKE ?', ["%$search%"])
                  ->orWhereRaw('LOWER(users.phone) LIKE ?', ["%$search%"])
                  ->orWhereRaw('LOWER(users.created_at) LIKE ?', ["%$search%"])
                  ->orWhereRaw('LOWER(core_territory.territory_name) LIKE ?', ["%$search%"])
                  ->orWhereRaw('LOWER(core_region.region_name) LIKE ?', ["%$search%"])
                  ->orWhereRaw('LOWER(core_zone.zone_name) LIKE ?', ["%$search%"])
                  ->orWhereRaw('core_employee.emp_vertical IN (1, 2) AND (
                      (core_employee.emp_vertical = 1 AND LOWER(?) LIKE ?) OR 
                      (core_employee.emp_vertical = 2 AND LOWER(?) LIKE ?)
                  )', ['Field Crop', "%$search%", 'Veg Crop', "%$search%"]);
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Roles',
            'Territory',
            'Region',
            'Zone',
            'Crop Vertical',
            'Created At',
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email ?: '-',
            $user->phone ?: '-',
            $user->roles ?: '-',
            $user->territory_name,
            $user->region_name,
            $user->zone_name,
            $user->crop_vertical_name,
            $user->created_at ? $user->created_at->format('Y-m-d') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FF000080'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        foreach (range('A', 'J') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }
}