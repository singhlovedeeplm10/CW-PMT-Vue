<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'start_date',
        'end_date',
        'hours',
        'description',
        'status',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
