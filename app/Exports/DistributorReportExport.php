<?php

namespace App\Exports;

use App\Models\Onboarding;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class DistributorReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, ShouldAutoSize
{
    protected $distributors;
    protected $reportType;
    protected $filters;

    public function __construct($distributors, $reportType = 'summary', $filters = [])
    {
        $this->distributors = $distributors;
        $this->reportType = $reportType;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->distributors;
    }

    public function headings(): array
    {
        $baseHeadings = [
            'Application Code',
            'Focus Code',
            'Establishment Name',
            'Authorized Person',
            'Vertical',
            'App Date',
            'Appointment Date',
            'Status'
        ];

        if ($this->reportType === 'tat') {
            return array_merge($baseHeadings, [
                'RBM TAT',
                'GM TAT', 
                'SE TAT',
                'MIS TAT',
                'Physical TAT',
                'Total TAT',
                'TAT Status'
            ]);
        }

        // Default headings for other report types
        return [
            'Application Code',
            'Focus Code',
            'Establishment Name',
            'Authorized Person',
            'Vertical',
            'Business Unit',
            'Zone', 
            'Region',
            'Territory',
            'Date of Appointment',
            'Status',
            'Created By',
            'Created Date'
        ];
    }

    public function map($distributor): array
    {
        if ($this->reportType === 'tat') {
            return $this->mapTatData($distributor);
        }

        // Default mapping for other report types
        return [
            $distributor->application_code ?? 'N/A',
            $distributor->distributor_code ?? 'N/A',
            $distributor->establishment_name ?? 'N/A',
            $this->getAuthorizedPersonName($distributor),
            $distributor->vertical?->vertical_name ?? 'N/A',
            $distributor->buDetail?->bu_name ?? 'N/A',
            $distributor->zone_name ?? 'N/A',
            $distributor->region_name ?? 'N/A', 
            $distributor->territory_name ?? 'N/A',
            $distributor->date_of_appointment ? \Carbon\Carbon::parse($distributor->date_of_appointment)->format('d-m-Y') : 'N/A',
            ucfirst(str_replace('_', ' ', $distributor->status)),
            $distributor->created_by_name ?? 'N/A',
            $distributor->created_at?->format('d-m-Y H:i') ?? 'N/A'
        ];
    }

    private function mapTatData($distributor): array
    {
        // Calculate TAT values using the same logic as the view
        $rbmTat = $this->calculateRbmTat($distributor);
        $gmTat = $this->calculateGmTat($distributor);
        $seTat = $this->calculateSeTat($distributor);
        $misTat = $this->calculateMisTat($distributor);
        $physicalTat = $this->calculatePhysicalTat($distributor);
        $totalTat = $this->calculateTotalTat($distributor);
        $tatStatus = $this->getTatStatus($totalTat);

        return [
            $distributor->application_code ?? 'N/A',
            $distributor->distributor_code ?? 'N/A',
            $distributor->establishment_name ?? 'N/A',
            $this->getAuthorizedPersonName($distributor),
            $distributor->vertical?->vertical_name ?? 'N/A',
            $distributor->created_at?->format('d-m-Y') ?? 'N/A',
            $distributor->date_of_appointment ? \Carbon\Carbon::parse($distributor->date_of_appointment)->format('d-m-Y') : 'N/A',
            ucfirst(str_replace('_', ' ', $distributor->status)),
            $rbmTat,
            $gmTat,
            $seTat,
            $misTat,
            $physicalTat,
            $totalTat,
            $tatStatus
        ];
    }

    private function calculateRbmTat($distributor): string
    {
        $rbmLog = $distributor->approvalLogs->where('role', 'Regional Business Manager')->first();
        $rbmTat = $rbmLog ? ceil($distributor->created_at->diffInDays($rbmLog->created_at)) : 'Pending';
        return is_numeric($rbmTat) ? $rbmTat . ($rbmTat != 1 ? '' : '') : $rbmTat;
    }

    private function calculateGmTat($distributor): string
    {
        $gmLog = $distributor->approvalLogs->where('role', 'General Manager')->first();
        if ($gmLog) {
            $prev = $distributor->approvalLogs->where('created_at', '<', $gmLog->created_at)->sortByDesc('created_at')->first();
            $gmTat = $prev ? ceil($prev->created_at->diffInDays($gmLog->created_at)) : 'N/A';
        } else {
            $gmTat = 'Pending';
        }
        return is_numeric($gmTat) ? $gmTat . ($gmTat != 1 ? '' : '') : $gmTat;
    }

    private function calculateSeTat($distributor): string
    {
        $seLog = $distributor->approvalLogs->where('role', 'Senior Executive')->first();
        if ($seLog) {
            $prev = $distributor->approvalLogs->where('created_at', '<', $seLog->created_at)->sortByDesc('created_at')->first();
            $seTat = $prev ? ceil($prev->created_at->diffInDays($seLog->created_at)) : 'N/A';
        } else {
            $seTat = 'Pending';
        }
        return is_numeric($seTat) ? $seTat . ($seTat != 1 ? '' : '') : $seTat;
    }

    private function calculateMisTat($distributor): string
    {
        if ($distributor->mis_verified_at) {
            $finalApp = $distributor->approvalLogs->where('action', 'approved')->last();
            $misTat = $finalApp ? ceil($finalApp->created_at->diffInDays($distributor->mis_verified_at)) : 'N/A';
            return is_numeric($misTat) ? $misTat . ($misTat != 1 ? '' : '') : $misTat;
        }
        return 'Pending';
    }

    private function calculatePhysicalTat($distributor): string
    {
        $dispatch = $distributor->physicalDispatch;
        if ($dispatch && $dispatch->dispatch_date && $distributor->mis_verified_at) {
            $physTat = ceil($distributor->mis_verified_at->diffInDays($dispatch->dispatch_date));
            return $physTat . ($physTat != 1 ? '' : '');
        }
        return 'Pending';
    }

    private function calculateTotalTat($distributor): string
    {
        $endDate = null;
        if (in_array($distributor->status, ['completed', 'distributorship_created'])) {
            $endDate = $distributor->physicalDispatch?->dispatch_date ?? $distributor->updated_at;
        } elseif ($distributor->mis_verified_at) {
            $endDate = $distributor->mis_verified_at;
        } elseif ($distributor->approvalLogs->isNotEmpty()) {
            $endDate = $distributor->approvalLogs->last()->created_at;
        } else {
            $endDate = now();
        }
        
        $totalTatDays = ceil($distributor->created_at->diffInDays($endDate));
        return $totalTatDays . ($totalTatDays != 1 ? '' : '');
    }

    private function getTatStatus($totalTat): string
    {
        // Extract numeric value from string (e.g., "26" from "26")
        $tatDays = is_numeric($totalTat) ? $totalTat : (int) preg_replace('/[^0-9]/', '', $totalTat);
        
        if ($tatDays <= 7) {
            return 'On Time';
        } elseif ($tatDays <= 14) {
            return 'Delayed';
        } else {
            return 'Overdue';
        }
    }

    private function getAuthorizedPersonName($distributor)
    {
        // Try to get from authorized persons relationship
        if ($distributor->authorizedPersons && $distributor->authorizedPersons->isNotEmpty()) {
            $primaryPerson = $distributor->authorizedPersons->first();
            return $primaryPerson->name ?? 'N/A';
        }
        
        // Fallback to establishment name
        return $distributor->establishment_name ?? 'N/A';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getRowDimension(1)->setRowHeight(25);

        return [
            // Header row
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE6E6E6'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ],
            // Data rows
            '2:' . $sheet->getHighestRow() => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFCCCCCC'],
                    ],
                ],
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Text alignment
            'A:D' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],
            'E:E' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'F:G' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'H:H' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'I:O' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Freeze header row
                $sheet->freezePane('A2');

                // Add alternating row colors
                $highestRow = $sheet->getHighestRow();
                for ($row = 2; $row <= $highestRow; $row += 2) {
                    $sheet->getStyle("A$row:" . $sheet->getHighestColumn() . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFF8F9FA'],
                        ],
                    ]);
                }

                // Add filter information if available
                if (!empty($this->filters)) {
                    $filterInfo = $this->getFilterInfo();
                    if ($filterInfo) {
                        $sheet->insertNewRowBefore(1, 2);
                        $sheet->setCellValue('A1', 'Report Filters: ' . $filterInfo);
                        $sheet->mergeCells('A1:' . $sheet->getHighestColumn() . '1');
                        $sheet->getStyle('A1')->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF666666']],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF0F0F0'],
                            ],
                        ]);
                    }
                }
            },
        ];
    }

    private function getFilterInfo()
    {
        $filters = [];
        
        if (!empty($this->filters['search'])) {
            $filters[] = 'Search: ' . $this->filters['search'];
        }
        if (!empty($this->filters['bu']) && $this->filters['bu'] !== 'All') {
            $filters[] = 'BU: ' . $this->filters['bu'];
        }
        if (!empty($this->filters['zone']) && $this->filters['zone'] !== 'All') {
            $filters[] = 'Zone: ' . $this->filters['zone'];
        }
        if (!empty($this->filters['region']) && $this->filters['region'] !== 'All') {
            $filters[] = 'Region: ' . $this->filters['region'];
        }
        if (!empty($this->filters['territory']) && $this->filters['territory'] !== 'All') {
            $filters[] = 'Territory: ' . $this->filters['territory'];
        }
        if (!empty($this->filters['status']) && $this->filters['status'] !== 'All') {
            $filters[] = 'Status: ' . $this->filters['status'];
        }
        if (!empty($this->filters['date_from'])) {
            $filters[] = 'From: ' . $this->filters['date_from'];
        }
        if (!empty($this->filters['date_to'])) {
            $filters[] = 'To: ' . $this->filters['date_to'];
        }

        return implode(' | ', $filters);
    }
}