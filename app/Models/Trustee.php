<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trustee extends Model
{
    protected $table = 'trustees';
    public $timestamps = true;

    protected $fillable = [
        'application_id',
        'name',
        'designation',
        'contact',
        'address',
    ];

    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id');
    }
}