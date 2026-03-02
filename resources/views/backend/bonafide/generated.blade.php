<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bonafide Certificate</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .page {
            width: 190mm;
            height: 267mm;
            padding: 20mm;
            margin: 15mm auto;
            border: 2px solid #d00;
            box-sizing: border-box;
            position: relative;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #d00;
            padding-bottom: 10px;
        }

        .header h1, .header p {
            margin: 0;
        }

        .title {
            text-align: center;
            font-weight: bold;
            margin-top: 30px;
            text-decoration: underline;
            font-size: 20px;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            font-size: 14px;
        }

        .content {
            margin-top: 30px;
            font-size: 16px;
            line-height: 1.8;
        }

        .footer {
            margin-top: 60px;
            font-size: 15px;
        }

        .footer p {
            margin: 8px 0;
        }

        .print-btn {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            z-index: 9999;
        }

        .print-btn:hover {
            background-color: #0056b3;
        }

        @media print {
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>

@php
    function romanClass($class) {
        $map = [
            'Nursery' => 'Nursery',
            'KGI' => 'K.G. I',
            'KGII' => 'K.G. II',
            'KG II' => 'K.G. II',
            '1' => 'I', '2' => 'II', '3' => 'III', '4' => 'IV',
            '5' => 'V', '6' => 'VI', '7' => 'VII', '8' => 'VIII',
            '9' => 'IX', '10' => 'X', '11' => 'XI', '12' => 'XII'
        ];
        return $map[trim(strtoupper($class))] ?? $class;
    }

    function formattedDateWithSuffix($date) {
        $day = \Carbon\Carbon::parse($date)->format('j');
        $suffix = 'th';
        if (!in_array($day % 100, [11, 12, 13])) {
            switch ($day % 10) {
                case 1: $suffix = 'st'; break;
                case 2: $suffix = 'nd'; break;
                case 3: $suffix = 'rd'; break;
            }
        }
        return $day . $suffix . \Carbon\Carbon::parse($date)->format(' F, Y');
    }
@endphp

<div class="page">
    <div class="header">
        <img src="{{ asset('assets/backend/images/LVN-logo.png') }}" alt="School Logo" height="60"><br>
        KESAR BAGH ROAD, LOKMANYA NAGAR, INDORE (M.P.)<br>
        Phone: 0731-2360169, 9479460169 | Email: lvnschoolofficial@gmail.com<br>
        Website: www.lvnindore.org.in<br>
        C.B.S.E. Affiliation No.1030429
    </div>

    <div class="meta">
        <div>Ref/ LVN</div>
        <div>School Code: 50396</div>
        <div>Date: {{ formattedDateWithSuffix($current_date) }}</div>
    </div>

    <br><br>
    <div class="title">
        Bonafide Certificate<br>
        (Session 2025–26)
    </div>

    <div class="content">
        This is to certify that <strong>{{ ucwords($merged_data['student_name']) }}</strong> {{ $merged_data['relation'] }} <strong>{{ ucwords($merged_data['father_name']) }}</strong>, is a regular student of our school studying in <strong>Class {{ romanClass($merged_data['class']) }}</strong> session <strong>2025–2026</strong>. According to school record, {{ Str::lower($merged_data['his_her']) }} scholar no. is <strong>{{ $merged_data['scholar_no'] }}</strong> and date of birth is <strong>{{ \Carbon\Carbon::parse($merged_data['dob'])->format('d-m-Y') }}</strong>. {{ Str::ucfirst($merged_data['his_her']) }} address is <strong>{{ $merged_data['local_address'] }}</strong>. This certificate is provided on the demand of parents.

        {!! ucfirst($remarks) !!}
    </div>

    <div class="footer">
        <p>Signature: _____________________</p>
        <p>Principal</p>
    </div>
</div>

<button class="print-btn" onclick="window.print()">Print Certificate</button>

</body>
</html>
