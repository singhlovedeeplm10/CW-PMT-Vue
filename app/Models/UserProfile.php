<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

 protected $fillable = [
    'user_id',
    'employee_personal_email',
    'permanent_address',
    'qualifications',
    'employee_code',
    'academic_documents',
    'identification_documents',
    'offer_letter',
    'joining_letter',
    'contract',
    'user_image',
    'user_DOB',
    'gender',
    'contact',
    'date_of_joining',
    'date_of_releaving',
    'releaving_note',
    'next_appraisal_month',
    'blood_group',
    'temporary_address',
    'alternate_contact_number',
    'designation',
    'current_salary',
    'appraisals',
    'credentials',
    'profile_password', // ← Add this line
];


    protected $casts = [
        'appraisals' => 'array',
        'credentials' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor for appraisals - ensures we always return an array
     */
    public function getAppraisalsAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    /**
     * Accessor for credentials - ensures we always return an array
     */
    public function getCredentialsAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    /**
     * Mutator for appraisals - automatically encodes to JSON
     */
    public function setAppraisalsAttribute($value)
    {
        $this->attributes['appraisals'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Mutator for credentials - automatically encodes to JSON
     */
    public function setCredentialsAttribute($value)
    {
        $this->attributes['credentials'] = is_array($value) ? json_encode($value) : $value;
    }
}

