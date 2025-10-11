<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LlpDetails extends Model
{
    protected $table = 'llp_details';
    public $timestamps = true;

    protected $fillable = [
        'application_id',
        'llpin_number',
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