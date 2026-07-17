@extends('backend.layouts.main')

@section('main-container')

@php
    use Carbon\Carbon;
    use Carbon\CarbonPeriod;

    $months = [
        '01' => 'January', '02' => 'February', '03' => 'March',
        '04' => 'April', '05' => 'May', '06' => 'June',
        '07' => 'July', '08' => 'August', '09' => 'September',
        '10' => 'October', '11' => 'November', '12' => 'December',
    ];
@endphp

<h2>Manual Attendance Management</h2>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<form method="GET" action="{{ route('manual.attendance') }}">
    <div class="row mb-3">
        <div class="col-md-4">
            <label>Employee:</label>
            <select name="employee_id" class="form-control" required>
                <option value="">Select Employee</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}" {{ $selectedEmployee == $emp->id ? 'selected' : '' }}>
                        {{ $emp->first_name }} {{ $emp->last_name }} - {{ $emp->biometricDetail->ess_emp_code ?? 'NoCode' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label>Month:</label>
            <select name="month" class="form-control">
                @foreach ($months as $key => $val)
                    <option value="{{ $key }}" {{ $month == $key ? 'selected' : '' }}>{{ $val }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label>Year:</label>
            <select name="year" class="form-control">
                @for ($y = 2020; $y <= 2030; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-2 align-self-end">
            <button type="submit" class="btn btn-primary">View</button>
        </div>
    </div>
</form>

@if ($selectedEmployee && $startDate && $endDate)

@php
    $today = now();
    $attendanceMonth = Carbon::create($year, $month)->startOfMonth();
    // Temporarily disabled: manual attendance was locked after the 12th of the next month.
    // $lockDate = $attendanceMonth->copy()->addMonth()->day(12)->endOfDay();
    // $lockEditing = $today->greaterThan($lockDate);
    $lockDate = null;
    $lockEditing = false;
@endphp

<form method="POST" action="{{ route('manual.attendance.update') }}">
    @csrf
    <input type="hidden" name="employee_id" value="{{ $selectedEmployee }}">
    <input type="hidden" name="month" value="{{ $month }}">
    <input type="hidden" name="year" value="{{ $year }}">

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Status</th>
                <th>In Time</th>
                <th>Out Time</th>
            </tr>
        </thead>
        <tbody>
            @foreach (CarbonPeriod::create($startDate, $endDate) as $date)
                @php
                    $dateStr = $date->toDateString();
                    $att = $attendanceData[$dateStr] ?? null;
                    $disabled = ($att?->entry_type === 'biometric' || $lockEditing) ? 'disabled' : '';
                @endphp
                <tr>
                    <td>
                        {{ $date->format('d-M-Y') }}
                        @if ($att?->entry_type === 'biometric')
                            <span class="badge bg-info text-white">Biometric</span>
                        @elseif ($att?->entry_type === 'manual')
                            <span class="badge bg-warning text-dark">Manual</span>
                        @endif
                    </td>
                    <td>
                        <select name="dates[{{ $dateStr }}][status]" class="form-control" {{ $disabled }}>
                            <option value="">-- Select --</option>
                            <option value="Present" {{ $att?->status == 'Present' ? 'selected' : '' }}>Present</option>
                            <option value="Absent" {{ $att?->status == 'Absent' ? 'selected' : '' }}>Absent</option>
                            <option value="Late" {{ $att?->status == 'Late' ? 'selected' : '' }}>Late</option>
                            <option value="Half Day" {{ $att?->status == 'Half Day' ? 'selected' : '' }}>Half Day</option>
                        </select>
                    </td>
                    <td>
                        <input type="time" name="dates[{{ $dateStr }}][in_time]" value="{{ $att?->in_time }}" class="form-control" {{ $disabled }}>
                    </td>
                    <td>
                        <input type="time" name="dates[{{ $dateStr }}][out_time]" value="{{ $att?->out_time }}" class="form-control" {{ $disabled }}>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if (!$lockEditing)
        <button type="submit" class="btn btn-success">Save Attendance</button>
    @else
        <div class="alert alert-warning">
            <strong>Note:</strong> Attendance for <strong>{{ $attendanceMonth->format('F Y') }}</strong> is locked after {{ $lockDate->format('d-M-Y') }}. Save is disabled.
        </div>
    @endif

</form>
@endif

@endsection
