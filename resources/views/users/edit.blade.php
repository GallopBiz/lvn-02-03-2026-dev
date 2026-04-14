@extends('layouts.app')
@section('main-container')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="row justify-content-center mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Edit Staff/Admin User</h4>
            </div>
            <div class="card-body">
                {!! Form::model($user, ['method' => 'PATCH','route' => ['users.update', $user->id]]) !!}
                    <div class="form-group mb-3">
                        <label for="student_name">Name</label>
                        {!! Form::text('student_name', $user->student_name ?? $user->name ?? '', ['class' => 'form-control', 'placeholder' => 'Enter name', 'required']) !!}
                        <label for="username">Username</label>
                        {!! Form::text('username', null, ['class' => 'form-control', 'placeholder' => 'Enter username', 'required', 'readonly']) !!}
                    </div>
                    <div class="form-group mb-3">
                        <label for="current_password">Current Password</label>
                        <input type="password" class="form-control mb-2" value="********" readonly>

                        <label for="new_password">New Password</label>
                        <div class="input-group mb-2">
                            <input class="form-control" placeholder="Enter new password (leave blank to keep current)" id="edit_new_password" name="password" type="password">
                            <span class="input-group-text" onclick="toggleEditPassword()" style="cursor:pointer;">
                                <i class="fa fa-eye" id="toggleEditPasswordIcon"></i>
                            </span>
                        </div>
                        <label for="confirm_password">Confirm New Password</label>
                        <input class="form-control" placeholder="Confirm new password" id="edit_confirm_password" name="confirm-password" type="password">
                    </div>
                    <div class="form-group mb-3">
                        <label for="employee_id">Employee</label>
                        <input type="text" class="form-control" value="{{ $employees[$user->employee_id] ?? '' }}" readonly>
                        <input type="hidden" name="employee_id" value="{{ $user->employee_id }}">
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
