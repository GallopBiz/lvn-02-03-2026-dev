@extends('backend.layouts.main')
@section('main-container')

<div class="container mt-4">
    <h4 class="mb-4">{{ $editRefund ? 'Edit Security Deposit Refund' : 'Security Deposit Refund' }}</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ $editRefund ? route('security.refund.update', $editRefund->id) : route('security.refund.submit') }}" method="POST">
        @csrf
        @if($editRefund)
            @method('POST')
        @endif

        <div class="row">
            <!-- Employee Dropdown -->
            <div class="form-group col-md-3">
                <label for="employee_id">Select Employee</label>
                <select name="employee_id" id="employee_id" class="form-control" required onchange="loadLoanDetails(this)">
                    <option value="">-- Select --</option>
                    @foreach($loans as $loan)
                        <option value="{{ $loan->employee->id }}"
                            data-loan-id="{{ $loan->id }}"
                            data-status="{{ ucfirst($loan->status) }}"
                            data-amount="{{ $loan->loan_amount }}"
                            data-start="{{ $loan->start_date }}"
                            {{ $editRefund && $editRefund->employee_id == $loan->employee->id ? 'selected' : '' }}
                        >
                            {{ $loan->employee->first_name }} {{ $loan->employee->last_name }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="loan_id" id="loan_id" value="{{ $editRefund->loan_id ?? '' }}">
            </div>

            <div class="form-group col-md-3">
                <label>Loan Status</label>
                <input type="text" id="loan_status" class="form-control" readonly value="{{ ucfirst($editRefund->loan->status ?? '') }}">
            </div>

            <div class="form-group col-md-3">
                <label>Security Deposit Amount</label>
                <input type="text" id="loan_amount" class="form-control" readonly value="{{ $editRefund->loan->loan_amount ?? '' }}">
            </div>

            <div class="form-group col-md-3">
                <label>Start Date</label>
                <input type="text" id="loan_start_date" class="form-control" readonly value="{{ $editRefund->loan->start_date ?? '' }}">
            </div>

            <div class="form-group col-md-3">
                <label>Refund Amount</label>
                <input type="text" name="refund_amount" class="form-control" required value="{{ $editRefund->refund_amount ?? '' }}">
            </div>

            <div class="form-group col-md-3">
                <label>Refund Date</label>
                <input type="date" name="refund_date" class="form-control" required value="{{ $editRefund->refund_date ?? '' }}">
            </div>

            <div class="form-group col-md-6">
                <label>Description (Optional)</label>
                <textarea name="description" class="form-control" rows="2">{{ $editRefund->description ?? '' }}</textarea>
            </div>
        </div>

        <button class="btn btn-{{ $editRefund ? 'primary' : 'success' }} mt-3" type="submit">
            {{ $editRefund ? 'Update Refund' : 'Submit Refund' }}
        </button>
    </form>
</div>

<!-- Refund Table -->
<div class="row mt-5">
    <div class="col-md-12 mb-4">
        <div class="breadcrumb">
            <h1 class="me-2">List of Security Deposit Refunds</h1>
        </div>
        <div class="separator-breadcrumb border-top"></div>

        <div class="card text-start">
            <div class="card-body">
				<div class="row mb-3 justify-content-end">
						<div class="col-md-4">
							<input type="text" id="refundSearch" class="form-control" placeholder="Search by employee name...">
						</div>
					</div>
                <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="deafult_ordering_table_wrapper" style="width: 100%">
                        <thead>
                            <tr>
                                <th>Sr.</th>
                                <th>Employee Name</th>
                                <th>Security Deposit Amount</th>
                                <th>Refund Amount</th>
                                <th>Refund Date</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i = 0; @endphp
                            @forelse ($refunds as $refund)
                                <tr>
                                    <td>{{ ++$i }}</td>
                                    <td>{{ $refund->employee->first_name ?? 'Unknown' }} {{ $refund->employee->last_name ?? '' }}</td>
                                    <td>{{ $refund->loan->loan_amount ?? '0.00' }}</td>
                                    <td>{{ number_format($refund->refund_amount, 2) }}</td>
                                    <td>{{ $refund->refund_date ? \Carbon\Carbon::parse($refund->refund_date)->format('d-m-Y') : '-' }}</td>
                                    <td>{{ $refund->description ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('security.refund.form', ['edit_id' => $refund->id]) }}" class="btn btn-primary btn-sm m-1">Edit</a>
                                        <form action="{{ route('security.refund.delete', $refund->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm m-1" onclick="return confirm('Are you sure you want to delete this refund?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No refund records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS for dynamic field population -->
<script>
    function loadLoanDetails(select) {
        const option = select.options[select.selectedIndex];
        const loanId = option.getAttribute('data-loan-id');
        const status = option.getAttribute('data-status');
        const amount = option.getAttribute('data-amount');
        const start = option.getAttribute('data-start');

        document.getElementById('loan_id').value = loanId || '';
        document.getElementById('loan_status').value = status || '';
        document.getElementById('loan_amount').value = amount || '';
        document.getElementById('loan_start_date').value = start || '';
    }
	
	document.getElementById('refundSearch').addEventListener('keyup', function () {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#deafult_ordering_table_wrapper tbody tr');

        rows.forEach(row => {
            const employeeName = row.cells[1]?.textContent.toLowerCase();
            if (employeeName.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
	
</script>

@endsection
