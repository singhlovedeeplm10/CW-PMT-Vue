<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_user_id',
        'type',
        'type_id',
        'notification_message',
        'to_user_id',
        'is_read',
    ];

    /**
     * User who sent the notification
     */
    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * User who receives the notification
     */
    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

   public function relatedItem()
{
    return match ($this->type) {
        'projects' => $this->belongsTo(Project::class, 'type_id'),
        'leaves' => $this->belongsTo(Leave::class, 'type_id'),
        'devices' => $this->belongsTo(Device::class, 'type_id'),
        default => $this->belongsTo(Project::class, 'type_id')->whereRaw('1=0'), // Returns empty relation
    };
}

}
