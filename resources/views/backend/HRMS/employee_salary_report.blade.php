@extends('backend.layouts.main')

@section('main-container')
<div class="main-content">
    <div class="breadcrumb">
        <h1>Generated Salary Report</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    @php
        /**
         * Safely get amount by key from a single array (case-insensitive).
         * Returns float value or 0.
         */
        function getD($d, $k) {
            if (!is_array($d)) return 0.0;

            $keyLower = strtolower($k);
            foreach ($d as $item) {
                $name = isset($item['name']) ? $item['name'] : (isset($item['key']) ? $item['key'] : null);
                if ($name && strtolower($name) === $keyLower) {
                    return (float) ($item['amount'] ?? $item['value'] ?? 0);
                }

                // Also support objects where top-level keys are the names:
                // e.g. ["HRA" => 1000, "DA" => 500]
                if (is_string($item) && strtolower($item) === $keyLower) {
                    // If array contains plain strings, skip (no amount available).
                    continue;
                }
            }

            // If $d is an associative array with name => amount pairs:
            if (array_key_exists($k, $d)) {
                return (float) $d[$k];
            }
            // try case-insensitive associative lookup
            foreach ($d as $assocKey => $val) {
                if (is_string($assocKey) && strtolower($assocKey) === $keyLower) {
                    return (float) $val;
                }
            }

            return 0.0;
        }

        /**
         * Search additions first, then deductions for the given key.
         */
        function getAmount($additions, $deductions, $key) {
            $val = getD($additions, $key);
            if ($val !== 0.0) return $val;
            return getD($deductions, $key);
        }
    @endphp

    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ url('employee-salary-report') }}" class="row mb-3">
                <div class="col-md-3">
                    <label for="month">Month</label>
                    <select name="month" class="form-control">
                        <option value="">All</option>
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="year">Year</label>
                    <select name="year" class="form-control">
                        <option value="">All</option>
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search">Search (Name or ESS Code)</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search...">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            @if($salaryReports->count())
                <div class="table-responsive">
                    <table class="table table-bordered" id="salaryReportTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Employee ID</th>
                                <th>Name / ESS Code</th>
                                <th>Month</th>
                                <th>Year</th>
                                <th>Gross</th>
								<th>Basic Salary</th>
								<th>DA</th>
								<th>HRA</th>
                                <th>EPFA</th>
                                <th>ESIC</th>
                                <!--<th>Leaves</th>-->
                                <th>Late</th>
                                <th>Unapplied Leave</th>
                                <th>Grain Loan</th>
								<th>Net</th>
                                <th>Avg Salary</th>
                                <th>Generated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $grouped = $salaryReports->groupBy(function($item) {
                                    return $item->month . '-' . $item->year;
                                });
                            @endphp

                            @foreach($grouped as $monthYear => $reports)
                                @php
                                    [$monthNum, $year] = explode('-', $monthYear);
                                    $monthName = date('F', mktime(0, 0, 0, $monthNum, 1));
                                @endphp

                                <tr class="table-primary">
                                    <td colspan="18"><strong>{{ $monthName }} {{ $year }}</strong></td>
                                </tr>

                                @foreach($reports->sortBy('employee_id') as $index => $report)
                                    @php
                                        // Safe decode additions and deductions -> always arrays
                                        $additionsRaw = $report->additions ?? '[]';
                                        $deductionsRaw = $report->deductions ?? '[]';

                                        $additions = is_array($additionsRaw) ? $additionsRaw : (json_decode($additionsRaw, true) ?: []);
                                        $deductions = is_array($deductionsRaw) ? $deductionsRaw : (json_decode($deductionsRaw, true) ?: []);

                                        // Final safety: if decode returned null, ensure array
                                        if (!is_array($additions)) $additions = [];
                                        if (!is_array($deductions)) $deductions = [];
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $report->employee_id }}</td>
                                        <td>
                                            {{ $report->employee->first_name ?? 'N/A' }} {{ $report->employee->last_name ?? '' }}<br>
                                            <small>{{ $report->employee->biometricDetail->ess_emp_code ?? 'N/A' }}</small>
                                        </td>
                                        <td>{{ $monthName }}</td>
                                        <td>{{ $year }}</td>
                                        <td>{{ number_format($report->gross_salary, 2) }}</td>
                                        <td>{{ number_format($report->basic_salary ?? 0, 2) }}</td>
										<td>{{ number_format($report->da, 2) }}</td>
										<td>{{ number_format($report->hra, 2) }}</td>
										<td>{{ number_format($report->epfa, 2) }}</td>
										<td>{{ number_format($report->esic, 2) }}</td>
                                        <!--<td>{{ number_format(getAmount($additions, $deductions, 'Leaves'), 2) }}</td>-->
                                        <td>{{ number_format($report->late_deduction_amount, 2) }}</td>
                                        <td>{{ number_format($report->unapprovd_leaves, 2) }}</td>
                                        <td>{{ number_format(getAmount($additions, $deductions, 'Grain Loan'), 2) }}</td>
										<td>{{ number_format($report->net_salary, 2) }}</td>

                                        <td>{{ number_format($avgNetSalaries[$report->employee_id] ?? 0, 2) }}</td>
                                        <td>{{ $report->generated_at ? \Carbon\Carbon::parse($report->generated_at)->format('d M Y h:i A') : 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('salary.slip.print', $report->id) }}" target="_blank" class="btn btn-sm btn-info">Print Slip</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-center text-danger mt-4">No records found for selected filters.</p>
            @endif
        </div>
    </div>
</div>
@endsection
