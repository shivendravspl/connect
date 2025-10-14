<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityDepositDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'physical_document_check_id',
        'deposit_date',
        'amount',
        'mode_of_payment',
        'reference_no',
    ];

    protected $casts = [
        'deposit_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function physicalDocumentCheck()
    {
        return $this->belongsTo(PhysicalDocumentCheck::class);
    }
}