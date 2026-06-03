@extends('backend.layouts.main')
@section('main-container')
    @php
        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];
    @endphp

    <div class="main-content">
        <div class="breadcrumb">
            <h1 class="me-2">Payroll Attendance Configuration</h1>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('payroll.attendance.configuration.update') }}">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card text-start">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Month Wise Biometric Requirement</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Month</th>
                                            <th class="text-center">Biometric Required</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($months as $monthNumber => $monthName)
                                            @php
                                                $monthConfig = $monthConfigurations->get($monthNumber);
                                                $isRequired = $monthConfig ? $monthConfig->biometric_required : true;
                                            @endphp
                                            <tr>
                                                <td>{{ $monthName }}</td>
                                                <td class="text-center">
                                                    <input type="hidden" name="months[{{ $monthNumber }}]" value="0">
                                                    <input type="checkbox" name="months[{{ $monthNumber }}]" value="1"
                                                        @checked($isRequired)>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card text-start">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Staff Type Rule During Disabled Months</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Staff Type</th>
                                            <th class="text-center">Biometric Required</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($staffTypes as $staffType)
                                            @php
                                                $staffConfig = $staffConfigurations->get($staffType->id);
                                                $isRequired = $staffConfig ? $staffConfig->biometric_required : false;
                                            @endphp
                                            <tr>
                                                <td>{{ $staffType->staff_type_name }}</td>
                                                <td class="text-center">
                                                    <input type="hidden" name="staff_types[{{ $staffType->id }}]" value="0">
                                                    <input type="checkbox" name="staff_types[{{ $staffType->id }}]"
                                                        value="1" @checked($isRequired)>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center text-muted">No staff types found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card text-start mt-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Department Rule During Disabled Months</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Department</th>
                                            <th class="text-center">Biometric Required</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($departments as $department)
                                            @php
                                                $departmentConfig = $departmentConfigurations->get($department->id);
                                                $isRequired = $departmentConfig ? $departmentConfig->biometric_required : false;
                                            @endphp
                                            <tr>
                                                <td>{{ $department->department_name }}</td>
                                                <td class="text-center">
                                                    <input type="hidden" name="departments[{{ $department->id }}]" value="0">
                                                    <input type="checkbox" name="departments[{{ $department->id }}]"
                                                        value="1" @checked($isRequired)>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center text-muted">No departments found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end">
                <a href="{{ route('payroll') }}" class="btn btn-secondary">Back</a>
                <button type="submit" class="btn btn-primary">Save Configuration</button>
            </div>
        </form>
    </div>
@endsection
