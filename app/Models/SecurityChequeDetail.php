<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityChequeDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'physical_document_check_id',
        'date_obtained',
        'cheque_no',
        'date_use',
        'purpose',
        'date_return',
        'remark_return',
    ];

    protected $casts = [
        'date_obtained' => 'date',
        'date_use' => 'date',
        'date_return' => 'date',
    ];

    public function physicalDocumentCheck()
    {
        return $this->belongsTo(PhysicalDocumentCheck::class);
    }
}