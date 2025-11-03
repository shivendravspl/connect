<?php

namespace App\Exports;

use App\Models\RequiredDocument;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DocumentChecklistExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $entityType;
    protected $entityTypeName;

    public function __construct($entityType)
    {
        $this->entityType = $entityType;
        $this->entityTypeName = RequiredDocument::ENTITY_TYPES[$entityType] ?? $entityType;
    }

    public function collection()
    {
        return RequiredDocument::forEntityType($this->entityType)
            ->orderBy('sort_order')
            ->orderBy('category')
            ->orderBy('sub_category')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Category',
            'Sub Category',
            'Document Name',
            'Checkpoints',
            'Applicability/Justification',
            'Applicability'
        ];
    }

    public function map($document): array
    {
        return [
            $document->category,
            $document->sub_category ?? '',
            $document->document_name,
            $document->checkpoints ?? '',
            $document->applicability_justification ?? '',
            $document->applicability
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getColumnDimension('E')->setWidth(40);
        $sheet->getColumnDimension('F')->setWidth(15);

        // Style the header row
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2C3E50'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Add title row
        $sheet->insertNewRowBefore(1, 2);
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'Document Checklist - ' . $this->entityTypeName);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Style for mandatory documents
        $row = 3; // Start after header and title
        foreach ($this->collection() as $document) {
            $styleArray = [];
            
            if ($document->applicability === 'Mandatory') {
                $styleArray['fill'] = [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFE6E6'], // Light red
                ];
            } elseif ($document->applicability === 'Optional') {
                $styleArray['fill'] = [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFF9E6'], // Light yellow
                ];
            }
            
            if (!empty($styleArray)) {
                $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($styleArray);
            }
            $row++;
        }

        // Auto-wrap text for better readability
        $sheet->getStyle('A3:F' . ($row - 1))->getAlignment()->setWrapText(true);

        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            3 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Document Checklist';
    }
}