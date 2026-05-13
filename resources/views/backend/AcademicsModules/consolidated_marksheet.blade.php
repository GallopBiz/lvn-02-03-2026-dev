@extends('backend.layouts.main')

@section('main-container')
<div class="main-content pt-4">
    <div class="breadcrumb">
        <h2>Consolidated Marksheet</h2>
    </div>
    <div class="separator-breadcrumb border-top"></div>

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
                    <select class="form-control" name="exam_id">
                        <option value="">Auto Term I / Term II</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" {{ (string) request('exam_id') === (string) $exam->id ? 'selected' : '' }}>
                                {{ $exam->exam_name }} - {{ $exam->exam_type }}{{ $exam->classInfo ? ' ('.$exam->classInfo->class_name.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" type="submit">Show Report</button>
                    <a class="btn btn-outline-secondary" href="{{ route('academic.consolidated-marksheets.index') }}">Clear</a>
                    <a class="btn btn-success" target="_blank" href="{{ route('academic.consolidated-marksheets.print', request()->only('class_name', 'section_name', 'exam_id')) }}">Print</a>
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
                                    <td>{{ number_format($studentRow['grand_total'], 2) }}</td>
                                    <td>{{ $studentRow['percentage'] !== null ? number_format($studentRow['percentage'], 2) : '' }}</td>
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
            <div class="alert alert-warning">No consolidated marks found for the selected filters.</div>
        @endif
    @endforelse
</div>

<script>
    (function () {
        var sectionsByClass = @json($sectionsByClass);
        var classSelect = document.getElementById('class_name');
        var sectionSelect = document.getElementById('section_name');
        var selectedSection = @json(request('section_name'));

        function renderSections() {
            var className = classSelect.value;
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

        classSelect.addEventListener('change', function () {
            selectedSection = '';
            renderSections();
        });
    })();
</script>
@endsection
