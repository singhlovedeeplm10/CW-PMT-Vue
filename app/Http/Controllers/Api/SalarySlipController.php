<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalarySlips;
use App\Models\User;
use App\Models\UserProfile;
use League\Csv\Reader;


class SalarySlipController extends Controller
{
    public function upload(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:csv,txt',
    ]);

    $file = $request->file('file');
    $csv = Reader::createFromPath($file->getPathname(), 'r');
    $csv->setHeaderOffset(0);

    foreach ($csv as $record) {
        $employeeCode = $record['employee_code'];
        $userProfile = UserProfile::where('employee_code', $employeeCode)->first();

        if ($userProfile) {
            $user = User::find($userProfile->user_id);

            // Use updateOrCreate to create or update the record
            SalarySlips::updateOrCreate(
                ['employee_code' => $employeeCode], // Condition to match existing record
                [
                    'employee_name' => $user->name,
                    'total_salary' => $record['total_salary'],
                    'basic_salary' => $record['basic_salary'],
                    'house_rent' => $record['house_rent'],
                    'conveyance' => $record['conveyance'],
                    'telephone' => $record['telephone'],
                    'medical' => $record['medical'],
                    'other' => $record['other'],
                    'tax' => $record['tax'],
                    'unpaid_leaves_deduction' => $record['unpaid_leaves_deduction'],
                    'provident_fund' => $record['provident_fund'],
                    'employee_state_insurance' => $record['employee_state_insurance'],
                    'total_deductions' => $record['total_deductions'],
                    'extra_working_incentive' => $record['extra_working_incentive'],
                    'gratuity' => $record['gratuity'],
                    'bonus' => $record['bonus'],
                    'total_incentives' => $record['total_incentives'],
                    'total_leaves' => $record['total_leaves'],
                    'extra_working_days' => $record['extra_working_days'],
                    'earned_leave' => $record['earned_leave'],
                    'leaves_without_pay' => $record['leaves_without_pay'],
                    'net_salary_credited' => $record['net_salary_credited'],
                ]
            );
        }
    }

    return response()->json(['message' => 'Salary slips processed successfully!']);
}
}
