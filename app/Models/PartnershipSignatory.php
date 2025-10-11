<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnershipSignatory extends Model
{
    protected $table = 'partnership_signatories';
    public $timestamps = true;

    protected $fillable = [
        'application_id',
        'name',
        'designation',
        'contact',
    ];

    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id');
    }
}