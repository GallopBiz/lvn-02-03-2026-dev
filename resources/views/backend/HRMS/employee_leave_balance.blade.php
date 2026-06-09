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
                            <button id="runCommandBtn" type="button" class="btn btn-warning">Reset Employee Leave Balances</button>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div id="leave-balance-table">
                @include('backend.HRMS.employee_leave_balance_table')
            </div>

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
            if (!confirm('Are you sure you want to reset all employee leave balances?')) return;

            btn.disabled = true;
            btn.textContent = 'Running...';

            fetch("{{ route('employee.leave.reset') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            }).then(async (res) => {
                btn.disabled = false;
                btn.textContent = 'Reset Employee Leave Balances';
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    alert(err.message || 'Reset failed');
                    return;
                }
                const data = await res.json();
                alert(data.message || 'Reset completed');
                location.reload();
            }).catch((err) => {
                btn.disabled = false;
                btn.textContent = 'Reset Employee Leave Balances';
                alert('Error: ' + err.message);
            });
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
