<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentVerification extends Model
{
    protected $table = 'document_verifications';
    protected $fillable = [
        'application_id',
        'document_type',
        'status',
        'remarks',
        'verified_by',
        'verified_at'
    ];
    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(Employee::class, 'verified_by', 'employee_id');
    }
}
