@extends('layouts.app')
@section('main-container')

@if (count($errors) > 0)
@endif
<!-- Error messages removed as requested -->
<div class="row justify-content-center mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Edit Staff/Admin User</h4>
            </div>
            <div class="card-body">
                {!! Form::model($user, ['method' => 'PATCH','route' => ['users.update', $user->id]]) !!}
                    <div class="form-group mb-3">
                            <label for="username">Username</label>
                            {!! Form::text('username', null, ['class' => 'form-control', 'placeholder' => 'Enter username', 'required', 'readonly']) !!}
                    </div>
                    <div class="form-group mb-3">
                            <label for="current_password">Current Password</label>
                                <input type="password" class="form-control mb-2" value="********" readonly>
                           
                            <label for="new_password">New Password</label>
                            <div class="input-group">
                                <input class="form-control" placeholder="Enter new password (leave blank to keep current)" id="edit_new_password" name="password" type="password">
                                <span class="input-group-text" onclick="toggleEditPassword()" style="cursor:pointer;">
                                    <i class="fa fa-eye" id="toggleEditPasswordIcon"></i>
                                </span>
                            </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="employee_id">Select Employee</label>
                        {!! Form::select('employee_id', $employees, $user->employee_id, ['class' => 'form-control', 'required']) !!}
                    </div>
                    <div class="form-group mb-3">
                        <label for="role">Role</label>
                        {!! Form::select('role', $roles, $user->role, ['class' => 'form-control', 'required']) !!}
                    </div>
                    <div class="form-group mb-3 text-center">
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

@endsection
<script>
function toggleCurrentPassword() {
    var input = document.getElementById('current_password');
    var icon = document.getElementById('toggleCurrentPasswordIcon');
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
function toggleEditPassword() {
    var input = document.getElementById('edit_new_password');
    var icon = document.getElementById('toggleEditPasswordIcon');
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
<script>
function togglePassword(id) {
    var input = document.getElementById(id);
    var icon = document.getElementById('toggleEditPasswordIcon');
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = "password";
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
<!-- Make sure FontAwesome is loaded in your layout -->
