@extends('backend.layouts.main')
@section('main-container')
<style>
    .uperletter {
        text-transform: capitalize;
    }
</style>
<div class="main-content">
    <div class="form_section1_div">
        <div class="breadcrumb">
            <h1 class="me-2">Employee Leave Balance</h1>
        </div>
        <div class="separator-breadcrumb border-top"></div>
        <form id="progress-form" class="p-4 progress-form"
            action="{{ !empty($employeeLeave) ? url('store-employeeleave') : url('save-employeeleave') }}"
            method="post">
            @csrf
            @if(!empty($employeeLeave))
                <input type="hidden" name="id" value="{{ $employeeLeave->Emp_ID }}">
            @endif
            <div class="row">
                <div class="col-md-3 form-group mb-3">
                    <label for="CL_Balance">CL Balance</label>
                    <input
                        required
                        class="form-control"
                        id="CL_Balance"
                        name="CL_Balance"
                        type="number"
                        value="{{ $employeeLeave->CL_Balance ?? '' }}"
                        placeholder="CL Balance"
                        min="0"
                        step="any"
                        oninput="validateNumber(this)"
                    />
                    <small class="form-text text-muted">Enter only numbers.</small>
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="ML_Balance">ML Balance</label>
                    <input
                        required
                        class="form-control uperletter"
                        id="ML_Balance"
                        name="ML_Balance"
                        type="number"
                        value="{{ $employeeLeave->ML_Balance ?? '' }}"
                        placeholder="ML Balance"
                        min="0"
                        step="any"
                        oninput="validateNumber(this)"
                    />
                    <small class="form-text text-muted">Enter only numbers.</small>
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label for="EL_Balance">EL Balance</label>
                    <input
                        required
                        class="form-control uperletter"
                        id="EL_Balance"
                        name="EL_Balance"
                        type="number"
                        value="{{ $employeeLeave->EL_Balance ?? '' }}"
                        placeholder="EL Balance"
                        min="0"
                        step="any"
                        oninput="validateNumber(this)"
                    />
                    <small class="form-text text-muted">Enter only numbers.</small>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form><br>
    </div>
    <div class="separator-breadcrumb border-top"></div>
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="breadcrumb">
                <h1 class="me-2">List of Employee Leaves:</h1>
            </div>
            <div class="separator-breadcrumb border-top"></div>
            <div class="card text-start">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="default_ordering_table_wrapper" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>CL Balance</th>
                                    <th>ML Balance</th>
                                    <th>EL Balance</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($employeeLeaves))
                                    @foreach($employeeLeaves as $index => $leave)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $leave->CL_Balance }}</td>
                                            <td>{{ $leave->ML_Balance }}</td>
                                            <td>{{ $leave->EL_Balance }}</td>
                                            <td class='d-flex'>
                                                <a class="btn btn-primary m-1" href="{{ url('view-employeeleave/' . $leave->Emp_ID) }}">Edit</a>
                                                <a class="btn btn-raised ripple btn-danger m-1" href="{{ url('delete-employeeleave/' . $leave->Emp_ID) }}" onclick="confirmDelete(event)">Delete</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr><td colspan="5" class="text-center">No Data Found</td></tr>
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
            cancelButtonColor: '#3085D6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = event.target.href;
            }
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        $("#reset").on("click", function () {
            $("#CL_Balance").val("");
            $("#ML_Balance").val("");
            $("#EL_Balance").val("");
        });
    });
</script>
@endsection