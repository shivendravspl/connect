<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Declaration extends Model
{
    use HasFactory;

    protected $table = 'declarations';

    protected $fillable = [
        'application_id',
        'question_key',
        'has_issue',
        'details',
    ];

    protected $casts = [
        'has_issue' => 'boolean',
        'details' => 'array', // Automatically cast JSON to array
    ];

    public $timestamps = true;

    // Optional: relationship with the application model if defined
    public function application()
    {
         return $this->belongsTo(Onboarding::class, 'application_id', 'id');
    }
}
