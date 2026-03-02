@extends('backend.layouts.main')

@section('main-container')
    <style>
        .uperletter {
            text-transform: capitalize;
        }
    </style>
<meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="main-content">
        <div class="form_section1_div">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="me-2">Leave Type Master</h1>
                <button id="runCommandBtn" class="btn btn-primary">Run Leave Reset</button>
            </div>
            <div class="separator-breadcrumb border-top"></div>
            

            <form id="progress-form" class="p-4 progress-form"
                action="{{ !empty($leaveType) ? url('store-leave_Type') : url('save-leave_Type') }}" method="post">
                @csrf

                @if (!empty($leaveType))
                    <input type="hidden" name="id" value="{{ $leaveType->id }}">
                @endif

                <div class="row">

                    <div class="col-md-4 form-group mb-3">
                        <label for="name">Leave Name</label>
                        <input required class="form-control" id="name" name="name" type="text"
                            value="{{ $leaveType->name ?? '' }}" placeholder="Leave Name" />
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 form-group mb-3">
                        <label for="annual_entitlement">Annual Entitlement</label>
                        <input required class="form-control" id="annual_entitlement" name="annual_entitlement"
                            type="number" value="{{ $leaveType->annual_entitlement ?? '' }}"
                            placeholder="Annual Entitlement" min="0" step="any" />
                        <small class="form-text text-muted">Enter only numbers.</small>
                        @error('annual_entitlement')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 form-group mb-3">
                        <label for="reset_month">Reset Month</label>
                        <select required name="reset_month" id="reset_month" class="form-control">
                            <option value="">Please Select</option>
                            <option value="1"
                                {{ isset($leaveType) && $leaveType->reset_month == 1 ? 'selected' : '' }}>January</option>
                            <option value="2"
                                {{ isset($leaveType) && $leaveType->reset_month == 2 ? 'selected' : '' }}>February</option>
                            <option value="3"
                                {{ isset($leaveType) && $leaveType->reset_month == 3 ? 'selected' : '' }}>March</option>
                            <option value="4"
                                {{ isset($leaveType) && $leaveType->reset_month == 4 ? 'selected' : '' }}>April</option>
                            <option value="5"
                                {{ isset($leaveType) && $leaveType->reset_month == 5 ? 'selected' : '' }}>May</option>
                            <option value="6"
                                {{ isset($leaveType) && $leaveType->reset_month == 6 ? 'selected' : '' }}>June</option>
                            <option value="7"
                                {{ isset($leaveType) && $leaveType->reset_month == 7 ? 'selected' : '' }}>July</option>
                            <option value="8"
                                {{ isset($leaveType) && $leaveType->reset_month == 8 ? 'selected' : '' }}>August</option>
                            <option value="9"
                                {{ isset($leaveType) && $leaveType->reset_month == 9 ? 'selected' : '' }}>September
                            </option>
                            <option value="10"
                                {{ isset($leaveType) && $leaveType->reset_month == 10 ? 'selected' : '' }}>October</option>
                            <option value="11"
                                {{ isset($leaveType) && $leaveType->reset_month == 11 ? 'selected' : '' }}>November
                            </option>
                            <option value="12"
                                {{ isset($leaveType) && $leaveType->reset_month == 12 ? 'selected' : '' }}>December
                            </option>
                        </select>

                        @error('reset_month')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group mb-3">
                        <label for="apply_before_days">Apply Before Days</label>
                        <input class="form-control" id="apply_before_days" name="apply_before_days" type="number"
                            value="{{ $leaveType->apply_before_days ?? '' }}" placeholder="Apply Before Days"
                            min="0" step="any" />
                        <small class="form-text text-muted">Enter only numbers.</small>
                        @error('apply_before_days')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2 form-group mb-3">
                        <label for="is_paid">Is Paid</label>
                        <br>
                        <input id="is_paid" type="checkbox" name="is_paid" value="1"
                            {{ isset($leaveType) && $leaveType->is_paid ? 'checked' : '' }} />
                        @error('is_paid')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2 form-group mb-3">
                        <label for="allow_backdate">Allow Backdate</label>
                        <br>
                        <input id="allow_backdate" type="checkbox" name="allow_backdate" value="1"
                            {{ isset($leaveType) && $leaveType->allow_backdate ? 'checked' : '' }} />
                        @error('allow_backdate')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2 form-group mb-3">
                        <label for="is_carry_forward">Carry Forward Allow?</label>
                        <br>
                        <input id="is_carry_forward" type="checkbox" name="is_carry_forward" value="1"
                            {{ isset($leaveType) && $leaveType->is_carry_forward ? 'checked' : '' }} />
                        @error('is_carry_forward')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" class="btn btn-secondary" id="reset-button">Reset</button>
                    @if (request()->route()->getName() !== 'leave_Types')
                        <a href="{{ url('leave_Types') }}" class="btn btn-primary">Add New</a>
                        @endif
                </div>
            </form><br>
        </div>

        <div class="separator-breadcrumb border-top"></div>
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="breadcrumb">
                    <h1 class="me-2">List of Leave Types:</h1>
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
                                        <th>Name</th>
                                        <th>Annual Entitlement</th>
                                        <th>Reset Month</th>
                                        <th>Is Paid</th>
                                        <th>Apply Before Days</th>
                                        <th>Allow Backdate</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($leaveTypes))
                                        @foreach ($leaveTypes as $index => $leaveType)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $leaveType->name }}</td>
                                                <td>{{ $leaveType->annual_entitlement }}</td>
                                                <td>{{ $leaveType->reset_month }}</td>
                                                <td>{{ $leaveType->is_paid == 1 ? 'Yes' : 'No' }}</td>
                                                <td>{{ $leaveType->apply_before_days }}</td>
                                                <td>{{ $leaveType->allow_backdate == 0 ? 'No' : 'Yes' }}</td>

                                                <td class='d-flex'>
                                                    <a class="btn btn-primary m-1"
                                                        href="{{ url('view-leave_type/' . $leaveType->id) }}">Edit</a>
                                                    <a class="btn btn-raised ripple btn-danger m-1"
                                                        href="{{ url('delete-leave_type/' . $leaveType->id) }}"
                                                        onclick="confirmDelete(event)">Delete</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="9" class="text-center">No Data Found</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- SweetAlert2 Script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        function confirmDelete(event) {
            event.preventDefault();

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
                    window.location.href = event.target.href;
                }
            });
        }

        function resetForm() {
                $("#name").val("");
                $("#annual_entitlement").val("");
                $("#reset_month").val("");
                $("#apply_before_days").val("");
                $("#is_paid").prop("checked", false);
                $("#allow_backdate").prop("checked", false);
                $("#is_carry_forward").prop("checked", false);
        }
        document.addEventListener('DOMContentLoaded', () => {
            // Attach event listener to the reset button
            document.getElementById('reset-button').addEventListener('click', resetForm);
        });

        document.getElementById("runCommandBtn").addEventListener("click", function () {
        if (confirm("Are you sure you want to run the leave reset")) {
            fetch("{{ route('run.leave.reset') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                }
            })
            .then(response => response.json())
            .then(data => alert(data.message))
            .catch(error => alert("Error: " + error));
        }
    });
    </script>
@endsection
