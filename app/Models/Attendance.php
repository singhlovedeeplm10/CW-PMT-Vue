<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;


class Attendance extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'user_id',
        'clockin_time',
        'clockout_time',
        'productive_hours',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(Breaks::class);
    }
    
}
