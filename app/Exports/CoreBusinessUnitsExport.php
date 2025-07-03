<?php

namespace App\Exports;

use App\Models\CoreBusinessUnit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class CoreBusinessUnitsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Business Units List';
    }

    public function collection()
    {
        return CoreBusinessUnit::select(
            'core_business_unit.id',
            'core_business_unit.business_unit_name',
            'core_business_unit.business_unit_code',
            'core_business_unit.numeric_code',
            'core_business_unit.effective_date',
            'core_business_unit.is_active',
            'core_vertical.vertical_name',
            //'core_business_type.name as business_type_name'
        )
        ->join('core_vertical', 'core_business_unit.vertical_id', '=', 'core_vertical.id')
        //->join('core_business_type', 'core_business_unit.business_type', '=', 'core_business_type.id')
        ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Business Unit Name',
            'Business Unit Code',
            'Numeric Code',
            'Effective Date',
            'Status',
            'Vertical Name',
            //'Business Type'
        ];
    }

    public function map($unit): array
    {
        return [
            $unit->id,
            $unit->business_unit_name,
            $unit->business_unit_code,
            $unit->numeric_code,
            $unit->effective_date ? \Carbon\Carbon::parse($unit->effective_date)->format('Y-m-d') : '',
            $unit->is_active ? 'Active' : 'Inactive',
            $unit->vertical_name,
            //$unit->business_type_name
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
        foreach (range('A', 'H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Freeze header row
        $sheet->freezePane('A2');
    }
}