<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhysicalDocumentCheck extends Model
{
    protected $table = 'physical_document_checks';

    protected $fillable = [
        'application_id',
        'document_type',
        'received',
        'status',
        'reason',
        'amount',
        'file_path',
        'original_filename',
        'submitted_by',
        'verified_date',
    ];

    protected $casts = [
        'received' => 'boolean',
        'amount' => 'decimal:2',
        'verified_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id');
    }

    public function securityChequeDetails()
    {
        return $this->hasMany(SecurityChequeDetail::class,'physical_document_check_id');
    }

    public function securityDepositDetail()
    {
        return $this->hasOne(SecurityDepositDetail::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(Employee::class, 'submitted_by', 'id');
    }
}