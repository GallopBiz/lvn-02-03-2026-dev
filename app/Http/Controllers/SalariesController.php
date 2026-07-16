<?php

namespace App\Http\Controllers;
use App\Models\CommanModel;
use App\Models\HrmsBasicDeduction;
use App\Models\HrmsEmployeeDeduction;
use App\Models\HrmsSalary;
use App\Models\Salaries;
use App\Models\HrmsEmployee;
use App\Models\Student_registration;
use App\Models\HrmsEmployeeChild;
use App\Models\HrmsEmployeeFeeEMI;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalariesController extends Controller
{


    public function index()
    {
        $stream = HrmsSalary::all();

        $employeeName = HrmsEmployee::all();
        $basicDeduction = HrmsBasicDeduction::all();
        $students = Student_registration::all();
        return view('backend.HRMS.salaries', compact('stream', 'employeeName', 'basicDeduction', 'students'));
    }


    public function create(Request $request)
    {
        $this->trimSalaryInputs($request);

        // Start a transaction
    DB::beginTransaction();
    try {
        $validator = Validator::make($request->all(), [
            //'employee_id' => 'required|integer|exists:hrms_employees,id',
            'employee_id' => [
                'required',
                'exists:hrms_employees,id',
                function ($attribute, $value, $fail) use ($request): void {
                    $employeeExists = HrmsSalary::where(['employee_id' => $value, 'is_active' => true])->exists();

                    // Check if the request's `is_active` is explicitly true and add further conditions
                    $isRequestActive = isset($request->is_active) && $request->is_active == true;

                    if ($employeeExists && $isRequestActive) {
                        $fail('A active salary with this Employee ID already exists.');
                    }
                },
            ],

            'basic_salary' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'effective_date' => 'required|date',
            'has_children' => 'nullable|boolean',
            'child_select' => 'required_if:has_children,1|array', // Ensure 'child_select' exists when 'has_children' is true
            'child_select.*' => 'exists:student_registration,id', // Ensure each child ID exists in the children table
            'child_amount' => 'required_if:has_children,1|array', // Ensure 'child_amount' exists when 'has_children' is true
            'child_amount.*' => 'numeric|min:0', // Validate that each amount is numeric and greater than or equal to 0
            'emi_amount' => 'required_if:has_children,1|array', // Ensure 'emi_amount' exists when 'has_children' is true
            'emi_amount.*' => 'numeric|min:0', // Validate that each amount is numeric and greater than or equal to 0
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $salaryData = [
            'employee_id' => $request->employee_id,
            'basic_salary' => $request->basic_salary,
            'effective_date' => $request->effective_date,
            'is_active' => isset($request->is_active) ? 1 : 0
        ];
        $salaryId = HrmsSalary::create($salaryData);
        if (isset($request->deductions_ids)) {
            foreach ($request->deductions_ids as $basicDeduction) {
                $basicDeductionData = [
                    'employee_id' => $request->employee_id,
                    'basic_deduction_id' => $basicDeduction,
                    'manual_amount'=> $this->getDeductionAmount($basicDeduction, $request->manual_deductions[$basicDeduction] ?? null),
                    'employee_salary_id' => $salaryId->id
                ];
                HrmsEmployeeDeduction::create($basicDeductionData);
            }
        }
        if ($request->has('has_children') && $request->has('child_select') && $request->has('child_amount')) {
            foreach ($request->child_select as $index => $childId) {
                $childData = [
                    'employee_id' => $request->employee_id,
                    'student_id' => $childId,
                    'fee_amount' => $request->child_amount[$index],
                    'employee_salary_id' => $salaryId->id,
                    'emi_amount' => $request->emi_amount[$index],
                    'is_active' => true
                ];
               $employeeChild= HrmsEmployeeChild::create($childData);
                // Generate EMIs for school fees
            $this->generateFeeEMIs($salaryId, $request->child_select, $request->child_amount, $request->emi_amount,$employeeChild);
            }
            
        }
        DB::commit();
        return redirect()->route('salaries')->with('success', 'Employee salary has been created successfully.');
    } catch (\Exception $e) {
        // Rollback the transaction if anything fails
        DB::rollBack();

        // Log the exception (you can log the error message or handle it as needed)
        \Log::error('Error creating salary: ' . $e->getMessage());

        return redirect()->back()->with('error', 'An error occurred while processing your request.');
    }
    }

    private function generateFeeEMIs($salaryId, $children, $amounts, $emiAmounts,$employeeChild)
    {
        DB::beginTransaction();

        try {
        foreach ($children as $index => $childId) {
            $amount = $amounts[$index];
            $emis = $this->calculateFeeEMIs($amount, $salaryId->effective_date, $emiAmounts[$index]); // Custom function to calculate EMIs
            foreach ($emis as $emi) {
                HrmsEmployeeFeeEMI::create([
                    'employee_salary_id' => $salaryId->id,
                    'student_id' => $childId,
                    'emi_date' => $emi['date'],
                    'emi_amount' => $emi['amount'],
                    'status' => 'pending',
                    'employee_child_id' => $employeeChild->id
                ]);
            }
        }
        DB::commit();
    } catch (\Exception $e) {
        // Rollback if any error occurs
        DB::rollBack();
        // Log the exception for debugging
        \Log::error('Error generating fee EMIs: ' . $e->getMessage());
    }
    }

    private function calculateFeeEMIs($totalAmount, $startDate, $emiAmount)
    {
        $emis = [];
        while ($totalAmount > 0) {
            $emis[] = [
                'date' => $startDate,
                'amount' => min($emiAmount, $totalAmount)
            ];
            $totalAmount -= $emiAmount;
            $startDate = Carbon::parse($startDate);
            $startDate->addMonth();
        }
        return $emis;
    }

    public function pauseFeeEmi(Request $request)
    {
        $emi = HrmsEmployeeFeeEMI::findOrFail($request->emi_id);

        if (isset($request->cash_amount)&& $request->cash_amount > 0) {
            $emi->status = 'paid';
            $emi->paid_in_cash = $request->cash_amount;
            if($request->cash_amount < $emi->emi_amount){
                $emi->emi_amount -= $request->cash_amount;
                $this->addExtraMonthToFeeEmiSchedule($emi);
                $emi->emi_amount=$request->cash_amount;
            }
        } else {
            $emi->status = 'paused';
            $this->addExtraMonthToFeeEmiSchedule($emi);
        }

        $emi->save();
        return response()->json([
            'success' => true,
            'emi_id' => $emi->id,
            'message' => 'EMI has been paused and schedule recalculated.'
        ]);
        
    }

    private function addExtraMonthToFeeEmiSchedule($emi)
    {
        $lastEmi = HrmsEmployeeFeeEMI::where('employee_salary_id', $emi->employee_salary_id)
            ->orderBy('emi_date', 'desc')
            ->first();

        $newEmiDate = Carbon::parse($lastEmi->emi_date)->addMonth();

        HrmsEmployeeFeeEMI::create([
            'employee_salary_id' => $emi->employee_salary_id,
            'student_id' => $emi->student_id,
            'employee_child_id' => $lastEmi->employee_child_id,
            'emi_date' => $newEmiDate,
            'emi_amount' => $emi->emi_amount,
            'status' => 'pending'
        ]);
    }

    public function view($id)
    {
        // $stream_master = Salaries::where('SalaryID',$id)->get();
        // $stream = Salaries::whereId($id)->get();
        // $stream = Salaries::orderBy('id','desc')->paginate(5);

        $stream_master = HrmsSalary::where('id', $id)->first();
        $stream = HrmsSalary::all();
        $employeeName = HrmsEmployee::all();
        $basicDeduction = HrmsBasicDeduction::all();
        $students = Student_registration::all();
        $manualDeductions = $stream_master->employeeDeductions->pluck('manual_amount', 'basic_deduction_id');
        return view('backend.HRMS.salaries', compact('stream_master', 'stream', 'employeeName', 'basicDeduction', 'students','manualDeductions'));
    }

    public function store(Request $request)
    {
        $this->trimSalaryInputs($request);

        try {
            $validator = Validator::make($request->all(), [
                'employee_id' => [
                    'required',
                    'exists:hrms_employees,id',
                    function ($attribute, $value, $fail) use ($request): void {
                        $employeeExists = HrmsSalary::where(['employee_id' => $value, 'is_active' => true])->where('id', '<>', $request->id)->exists();

                        // Check if the request's `is_active` is explicitly true and add further conditions
                        $isRequestActive = isset($request->is_active) && $request->is_active == true;

                        if ($employeeExists && $isRequestActive) {
                            $fail('A active salary with this Employee ID already exists.');
                        }
                    },
                ],
                'basic_salary' => 'required|regex:/^\d+(\.\d{1,2})?$/',
                'effective_date' => 'required|date',
                'has_children' => 'nullable|boolean',
                'child_select' => 'required_if:has_children,1|array', // Ensure 'child_select' exists when 'has_children' is true
                'child_select.*' => 'exists:student_registration,id', // Ensure each child ID exists in the children table
                'child_amount' => 'required_if:has_children,1|array', // Ensure 'child_amount' exists when 'has_children' is true
                'child_amount.*' => 'numeric|min:0',
                'emi_amount' => 'required_if:has_children,1|array', // Ensure 'emi_amount' exists when 'has_children' is true
                'emi_amount.*' => 'numeric|min:0', // Validate that each amount is numeric and greater than or equal to 0
            ]);
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            $salary = HrmsSalary::findOrFail($request->id);

            // Update the salary data
            $salary->update([
                'employee_id' => $request->employee_id,
                'basic_salary' => $request->basic_salary,
                'effective_date' => $request->effective_date,
                'is_active' => isset($request->is_active) ? 1 : 0
            ]);

            // Update deductions
            HrmsEmployeeDeduction::where('employee_salary_id', $salary->id)->delete();
            if (isset($request->deductions_ids)) {
                foreach ($request->deductions_ids as $basicDeduction) {
                    $basicDeductionData = [
                        'employee_id' => $request->employee_id,
                        'employee_salary_id' => $salary->id,
                        'manual_amount' => $this->getDeductionAmount($basicDeduction, $request->manual_deductions[$basicDeduction] ?? null),
                        'basic_deduction_id' => $basicDeduction
                    ];
                    HrmsEmployeeDeduction::create($basicDeductionData);
                }
            }
            //deleted_children
            if (isset($request->deleted_children)) {
                foreach ($request->deleted_children as $deletedChild) {
                    if ($deletedChild != null) {
                        HrmsEmployeeChild::where('id', $deletedChild)->delete();
                        HrmsEmployeeFeeEMI::where('employee_child_id', $deletedChild)->delete();
                    }

                }
            }
            if ($request->has('has_children') && $request->has('child_select') && $request->has('child_amount')) {
                // HrmsEmployeeChild::where('employee_id', $salary->employee_id)
                //     ->where('employee_salary_id', $salary->id)
                //     ->delete();

                foreach ($request->child_select as $index => $childId) {
                    //check if alrady exist
                    $child = HrmsEmployeeChild::where('student_id', $childId)
                        ->where('employee_salary_id', $salary->id)
                        ->first();
                    if ($child) {
                        //recreate emis and reschdules emis
                        // $child->update([
                        //     'fee_amount' => $request->child_amount[$index],
                        //     'emi_amount' => $request->emi_amount[$index]
                        // ]);
                    } else {
                        $childData = [
                            'employee_id' => $request->employee_id,
                            'student_id' => $childId,
                            'fee_amount' => $request->child_amount[$index],
                            'employee_salary_id' => $salary->id,
                            'emi_amount' => $request->emi_amount[$index]
                        ];
                        $employeeChild = HrmsEmployeeChild::create($childData);
                        $emis = $this->calculateFeeEMIs($request->child_amount[$index], $salary->effective_date, $request->emi_amount[$index]); // Custom function to calculate EMIs
                        foreach ($emis as $emi) {
                            HrmsEmployeeFeeEMI::create([
                                'employee_salary_id' => $salary->id,
                                'student_id' => $childId,
                                'employee_child_id' => $employeeChild->id,
                                'emi_date' => $emi['date'],
                                'emi_amount' => $emi['amount'],
                                'status' => 'pending'
                            ]);
                        }
                    }

                }
            }
            if (!$request->has('has_children')) {
                $childIds = HrmsEmployeeChild::where('employee_salary_id', $salary->id)->pluck('id');
                HrmsEmployeeFeeEMI::whereIn('employee_child_id', $childIds)->delete();
                HrmsEmployeeChild::where('employee_salary_id', $salary->id)->delete();
            }
            return redirect()->route('salaries')->with('success', 'salaries has been Updated successfully.');
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    private function trimSalaryInputs(Request $request)
    {
        $childRows = collect($request->child_select ?? [])
            ->map(function ($childId, $index) use ($request) {
                return [
                    'child_select' => is_string($childId) ? trim($childId) : $childId,
                    'child_amount' => is_string($request->child_amount[$index] ?? null)
                        ? trim($request->child_amount[$index])
                        : ($request->child_amount[$index] ?? null),
                    'emi_amount' => is_string($request->emi_amount[$index] ?? null)
                        ? trim($request->emi_amount[$index])
                        : ($request->emi_amount[$index] ?? null),
                ];
            })
            ->filter(function ($row) {
                return $row['child_select'] !== null
                    && $row['child_select'] !== ''
                    && $row['child_amount'] !== null
                    && $row['child_amount'] !== ''
                    && $row['emi_amount'] !== null
                    && $row['emi_amount'] !== '';
            })
            ->values();

        $request->merge([
            'id' => is_string($request->id) ? trim($request->id) : $request->id,
            'basic_salary' => is_string($request->basic_salary) ? trim($request->basic_salary) : $request->basic_salary,
            'effective_date' => is_string($request->effective_date) ? trim($request->effective_date) : $request->effective_date,
            'child_select' => $childRows->pluck('child_select')->all(),
            'child_amount' => $childRows->pluck('child_amount')->all(),
            'emi_amount' => $childRows->pluck('emi_amount')->all(),
        ]);

        if ($childRows->isEmpty()) {
            $request->request->remove('has_children');
        }
    }

    private function getDeductionAmount($basicDeductionId, $manualAmount)
    {
        if ($manualAmount !== null && $manualAmount !== '') {
            return $manualAmount;
        }

        return HrmsBasicDeduction::where('id', $basicDeductionId)->value('amount') ?? 0;
    }

    public function delete($id)
    {
        $stream = HrmsSalary::findOrFail($id);
        $stream->employeeChilds()->delete();
        $stream->employeeDeductions()->delete();
        $stream->delete();
        return redirect()->route('salaries')->with('success', 'Deleted successfully.');
    }

    public function importSalaries(Request $request)
    {
        \Log::info("Import Salary");
        // $request->validate([
        //     'file' => 'required|file|mimes:csv,xlsx|max:2048', // CSV or XLSX, max 2MB
        // ]);
        \Log::info("Import");
        try {
            \Log::info("Importq");
            $file = $request->file('file');
            $path = $file->getRealPath();
            $extension = $file->getClientOriginalExtension();
            \Log::info( $extension);
            if ($extension === 'csv') {
                $this->importCSV($path);
            } else {
                return response()->json(['error' => 'Invalid file format'], 422);
            }

            return redirect()->back()->with('success', 'Salaries imported successfully!');
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return response()->json(['error' => 'File processing failed', 'details' => $e->getMessage()], 500);
        }
    }

    private function importCSV($path)
    {
        if (!file_exists($path)) {
            throw new \Exception("File not found: $path");
        }

        DB::beginTransaction();

        try {
            $handle = fopen($path, "r");
            if ($handle === FALSE) {
                return;
            }

            // Read the first row as headers and normalize them
            $headers = fgetcsv($handle);
            $headers = array_map(fn($header) => strtolower(str_replace(' ', '_', trim($header))), $headers);


            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Map row data dynamically using the headers
                $mappedRow = array_combine($headers, $row);
                if (!$mappedRow) {
                    continue; // Skip row if mapping fails
                }

                // Create Salary Entry
                $salaryData = [
                    'employee_id' => $mappedRow['employee_id'],
                    'basic_salary' => $mappedRow['basic_salary'],
                    'effective_date' =>$this->convertToDate($mappedRow['effective_date'], 'csv'), 
                    'is_active' => isset($mappedRow['is_active']) ? 1 : 0
                ];
                $salary = HrmsSalary::create($salaryData);

                // Handle Multiple Deductions
                $deductions = [];
                foreach ($mappedRow as $key => $value) {
                    if (strpos($key, 'deduction_') === 0 && !empty($value)) {
                        // Get deduction number (e.g., deduction_1 → 1)
                        $deductionIndex = str_replace('deduction_', '', $key);
                        $manualAmountKey = "manual_deduction_{$deductionIndex}";

                        $deductions[] = [
                            'employee_id' => $mappedRow['employee_id'],
                            'basic_deduction_id' => $value,
                            'manual_amount' => $this->getDeductionAmount($value, $mappedRow[$manualAmountKey] ?? null),
                            'employee_salary_id' => $salary->id
                        ];
                    }
                }

                // Insert deductions in bulk
                if (!empty($deductions)) {
                    HrmsEmployeeDeduction::insert($deductions);
                }
            }

            fclose($handle);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('CSV Import Error: ' . $e->getMessage());
            throw $e;
        }
    }
    private function convertToDate($dateValue, $fileType)
    {
        return $fileType === 'csv' ? Carbon::parse($dateValue) : null;
    }
}
