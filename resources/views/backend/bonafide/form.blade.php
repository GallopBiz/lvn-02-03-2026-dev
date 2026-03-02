@extends('backend.layouts.main')
@section('main-container')

<style type="text/css">
    .validation_err {
        color: red !important;
    }
    input[type="number"] {
        appearance: textfield;
        -webkit-appearance: textfield;
        -moz-appearance: textfield;
    }
    input[type="date"]::-webkit-calendar-picker-indicator {
        background-position: right;
        background-size: auto;
        cursor: pointer;
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        top: 7px;
        width: auto;
    }
    .uperletter {
        text-transform: capitalize;
    }
</style>

<div class="main-content pt-4">
    <div class="breadcrumb">
        <h1 class="me-2">Bonafide Certificate</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="row">
        <div class="form_section1_div">
            <form method="post" action="{{url('generate-bonafide')}}" novalidate="novalidate">
                @csrf
                <div class="row">
                    <div class="col-md-4 form-group mb-3">
                        <label for="session_name">Session</label>
                        <input type="text" id="session_name" name="session_name" class="form-control" readonly value="{{ $session ?? '' }}">
                    </div>

                    <div class="col-md-4 form-group mb-3">
                        <label for="student_id">Student ID</label>
                        <input type="text" name="student_id" class="form-control" id="student_id" placeholder="Enter Student ID" value="{{ old('student_id') }}">
                        <span class="validation_err">{{ $errors->first('student_id') }}</span>
                    </div>

                    <div class="col-md-4 form-group mb-3">
                        <label for="student_name">Student Name</label>
                        <input type="text" name="student_name" class="form-control uperletter" id="student_name" placeholder="Student Name" value="{{ old('student_name') }}">
                        <span class="validation_err">{{ $errors->first('student_name') }}</span>
                    </div>

                    <div class="col-md-4 form-group mb-3">
                        <label for="father_name">Father Name</label>
                        <input type="text" name="father_name" class="form-control uperletter" id="father_name" placeholder="Father Name" value="{{ old('father_name') }}">
                        <span class="validation_err">{{ $errors->first('father_name') }}</span>
                    </div>

                    <div class="col-md-4 form-group mb-3">
                        <label for="dob">Date of Birth</label>
                        <input type="date" name="dob" class="form-control" id="dob" value="{{ old('dob') }}">
                        <span class="validation_err">{{ $errors->first('dob') }}</span>
                    </div>

                    <div class="col-md-4 form-group mb-3">
                        <label for="class_name">Class</label>
                        <select name="class_name" id="class_name" class="form-control" required>
                            <option value="" disabled selected>Please select</option>
                            @foreach($classlist as $each)
                                <option value="{{ $each->class_name }}" {{ old('class_name') == $each->class_name ? 'selected' : '' }}>{{ $each->class_name }}</option>
                            @endforeach
                        </select>
                        <span class="validation_err">{{ $errors->first('class_name') }}</span>
                    </div>

                    <div class="col-md-4 form-group mb-3">
                        <label for="issue_date">Issue Date</label>
                        <input type="date" name="issue_date" class="form-control" id="issue_date" value="{{ old('issue_date') }}">
                        <span class="validation_err">{{ $errors->first('issue_date') }}</span>
                    </div>

                    <div class="col-md-4 form-group mb-3">
                        <label for="purpose">Purpose</label>
                        <input type="text" name="purpose" class="form-control" id="purpose" placeholder="Purpose" value="{{ old('purpose') }}">
                        <span class="validation_err">{{ $errors->first('purpose') }}</span>
                    </div>

                    <div class="col-md-4 form-group mb-3">
                        <label for="remarks">Remarks</label>
                        <input type="text" name="remarks" class="form-control" id="remarks" placeholder="Remarks" value="{{ old('remarks') }}">
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-12 text-left">
                        <button type="submit" class="btn btn-primary">Generate</button>
                        <input type="reset" class="btn btn-danger text-white" value="Reset">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="separator-breadcrumb border-top mt-4"></div>

    @if(isset($bonafide_records) && count($bonafide_records) > 0)
        <div class="col-md-12 mb-4">
            <div class="card text-start">
                <div class="card-body">
                    <h4 class="card-title mb-3 text-end">
                        <form method="POST" action="{{ route('bonafide.export.csv') }}">
                            @csrf
                            <input type="hidden" name="table_name" value="bonafide_certificates">
                            <button type="submit" class="btn btn-raised ripple btn-raised-warning m-1">Export CSV</button>
                        </form>
                    </h4>
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>S No.</th>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Father Name</th>
                                    <th>DOB</th>
                                    <th>Class</th>
                                    <th>Session</th>
                                    <th>Issue Date</th>
                                    <th>Purpose</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bonafide_records as $index => $record)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $record->student_id }}</td>
                                        <td>{{ $record->student_name }}</td>
                                        <td>{{ $record->father_name }}</td>
                                        <td>{{ $record->dob }}</td>
                                        <td>{{ $record->class_name }}</td>
                                        <td>{{ $record->session_name }}</td>
                                        <td>{{ $record->issue_date }}</td>
                                        <td>{{ $record->purpose }}</td>
                                        <td>{{ $record->remarks }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
