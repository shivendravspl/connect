<?php

namespace App\Exports;

use App\Models\Onboarding;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class DistributorReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $distributors;
    protected $reportType;

    public function __construct($distributors, $reportType = 'summary')
    {
        $this->distributors = $distributors;
        $this->reportType = $reportType;
    }

    public function collection()
    {
        return $this->distributors;
    }

    public function headings(): array
    {
        switch ($this->reportType) {
            case 'approval':
                return [
                    'Application Code',
                    'Establishment Name',
                    'Authorized Person',
                    'Current Approval Level',
                    'Current Approver',
                    'Status',
                    'Last Updated',
                    'Remarks'
                ];

            case 'verification':
                return [
                    'Application Code',
                    'Establishment Name',
                    'Authorized Person',
                    'Document Verification Status',
                    'MIS Verified At',
                    'Physical Document Status',
                    'Verified By'
                ];

            case 'dispatch':
                return [
                    'Application Code',
                    'Establishment Name',
                    'Authorized Person',
                    'Dispatch Mode',
                    'Transport/Company Name',
                    'Driver/Person Name',
                    'Contact Number',
                    'Docket Number',
                    'Dispatch Date',
                    'Receive Date',
                    'Created By'
                ];

            case 'lifecycle':
                return [
                    'Application Code',
                    'Establishment Name',
                    'Authorized Person',
                    'Application Created',
                    'Approval Level 1 (RBM)',
                    'Approval Level 2 (GM)',
                    'Approval Level 3 (SE)',
                    'MIS Verification',
                    'Physical Docs Status',
                    'Agreement Status',
                    'Final Status'
                ];

            case 'pending':
                return [
                    'Application Code',
                    'Establishment Name',
                    'Authorized Person',
                    'Pending Step',
                    'Documents Pending',
                    'Created By',
                    'Created At'
                ];

            case 'rejected':
                return [
                    'Application Code',
                    'Establishment Name',
                    'Authorized Person',
                    'Rejected By',
                    'Rejection Reason',
                    'Rejection Date',
                    'Follow Up Date'
                ];
            case 'tat':
                return [
                    'Application Code',
                    'Establishment Name',
                    'Authorized Person',
                    'Vertical',
                    'Region',
                    'Application Date',
                    'Current Status',
                    'RBM Approval TAT (Days)',
                    'GM Approval TAT (Days)',
                    'SE Approval TAT (Days)',
                    'MIS Verification TAT (Days)',
                    'Physical Docs TAT (Days)',
                    'Total TAT (Days)',
                    'Status'
                ];

            default: // summary
                return [
                    'Application Code',
                    'Establishment Name',
                    'Authorized Person',
                    'Vertical',
                    'Region',
                    'Status',
                    'Created By',
                    'Date of Appointment'
                ];
        }
    }

    public function map($distributor): array
    {
        switch ($this->reportType) {
            case 'approval':
                return [
                    $distributor->application_code ?? 'N/A',
                    $distributor->entityDetails->establishment_name ?? 'N/A',
                    $distributor->getAuthorizedOrEntityName() ?? 'N/A',
                    ucfirst($distributor->approval_level),
                    $distributor->currentApprover?->name ?? 'N/A',
                    ucfirst(str_replace('_', ' ', $distributor->status)),
                    $distributor->updated_at?->format('d-m-Y H:i') ?? 'N/A',
                    $distributor->approvalLogs->last()?->remarks ?? 'N/A'
                ];
            case 'tat':
                return $this->mapTatData($distributor);

            case 'verification':
                return [
                    $distributor->application_code ?? 'N/A',
                    $distributor->entityDetails->establishment_name ?? 'N/A',
                    $distributor->getAuthorizedOrEntityName() ?? 'N/A',
                    $distributor->doc_verification_status ?? 'N/A',
                    $distributor->mis_verified_at?->format('d-m-Y') ?? 'N/A',
                    $distributor->physical_docs_status ?? 'N/A',
                    $distributor->documentVerifications->last()?->user?->name ?? 'N/A'
                ];

            case 'dispatch':
                $dispatch = $distributor->physicalDispatch;

                // Determine transport/company name based on mode
                $transportCompany = 'N/A';
                if ($dispatch?->mode == 'transport') {
                    $transportCompany = $dispatch?->transport_name ?? 'N/A';
                } elseif ($dispatch?->mode == 'courier') {
                    $transportCompany = $dispatch?->courier_company_name ?? 'N/A';
                }

                // Determine person name based on mode
                $personName = 'N/A';
                if ($dispatch?->mode == 'transport') {
                    $personName = $dispatch?->driver_name ?? 'N/A';
                } elseif ($dispatch?->mode == 'by_hand') {
                    $personName = $dispatch?->person_name ?? 'N/A';
                }

                // Determine contact number based on mode
                $contactNumber = 'N/A';
                if ($dispatch?->mode == 'transport') {
                    $contactNumber = $dispatch?->driver_contact ?? 'N/A';
                } elseif ($dispatch?->mode == 'by_hand') {
                    $contactNumber = $dispatch?->person_contact ?? 'N/A';
                }

                return [
                    $distributor->application_code ?? 'N/A',
                    $distributor->entityDetails->establishment_name ?? 'N/A',
                    $distributor->getAuthorizedOrEntityName() ?? 'N/A',
                    $dispatch?->mode ? ucwords(str_replace('_', ' ', $dispatch->mode)) : 'N/A',
                    $transportCompany,
                    $personName,
                    $contactNumber,
                    $dispatch?->docket_number ?? 'N/A',
                    $dispatch?->dispatch_date?->format('d-m-Y') ?? 'N/A',
                    $dispatch?->receive_date?->format('d-m-Y') ?? 'N/A',
                    $dispatch?->createdBy?->emp_name ?? 'N/A'
                ];

            case 'lifecycle':
                // Get approval logs by role
                $level1 = $distributor->approvalLogs->where('role', 'Regional Business Manager')->last();
                $level2 = $distributor->approvalLogs->where('role', 'General Manager')->last();
                $level3 = $distributor->approvalLogs->where('role', 'Senior Executive')->last();

                return [
                    $distributor->application_code ?? 'N/A',
                    $distributor->entityDetails->establishment_name ?? 'N/A',
                    $distributor->getAuthorizedOrEntityName() ?? 'N/A',
                    $distributor->created_at?->format('d-m-Y') ?? 'N/A',
                    $level1 ? ucfirst($level1->action) . ' (' . $level1->created_at?->format('d-m-Y') . ')' : 'Pending',
                    $level2 ? ucfirst($level2->action) . ' (' . $level2->created_at?->format('d-m-Y') . ')' : 'Pending',
                    $level3 ? ucfirst(str_replace('_', ' ', $level3->action)) . ' (' . $level3->created_at?->format('d-m-Y') . ')' : 'Pending',
                    $distributor->mis_verified_at?->format('d-m-Y') ?? 'N/A',
                    $distributor->physical_docs_status ?? 'N/A',
                    $distributor->agreement_status ?? 'N/A',
                    $distributor->final_status ?? 'N/A'
                ];


            case 'pending':
                return [
                    $distributor->application_code ?? 'N/A',
                    $distributor->entityDetails->establishment_name ?? 'N/A',
                    $distributor->getAuthorizedOrEntityName() ?? 'N/A',
                    $distributor->current_progress_step ?? 'N/A',
                    $distributor->doc_verification_status ?? 'N/A',
                    $distributor->createdBy?->emp_name ?? 'N/A',
                    $distributor->created_at?->format('d-m-Y') ?? 'N/A'
                ];

            case 'rejected':
                $lastRejection = $distributor->approvalLogs->where('action', 'rejected')->last();
                return [
                    $distributor->application_code ?? 'N/A',
                    $distributor->entityDetails->establishment_name ?? 'N/A',
                    $distributor->getAuthorizedOrEntityName() ?? 'N/A',
                    $lastRejection?->user?->name ?? 'N/A',
                    $lastRejection?->remarks ?? 'N/A',
                    $lastRejection?->created_at?->format('d-m-Y') ?? 'N/A',
                    $lastRejection?->follow_up_date?->format('d-m-Y') ?? 'N/A'
                ];


            default: // summary
                return [
                    $distributor->application_code ?? 'N/A',
                    $distributor->entityDetails->establishment_name ?? 'N/A',
                    $distributor->getAuthorizedOrEntityName() ?? 'N/A',
                    $distributor->vertical?->vertical_name ?? 'N/A',
                    $distributor->regionDetail?->region_name ?? 'N/A',
                    ucfirst(str_replace('_', ' ', $distributor->status)),
                    $distributor->createdBy?->emp_name ?? 'N/A',
                    $distributor->date_of_appointment?->format('d-m-Y') ?? 'N/A'
                ];
        }
    }

    public function columnWidths(): array
    {
        // Dynamic widths based on report type (adjust as needed for content length)
        $baseWidths = [10, 25, 20, 15, 15, 12, 15, 30]; // Default for longest (e.g., remarks)

        switch ($this->reportType) {
            case 'approval':
            case 'verification':
            case 'dispatch':
            case 'rejected':
                return array_combine(range('A', chr(64 + count($baseWidths))), $baseWidths);

            case 'lifecycle':
            case 'pending':
            case 'summary':
                // Shorter for some columns
                return array_combine(range('A', chr(64 + count($baseWidths) - 1)), array_slice($baseWidths, 0, -1));

            default:
                return [];
        }
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getRowDimension(1)->setRowHeight(20); // Increase header height

        return [
            // Header row: Bold, centered, gray background
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
            // All data rows: Thin borders, wrap text for long content
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
            // Specific columns: Left align for text, center for status/dates
            // Assuming common columns; adjust indices per type if needed
            'A:A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]], // Codes
            'B:B' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]], // Names
            'C:C' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]], // Persons
            'F:F' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Status
            'G:G' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Dates
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Auto-size all columns (handles >26 columns safely)
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
                for ($col = 1; $col <= $highestColumnIndex; ++$col) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }

                // Freeze first row for better scrolling
                $sheet->freezePane('A2');

                // Add a subtle alternating row color for readability
                $highestRow = $sheet->getHighestRow();
                for ($row = 2; $row <= $highestRow; $row += 2) {
                    $sheet->getStyle("A$row:" . $sheet->getHighestColumn() . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFF8F9FA'], // Very light gray
                        ],
                    ]);
                }
            },
        ];
    }


    private function mapTatData($distributor): array
    {
        // Calculate TAT for each stage
        $rbmTat = $this->calculateApprovalTat($distributor, 'Regional Business Manager');
        $gmTat = $this->calculateApprovalTat($distributor, 'General Manager');
        $seTat = $this->calculateApprovalTat($distributor, 'Senior Executive');
        $misTat = $this->calculateMisTat($distributor);
        $physicalDocsTat = $this->calculatePhysicalDocsTat($distributor);
        $totalTat = $this->calculateTotalTat($distributor);

        return [
            $distributor->application_code ?? 'N/A',
            $distributor->entityDetails->establishment_name ?? 'N/A',
            $distributor->getAuthorizedOrEntityName() ?? 'N/A',
            $distributor->vertical?->vertical_name ?? 'N/A',
            $distributor->regionDetail?->region_name ?? 'N/A',
            $distributor->created_at?->format('d-m-Y') ?? 'N/A',
            ucfirst(str_replace('_', ' ', $distributor->status)),
            $rbmTat,
            $gmTat,
            $seTat,
            $misTat,
            $physicalDocsTat,
            $totalTat,
            $this->getTatStatus($totalTat)
        ];
    }

    private function calculateApprovalTat($distributor, $role): string
    {
        $approvalLogs = $distributor->approvalLogs->where('role', $role);

        if ($approvalLogs->isEmpty()) {
            return 'Pending';
        }

        $approvalLog = $approvalLogs->sortBy('created_at')->first();

        // Find the previous approval log or creation date
        $previousLog = $distributor->approvalLogs
            ->where('created_at', '<', $approvalLog->created_at)
            ->sortByDesc('created_at')
            ->first();

        $startDate = $previousLog ? $previousLog->created_at : $distributor->created_at;
        $endDate = $approvalLog->created_at;

        $tat = $startDate->diffInDays($endDate);
        return $tat > 0 ? $tat . ' days' : 'Same day';
    }

    private function calculateMisTat($distributor): string
    {
        if (!$distributor->mis_verified_at) {
            return 'Pending';
        }

        // MIS verification starts after final approval
        $finalApproval = $distributor->approvalLogs
            ->where('action', 'approved')
            ->sortByDesc('created_at')
            ->first();

        if (!$finalApproval) {
            return 'N/A';
        }

        $startDate = $finalApproval->created_at;
        $endDate = $distributor->mis_verified_at;

        $tat = $startDate->diffInDays($endDate);
        return $tat > 0 ? $tat . ' days' : 'Same day';
    }

    private function calculatePhysicalDocsTat($distributor): string
    {
        $dispatch = $distributor->physicalDispatch;

        if (!$dispatch || !$dispatch->dispatch_date) {
            return 'Pending';
        }

        // Physical docs start after MIS verification
        $startDate = $distributor->mis_verified_at;

        if (!$startDate) {
            return 'N/A';
        }

        $endDate = $dispatch->dispatch_date;
        $tat = $startDate->diffInDays($endDate);
        return $tat > 0 ? $tat . ' days' : 'Same day';
    }

    private function calculateTotalTat($distributor): string
    {
        $startDate = $distributor->created_at;
        $endDate = null;

        // Determine end date based on current status
        if (in_array($distributor->status, ['completed', 'distributorships_created'])) {
            // For completed applications, use physical dispatch date or last update
            $endDate = $distributor->physicalDispatch?->dispatch_date ?? $distributor->updated_at;
        } elseif ($distributor->mis_verified_at) {
            $endDate = $distributor->mis_verified_at;
        } elseif ($distributor->approvalLogs->isNotEmpty()) {
            $endDate = $distributor->approvalLogs->last()->created_at;
        } else {
            $endDate = now(); // Still in process
        }

        $tat = $startDate->diffInDays($endDate);
        return $tat . ' days';
    }

    private function getTatStatus($totalTat): string
    {
        $days = (int) $totalTat;

        if ($days <= 7) {
            return 'Within SLA';
        } elseif ($days <= 14) {
            return 'Moderate Delay';
        } else {
            return 'Critical Delay';
        }
    }
}