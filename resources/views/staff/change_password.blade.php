@extends('layouts.app')
@section('main-container')
<div class="row justify-content-center mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Change Password</h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('staff.password.update') }}">
                    @csrf
                    <div class="mb-4">
                        <h5 class="mb-3">Profile Information</h5>
                        <div class="form-group mb-2">
                            <label>Name</label>
                            <input class="form-control" value="{{ auth('staff')->user()->name }}" type="text" readonly>
                        </div>
                        <!-- Email removed as requested -->
                        <div class="form-group mb-2">
                            <label>Username</label>
                            <input class="form-control" value="{{ auth('staff')->user()->username ?? '' }}" type="text" readonly>
                        </div>
                        <div class="form-group mb-2">
                            <label>Role</label>
                            <input class="form-control" value="{{ auth('staff')->user()->getRoleNames()->first() ?? '' }}" type="text" readonly>
                        </div>
                    </div>
                    <!-- Current password field removed as requested -->
                    <div class="form-group mb-3">
                        <label for="new_password">New Password</label>
                        <div class="input-group">
                            <input class="form-control" id="new_password" name="new_password" type="password" required>
                            <span class="input-group-text" onclick="togglePassword('new_password', 'toggleNewPasswordIcon')" style="cursor:pointer;">
                                <i class="fa fa-eye" id="toggleNewPasswordIcon"></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="new_password_confirmation">Confirm New Password</label>
                        <div class="input-group">
                            <input class="form-control" id="new_password_confirmation" name="new_password_confirmation" type="password" required>
                            <span class="input-group-text" onclick="togglePassword('new_password_confirmation', 'toggleConfirmPasswordIcon')" style="cursor:pointer;">
                                <i class="fa fa-eye" id="toggleConfirmPasswordIcon"></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-group mb-3 text-center">
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
function togglePassword(id, iconId) {
    var input = document.getElementById(id);
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
<!-- Make sure FontAwesome is loaded in your layout -->
@endsection
