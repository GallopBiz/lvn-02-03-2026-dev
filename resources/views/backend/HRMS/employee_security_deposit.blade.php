@extends('backend.layouts.main')

@section('main-container')

<div class="main-content">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Employee Security Deposit</h1>
        </div>
        <div class="separator-breadcrumb border-top"></div>
        <div class="card-header">
            <h4>{{ isset($deposit) ? 'Edit Security Deposit' : 'Create Security Deposit' }}</h4>
        </div>

        <form id="progress-form" class="p-4 progress-form" 
              action="{{ route('employee_security_deposit.create') }}" method="POST">
            @csrf
            @if(isset($deposit))
                <input type="hidden" name="id" value="{{ $deposit->id }}">
            @endif

            {{-- Row 1: Employee, Deposit Amount, Duration, Start Date --}}
            <div class="row">
                <div class="col-md-3 form-group mb-3">
                    <label for="employee_id">Employee</label>
                    <select name="employee_id" class="form-control" required>
                        <option value="">-- Select Employee --</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}"
                                {{ old('employee_id', $deposit->employee_id ?? '') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->first_name }} {{ $employee->last_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="security_deposit">Deposit Amount</label>
                    <input type="number" name="security_deposit" class="form-control"
                           value="{{ old('security_deposit', $deposit->security_deposit ?? '') }}" required>
                    @error('security_deposit')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="duration_months">Duration (Months)</label>
                    <input class="form-control" id="duration_months" name="duration_months" type="number"
                        value="{{ old('duration_months', $deposit->duration_months ?? '') }}"
                        placeholder="Duration (Months)" />
                    @error('duration_months')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" class="form-control"
                           value="{{ old('start_date', isset($deposit) ? \Carbon\Carbon::parse($deposit->start_date)->format('Y-m-d') : '') }}" required>
                    @error('start_date')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Row 2: Description, Is Active, Status --}}
            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $deposit->description ?? '') }}</textarea>
                    @error('description')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-2 form-group mb-3">
                    <label for="is_active">Is Active</label><br>
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $deposit->is_active ?? false) ? 'checked' : '' }}>
                </div>

                <div class="col-md-4 form-group mb-3">
                    <label>Status</label><br>
                    <label class="me-3">
                        <input type="radio" name="status" value="pending"
                               {{ old('status', $deposit->status ?? '') == 'pending' ? 'checked' : '' }}> Pending
                    </label>
                    <label class="me-3">
                        <input type="radio" name="status" value="completed"
                               {{ old('status', $deposit->status ?? '') == 'completed' ? 'checked' : '' }}> Completed
                    </label>
                    <label>
                        <input type="radio" name="status" value="refund"
                               {{ old('status', $deposit->status ?? '') == 'refund' ? 'checked' : '' }}> Refund
                    </label>
                    @error('status')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="col-md-12 mt-2">
                <button type="submit" class="btn btn-primary">{{ isset($deposit) ? 'Update' : 'Submit' }}</button>
                <a href="{{ route('employee_security_deposit') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table Section --}}
    <div class="separator-breadcrumb border-top mt-4"></div>
    <div class="card text-start">
        <div class="card-body">
            <h5>List of Security Deposits</h5>
            <div class="table-responsive">
                <table class="display table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Deposit Amount</th>
                            <th>Start Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($deposits as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $item->employee->first_name }} {{ $item->employee->last_name }}</td>
                                <td>{{ number_format($item->security_deposit, 2) }}</td>
                                <td>{{ $item->start_date }}</td>
                                <td>{{ ucfirst($item->status) }}</td>
                                <td class="d-flex">
                                    <a href="{{ route('employee_security_deposit', ['id' => $item->id]) }}" class="btn btn-sm btn-primary me-1">View</a>
                                    <form action="{{ route('employee_security_deposit.delete', $item->id) }}" method="POST" 
                                          onsubmit="return confirm('Are you sure you want to delete this deposit?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- EMI Table (Shown only in View/Edit) --}}
    @if(isset($deposit) && $deposit->emis->count())
        <div class="card mt-4">
            <div class="card-body">
                <h5>EMI Schedule</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>EMI Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deposit->emis as $index => $emi)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ \Carbon\Carbon::parse($emi->emi_date)->format('d-m-Y') }}</td>
                                    <td>₹{{ number_format($emi->emi_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $emi->status == 'pending' ? 'warning text-dark' : ($emi->status == 'paid' ? 'success' : 'secondary') }}">
                                            {{ ucfirst($emi->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($emi->status === 'pending')
                                            <form action="{{ route('employee_security_deposit.pause_emi', $emi->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning">Pause</button>
                                            </form>
                                            <form action="{{ route('employee_security_deposit.pay_emi', $emi->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">Mark as Paid</button>
                                            </form>
                                        @elseif ($emi->status === 'paused')
                                            <form action="{{ route('employee_security_deposit.resume_emi', $emi->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-info">Resume</button>
                                            </form>
                                        @else
                                            <em class="text-muted">No Action</em>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

@endsection
