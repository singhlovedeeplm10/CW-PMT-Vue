<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    // Specify the table name if it's not the default plural form
    protected $table = 'uploads';

    // Define fillable fields for mass assignment
    protected $fillable = [
        'admin_id',
        'file_name',
        'file_description',
        'path',
    ];

    /**
     * Relationship to the User model
     * Assuming 'admin_id' is a foreign key referencing the User model
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
