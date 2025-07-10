<?php
// app/Models/SecurityCheque.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityCheque extends Model
{
    protected $fillable = [
        'application_id', 'cheque_number', 'bank_name', 'status'
    ];
    
    public function application()
    {
        return $this->belongsTo(Onboarding::class);
    }
}