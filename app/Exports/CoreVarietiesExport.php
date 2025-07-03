<?php

namespace App\Exports;

use App\Models\CoreVariety;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class CoreVarietiesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Varieties List';
    }

    public function collection()
    {
        return CoreVariety::select(
            'core_variety.id',
            'core_crop.crop_name',
            'core_variety.variety_name',
            'core_variety.variety_code',
            'core_variety.numeric_code',
            'core_category.category_name',
            'core_variety.is_active',
        )
            ->join('core_crop', 'core_variety.crop_id', '=', 'core_crop.id')
            ->join('core_category', 'core_variety.category_id', '=', 'core_category.id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Crop Name',
            'Variety Name',
            'Variety Code',
            'Numeric Code',
            'Category Name',
            'Status',
        ];
    }

    public function map($variety): array
    {
        return [
            $variety->id,
            $variety->crop_name,
            $variety->variety_name,
            $variety->variety_code,
            $variety->numeric_code,
            $variety->category_name,
            $variety->is_active ? 'Active' : 'Inactive',
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
            $statusCell = 'G' . $row;
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
