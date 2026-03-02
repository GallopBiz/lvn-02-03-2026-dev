<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tuition Fees Certificate</title>
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

        .print-button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 14px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .print-button:hover {
            background-color: #45a049;
        }

        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
@php
    // Convert class to Roman numeral
    $class = trim($merged_data['class']);
    $romanMap = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V',
        6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX',
        10 => 'X', 11 => 'XI', 12 => 'XII'
    ];
    $classRoman = (is_numeric($class) && isset($romanMap[(int)$class]))
        ? $romanMap[(int)$class]
        : $class;

    // Format date with suffix
    $day = \Carbon\Carbon::parse($current_date)->format('j');
    $suffix = 'th';
    if (!in_array($day % 100, [11, 12, 13])) {
        switch ($day % 10) {
            case 1: $suffix = 'st'; break;
            case 2: $suffix = 'nd'; break;
            case 3: $suffix = 'rd'; break;
        }
    }
    $formattedDate = $day . $suffix . \Carbon\Carbon::parse($current_date)->format(' F, Y');
@endphp

<div class="page">
    <div class="header">
        <img src="{{ asset('assets/backend/images/LVN-logo.png') }}" alt="School Logo" height="60"><br>
        KESAR BAGH ROAD, LOKMANYA NAGAR, INDORE (M.P.)<br>
        Phone: 0731-2360169, 9479460169 | Email: lvnschoolofficial@gmail.com<br>
        Website: www.lvnindore.org<br>
        C.B.S.E. Affiliation No.1030429
    </div>

    <div class="meta">
        <div>Ref/ LVN</div>
        <div>School Code: 50396</div>
        <div>Date: {{ $formattedDate }}</div>
    </div>

    <br><br>
    <div class="title">
        Tuition Fees Certificate<br>
        (Session {{ $session }})
    </div>

    <div class="content">
        This is to certify that <strong>{{ ucwords($merged_data['student_name']) }}</strong>, 
        {{ $merged_data['gender_prefix'] }} <strong>{{ ucwords($merged_data['father_name']) }}</strong>, is a regular student 
        of class <strong>{{ $classRoman }}</strong>, section <strong>{{ $merged_data['section'] }}</strong>. His/Her Scholar Number is <strong>{{ $merged_data['scholar_no'] }}</strong>.<br>
        The school has received his/her fees of <strong>₹{{ $merged_data['fees_paid'] }}</strong> for the session <strong>{{ $session }}</strong>.
    </div>

    <div class="footer">
        <p>Signature: _____________________</p>
        <p>Principal</p>
        <button class="print-button" onclick="window.print()">Print Certificate</button>
    </div>
</div>
</body>
</html>
