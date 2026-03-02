<?php

namespace App\Http\Controllers;
use App\Models\CommanModel;
use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeLoan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\HrmsEmployeeLoanEmi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class EmployeeLoanController extends Controller
{


    public function index()
    {
        $stream = HrmsEmployeeLoan::with('employee')->get();
        $employeeName = HrmsEmployee::all();
        return view('backend.HRMS.employeeloan', compact('stream', 'employeeName'));
    }


    public function create(Request $request)
	{
		DB::beginTransaction();

		try {
			$validator = Validator::make($request->all(), [
				'employee_id' => 'required|integer|exists:hrms_employees,id',
				'loan_amount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
				'duration_months' => [
    'required',
    'integer',
    function ($attribute, $value, $fail) use ($request) {
        if (in_array($request->status, ['completed', 'refund']) && $value != 0) {
            $fail('Duration must be 0 for completed or refund status.');
        } elseif ($request->status === 'pending' && $value < 1) {
            $fail('Duration must be at least 1 for pending status.');
        }
    },
],

				'description' => 'nullable|string|max:255',
				'start_date' => 'required|date',
				'status' => 'required|in:pending,completed,refund',
				'payment_mode' => 'nullable|string|max:255',
				'refund_amount' => 'nullable|numeric',
				'refund_date' => 'nullable|date'
			]);

			if ($validator->fails()) {
				return redirect()->back()
					->withErrors($validator)
					->withInput();
			}

			$loanAmount = $request->loan_amount;
			$durationMonths = $request->duration_months;
			$startDate = Carbon::parse($request->start_date);
			$status = $request->status;

			$emiAmount = $status === 'pending'
				? $this->calculateEmi($loanAmount, $durationMonths)
				: 0;

			// Additional validation rules based on status
			if ($request->status === 'completed' && empty($request->payment_mode)) {
				return redirect()->back()->with('error', 'Payment mode is required for completed status.');
			}

			if ($request->status === 'refund') {
				if (empty($request->refund_amount) || empty($request->refund_date) || empty($request->payment_mode)) {
					return redirect()->back()->with('error', 'Refund details are required for refund status.');
				}
			}


			$loan = HrmsEmployeeLoan::create([
				'employee_id' => $request->employee_id,
				'loan_amount' => $loanAmount,
				'emi_amount' => $emiAmount,
				'interest_rate' => 0,
				'start_date' => $startDate,
				'duration_months' => $status === 'completed' ? 0 : $durationMonths,
				'original_duration_months' => $durationMonths,
				'description' => $request->description,
				'status' => $status,
				'payment_mode' => $request->payment_mode,
				'refund_amount' => $request->refund_amount,
				'refund_date' => $request->refund_date,
				'is_active' => isset($request->is_active) ? 1 : 0
			]);

			if ($status === 'pending') {
				$this->generateEmis($loan);
			}

			DB::commit();
			return redirect()->route('employeeloan')->with('success', 'Employee Loan has been created successfully.');
		} catch (\Exception $e) {
			DB::rollBack();
			\Log::error('Error generating loan: ' . $e->getMessage());
			return redirect()->back()->with('error', 'An error occurred while processing your request.');
		}
	}


    // Pause EMI
    public function pauseEmi(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'emi_id' => 'required|exists:hrms_employee_loan_emis,id',
            'cash_amount' => 'nullable|numeric|min:0', // Cash is optional
            'payment_method' => 'nullable|string|in:cash,credit',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $emi = HrmsEmployeeLoanEmi::findOrFail($request->emi_id);
        $loan = $emi->loan;

        if ($request->cash_amount) {
            $emi->update([
                'status' => 'paid',
                'payment_mode' => 'cash',
                'paid_amount' => $request->cash_amount,
            ]);
            if($request->cash_amount < $emi->emi_amount){
                //$loan->increment('duration_months');
                //$loan->update(['end_date' => Carbon::parse($loan->end_date)->addMonth()]);
                $emi->principal_component=$request->cash_amount;
                $emi->save();
                $this->recalculateEmiSchedule($loan);
            }
        } else {
            $emi->update(['status' => 'paused', 'notes' => 'Paused without payment.']);
            //$loan->increment('duration_months');
            //$loan->update(['end_date' => Carbon::parse($loan->end_date)->addMonth()]);
            $this->recalculateEmiSchedule($loan);
        }
        return response()->json([
            'success' => true,
            'emi_id' => $emi->id,
            'message' => 'EMI has been paused and schedule recalculated.'
        ]);
        //return redirect()->route('employeeloan')->with('success', 'EMI updated successfully.');
    }

    // Recalculate EMI Schedule
    private function recalculateEmiSchedule(HrmsEmployeeLoan $loan)
    {
        $remainingBalance = $loan->loan_amount - HrmsEmployeeLoanEmi::where('loan_id', $loan->id)
            ->where('status', 'paid')
            ->sum('principal_component');

        $remainingMonths = $loan->duration_months - HrmsEmployeeLoanEmi::where('loan_id', $loan->id)
            ->whereIn('status', ['paid', 'paused'])
            ->count();

        // $monthlyInterestRate = $loan->interest_rate / 12 / 100;
        $emiAmount = $this->calculateEmi($remainingBalance, $remainingMonths);

        HrmsEmployeeLoanEmi::where('loan_id', $loan->id)->where('status', 'pending')->delete();

        $currentDate = Carbon::parse(HrmsEmployeeLoanEmi::where('loan_id', $loan->id)
            ->whereIn('status', ['paid', 'paused'])
            ->max('emi_date'))->addMonth();

        $emiSchedule = [];
        for ($i = 0; $i < $remainingMonths; $i++) {
            // $interestComponent = $remainingBalance * $monthlyInterestRate;
            $principalComponent = $emiAmount;// - $interestComponent;

            $emiSchedule[] = [
                'loan_id' => $loan->id,
                'emi_date' => $currentDate->toDateString(),
                'emi_amount' => $emiAmount,
                'principal_component' => $principalComponent,
                'interest_component' =>0,// $interestComponent,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $remainingBalance -= $principalComponent;
            $currentDate->addMonth();
        }

        HrmsEmployeeLoanEmi::insert($emiSchedule);
    }

    // Calculate EMI
    private function calculateEmi($loanAmount, $durationMonths)
    {
        // return ($loanAmount * $interestRate * pow(1 + $interestRate, $durationMonths)) /
        //     (pow(1 + $interestRate, $durationMonths) - 1);
            return $loanAmount / $durationMonths;
    }

    // Generate EMI Schedule
    private function generateEmis(HrmsEmployeeLoan $loan)
    {
        $remainingBalance = $loan->loan_amount;
        // $monthlyInterestRate = $loan->interest_rate / 12 / 100;
        $emiAmount = $loan->emi_amount;

        $startDate = Carbon::parse($loan->start_date);
        $emiSchedule = [];

        for ($i = 0; $i < $loan->duration_months; $i++) {
            // $interestComponent = $remainingBalance * $monthlyInterestRate;
            $principalComponent = $emiAmount;// - $interestComponent;

            $emiSchedule[] = [
                'loan_id' => $loan->id,
                'emi_date' => $startDate->toDateString(),
                'emi_amount' => $emiAmount,
                'principal_component' => $principalComponent,
                'interest_component' =>0,// $interestComponent,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $remainingBalance -= $principalComponent;
            $startDate->addMonth();
        }

        HrmsEmployeeLoanEmi::insert($emiSchedule);
    }

    public function view($id)
    {
        // $stream_master = HrmsEmployeeLoan::whereId($id)->get();
        $stream_master = HrmsEmployeeLoan::with('emis')->findOrFail($id);
        // $stream = HrmsEmployeeLoan::orderBy('HolidayID','desc')->paginate(5);
        $stream = HrmsEmployeeLoan::with('employee')->get();
        $employeeName = HrmsEmployee::all();
        return view('backend.HRMS.employeeloan', compact('stream_master', 'stream', 'employeeName'));
    }

    public function store(Request $request)
{
    $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'loan_type' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
        'status' => 'required|in:pending,completed,refund',
        'payment_mode' => 'nullable|string|max:255',
        'refund_amount' => 'nullable|numeric|min:0',

        // Conditional validation
        'start_date' => [
            'nullable',
            function ($attribute, $value, $fail) use ($request) {
                if (in_array($request->status, ['pending', 'completed']) && empty($value)) {
                    $fail('Start date is required for pending or completed status.');
                }
            },
        ],
        'refund_date' => [
            'nullable',
            function ($attribute, $value, $fail) use ($request) {
                if ($request->status === 'refund' && empty($value)) {
                    $fail('Refund date is required for refund status.');
                }
            },
        ],

        'duration_months' => [
            'required',
            'integer',
            function ($attribute, $value, $fail) use ($request) {
                if (in_array($request->status, ['completed', 'refund']) && $value != 0) {
                    $fail('Duration must be 0 for completed or refund status.');
                } elseif ($request->status === 'pending' && $value < 1) {
                    $fail('Duration must be at least 1 for pending status.');
                }
            },
        ],
    ]);

    // Remaining logic (same as before)
    $loan = EmployeeLoan::create([
        'employee_id' => $request->employee_id,
        'loan_type' => $request->loan_type,
        'amount' => $request->amount,
        'start_date' => $request->start_date,
        'duration_months' => in_array($request->status, ['completed', 'refund']) ? 0 : $request->duration_months,
        'status' => $request->status,
        'payment_mode' => $request->payment_mode,
        'refund_amount' => $request->refund_amount,
        'refund_date' => $request->refund_date,
    ]);

    if ($request->status === 'pending') {
        $this->generateEmis($loan);
    }

    return redirect()->route('employee-loan.index')->with('success', 'Loan added successfully');
}


    protected function loanDetailsChanged($loan, $originalLoanAmount, $originalEmiAmount, $originalDurationMonths, $originalStartDate)
    {
        return $loan->loan_amount != $originalLoanAmount ||
            $loan->emi_amount != $originalEmiAmount ||
            // $loan->interest_rate != $originalInterestRate ||
            $loan->duration_months != $originalDurationMonths ||
            $loan->start_date != $originalStartDate;
    }
    protected function recalculateEmis(HrmsEmployeeLoan $loan, $originalLoanAmount, $originalEmiAmount, $originalDurationMonths)
    {
        // If loan amount, interest rate, or duration is updated, recalculate EMIs
        if ($loan->loan_amount != $originalLoanAmount  || $loan->duration_months != $originalDurationMonths) {
            // || $loan->interest_rate != $originalInterestRate
            // Recalculate EMI using the formula: EMI = [P * r * (1+r)^n] / [(1+r)^n-1]
            $principal = $loan->loan_amount;
            // $rate = $loan->interest_rate / 100 / 12; // Monthly interest rate
            $duration = $loan->duration_months;

            // EMI formula (without rounding off to prevent precision issues)
            // $emi = ($principal * $rate * pow(1 + $rate, $duration)) / (pow(1 + $rate, $duration) - 1);
            $emi = $principal / $duration;
            // Update the EMI in the loan model
            $loan->emi_amount = round($emi, 2);
            $loan->save();

            // Recalculate the EMIs for the loan after the update
            $this->recreateEmis($loan);
        }
    }

    protected function recreateEmis(HrmsEmployeeLoan $loan)
    {
        // Get the paid, skipped, and pending EMIs
        $paidEmis = $loan->emis()->where('status', 'paid')->get();
        $skippedEmis = $loan->emis()->where('status', 'paused')->get();
        $pendingEmis = $loan->emis()->where('status', 'pending')->get();

        // Recreate pending or skipped EMIs only
        foreach ($pendingEmis as $emi) {
            // No need to update or recreate paid EMIs, leave them as is.
            $emi->status = 'recalculated'; // Optionally mark recalculated or update fields if necessary
            $emi->save();
        }

        // Now create the remaining EMIs
        $startDate = $loan->start_date;
        $emiAmount = $loan->emi_amount;
        $remainingMonths = $loan->duration_months - count($paidEmis) - count($skippedEmis);  // Remaining months after excluding paid and skipped

        for ($i = 0; $i < $remainingMonths; $i++) {
            $startDate = Carbon::parse($startDate);
            $emiDate = $startDate->addMonth();
            $emi = new HrmsEmployeeLoanEmi([
                'emi_date' => $emiDate,
                'emi_amount' => $emiAmount,
                'principal_component' => $emiAmount - ($emiAmount * $loan->interest_rate / 100),
                'interest_component' =>0,// $emiAmount * $loan->interest_rate / 100,
                'status' => 'pending'
            ]);
            $loan->emis()->save($emi);
        }
    }

    public function delete($id)
    {
        $stream = HrmsEmployeeLoan::findOrFail($id);
        $stream->emis()->delete();
        $stream->delete();
        return redirect()->route('employeeloan')->with('success', 'Deleted successfully.');
    }

}
