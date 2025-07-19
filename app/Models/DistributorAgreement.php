<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistributorAgreement extends Model
{
    protected $table = 'distributor_agreements';
    protected $fillable = [
        'application_id', 'agreement_path', 'generated_by', 'generated_at'
    ];

    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id');
    }

    public function generatedBy()
    {
        return $this->belongsTo(Employee::class, 'generated_by', 'emp_id');
    }
}