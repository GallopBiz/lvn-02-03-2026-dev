<?php

namespace App\Http\Controllers;
use App\Models\CommanModel;
use App\Models\HrmsBasicDeduction;
use App\Http\Controllers\Controller;
use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeDeductionHistory;
use App\Models\HrmsEmployeeDeduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class BasicDeductionsController extends Controller
{


    public function index()
    {
        $stream = HrmsBasicDeduction::all();
        return view('backend.HRMS.BasicDeductions', compact('stream'));
    }


    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'amount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'description' => 'required|string|max:255'
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        HrmsBasicDeduction::create($request->post());
        return redirect()->route('basicDeduction')->with('success', '  holidays has been created successfully.');
    }

    public function view($id)
    {
        // $stream_master = HrmsBasicDeduction::whereId($id)->get();
        $stream_master = HrmsBasicDeduction::where('id', $id)->get();
        // $stream = HrmsBasicDeduction::orderBy('HolidayID','desc')->paginate(5);
        $stream = HrmsBasicDeduction::all();
        return view('backend.HRMS.BasicDeductions', compact('stream_master', 'stream'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'amount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'description' => 'required|string|max:255'
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $data = [
            'name' => $request->name,
            'amount' => $request->amount,
            'description' => $request->description
        ];
        HrmsBasicDeduction::where('id', $request->id)->update($data);
        return redirect()->route('basicDeduction')->with('success', 'holidays has been Updated successfully.');
    }


    public function delete($id)
    {
        $stream = HrmsBasicDeduction::findOrFail($id);
        $stream->delete();
        return redirect()->route('basicDeduction')->with('success', 'Deleted successfully.');
    }

    //manage employee deductions
    public function employeeMonthlyDeductionIndex()
    {
        $currentYear = now()->year;
        $lastYear = $currentYear - 1;
        $years = [$lastYear, $currentYear];
        // Get selected year and month from request, or default to current
        $selectedYear = request('year', $currentYear);
        $selectedMonthNum = null;
        if (request('month')) {
            $selectedMonthNum = explode('-', request('month'))[1];
        } else {
            $selectedMonthNum = now()->format('m');
        }
        $selectedMonth = $selectedYear . '-' . $selectedMonthNum;
        // Include months from current and previous year
        $months = collect();
        foreach ($years as $year) {
            for ($m = 1; $m <= 12; $m++) {
                $months->push(sprintf('%04d-%02d', $year, $m));
            }
        }
        $months = $months->sortDesc()->values();
        $employees = HrmsEmployee::with([
            'employeeDeductions' => function ($query) {
                $query->whereHas('salary', function ($salaryQuery) {
                    $salaryQuery->where('is_active', 1); // Assuming active salary is marked with `is_active = 1`
                });
            },
            'employeeDeductions.basicDeduction'
        ])->get();
        // Fetch deductions from history if available
        $deductionHistory = HrmsEmployeeDeductionHistory::where('month', $selectedMonthNum)
            ->where('year', $selectedYear)
            ->get()
            ->groupBy(['employee_id', 'basic_deduction_id']);
        $uniqueDeductions = collect();
        $employees->each(function ($employee) use ($deductionHistory, &$uniqueDeductions) {
            $employee->employeeDeductions->each(function ($deduction) use ($employee, $deductionHistory, &$uniqueDeductions) {
                $historyEntry = $deductionHistory[$employee->id][$deduction->basicDeduction->id] ?? null;
                if ($historyEntry) {
                    $deduction->manual_amount = $historyEntry->first()->amount;
                }
                $uniqueDeductions->put($deduction->basicDeduction->id, $deduction->basicDeduction->name);
            });
        });
        return view('backend.HRMS.EmployeeMonthlyDeductions', compact('employees', 'months', 'selectedMonth', 'uniqueDeductions', 'years', 'selectedYear'));
    }
    public function updateemployeeMonthlyDeduction(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:hrms_employees,id',
            'deduction_id' => 'required|exists:hrms_basic_deductions,id',
            'amount' => 'required|numeric|min:0',
            'month' => 'required|date'
        ]);
        $employee = HrmsEmployee::where('id', $request->employee_id)->with('activeSalary')->first();
        if (!$employee || !$employee->activeSalary) {
            return response()->json(['success' => false, 'message' => 'Employee or active salary not found.'], 422);
        }
        // Check if a deduction entry exists for the employee and month
        $deduction = HrmsEmployeeDeductionHistory::where('employee_id', $request->employee_id)
            ->where('basic_deduction_id', $request->deduction_id)
            ->where('month', Carbon::parse($request->month)->format('m'))
            ->where('year', Carbon::parse($request->month)->format('Y'))
            ->first();

        if ($deduction) {
            // Update existing deduction
            $deduction->amount = $request->amount;
            $deduction->save();
        } else {
            // Create new deduction entry
            HrmsEmployeeDeductionHistory::create([
                'employee_id' => $request->employee_id,
                'basic_deduction_id' => $request->deduction_id,
                'employee_salary_id' => $employee->activeSalary->id,
                'month' => Carbon::parse($request->month)->format('m'),
                'year' => Carbon::parse($request->month)->format('Y'),
                'amount' => $request->amount
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Deduction updated successfully.']);
    }

    public function fetchDeductions(Request $request)
	{
		$selectedMonth = $request->month ?? now()->format('Y-m');
		$employees = HrmsEmployee::with([
			'employeeDeductions' => function ($query) {
				$query->whereHas('salary', function ($salaryQuery) {
					$salaryQuery->where('is_active', 1);
				});
			},
			'employeeDeductions.basicDeduction'
		])->get();

		$deductionHistory = HrmsEmployeeDeductionHistory::where('month', Carbon::parse($selectedMonth)->format('m'))
			->where('year', Carbon::parse($selectedMonth)->format('Y') )
			->get()
			->groupBy(['employee_id', 'basic_deduction_id']);
			$uniqueDeductions = collect();
		$employees->each(function ($employee) use ($deductionHistory, &$uniqueDeductions) {
			$employee->employeeDeductions->each(function ($deduction) use ($employee, $deductionHistory, &$uniqueDeductions) {
				$historyEntry = $deductionHistory[$employee->id][$deduction->basicDeduction->id] ?? null;
				if ($historyEntry) {
					$deduction->manual_amount = $historyEntry->first()->amount;
				}
				$uniqueDeductions->put($deduction->basicDeduction->id, $deduction->basicDeduction->name);
			});
		});
	   // $uniqueDeductions = HrmsBasicDeduction::all()->pluck('name', 'id');
		$html = view('backend.HRMS.partials.employee_deductions', compact('employees','uniqueDeductions'))->render();

		return response()->json(['html' => $html]);
	}
	
	public function appliedDeductionsReport()
	{
		$employees = HrmsEmployee::with(['employeeDeductions.basicDeduction', 'biometricDetail'])->get();

		// Collect all unique deductions applied to any employee
		$uniqueDeductions = HrmsBasicDeduction::whereIn(
			'id',
			HrmsEmployeeDeduction::pluck('basic_deduction_id')->unique()
		)->get();

		return view('backend.HRMS.EmployeeDeductionApplyReport', compact('employees', 'uniqueDeductions'));
	}

	public function exportDeductions()
	{
		$employees = HrmsEmployee::with(['employeeDeductions.basicDeduction', 'biometricDetail'])->get();

		$deductionHeads = HrmsBasicDeduction::whereIn(
			'id',
			DB::table('hrms_employee_deductions')->pluck('basic_deduction_id')->unique()
		)->get();

		// CSV headers
		$csvHeader = ['S.No', 'Employee Name', 'ESS Code'];
		foreach ($deductionHeads as $head) {
			$csvHeader[] = $head->name;
		}

		$rows = [];
		$rows[] = $csvHeader;

		foreach ($employees as $index => $employee) {
			$row = [
				$index + 1,
				$employee->first_name . ' ' . $employee->last_name,
				$employee->biometricDetail->ess_emp_code ?? 'N/A',
			];

			foreach ($deductionHeads as $deduction) {
				$found = $employee->employeeDeductions->where('basic_deduction_id', $deduction->id)->first();
				$row[] = $found ? ($found->manual_amount ?? 'Default') : 'NA';
			}

			$rows[] = $row;
		}

		$fileName = 'employee_deductions_' . now()->format('Y_m_d_H_i_s') . '.csv';

		$handle = fopen('php://temp', 'r+');
		foreach ($rows as $row) {
			fputcsv($handle, $row);
		}
		rewind($handle);
		$csvContent = stream_get_contents($handle);
		fclose($handle);

		return Response::make($csvContent, 200, [
			'Content-Type' => 'text/csv',
			'Content-Disposition' => "attachment; filename=\"$fileName\"",
		]);
	}



}
