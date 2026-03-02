<?php

namespace App\Http\Controllers;

use App\Models\HrmsEmployee;
use App\Models\HrmsEmployeeSecurityDeposit;
use App\Models\HrmsEmployeeSecurityDepositEmi;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmployeeSecurityDepositController extends Controller
{
    public function index()
    {
        $employees = HrmsEmployee::on('dynamic')->get();
        $deposits = HrmsEmployeeSecurityDeposit::on('dynamic')
            ->with('employee')
            ->orderByDesc('id')
            ->get();

        return view('backend.HRMS.employee_security_deposit', compact('employees', 'deposits'));
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer',
            'security_deposit' => 'required|numeric',
            'duration_months' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'status' => 'required|in:pending,completed,refund',
        ]);

        $deposit = HrmsEmployeeSecurityDeposit::on('dynamic')->updateOrCreate(
            ['id' => $request->id],
            [
                'employee_id' => $request->employee_id,
                'security_deposit' => $request->security_deposit,
                'duration_months' => $request->duration_months,
                'start_date' => $request->start_date,
                'description' => $request->description,
                'is_active' => $request->has('is_active') ? 1 : 0,
                'status' => $request->status,
            ]
        );

        if (!$request->id) {
            $this->generateEmiSchedule($deposit);
        }

        return redirect()->route('employee_security_deposit')->with('success', 'Security deposit saved successfully.');
    }

    protected function generateEmiSchedule(HrmsEmployeeSecurityDeposit $deposit)
    {
        $amountPerEmi = round($deposit->security_deposit / $deposit->duration_months, 2);
        $startDate = Carbon::parse($deposit->start_date);

        for ($i = 0; $i < $deposit->duration_months; $i++) {
            HrmsEmployeeSecurityDepositEmi::on('dynamic')->create([
                'security_deposit_id' => $deposit->id,
                'emi_date' => $startDate->copy()->addMonths($i),
                'emi_amount' => $amountPerEmi,
                'status' => 'pending',
            ]);
        }
    }

    public function view($id)
    {
        $deposit = HrmsEmployeeSecurityDeposit::on('dynamic')
            ->with(['employee', 'emis' => function ($query) {
                $query->orderBy('emi_date', 'asc');
            }])
            ->findOrFail($id);

        return view('backend.HRMS.security_deposit_view', compact('deposit'));
    }

    public function delete($id)
    {
        HrmsEmployeeSecurityDeposit::on('dynamic')->findOrFail($id)->delete();
        HrmsEmployeeSecurityDepositEmi::on('dynamic')->where('security_deposit_id', $id)->delete();

        return back()->with('success', 'Security deposit and EMIs deleted successfully.');
    }

    public function pauseEmi($id)
    {
        $emi = HrmsEmployeeSecurityDepositEmi::on('dynamic')->findOrFail($id);
        $emi->status = 'paused';
        $emi->save();

        return back()->with('success', 'EMI paused successfully.');
    }

    public function resumeEmi($id)
    {
        $emi = HrmsEmployeeSecurityDepositEmi::on('dynamic')->findOrFail($id);
        $emi->status = 'pending';
        $emi->save();

        return back()->with('success', 'EMI resumed successfully.');
    }

    public function markAsPaid($id)
    {
        $emi = HrmsEmployeeSecurityDepositEmi::on('dynamic')->findOrFail($id);
        $emi->status = 'paid';
        $emi->save();

        return back()->with('success', 'EMI marked as paid.');
    }
}
