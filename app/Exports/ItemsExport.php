<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
     * Retrieve the collection of items with related item group data
     */
    public function collection()
    {
        return Item::with('itemGroup')->get();
    }

    /**
     * Define the headings for the Excel sheet
     */
    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Group',
            'UOM',
            'Status',
            'Created At'
        ];
    }

    /**
     * Map the data for each item
     */
    public function map($item): array
    {
        return [
            $item->code,
            $item->name,
            $item->itemGroup->name ?? 'N/A',
            $item->uom,
            $item->is_active ? 'Active' : 'Inactive',
            $item->created_at->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Get the highest row and column for applying borders
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Apply styles to the header row
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10, // Compact font size
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4CAF50'], // Green header (same as CategoriesExport)
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Apply borders and compact font size to all data cells
        $sheet->getStyle("A2:F$highestRow")->applyFromArray([
            'font' => [
                'size' => 9, // Smaller font for data
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Set fixed column widths (in Excel units, compact)
        $columnWidths = [
            'A' => 12, // Code
            'B' => 20, // Name
            'C' => 15, // Group
            'D' => 10, // UOM
            'E' => 10, // Status
            'F' => 15, // Created At
        ];

        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        // Disable auto-sizing to enforce fixed widths
        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(false);
        }

        return [];
    }
}