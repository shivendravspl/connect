<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExistingDistributorship extends Model
{
    protected $fillable = [
        'application_id',
        'company_name'
    ];
    public $timestamps = true;

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function application()
    {
         return $this->belongsTo(DistributorApplication::class, 'application_id', 'id');
    }
}