<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhysicalDispatch extends Model
{
    protected $fillable = [
        'application_id',
        'mode',
        'transport_name',
        'driver_name',
        'driver_contact',
        'docket_number',
        'courier_company_name',
        'person_name',
        'person_contact',
        'dispatch_date',
        'receive_date',
    ];

    protected $casts = [
        'dispatch_date' => 'date',
        'receive_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function onboarding()
    {
        return $this->belongsTo(Onboarding::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by' ,'employee_id');
    }
}
