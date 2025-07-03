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
        return CoreDistributor::leftJoin('core_territory as vc', 'core_distributor.vc_territory', '=', 'vc.id')
            ->leftJoin('core_territory as fc', 'core_distributor.fc_territory', '=', 'fc.id')
            ->leftJoin('core_territory as bulk', 'core_distributor.bulk_territory', '=', 'bulk.id')
            ->select(
                'core_distributor.*',
                'vc.territory_name as vc_territory_name',
                'fc.territory_name as fc_territory_name',
                'bulk.territory_name as bulk_territory_name'
            )
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Phone',
            'VC Territory',
            'FC Territory',
            'Bulk Territory',
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
            $distributor->fc_territory_name,
            $distributor->bulk_territory_name,
            $distributor->status
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A2');
        // Header row styling (red background with white text)
        $sheet->getStyle('A1:G1')->applyFromArray([
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
        foreach (range('A', 'G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }
}