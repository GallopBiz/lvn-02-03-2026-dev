@extends('backend.layouts.main')

@section('main-container')
<div class="main-content pt-4">
    <div class="breadcrumb">
        <h2>Senior Consolidated Marksheet (Classes 11 & 12)</h2>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    @if(!empty($missingMarksClasses) && count($missingMarksClasses) > 0)
        <div class="alert alert-warning mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="alert-heading mb-1 font-weight-bold">
                        <i class="i-Warning-Window mr-2 font-weight-bold"></i> Marks Not Entered
                    </h5>
                    <p class="mb-0">
                        Marks have not been entered yet for class: <strong>{{ implode(', ', $missingMarksClasses) }}</strong>.
                        Report cards and mark totals will show blank until entered in the marks entry module.
                    </p>
                </div>
                <a href="{{ url('marks') }}" target="_blank" class="btn btn-warning font-weight-bold text-dark ml-3 text-nowrap">
                    <i class="i-Pen-4 mr-1"></i> Go to Marks Entry
                </a>
            </div>
        </div>
    @endif

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('academic.senior-consolidated-marksheets.index') }}" class="row align-items-end">
                <div class="col-md-2 form-group mb-md-0">
                    <label class="font-weight-bold">Class</label>
                    <select class="form-control" name="class_name" id="class_name">
                        <option value="">All Senior Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class }}" {{ (string) request('class_name') === (string) $class ? 'selected' : '' }}>
                                Class {{ \App\Academic\AcademicHelper::toRoman($class) ?: $class }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group mb-md-0">
                    <label class="font-weight-bold">Section</label>
                    <select class="form-control" name="section_name" id="section_name">
                        <option value="">All Sections</option>
                        @foreach($sections as $section)
                            <option value="{{ $section }}" {{ (string) request('section_name') === (string) $section ? 'selected' : '' }}>
                                {{ $section }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group mb-md-0">
                    <label class="font-weight-bold">Exam Name</label>
                    <select class="form-control" name="exam_id" id="exam_id">
                        <option value="">Auto Term I / Term II</option>
                        @foreach($groupedExams as $gExam)
                            @php
                                $isSelected = (string) request('exam_id') === (string) $gExam['id']
                                    || (isset($gExam['all_exam_ids']) && in_array((int) request('exam_id'), $gExam['all_exam_ids'], true));
                            @endphp
                            <option value="{{ $gExam['id'] }}" {{ $isSelected ? 'selected' : '' }}>
                                {{ $gExam['display_name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group mb-md-0">
                    <label class="font-weight-bold">Stream</label>
                    <select class="form-control" name="stream_id" id="stream_id">
                        <option value="">All Streams</option>
                        @foreach($streams as $st)
                            <option value="{{ $st->id }}" {{ (string) request('stream_id') === (string) $st->id ? 'selected' : '' }}>
                                {{ $st->stream_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" type="submit"><i class="i-Filter-2 mr-1"></i> Show Report</button>
                    <a class="btn btn-outline-secondary" href="{{ route('academic.senior-consolidated-marksheets.index') }}">Clear</a>
                    <a class="btn btn-success" target="_blank" href="{{ route('academic.senior-consolidated-marksheets.print', request()->only('class_name', 'section_name', 'exam_id', 'stream_id', 'combination_id')) }}">
                        <i class="i-Printer mr-1"></i> Print
                    </a>
                </div>
            </form>
        </div>
    </div>

    <style>
        .senior-consolidated-table { border-collapse: collapse !important; width: 100%; font-size: 12px; }
        .senior-consolidated-table th, .senior-consolidated-table td { border: 1px solid #c2c8d0 !important; padding: 4px 6px; vertical-align: middle; }
        .senior-consolidated-table thead th { background: #eef2f7; color: #23272e; font-weight: 700; text-align: center; font-size: 11.5px; }
        .senior-consolidated-table tbody.student-group { border-top: 2px solid #4b545c !important; }
        .senior-consolidated-table tr.rollup td { background: #f6f8fb; font-weight: bold; }
        .stream-group-card { border-left: 4px solid #1b5fbf; margin-bottom: 24px; background: #fff; }
        .stream-comb-header { background: #f0f4f9; padding: 8px 12px; font-size: 13px; font-weight: bold; color: #1b5fbf; border-bottom: 1px solid #dce2e9; }
        .badge-optional { display: inline-block; padding: 3px 7px; background: #e8f4fd; color: #0d6efd; border: 1px solid #b6d4fe; border-radius: 4px; font-size: 11px; font-weight: 600; }
    </style>

    @php
        $formatMark = fn ($value) => is_numeric($value) && (float) $value != 0 ? number_format((float) $value, 2, '.', '') : (is_numeric($value) && (float) $value == 0 ? '0.00' : '-');
        $formatPercentage = fn ($value) => is_numeric($value) ? number_format((float) $value, 2, '.', '') : '';
    @endphp

    @forelse($reports as $report)
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="border-bottom pb-3 mb-3">
                    <div class="text-center mb-2">
                        <h4 class="font-weight-bold mb-1" style="letter-spacing: 0.5px;">Lokmanya Vidya Niketan</h4>
                        <h5 class="text-primary font-weight-bold mb-1">
                            {{ $report['exam_title'] ?? (!empty($report['show_term_2']) ? 'Consolidated Term - II (Annual) Examination' : 'Consolidated Term - I Examination') }}
                        </h5>
                        <div class="text-muted font-weight-500">Session : {{ str_replace('_', '-', $report['session_year'] ?? '') }}</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center font-weight-bold text-dark px-1">
                        <div>
                            <i class="i-Student-Hat mr-1 text-primary"></i> Class-Section : 
                            <span class="text-primary font-weight-bold">{{ \App\Academic\AcademicHelper::formatClassSection($report['class']->class_name, $report['class']->section_name) }}</span>
                            <span class="badge badge-pill badge-info px-2 py-1 ml-2 font-weight-normal">{{ count($report['students']) }} Student(s)</span>
                        </div>
                        <div class="text-right">
                            <i class="i-Administrator mr-1 text-primary"></i> Class Teacher : <span>{{ $report['class_teacher'] ?: 'Not Assigned' }}</span>
                        </div>
                    </div>
                </div>

                @if(empty($report['has_marks']))
                    <div class="alert alert-warning py-2 px-3 d-flex align-items-center justify-content-between mb-3">
                        <span>
                            <i class="i-Warning-Window mr-2 font-weight-bold"></i>
                            <strong>Marks Missing:</strong> Marks have not been entered yet for this class / section.
                        </span>
                        <a href="{{ url('marks') }}" target="_blank" class="btn btn-sm btn-outline-warning text-dark font-weight-bold">
                            <i class="i-Pen-4 mr-1"></i> Enter Marks
                        </a>
                    </div>
                @endif

                @php $globalSerial = 1; @endphp

                @foreach($report['groups'] as $group)
                    <div class="stream-group-card shadow-sm rounded mb-4">
                        <div class="stream-comb-header d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-dark">STREAM:</span> <span class="text-primary font-weight-bold">{{ $group['stream_name'] }}</span>
                                <span class="mx-2 text-muted">|</span>
                                <span class="text-dark">COMBINATION:</span> <span class="text-success font-weight-bold">{{ $group['combination_name'] }}</span>
                            </div>
                            <span class="badge badge-pill badge-secondary font-weight-normal px-2 py-1">{{ count($group['students']) }} Student(s)</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm senior-consolidated-table text-center mb-0">
                                <thead>
                                    <tr>
                                        <th rowspan="2" style="width: 45px;">Sr.<br>No.</th>
                                        <th rowspan="2" class="text-left" style="min-width: 140px;">Student Name</th>
                                        <th rowspan="2" style="min-width: 85px;">Sch. No<br>Att.</th>
                                        <th rowspan="2" style="min-width: 110px;">Optional<br>Subject</th>
                                        <th rowspan="2" class="text-left" style="min-width: 130px;">Subject</th>
                                        <th colspan="5">Term - I</th>
                                        @if(!empty($report['show_term_2']))
                                            <th colspan="5">Term - II</th>
                                        @endif
                                        <th rowspan="2" style="width: 55px;">Perc%</th>
                                        <th rowspan="2" style="width: 60px;">Division</th>
                                        <th rowspan="2" style="width: 55px;">Result</th>
                                    </tr>
                                    <tr>
                                        <th style="width: 48px;">PT-1<br>(5)</th>
                                        <th style="width: 48px;">TH</th>
                                        <th style="width: 48px;">PR</th>
                                        <th style="width: 55px;">Total<br>(100)</th>
                                        <th style="width: 45px;">Grade</th>
                                        @if(!empty($report['show_term_2']))
                                            <th style="width: 48px;">PT-2<br>(5)</th>
                                            <th style="width: 48px;">TH</th>
                                            <th style="width: 48px;">PR</th>
                                            <th style="width: 55px;">Total<br>(100)</th>
                                            <th style="width: 45px;">Grade</th>
                                        @endif
                                    </tr>
                                </thead>
                                @foreach($group['students'] as $studentRow)
                                    @php
                                        $serialNo = $globalSerial++;
                                        $displaySubjects = collect($studentRow['subjects']);
                                        $rowspanCount = $displaySubjects->count() + ($displaySubjects->count() > 0 ? 1 : 0);
                                    @endphp
                                    <tbody class="student-group">
                                        @forelse($displaySubjects as $sIdx => $sub)
                                            <tr>
                                                @if($sIdx === 0)
                                                    <td rowspan="{{ $rowspanCount }}">{{ $serialNo }}</td>
                                                    <td rowspan="{{ $rowspanCount }}" class="text-left font-weight-500">
                                                        {{ $studentRow['student']->student_name }}
                                                    </td>
                                                    <td rowspan="{{ $rowspanCount }}">
                                                        {{ $studentRow['scholar_no'] }}<br>
                                                        <small class="text-muted">{{ $studentRow['attendance'] ?: '-' }}</small>
                                                    </td>
                                                    <td rowspan="{{ $rowspanCount }}">
                                                        <span class="badge-optional">{{ $studentRow['optional_subject'] ?: '-' }}</span>
                                                    </td>
                                                @endif
                                                <td class="text-left">{{ $sub['subject'] }}</td>
                                                {{-- Term 1 --}}
                                                <td>{{ is_numeric($sub['term_1']['pt']) ? $formatMark($sub['term_1']['pt']) : ($sub['term_1']['pt'] ?: '-') }}</td>
                                                <td>{{ is_numeric($sub['term_1']['theory']) ? $formatMark($sub['term_1']['theory']) : ($sub['term_1']['theory'] ?: '-') }}</td>
                                                <td>{{ is_numeric($sub['term_1']['practical']) ? $formatMark($sub['term_1']['practical']) : ($sub['term_1']['practical'] ?: '-') }}</td>
                                                <td class="font-weight-bold">{{ is_numeric($sub['term_1']['total']) ? $formatMark($sub['term_1']['total']) : ($sub['term_1']['total'] ?: '-') }}</td>
                                                <td>{{ $sub['term_1']['grade'] ?: '-' }}</td>
                                                {{-- Term 2 --}}
                                                @if(!empty($report['show_term_2']))
                                                    <td>{{ is_numeric($sub['term_2']['pt']) ? $formatMark($sub['term_2']['pt']) : ($sub['term_2']['pt'] ?: '-') }}</td>
                                                    <td>{{ is_numeric($sub['term_2']['theory']) ? $formatMark($sub['term_2']['theory']) : ($sub['term_2']['theory'] ?: '-') }}</td>
                                                    <td>{{ is_numeric($sub['term_2']['practical']) ? $formatMark($sub['term_2']['practical']) : ($sub['term_2']['practical'] ?: '-') }}</td>
                                                    <td class="font-weight-bold">{{ is_numeric($sub['term_2']['total']) ? $formatMark($sub['term_2']['total']) : ($sub['term_2']['total'] ?: '-') }}</td>
                                                    <td>{{ $sub['term_2']['grade'] ?: '-' }}</td>
                                                @endif
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td>{{ $serialNo }}</td>
                                                <td class="text-left font-weight-500">{{ $studentRow['student']->student_name }}</td>
                                                <td>
                                                    {{ $studentRow['scholar_no'] }}<br>
                                                    <small class="text-muted">{{ $studentRow['attendance'] ?: '-' }}</small>
                                                </td>
                                                <td><span class="badge-optional">{{ $studentRow['optional_subject'] ?: '-' }}</span></td>
                                                <td colspan="{{ !empty($report['show_term_2']) ? 11 : 6 }}" class="text-muted">No assigned subjects found</td>
                                                <td>{{ $studentRow['percentage'] !== null ? $formatPercentage($studentRow['percentage']) : '' }}</td>
                                                <td>{{ $studentRow['division'] }}</td>
                                                <td>{{ $studentRow['result_short'] ?? ($studentRow['result'] === 'Pass' ? 'P' : ($studentRow['result'] === 'Fail' ? 'F' : '')) }}</td>
                                            </tr>
                                        @endforelse

                                        @if($displaySubjects->count() > 0)
                                            <tr class="rollup">
                                                <td class="text-right font-weight-bold pr-2">Total:</td>
                                                <td></td>
                                                <td>{{ (float) ($studentRow['term_1_theory_total'] ?? 0) > 0 ? $formatMark($studentRow['term_1_theory_total']) : '' }}</td>
                                                <td>{{ (float) ($studentRow['term_1_practical_total'] ?? 0) > 0 ? $formatMark($studentRow['term_1_practical_total']) : '' }}</td>
                                                <td class="font-weight-bold text-primary">{{ (float) ($studentRow['term_1_total'] ?? 0) > 0 ? $formatMark($studentRow['term_1_total']) : '' }}</td>
                                                <td></td>
                                                @if(!empty($report['show_term_2']))
                                                    <td></td>
                                                    <td>{{ (float) ($studentRow['term_2_theory_total'] ?? 0) > 0 ? $formatMark($studentRow['term_2_theory_total']) : '' }}</td>
                                                    <td>{{ (float) ($studentRow['term_2_practical_total'] ?? 0) > 0 ? $formatMark($studentRow['term_2_practical_total']) : '' }}</td>
                                                    <td class="font-weight-bold text-primary">{{ (float) ($studentRow['term_2_total'] ?? 0) > 0 ? $formatMark($studentRow['term_2_total']) : '' }}</td>
                                                    <td></td>
                                                @endif
                                                <td class="font-weight-bold text-dark">{{ $studentRow['percentage'] !== null ? $formatPercentage($studentRow['percentage']) : '' }}</td>
                                                <td class="font-weight-bold">{{ $studentRow['division'] }}</td>
                                                <td class="font-weight-bold {{ $studentRow['result'] === 'Pass' ? 'text-success' : ($studentRow['result'] === 'Fail' ? 'text-danger' : '') }}">
                                                    {{ $studentRow['result_short'] ?? ($studentRow['result'] === 'Pass' ? 'P' : ($studentRow['result'] === 'Fail' ? 'F' : '')) }}
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                @endforeach
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="i-File-Clipboard-File--Text text-muted" style="font-size: 3rem;"></i>
                <h5 class="mt-3 text-muted">No senior consolidated marks found for the selected filter.</h5>
                <p class="text-muted">Please select Class, Section, and Exam above and click <strong>Show Report</strong>.</p>
            </div>
        </div>
    @endforelse
</div>
@endsection

