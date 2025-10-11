<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationAdditionalUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'additional_doc_id',
        'path',
        'status',
        'uploaded_by'
    ];

	  public function document()
    {
        return $this->belongsTo(ApplicationAdditionalDocument::class, 'additional_doc_id', 'id');
    }
}