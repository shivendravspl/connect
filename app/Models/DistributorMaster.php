<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DistributorMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id', 'territory_id', 'distributor_code', 'name',
        'entity_type', 'pan_number', 'gst_number', 'agreement_date',
        'security_cheque_amount', 'security_deposit_amount', 'status',
        'created_by'
    ];

    public function application()
    {
        return $this->belongsTo(Onboarding::class);
    }

    public function territory()
    {
        return $this->belongsTo(CoreTerritory::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by' ,'emp_id');
    }
}
