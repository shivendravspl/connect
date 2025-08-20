<?php

namespace App\Exports;

use App\Models\ItemCategory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CategoriesExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
     * Retrieve the collection of categories with related item data
     */
    public function collection()
    {
        return ItemCategory::with('item')->get();
    }

    /**
     * Define the headings for the Excel sheet
     */
    public function headings(): array
    {
        return [
            'ID',
            'Item ID',
            'Item Name',
            'Category Name',
            'Description',
            'Status',
            'Created At'
        ];
    }

    /**
     * Map the data for each category
     */
    public function map($category): array
    {
        return [
            $category->id,
            $category->item_id,
            $category->item->name ?? 'N/A',
            $category->name,
            $category->description ?? '',
            $category->is_active ? 'Active' : 'Inactive',
            $category->created_at->format('Y-m-d H:i:s')
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
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10, // Compact font size
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4CAF50'], // Green header background
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
        $sheet->getStyle("A2:G$highestRow")->applyFromArray([
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
            'A' => 8,  // ID
            'B' => 10, // Item ID
            'C' => 20, // Item Name
            'D' => 20, // Category Name
            'E' => 30, // Description
            'F' => 10, // Status
            'G' => 15, // Created At
        ];

        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        // Optional: Auto-size columns if content exceeds width (fallback)
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(false);
        }

        return [];
    }
}