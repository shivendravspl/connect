<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndividualDetails extends Model
{
    protected $table = 'individual_details';
    public $timestamps = true;

    protected $fillable = [
        'application_id',
        'name',
        'dob',
        'father_name',
        'age',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id');
    }
}