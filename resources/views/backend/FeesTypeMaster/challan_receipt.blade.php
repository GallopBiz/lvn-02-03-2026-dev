@extends('backend.layouts.main')
@section('main-container')

<style>
    .print-wrapper {
        border: 1px solid #e4e0e0;
        width: 75%;
        max-width: 820px;
        margin: 20px auto;
        padding: 25px 30px;
        box-sizing: border-box;
        font-family: 'Arial', sans-serif;
        font-size: 13px;
        background: #ffffff;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        border-radius: 4px;
    }
    .header {
        text-align: center;
        font-size: 13px;
        line-height: 1.35;
        margin-bottom: 8px;
    }
    .school-logo {
        max-width: 240px;
        height: auto;
        margin: 0 auto 6px auto;
        display: block;
    }
    .red-underline {
        width: 100%;
        border-bottom: 2px solid #d92525;
        margin: 8px 0 12px 0;
    }
    .meta-line {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        margin-bottom: 12px;
        color: #333;
    }
    .student-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        background: #fafafa;
        padding: 10px 14px;
        border-radius: 4px;
        border: 1px solid #eee;
    }
    .student-info > div {
        width: 48%;
    }
    .student-info h5 {
        margin: 4px 0;
        font-size: 13px;
        font-weight: normal;
        color: #333;
    }
    .student-info strong {
        font-weight: 600;
        color: #111;
    }
    .invoice-summary {
        text-align: right;
    }
    .receipt-title {
        text-align: center;
        font-size: 16px;
        font-weight: 700;
        letter-spacing: 0.5px;
        margin: 15px 0 10px 0;
        text-transform: uppercase;
        color: #222;
        border-bottom: 1px dashed #ccc;
        padding-bottom: 6px;
    }
    table.receipt-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        margin-bottom: 15px;
    }
    table.receipt-table th, table.receipt-table td {
        border: 1px solid #ddd;
        padding: 7px 10px;
        text-align: left;
        vertical-align: middle;
    }
    table.receipt-table th {
        background-color: #f5f5f5;
        font-weight: 600;
        color: #333;
    }
    table.receipt-table td.amount, table.receipt-table th.amount {
        text-align: right;
    }
    table.receipt-table tr.total-row {
        background-color: #f9f9f9;
        font-weight: bold;
    }
    .amount-words-box {
        font-size: 12px;
        background: #fdfdfd;
        border: 1px solid #e9e9e9;
        padding: 8px 12px;
        margin-bottom: 12px;
        border-radius: 3px;
    }
    .signature-section {
        display: flex;
        justify-content: flex-end;
        margin-top: 45px;
        padding: 0 10px;
        font-size: 12px;
    }
    .signature-box {
        text-align: center;
        width: 200px;
    }
    .signature-line {
        border-top: 1px dotted #333;
        margin-bottom: 5px;
    }
    .note-text {
        font-size: 11px;
        color: #777;
        text-align: center;
        margin-top: 20px;
    }
    @media print {
        body * {
            visibility: hidden !important;
        }
        #printArea, #printArea * {
            visibility: visible !important;
        }
        #printArea {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .print-wrapper {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 15px !important;
            border: 1px solid #666 !important;
            box-shadow: none !important;
        }
        .no-print, .btn, header, .sidebar, .main-header, .footer {
            display: none !important;
        }
    }
</style>

<div class="main-content pt-3">
    <div class="text-center no-print mb-3">
        <button class="btn btn-primary btn-sm me-2" onclick="printReceipt()"><i class="fa fa-print"></i> Print Receipt</button>
        <button class="btn btn-secondary btn-sm" onclick="window.close(); history.back();">Close</button>
    </div>

    <div id="printArea">
        <div class="print-wrapper">
            <div class="header">
                <img src="{{ asset('assets/backend/images/LVN-logo.png') }}" alt="School Logo" class="school-logo">
                <div><strong>KESAR BAGH ROAD, LOKMANYA NAGAR, INDORE (M.P.)</strong></div>
                <div>Phone: 0731-2360169, 4651250-8 | Email: lvnschoolofficial@gmail.com</div>
                <div>Website: www.lvnindore.org</div>
                <div><strong>C.B.S.E. Affiliation No. 1030429</strong></div>
            </div>

            <div class="red-underline"></div>

            <div class="meta-line">
                <div><strong>School Code:</strong> 50396</div>
                <div><strong>Voucher / Receipt No.:</strong> {{ $challan->id }}</div>
                <div><strong>Date:</strong> {{ $displayDate }}</div>
            </div>

            <div class="student-info">
                <div>
                    <h5><strong>Scholar No.:</strong> {{ $json['name_scholarno'] ?? $student->scholar_no ?? '-' }}</h5>
                    <h5><strong>Student Name:</strong> {{ ucwords(strtolower($challan->name_student ?? $json['name_student'] ?? $studentJson['studentname'] ?? $student->student_name ?? '-')) }}</h5>
                    <h5><strong>Father Name:</strong> {{ ucwords(strtolower($json['name_father'] ?? $studentJson['fathername'] ?? '-')) }}</h5>
                </div>
                <div class="invoice-summary">
                    <h5><strong>Session:</strong> {{ $formattedSession }}</h5>
                    <h5><strong>Class:</strong> {{ $formattedClass }}</h5>
                    <h5><strong>Payment Mode:</strong> {{ $json['payment_by_select'] ?? 'CASH' }}</h5>
                </div>
            </div>

            <div class="receipt-title">Fees Receipt</div>

            <table class="receipt-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">S.No.</th>
                        <th style="width: 52%;">Fees Head</th>
                        <th style="width: 20%; text-align: center;">Term / Installment</th>
                        <th class="amount" style="width: 20%;">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $idx => $item)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><strong>{{ strtoupper($item['head_name']) }}</strong></td>
                            <td style="text-align: center;">{{ $item['term'] }}</td>
                            <td class="amount">{{ number_format($item['amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No fee heads recorded.</td>
                        </tr>
                    @endforelse
                    <tr class="total-row">
                        <td colspan="3" style="text-align: right;"><strong>Total Amount Received:</strong></td>
                        <td class="amount"><strong>₹ {{ number_format($totalAmount, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>

            @if(!empty($amountInWords))
                <div class="amount-words-box">
                    <strong>Amount in Words:</strong> {{ $amountInWords }}
                </div>
            @endif

            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div><strong>Authorized Signatory</strong></div>
                </div>
            </div>

            <div class="note-text">
                <em>* Note: This is a computer-generated fee receipt.</em>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function printReceipt() {
        const printContent = document.getElementById('printArea');
        if (!printContent) {
            window.focus();
            window.print();
            return;
        }

        let printFrame = document.getElementById('receiptPrintFrame');
        if (printFrame) {
            printFrame.remove();
        }
        printFrame = document.createElement('iframe');
        printFrame.id = 'receiptPrintFrame';
        printFrame.style.position = 'fixed';
        printFrame.style.right = '0';
        printFrame.style.bottom = '0';
        printFrame.style.width = '0';
        printFrame.style.height = '0';
        printFrame.style.border = '0';
        document.body.appendChild(printFrame);

        const frameDoc = printFrame.contentWindow.document;
        frameDoc.open();
        frameDoc.write(`<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fees Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 20px;
            background: #fff;
            color: #111;
        }
        .print-wrapper {
            border: 1px solid #444;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px 25px;
            box-sizing: border-box;
            background: #fff;
        }
        .header {
            text-align: center;
            font-size: 13px;
            line-height: 1.35;
            margin-bottom: 8px;
        }
        .school-logo {
            max-width: 240px;
            height: auto;
            margin: 0 auto 6px auto;
            display: block;
        }
        .red-underline {
            width: 100%;
            border-bottom: 2px solid #d92525;
            margin: 8px 0 12px 0;
        }
        .meta-line {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 12px;
            color: #333;
        }
        .student-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            background: #fafafa;
            padding: 10px 14px;
            border-radius: 4px;
            border: 1px solid #eee;
        }
        .student-info > div {
            width: 48%;
        }
        .student-info h5 {
            margin: 4px 0;
            font-size: 13px;
            font-weight: normal;
            color: #333;
        }
        .student-info strong {
            font-weight: 600;
            color: #111;
        }
        .invoice-summary {
            text-align: right;
        }
        .receipt-title {
            text-align: center;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin: 15px 0 10px 0;
            text-transform: uppercase;
            color: #222;
            border-bottom: 1px dashed #ccc;
            padding-bottom: 6px;
        }
        table.receipt-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 15px;
        }
        table.receipt-table th, table.receipt-table td {
            border: 1px solid #333;
            padding: 7px 10px;
            text-align: left;
            vertical-align: middle;
        }
        table.receipt-table th {
            background-color: #f5f5f5;
            font-weight: 600;
            color: #333;
        }
        table.receipt-table td.amount, table.receipt-table th.amount {
            text-align: right;
        }
        table.receipt-table tr.total-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .amount-words-box {
            font-size: 12px;
            background: #fdfdfd;
            border: 1px solid #e9e9e9;
            padding: 8px 12px;
            margin-bottom: 12px;
            border-radius: 3px;
        }
        .signature-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 45px;
            padding: 0 10px;
            font-size: 12px;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-top: 1px dotted #333;
            margin-bottom: 5px;
        }
        .note-text {
            font-size: 11px;
            color: #777;
            text-align: center;
            margin-top: 20px;
        }
        @media print {
            body {
                padding: 0;
            }
            .print-wrapper {
                border: 1px solid #333 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body>
    ${printContent.innerHTML}
</body>
</html>`);
        frameDoc.close();

        setTimeout(function() {
            printFrame.contentWindow.focus();
            printFrame.contentWindow.print();
        }, 250);
    }
</script>

@endsection
