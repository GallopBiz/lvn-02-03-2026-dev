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
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Edit Staff/Admin User</h4>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-light">Back</a>
            </div>
            <div class="card-body">
                {!! Form::model($user, ['method' => 'PATCH','route' => ['users.update', $user->id]]) !!}
                    <div class="form-group mb-3">
                        {!! Form::hidden('student_name', $user->student_name ?? $user->name ?? $user->username ?? 'User') !!}
                        <label for="username">Username</label>
                        {!! Form::text('username', null, ['class' => 'form-control', 'placeholder' => 'Enter username', 'required', 'readonly']) !!}
                    </div>
                    <div class="form-group mb-3">
                        <label for="new_password">Set New Password</label>
                        <div class="input-group mb-2">
                            <input class="form-control" placeholder="Enter new password (leave blank to keep current)" id="edit_new_password" name="password" type="password">
                            <span class="input-group-text" onclick="toggleFieldPassword('edit_new_password', 'toggleEditPasswordIcon')" style="cursor:pointer;">
                                <i class="fa fa-eye" id="toggleEditPasswordIcon"></i>
                            </span>
                        </div>
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="input-group mb-2">
                            <input class="form-control" placeholder="Confirm new password" id="edit_confirm_password" name="confirm-password" type="password">
                            <span class="input-group-text" onclick="toggleFieldPassword('edit_confirm_password', 'toggleConfirmPasswordIcon')" style="cursor:pointer;">
                                <i class="fa fa-eye" id="toggleConfirmPasswordIcon"></i>
                            </span>
                        </div>
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
                        <a href="{{ route('users.index') }}" class="btn btn-secondary ms-2">Back</a>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

@endsection

<script>
function toggleFieldPassword(inputId, iconId) {
    var input = document.getElementById(inputId);
    var icon = document.getElementById(iconId);
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
