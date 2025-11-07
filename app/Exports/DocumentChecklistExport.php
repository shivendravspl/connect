<?php

namespace App\Exports;

use App\Models\RequiredDocument;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class DocumentChecklistExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
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
        // Add category notes for special categories
        $categoryWithNote = $document->category;
        if ($document->category == 'Address Proof') {
            $categoryWithNote = $document->category . ' (Conditional)';
        } elseif ($document->category == 'Credit Worthiness') {
            $categoryWithNote = $document->category . ' (Any One Required)';
        } elseif ($document->category == 'Declarations') {
            $categoryWithNote = $document->category . ' (In Agreement)';
        }

        return [
            $categoryWithNote,
            $document->sub_category ?? '',
            $document->document_name,
            $document->checkpoints ?? '',
            $document->applicability_justification ?? '',
            $document->applicability
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Get all data
        $documents = $this->collection();
        $totalRows = $documents->count() + 4; // +4 for title, header, and extra rows
        
        // Add title row
        $sheet->insertNewRowBefore(1, 3);
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'DOCUMENT CHECKLIST');
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'Entity Type: ' . $this->entityTypeName);
        $sheet->mergeCells('A3:F3');
        $sheet->setCellValue('A3', 'Generated on: ' . date('F j, Y'));
        
        // Style title rows
        $sheet->getStyle('A1:F3')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '333333'],
                ],
            ],
        ]);
        
        $sheet->getStyle('A1')->getFont()->setSize(16);
        $sheet->getStyle('A2:A3')->getFont()->setSize(12);
        
        // Style header row (row 4)
        $sheet->getStyle('A4:F4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2C3E50'], // Dark blue-gray like PDF header
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Apply borders to all data cells
        $sheet->getStyle("A4:F{$totalRows}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'DDDDDD'],
                ],
            ],
        ]);

        // Style data rows with category grouping and colors
        $currentRow = 5;
        $currentCategory = null;
        
        foreach ($documents as $index => $document) {
            $rowStyle = [];
            
            // Category grouping - light blue background for category changes
            if ($currentCategory !== $document->category) {
                $currentCategory = $document->category;
                $rowStyle['fill'] = [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F4FD'], // Light blue like PDF category headers
                ];
                $rowStyle['font'] = [
                    'bold' => true,
                ];
                
                // Apply special category colors
                $categoryColorStyle = [];
                if ($document->category == 'Address Proof') {
                    $categoryColorStyle['fill'] = [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E6F7FF'], // Light blue for Conditional
                    ];
                } elseif ($document->category == 'Credit Worthiness') {
                    $categoryColorStyle['fill'] = [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFF9E6'], // Light yellow for Any One Required
                    ];
                } elseif ($document->category == 'Declarations') {
                    $categoryColorStyle['fill'] = [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8F9FA'], // Light gray for In Agreement
                    ];
                }
                
                if (!empty($categoryColorStyle)) {
                    $sheet->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray($categoryColorStyle);
                }
            }
            
            // Applicability colors - matching PDF badge colors
            $applicabilityStyle = [];
            if ($document->applicability === 'Mandatory') {
                $applicabilityStyle['fill'] = [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFE6E6'], // Light red
                ];
                $applicabilityStyle['font'] = [
                    'color' => ['rgb' => 'DC3545'], // Red text
                    'bold' => true,
                ];
            } elseif ($document->applicability === 'Optional') {
                $applicabilityStyle['fill'] = [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFF9E6'], // Light yellow
                ];
                $applicabilityStyle['font'] = [
                    'color' => ['rgb' => '856404'], // Dark yellow text
                    'bold' => true,
                ];
            } elseif ($document->applicability === 'Conditional' || $document->applicability === 'On Applicability') {
                $applicabilityStyle['fill'] = [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E6F7FF'], // Light blue
                ];
                $applicabilityStyle['font'] = [
                    'color' => ['rgb' => '0C5460'], // Dark blue text
                    'bold' => true,
                ];
            }
            
            // Apply category style to entire row
            if (!empty($rowStyle)) {
                $sheet->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray($rowStyle);
            }
            
            // Apply applicability style only to the applicability column
            if (!empty($applicabilityStyle)) {
                $sheet->getStyle("F{$currentRow}")->applyFromArray($applicabilityStyle);
            }
            
            $currentRow++;
        }

        // Auto-wrap text for better readability
        $sheet->getStyle("A5:F{$totalRows}")->getAlignment()->setWrapText(true);
        
        // Set row heights for better visibility
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(4)->setRowHeight(25);
        
        for ($i = 5; $i <= $totalRows; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(20);
        }

        // Add footer
        $footerRow = $totalRows + 1;
        $sheet->mergeCells("A{$footerRow}:F{$footerRow}");
        $sheet->setCellValue("A{$footerRow}", 'Document Checklist generated on ' . date('F j, Y \a\t g:i A'));
        $sheet->getStyle("A{$footerRow}")->applyFromArray([
            'font' => [
                'italic' => true,
                'color' => ['rgb' => '666666'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'DDDDDD'],
                ],
            ],
        ]);

        return [
            // Title styles
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => '2C3E50'],
                ],
            ],
            2 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => '666666'],
                ],
            ],
            3 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => '666666'],
                ],
            ],
            // Header row
            4 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2C3E50'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        // Clean title for Excel sheet name (max 31 characters, no special chars)
        $title = 'Checklist_' . substr(str_replace(' ', '_', $this->entityTypeName), 0, 20);
        return preg_replace('/[\/:*?"<>|]/', '', $title);
    }
}