<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'project_id',
        'task_description',
        'hours',
        'task_status',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
