<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Breaks extends Model
{

    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'start_time',
        'reason',
        'break_time',
        'end_time',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
