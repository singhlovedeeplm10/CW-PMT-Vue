<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalarySlips extends Model
{

    use HasFactory;

    protected $table = 'salary_slips';

    protected $fillable = [
        'employee_code',
        'employee_name',
        'total_salary',
        'basic_salary',
        'house_rent',
        'conveyance',
        'telephone',
        'medical',
        'other',
        'tax',
        'unpaid_leaves_deduction',
        'provident_fund',
        'employee_state_insurance',
        'total_deductions',
        'extra_working_incentive',
        'gratuity',
        'bonus',
        'total_incentives',
        'total_leaves',
        'extra_working_days',
        'earned_leave',
        'leaves_without_pay',
        'net_salary_credited',
    ];
}
