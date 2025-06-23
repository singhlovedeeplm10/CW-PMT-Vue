<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'device',
        'device_number',
        'note',
        'developer_assign_list',
        'date_of_assign',
        'status',
        'history',
    ];

    protected $casts = [
        'developer_assign_list' => 'array',
        'history' => 'array',
    ];

    // In Device.php model
public function assignedDevelopers()
{
    return $this->belongsToMany(User::class, null, 'device_id', 'user_id')
                ->whereIn('id', $this->developer_assign_list ?? []);
}
}

