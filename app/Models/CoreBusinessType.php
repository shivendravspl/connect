<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoreBusinessType extends Model
{
    protected $table = 'core_business_type';
     protected $fillable = [
        'business_type',
        'is_active',
        ];

    public $timestamps = false;
}
