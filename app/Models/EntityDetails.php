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

        // PAN
        'pan_number',
        'pan_path',
        'pan_verified',

        // GST
        'gst_applicable',
        'gst_number',
        'gst_path',
        //'gst_validity',
        'gst_verified',

        // Seed License
        'seed_license',
        'seed_license_path',
        'seed_license_validity',
        'seed_license_verified',

        // New field
        'entity_proof_path',
        'ownership_info_path', 
        'bank_statement_path',
        'itr_acknowledgement_path',
        'balance_sheet_path', // New field

        // Bank
        'bank_name',
        'account_holder_name',
        'account_number',
        'ifsc_code',
        'bank_document_path',

        // Additional Identifiers
        'tan_number',
        'has_authorized_persons',
        'created_at',
        'updated_at',
    ];

    
    protected $casts = [
        'pan_verified' => 'boolean',
        'gst_verified' => 'boolean',
        'seed_license_verified' => 'boolean',
        //'gst_validity' => 'date',
        'seed_license_validity' => 'date:Y-m-d',
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