<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'start_date',
        'end_date',
        'billing_type',
        'billing_hours',
        'milestone_ids',
        'billing_credential_id',
        'credentials',
        'technology_front',
        'technology_back',
        'technology_dbms',
        'dependencies',
        'upload_ids',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function billingCredential()
    {
        return $this->belongsTo(BillingCredential::class, 'billing_credential_id');
    }
}
