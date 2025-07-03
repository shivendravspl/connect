<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialInfo extends Model
{
    use HasFactory;

    protected $table = 'financial_infos';

    protected $guarded = [];

    protected $casts = [
        'annual_turnover' => 'array',
        'net_worth' => 'float',
        'years_in_business' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;
}
