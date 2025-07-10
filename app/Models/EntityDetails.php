<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntityDetails extends Model
{
    protected $table = 'entity_details';
    public $timestamps = true;

    protected $fillable = [
        'application_id',
        'establishment_name',
        'entity_type',
        'business_address',
        'house_no',
        'landmark',
        'city',
        'district_id',
        'state_id',
        'country_id',
        'pincode',
        'mobile',
        'email',
        'pan_number',
        'gst_applicable',
        'gst_number',
        'seed_license',
        'additional_data',
        'documents_data',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'additional_data' => 'array',
        'documents_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the Onboarding application that owns the entity details.
     */
    public function Onboarding()
    {
        return $this->belongsTo(Onboarding::class, 'application_id', 'id');
    }

    public function state()
    {
        return $this->belongsTo(CoreState::class, 'state_id');
    }

    public function district()
    {
        return $this->belongsTo(CoreDistrict::class, 'district_id');
    }

    public function country()
    {
        return $this->belongsTo(CoreCountry::class, 'country_id');
    }
}