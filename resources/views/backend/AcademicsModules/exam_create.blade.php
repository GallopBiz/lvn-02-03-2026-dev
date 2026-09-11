@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Exam Master</h2>

    @if(session('success'))
        <div class="alert alert-success mt-2">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mt-2">
            {{ session('error') }}
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title mb-2">Copy Exams from Previous Session</h5>
            <form method="GET" action="{{ route('academic.exams.create') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Source Session</label>
                    <select name="source_session" class="form-control" required>
                        <option value="">Select source session</option>
                        @foreach($databaseNames as $db)
                            @if (is_numeric(substr($db, 0, 1)))
                                <option value="{{ $db }}" {{ ($sourceSession ?? '') === $db ? 'selected' : '' }}>{{ $db }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Target Session</label>
                    <input type="text" class="form-control" value="{{ $currentSessionYear ?? '-' }}" disabled>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary">Load Exams</button>
                </div>
            </form>

            @if(!empty($sourceSession) && isset($sourceExamGroups) && $sourceExamGroups->count())
                <hr>
                <form method="POST" action="{{ route('academic.exams.copyFromSession') }}">
                    @csrf
                    <input type="hidden" name="source_session" value="{{ $sourceSession }}">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th style="width:60px;">Select</th>
                                    <th>Exam Name</th>
                                    <th>Exam Type</th>
                                    <th>Classes</th>
                                    <th>Session Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sourceExamGroups as $g)
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="source_exam_ids[]" value="{{ $g->seed_id }}">
                                        </td>
                                        <td>{{ $g->exam_name }}</td>
                                        <td>{{ $g->exam_type }}</td>
                                        <td>{{ $g->class_names !== '' ? $g->class_names : '-' }}</td>
                                        <td>{{ $g->session_year ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-success" onclick="return confirm('Copy selected exams into current session?');">Copy Selected</button>
                </form>
            @endif

            <div class="text-muted small mt-2">
                Note: Copy maps classes by <strong>class name</strong> (source → target). If a class name doesn’t exist in target session, that class will be skipped.
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('academic.exams.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="exam_name">Exam Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="exam_name" name="exam_name" required>
                </div>
                <div class="form-group mt-2">
                    <label for="exam_title">Exam Title</label>
                    <input type="text" class="form-control" id="exam_title" name="exam_title" value="{{ old('exam_title') }}" maxlength="150">
                </div>
                @if(!empty($currentSessionYear))
                    <p class="text-muted small mb-2">Session year: <strong>{{ $currentSessionYear }}</strong></p>
                @endif
                <div class="form-group mt-2">
                    <label>Exam Type <span class="text-danger">*</span></label><br>
                    @forelse($examTypes as $type)
                        <label class="me-3">
                            <input
                                type="radio"
                                name="exam_type"
                                value="{{ $type }}"
                                {{ old('exam_type') === $type ? 'checked' : '' }}
                                required
                            >
                            {{ $type }}
                        </label>
                    @empty
                        <div class="alert alert-warning mb-0">
                            No exam types found. Please create one from Exam Type first.
                        </div>
                    @endforelse
                </div>
                <div class="form-group mt-2">
                    <label>Max Marks (Theory)</label>
                    <input type="number" class="form-control" name="max_marks_theory">
                </div>
                <div class="form-group mt-2">
                    <label>Max Marks (Practical)</label>
                    <input type="number" class="form-control" name="max_marks_practical">
                </div>
                <div class="form-group mt-2">
                    <label>Fail %</label>
                    <input type="number" class="form-control" name="fail_percent" step="0.01">
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
            <div class="col-md-6">
                <label>Applicable to these Classes:</label>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <label class="mb-0">
                        <input type="checkbox" id="select_all_classes">
                        Select All Classes
                    </label>
                    <button type="button" class="btn btn-sm btn-secondary" id="clear_all_classes">Clear</button>
                </div>
                <div class="border p-2" style="max-height: 350px; overflow-y: auto;">
                    @php
                        $oldClassIds = old('class_ids', []);
                    @endphp
                    @foreach($classes as $class)
                        <div class="form-check d-flex align-items-center justify-content-between">
                            <div>
                                <input
                                    type="checkbox"
                                    class="form-check-input class-checkbox"
                                    name="class_ids[]"
                                    value="{{ $class->id }}"
                                    id="class_{{ $class->id }}"
                                    {{ in_array($class->id, $oldClassIds) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="class_{{ $class->id }}">{{ $class->class_name }}</label>
                            </div>
                            <!-- Edit/Delete removed from classes list -->
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </form>

    <hr>
    <h4>List of saved Exam Names</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>S.No.</th>
                <th>Exam Name</th>
                <th>Exam Title</th>
                <th>Exam Type</th>
                <th>Classes</th>
                <th>Session Year</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($savedExamGroups as $i => $exam)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $exam->exam_name }}</td>
                    <td>{{ $exam->exam_title ?? '-' }}</td>
                    <td>{{ $exam->exam_type }}</td>
                    <td>{{ $exam->class_names }}</td>
                    <td>{{ $exam->session_year ?? '-' }}</td>
                    <td>
                        @if(!empty($exam->is_locked))
                            <span class="badge bg-danger">Locked</span>
                        @else
                            <span class="badge bg-success">Unlocked</span>
                        @endif
                    </td>
                    <td>
                        @if(Route::has('academic.exams.toggleLock'))
                            <form action="{{ route('academic.exams.toggleLock', $exam->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ !empty($exam->is_locked) ? 'btn-warning' : 'btn-dark' }}" onclick="return confirm('{{ !empty($exam->is_locked) ? 'Unlock this exam for staff marks editing?' : 'Lock this exam for staff marks editing?' }}');">
                                    {{ !empty($exam->is_locked) ? 'Unlock' : 'Lock' }}
                                </button>
                            </form>
                        @endif
                        @if(Route::has('academic.exams.edit'))
                            @if(!empty($exam->is_locked))
                                <button type="button" class="btn btn-sm btn-info" disabled>Edit</button>
                            @else
                                <a href="{{ route('academic.exams.edit', $exam->id) }}" class="btn btn-sm btn-info">Edit</a>
                            @endif
                        @endif
                        @if(Route::has('academic.exams.duplicate'))
                            <form action="{{ route('academic.exams.duplicate', $exam->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Duplicate this exam in current session?');">Duplicate</button>
                            </form>
                        @endif
                        @if(Route::has('academic.exams.destroy'))
                            @if(!empty($exam->is_locked))
                                <button type="button" class="btn btn-sm btn-danger" disabled>Delete</button>
                            @else
                                <form action="{{ route('academic.exams.destroy', $exam->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this exam? This will delete the exam across all classes.');">Delete</button>
                                </form>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var selectAll = document.getElementById('select_all_classes');
        var clearAll = document.getElementById('clear_all_classes');
        var classCheckboxes = Array.prototype.slice.call(document.querySelectorAll('.class-checkbox'));

        function syncSelectAllState() {
            var checkedCount = classCheckboxes.filter(function (checkbox) {
                return checkbox.checked;
            }).length;

            selectAll.checked = classCheckboxes.length > 0 && checkedCount === classCheckboxes.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < classCheckboxes.length;
        }

        selectAll.addEventListener('change', function () {
            classCheckboxes.forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });
            syncSelectAllState();
        });

        clearAll.addEventListener('click', function () {
            classCheckboxes.forEach(function (checkbox) {
                checkbox.checked = false;
            });
            syncSelectAllState();
        });

        classCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', syncSelectAllState);
        });

        syncSelectAllState();
    });
</script>
@endsection
