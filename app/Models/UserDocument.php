<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserDocument extends Model
{
    use HasFactory;

    protected $table = 'user_documents';

    // Mass assignable fields
    protected $fillable = [
        'user_id',
        'document_label',
        'file_path',
        'file_type',
        'document_note',
        'show_to_employee',
    ];

    /**
     * Relationship: Each document belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
