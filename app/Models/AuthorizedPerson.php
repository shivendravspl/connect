<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthorizedPerson extends Model
{
    protected $table = 'authorized_persons';
    public $timestamps = true;

    protected $fillable = [
        'application_id',
        'name',
        'contact',
        'email',
        'address',
        'relation',
        'aadhar_number',
        'letter_path',
        'aadhar_path',
    ];

    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id');
    }
}