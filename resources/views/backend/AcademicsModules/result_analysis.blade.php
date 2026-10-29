@extends('backend.layouts.main')

@section('main-container')
<div class="main-content pt-4">
    <div class="breadcrumb d-flex align-items-center justify-content-between mb-3">
        <div>
            <h2 class="mb-0">Result Analysis</h2>
            <p class="text-muted mb-0 small">Class-wise Subject and Overall Performance Analysis</p>
        </div>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    {{-- Filter Card --}}
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <form method="GET" action="{{ route('academic.result-analysis.index') }}" class="row align-items-end g-2">
                <div class="col-md-3 form-group mb-md-0">
                    <label class="font-weight-bold text-dark small text-uppercase">Class</label>
                    <select class="form-control form-control-sm" name="class_name" id="class_name">
                        <option value="">All Classes</option>
                        @foreach($classes as $c)
                            <option value="{{ $c }}" {{ (string) request('class_name') === (string) $c ? 'selected' : '' }}>
                                Class {{ \App\Academic\AcademicHelper::toRoman($c) ?: $c }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group mb-md-0">
                    <label class="font-weight-bold text-dark small text-uppercase">Section</label>
                    <select class="form-control form-control-sm" name="section_name" id="section_name">
                        <option value="">All Sections</option>
                        @foreach($sections as $sec)
                            <option value="{{ $sec }}" {{ (string) request('section_name') === (string) $sec ? 'selected' : '' }}>
                                {{ $sec }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group mb-md-0">
                    <label class="font-weight-bold text-dark small text-uppercase">Exam Name</label>
                    <select class="form-control form-control-sm" name="exam_id" id="exam_id">
                        <option value="">Auto Select Exam</option>
                        @php
                            $reqClassName = (string) request('class_name');
                            $reqExamId = request('exam_id');
                        @endphp
                        @foreach($groupedExams as $gExam)
                            @php
                                $appliesToClass = empty($reqClassName)
                                    || empty($gExam['class_names'])
                                    || in_array($reqClassName, $gExam['class_names'], true)
                                    || in_array(ltrim($reqClassName, '0'), $gExam['class_names'], true);
                                $isSelected = (string) $reqExamId === (string) $gExam['id']
                                    || (isset($gExam['all_exam_ids']) && in_array((int) $reqExamId, $gExam['all_exam_ids'], true));
                            @endphp
                            @if($appliesToClass)
                                <option value="{{ $gExam['id'] }}" {{ $isSelected ? 'selected' : '' }}>
                                    {{ $gExam['display_name'] }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-center gap-1 flex-wrap">
                    <button class="btn btn-primary btn-sm px-3" type="submit">
                        <i class="i-Filter-2 mr-1"></i> Show Report
                    </button>
                    <a class="btn btn-outline-secondary btn-sm px-3" href="{{ route('academic.result-analysis.index') }}">
                        Clear
                    </a>
                    <a class="btn btn-outline-success btn-sm px-3" href="{{ route('academic.result-analysis.export-excel', request()->only('class_name', 'section_name', 'exam_id')) }}">
                        <i class="i-File-Excel mr-1"></i> Export Excel
                    </a>
                    <a class="btn btn-success btn-sm px-3" target="_blank" href="{{ route('academic.result-analysis.print', request()->only('class_name', 'section_name', 'exam_id')) }}">
                        <i class="i-Printer mr-1"></i> Print
                    </a>
                    @if($reports->isNotEmpty())
                        <button type="button" class="btn btn-outline-info btn-sm px-2" id="toggleStudentTableBtn" title="Show/Hide Student Tabulation Table">
                            <i class="i-Eye mr-1"></i> Student Details
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Report Cards --}}
    @if($reports->isEmpty())
        @if(request()->hasAny(['class_name', 'section_name', 'exam_id']))
            <div class="card shadow-sm border-0">
                <div class="card-body text-center py-5">
                    <i class="i-Information text-muted mb-3" style="font-size: 3rem;"></i>
                    <h5 class="text-dark font-weight-bold">No Records Found</h5>
                    <p class="text-muted mb-0">No student enrollment or examination marks were found matching your selected filters.</p>
                </div>
            </div>
        @else
            <div class="card shadow-sm border-0">
                <div class="card-body text-center py-5">
                    <i class="i-Bar-Chart text-primary mb-3" style="font-size: 3rem;"></i>
                    <h5 class="text-dark font-weight-bold">Select Filters to View Result Analysis</h5>
                    <p class="text-muted mb-0">Please select a Class, Section, and Exam above and click <strong>Show Report</strong>.</p>
                </div>
            </div>
        @endif
    @else
        @foreach($reports as $reportIndex => $report)
            <div class="card mb-5 shadow-sm border-0 result-report-card">
                {{-- School & Class Header Banner --}}
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                        <div>
                            <h4 class="mb-1 text-primary font-weight-bold">Lokmanya Vidya Niketan</h4>
                            <h5 class="mb-0 text-dark font-weight-bold">
                                Class Result Analysis &mdash; Class {{ \App\Academic\AcademicHelper::toRoman($report['class']->class_name) ?: $report['class']->class_name }} - {{ $report['class']->section_name }}
                            </h5>
                        </div>
                        <div class="text-md-right mt-2 mt-md-0">
                            <span class="badge bg-purple text-white px-3 py-1 font-weight-normal mr-2" style="background-color: #663399; font-size: 0.85rem;">
                                {{ $report['exam_title'] }}
                            </span>
                            <span class="badge bg-secondary text-white px-3 py-1 font-weight-normal" style="font-size: 0.85rem;">
                                Session: {{ str_replace('_', '-', $report['session_year']) }}
                            </span>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top small text-muted">
                        <div>
                            <strong>Class Teacher:</strong> {{ $report['class_teacher'] ?: 'Not Assigned' }}
                        </div>
                        <div>
                            <strong>Total On Roll:</strong> {{ $report['total_on_roll'] }} Students
                        </div>
                    </div>
                </div>

                <div class="card-body p-3">
                    {{-- Result Analysis Summary Table (The Reference Table) --}}
                    <div class="result-analysis-summary-wrapper mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="font-weight-bold text-dark text-uppercase mb-0 small">
                                <i class="i-Bar-Chart-2 mr-1"></i> Result Analysis Summary Table
                            </h6>
                            <span class="badge bg-primary text-white px-2 py-1 small">Official Summary</span>
                        </div>
                        <div class="table-responsive analysis-table-card">
                            <table class="table analysis-matrix-table mb-0 text-center">
                                <thead>
                                    <tr class="analysis-head-row">
                                        <th class="text-left metric-th" style="min-width: 175px;">
                                            METRIC
                                        </th>
                                        @foreach($report['subject_columns'] as $colKey => $colDef)
                                            <th class="subject-th" style="min-width: 95px;">
                                                {{ $colDef['label'] }}
                                            </th>
                                        @endforeach
                                        <th class="overall-th" style="min-width: 110px;">
                                            OVERALL
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $metricConfig = [
                                            'TOTAL ON ROLL'   => ['row_class' => 'row-enrollment', 'is_danger' => false],
                                            'APPEARED'        => ['row_class' => 'row-enrollment', 'is_danger' => false],
                                            'PASSED'          => ['row_class' => 'row-enrollment', 'is_danger' => false],
                                            'FAIL/ COMPT.'    => ['row_class' => 'row-danger-stat group-divider-bottom', 'is_danger' => true],
                                            'PASS PERCENTAGE' => ['row_class' => 'row-pass-pct', 'is_danger' => false],
                                            'HIGHEST'         => ['row_class' => 'row-benchmark', 'is_danger' => false],
                                            'LOWEST'          => ['row_class' => 'row-benchmark', 'is_danger' => false],
                                            'CLASS AVERAGE'   => ['row_class' => 'row-class-avg group-divider-bottom', 'is_danger' => false],
                                            'ABOVE 90'        => ['row_class' => 'row-distribution', 'is_danger' => false],
                                            'ABOVE 75'        => ['row_class' => 'row-distribution', 'is_danger' => false],
                                            'I Div.'          => ['row_class' => 'row-distribution', 'is_danger' => false],
                                            'II Div.'         => ['row_class' => 'row-distribution', 'is_danger' => false],
                                            'III Div.'        => ['row_class' => 'row-distribution', 'is_danger' => false],
                                            'Below 33'        => ['row_class' => 'row-danger-stat', 'is_danger' => true],
                                        ];
                                    @endphp

                                    @foreach($metricConfig as $metricKey => $cfg)
                                        <tr class="{{ $cfg['row_class'] }}">
                                            <td class="metric-label-cell">
                                                {{ $metricKey }}
                                            </td>
                                            @foreach($report['subject_columns'] as $colKey => $colDef)
                                                @php
                                                    $val = $report['stats'][$metricKey][$colKey] ?? 0;
                                                @endphp
                                                <td class="cell-val">
                                                    @if($cfg['is_danger'])
                                                        @if((float)$val > 0)
                                                            <span class="val-danger-badge">{{ $val }}</span>
                                                        @else
                                                            <span class="val-zero">0</span>
                                                        @endif
                                                    @else
                                                        {{ $val }}
                                                    @endif
                                                </td>
                                            @endforeach
                                            {{-- OVERALL Column --}}
                                            @php
                                                $overallVal = $report['stats'][$metricKey]['OVERALL'] ?? 0;
                                            @endphp
                                            <td class="overall-cell">
                                                @if($cfg['is_danger'])
                                                    @if((float)$overallVal > 0)
                                                        <span class="val-danger-badge">{{ $overallVal }}</span>
                                                    @else
                                                        <span class="val-zero">0</span>
                                                    @endif
                                                @else
                                                    {{ $overallVal }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Student Marks Tabulation Section (Collapsible) --}}
                    <div class="student-details-wrapper" id="student_wrapper_{{ $reportIndex }}" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="font-weight-bold text-dark text-uppercase mb-0 small">
                                <i class="i-List mr-1"></i> Student-wise Marks Tabulation
                            </h6>
                            <span class="text-muted small">Showing {{ count($report['students']) }} student records</span>
                        </div>
                        <div class="table-responsive border rounded" style="max-height: 480px; overflow-y: auto;">
                            <table class="table table-bordered table-hover table-sm student-marks-table mb-0 text-center">
                                <thead class="bg-light text-dark sticky-top">
                                    <tr>
                                        <th style="width: 45px;">S.No.</th>
                                        <th style="width: 85px;">Sch. No.</th>
                                        <th class="text-left" style="min-width: 170px;">Student Name</th>
                                        @if($report['is_senior'])
                                            <th style="min-width: 130px;">Combination</th>
                                        @endif
                                        @foreach($report['subject_columns'] as $colKey => $colDef)
                                            <th style="min-width: 80px;" class="text-uppercase">{{ $colDef['label'] }}</th>
                                        @endforeach
                                        <th style="width: 70px;">Total</th>
                                        <th style="width: 65px;">%</th>
                                        <th style="width: 75px;">Div.</th>
                                        <th style="width: 110px;">Result</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($report['students'] as $idx => $st)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td class="font-monospace small">{{ $st['scholar_no'] ?: '-' }}</td>
                                            <td class="text-left font-weight-bold text-dark">{{ $st['student_name'] }}</td>
                                            @if($report['is_senior'])
                                                <td>
                                                    @if(!empty($st['combination_name']))
                                                        <span class="badge bg-light text-dark border small text-uppercase" style="font-size: 10px;">
                                                            {{ $st['combination_name'] }}
                                                        </span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            @endif
                                            @foreach($report['subject_columns'] as $colKey => $colDef)
                                                @php
                                                    $score = $st['subject_scores'][$colKey] ?? null;
                                                @endphp
                                                <td class="{{ ($score && $score['is_failed']) ? 'text-danger font-weight-bold' : '' }}">
                                                    {{ $score ? $score['display'] : '-' }}
                                                    @if($colKey === 'OPTIONAL' && !empty($score['optional_name']) && $score['display'] !== '-')
                                                        <br><small class="text-muted" style="font-size: 9.5px;">({{ $score['optional_name'] }})</small>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="font-weight-bold text-dark">
                                                {{ $st['total_obtained'] !== null ? number_format($st['total_obtained'], 1) : '-' }}
                                            </td>
                                            <td class="font-weight-bold {{ ($st['percentage'] !== null && $st['percentage'] < 33.0) ? 'text-danger' : 'text-primary' }}">
                                                {{ $st['percentage'] !== null ? number_format($st['percentage'], 1) : '-' }}
                                            </td>
                                            <td class="small">{{ $st['division'] ?: '-' }}</td>
                                            <td>
                                                @if($st['result_code'] === 'PASS')
                                                    <span class="badge-result-pass">PASS</span>
                                                @elseif($st['result_code'] === 'COMP')
                                                    <span class="badge-result-comp">{{ $st['result_status'] }}</span>
                                                @elseif($st['result_code'] === 'FAIL')
                                                    <span class="badge-result-fail">FAIL</span>
                                                @else
                                                    <span class="badge-result-ab">AB</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ count($report['subject_columns']) + ($report['is_senior'] ? 8 : 7) }}" class="text-muted py-3">
                                                No student marks recorded.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

<style>
    .result-report-card {
        border-radius: 8px;
        overflow: hidden;
    }

    /* ==========================================================================
       Result Analysis Matrix Table (Summary Table) Styling
       ========================================================================== */
    .analysis-table-card {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .analysis-matrix-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        letter-spacing: 0.1px;
    }

    .analysis-matrix-table th,
    .analysis-matrix-table td {
        vertical-align: middle;
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        font-variant-numeric: tabular-nums;
    }

    /* Table Header */
    .analysis-head-row th {
        background-color: #f1f5f9;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 10px 12px;
        border-bottom: 2px solid #94a3b8;
    }

    .analysis-head-row th.metric-th {
        background-color: #e2e8f0;
        color: #0f172a;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.8px;
    }

    .analysis-head-row th.overall-th {
        background-color: #cbd5e1;
        color: #0f172a;
        font-weight: 800;
        letter-spacing: 0.8px;
        border-left: 2px solid #94a3b8;
    }

    /* Metric Column (Left Header Column) */
    .analysis-matrix-table tbody td.metric-label-cell {
        background-color: #f8fafc;
        color: #1e293b;
        font-weight: 600;
        font-size: 12.5px;
        text-align: left;
        padding-left: 16px;
        letter-spacing: 0.2px;
        border-right: 1px solid #cbd5e1;
    }

    /* Standard cell values */
    .analysis-matrix-table tbody td.cell-val {
        color: #1e293b;
        font-size: 13px;
        font-weight: 500;
    }

    /* OVERALL Column */
    .analysis-matrix-table tbody td.overall-cell {
        background-color: #f8fafc;
        color: #0f172a;
        font-weight: 700;
        border-left: 2px solid #cbd5e1;
    }

    /* Hover effect */
    .analysis-matrix-table tbody tr {
        transition: background-color 0.15s ease-in-out;
    }

    .analysis-matrix-table tbody tr:hover td {
        background-color: #f1f5f9;
    }

    .analysis-matrix-table tbody tr:hover td.metric-label-cell {
        background-color: #e2e8f0;
        color: #0f172a;
    }

    .analysis-matrix-table tbody tr:hover td.overall-cell {
        background-color: #e2e8f0;
    }

    /* Group Divider Rows (2px separator for logical grouping) */
    .analysis-matrix-table tr.group-divider-bottom td {
        border-bottom: 2px solid #cbd5e1 !important;
    }

    /* Special Highlight: Pass Percentage */
    .analysis-matrix-table tr.row-pass-pct td {
        background-color: #f0f9ff;
        color: #0369a1;
        font-weight: 700;
    }
    .analysis-matrix-table tr.row-pass-pct td.metric-label-cell {
        background-color: #e0f2fe;
        color: #0284c7;
    }
    .analysis-matrix-table tr.row-pass-pct td.overall-cell {
        background-color: #bae6fd;
        color: #0369a1;
    }
    .analysis-matrix-table tbody tr.row-pass-pct:hover td {
        background-color: #e0f2fe;
    }

    /* Special Highlight: Class Average */
    .analysis-matrix-table tr.row-class-avg td {
        background-color: #f0fdf4;
        color: #15803d;
        font-weight: 700;
    }
    .analysis-matrix-table tr.row-class-avg td.metric-label-cell {
        background-color: #dcfce7;
        color: #166534;
    }
    .analysis-matrix-table tr.row-class-avg td.overall-cell {
        background-color: #bbf7d0;
        color: #14532d;
    }
    .analysis-matrix-table tbody tr.row-class-avg:hover td {
        background-color: #dcfce7;
    }

    /* Subtle Muted Zeros */
    .val-zero {
        color: #94a3b8;
        font-weight: 400;
    }

    /* Danger Badge (for Fail/Compt and Below 33 when value > 0) */
    .val-danger-badge {
        display: inline-block;
        padding: 2px 7px;
        background-color: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
        border-radius: 4px;
        font-weight: 700;
        font-size: 12px;
    }

    /* ==========================================================================
       Student Marks Tabulation Table Styling
       ========================================================================== */
    .student-marks-table {
        font-size: 12px;
        border-collapse: collapse;
    }

    .student-marks-table th,
    .student-marks-table td {
        vertical-align: middle;
        padding: 6px 8px;
        border: 1px solid #e2e8f0;
        font-variant-numeric: tabular-nums;
    }

    .student-marks-table thead th {
        background-color: #f8fafc;
        color: #334155;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        border-bottom: 2px solid #cbd5e1;
    }

    .student-marks-table tbody tr:hover td {
        background-color: #f8fafc;
    }

    /* Student Status Pill Badges */
    .badge-result-pass {
        background-color: #dcfce7;
        color: #15803d;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 12px;
        font-size: 11px;
        display: inline-block;
        border: 1px solid #bbf7d0;
    }

    .badge-result-comp {
        background-color: #fee2e2;
        color: #b91c1c;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 12px;
        font-size: 11px;
        display: inline-block;
        border: 1px solid #fecaca;
    }

    .badge-result-fail {
        background-color: #fef2f2;
        color: #dc2626;
        font-weight: 700;
        padding: 3px 9px;
        border-radius: 12px;
        font-size: 11px;
        display: inline-block;
        border: 1px solid #fca5a5;
    }

    .badge-result-ab {
        background-color: #f1f5f9;
        color: #64748b;
        font-weight: 600;
        padding: 3px 9px;
        border-radius: 12px;
        font-size: 11px;
        display: inline-block;
        border: 1px solid #e2e8f0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sectionsByClass = @json($sectionsByClass ?? []);
        const classSelect = document.getElementById('class_name');
        const sectionSelect = document.getElementById('section_name');

        if (classSelect && sectionSelect) {
            classSelect.addEventListener('change', function () {
                const selectedClass = this.value;
                const currentSec = sectionSelect.value;
                sectionSelect.innerHTML = '<option value="">All Sections</option>';

                if (selectedClass && sectionsByClass[selectedClass]) {
                    sectionsByClass[selectedClass].forEach(function (sec) {
                        const opt = document.createElement('option');
                        opt.value = sec;
                        opt.textContent = sec;
                        if (sec === currentSec) opt.selected = true;
                        sectionSelect.appendChild(opt);
                    });
                }
            });
        }

        const toggleBtn = document.getElementById('toggleStudentTableBtn');
        if (toggleBtn) {
            let visible = false;
            toggleBtn.addEventListener('click', function () {
                visible = !visible;
                document.querySelectorAll('.student-details-wrapper').forEach(function (el) {
                    el.style.display = visible ? 'block' : 'none';
                });
                toggleBtn.innerHTML = visible
                    ? '<i class="i-Eye-Slash mr-1"></i> Hide Student Details'
                    : '<i class="i-Eye mr-1"></i> Student Details';
            });
        }
    });
</script>
@endsection

