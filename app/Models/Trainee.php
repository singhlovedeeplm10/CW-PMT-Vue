<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trainee extends Model
{
    // Table name (optional if it follows Laravel naming convention)
    protected $table = 'trainees';

    // Mass assignable attributes
    protected $fillable = [
        'trainee_name',
        'trainee_email',
        'training_start_date',
        'training_end_date',
        'training_note',
        'status',
    ];

    // Casts
    protected $casts = [
        'training_start_date' => 'date',
        'training_end_date' => 'date',
    ];
}
