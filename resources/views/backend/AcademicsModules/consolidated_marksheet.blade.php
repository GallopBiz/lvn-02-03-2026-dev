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
                                {{ $class }}
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
                        ?>
                        @foreach($groupedExams as $gExam)
                            <?php
                                $appliesToClass = empty($reqClassName)
                                    || empty($gExam['class_names'])
                                    || in_array($reqClassName, $gExam['class_names'], true)
                                    || in_array(ltrim($reqClassName, '0'), $gExam['class_names'], true);
                                $isSelected = (string) $reqExamId === (string) $gExam['id']
                                    || (isset($gExam['all_exam_ids']) && in_array((int) $reqExamId, $gExam['all_exam_ids'], true));
                            ?>
                            @if($appliesToClass)
                                <option value="{{ $gExam['id'] }}" {{ $isSelected ? 'selected' : '' }}>
                                    {{ $gExam['display_name'] }}
                                </option>
                            @endif
                        @endforeach
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

    @forelse($reports as $report)
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="card-title mb-1">{{ $report['class']->class_name }}{{ $report['class']->section_name ? ' - '.$report['class']->section_name : '' }}</h4>
                        <div class="text-muted">{{ $report['exam_title'] }} | Session: {{ str_replace('_', '-', $report['session_year'] ?? '') }}</div>
                    </div>
                    <span class="badge badge-primary">{{ count($report['students']) }} student(s)</span>
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
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Scholar No</th>
                                <th>Subjects</th>
                                <th>Total</th>
                                <th>%</th>
                                <th>Division</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report['students'] as $studentRow)
                                <tr>
                                    <td>{{ $studentRow['student']->student_name }}</td>
                                    <td>{{ $studentRow['student']->scholar_no }}</td>
                                    <td>{{ count($studentRow['subjects']) }}</td>
                                    <td>{{ $formatMark($studentRow['grand_total']) }}</td>
                                    <td>{{ $studentRow['percentage'] !== null ? $formatMark($studentRow['percentage']) : '' }}</td>
                                    <td>{{ $studentRow['division'] }}</td>
                                    <td>{{ $studentRow['result'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
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
