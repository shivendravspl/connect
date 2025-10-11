<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LlpPartner extends Model
{
    protected $table = 'llp_partners';
    public $timestamps = true;

    protected $fillable = [
        'application_id',
        'name',
        'dpin_number',
        'contact',
        'address',
    ];

    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id');
    }
}