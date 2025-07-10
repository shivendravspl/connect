<?php
// app/Models/SecurityDeposit.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityDeposit extends Model
{
    protected $fillable = [
        'application_id', 'deposit_date', 'amount',
        'payment_mode', 'reference_number'
    ];
    
    protected $casts = [
        'deposit_date' => 'date',
        'amount' => 'decimal:2'
    ];
    
    public function application()
    {
        return $this->belongsTo(Onboarding::class);
    }
}