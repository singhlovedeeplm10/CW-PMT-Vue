<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'policy_title',
        'last_updated_at',
        'document_path',
    ];

    /**
     * Get the user associated with the policy.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
