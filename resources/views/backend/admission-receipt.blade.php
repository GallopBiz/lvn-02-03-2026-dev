@extends('backend.layouts.main')
@section('main-container')

<style>
    .print-wrapper {
        border: 1px solid #e4e0e0;
        width: 70%;
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        box-sizing: border-box;
        font-family: 'Arial', sans-serif;
        font-size: 13px;
        min-height: auto;
    }
    .header {
        text-align: center;
        font-size: 14px;
        line-height: 1.3;
        margin-bottom: 10px;
    }
    .school-logo {
        max-width: 250px;
        height: auto;
        margin: 0 auto 5px auto;
        display: block;
    }
    .red-underline {
        width: 100%;
        border-bottom: 2px solid red;
        margin: 10px 0;
    }
    .meta-line {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        margin-bottom: 15px;
    }
    .student-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    .student-info > div {
        width: 48%;
    }
    .student-info h5 {
        margin: 2px 0;
        font-size: 13px;
        font-weight: normal;
    }
    .student-info strong {
        font-weight: 600;
    }
    .invoice-summary {
        text-align: right;
        font-size: 13px;
    }
    table.table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        margin-bottom: 20px;
    }
    table.table th, table.table td {
        border: 1px solid #ddd;
        padding: 6px 10px;
        text-align: left;
        vertical-align: middle;
    }
    table.table th {
        background-color: #f8f8f8;
    }
    table.table td.amount {
        text-align: right;
    }
    .approval-section {
        text-align: center;
        margin-top: 20px;
        font-size: 14px;
    }
    @media print {
        .btn-danger, .btn-success, .mb-3 {
            display: none !important;
        }
        .print-wrapper {
            width: 100% !important;
            margin: 0 auto !important;
        }
    }
</style>

@php
    // Corrected class mapping
    $classMap = [
        'nursery' => 'Nursery',
        'kg1' => 'KG-I',
        'kg2' => 'KG-II',
        '01' => 'I',
        '02' => 'II',
        '03' => 'III',
        '04' => 'IV',
        '05' => 'V',
        '06' => 'VI',
        '07' => 'VII',
        '08' => 'VIII',
        '09' => 'IX',
        '10' => 'X',
        '11' => 'XI',
        '12' => 'XII',
    ];

    $classRaw = strtolower(trim($json['classname'] ?? ''));
    $cleaned = preg_replace('/[^a-z0-9]/i', '', $classRaw); // removes spaces/symbols
    $romanClass = $classMap[$cleaned] ?? ucfirst($classRaw); // fallback if not mapped
@endphp

@php
    $session = $student->session_name ?? '';
    $formattedSession = (!empty($session) && str_contains($session, '_')) ? str_replace('_', ' - ', $session) : ($session ?: 'N/A');
@endphp

<div class="main-content">
    <div id="printme">
        <div class="print-wrapper">
            <div class="header">
                <img src="{{ asset('assets/backend/images/LVN-logo.png') }}" alt="School Logo" class="school-logo">
                <div>KESAR BAGH ROAD, LOKMANYA NAGAR, INDORE (M.P.)</div>
                <div>Phone: 0731-2360169, 4651250-8 | Email: lvnschoolofficial@gmail.com</div>
                <div>Website: www.lvnindore.org</div>
                <div><strong>C.B.S.E. Affiliation No.1030429</strong></div>
            </div>

            <div class="red-underline"></div>

            <div class="meta-line">
                <div><strong>School Code:</strong> 50396</div>
                <div><strong>Date:</strong> {{ \Carbon\Carbon::parse($student->created_at ?? now())->format('d-m-Y') }}</div>
            </div>
            <div class="student-info">
                <div>
                    <h5><strong>Form No.:</strong> {{ $student->form_number ?? '123456' }}</h5>
                    <h5><strong>Student Name:</strong> {{ ucwords(strtolower($json['studentname'] ?? 'Test Student')) }}</h5>
                    <h5><strong>Father Name:</strong> {{ ucfirst(strtolower($json['fathername_prefix'] ?? 'Mr.')) }} {{ ucwords(strtolower($json['fathername'] ?? 'Test Father')) }}</h5>
                </div>
                <div class="invoice-summary">
                    <h5><strong>Session:</strong>&nbsp;{{ $formattedSession }}</h5>
                    <h5><strong>Class:</strong>&nbsp;&nbsp;{{ $romanClass }}</h5>
                </div>
            </div>

            @php
                $total = 0;
                $feeHeads = $feeHeads ?? collect([
                    (object)['account_name' => 'Admission Fees', 'fees' => 10000],
                    (object)['account_name' => 'Alumini Fees', 'fees' => 2000],
                    (object)['account_name' => 'CUATION MONEY', 'fees' => 3000],
                ]);
                $corrections = ['CUATION MONEY' => 'Caution Money'];
                $refundMap = [
                    'admission fees' => '(Non Refundable)',
                    'alumini fees' => '(Non Refundable)',
                    'caution money' => '(Refundable)',
                ];
            @endphp

            <div style="text-align:center; font-size:18px; font-weight:600;">Admission Fees Receipt</div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Fees Head</th>
                        <th class="amount" style="text-align:right;">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($feeHeads as $fee)
                        @php
                            $rawHead = trim($fee->account_name);
                            $correctedHead = $corrections[$rawHead] ?? $rawHead;
                            $normalizedHead = strtolower($correctedHead);
                            $label = $refundMap[$normalizedHead] ?? '';
                            $total += $fee->fees;
                        @endphp
                        <tr>
                            <td>{{ $correctedHead }} <small style="color:#555;">{{ $label }}</small></td>
                            <td class="amount">{{ number_format($fee->fees, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td><strong>Grand Total</strong></td>
                        <td class="amount"><strong>₹ {{ number_format($total, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>

            <p style="font-size:12px; margin-top:-10px;">
                <em>Note: Only Caution Money is refundable at the time of issuing the Transfer Certificate (T.C.).</em>
            </p>

            <div class="approval-section" style="text-align:right;">
                <div>.........................................</div>
                <div><strong>Approved by: Name</strong></div>
            </div>
        </div>
    </div>
    <div class="text-center">
        <button class="btn btn-sm btn-secondary mb-3" onclick="history.back()">Back</button>
        <button class="btn btn-sm btn-success mb-3" onclick="printDiv('printme')">Print Report</button>
    </div>
</div>

<script type="text/javascript">
    function printDiv(divName) {
        var divContents = document.getElementById(divName).innerHTML;
        var printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print Admission Receipt</title>');
        printWindow.document.write('<style>');
        printWindow.document.write(`
            body { font-family: Arial, sans-serif; font-size: 13px; margin: 20px; }
            .print-wrapper { width: 100%; border: 1px solid #e4e0e0; padding: 15px; box-sizing: border-box; }
            .header { text-align: center; font-size: 10px; line-height: 1.3; margin-bottom: 10px; }
            .school-logo { max-width: 250px; height: auto; margin: 0 auto 5px auto; display: block; }
            .red-underline { width: 100%; border-bottom: 2px solid red; margin: 10px 0; }
            .meta-line { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 15px; }
            .student-info { display: flex; justify-content: space-between; margin-bottom: 20px; }
            .student-info > div { width: 48%; }
            .student-info h5 { margin: 2px 0; font-size: 12px; font-weight: normal; }
            .student-info strong { font-weight: 600; }
            .invoice-summary { text-align: right; font-size: 12px; }
            table.table { width: 100%; border-collapse: collapse; font-size: 12px; margin-bottom: 20px; }
            table.table th, table.table td { border: 1px solid #ddd; padding: 6px 10px; text-align: left; vertical-align: middle; }
            table.table th { background-color: #f8f8f8; }
            table.table td.amount { text-align: right; }
            .approval-section { text-align: right; margin-top: 20px; font-size: 14px; }
        `);
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(divContents);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }
</script>

@endsection
