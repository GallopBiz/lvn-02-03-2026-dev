@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Exam Master</h2>

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
                @if(!empty($currentSessionYear))
                    <p class="text-muted small mb-2">Session year: <strong>{{ $currentSessionYear }}</strong></p>
                @endif
                <div class="form-group mt-2">
                    <label>Exam Type <span class="text-danger">*</span></label><br>
                    <label class="me-3"><input type="radio" name="exam_type" value="PT-1" required> PT-1</label>
                    <label class="me-3"><input type="radio" name="exam_type" value="PT-2" required> PT-2</label>
                    <label class="me-3"><input type="radio" name="exam_type" value="Term-1" required> Term-1</label>
                    <label class="me-3"><input type="radio" name="exam_type" value="Term-4" required> Term-4</label>
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
                <div class="border p-2" style="max-height: 350px; overflow-y: auto;">
                    @foreach($classes as $class)
                        <div class="form-check d-flex align-items-center justify-content-between">
                            <div>
                                <input type="checkbox" class="form-check-input" name="class_ids[]" value="{{ $class->id }}" id="class_{{ $class->id }}">
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
                <th>Exam Type</th>
                <th>Classes</th>
                <th>Session Year</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($savedExamGroups as $i => $exam)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $exam->exam_name }}</td>
                    <td>{{ $exam->exam_type }}</td>
                    <td>{{ $exam->class_names }}</td>
                    <td>{{ $exam->session_year ?? '-' }}</td>
                    <td>
                        @if(Route::has('academic.exams.edit'))
                            <a href="{{ route('academic.exams.edit', $exam->id) }}" class="btn btn-sm btn-info">Edit</a>
                        @endif
                        @if(Route::has('academic.exams.duplicate'))
                            <form action="{{ route('academic.exams.duplicate', $exam->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Duplicate this exam in current session?');">Duplicate</button>
                            </form>
                        @endif
                        @if(Route::has('academic.exams.destroy'))
                            <form action="{{ route('academic.exams.destroy', $exam->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this exam?');">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
