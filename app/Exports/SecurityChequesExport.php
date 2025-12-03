<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SecurityChequesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Establishment Name',
            'Distributor Code',
            'Cheque No',
            'Date Obtained',
            'Purpose',
            'Date of Use',
            'Date Return',
            'Status',
            'Created By',
            'Application Status',
            'Created At',
        ];
    }

    public function map($row): array
    {
        $app = $row['application'];
        $cheque = $row['cheque'];

        $status = 'No Cheque';
        if ($cheque) {
            if ($cheque->date_return) {
                $status = 'Returned';
            } elseif ($cheque->date_use) {
                $status = 'In Use';
            } else {
                $status = 'Held';
            }
        }

        return [
            $app->entityDetails->establishment_name ?? 'N/A',
            $app->distributor_code ?? 'Not Assigned',
            $cheque?->cheque_no ?? '—',
            $cheque?->date_obtained?->format('d-m-Y') ?? '—',
            $cheque?->purpose ?? '—',
            $cheque?->date_use?->format('d-m-Y') ?? 'Not Used',
            $cheque?->date_return?->format('d-m-Y') ?? 'Not Returned',
            $status,
            $app->createdBy->emp_name ?? 'N/A',
            ucfirst(str_replace('_', ' ', $app->status)),
            $app->created_at->format('d-m-Y H:i'),
        ];
    }
}