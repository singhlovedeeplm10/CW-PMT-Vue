<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimelineUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'timeline_id',
        'file_path',
        'file_type',
        'file_link',
    ];

    /**
     * Get the timeline associated with the upload.
     */
    public function timeline()
    {
        return $this->belongsTo(Timeline::class);
    }
}
