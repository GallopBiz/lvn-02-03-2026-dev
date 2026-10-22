<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Manual Attendance & Working Days Report</title>
    <style>
        @page { size: A4 portrait; margin: 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; color: #1f2a3d; background: #eef2f7; }
        .print-actions { position: sticky; top: 0; z-index: 5; padding: 12px 18px; background: #fff; border-bottom: 1px solid #d9e1ec; display: flex; justify-content: flex-end; align-items: center; gap: 10px; }
        .print-actions button, .print-actions a { border: 1px solid #1f6feb; background: #1f6feb; color: #fff; border-radius: 4px; padding: 7px 16px; cursor: pointer; text-decoration: none; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; }
        .print-actions a.btn-back { background: #6c757d; border-color: #6c757d; color: #fff; }
        .print-actions a.btn-back:hover { background: #5a6268; }
        .print-actions button:hover { background: #1858ba; }
        .sheet { width: 210mm; min-height: 297mm; margin: 18px auto; padding: 12mm; background: #fff; box-shadow: 0 8px 28px rgba(31, 42, 61, .15); }
        .school-header { display: grid; grid-template-columns: 1fr auto; gap: 12px; border-bottom: 2px solid #1f2a3d; padding-bottom: 10px; margin-bottom: 12px; }
        .school-name { font-size: 20px; font-weight: 800; color: #0f172a; }
        .report-title { font-size: 13px; font-weight: 700; margin-top: 4px; text-transform: uppercase; color: #2563eb; }
        .meta { text-align: right; font-size: 10px; line-height: 1.6; color: #475569; }
        .criteria, .summary { display: grid; gap: 8px; margin: 10px 0; }
        .criteria { grid-template-columns: repeat(4, 1fr); }
        .summary { grid-template-columns: repeat(5, 1fr); }
        .box { border: 1px solid #d9e1ec; border-radius: 4px; padding: 6px 8px; background: #fff; }
        .summary .box { background: #f8fafc; border-color: #cbd5e1; }
        .label { display: block; font-size: 8.5px; color: #64748b; text-transform: uppercase; margin-bottom: 2px; font-weight: 600; }
        .value { font-size: 11px; font-weight: 700; color: #0f172a; }
        .summary strong { display: block; font-size: 15px; color: #0f172a; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 6px; font-size: 9.5px; vertical-align: middle; }
        th { background: #f1f5f9; font-weight: 700; text-align: left; color: #334155; }
        .num { text-align: right; font-weight: 600; }
        .center { text-align: center; }
        .low { color: #b91c1c; font-weight: 700; background: #fef2f2; }
        .ok { color: #15803d; font-weight: 700; background: #f0fdf4; }
        .footer { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 36px; font-size: 10.5px; }
        .sign { border-top: 1px solid #334155; padding-top: 6px; text-align: center; font-weight: 600; }
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
        <a href="{{ (url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : route('academic.attendance.manual') }}" class="btn-back" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }">&larr; Back</a>
        <button onclick="window.print()">Print / Save as PDF</button>
    </div>

    <main class="sheet">
        <header class="school-header">
            <div>
                <div class="school-name">Lokmanya Vidya Niketan</div>
                <div class="report-title">Student Attendance & Working Days Record (Manual Adjustment)</div>
            </div>
            <div class="meta">
                <div><strong>Printed:</strong> {{ now()->format('d M Y, h:i A') }}</div>
                <div><strong>Session:</strong> {{ $collective->academic_session }}</div>
            </div>
        </header>

        <section class="criteria">
            <div class="box"><span class="label">Class & Section</span><span class="value">{{ $className }} - {{ $collective->section_name }}</span></div>
            <div class="box"><span class="label">Exam / Assessment</span><span class="value">{{ $collective->exam_name ?: 'Session Collective' }}</span></div>
            <div class="box"><span class="label">School Working Days</span><span class="value">{{ $collective->working_days }} Days</span></div>
            <div class="box"><span class="label">Adjusted By</span><span class="value">Admin (Manual Entry)</span></div>
        </section>

        <section class="summary">
            <div class="box"><span class="label">Total Students</span><strong>{{ $details->count() }}</strong></div>
            <div class="box"><span class="label">School Working Days</span><strong>{{ $collective->working_days }}</strong></div>
            <div class="box"><span class="label">Average Attendance</span><strong>{{ number_format($avgPercentage, 2) }}%</strong></div>
            <div class="box"><span class="label">CBSE &ge; 75% Eligible</span><strong style="color:#16a34a;">{{ $details->count() - $belowCount }}</strong></div>
            <div class="box"><span class="label">Below 75% Shortage</span><strong style="color:#dc2626;">{{ $belowCount }}</strong></div>
        </section>

        <table>
            <thead>
                <tr>
                    <th class="center" style="width: 32px;">SNo</th>
                    <th style="width: 70px;">Scholar No</th>
                    <th>Student Name</th>
                    <th>Father Name</th>
                    <th class="num" style="width: 60px;">Working</th>
                    <th class="num" style="width: 60px;">Present</th>
                    <th class="num" style="width: 65px;">%</th>
                    <th class="center" style="width: 75px;">CBSE 75%</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse($details as $row)
                    @php
                        $fatherName = optional($row->student)->father_name ?: '';
                    @endphp
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $row->scholar_no }}</td>
                        <td><strong>{{ $row->student_name }}</strong></td>
                        <td style="color:#475569;">{{ $fatherName ?: '-' }}</td>
                        <td class="num">{{ $row->working_days }}</td>
                        <td class="num">{{ number_format((float) $row->present_days, 1) }}</td>
                        <td class="num">{{ number_format((float) $row->attendance_percentage, 2) }}%</td>
                        <td class="center {{ $row->is_below_minimum ? 'low' : 'ok' }}">
                            {{ $row->is_below_minimum ? 'Below 75%' : 'Eligible' }}
                        </td>
                        <td style="color:#64748b; font-size: 9px;">{{ $row->remarks ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="center">No student records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            <div class="sign">Class Teacher Signature</div>
            <div class="sign">Academic Coordinator Signature</div>
            <div class="sign">Principal Signature & Seal</div>
        </div>
    </main>
</body>
</html>
