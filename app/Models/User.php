<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function leaves()
{
    return $this->hasMany(Leave::class);
}

public function attendances()
{
    return $this->hasMany(Attendance::class);
}
public function breaks()
    {
        return $this->hasMany(Breaks::class, 'user_id');
    }
    
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function dailyTasks()
    {
        return $this->hasMany(DailyTask::class);
    }

    // Assuming "team_lead_name" is a field in your users table
    public function getTeamLeadNameAttribute()
    {
        return $this->team_lead_name; // Replace with your logic for fetching team lead name
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'id');
    }
    
}
