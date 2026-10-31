<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Topper List &amp; Result Analysis</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            margin: 0;
            color: #172033;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .print-actions {
            padding: 12px;
            text-align: center;
            background: #f1f5f9;
        }

        .print-actions button {
            padding: 8px 18px;
            color: #fff;
            background: #263f64;
            border: 0;
            border-radius: 4px;
            cursor: pointer;
        }

        .report {
            max-width: 190mm;
            margin: 0 auto;
        }

        .report-header {
            padding-bottom: 10px;
            text-align: center;
            border-bottom: 2px solid #263f64;
        }

        .report-header h1 {
            margin: 0 0 5px;
            color: #263f64;
            font-size: 22px;
        }

        .report-header h2 {
            margin: 0 0 5px;
            font-size: 16px;
        }

        .report-header p {
            margin: 0;
            color: #526179;
        }

        h3 {
            margin: 16px 0 8px;
            color: #263f64;
            font-size: 15px;
        }

        .topper-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .topper-card {
            min-height: 165px;
            padding: 10px 6px;
            text-align: center;
            border: 1px solid #cbd5e1;
            border-top: 4px solid #e8b923;
            border-radius: 8px;
        }

        .topper-card.rank-2 {
            border-top-color: #9aa8bb;
        }

        .topper-card.rank-3 {
            border-top-color: #bd7953;
        }

        .medal {
            font-size: 22px;
        }

        .position {
            margin: 2px 0 6px;
            font-weight: bold;
        }

        .photo,
        .initial {
            width: 52px;
            height: 52px;
            margin: 0 auto 6px;
            border-radius: 50%;
            object-fit: cover;
        }

        .initial {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: #5272a0;
            font-size: 22px;
            font-weight: bold;
        }

        .student-name {
            margin-bottom: 3px;
            font-size: 13px;
            font-weight: bold;
        }

        .student-meta {
            color: #526179;
            font-size: 10px;
            line-height: 1.4;
        }

        .percentage {
            margin-top: 4px;
            color: #176b53;
            font-size: 15px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            padding: 6px 5px;
            border: 1px solid #64748b;
            text-align: center;
            overflow-wrap: anywhere;
        }

        th {
            color: #fff;
            background: #263f64;
        }

        td.name {
            text-align: left;
        }

        @media print {
            .print-actions {
                display: none;
            }

            .report {
                max-width: none;
            }

            .topper-section {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button type="button" onclick="window.print()">Print Report</button>
    </div>

    <main class="report">
        @if($rankGroups->isNotEmpty())
            <header class="report-header">
                <h1>Lokmanya Vidya Niketan</h1>
                <h2>Topper List &amp; Result Analysis</h2>
                <p>
                    Class {{ \App\Academic\AcademicHelper::toRoman(request('class_name')) ?: request('class_name') }}
                    @if($rankingType === 'section')
                        - {{ request('section_name') }}
                    @elseif($rankingType === 'all_sections')
                        - All Sections Combined
                    @else
                        - Stream-Wise
                    @endif
                    &nbsp;|&nbsp; {{ $selectedExam->exam_title ?: $selectedExam->exam_name }}
                    &nbsp;|&nbsp; Session {{ str_replace('_', '-', $sessionYear) }}
                </p>
            </header>

            @foreach($rankGroups as $rankGroup)
                <section class="topper-section">
                    <h3>{{ $rankingType === 'stream_wise' ? $rankGroup['label'] . ' Stream' : $rankGroup['label'] }} &mdash; Top 3 Toppers</h3>
                    <div class="topper-grid">
                        @php
                            $medals = ['🥇', '🥈', '🥉'];
                        @endphp
                        @foreach($rankGroup['top_students'] as $index => $topper)
                            @php
                                $photoUrl = trim($topper['photo']);
                                $photoSrc = $photoUrl !== ''
                                    ? (preg_match('/^https?:\/\//i', $photoUrl) ? $photoUrl : asset($photoUrl))
                                    : null;
                                $studentInitial = mb_strtoupper(mb_substr(trim($topper['student_name']), 0, 1));
                            @endphp
                            <article class="topper-card rank-{{ $topper['rank'] }}">
                                <div class="medal">{{ $medals[$index] }}</div>
                                <div class="position">{{ $index + 1 }}{{ $index === 0 ? 'st' : ($index === 1 ? 'nd' : 'rd') }} Position</div>
                                @if($photoSrc)
                                    <img class="photo" src="{{ $photoSrc }}" alt="{{ $topper['student_name'] }}">
                                @else
                                    <div class="initial">{{ $studentInitial ?: '?' }}</div>
                                @endif
                                <div class="student-name">{{ $topper['student_name'] }}</div>
                                <div class="student-meta">
                                    Class {{ \App\Academic\AcademicHelper::toRoman($topper['class_name']) ?: $topper['class_name'] }} - {{ $topper['section_name'] }}<br>
                                    Roll No: {{ $topper['roll_no'] ?: '-' }}<br>
                                    {{ number_format($topper['total_obtained'], 1) }} / {{ number_format($topper['total_max'], 1) }} marks
                                </div>
                                <div class="percentage">{{ number_format($topper['percentage'], 2) }}%</div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="merit-section">
                    <h3>{{ $rankingType === 'stream_wise' ? $rankGroup['label'] . ' Stream' : $rankGroup['label'] }} &mdash; Complete Merit List</h3>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 8%;">Rank</th>
                                <th style="width: 27%;">Student Name</th>
                                <th style="width: 13%;">Roll Number</th>
                                <th style="width: 18%;">Class &amp; Section</th>
                                <th style="width: 12%;">Obtained</th>
                                <th style="width: 12%;">Maximum</th>
                                <th style="width: 10%;">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rankGroup['students'] as $student)
                                <tr>
                                    <td>{{ $student['rank'] }}</td>
                                    <td class="name">{{ $student['student_name'] }}</td>
                                    <td>{{ $student['roll_no'] ?: '-' }}</td>
                                    <td>{{ \App\Academic\AcademicHelper::toRoman($student['class_name']) ?: $student['class_name'] }} - {{ $student['section_name'] }}</td>
                                    <td>{{ number_format($student['total_obtained'], 1) }}</td>
                                    <td>{{ number_format($student['total_max'], 1) }}</td>
                                    <td><strong>{{ number_format($student['percentage'], 2) }}%</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>
            @endforeach
        @else
            <p>No student result marks were found for the selected filters.</p>
        @endif
    </main>
</body>
</html>
