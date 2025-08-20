<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitMaster extends Model
{
    protected $table = 'unit_master';

    protected $fillable = [
        'unit_code',
        'unit_name',
    ];
}