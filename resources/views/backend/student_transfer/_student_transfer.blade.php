@extends('layouts.app') <!-- Ensure this extends your main layout -->

@section('content')
<div class="container">
    <h2>Student Transfer</h2>

    <!-- Dropdown for Class and Section -->
    <form method="GET" action="{{ route('student.transfer') }}">
        <label for="class_name">Select Class:</label>
        <select name="class_name" id="class_name" onchange="this.form.submit()">
            <option value="">-- Select Class --</option>
            @foreach($classes as $class)
                <option value="{{ $class }}" {{ request('class_name') == $class ? 'selected' : '' }}>
                    {{ $class }}
                </option>
            @endforeach
        </select>

        @if (!empty($sections))
        <label for="section">Select Section:</label>
        <select name="section" id="section" onchange="this.form.submit()">
            <option value="">-- Select Section --</option>
            @foreach($sections as $section)
                <option value="{{ $section }}" {{ request('section') == $section ? 'selected' : '' }}>
                    {{ $section }}
                </option>
            @endforeach
        </select>
        @endif
    </form>

    <!-- Student List -->
    @if (!empty($students))
    <form method="POST" action="{{ route('student.promote') }}">
        @csrf
        <table border="1">
            <tr>
                <th>Select</th>
                <th>Student Name</th>
                <th>Class</th>
                <th>Section</th>
            </tr>
            @foreach($students as $student)
            <tr>
                <td><input type="checkbox" name="students[]" value="{{ $student->id }}"></td>
                <td>{{ $student->name }}</td>
                <td>{{ $student->class_name }}</td>
                <td>{{ $student->section }}</td>
            </tr>
            @endforeach
        </table>

        <!-- Promotion Dropdown -->
        <label for="new_class">Promote to Class:</label>
        <select name="new_class">
            @foreach($classes as $class)
                <option value="{{ $class }}">{{ $class }}</option>
            @endforeach
        </select>

        <button type="submit">Promote Selected Students</button>
    </form>
    @endif
</div>
@endsection
