<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'amount_credited',
        'amount_deducted',
        'deduction_type',
        'unpaid_leaves',
        'extra_amount_credited',
        'credit_type',
        'ot_hours',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
