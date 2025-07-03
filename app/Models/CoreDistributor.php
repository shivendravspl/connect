<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreDistributor extends Model
{
    protected $table = 'core_distributor';
     protected $fillable = [
        'business_type',
        'name',
        'state',
        'district',
        'city',
        'address',
        'pin_code',
        'contact_person',
        'email',
        'phone',
        'phone_two',
        'bulk_party',
        'vc_territory',
        'fc_territory',
        'bulk_territory',
        'vc_emp',
        'fc_emp',
        'bulk_emp',
        'status',
    ];

    public $timestamps = false;

    
}
