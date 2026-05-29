<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Collective Attendance</title>
    <style>
        @page { size: A4 portrait; margin: 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; color: #1f2a3d; background: #eef2f7; }
        .print-actions { position: sticky; top: 0; z-index: 5; padding: 12px 18px; background: #fff; text-align: right; border-bottom: 1px solid #d9e1ec; }
        .print-actions button { border: 1px solid #1f6feb; background: #1f6feb; color: #fff; border-radius: 4px; padding: 7px 14px; cursor: pointer; }
        .sheet { width: 210mm; min-height: 297mm; margin: 18px auto; padding: 12mm; background: #fff; box-shadow: 0 8px 28px rgba(31, 42, 61, .18); }
        .school-header { display: grid; grid-template-columns: 1fr auto; gap: 12px; border-bottom: 2px solid #1f2a3d; padding-bottom: 8px; margin-bottom: 10px; }
        .school-name { font-size: 19px; font-weight: 700; }
        .report-title { font-size: 14px; font-weight: 700; margin-top: 4px; text-transform: uppercase; }
        .meta { text-align: right; font-size: 10px; line-height: 1.5; }
        .criteria, .summary { display: grid; gap: 8px; margin: 12px 0; }
        .criteria { grid-template-columns: repeat(2, 1fr); }
        .summary { grid-template-columns: repeat(5, 1fr); }
        .box { border: 1px solid #d9e1ec; border-radius: 4px; padding: 6px; background: #fff; }
        .summary .box { background: #f6f8fb; }
        .label { display: block; font-size: 9px; color: #65748b; text-transform: uppercase; margin-bottom: 3px; }
        .value { font-size: 11px; font-weight: 700; }
        .summary strong { display: block; font-size: 15px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #cfd8e5; padding: 4px; font-size: 9.5px; vertical-align: middle; }
        th { background: #e9eef6; font-weight: 700; text-align: left; }
        .num { text-align: right; }
        .center { text-align: center; }
        .low { color: #b42318; font-weight: 700; background: #fff0f0; }
        .ok { color: #167146; font-weight: 700; background: #e9f8ef; }
        .note { margin-top: 8px; color: #65748b; font-size: 9.5px; }
        .footer { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin-top: 24px; font-size: 10px; }
        .sign { border-top: 1px solid #1f2a3d; padding-top: 6px; text-align: center; }
        @media print {
            .print-actions { display: none; }
            body { background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .sheet { width: auto; min-height: auto; margin: 0; padding: 0; box-shadow: none; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
@php
    $details = $collective->details;
    $belowCount = $details->where('is_below_minimum', true)->count();
    $avgPercentage = $details->count() ? round($details->avg('attendance_percentage'), 2) : 0;
    $className = optional($collective->className)->class_name ?: $collective->class_name;
@endphp
<body>
    <div class="print-actions">
        <button onclick="window.print()">Print / Save PDF</button>
    </div>

    <main class="sheet">
        <header class="school-header">
            <div>
                <div class="school-name">Lokmanya Vidya Niketan</div>
                <div class="report-title">Student Collective Attendance Report</div>
            </div>
            <div class="meta">
                <div>Printed: {{ now()->format('d-m-Y h:i A') }}</div>
                <div>Academic Session: {{ $collective->academic_session }}</div>
            </div>
        </header>

        <section class="criteria">
            <div class="box"><span class="label">Exam</span><span class="value">{{ $collective->exam_name ?: 'Session Collective' }}</span></div>
            <div class="box"><span class="label">Class / Section</span><span class="value">{{ $className }} / {{ $collective->section_name }}</span></div>
            <div class="box"><span class="label">Date Range</span><span class="value">{{ optional($collective->start_date)->format('d-m-Y') }} to {{ optional($collective->end_date)->format('d-m-Y') }}</span></div>
            <div class="box"><span class="label">Teacher</span><span class="value">{{ trim(optional($collective->teacher)->first_name . ' ' . optional($collective->teacher)->last_name) ?: '-' }}</span></div>
        </section>

        <section class="summary">
            <div class="box"><span class="label">Students</span><strong>{{ $details->count() }}</strong></div>
            <div class="box"><span class="label">Working Days</span><strong>{{ $collective->working_days }}</strong></div>
            <div class="box"><span class="label">Below 75%</span><strong>{{ $belowCount }}</strong></div>
            <div class="box"><span class="label">Average %</span><strong>{{ number_format($avgPercentage, 2) }}%</strong></div>
            <div class="box"><span class="label">CBSE Minimum</span><strong>75%</strong></div>
        </section>

        <table>
            <thead>
                <tr>
                    <th class="center" style="width: 30px;">SNo</th>
                    <th>Student Name</th>
                    <th style="width: 72px;">Scholar No</th>
                    <th class="num" style="width: 58px;">Present</th>
                    <th class="num" style="width: 58px;">Working</th>
                    <th class="num" style="width: 66px;">%</th>
                    <th class="center" style="width: 62px;">75%</th>
                </tr>
            </thead>
            <tbody>
                @forelse($details as $row)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $row->student_name }}</td>
                        <td>{{ $row->scholar_no }}</td>
                        <td class="num">{{ number_format((float) $row->present_days, 2) }}</td>
                        <td class="num">{{ $row->working_days }}</td>
                        <td class="num">{{ number_format((float) $row->attendance_percentage, 2) }}%</td>
                        <td class="center {{ $row->is_below_minimum ? 'low' : 'ok' }}">{{ $row->is_below_minimum ? 'Below' : 'OK' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="center">No student attendance found.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="note">
            Attendance percentage is calculated as Present Days / Working Days * 100. Working days exclude Sundays and HRMS holiday/vacation/closure date ranges.
        </div>

        <footer class="footer">
            <div class="sign">Class Teacher</div>
            <div class="sign">Academic Coordinator</div>
            <div class="sign">Principal</div>
        </footer>
    </main>

</body>
</html>
