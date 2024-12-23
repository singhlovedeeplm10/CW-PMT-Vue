<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LikesComment extends Model
{
    use HasFactory;

    protected $table = 'likes_comments';

    protected $fillable = [
        'timeline_id',
        'timeline_uploads_id',
        'likes',
        'comments',
        'user_id', // Add user_id to fillable
    ];

    protected $casts = [
        'timeline_id' => 'array',
        'timeline_uploads_id' => 'array',
        'likes' => 'array',
        'comments' => 'array',
    ];

    public function timeline()
    {
        return $this->belongsTo(Timeline::class);
    }

    // Relationship with User model
    public function user()
    {
        return $this->belongsTo(User::class); // Add this method to define the relationship
    }
}

