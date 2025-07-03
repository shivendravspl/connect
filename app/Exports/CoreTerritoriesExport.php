<?php

namespace App\Exports;

use App\Models\CoreTerritory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class CoreTerritoriesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Territories List';
    }

    public function collection()
    {
        return CoreTerritory::select(
            'core_territory.id',
            'core_territory.territory_name',
            'core_territory.territory_code',
            'core_territory.numeric_code',
            'core_territory.effective_date',
            'core_territory.is_active',
            'core_business_type.business_type'
        )->join('core_business_type', 'core_territory.business_type', '=', 'core_business_type.id')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Territory Name',
            'Territory Code',
            'Numeric Code',
            'Effective Date',
            'Status',
            'Business Type',
        ];
    }

    public function map($territory): array
    {
        return [
            $territory->id,
            $territory->territory_name,
            $territory->territory_code,
            $territory->numeric_code,
            $territory->effective_date ? \Carbon\Carbon::parse($territory->effective_date)->format('Y-m-d') : '',
            $territory->is_active ? 'Active' : 'Inactive',
            $territory->business_type,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header row styling (red background with white text)
        $sheet->getStyle('A1:G1')->applyFromArray([
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
            $statusCell = 'F' . $row;
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
        foreach (range('A', 'G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Freeze header row
        $sheet->freezePane('A2');
    }
}
