@extends('layouts.app') {{-- Adjust this layout as per your structure --}}

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card box-shadow p-3">
                <div class="text-center">
                    {{-- Profile Image --}}
                    <div class="avatar box-shadow-2 mb-3 mx-auto" style="border-radius:50%; width:120px; height:120px; overflow:hidden;">
                        <img src="{{ url('assets/backend/images/student.png') }}" alt="Student Avatar" class="img-fluid">
                    </div>

                    {{-- Student Name --}}
                    <h5 class="text-uppercase m-0">
                        @if (!empty($student->studentname_prefix))
                            {{ $student->studentname_prefix }}
                        @endif
                        {{ $student->student_name }}
                    </h5>
                    <p class="text-muted">Class - {{ $student->class_name }}</p>
                </div>

                {{-- Student Details Table --}}
                <div class="table-responsive mt-4">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Scholar Number</th>
                                <td>{{ $student->scholar_no }}</td>
                            </tr>
                            <tr>
                                <th>Name</th>
                                <td>{{ $student->student_name }}</td>
                            </tr>
                            <tr>
                                <th>Class</th>
                                <td>{{ $student->class_name }}</td>
                            </tr>
                            <tr>
                                <th>Session</th>
                                <td>{{ $student->session_name }}</td>
                            </tr>
                            <tr>
                                <th>Form No.</th>
                                <td>{{ $student->form_number }}</td>
                            </tr>
                            <tr>
                                <th>Reset Password <br><small>(LVN@123)</small></th>
                                <td>
                                    <button class="btn btn-sm btn-primary" id="resetpassword" value="{{ $student->id }}">
                                        Reset
                                    </button>
                                    <div class="text-success mt-2" id="success"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Optional: Add Edit / Contact Button --}}
                {{-- <div class="text-center mt-3">
                    <a href="#" class="btn btn-outline-info">Edit Profile</a>
                </div> --}}
            </div>
        </div>
    </div>
</div>
@endsection
