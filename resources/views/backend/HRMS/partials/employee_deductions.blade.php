{{-- @foreach ($employees as $employee)
<tr>
    <td class="uperletter">{{ $employee->first_name }} {{ $employee->last_name }}</td>
    @foreach ($employee->employeeDeductions as $deduction)
        <td>
            <input type="number" 
                class="form-control deduction-input"
                data-employee-id="{{ $employee->id }}" 
                data-deduction-id="{{ $deduction->basicDeduction->id }}" 
                value="{{ old('deductions.' . $employee->id . '.' . $deduction->basicDeduction->id, $deduction->manual_amount ?? $deduction->basicDeduction->amount) }}">
        </td>
    @endforeach
</tr>
@endforeach --}}

@foreach ($employees as $employee)
<tr>
    <td class="uperletter">{{ $employee->first_name }} {{ $employee->last_name }}</td>
    
    @foreach ($uniqueDeductions as $deductionId => $deductionName)
        @php
            // Get the deduction for the current employee
            $deduction = $employee->employeeDeductions->firstWhere('basicDeduction.id', $deductionId);
            $amount = $deduction ? ($deduction->manual_amount ?? $deduction->basicDeduction->amount) : null;
        @endphp

        <td>
            <input type="number" 
                class="form-control deduction-input"
                data-employee-id="{{ $employee->id }}" 
                data-deduction-id="{{ $deductionId }}" 
                value="{{ old('deductions.' . $employee->id . '.' . $deductionId, $amount) }}">
        </td>
    @endforeach
</tr>
@endforeach
