<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address',
        'qualifications',
        'employee_code',
        'academic_documents',
        'identification_documents',
        'offer_letter',
        'joining_letter',
        'contract',
        'user_image',      // Profile image field
        'user_DOB',        // Date of birth field
        'gender',          // Gender field
        'contact',         // Contact field
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
