<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Voucher #{{ $voucher->voucher_no }} - Lokmanya Vidya Niketan</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: #f1f5f9;
            padding: 20px;
            color: #1e293b;
        }
        .no-print-bar {
            max-width: 800px;
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
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.3);
        }
        .btn-back {
            background-color: #64748b;
            color: #ffffff;
            text-decoration: none;
            padding: 8px 18px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 6px;
            display: inline-block;
        }
        .voucher-sheet {
            max-width: 800px;
            margin: 0 auto 20px auto;
            background: #ffffff;
            border: 2px solid #334155;
            border-radius: 6px;
            padding: 24px 28px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .school-header {
            text-align: center;
            border-bottom: 2px double #334155;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .school-header h1 {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .school-header p {
            font-size: 12px;
            color: #475569;
            margin-bottom: 2px;
        }
        .voucher-title-pill {
            display: inline-block;
            background-color: #0f172a;
            color: #ffffff;
            padding: 4px 18px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-radius: 4px;
            margin-top: 6px;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            row-gap: 6px;
            column-gap: 20px;
            margin-bottom: 14px;
            font-size: 13px;
            background: #f8fafc;
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        .meta-row {
            display: flex;
        }
        .meta-label {
            font-weight: 700;
            color: #475569;
            width: 140px;
            flex-shrink: 0;
        }
        .meta-val {
            font-weight: 600;
            color: #0f172a;
        }
        .voucher-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 13px;
        }
        .voucher-table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            font-size: 11px;
        }
        .voucher-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            color: #1e293b;
        }
        .amount-words-box {
            background: #f8fafc;
            border: 1px dashed #94a3b8;
            padding: 8px 12px;
            font-size: 12px;
            margin-bottom: 14px;
            border-radius: 4px;
        }
        .payment-box {
            border: 1px solid #e2e8f0;
            background: #ffffff;
            padding: 10px 14px;
            margin-bottom: 24px;
            font-size: 12px;
            border-radius: 4px;
        }
        .sig-row {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 10px;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            text-align: center;
        }
        .sig-box {
            width: 22%;
            border-top: 1px dashed #64748b;
            padding-top: 6px;
        }
        .cut-line {
            border-top: 2px dashed #94a3b8;
            margin: 24px auto;
            position: relative;
            text-align: center;
            max-width: 800px;
        }
        .cut-line span {
            background: #f1f5f9;
            padding: 0 10px;
            position: relative;
            top: -10px;
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .no-print-bar {
                display: none !important;
            }
            .voucher-sheet {
                box-shadow: none;
                margin: 0 auto;
                page-break-inside: avoid;
            }
            .cut-line span {
                background: #ffffff;
            }
        }
    </style>
</head>
<body>

@php
    function numberToWordsINR($num) {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        
        $num = (int) $num;
        if ($num == 0) return 'Zero';

        $words = [];
        if ($num >= 10000000) {
            $crore = floor($num / 10000000);
            $words[] = numberToWordsINR($crore) . ' Crore';
            $num %= 10000000;
        }
        if ($num >= 100000) {
            $lakh = floor($num / 100000);
            $words[] = numberToWordsINR($lakh) . ' Lakh';
            $num %= 100000;
        }
        if ($num >= 1000) {
            $thousand = floor($num / 1000);
            $words[] = numberToWordsINR($thousand) . ' Thousand';
            $num %= 1000;
        }
        if ($num >= 100) {
            $hundred = floor($num / 100);
            $words[] = numberToWordsINR($hundred) . ' Hundred';
            $num %= 100;
        }
        if ($num > 0) {
            if ($num < 20) {
                $words[] = $ones[$num];
            } else {
                $words[] = $tens[floor($num / 10)] . ($num % 10 ? ' ' . $ones[$num % 10] : '');
            }
        }
        return implode(' ', $words);
    }
    $amountWords = 'Rupees ' . numberToWordsINR($voucher->refund_amount) . ' Only';
@endphp

<div class="no-print-bar">
    <a href="{{ route('refund-vouchers.index') }}" class="btn-back">
        &larr; Back to Register
    </a>
    <div>
        <a href="{{ route('view-student-ledger', $voucher->student_id) }}" target="_blank" class="btn-back" style="background:#0284c7;margin-right:8px;">
            View Student Ledger
        </a>
        <button onclick="window.print()" class="btn-print">
            &#128438; Print Voucher
        </button>
    </div>
</div>

{{-- COPY 1: OFFICE COPY --}}
<div class="voucher-sheet">
    <div class="school-header">
        <h1>Lokmanya Vidya Niketan</h1>
        <p>Affiliated to CBSE, New Delhi | Senior Secondary School</p>
        <p>Indore (M.P.) &bull; Phone: 0731-2788500 &bull; Email: info@lvn.edu.in</p>
        <div class="voucher-title-pill">REFUND VOUCHER &bull; OFFICE COPY</div>
    </div>

    <div class="meta-grid">
        <div class="meta-row">
            <span class="meta-label">Voucher No.:</span>
            <span class="meta-val">{{ $voucher->voucher_no }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Voucher Date:</span>
            <span class="meta-val">{{ optional($voucher->voucher_date)->format('d-M-Y') }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Scholar Number:</span>
            <span class="meta-val">{{ $voucher->scholar_no }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Student Name:</span>
            <span class="meta-val">{{ $voucher->student_name }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Father's Name:</span>
            <span class="meta-val">{{ $voucher->father_name ?? 'N/A' }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Class & Section:</span>
            <span class="meta-val">{{ $voucher->class_name ?? 'N/A' }} {{ $voucher->section_name ? '(' . $voucher->section_name . ')' : '' }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Academic Session:</span>
            <span class="meta-val">{{ $voucher->session_name ?? date('Y') . '-' . (date('Y') + 1) }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Student Status:</span>
            <span class="meta-val">
                @if($voucher->is_discontinued || $voucher->tc_id)
                    <strong style="color: #b45309;">T.C. Issued / Discontinued</strong>
                @else
                    Regular Student
                @endif
            </span>
        </div>
    </div>

    <table class="voucher-table">
        <thead>
            <tr>
                <th style="width: 40px;">S.No</th>
                <th>Refund Particulars / Fee Head</th>
                <th>Description / Narration</th>
                <th style="width: 140px; text-align: right;">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><strong>{{ $voucher->refund_head }} REFUND</strong></td>
                <td>{{ $voucher->remarks ?: 'Refund of refundable caution balance on student leaving school / T.C. generation.' }}</td>
                <td style="text-align: right; font-weight: 700; font-size: 14px;">₹{{ number_format($voucher->refund_amount, 2) }}</td>
            </tr>
            <tr style="background-color: #f8fafc; font-weight: 800;">
                <td colspan="3" style="text-align: right; text-transform: uppercase;">Total Refunded Amount:</td>
                <td style="text-align: right; font-size: 15px; color: #047857;">₹{{ number_format($voucher->refund_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="amount-words-box">
        <strong>Amount in Words:</strong> {{ $amountWords }}
    </div>

    <div class="payment-box">
        <div style="margin-bottom: 4px;">
            <strong>Payment Mode:</strong> {{ $voucher->payment_mode }}
            @if($voucher->payment_mode === 'Cheque')
                &nbsp;|&nbsp; <strong>Cheque No:</strong> {{ $voucher->cheque_no ?? 'N/A' }}
                &nbsp;|&nbsp; <strong>Cheque Date:</strong> {{ $voucher->cheque_date ? optional($voucher->cheque_date)->format('d-M-Y') : 'N/A' }}
                &nbsp;|&nbsp; <strong>Bank Account:</strong> {{ $voucher->bank_name ?? 'N/A' }}
            @elseif($voucher->cash_account)
                &nbsp;|&nbsp; <strong>Cash A/C:</strong> {{ $voucher->cash_account }}
            @endif
        </div>
        <div>
            <strong>Student Ledger Debit Ref:</strong> VOUCHER #{{ $voucher->voucher_no }} &bull; Automatically recorded in Student Ledger
        </div>
    </div>

    <div class="sig-row">
        <div class="sig-box">Prepared By<br><small style="font-weight: normal;">{{ $voucher->created_by_name ?? 'Admin' }}</small></div>
        <div class="sig-box">Accountant<br><small style="font-weight: normal;">Signature & Date</small></div>
        <div class="sig-box">Principal / Manager<br><small style="font-weight: normal;">Authorized Signatory</small></div>
        <div class="sig-box">Receiver's Signature<br><small style="font-weight: normal;">(Parent / Guardian)</small></div>
    </div>
</div>

{{-- Cut Line for Dual Copy --}}
<div class="cut-line">
    <span>&#9986; TEAR HERE FOR PARENT COPY &#9986;</span>
</div>

{{-- COPY 2: PARENT / STUDENT COPY --}}
<div class="voucher-sheet">
    <div class="school-header">
        <h1>Lokmanya Vidya Niketan</h1>
        <p>Affiliated to CBSE, New Delhi | Senior Secondary School</p>
        <p>Indore (M.P.) &bull; Phone: 0731-2788500 &bull; Email: info@lvn.edu.in</p>
        <div class="voucher-title-pill">REFUND VOUCHER &bull; STUDENT / PARENT COPY</div>
    </div>

    <div class="meta-grid">
        <div class="meta-row">
            <span class="meta-label">Voucher No.:</span>
            <span class="meta-val">{{ $voucher->voucher_no }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Voucher Date:</span>
            <span class="meta-val">{{ optional($voucher->voucher_date)->format('d-M-Y') }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Scholar Number:</span>
            <span class="meta-val">{{ $voucher->scholar_no }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Student Name:</span>
            <span class="meta-val">{{ $voucher->student_name }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Father's Name:</span>
            <span class="meta-val">{{ $voucher->father_name ?? 'N/A' }}</span>
        </div>
        <div class="meta-row">
            <span class="meta-label">Class & Section:</span>
            <span class="meta-val">{{ $voucher->class_name ?? 'N/A' }} {{ $voucher->section_name ? '(' . $voucher->section_name . ')' : '' }}</span>
        </div>
    </div>

    <table class="voucher-table">
        <thead>
            <tr>
                <th style="width: 40px;">S.No</th>
                <th>Refund Particulars</th>
                <th>Description</th>
                <th style="width: 140px; text-align: right;">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><strong>{{ $voucher->refund_head }} REFUND</strong></td>
                <td>{{ $voucher->remarks ?: 'Refund against Transfer Certificate (T.C.)' }}</td>
                <td style="text-align: right; font-weight: 700; font-size: 14px;">₹{{ number_format($voucher->refund_amount, 2) }}</td>
            </tr>
            <tr style="background-color: #f8fafc; font-weight: 800;">
                <td colspan="3" style="text-align: right; text-transform: uppercase;">Total Received:</td>
                <td style="text-align: right; font-size: 15px; color: #047857;">₹{{ number_format($voucher->refund_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="amount-words-box">
        <strong>Amount in Words:</strong> {{ $amountWords }}
    </div>

    <div class="payment-box">
        <strong>Disbursed By:</strong> {{ $voucher->payment_mode }}
        @if($voucher->payment_mode === 'Cheque')
            &bull; Chq No: <strong>{{ $voucher->cheque_no ?? 'N/A' }}</strong>
            &bull; Dt: <strong>{{ $voucher->cheque_date ? optional($voucher->cheque_date)->format('d-M-Y') : 'N/A' }}</strong>
            &bull; Bank: <strong>{{ $voucher->bank_name ?? 'N/A' }}</strong>
        @endif
    </div>

    <div class="sig-row">
        <div class="sig-box">Accountant</div>
        <div class="sig-box">Authorized Signatory / Seal</div>
        <div class="sig-box" style="margin-left: auto;">Receiver's Signature</div>
    </div>
</div>

<script>
window.addEventListener('load', function() {
    // Optionally trigger print automatically if query parameter ?autoprint=1 is set
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('autoprint') === '1') {
        window.print();
    }
});
</script>
</body>
</html>
