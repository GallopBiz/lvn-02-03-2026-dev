@extends('backend.layouts.main')

@section('main-container')
<div class="main-content pt-4">
    <div class="breadcrumb">
        <h2>Marksheet Management</h2>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row">
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title">Generate / Print Marksheet</h4>
                    <form method="POST" action="{{ route('academic.marksheets.generate') }}" id="marksheetGenerateForm">
                        @csrf
                        <div class="form-group">
                            <label>Class</label>
                            <select class="form-control" name="class_picker" id="print_class_picker" onchange="window.location='{{ route('marksheet') }}?class_id='+this.value">
                                <option value="">Select class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ (string) $selectedClassId === (string) $class->id ? 'selected' : '' }}>
                                        {{ $class->class_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                        <div class="form-group">
                            <label>Exam</label>
                            <select class="form-control" name="exam_id" id="print_exam_id">
                                <option value="">Select exam</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}">{{ $exam->exam_name }} - {{ $exam->exam_type }}{{ $exam->classInfo ? ' ('.$exam->classInfo->class_name.')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Student</label>
                            <select class="form-control" name="student_id" id="print_student_id">
                                <option value="">All students in selected class</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->student_name }}{{ $student->scholar_no ? ' - '.$student->scholar_no : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-success" type="submit">Generate & Save</button>
                        <button class="btn btn-outline-primary" type="button" onclick="openMarksheetPreview()">Print Preview</button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h4 class="card-title">Generated Marksheets</h4>
            <form method="GET" action="{{ route('marksheet') }}" class="row align-items-end mb-3">
                <input type="hidden" name="class_id" value="{{ $selectedClassId }}">
                <div class="col-md-3 form-group mb-md-0">
                    <label>Search Generated Marksheets</label>
                    <input class="form-control" name="generated_search" value="{{ $generatedSearch }}" placeholder="Search student, scholar no, exam or session">
                </div>
                <div class="col-md-3 form-group mb-md-0">
                    <label>Class Filter</label>
                    <select class="form-control" name="generated_class_id">
                        <option value="">All Classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ (string) $generatedClassId === (string) $class->id ? 'selected' : '' }}>
                                {{ $class->class_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group mb-md-0">
                    <label>Exam Filter</label>
                    <select class="form-control" name="generated_exam_id">
                        <option value="">All Exams</option>
                        @foreach($exams as $exam)
                            <option value="{{ $exam->id }}" {{ (string) $generatedExamId === (string) $exam->id ? 'selected' : '' }}>
                                {{ $exam->exam_name }}{{ $exam->classInfo ? ' ('.$exam->classInfo->class_name.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary" type="submit">Search</button>
                    <a class="btn btn-outline-secondary" href="{{ route('marksheet', array_filter(['class_id' => $selectedClassId])) }}">Clear</a>
                </div>
            </form>

            <form method="POST" action="{{ route('academic.marksheets.generated.bulk-print') }}" id="generatedBulkForm">
                @csrf
                <div class="mb-2">
                    <button class="btn btn-sm btn-primary" type="button" onclick="submitGeneratedBulk('print')">Bulk Print</button>
                    <button class="btn btn-sm btn-danger" type="button" onclick="submitGeneratedBulk('delete')">Bulk Delete</button>
                    <span class="text-muted ml-2">{{ $generatedMarksheets->total() }} total record(s)</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th style="width: 34px;">
                                    <input type="checkbox" id="select_all_generated" onclick="toggleGeneratedSelection(this)">
                                </th>
                                <th>Student</th>
                                <th>Scholar No</th>
                                <th>Class</th>
                                <th>Exam</th>
                                <th>Session</th>
                                <th>Generated On</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($generatedMarksheets as $marksheet)
                                @php($snapshot = $marksheet->snapshot ?: [])
                                @php($printData = $snapshot['print_data'] ?? [])
                                @php($studentMeta = $printData['student_meta'] ?? [])
                                <tr>
                                    <td>
                                        <input type="checkbox" class="generated-check" name="marksheet_ids[]" value="{{ $marksheet->id }}">
                                    </td>
                                    <td>{{ $snapshot['student_name'] ?? ('Student #'.$marksheet->student_id) }}</td>
                                    <td>{{ $snapshot['scholar_no'] ?? ($studentMeta['scholar_no'] ?? '-') }}</td>
                                    <td>{{ $snapshot['class_name'] ?? ('Class #'.$marksheet->class_id) }}</td>
                                    <td>{{ $snapshot['exam_name'] ?? ($marksheet->exam_id ? 'Exam #'.$marksheet->exam_id : '-') }}</td>
                                    <td>{{ $marksheet->session_year ?: '-' }}</td>
                                    <td>{{ optional($marksheet->created_at)->format('d M Y h:i A') }}</td>
                                    <td>
                                        <a class="btn btn-sm btn-primary" target="_blank" href="{{ route('marksheet.generated.print', $marksheet) }}">Print</a>
                                        <button class="btn btn-sm btn-danger" type="button" onclick="deleteGeneratedSingle('{{ route('academic.marksheets.generated.delete', $marksheet) }}')">Delete</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center">No marksheets generated yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
            <div class="mt-3">
                {{ $generatedMarksheets->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<script>
    function openMarksheetPreview() {
        var params = new URLSearchParams();
        ['class_id', 'exam_id', 'student_id'].forEach(function (name) {
            var input = document.querySelector('#marksheetGenerateForm [name="' + name + '"]');
            if (input && input.value) {
                params.set(name, input.value);
            }
        });
        window.open('{{ route('academic.marksheets.print') }}?' + params.toString(), '_blank');
    }

    function selectedGeneratedCount() {
        return document.querySelectorAll('.generated-check:checked').length;
    }

    function toggleGeneratedSelection(source) {
        document.querySelectorAll('.generated-check').forEach(function (checkbox) {
            checkbox.checked = source.checked;
        });
    }

    function submitGeneratedBulk(action) {
        if (selectedGeneratedCount() === 0) {
            alert('Please select at least one marksheet.');
            return;
        }

        var form = document.getElementById('generatedBulkForm');
        var methodInput = document.getElementById('generated_bulk_method');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.id = 'generated_bulk_method';
            form.appendChild(methodInput);
        }

        if (action === 'delete') {
            if (!confirm('Delete selected generated marksheets?')) {
                return;
            }
            form.action = '{{ route('academic.marksheets.generated.bulk-delete') }}';
            form.target = '';
            methodInput.disabled = false;
            methodInput.value = 'DELETE';
        } else {
            var ids = Array.prototype.map.call(document.querySelectorAll('.generated-check:checked'), function (checkbox) {
                return checkbox.value;
            }).join(',');
            var bulkPrintUrl = '{{ route('marksheet.generated.bulk-print', ['marksheets' => '__IDS__']) }}'.replace('__IDS__', ids);
            window.open(bulkPrintUrl, '_blank');
            return;
        }
        form.submit();
    }

    function deleteGeneratedSingle(action) {
        if (!confirm('Delete this generated marksheet?')) {
            return;
        }

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = action;
        form.innerHTML = '@csrf @method('DELETE')';
        document.body.appendChild(form);
        form.submit();
    }
</script>
@endsection
