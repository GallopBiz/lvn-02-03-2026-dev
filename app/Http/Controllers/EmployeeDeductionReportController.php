<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HrmsEmployee;
use App\Models\HrmsPayroll;
use Carbon\Carbon;

class EmployeeDeductionReportController extends Controller
{
    public function index(Request $request)
    {
        // Fetch employees for filter dropdown
        $employees = HrmsEmployee::with('position')->get();

        // Prepare years for filter dropdown (last 5 years)
        $currentYear = now()->year;
        $years = range($currentYear, $currentYear - 5);

        // Query payrolls with employee relationship
        $query = HrmsPayroll::with('employee');

        // Apply filters
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $deductionRows = [];

        // Only fetch payrolls if at least one filter is applied
        if ($request->filled('employee_id') || $request->filled('month') || $request->filled('year')) {
            $payrolls = $query->get();

            // Keep only the latest payroll per employee per month/year
            $latestPayrolls = [];
            foreach ($payrolls as $payroll) {
                $key = $payroll->employee_id . '_' . $payroll->month . '_' . $payroll->year;
                if (!isset($latestPayrolls[$key]) || $payroll->id > $latestPayrolls[$key]->id) {
                    $latestPayrolls[$key] = $payroll;
                }
            }

            // Prepare deduction rows
            foreach ($latestPayrolls as $payroll) {
                $deductions = $payroll->deductions;

                // Decode JSON if stored as string
                if (is_string($deductions)) {
                    $deductions = json_decode($deductions, true);
                }

                if (!empty($deductions) && is_array($deductions)) {
                    foreach ($deductions as $deduction) {
                        $name = ucfirst($deduction['name'] ?? 'N/A'); // capitalize first letter
                        $amount = $deduction['amount'] ?? 0;

                        // Sum if amount is array
                        if (is_array($amount)) {
                            $amount = array_sum($amount);
                        }

                        $deductionRows[] = [
                            'employee_name' => $payroll->employee->first_name . ' ' . $payroll->employee->last_name,
                            'month' => Carbon::create()->month($payroll->month)->format('F'),
                            'year' => $payroll->year,
                            'deduction_type' => $name,
                            'amount' => $amount,
                        ];
                    }
                }
            }
        }

        // CSV export
        if ($request->has('export')) {
            $filename = 'employee_deductions_' . now()->format('Ymd_His') . '.csv';
            $headers = ['#', 'Employee Name', 'Month', 'Year', 'Deduction Type', 'Amount'];

            $handle = fopen('php://output', 'w');
            ob_start();
            fputcsv($handle, $headers);

            foreach ($deductionRows as $index => $row) {
                fputcsv($handle, [
                    $index + 1,
                    $row['employee_name'],
                    $row['month'],
                    $row['year'],
                    $row['deduction_type'],
                    number_format($row['amount'], 2),
                ]);
            }

            fclose($handle);
            $content = ob_get_clean();

            return response($content)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', "attachment; filename=\"$filename\"");
        }

        // Return view
        return view('backend.HRMS.employee-deduction-report', compact(
            'employees',
            'years',
            'deductionRows'
        ));
    }
}
