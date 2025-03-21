<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyTask extends Model
{
    use HasFactory;

    protected $table = 'daily_tasks';

    protected $fillable = [
        'user_id',
        'attendance_id',
        'project_id',
        'project_name', // New attribute
        'leave_id', // New attribute
        'hours',
        'task_description',
        'task_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function leave()
    {
        return $this->belongsTo(Leave::class);
    }
}
