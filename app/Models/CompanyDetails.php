<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDetails extends Model
{
    protected $table = 'company_details';
    public $timestamps = true;

    protected $fillable = [
        'application_id',
        'entity_type',
        'cin_number',
        'incorporation_date',
    ];

    protected $casts = [
        'incorporation_date' => 'date',
    ];

    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id');
    }
}