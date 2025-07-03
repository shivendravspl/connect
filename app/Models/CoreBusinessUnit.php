<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreBusinessUnit extends Model
{
    protected $table = 'core_business_unit';
     protected $fillable = [
        'business_unit_name',
        'business_unit_code',
        'numeric_code',
        'effective_date',
        'is_active'
        ];

    public $timestamps = false;
}
