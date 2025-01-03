<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'type',
        'status',
        'comment',
        'developer_assign_list',
    ];

    protected $casts = [
        'developer_assign_list' => 'array',  // This will automatically cast the JSON to an array
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
