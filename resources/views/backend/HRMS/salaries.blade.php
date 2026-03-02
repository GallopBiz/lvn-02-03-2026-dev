@extends('backend.layouts.main')
@section('main-container')
    @php
        $i = 0;
    @endphp
    <div class="main-content">
        <div class="form_section1_div">
            <div class="breadcrumb d-flex justify-content-between align-items-center">
                <h1 class="me-2">Employees Salaries</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                    Import Salaries
                </button>
                
            </div>
             <!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Salaries</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="importForm" action="{{ route('salaries.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="importFile" class="form-label">Select File (XLSX or CSV)</label>
                        <input type="file" name="file" id="importFile" class="form-control" accept=".xlsx,.csv" required>
                        <small class="text-muted">Only .xlsx or .csv files are allowed.</small>
                    </div>
                    <div id="fileError" class="text-danger" style="display: none;">Invalid file type. Please upload an XLSX or CSV file.</div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="processImport">Upload & Import</button>
            </div>
        </div>
    </div>
</div>
            <div class="separator-breadcrumb border-top"></div>
            @if (!empty($stream_master))
                <form id="progress-form" class="p-4 progress-form" action="{{ url('store-salaries') }}" method="post">
                    <input type="hidden"
                        @if (!empty($stream_master)) value=" {{ $stream_master->id }}"
                        @else
                            value="" @endif
                        name="id">
                @else
                    <form id="progress-form" class="p-4 progress-form" action="{{ url('save-salaries') }}" method="post">
            @endif

            @csrf
            <div class="row">
                <div class="col-md-3 form-group mb-3">
                    <label for="DepartmentID">Employees</label>

                    <select type="text" name="employee_id" class="form-control" id="EmployeeID1">
                        @if (!empty($stream_master))
                            @foreach ($employeeName as $employee)
                                <option {{ $stream_master->employee_id == $employee->id ? 'selected' : '' }}
                                    value="{{ $employee->id }}">{{ $employee->first_name }} {{ $employee->last_name }}
                                </option>
                            @endforeach
                        @else
                            <option value="" selected> -- Please select -- </option>
                            @foreach ($employeeName as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->first_name }}
                                    {{ $employee->last_name }}</option>
                            @endforeach
                        @endif

                    </select>
                    @error('employee_id')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="basic_salary">Gross Salary Amount</label>
                    <input required class="form-control" id="basic_salary" name="basic_salary" type="text"
                        onchange="validateAmount(this,'basic_salary')"
                        @if (!empty($stream_master)) value=" {{ $stream_master->basic_salary }}"
                    
                  @else
                    value="" @endif
                        placeholder="Gross Salary" />
                    <span class="text-danger" id="basic_salary"></span>
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="effective_date">Effective Date</label>
                    <input required class="form-control" id="effective_date" name="effective_date" type="date"
                        @if (!empty($stream_master)) value="{{ $stream_master->effective_date }}"
                  @else
                    value="" @endif
                        placeholder="effective_date" />
                    @error('effective_date')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                {{-- <div class="col-md-3 form-group mb-3">
                    <label for="deductions">Deductions</label>
                    <select class="form-control deductions" name="deductions_ids[]" multiple id="deductions"
                        data-live-search="true">

                        <option value="" disabled>Please Select</option>

                        @foreach ($basicDeduction as $deduction)
                            <option value="{{ $deduction->id }}"
                                {{ !empty($stream_master) && $stream_master->employeeDeductions->pluck('basic_deduction_id')->contains($deduction->id) ? 'selected' : '' }}>
                                {{ $deduction->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('deductions_ids')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div> --}}
                <div class="col-md-3 form-group mb-3">
                    <label for="deductions">Deductions</label>
                    <select class="form-control deductions" name="deductions_ids[]" multiple id="deductions" data-live-search="true">
                        <option value="" disabled>Please Select</option>
                
                        @foreach ($basicDeduction as $deduction)
                            <option value="{{ $deduction->id }}"
                                {{ !empty($stream_master) && $stream_master->employeeDeductions->pluck('basic_deduction_id')->contains($deduction->id) ? 'selected' : '' }}>
                                {{ $deduction->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                @error('deductions_ids')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror
                
                <!-- Manual Amount Fields for Selected Deductions -->
                <div id="manual-deductions" class="col-md-3 form-group mb-3">
                    @isset($stream_master)
                    @foreach ($basicDeduction as $deduction)
                        @if(in_array($deduction->id, $stream_master->employeeDeductions->pluck('basic_deduction_id')->toArray()))
                            <div class="form-group manual-deduction-field" id="manual_amount_group_{{ $deduction->id }}">
                                <label for="manual_amount_{{ $deduction->id }}">{{ $deduction->name }} (Manual Amount)</label>
                                <input type="number" name="manual_deductions[{{ $deduction->id }}]"
                                    id="manual_amount_{{ $deduction->id }}"
                                    class="form-control manual-amount"
                                    value="{{ $manualDeductions[$deduction->id] ?? '' }}"
                                    placeholder="Enter amount">
                            </div>
                        @endif
                    @endforeach
                    @endisset
                </div>
                


                <div class="col-md-2 form-group mb-3">
                    <label for="is_active">Is Active Salary</label>
                    <input id="is_active" type="checkbox" name="is_active" value="1"
                        {{ isset($stream_master) && $stream_master->is_active ? 'checked' : '' }} />
                    @error('is_active')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label for="has_children">Has Children</label>
                    <input type="checkbox" id="has_children" name="has_children" value="1"
                        {{ isset($stream_master) && count($stream_master->employeeChilds) >= 1 ? 'checked' : '' }}>
                </div>

                <!-- Display child select and amount fields -->
                <div id="children-container">
                    @if (isset($stream_master) && $stream_master->employeeChilds->isNotEmpty())
                        @foreach ($stream_master->employeeChilds as $index => $child)
                            <div class="child-group" data-index="{{ $index }}">
                                <div class="col-md-3 form-group mb-3">
                                    <label for="child_select_{{ $index }}">Select Child</label>
                                    <select class="form-control" id="child_select_{{ $index }}"
                                        name="child_select[]">
                                        <option value="" disabled>Select Child</option>
                                        @foreach ($students as $student)
                                            <option value="{{ $student->id }}"
                                                {{ old('child_select.' . $index, $child->student_id) == $student->id ? 'selected' : '' }}>
                                                {{ $student->student_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('child_select.' . $index)
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 form-group mb-3">
                                    <label for="child_amount_{{ $index }}">Child Amount</label>
                                    <input type="text" class="form-control" id="child_amount_{{ $index }}"
                                        name="child_amount[]" value="{{ $child->fee_amount }}" placeholder="Enter Amount"
                                        onchange="validateAmount(this, 'child_amount_error_{{ $index }}')">
                                    <span class="text-danger" id="child_amount_error_{{ $index }}"></span>
                                    @error('child_amount.' . $index)
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 form-group mb-3">
                                    <label for="emi_amount_{{ $index }}">EMI Amount</label>
                                    <input type="text" class="form-control" id="emi_amount_{{ $index }}"
                                        name="emi_amount[]" value="{{ $child->emi_amount }}" placeholder="Enter Amount"
                                        onchange="validateAmount(this, 'emi_amount_error_{{ $index }}')">
                                    <span class="text-danger" id="emi_amount_error_{{ $index }}"></span>
                                    @error('emi_amount.' . $index)
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- EMI Details Section -->
                                <div class="col-md-12 form-group mb-3">
                                    <h5>EMIs for {{ $child->student->student_name }}</h5>
                                    <table class="table table-bordered">
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
                                            {{-- @foreach ($child->student->emis as $emi) --}}
                                            @foreach ($child->student->emis->filter(function ($emi) use ($stream_master) {
            return $emi->employee_salary_id == $stream_master->id;
        }) as $emi)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $emi->emi_date }}</td>
                                                    <td>{{ $emi->emi_amount }}</td>
                                                    @php
                                                        $statusAmount=$emi->status=="paid"?"(".$emi->paid_in_cash.")"??"(".$emi->emi_amount.")":"";
                                                    @endphp
                                                    <td>{{ ucfirst($emi->status) }}{{ $statusAmount }}</td>
                                                    <td>
                                                        @if ($emi->status == 'pending')
                                                            <button type="button" class="btn btn-warning btn-sm"
                                                                data-bs-toggle="modal" data-bs-target="#pauseEmiModal"
                                                                onclick="setEmiDetails({{ $emi->id }}, {{ $emi->emi_amount }})">
                                                                Pause EMI
                                                            </button>
                                                        @endif
                                                        @if ($emi->status == 'paid' || $emi->status == 'paused')
                                                            <span
                                                                class="badge bg-{{ $emi->status == 'paid' ? 'success' : ($emi->status == 'paused' ? 'warning' : 'secondary') }}">
                                                                {{ ucfirst($emi->status) }}
                                                        @endif
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Remove Button (only show if there are more than one child) -->
                                @if (count($stream_master->employeeChilds) >= 1)
                                    <button type="button" class="btn btn-danger remove-child-button"
                                        style="margin-top: 20px;"
                                        onclick="markForDeletion(this, {{ $child->id }})">Remove</button>
                                    <input type="hidden" name="deleted_children[]" class="delete-flag" value="">
                                @endif
                            </div>
                        @endforeach
                    @else
                        <!-- Empty case for create: If editing, already selected children will show up above -->
                        <div id="children_fields"
                            style="display: {{ !empty($stream_master) && $stream_master->has_children ? 'block' : 'none' }};">
                            <div class="child-group" data-index="0">
                                <div class="col-md-3 form-group mb-3">
                                    <label for="child_select_0">Select Child</label>
                                    <select class="form-control" id="child_select_0" name="child_select[]">
                                        <option value="" disabled selected>Select Child</option>
                                        @foreach ($students as $student)
                                            <option value="{{ $student->id }}">
                                                {{ $student->student_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('child_select.0')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 form-group mb-3">
                                    <label for="child_amount_0">Child Amount</label>
                                    <input type="text" class="form-control" id="child_amount_0" name="child_amount[]"
                                        placeholder="Enter Amount"
                                        onchange="validateAmount(this, 'child_amount_error_0')">
                                    <span class="text-danger" id="child_amount_error_0"></span>
                                    @error('child_amount.0')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3 form-group mb-3">
                                    <label for="emi_amount_0">EMI Amount</label>
                                    <input type="text" class="form-control" id="emi_amount_0" name="emi_amount[]"
                                        placeholder="Enter Amount" onchange="validateAmount(this, 'emi_amount_error_0')">
                                    <span class="text-danger" id="emi_amount_error_0"></span>
                                    @error('emi_amount.0')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="button" class="btn btn-danger remove-child-button"
                                    style="margin-top: 20px;">Remove</button>
                            </div>
                        </div>
                    @endif

                </div>



                <!-- Button to Add New Child (Initially hidden) -->
                <div class="col-md-3 form-group mb-3" id="add_child_button_container"
                    style="{{ isset($stream_master) && $stream_master->employeeChilds->isNotEmpty() ? '' : 'display:none;' }}">
                    <button type="button" id="add_child_button" class="btn btn-primary">Add Another Child</button>
                </div>

                {{-- <div class="col-md-3 form-group mb-3">
                    <label for="AttendanceMonth">Attendance Month</label>
                    <select type="text" name="AttendanceMonth" class="form-control" id="EmployeeID">
                        <option value="" hidden>--select--</option>
                        <option
                            @if (!empty($stream_master)) {{ $stream_master[0]->AttendanceMonth == '1' ? 'selected' : '' }} @endif
                            value="1">january</option>
                        <option
                            @if (!empty($stream_master)) {{ $stream_master[0]->AttendanceMonth == '2' ? 'selected' : '' }} @endif
                            value="2">february</option>
                        <option
                            @if (!empty($stream_master)) {{ $stream_master[0]->AttendanceMonth == '3' ? 'selected' : '' }} @endif
                            value="3">march</option>
                        <option
                            @if (!empty($stream_master)) {{ $stream_master[0]->AttendanceMonth == '4' ? 'selected' : '' }} @endif
                            value="4">april</option>
                        <option
                            @if (!empty($stream_master)) {{ $stream_master[0]->AttendanceMonth == '5' ? 'selected' : '' }} @endif
                            value="5">may</option>
                        <option
                            @if (!empty($stream_master)) {{ $stream_master[0]->AttendanceMonth == '6' ? 'selected' : '' }} @endif
                            value="6">june</option>
                        <option
                            @if (!empty($stream_master)) {{ $stream_master[0]->AttendanceMonth == '7' ? 'selected' : '' }} @endif
                            value="7">july</option>
                        <option
                            @if (!empty($stream_master)) {{ $stream_master[0]->AttendanceMonth == '8' ? 'selected' : '' }} @endif
                            value="8">august</option>
                        <option
                            @if (!empty($stream_master)) {{ $stream_master[0]->AttendanceMonth == '9' ? 'selected' : '' }} @endif
                            value="9">september</option>
                        <option
                            @if (!empty($stream_master)) {{ $stream_master[0]->AttendanceMonth == '10' ? 'selected' : '' }} @endif
                            value="10">october</option>
                        <option
                            @if (!empty($stream_master)) {{ $stream_master[0]->AttendanceMonth == '11' ? 'selected' : '' }} @endif
                            value="11">november</option>
                        <option
                            @if (!empty($stream_master)) {{ $stream_master[0]->AttendanceMonth == '12' ? 'selected' : '' }} @endif
                            value="12">december</option>
                    </select>

                </div> --}}








                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" id="reset" class="btn btn-primary" name="btn"
                        value="Reset Form">Reset</button>

                    @if (request()->route()->getName() !== 'salaries')
                        <a href="{{ url('salaries') }}" class="btn btn-primary">Add New</a>
                    @endif

                </div>
            </div>
            </form>
            <br>
        </div>
        <!-- Modal for Pause EMI -->
        <div class="modal fade" id="pauseEmiModal" tabindex="-1" aria-labelledby="pauseEmiModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <form id="pauseEmiForm" method="POST" action="{{ route('pauseStudentEmi') }}">
                            @csrf
                            <input type="hidden" name="emi_id" id="emi_id">

                            <div class="modal-header">
                                <h5 class="modal-title" id="pauseEmiModalLabel">Pause EMI</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="cash_amount" class="form-label">Cash Amount (Optional)</label>
                                    <input type="text" class="form-control" id="cash_amount" name="cash_amount"
                                        placeholder="Enter cash amount if paying manually">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-warning">Pause EMI</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="separator-breadcrumb border-top"></div>
        <div class="row">


            <div class="col-md-12 mb-4">
                <div class="breadcrumb">
                    <h1 class="me-2">List of Employess :-</h1>
                </div>
                <div class="separator-breadcrumb border-top"></div>

                <div class="card text-start">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="display table table-striped table-bordered" id="deafult_ordering_table_wrapper"
                                style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Employee Name</th>
                                        <th>Gross Amount</th>
                                        <th>Effective Date</th>
                                        <th>Deductions</th>
                                        <th>Status</th>
                                        <th>Action </th>
                                        <!-- <th>Section</th>
                                                        <th>Class Strength</th> -->
                                    </tr>
                                </thead>
                                <tbody>


                                    @if (!empty($stream))
                                        @foreach ($stream as $streams)
                                            <tr>
                                                <td>{{ ++$i }}</td>
                                                <td class= "uperletter">{{ $streams->employee->first_name }}
                                                    {{ $streams->employee->last_name }}</td>
                                                <td>{{ $streams->basic_salary }}</td>
                                                <td>{{ date('d-m-Y', strtotime($streams->effective_date)) }}</td>
                                                <td class="uperletter">
                                                    @php
                                                        $names = [];
                                                    @endphp

                                                    @foreach ($streams->employeeDeductions as $deduction)
														@php
															if ($deduction->basicDeduction) {
																$amount = $deduction->manual_amount ?? $deduction->basicDeduction->amount;
																$names[] = $deduction->basicDeduction->name . ' (' . $amount . ')';
															}
														@endphp
													@endforeach

                                                    {{ implode(', ', $names) }}
                                                </td>
                                                <td>{{ $streams->is_active == 1 ? 'Yes' : 'No' }}</td>
                                                <td class='d-flex'>
                                                    <a class="btn btn-primary m-1"
                                                        href="{{ url('view-salaries') . '/' . $streams->id }}">Edit</a>
                                                    @csrf
                                                    <a class="btn btn-raised ripple btn-danger m-1"
                                                        href="{{ url('delete-salaries') . '/' . $streams->id }}"
                                                        onclick="confirmDelete(event)">Delete</a>
                                                </td>
                                            </tr>
                                            <!-- </?php $i++; ?> -->
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="9" class="text-center">No Data Found</td>
                                        </tr>
                                    @endif
                                </tbody>
                                <tfoot>

                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- end of main-content -->
    </div>




    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <script>
        jQuery(document).ready(function($) {
            // Initialize Select2
            // $('#inq-form-nomenu').select2();
            $('.deductions').select2();
            document.getElementById('progress-form').addEventListener('submit', function(e) {
                const childGroups = this.querySelectorAll('.child-group');
                childGroups.forEach(group => {
                    const amountInput = group.querySelector('input[name="child_amount[]"]');
                    if (amountInput && !amountInput.value.trim()) {
                        group.remove(); // Remove the group if the input is empty
                    }
                });
            });
        });

        function markForDeletion(button, recordId) {
            const group = button.closest('.child-group');
            const deleteFlag = group.querySelector('.delete-flag');

            // Mark as deleted
            deleteFlag.value = recordId; // Set the ID to mark for deletion on the server

            // Clear form values
            const childSelect = group.querySelector('select[name="child_select[]"]');
            const childAmount = group.querySelector('input[name="child_amount[]"]');
            const emiAmount = group.querySelector('input[name="emi_amount[]"]');

            if (childSelect) childSelect.value = ""; // Clear the select field
            if (childAmount) childAmount.value = ""; // Clear the child amount
            if (emiAmount) emiAmount.value = ""; // Clear the EMI amount

            // Hide the group visually but keep it in the form
            group.style.display = 'none';
        }

        const students = @json($students);


        let childIndex = 0; // Initialize child index

        document.getElementById('has_children').addEventListener('change', function() {
            const childrenFields = document.getElementById('children-container');
            const addChildButtonContainer = document.getElementById('add_child_button_container');

            if (this.checked) {
                childrenFields.style.display = 'block';
                addChildButtonContainer.style.display = 'block';
                if (childIndex === 0) addChildInput(); // Ensure at least one child is added
            } else {
                childrenFields.innerHTML = ''; // Clear all child inputs
                childrenFields.style.display = 'none';
                addChildButtonContainer.style.display = 'none';
                childIndex = 0; // Reset index
            }
        });

        // Add New Child Input Field
        document.getElementById('add_child_button').addEventListener('click', addChildInput);

        function addChildInput() {
            const container = document.getElementById('children-container');

            const childGroup = document.createElement('div');
            childGroup.className = 'child-group';
            childGroup.dataset.index = childIndex;

            // Use the `students` variable from Blade to populate the options
            const studentOptions = students.map(student =>
                `<option value="${student.id}">${student.student_name}</option>`
            ).join('');

            childGroup.innerHTML = `
        <div class="col-md-3 form-group mb-3">
            <label for="child_select_${childIndex}">Select Child</label>
            <select class="form-control" id="child_select_${childIndex}" name="child_select[]">
                <option value="" disabled selected>Select Child</option>
                ${studentOptions}
            </select>
        </div>
        <div class="col-md-3 form-group mb-3">
            <label for="child_amount_${childIndex}">Child Amount</label>
            <input type="text" class="form-control" id="child_amount_${childIndex}" name="child_amount[]" placeholder="Enter Amount"
                onchange="validateAmount(this, 'child_amount_error_${childIndex}')">
            <span class="text-danger" id="child_amount_error_${childIndex}"></span>
        </div>
        <div class="col-md-3 form-group mb-3">
            <label for="emi_amount_${childIndex}">EMI Amount</label>
            <input type="text" class="form-control" id="emi_amount_${childIndex}" name="emi_amount[]" placeholder="Enter Amount"
                onchange="validateAmount(this, 'emi_amount_error_${childIndex}')">
            <span class="text-danger" id="emi_amount_error_${childIndex}"></span>
        </div>
        <button type="button" class="btn btn-danger remove-child-button" style="margin-top: 20px;">Remove</button>`;

            // Add Remove Button Functionality
            const removeButton = childGroup.querySelector('.remove-child-button');
            removeButton.addEventListener('click', function() {
                childGroup.remove();
            });

            container.appendChild(childGroup);
            childIndex++; // Increment index for the next child
        }


        // Validate Amount Input
        function validateAmount(input, errorId) {
            const regex = /^[0-9]*(\.[0-9]{0,2})?$/;
            const value = input.value;

            if (!regex.test(value)) {
                document.getElementById(errorId).textContent = "Please enter a valid number (integer or decimal).";
                input.setCustomValidity("Invalid number.");
            } else {
                document.getElementById(errorId).textContent = "";
                input.setCustomValidity("");
            }
        }
    </script>

    <script>
        function confirmDelete(event) {
            event.preventDefault(); // Prevents the default link navigation

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If the user clicks on "Yes, delete it!", navigate to the delete URL
                    window.location.href = event.target.href;
                }
            });
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var select = document.getElementById('EmployeeID1');
            for (var i = 0; i < select.options.length; i++) {
                select.options[i].innerText = capitalizeFirstLetter(select.options[i].innerText);
            }
        });

        function capitalizeFirstLetter(str) {
            var words = str.toLowerCase().split(' ');
            for (var i = 0; i < words.length; i++) {
                words[i] = words[i].charAt(0).toUpperCase() + words[i].substring(1);
            }
            return words.join(' ');
        }
    </script>



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var select = document.getElementById('EmployeeID1');
            for (var i = 0; i < select.options.length; i++) {
                select.options[i].innerText = capitalizeFirstLetter(select.options[i].innerText);
            }
        });

        function capitalizeFirstLetter(str) {
            var words = str.toLowerCase().split(' ');
            for (var i = 0; i < words.length; i++) {
                words[i] = words[i].charAt(0).toUpperCase() + words[i].substring(1);
            }
            return words.join(' ');
        }

        document.addEventListener('DOMContentLoaded', function() {
            $("#reset").on("click", function() {
                $("#SalaryAmount").val("");
                $("#EffectiveDate").val("");
                $("#EmployeeID").val("");
                $("#EmployeeID1").val("");
            });

        })


        function setEmiDetails(emiId, emiAmount) {
            $(document).ready(function() {
                document.getElementById('emi_id').value = emiId;
                document.getElementById('cash_amount').value = emiAmount;
            })
        }


        $(document).ready(function() {
            // When the "Pause EMI" button is clicked
            $('#pauseEmiModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget); // Button that triggered the modal
                var emiId = button.data('emi-id'); // Extract the EMI ID from data attributes

                // Update the modal's action and the EMI ID field
                var modal = $(this);
                modal.find('#emi_id').val(emiId);
            });

            // When the form is submitted
            $('#pauseEmiForm').on('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(this);
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        // Check if the EMI was paused successfully
                        if (response.success) {
                            // Update the EMI status in the table (optional)
                            $('tr').each(function() {
                                var emiId = $(this).data('emi-id');
                                if (emiId == response.emi_id) {
                                    $(this).find('.badge').text('Paused').removeClass(
                                        'bg-secondary').addClass('bg-warning');
                                }
                            });

                            // Close the modal
                            $('#pauseEmiModal').modal('hide');
                            location.reload();
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle error
                        alert('An error occurred while pausing the EMI.');
                    }
                });
            });
        });

        let manualDeductionsContainer = document.getElementById("manual-deductions");
    let deductionsDropdown = document.getElementById("deductions");

    // Store existing manual deduction amounts
    let manualDeductions = {};

    // Function to update manual deduction input fields dynamically
    function updateManualDeductions() {
        let selectedDeductions = Array.from(deductionsDropdown.selectedOptions).map(option => option.value);

        // Remove input fields for unselected deductions
        document.querySelectorAll(".manual-deduction-field").forEach(field => {
            let fieldId = field.id.replace("manual_amount_group_", "");
            if (!selectedDeductions.includes(fieldId)) {
                field.remove();
                delete manualDeductions[fieldId]; // Remove from stored values
            }
        });

        // Add input fields for new selections
        selectedDeductions.forEach(deductionId => {
            if (!document.getElementById("manual_amount_group_" + deductionId)) {
                let deductionName = deductionsDropdown.querySelector(`option[value="${deductionId}"]`).innerText;
                let inputValue = manualDeductions[deductionId] || ''; // Keep old value if exists

                let inputFieldHtml = `
                    <div class="form-group manual-deduction-field" id="manual_amount_group_${deductionId}">
                        <label for="manual_amount_${deductionId}">${deductionName} (Manual Amount)</label>
                        <input type="number" name="manual_deductions[${deductionId}]" 
                            id="manual_amount_${deductionId}"
                            class="form-control manual-amount"
                            value="${inputValue}" 
                            placeholder="Enter amount">
                    </div>
                `;

                manualDeductionsContainer.insertAdjacentHTML("beforeend", inputFieldHtml);
            }
        });

        // Store input values when they change
        document.querySelectorAll(".manual-amount").forEach(input => {
            input.addEventListener("input", function() {
                manualDeductions[this.id.replace("manual_amount_", "")] = this.value;
            });
        });
    }

        const allDeductions = @json($basicDeduction); // Pass all deductions as a JS array
            console.log(allDeductions);

            // Handle selection change on deductions dropdown
            $(document).on('change', '#deductions', updateManualDeductions);


            document.getElementById('processImport').addEventListener('click', function() {
    let fileInput = document.getElementById('importFile');
    let filePath = fileInput.value;
    let allowedExtensions = /(\.xlsx|\.csv)$/i;

    if (!allowedExtensions.exec(filePath)) {
        document.getElementById('fileError').style.display = 'block';
        fileInput.value = '';
        return false;
    } else {
        document.getElementById('fileError').style.display = 'none';
        document.getElementById('importForm').submit();
    }
});
    </script>



@endsection
