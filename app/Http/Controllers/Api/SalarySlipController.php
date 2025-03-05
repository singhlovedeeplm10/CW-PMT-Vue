<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalarySlips;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Auth;
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
            // Extract numeric part from employee_id (e.g., CW001 → 1)
            $employeeId = preg_replace('/[^0-9]/', '', $record['employee_id']);
    
            // Find user by extracted numeric ID
            $user = User::find($employeeId);
    
            if ($user) {
                SalarySlips::updateOrCreate(
                    ['employee_id' => $record['employee_id']], // Match by employee_id
                    [
                        'employee_name' => $user->name,
                        'month_year' => $record['month_year'],
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
    public function getSalary(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();
    
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        // Initialize query for salary slips
        $query = SalarySlips::select(
            'id',
            'employee_id', // Corrected from employee_code
            'employee_name',
            'total_salary',
            'basic_salary',
            'total_deductions',
            'total_incentives',
            'net_salary_credited',
            'month_year',
            'created_at'
        );
    
        // Check if the logged-in user is an admin
        if (!$user->hasRole('Admin')) {
            // Filter only the logged-in user's salary slips
            $employeeId = 'CW' . str_pad($user->id, 3, '0', STR_PAD_LEFT); // Convert user id to employee_id format (e.g., 1 -> CW001)
    
            if (!$employeeId) {
                return response()->json(['error' => 'Employee ID not found'], 404);
            }
    
            $query->where('employee_id', $employeeId);
        }
    
        // Get the requested month and year or default to the current month-year
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
    
        // Convert month number to short month name (e.g., 01 -> Jan)
        $monthName = date('M', mktime(0, 0, 0, $month, 1));
        $monthYear = "$monthName-" . substr($year, -2); // Format: Jan-25
    
        // Filter by month_year column
        $query->where('month_year', $monthYear);
    
        $salarySlips = $query->get();
    
        return response()->json($salarySlips);
    }
    
    public function viewSalarySlip($employeeCode)
{
    $salarySlip = SalarySlips::where('employee_id', $employeeCode)->first();

    if (!$salarySlip) {
        return response()->json(['message' => 'Salary slip not found'], 404);
    }

    return response()->json($salarySlip);
}
public function updateSalarySlip(Request $request, $id)
    {
        // Validate the incoming data
        $validated = $request->validate([
            'employee_name' => 'required|string|max:255',
            'total_salary' => 'required|integer',
            'basic_salary' => 'nullable|integer',
            'house_rent' => 'nullable|integer',
            'conveyance' => 'nullable|integer',
            'telephone' => 'nullable|integer',
            'medical' => 'nullable|integer',
            'other' => 'nullable|integer',
            'tax' => 'nullable|integer',
            'unpaid_leaves_deduction' => 'nullable|integer',
            'provident_fund' => 'nullable|integer',
            'employee_state_insurance' => 'nullable|integer',
            'total_deductions' => 'nullable|integer',
            'extra_working_incentive' => 'nullable|integer',
            'gratuity' => 'nullable|integer',
            'bonus' => 'nullable|integer',
            'total_incentives' => 'nullable|integer',
            'total_leaves' => 'nullable|integer',
            'extra_working_days' => 'nullable|integer',
            'earned_leave' => 'nullable|integer',
            'leaves_without_pay' => 'nullable|integer',
            'net_salary_credited' => 'nullable|integer',
        ]);

        // Find the salary slip by ID
        $salarySlip = SalarySlips::find($id);

        // Check if the salary slip exists
        if (!$salarySlip) {
            return response()->json(['message' => 'Salary slip not found'], 404);
        }

        // Update the salary slip with the validated data
        $salarySlip->update($validated);

        // Return a response indicating success
        return response()->json(['message' => 'Salary slip updated successfully', 'data' => $salarySlip], 200);
    }
    public function deleteSalarySlip($id)
    {
        $salarySlip = SalarySlips::find($id);

        if (!$salarySlip) {
            return response()->json(['message' => 'Salary slip not found'], 404);
        }

        $salarySlip->delete();

        return response()->json(['message' => 'Salary slip deleted successfully'], 200);
    }
}
