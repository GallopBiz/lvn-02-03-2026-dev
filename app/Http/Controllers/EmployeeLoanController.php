<?php

namespace App\Http\Controllers;
use App\Models\CommanModel;
use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeLoan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\HrmsEmployeeLoanEmi;
use App\Models\HrmsSecurityDepositeRefund;
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
            'status' => 'required|in:pending,completed',
            'description' => 'nullable|string|max:255',
            'start_date' => 'required|date',
        ]);

        // Add validation conditionally
        if ($request->status === 'pending') {
            $validator->after(function ($validator) use ($request) {
                if (empty($request->duration_months)) {
                    $validator->errors()->add('duration_months', 'The duration is required for pending status.');
                }
            });
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $loanAmount = $request->loan_amount;
        $startDate = Carbon::parse($request->start_date);
        $status = $request->status;

        $durationMonths = $status === 'pending' ? $request->duration_months : 0;

        $emiAmount = $status === 'pending'
            ? $this->calculateEmi($loanAmount, $durationMonths)
            : 0;

        $loan = HrmsEmployeeLoan::create([
            'employee_id' => $request->employee_id,
            'loan_amount' => $loanAmount,
            'emi_amount' => $emiAmount,
            'interest_rate' => 0,
            'start_date' => $startDate,
            'duration_months' => $durationMonths,
            'original_duration_months' => $durationMonths,
            'description' => $request->description,
            'status' => $status,
            'is_active' => $status === 'pending' ? (isset($request->is_active) ? 1 : 0) : 0,
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
		DB::beginTransaction();

		try {
			// Common validation rules
			$rules = [
				'employee_id' => 'required|integer|exists:hrms_employees,id',
				'loan_amount' => 'required|regex:/^\d+(\.\d{1,2})?$/',
				'status' => 'required|in:pending,completed',
				'description' => 'nullable|string|max:255',
				'start_date' => 'required|date',
			];

			// Add conditional rules based on loan status
			if ($request->status === 'pending') {
				$rules['duration_months'] = 'required|integer|min:1';
			} else {
				// For completed loans, duration is not needed
				$rules['duration_months'] = 'nullable';
			}

			$validator = Validator::make($request->all(), $rules);

			if ($validator->fails()) {
				return redirect()->back()
					->withErrors($validator)
					->withInput();
			}

			// Fetch the existing loan record
			$loan = HrmsEmployeeLoan::findOrFail($request->id);

			// Store original values
			$originalLoanAmount = $loan->loan_amount;
			$originalEmiAmount = $loan->emi_amount;
			$originalDurationMonths = $loan->duration_months;
			$originalStartDate = $loan->start_date;

			// Update loan fields
			$loan->employee_id = $request->employee_id;
			$loan->loan_amount = $request->loan_amount;
			$loan->start_date = $request->start_date;
			$loan->description = $request->description;
			$loan->status = $request->status;

			if ($request->status === 'pending') {
				$loan->duration_months = $request->duration_months;
				$loan->is_active = $request->has('is_active') ? 1 : 0;
			} else {
				// For completed, set duration and is_active to null/inactive
				$loan->duration_months = null;
				$loan->is_active = 0;
			}

			// Optionally recalculate EMI (for pending only)
			if (
				$request->status === 'pending' &&
				$this->loanDetailsChanged($loan, $originalLoanAmount, $originalEmiAmount, $originalDurationMonths, $originalStartDate)
			) {
				$this->recalculateEmis($loan, $originalLoanAmount, $originalEmiAmount, $originalDurationMonths);
			}

			$loan->save();

			DB::commit();

			return redirect()->route('employeeloan')->with('success', 'Loan has been updated successfully.');
		} catch (\Exception $e) {
			DB::rollBack();
			\Log::error('Error updating loan: ' . $e->getMessage());

			return redirect()->back()->with('error', 'An error occurred while processing your request.');
		}
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
	
	public function showRefundForm(Request $request)
{
    // Step 1: Get all refunded loan IDs
    $refundedLoanIds = HrmsSecurityDepositeRefund::pluck('loan_id')->toArray();

    // Step 2: Get loans with 'completed' status but NOT refunded yet
    $loans = HrmsEmployeeLoan::with('employee')
                ->where('status', 'completed')
                ->whereNotIn('id', $refundedLoanIds)
                ->get();

    // Step 3: Get all existing refunds
    $refunds = HrmsSecurityDepositeRefund::with('employee', 'loan')->get();

    // Step 4: If editing, find the refund
    $editRefund = null;
    if ($request->has('edit_id')) {
        $editRefund = HrmsSecurityDepositeRefund::find($request->edit_id);

        // 💡 Add the edited loan back into the list so it shows in dropdown
        if ($editRefund && $editRefund->loan) {
            $loans->push($editRefund->loan->load('employee'));
        }
    }

    return view('backend.HRMS.security_refund', compact('loans', 'refunds', 'editRefund'));
}

public function submitRefund(Request $request)
{
    $request->validate([
        'employee_id' => 'required|exists:hrms_employees,id',
        'loan_id' => 'required|exists:hrms_employee_loans,id',
        'refund_amount' => 'required|numeric',
        'refund_date' => 'required|date',
        'description' => 'nullable|string|max:255',
    ]);

    HrmsSecurityDepositeRefund::create([
        'employee_id' => $request->employee_id,
        'loan_id' => $request->loan_id,
        'refund_amount' => $request->refund_amount,
        'refund_date' => $request->refund_date,
        'description' => $request->description,
        'status' => 'refunded',
    ]);

    return redirect()->route('security.refund.form')->with('success', 'Refund submitted successfully.');
}

public function updateRefund(Request $request, $id)
{
    $request->validate([
        'refund_amount' => 'required|numeric',
        'refund_date' => 'required|date',
        'description' => 'nullable|string|max:255',
    ]);

    $refund = HrmsSecurityDepositeRefund::findOrFail($id);

    $refund->update([
        'refund_amount' => $request->refund_amount,
        'refund_date' => $request->refund_date,
        'description' => $request->description,
    ]);

    return redirect()->route('security.refund.form')->with('success', 'Refund updated successfully.');
}

public function deleteRefund($id)
{
    $refund = HrmsSecurityDepositeRefund::findOrFail($id);
    $refund->delete();

    return redirect()->route('security.refund.form')->with('success', 'Refund deleted successfully.');
}

}
