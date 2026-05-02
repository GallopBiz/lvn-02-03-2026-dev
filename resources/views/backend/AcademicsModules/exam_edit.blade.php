@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Exam</h2>
    <form method="POST" action="{{ route('academic.exams.update', $exam->id) }}">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="exam_name">Exam Name <span class="text-danger">*</span></label>
                    <input
                        type="text"
                        class="form-control"
                        id="exam_name"
                        name="exam_name"
                        value="{{ old('exam_name', $exam->exam_name) }}"
                        required
                    >
                </div>
                @if(!empty($currentSessionYear))
                    <p class="text-muted small mb-2">Session year: <strong>{{ $currentSessionYear }}</strong></p>
                @endif
                <div class="form-group mt-2">
                    <label>Exam Type <span class="text-danger">*</span></label><br>
                    @foreach($examTypes as $type)
                        <label class="me-3">
                            <input
                                type="radio"
                                name="exam_type"
                                value="{{ $type }}"
                                {{ old('exam_type', $exam->exam_type) === $type ? 'checked' : '' }}
                                required
                            >
                            {{ $type }}
                        </label>
                    @endforeach
                </div>
                <div class="form-group mt-2">
                    <label>Max Marks (Theory)</label>
                    <input type="number" class="form-control" name="max_marks_theory" value="{{ old('max_marks_theory', $exam->max_marks_theory) }}">
                </div>
                <div class="form-group mt-2">
                    <label>Max Marks (Practical)</label>
                    <input type="number" class="form-control" name="max_marks_practical" value="{{ old('max_marks_practical', $exam->max_marks_practical) }}">
                </div>
                <div class="form-group mt-2">
                    <label>Fail %</label>
                    <input type="number" class="form-control" name="fail_percent" step="0.01" value="{{ old('fail_percent', $exam->fail_percent) }}">
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('academic.exams.create') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
            <div class="col-md-6">
                <label>Applicable to these Classes:</label>
                <div class="border p-2" style="max-height: 350px; overflow-y: auto;">
                    @php
                        $oldClassIds = old('class_ids', $selectedClassIds ?? []);
                    @endphp
                    @foreach($classes as $class)
                        <div class="form-check d-flex align-items-center justify-content-between">
                            <div>
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    name="class_ids[]"
                                    value="{{ $class->id }}"
                                    id="class_{{ $class->id }}"
                                    {{ in_array($class->id, $oldClassIds) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="class_{{ $class->id }}">{{ $class->class_name }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
