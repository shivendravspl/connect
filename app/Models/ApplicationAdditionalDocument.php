<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationAdditionalDocument extends Model
{
    protected $table = 'application_additional_documents';
    protected $fillable = ['application_id', 'document_name', 'remark', 'submitted_by', 'status'];

    public function upload()
    {
        return $this->hasOne(ApplicationAdditionalUpload::class, 'additional_doc_id', 'id');
    }
}