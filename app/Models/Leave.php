<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type_of_leave',
        'half',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'reason',
        'status',
        'contact_during_leave',
    ];
    

    /**
     * Get the user that owns the leave.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    

}
