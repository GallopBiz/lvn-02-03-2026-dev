@extends('backend.layouts.main')
@section('main-container')

<style>
    .print-wrapper {
        border: 1px solid #e4e0e0;
        width: 70%;
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        box-sizing: border-box;
        font-family: 'Arial', sans-serif;
        font-size: 12px;
    }

    .header {
        text-align: center;
        font-size: 11px;
        line-height: 1.3;
        margin-bottom: 10px;
    }

    .school-logo {
        max-width: 225px;
        height: auto;
        margin: 0 auto 5px auto;
        display: block;
    }

    .red-underline {
        width: 100%;
        border-bottom: 2px solid red;
        margin: 10px 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        margin-bottom: 10px;
    }

    th, td {
        border: 1px solid #ccc;
        padding: 1px 10px;
        text-align: left;
        vertical-align: middle;
    }

    th {
        background-color: #f8f8f8;
    }

    td.amount {
        text-align: right;
    }

    .section-table {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-top: 0px;
        margin-bottom: 0px;
    }

    .section-table table {
        width: 100%;
        margin-top: 0px;
    }

    .approval-section {
        text-align: right;
        font-size: 13px;
    }

    .summary-table {
        table-layout: fixed;
        width: 100%;
    }

    .summary-table td {
        font-weight: 600;
        width: 25%;
    }

    @media print {
        @page {
            margin: 0 !important;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
        }

        .print-wrapper {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .section-table {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .section-table table {
            margin-top: 0 !important;
        }
		body {
        padding-left: 50px;
        padding-right: 50px;
    }

    .print-area {
        padding-left: 25px !important;
        padding-right: 25px !important;
        box-sizing: border-box;
    }
    }
</style>

@php
    function getD($d, $k) {
        foreach ($d as $item) {
            if (($item['name'] ?? '') === $k) {
                return (float)$item['amount'];
            }
        }
        return 0;
    }

    $bank = optional($report->employee->bankDetails->first());
    $statutory = optional($report->employee->statutoryInformation);
@endphp

<div class="main-content">
    <div id="printme">
        <div class="print-wrapper">
            <div class="header">
                <img src="{{ asset('assets/backend/images/LVN-logo.png') }}" alt="School Logo" class="school-logo">
                <div>KESAR BAGH ROAD, LOKMANYA NAGAR, INDORE (M.P.)</div>
                <div>Phone: 0731-2360169 | Email: lvnschoolofficial@gmail.com</div>
                <div><strong>Salary Slip</strong></div>
                <div><strong>Month:</strong> {{ $monthName }} {{ $report->year }}</div>
            </div>

            <div class="red-underline"></div>

            <table class="salary-info-table">
                <colgroup>
                    <col style="width: 40%;">
                    <col style="width: 22%;">
                    <col style="width: 25%;">
                </colgroup>
                <tr>
                    <td><strong>Employee Name:</strong> {{ $report->employee->first_name ?? '' }} {{ $report->employee->last_name ?? '' }}</td>
                    <td><strong>Emp ID:</strong> {{ $report->employee->biometricDetail->ess_emp_code ?? 'N/A' }}</td>
                    <td><strong>Designation:</strong> {{ $report->employee->position->position_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Bank A/C No:</strong> {{ $bank->account_number ?? '-' }}</td>
                   @php
    $monthDays = $report->month_days ?? cal_days_in_month(
        CAL_GREGORIAN,
        $report->month,
        $report->year
    );

    $approvedLeaves   = (int) ($report->approved_leaves ?? 0);
    $unapprovedLeaves = (int) ($report->unapproved_leaves ?? 0);
    $lwpDays          = (int) ($report->lwp_days ?? 0);

    // Approved leaves are PAID
    // Only LWP + Unapproved reduce paid days
    $paidDays = $monthDays - ($lwpDays + $unapprovedLeaves);

    if ($paidDays < 0) {
        $paidDays = 0;
    }
@endphp
					<td><strong>Paid Days:</strong> {{ $paidDays }}</td>
                    <td><strong>Approved Leaves:</strong> {{ $approvedLeaves }}</td>
                </tr>
                <tr>
                    <td><strong>UAN No:</strong> {{ $statutory->uan_number ?? '-' }}</td>
                    <td><strong>ESIC No:</strong> {{ $statutory->esic_number ?? '-' }}</td>
                    <td><strong>LWP:</strong> {{ $lwpDays }}</td>
                </tr>
            </table>

            <div class="section-table">
                {{-- Additions --}}
                <table>
                    <tr><th colspan="2">Additions</th></tr>
                    <tr><td>Basic Pay</td><td class="amount">{{ round(($report->gross_salary ?? 0) * 0.50) }}</td></tr>
                    <!--<tr><td>Special Pay</td><td class="amount">{{ number_format($additions['SPECIAL PAY'] ?? 0, 2) }}</td></tr>
                    <tr><td>Allow.</td><td class="amount">{{ number_format($additions['Allow.'] ?? 0, 2) }}</td></tr>-->
                    <tr><td>H.R.A</td><td class="amount">{{ round($report->hra ?? 0) }}</td></tr>
                    <tr><td>D.A</td><td class="amount">{{ round($report->da ?? 0) }}</td></tr>
                    <!--<tr><td>Conv. Reimb.</td><td class="amount">{{ number_format($additions['CONV. REIMBURSEMENT'] ?? 0, 2) }}</td></tr>
                    <tr><td>Wash Allow</td><td class="amount">{{ number_format($additions['WASH ALLOW'] ?? 0, 2) }}</td></tr>
                    <tr><td>Milk Tea</td><td class="amount">{{ number_format($additions['MILK TEA'] ?? 0, 2) }}</td></tr>-->
                </table>

                {{-- Deductions --}}
                <table>
                    <tr><th colspan="2">Deductions</th></tr>
                    <tr><td>EPFA</td><td class="amount">{{ round($report->epfa ?? 0) }}</td></tr>
                    <tr><td>ESIC</td><td class="amount">{{ round($report->esic ?? 0) }}</td></tr>
                    <tr><td>Lunch Allow</td><td class="amount">{{ round(getD($deductions, 'Lunch')) }}</td></tr>
                    <tr><td>Security Deposit</td><td class="amount">{{ round(getD($deductions, 'Secr. Dep.')) }}</td></tr>
                    <tr><td>Grain Loan</td><td class="amount">{{ round(getD($deductions, 'Grain Loan')) }}</td></tr>
                    <tr><td>TDS</td><td class="amount">{{ round(getD($deductions, 'TDS')) }}</td></tr>
                    <tr><td>Professional Tax</td><td class="amount">
                        {{ round(getD($deductions, 'Professional Tax')) }}
                    </td></tr>
                    <tr><td>Other Deduction</td><td class="amount">{{ round(getD($deductions, 'Misc')) }}</td></tr>
                    <tr><td>Staff Ward</td><td class="amount">{{ round(getD($deductions, 'staff ward')) }}</td></tr>
                </table>
            </div>
			@php
    // Sum all JSON based deductions
    $jsonDeductionTotal = 0;

    if (!empty($deductions) && is_array($deductions)) {
        foreach ($deductions as $row) {
            $jsonDeductionTotal += (float) ($row['amount'] ?? 0);
        }
    }

    // Add column based deductions
    // For display, exclude late coming deduction (lwp) from total
    $displayTotalDeductions =
        $jsonDeductionTotal +
        ($report->epfa ?? 0) +
        ($report->esic ?? 0)
        // do not add late_deduction_amount here
        ;
@endphp

            <table class="summary-table">
                <tr>
                    <td>Gross:</td>
                    <td style="text-align: right;">{{ round($report->gross_salary ?? 0) }}</td>
                    <td>Total Deductions:</td>
                    <td style="text-align: right;">{{ round($displayTotalDeductions) }}</td>
                </tr>
                <tr>
                    <td>Earned Gross:</td>
                    @php
                        // Prefer payroll-calculated earn_salary if available
                        $earnedSalary = $report->earn_salary ?? null;
                        if ($earnedSalary === null) {
                            $basic = $report->basic_salary ?? 0;
                            $hra = $report->hra ?? 0;
                            $da = $report->da ?? 0;
                            $monthDays = $report->month_days ?? 30;
                            $lwp = $report->lwp_days ?? 0;
                            $paidDays = $monthDays - $lwp;
                            $earnBase = $basic + $hra + $da;
                            $earnedSalary = $monthDays > 0 ? round($earnBase * ($paidDays / $monthDays)) : 0;
                        }
                    @endphp
                    <td style="text-align: right;">{{ round($earnedSalary) }}</td>

                    <td>Net Salary:</td>
                    <td style="text-align: right;">{{ round($report->net_salary ?? 0) }}</td>
                </tr>
            </table>

            <p style="font-size:12px; margin-top:-10px;"><em>Note: This is a system generated salary slip.</em></p>

            <div class="approval-section">
                <div>.............................................</div>
                <div><strong>Principal</strong></div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center gap-2 mt-3 mb-3">
    <button class="btn btn-sm btn-success" onclick="printDiv('printme')">Print Salary Slip</button>
    <a href="https://school.lvnindore.org.in/employee-salary-report" class="btn btn-sm btn-secondary">Back</a>
	</div>


</div>

<script>
function printDiv(divId) {
    const content = document.getElementById(divId).cloneNode(true);
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    printWindow.document.open();
    printWindow.document.write(`
        <html>
        <head>
            <title>Print Salary Slip</title>
            <style>
                @page { margin: 0 !important; }
                body { font-family: Arial, sans-serif; font-size: 12px; margin: 0 !important; padding: 0 !important; padding-left: 40px !important; padding-right: 40px !important; }
                .print-wrapper { border: 1px solid #e4e0e0; width: 100%; padding: 10px; box-sizing: border-box; }
                table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 10px; }
                th, td { border: 1px solid #ccc; padding: 1px 10px; text-align: left; vertical-align: middle; }
                th { background-color: #f8f8f8; }
                td.amount { text-align: right; }
                .section-table { display: flex; justify-content: space-between; gap: 10px; margin-top: 0 !important; }
                .section-table table { width: 100%; margin-top: 0 !important; }
                .approval-section { text-align: right; font-size: 13px; margin-top: 20px; }
                .summary-table { table-layout: fixed; width: 100%; }
                .summary-table td { font-weight: 600; width: 25%; }
                .school-logo { max-width: 225px; height: auto; margin: 0 auto 5px auto; display: block; }
                .header { text-align: center; font-size: 12px; line-height: 1.3; margin-bottom: 10px; }
                .red-underline { width: 100%; border-bottom: 2px solid red; margin: 10px 0; }
            </style>
        </head>
        <body></body>
        </html>
    `);
    printWindow.document.body.appendChild(content);
    printWindow.document.close();
    printWindow.onload = () => {
        setTimeout(() => {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        }, 500);
    };
}
</script>

@endsection
