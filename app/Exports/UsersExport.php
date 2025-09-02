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
            'users.status',
            'users.phone',
            'users.created_at',
            'users.emp_id',
            DB::raw('GROUP_CONCAT(roles.name) AS roles')
        ])
        ->leftJoin('core_employee', 'users.emp_id', '=', 'core_employee.id')
        ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
        ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
        ->groupBy('users.id', 'users.name', 'users.email',  'users.status','users.phone', 'users.created_at', 'users.emp_id');
        // Apply search filter
        if ($this->request->has('search') && !empty($this->request->input('search'))) {
            $search = strtolower($this->request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(users.id) LIKE ?', ["%$search%"])
                  ->orWhereRaw('LOWER(users.name) LIKE ?', ["%$search%"])
                  ->orWhereRaw('LOWER(users.email) LIKE ?', ["%$search%"])
                  ->orWhereRaw('LOWER(users.phone) LIKE ?', ["%$search%"])
                  ->orWhereRaw('LOWER(users.created_at) LIKE ?', ["%$search%"]);
                  
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
            'Status',
            'Phone',
            'Roles',
            'Created At',
        ];
    }

     public function map($user): array
    {
        $status = '-';
        if ($user->status) {
            switch ($user->status) {
                case 'A':
                    $status = 'Active';
                    break;
                case 'P':
                    $status = 'Pending';
                    break;
                case 'D':
                    $status = 'Disabled';
                    break;
                default:
                    $status = $user->status;
            }
        }

        return [
            $user->id,
            $user->name,
            $user->email ?: '-',
            $status,
            $user->phone ?: '-',
            $user->roles ?: '-',
            $user->created_at ? $user->created_at->format('Y-m-d') : '-',
        ];
    }


    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');
        $sheet->getStyle('A1:G1')->applyFromArray([ // Changed to G1 to account for the new column
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

        foreach (range('A', 'G') as $columnID) { // Changed to G to account for the new column
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }
}