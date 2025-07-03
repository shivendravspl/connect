<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Year extends Model
{
    use SoftDeletes;

    protected $table = 'years';

    protected $fillable = [
        'start_year',
        'end_year',
        'period',
        'year_id',
        'status',
    ];

    protected $casts = [
        'start_year' => 'date',
        'end_year' => 'date',
    ];
}