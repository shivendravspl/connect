<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntityDetailsAuditLog extends Model
{
    protected $table = 'entity_details_audit_logs';
    protected $fillable = ['application_id', 'entity_type','field_name', 'old_value', 'new_value', 'updated_by', 'updated_at'];
}