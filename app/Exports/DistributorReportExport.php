<?php

namespace App\Exports;

use App\Models\Onboarding;
use App\Http\Controllers\DistributorReportController;
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
        if ($this->reportType === 'tat') {
            return [
                'Sr No',
                'App Date',
                'App Code',
                'Initiated By',
                'Establishment Name',
                'Authorized Person',
                'Crop Vertical',
                'Application Status',
                'RBM Approval Date',
                'ZBM Approval Date',
                'GM Approval Date',
                'Revert Date',
                'Reply Date',
                'Dispatch Date',
                'Physical Receive Date',
                'MIS Verification Date',
                'Final Creation Date',
                'RBM TAT',
                'ZBM TAT',
                'GM TAT',
                'MIS Doc Verification',
                'Reply/Revert TAT',
                'Dispatch/Physical TAT',
                'MIS TAT',
                'Physical Doc Pendency',
                'Deposit TAT',
                'Distributor Finalisation',
                'Total TAT',
                'TAT Status',
                'Pending Level',
                'Days Pending',
                'Remarks/Comments'
            ];
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
        // Use the same TAT calculation method from controller
        $tatData = DistributorReportController::calculateTATData($distributor);

        return [
            '', // Sr No will be calculated in registerEvents
            $distributor->created_at->format('d-m-Y'),
            $distributor->application_code ?? 'N/A',
            $distributor->created_by_name ?? 'N/A',
            $distributor->establishment_name ?? 'N/A',
            $this->getAuthorizedPersonName($distributor),
            $distributor->vertical_name ?? 'N/A',
            ucfirst(str_replace('_', ' ', $distributor->status)),
            // Approval Dates
            $tatData['rbm_approval_date'] ? $tatData['rbm_approval_date']->format('d-m-Y') : 'Pending',
            $tatData['zbm_approval_date'] ? $tatData['zbm_approval_date']->format('d-m-Y') : 'Pending',
            $tatData['gm_approval_date'] ? $tatData['gm_approval_date']->format('d-m-Y') : 'Pending',
            $tatData['revert_date'] ? $tatData['revert_date']->format('d-m-Y') : 'N/A',
            $tatData['reply_date'] ? $tatData['reply_date']->format('d-m-Y') : 'N/A',
            $tatData['dispatch_date'] ? \Carbon\Carbon::parse($tatData['dispatch_date'])->format('d-m-Y') : 'N/A',
            $tatData['physical_receive_date'] ? \Carbon\Carbon::parse($tatData['physical_receive_date'])->format('d-m-Y') : 'N/A',
            $tatData['mis_verification_date'] ? $tatData['mis_verification_date']->format('d-m-Y') : 'Pending',
            $tatData['final_creation_date'] ? $tatData['final_creation_date']->format('d-m-Y') : 'Pending',
            // TAT Values - format exactly like web view
            $this->formatTatValue($tatData['rbm_tat']),
            $this->formatTatValue($tatData['zbm_tat']),
            $this->formatTatValue($tatData['gm_tat']),
            $this->formatTatValue($tatData['mis_doc_verification_tat']),
            $this->formatTatValue($tatData['revert_reply_tat'], true), // N/A for revert
            $this->formatTatValue($tatData['dispatch_tat']),
            $this->formatTatValue($tatData['mis_tat']),
            $this->formatTatValue($tatData['physical_pendency_tat'], true), // N/A for physical
            $this->formatTatValue($tatData['deposit_tat']),
            $this->formatTatValue($tatData['distributor_finalisation_tat']),
            $this->formatTatValue($tatData['total_tat']),
            // Status and Pending Info
            $tatData['tat_status'],
            $tatData['pending_level'],
            $this->formatDaysPending($tatData['days_pending']),
            '-' // Remarks/Comments
        ];
    }

    private function formatTatValue($value, $isNAType = false)
    {
        if ($value === null) {
            return $isNAType ? 'N/A' : 'Pending';
        }
        
        // Convert to integer and add "days" text
        return (int)$value . ' days';
    }

    private function formatDaysPending($days)
    {
        if ($days === null) {
            return '0 days';
        }
        
        // Convert to integer and add "days" text
        return (int)$days . ' days';
    }

    private function getAuthorizedPersonName($distributor)
    {
        // Use the same method as in the controller
        return $distributor->getAuthorizedOrEntityName() ?? 'N/A';
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Only apply TAT-specific styling for TAT reports
        if ($this->reportType === 'tat') {
            // Set all rows to have borders for TAT report
            $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ]);

            $sheet->getRowDimension(1)->setRowHeight(25);

            return [
                // Header row
                1 => [
                    'font' => ['bold' => true, 'size' => 11],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFE6E6E6'],
                    ],
                ],
                // Data rows
                '2:' . $highestRow => [
                    'alignment' => [
                        'wrapText' => true,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ],
                // Text alignment for TAT report
                'A:A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Sr No
                'B:B' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // App Date
                'C:C' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],   // App Code
                'D:D' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],   // Initiated By
                'E:F' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]],   // Establishment & Authorized Person
                'G:G' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Crop Vertical
                'H:H' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Application Status
                'I:Q' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Date columns
                'R:AA' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // TAT columns
                'AB:AD' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Status columns
                'AE:AE' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]], // Remarks
            ];
        }

        // Default styling for other report types
        $sheet->getRowDimension(1)->setRowHeight(25);

        return [
            // Header row for non-TAT reports
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
            ],
            // Data rows for non-TAT reports
            '2:' . $highestRow => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Freeze header row for all report types
                $sheet->freezePane('A2');

                if ($this->reportType === 'tat') {
                    // TAT report specific formatting
                    $this->applyTatReportFormatting($sheet, $highestRow, $highestColumn);
                } else {
                    // Other report types formatting
                    $this->applyDefaultReportFormatting($sheet, $highestRow, $highestColumn);
                }

                // Add filter information if available (for all report types)
                if (!empty($this->filters)) {
                    $filterInfo = $this->getFilterInfo();
                    if ($filterInfo) {
                        $sheet->insertNewRowBefore(1, 2);
                        $sheet->setCellValue('A1', 'Report Filters: ' . $filterInfo);
                        $sheet->mergeCells("A1:{$highestColumn}1");
                        $sheet->getStyle('A1')->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF666666']],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFF0F0F0'],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_LEFT,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                        ]);
                        
                        // Update highest row after insertion
                        $highestRow = $sheet->getHighestRow();
                    }
                }

                // Auto-size all columns for all report types
                foreach (range('A', $highestColumn) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    private function applyTatReportFormatting($sheet, $highestRow, $highestColumn)
    {
        // Add serial numbers for TAT report
        for ($row = 2; $row <= $highestRow; $row++) {
            $sheet->setCellValue('A' . $row, $row - 1);
        }

        // Add alternating row colors for TAT report
        for ($row = 2; $row <= $highestRow; $row += 2) {
            $sheet->getStyle("A$row:{$highestColumn}$row")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF8F9FA'],
                ],
            ]);
        }

        // Set specific column widths for TAT report
        $sheet->getColumnDimension('A')->setWidth(8);  // Sr No
        $sheet->getColumnDimension('B')->setWidth(12); // App Date
        $sheet->getColumnDimension('C')->setWidth(20); // App Code
        $sheet->getColumnDimension('D')->setWidth(20); // Initiated By
        $sheet->getColumnDimension('E')->setWidth(25); // Establishment Name
        $sheet->getColumnDimension('F')->setWidth(20); // Authorized Person
        $sheet->getColumnDimension('G')->setWidth(15); // Crop Vertical
        
        // TAT columns
        for ($col = 18; $col <= 31; $col++) { // R to AE
            $columnLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($columnLetter)->setWidth(12);
        }
    }

    private function applyDefaultReportFormatting($sheet, $highestRow, $highestColumn)
    {
        // Add alternating row colors for other reports
        for ($row = 2; $row <= $highestRow; $row += 2) {
            $sheet->getStyle("A$row:{$highestColumn}$row")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFF8F9FA'],
                ],
            ]);
        }
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