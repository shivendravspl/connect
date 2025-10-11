<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Director extends Model
{
    protected $table = 'directors';
    public $timestamps = true;

    protected $fillable = [
        'application_id',
        'name',
        'din_number',
        'contact',
        'address',
    ];

    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id');
    }
}