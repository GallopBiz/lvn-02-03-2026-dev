@extends('backend.layouts.main')

@section('main-container')
<div class="main-content pt-4">
    @php($formatMark = fn ($value) => (string) round((float) $value, 0, PHP_ROUND_HALF_UP))
    <div class="breadcrumb">
        <h2>Consolidated Marksheet</h2>
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
                        Report cards and mark totals will show blank or zero marks until entered in the marks entry module.
                    </p>
                </div>
                <a href="{{ url('marks') }}" target="_blank" class="btn btn-warning font-weight-bold text-dark ml-3 text-nowrap">
                    <i class="i-Pen-4 mr-1"></i> Go to Marks Entry
                </a>
            </div>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('academic.consolidated-marksheets.index') }}" class="row align-items-end">
                <div class="col-md-3 form-group mb-md-0">
                    <label>Class</label>
                    <select class="form-control" name="class_name" id="class_name">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class }}" {{ (string) request('class_name') === (string) $class ? 'selected' : '' }}>
                                {{ \App\Academic\AcademicHelper::toRoman($class) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group mb-md-0">
                    <label>Section</label>
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
                    <label>Exam Name</label>
                    <select class="form-control" name="exam_id" id="exam_id">
                        <option value="">Auto Term I / Term II</option>
                        <?php
                            $reqClassName = (string) request('class_name');
                            $reqExamId = request('exam_id');
                            foreach ($groupedExams as $gExam) {
                                $appliesToClass = empty($reqClassName)
                                    || empty($gExam['class_names'])
                                    || in_array($reqClassName, $gExam['class_names'], true)
                                    || in_array(ltrim($reqClassName, '0'), $gExam['class_names'], true);
                                $isSelected = (string) $reqExamId === (string) $gExam['id']
                                    || (isset($gExam['all_exam_ids']) && in_array((int) $reqExamId, $gExam['all_exam_ids'], true));
                                if ($appliesToClass) {
                        ?>
                                    <option value="{{ $gExam['id'] }}" {{ $isSelected ? 'selected' : '' }}>
                                        {{ $gExam['display_name'] }}
                                    </option>
                        <?php
                                }
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" type="submit">Show Report</button>
                    <a class="btn btn-outline-secondary" href="{{ route('academic.consolidated-marksheets.index') }}">Clear</a>
                    <a class="btn btn-success" href="{{ route('academic.consolidated-marksheets.print', request()->only('class_name', 'section_name', 'exam_id')) }}">Print</a>
                </div>
            </form>
        </div>
    </div>

    <style>
        .consolidated-table { border-collapse: collapse !important; width: 100%; font-size: 11.5px; }
        .consolidated-table th, .consolidated-table td { border: 1px solid #c2c8d0 !important; padding: 4px 6px; vertical-align: middle; }
        .consolidated-table thead th { background: #eef2f7; color: #23272e; font-weight: 700; text-align: center; }
        .consolidated-table tbody.student-group { border-top: 2px solid #4b545c !important; }
        .consolidated-table tr.rollup td { background: #f6f8fb; font-weight: bold; }
        .consolidated-header-box { border-bottom: 2px solid #e5e9f0; padding-bottom: 12px; margin-bottom: 15px; }
    </style>

    <?php
        $formatMark = fn ($value) => is_numeric($value) && (float) $value != 0 ? number_format((float) $value, 2, '.', '') : (is_numeric($value) && (float) $value == 0 ? '0.00' : '-');
        $formatPercentage = fn ($value) => is_numeric($value) ? number_format((float) $value, 2, '.', '') : '';
        $hasSubject = function ($subjects, $subject) {
            $needle = strtolower(preg_replace('/\s+/', ' ', trim((string) $subject)));
            return collect($subjects)->contains(fn ($item) => str_contains($item, $needle) || str_contains($needle, $item));
        };
    ?>

    @forelse($reports as $report)
        <?php
            $className = (string) ($report['class']->class_name ?? '');
            $isClassOne = preg_match('/(^|\D)(1|i|one|first)(\D|$)/i', $className);
            $normClass = \App\Academic\AcademicHelper::normalizeClassIdentifier($report['class']->class_name ?? '');
            $isClass10Or12 = in_array($normClass, ['10', '12'], true);

            $term1Heading = 'Half Yearly Exam';
            $term2Heading = $isClass10Or12 ? 'Pre Board' : 'Annual Exam';

            $rawExamTitle = $report['exam_title'] ?? (!empty($report['show_term_2']) ? ($isClass10Or12 ? 'Consolidated Pre Board Examination' : 'Consolidated Annual Examination') : 'Consolidated Half Yearly Examination');
            $examTitleDisplay = $rawExamTitle;
            if (preg_match('/\bterm[\s_-]*(?:2|ii)\b/i', $examTitleDisplay) || str_contains(strtolower($examTitleDisplay), 'annual')) {
                $examTitleDisplay = $isClass10Or12 ? 'Consolidated Pre Board Examination' : 'Consolidated Annual Examination';
            } elseif (preg_match('/\bterm[\s_-]*(?:1|i)\b/i', $examTitleDisplay)) {
                $examTitleDisplay = 'Consolidated Half Yearly Examination';
            }
        ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="consolidated-header-box">
                    <div class="text-center mb-2">
                        <h4 class="font-weight-bold mb-1" style="letter-spacing: 0.5px;">Lokmanya Vidya Niketan</h4>
                        <h5 class="text-primary font-weight-bold mb-1">{{ $examTitleDisplay }}</h5>
                        <div class="text-muted font-weight-500">Session : {{ str_replace('_', '-', $report['session_year'] ?? '') }}</div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center font-weight-bold text-dark px-1">
                        <div>
                            <i class="i-Student-Hat mr-1 text-primary"></i> Class-Section : <span class="text-primary">{{ \App\Academic\AcademicHelper::formatClassSection($report['class']->class_name, $report['class']->section_name) }}</span>
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

                <div class="table-responsive">
                    <table class="table table-bordered table-sm consolidated-table text-center mb-0">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width: 45px;">Sr.<br>No.</th>
                                <th rowspan="2" class="text-left" style="min-width: 140px;">Student Name</th>
                                <th rowspan="2" style="min-width: 85px;">Sch. No<br>Att.</th>
                                <th rowspan="2" class="text-left" style="min-width: 130px;">Subject</th>
                                <th colspan="7">{{ $term1Heading }}</th>
                                @if(!empty($report['show_term_2']))
                                    <th colspan="7">{{ $term2Heading }}</th>
                                @endif
                                <th rowspan="2" style="width: 55px;">Perc%</th>
                                <th rowspan="2" style="width: 50px;">Division</th>
                                <th rowspan="2" style="width: 45px;">Result</th>
                            </tr>
                            <tr>
                                <th style="width: 40px;">PT<br><small>(5)</small></th>
                                <th style="width: 40px;">MA<br><small>(5)</small></th>
                                <th style="width: 40px;">PF<br><small>(5)</small></th>
                                <th style="width: 40px;">SE<br><small>(5)</small></th>
                                <th style="width: 48px;">HY<br><small>(80)</small></th>
                                <th style="width: 55px;">Total<br><small>(100)</small></th>
                                <th style="width: 45px;">Grade</th>
                                @if(!empty($report['show_term_2']))
                                    <th style="width: 40px;">PT<br><small>(5)</small></th>
                                    <th style="width: 40px;">MA<br><small>(5)</small></th>
                                    <th style="width: 40px;">NBS<br><small>(5)</small></th>
                                    <th style="width: 40px;">SE<br><small>(5)</small></th>
                                    <th style="width: 48px;">ANN<br><small>(80)</small></th>
                                    <th style="width: 55px;">Total<br><small>(100)</small></th>
                                    <th style="width: 45px;">Grade</th>
                                @endif
                            </tr>
                        </thead>
                        @foreach($report['students'] as $studentIndex => $studentRow)
                            <?php
                                $serialNo = $studentIndex + 1;
                                $subjectRows = collect($studentRow['subjects']);
                                $existingSubjects = $subjectRows->pluck('subject')->map(fn ($subject) => strtolower(preg_replace('/\s+/', ' ', trim((string) $subject))))->all();
                                $specialNames = $report['special_subject_names'] ?? [];
                                $classOneSubjects = $isClassOne
                                    ? collect(['Computer', 'Sanskrit'])
                                        ->reject(fn ($subject) => in_array(strtolower(trim((string) $subject)), $specialNames, true) || $hasSubject($existingSubjects, $subject))
                                        ->map(fn ($subject) => [
                                            'subject' => $subject,
                                            'term_1' => ['pt' => '', 'mas' => '', 'pf' => '', 'sea' => '', 'theory' => '', 'total' => '', 'grade' => ''],
                                            'term_2' => ['pt' => '', 'mas' => '', 'pf' => '', 'sea' => '', 'theory' => '', 'total' => '', 'grade' => ''],
                                            'percentage' => null,
                                            'division' => '',
                                            'result' => '',
                                            'result_short' => '',
                                        ])
                                        ->values()
                                    : collect();
                                $displaySubjects = $subjectRows
                                    ->reject(fn ($s) => in_array(strtolower(trim((string) ($s['subject'] ?? ''))), $specialNames, true))
                                    ->merge($classOneSubjects)
                                    ->values();
                                $rowspanCount = $displaySubjects->count() + ($subjectRows->count() > 0 ? 1 : 0);
                            ?>
                            <tbody class="student-group">
                                @forelse($displaySubjects as $subjectIndex => $subject)
                                    <tr>
                                        @if($subjectIndex === 0)
                                            <td rowspan="{{ $rowspanCount }}" class="align-middle font-weight-bold">{{ $serialNo }}</td>
                                            <td rowspan="{{ $rowspanCount }}" class="align-middle text-left font-weight-bold text-dark">{{ $studentRow['student']->student_name }}</td>
                                            <td rowspan="{{ $rowspanCount }}" class="align-middle">
                                                <div class="font-weight-500">{{ $studentRow['student']->scholar_no }}</div>
                                                <div class="text-muted small">{{ $studentRow['attendance'] ?: '-' }}</div>
                                            </td>
                                        @endif
                                        <td class="text-left font-weight-500 text-dark">{{ $subject['subject'] }}</td>
                                        <td>{{ is_numeric($subject['term_1']['pt']) ? $formatMark($subject['term_1']['pt']) : '-' }}</td>
                                        <td>{{ is_numeric($subject['term_1']['mas']) ? $formatMark($subject['term_1']['mas']) : '-' }}</td>
                                        <td>{{ is_numeric($subject['term_1']['pf']) ? $formatMark($subject['term_1']['pf']) : '-' }}</td>
                                        <td>{{ is_numeric($subject['term_1']['sea']) ? $formatMark($subject['term_1']['sea']) : '-' }}</td>
                                        <td>{{ is_numeric($subject['term_1']['theory']) ? $formatMark($subject['term_1']['theory']) : '-' }}</td>
                                        <td class="font-weight-bold text-dark">{{ is_numeric($subject['term_1']['total']) ? $formatMark($subject['term_1']['total']) : '-' }}</td>
                                        <td class="font-weight-bold">{{ $subject['term_1']['grade'] ?: '-' }}</td>
                                        @if(!empty($report['show_term_2']))
                                            <td>{{ is_numeric($subject['term_2']['pt']) ? $formatMark($subject['term_2']['pt']) : '-' }}</td>
                                            <td>{{ is_numeric($subject['term_2']['mas']) ? $formatMark($subject['term_2']['mas']) : '-' }}</td>
                                            <td>{{ is_numeric($subject['term_2']['pf']) ? $formatMark($subject['term_2']['pf']) : '-' }}</td>
                                            <td>{{ is_numeric($subject['term_2']['sea']) ? $formatMark($subject['term_2']['sea']) : '-' }}</td>
                                            <td>{{ is_numeric($subject['term_2']['theory']) ? $formatMark($subject['term_2']['theory']) : '-' }}</td>
                                            <td class="font-weight-bold text-dark">{{ is_numeric($subject['term_2']['total']) ? $formatMark($subject['term_2']['total']) : '-' }}</td>
                                            <td class="font-weight-bold">{{ $subject['term_2']['grade'] ?: '-' }}</td>
                                        @endif
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td>{{ $serialNo }}</td>
                                        <td class="text-left font-weight-bold">{{ $studentRow['student']->student_name }}</td>
                                        <td>
                                            <div>{{ $studentRow['student']->scholar_no }}</div>
                                            <div class="text-muted small">{{ $studentRow['attendance'] ?: '-' }}</div>
                                        </td>
                                        <td colspan="{{ !empty($report['show_term_2']) ? 15 : 8 }}" class="text-muted">No assigned subjects found</td>
                                        <td>{{ $studentRow['percentage'] !== null ? $formatPercentage($studentRow['percentage']) : '-' }}</td>
                                        <td>{{ $studentRow['division'] ?: '-' }}</td>
                                        <td>
                                            <?php $res = $studentRow['result_short'] ?? ($studentRow['result'] === 'Pass' ? 'P' : ($studentRow['result'] === 'Fail' ? 'F' : '')); ?>
                                            @if($res === 'P')
                                                <span class="badge badge-success px-2">P</span>
                                            @elseif($res === 'F')
                                                <span class="badge badge-danger px-2">F</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                                @if($subjectRows->count() > 0)
                                    <tr class="rollup">
                                        <td colspan="5"></td>
                                        <td>{{ (float) ($studentRow['term_1_theory_total'] ?? 0) > 0 ? $formatMark($studentRow['term_1_theory_total']) : '' }}</td>
                                        <td class="font-weight-bold text-dark">{{ (float) ($studentRow['term_1_total'] ?? 0) > 0 ? $formatMark($studentRow['term_1_total']) : '' }}</td>
                                        <td></td>
                                        @if(!empty($report['show_term_2']))
                                            <td colspan="4"></td>
                                            <td>{{ (float) ($studentRow['term_2_theory_total'] ?? 0) > 0 ? $formatMark($studentRow['term_2_theory_total']) : '' }}</td>
                                            <td class="font-weight-bold text-primary">{{ (float) ($studentRow['term_2_total'] ?? 0) > 0 ? $formatMark($studentRow['term_2_total']) : ((float) ($studentRow['grand_total'] ?? 0) > 0 ? $formatMark($studentRow['grand_total']) : '') }}</td>
                                            <td></td>
                                        @endif
                                        <td class="font-weight-bold text-primary">{{ $studentRow['percentage'] !== null ? $formatPercentage($studentRow['percentage']) : '' }}</td>
                                        <td class="font-weight-bold text-primary">{{ $studentRow['division'] }}</td>
                                        <td>
                                            <?php $res = $studentRow['result_short'] ?? ($studentRow['result'] === 'Pass' ? 'P' : ($studentRow['result'] === 'Fail' ? 'F' : $studentRow['result'])); ?>
                                            @if($res === 'P')
                                                <span class="badge badge-success px-2 py-1">P</span>
                                            @elseif($res === 'F')
                                                <span class="badge badge-danger px-2 py-1">F</span>
                                            @else
                                                {{ $res }}
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    @empty
        @if(request()->hasAny(['class_name', 'section_name', 'class_id', 'exam_id']))
            <div class="alert alert-warning p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="alert-heading mb-1 font-weight-bold">
                            <i class="i-Warning-Window mr-2 font-weight-bold"></i> No Marks Found / Not Entered
                        </h5>
                        <p class="mb-0">
                            Marks have not been entered yet for the selected class/exam in the marks entry module.
                        </p>
                    </div>
                    <a href="{{ url('marks') }}" target="_blank" class="btn btn-warning font-weight-bold text-dark ml-3 text-nowrap">
                        <i class="i-Pen-4 mr-1"></i> Go to Marks Entry
                    </a>
                </div>
            </div>
        @endif
    @endforelse
</div>

<script>
    (function () {
        var sectionsByClass = @json($sectionsByClass);
        var groupedExams = @json($groupedExams);
        var classSelect = document.getElementById('class_name');
        var sectionSelect = document.getElementById('section_name');
        var examSelect = document.getElementById('exam_id');
        var selectedSection = @json(request('section_name'));
        var selectedExamId = @json(request('exam_id'));

        function renderSections() {
            var className = classSelect ? classSelect.value.trim() : '';
            var sections = className ? (sectionsByClass[className] || []) : Object.values(sectionsByClass).flat();
            sections = sections.filter(function (section, index, self) {
                return section && self.indexOf(section) === index;
            });

            sectionSelect.innerHTML = '<option value="">All Sections</option>';
            sections.forEach(function (section) {
                var option = document.createElement('option');
                option.value = section;
                option.textContent = section;
                option.selected = section === selectedSection;
                sectionSelect.appendChild(option);
            });
        }

        function renderExams() {
            if (!examSelect) return;
            var className = classSelect ? classSelect.value.trim() : '';
            var currentVal = examSelect.value || selectedExamId;

            var filtered = groupedExams.filter(function (g) {
                if (!className) return true;
                if (!g.class_names || g.class_names.length === 0) return true;
                return g.class_names.some(function (cName) {
                    return String(cName).toLowerCase() === String(className).toLowerCase() ||
                           String(cName).replace(/^0+/, '') === String(className).replace(/^0+/, '');
                });
            });

            examSelect.innerHTML = '<option value="">Auto Term I / Term II</option>';
            var matched = false;
            filtered.forEach(function (g) {
                var opt = document.createElement('option');
                opt.value = g.id;
                opt.textContent = g.display_name;
                if (currentVal && (String(g.id) === String(currentVal) || (g.all_exam_ids && g.all_exam_ids.map(String).indexOf(String(currentVal)) !== -1))) {
                    opt.selected = true;
                    matched = true;
                }
                examSelect.appendChild(opt);
            });

            if (currentVal && !matched) {
                examSelect.value = '';
            }
        }

        if (classSelect) {
            classSelect.addEventListener('change', function () {
                selectedSection = '';
                selectedExamId = '';
                renderSections();
                renderExams();
            });
        }
    })();
</script>
@endsection
