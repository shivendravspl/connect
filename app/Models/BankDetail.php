<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    use HasFactory;

    protected $table = 'bank_details';

    protected $fillable = [
        'application_id',
        'financial_status',
        'retailer_count',
        'bank_name',
        'account_holder',
        'account_number',
        'ifsc_code',
        'account_type',
        'relationship_duration',
        'od_limit',
        'od_security',
    ];

    public $timestamps = true;

     public function application()
    {
        return $this->belongsTo(DistributorApplication::class, 'application_id', 'id');
    }
}
