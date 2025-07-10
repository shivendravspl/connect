<?php
// app/Models/ApprovalLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalLog extends Model
{
    protected $fillable = [
        'application_id', 'user_id', 'role', 
        'action', 'remarks'
    ];
    
    public function application()
    {
        return $this->belongsTo(Onboarding::class, 'application_id');   
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','emp_id');
    }
}

