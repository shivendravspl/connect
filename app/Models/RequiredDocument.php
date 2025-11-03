<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequiredDocument extends Model
{
    use HasFactory;

    protected $table = 'required_documents_checklist';
    
    protected $fillable = [
        'category',
        'sub_category',
        'document_name',
        'checkpoints',
        'applicability_justification',
        'applicability',
        'entity_types',
        'sort_order'
    ];

    protected $casts = [
        'entity_types' => 'array'
    ];

    // Entity type constants
    const ENTITY_TYPES = [
        'sole_proprietorship' => 'SOLE PROPRIETORSHIP',
        'partnership' => 'PARTNERSHIP',
        'llp' => 'LLP',
        'company' => 'COMPANY',
        'cooperative_society' => 'COOPERATIVE SOCIETY',
        'trust' => 'TRUST'
    ];

    public function getEntityTypesAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function setEntityTypesAttribute($value)
    {
        $this->attributes['entity_types'] = json_encode($value);
    }

    // Scope to get documents by entity type
    public function scopeForEntityType($query, $entityType)
    {
        return $query->whereJsonContains('entity_types', $entityType);
    }

    // Scope to get by applicability
    public function scopeByApplicability($query, $applicability)
    {
        return $query->where('applicability', $applicability);
    }
}