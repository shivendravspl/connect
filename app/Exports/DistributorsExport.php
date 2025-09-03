<?php

namespace App\Exports;

use App\Models\CoreDistributor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class DistributorsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function title(): string
    {
        return 'Distributor List';
    }

    public function collection()
    {
        return CoreDistributor::query()
            ->leftJoin('core_territory as vc', 'core_distributor.vc_territory', '=', 'vc.id')
            ->leftJoin('core_territory as fc', 'core_distributor.fc_territory', '=', 'fc.id')
            ->leftJoin('core_territory as bc', 'core_distributor.bulk_territory', '=', 'bc.id')
            ->leftJoin('core_employee as e1', 'core_distributor.vc_emp', '=', 'e1.id')
            ->leftJoin('core_employee as e2', 'core_distributor.fc_emp', '=', 'e2.id')
            ->leftJoin('core_employee as e3', 'core_distributor.bulk_emp', '=', 'e3.id')
            ->leftJoin('core_business_type as b', 'core_distributor.business_type', '=', 'b.id')
            ->select([
                'core_distributor.*',
                'vc.territory_name as vc_territory_name',
                'fc.territory_name as fc_territory_name',
                'bc.territory_name as bulk_territory_name',
                \Illuminate\Support\Facades\DB::raw("CONCAT(e1.emp_name, ' - VC') as vc_emp"),
                \Illuminate\Support\Facades\DB::raw("CONCAT(e2.emp_name, ' - FC') as fc_emp"),
                \Illuminate\Support\Facades\DB::raw("CONCAT(e3.emp_name, ' - Bulk') as bulk_emp"),
                'b.business_type',
            ])
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Phone',
            'VC Territory',
            'VC Employee',
            'FC Territory',
            'FC Employee',
            'Bulk Territory',
            'Bulk Employee',
            'Business Type',
            'Bulk Party',
            'Status'
        ];
    }

    public function map($distributor): array
    {
        return [
            $distributor->id,
            $distributor->name,
            $distributor->phone,
            $distributor->vc_territory_name,
            $distributor->vc_emp,
            $distributor->fc_territory_name,
            $distributor->fc_emp,
            $distributor->bulk_territory_name,
            $distributor->bulk_emp,
            $distributor->business_type,
            $distributor->bulk_party === 'Y' ? 'Yes' : 'No',
            $distributor->status === 'A' ? 'Active' : 'Deactive'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');
        // Header row styling (red background with white text)
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FF000080']
            ],     
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // Apply styles to all cells (borders, centering, text wrapping)
        $sheet->getStyle($sheet->calculateWorksheetDimension())->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Auto-size columns
        foreach (range('A', 'L') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }
}