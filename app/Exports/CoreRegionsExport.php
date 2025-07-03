<?php

namespace App\Exports;

use App\Models\CoreRegion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class CoreRegionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Regions List';
    }

    public function collection()
    {
        return CoreRegion::select(
            'core_region.id',
            'core_business_type.business_type',
            'core_vertical.vertical_name',
            'core_region.region_name',
            'core_region.region_code',
            'core_region.numeric_code',
            'core_region.effective_date',
            'core_region.is_active'
        )
            ->join('core_vertical', 'core_region.vertical_id', '=', 'core_vertical.id')
            ->join('core_business_type', 'core_region.business_type', '=', 'core_business_type.id')

            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Business Type',
            'Vertical Name',
            'Region Name',
            'Region Code',
            'Numeric Code',
            'Effective Date',
            'Status',
        ];
    }

    public function map($region): array
    {
        return [
            $region->id,
            $region->business_type,
            $region->vertical_name,
            $region->region_name,
            $region->region_code,
            $region->numeric_code,
            $region->effective_date ? \Carbon\Carbon::parse($region->effective_date)->format('Y-m-d') : '',
            $region->is_active ? 'Active' : 'Inactive',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header row styling (red background with white text)
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => Color::COLOR_WHITE]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FF000080']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
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
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // Status column styling (color based on active/inactive)
        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            $statusCell = 'H' . $row;
            $statusValue = $sheet->getCell($statusCell)->getValue();

            $color = ($statusValue == 'Active') ? '00FF00' : 'FF0000';

            $sheet->getStyle($statusCell)->applyFromArray([
                'font' => [
                    'color' => ['rgb' => $color],
                    'bold' => true
                ]
            ]);
        }

        // Auto-size columns
        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Freeze header row
        $sheet->freezePane('A2');
    }
}
