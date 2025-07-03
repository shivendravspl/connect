<?php

namespace App\Exports;

use App\Models\CoreZone;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class CoreZonesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Zones List';
    }

    public function collection()
    {
        return CoreZone::select(
            'core_zone.id',
            'core_zone.zone_name',
            'core_zone.zone_code',
            'core_zone.numeric_code',
            'core_zone.effective_date',
            'core_zone.is_active',
            'core_vertical.vertical_name',
            'core_business_type.business_type'
        )
            ->join('core_vertical', 'core_zone.vertical_id', '=', 'core_vertical.id')
            ->join('core_business_type', 'core_zone.business_type', '=', 'core_business_type.id')

            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Zone Name',
            'Zone Code',
            'Numeric Code',
            'Effective Date',
            'Status',
            'Vertical Name',
            'Business Type',
        ];
    }

    public function map($zone): array
    {
        return [
            $zone->id,
            $zone->zone_name,
            $zone->zone_code,
            $zone->numeric_code,
            $zone->effective_date ? \Carbon\Carbon::parse($zone->effective_date)->format('Y-m-d') : '',
            $zone->is_active ? 'Active' : 'Inactive',
            $zone->vertical_name,
            $zone->business_type,
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
        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Freeze header row
        $sheet->freezePane('A2');
    }
}
