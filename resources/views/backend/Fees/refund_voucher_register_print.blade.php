<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Vouchers Register - Lokmanya Vidya Niketan</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: #f8fafc;
            padding: 20px;
            color: #1e293b;
        }
        .no-print-bar {
            max-width: 1000px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background-color: #4f46e5;
            color: #ffffff;
            border: none;
            padding: 8px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn-back {
            background-color: #64748b;
            color: #ffffff;
            text-decoration: none;
            padding: 8px 18px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
        }
        .report-sheet {
            max-width: 1000px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 24px;
            border-radius: 6px;
        }
        .school-header {
            text-align: center;
            border-bottom: 2px solid #334155;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .school-header h1 {
            font-size: 22px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
        }
        .school-header h3 {
            font-size: 15px;
            font-weight: 700;
            color: #475569;
            margin-top: 4px;
            text-transform: uppercase;
        }
        .filter-summary {
            font-size: 12px;
            color: #475569;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #94a3b8;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            background-color: #f1f5f9;
            font-weight: 800;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .no-print-bar {
                display: none !important;
            }
            .report-sheet {
                border: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>

<div class="no-print-bar">
    <a href="{{ route('refund-vouchers.index', request()->query()) }}" class="btn-back">&larr; Back to Register</a>
    <button onclick="window.print()" class="btn-print">&#128438; Print Register</button>
</div>

<div class="report-sheet">
    <div class="school-header">
        <h1>Lokmanya Vidya Niketan</h1>
        <h3>Refund Vouchers Register / Report</h3>
        <p style="font-size: 11px; color: #64748b; margin-top: 2px;">Generated on: {{ now()->format('d-M-Y h:i A') }}</p>
    </div>

    <div class="filter-summary">
        <div>
            <strong>Date Range:</strong>
            {{ $request->filled('from_date') ? \Carbon\Carbon::parse($request->from_date)->format('d-m-Y') : 'All' }}
            to
            {{ $request->filled('to_date') ? \Carbon\Carbon::parse($request->to_date)->format('d-m-Y') : 'All' }}
            @if($request->filled('refund_head'))
                | <strong>Head:</strong> {{ $request->refund_head }}
            @endif
            @if($request->filled('payment_mode'))
                | <strong>Mode:</strong> {{ $request->payment_mode }}
            @endif
        </div>
        <div>
            <strong>Total Records:</strong> {{ $vouchers->count() }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 35px;">#</th>
                <th>Voucher No</th>
                <th>Date</th>
                <th>Scholar No</th>
                <th>Student Name</th>
                <th>Class / Sec</th>
                <th>Refund Head</th>
                <th class="text-right">Amount (₹)</th>
                <th>Mode</th>
                <th>Cheque / Details</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vouchers as $index => $v)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $v->voucher_no }}</strong></td>
                    <td>{{ optional($v->voucher_date)->format('d-m-Y') }}</td>
                    <td>{{ $v->scholar_no }}</td>
                    <td>{{ $v->student_name }}</td>
                    <td>{{ $v->class_name }} {{ $v->section_name ? '(' . $v->section_name . ')' : '' }}</td>
                    <td>{{ $v->refund_head }}</td>
                    <td class="text-right font-weight-bold">₹{{ number_format($v->refund_amount, 2) }}</td>
                    <td>{{ $v->payment_mode }}</td>
                    <td>
                        @if($v->payment_mode === 'Cheque')
                            Chq: {{ $v->cheque_no ?? '-' }} (Dt: {{ $v->cheque_date ? optional($v->cheque_date)->format('d-m-Y') : '-' }})
                        @else
                            {{ $v->cash_account ?? '-' }}
                        @endif
                    </td>
                    <td>{{ ucfirst($v->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center; padding: 20px;">No refund vouchers found.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="7" class="text-right">Total Active Refund Amount:</td>
                <td class="text-right" style="color: #047857; font-size: 13px;">₹{{ number_format($totalAmount, 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 40px; display: flex; justify-content: space-between; font-size: 12px; font-weight: 700;">
        <div>Prepared By: ___________________</div>
        <div>Accountant: ___________________</div>
        <div>Principal: ___________________</div>
    </div>
</div>

</body>
</html>
