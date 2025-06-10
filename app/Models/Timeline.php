<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timeline extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'timeline_uploads_ids',
    ];

    /**
     *
     * @var array
     */
    protected $casts = [
        'timeline_uploads_ids' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function timelineUploads()
{
    return $this->hasMany(TimelineUpload::class, 'timeline_id');
}
public function likesComments()
{
    return $this->hasOne(LikesComment::class, 'timeline_id');
}
}
