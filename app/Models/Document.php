<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory;

    protected $table = 'documents';

    protected $fillable = [
        'application_id',
        'type',
        'path',
        'status',
        'remarks',
    ];

    public $timestamps = true;

    // Optional: Define relationship with Onboarding
    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id');
    }
}
