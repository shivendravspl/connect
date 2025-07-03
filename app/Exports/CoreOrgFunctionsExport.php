<?php

namespace App\Exports;

use App\Models\CoreOrgFunction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CoreOrgFunctionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function title(): string
    {
        return 'Organization Function List';
    }

    public function collection()
    {
        return CoreOrgFunction::select('id', 'function_name', 'function_code', 'effective_date', 'is_active')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Function Name',
            'Function Code',
            'Effective Date',
            'Status'
        ];
    }

    public function map($function): array
    {
        return [
            $function->id,
            $function->function_name,
            $function->function_code,
            $function->effective_date ? \Carbon\Carbon::parse($function->effective_date)->format('Y-m-d') : '',
            $function->is_active ? 'Active' : 'Deactive'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:E1')->applyFromArray([
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

        // Auto-size columns
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}
