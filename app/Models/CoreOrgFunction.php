<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreOrgFunction extends Model
{
     protected $table = 'core_org_function';
     protected $fillable = [
        'function_name',
        'function_code',
        'effective_date',
        'is_active'
        ];

    public $timestamps = false;
}
