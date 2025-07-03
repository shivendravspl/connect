<?php
// app/Models/Agreement.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agreement extends Model
{
    protected $fillable = [
        'application_id', 'draft_path', 'final_path',
        'status', 'created_by'
    ];
    
    public function application()
    {
        return $this->belongsTo(DistributorApplication::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}