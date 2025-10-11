<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnershipPartner extends Model
{
    protected $table = 'partnership_partners';
    public $timestamps = true;

    protected $fillable = [
        'id',
        'application_id',
        'name',
        'pan',
        'contact',
        'aadhar_path',
        'aadhar_original_filename',
    ];

    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id', 'id');
    }
}