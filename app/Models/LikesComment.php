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
}
