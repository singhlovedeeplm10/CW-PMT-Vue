<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Technology extends Model
{
    use HasFactory;

    // Define the table name (optional, only if it doesn't follow Laravel's naming convention)
    protected $table = 'technologies';

    // Specify the fillable attributes for mass assignment
    protected $fillable = [
        'name',
        'description',
        'type',
        'admin_id',
    ];

    /**
     * Define the relationship with the User model (admin).
     * Assuming each technology is associated with an admin (user).
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
