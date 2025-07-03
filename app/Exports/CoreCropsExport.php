<?php

namespace App\Exports;

use App\Models\CoreCrop;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class CoreCropsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Crops List';
    }

    public function collection()
    {
        return CoreCrop::select(
            'core_crop.id',
            'core_vertical.vertical_name',
            'core_crop.crop_name',
            'core_crop.crop_code',
            'core_crop.numeric_code',
            'core_crop.effective_date',
            'core_crop.is_active'
        )
            ->join('core_vertical', 'core_crop.vertical_id', '=', 'core_vertical.id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Vertical Name',
            'Crop Name',
            'Crop Code',
            'Numeric Code',
            'Effective Date',
            'Status',
        ];
    }

    public function map($crop): array
    {
        return [
            $crop->id,
            $crop->vertical_name,
            $crop->crop_name,
            $crop->crop_code,
            $crop->numeric_code,
            $crop->effective_date ? \Carbon\Carbon::parse($crop->effective_date)->format('Y-m-d') : '',
            $crop->is_active ? 'Active' : 'Inactive',
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
