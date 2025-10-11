<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CooperativeDetails extends Model
{
    protected $table = 'cooperative_details';
    public $timestamps = true;

    protected $fillable = [
        'application_id',
        'reg_number',
        'reg_date',
    ];

    protected $casts = [
        'reg_date' => 'date',
    ];

    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id');
    }
}