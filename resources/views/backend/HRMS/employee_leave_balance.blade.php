@extends('backend.layouts.main')

@section('main-container')
<style>
    .employee-leave-balance-page,
    .employee-leave-balance-page .table,
    .employee-leave-balance-page .table th,
    .employee-leave-balance-page .table td,
    .employee-leave-balance-page .form-control,
    .employee-leave-balance-page button,
    .employee-leave-balance-page .breadcrumb h3 {
        font-size: 14px !important;
    }
    .employee-leave-balance-page .table th,
    .employee-leave-balance-page .table td {
        vertical-align: middle;
    }
</style>
<div class="main-content pt-4 employee-leave-balance-page">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h3 class="me-2">Employee Leave Balance</h3>
        </div>
    </div>
    <div class="separator-breadcrumb border-top mb-3"></div>

    <div class="card text-start">
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Search Form --}}
            <form id="searchForm" method="GET" action="{{ route('employee.leave.balances') }}">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="Search by employee name" value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="{{ route('employee.leave.balances') }}" class="btn btn-secondary ms-2">Clear</a>
                    </div>
                </div>
            </form>

            <form method="POST" action="{{ route('employee.leave.balances.import') }}" enctype="multipart/form-data" class="mb-3">
                @csrf
                <div class="row align-items-end g-2">
                    <div class="col-md-6">
                        <label for="leave_balance_file">Bulk Import Leave Balances</label>
                        <input type="file" name="leave_balance_file" id="leave_balance_file" class="form-control" accept=".csv,.txt" required>
                        <small class="form-text text-muted">Download the sample, update the balance column, then import the same file.</small>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-2 justify-content-md-end">
                            <a href="{{ route('employee.leave.balances.sample') }}" class="btn btn-secondary">Download Sample CSV</a>
                            <button type="submit" class="btn btn-warning">Import CSV</button>
                        </div>
                    </div>
                </div>
            </form>

            <form id="resetLeaveBalancesForm" method="POST" action="{{ route('employee.leave.reset') }}" class="mb-3">
                @csrf
                <button id="runCommandBtn" type="button" class="btn btn-warning">Reset Employee Leave Balances</button>
            </form>

            {{-- Table --}}
            <div id="leave-balance-table">
                @include('backend.HRMS.employee_leave_balance_table')
            </div>

            @if(isset($resetHistories))
                <style>
                    /* Reset history: sticky header + scroll */
                    .reset-history-container {
                        max-height: 300px;
                        overflow-x: auto;
                        overflow-y: auto;
                        border: 1px solid #e9ecef;
                        padding: 6px;
                        background: #fff;
                    }
                    .reset-history-table {
                        min-width: 700px;
        		border-collapse: collapse;
                    }
                    .reset-history-table thead th {
                        position: sticky;
                        top: 0;
                        background-color: #343a40 !important;
                        color: #ffffff !important;
                        z-index: 3;
                    }
                    .reset-history-table th, .reset-history-table td { white-space: nowrap; }
                </style>

                <div class="card text-start mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Leave Reset History</h5>
                        @if($resetHistories->isNotEmpty())
                            <div class="reset-history-container">
                                <table class="table table-bordered reset-history-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>User</th>
                                            <th>IP Address</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($resetHistories as $history)
                                            <tr>
                                                <td>{{ $history->created_at->format('d-m-Y H:i:s') }}</td>
                                                <td>{{ optional($history->createdBy)->name ?? 'System' }}</td>
                                                <td>{{ $history->ip_address }}</td>
                                                <td>{{ $history->message }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="mb-0 text-muted">No leave reset history found.</p>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Reset button handler
    const btn = document.getElementById('runCommandBtn');
    if (btn) {
        btn.addEventListener('click', function () {
            if (!confirm('Are you sure you want to reset all employee leave balances? This action cannot be undone.')) return;

            const resetForm = document.getElementById('resetLeaveBalancesForm');
            if (!resetForm) {
                alert('Reset form not found. Please refresh the page and try again.');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Resetting...';
            resetForm.submit();
        });
    }

    // Intercept the update form submit and send JSON to avoid PHP max_input_vars limits
    const tableContainer = document.getElementById('leave-balance-table');
    if (!tableContainer) return;

    const updateForm = tableContainer.querySelector('form');
    if (!updateForm) return;

    updateForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const tokenInput = updateForm.querySelector('input[name="_token"]');
        const csrf = tokenInput ? tokenInput.value : '{{ csrf_token() }}';

        const inputs = updateForm.querySelectorAll('input[name^="balances["]');
        const balances = {};

        inputs.forEach(input => {
            const name = input.getAttribute('name');
            // name format: balances[<employeeId>][<leaveTypeId>]
            const m = name.match(/balances\[(\d+)\]\[(\d+)\]/);
            if (!m) return;
            const empId = m[1];
            const leaveTypeId = m[2];
            const val = input.value === '' ? null : parseFloat(input.value);

            if (!balances[empId]) balances[empId] = {};
            balances[empId][leaveTypeId] = val;
        });

        fetch(updateForm.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ balances })
        }).then(async res => {
            if (!res.ok) {
                const err = await res.json().catch(() => ({}));
                alert(err.message || 'Update failed');
                return;
            }
            const data = await res.json();
            alert(data.message || 'Leave balances updated successfully.');
            location.reload();
        }).catch(err => {
            alert('Error: ' + err.message);
        });
    });
});
</script>
