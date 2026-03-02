@extends('layouts.app')

@section('page-title')
    <h4>Employee Deduction Apply Report</h4>
@endsection

@section('content')
<style>
    .report-container {
        display: flex;
        gap: 20px;
    }

    .left-table {
        flex: 1;
        overflow-x: auto;
    }

    .totals-panel {
        width: 260px;
        max-height: 600px;
        overflow-y: auto;
        background-color: #f9f9f9;
        padding: 15px;
        border: 1px solid #ccc;
        border-radius: 8px;
        flex-shrink: 0;
    }

    .totals-panel h6 {
        font-weight: bold;
        margin-bottom: 15px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 8px;
    }

    .totals-panel .list-group-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 14px;
        border: none;
        border-bottom: 1px solid #eee;
    }

    .totals-panel .badge {
        background-color: #663399;
        color: #fff;
        font-size: 13px;
    }
	.breadcrumb ul li {
    padding: 10px 0.5rem;
    }
</style>

<div class="card p-3">
    <h5>Applied Deductions Overview</h5>

    <div class="mb-3">
        <input type="text" id="employeeSearch" class="form-control" placeholder="Search by ESS Code or Name">
    </div>

    <div class="report-container">
        <!-- Left Table -->
        <div class="left-table">
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>S.No.</th>
                        <th>Employee Name</th>
                        <th>ESS Code</th>
                        @foreach($uniqueDeductions as $deduction)
                            <th>{{ $deduction->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $index => $employee)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $employee->first_name }} {{ $employee->last_name }}</td>
                            <td>{{ $employee->biometricDetail->ess_emp_code ?? 'N/A' }}</td>
                            @foreach($uniqueDeductions as $deduction)
                                @php
                                    $hasDeduction = $employee->employeeDeductions->where('basic_deduction_id', $deduction->id)->first();
                                @endphp
                                <td>
                                    @if($hasDeduction)
                                        {{ $hasDeduction->manual_amount ?? 'Default' }}
                                    @else
                                        NA
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Right Totals -->
        <div class="totals-panel">
            <h6>Total Deductions</h6>
            <ul class="list-group">
                @foreach($uniqueDeductions as $deduction)
                    @php
                        $total = $employees->map(function($employee) use ($deduction) {
                            $entry = $employee->employeeDeductions->where('basic_deduction_id', $deduction->id)->first();
                            return $entry ? floatval($entry->manual_amount ?? 0) : 0;
                        })->sum();
                    @endphp
                    <li class="list-group-item">
                        {{ $deduction->name }}
                        <span class="badge rounded-pill">₹{{ number_format($total, 2) }}</span>
                    </li>
                @endforeach
            </ul>
			<div class="text-end mt-3">
				<a href="{{ route('export.deductions') }}" class="btn btn-success">
					Export Deduction Report
				</a>
			</div>

        </div>
		

    </div>
</div>

<script>
    document.getElementById('employeeSearch').addEventListener('keyup', function () {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('table tbody tr');

        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
</script>
@endsection
