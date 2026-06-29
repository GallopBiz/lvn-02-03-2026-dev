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
                    <h4 class="mb-0">Create New Staff/Admin User</h4>
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-light">Back</a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('users.store') }}">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="username">Username</label>
                            <input class="form-control" placeholder="Enter username" required name="username" type="text">
                        </div>
                        <div class="form-group mb-3">
                                <label for="password">Password</label>
                                <div class="input-group">
                                    <input class="form-control" placeholder="Enter password" required id="password" name="password" type="password">
                                    <span class="input-group-text" onclick="togglePassword('password')" style="cursor:pointer;">
                                        <i class="fa fa-eye" id="togglePasswordIcon"></i>
                                    </span>
                                </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="employee_id">Select Employee</label>
                            <select class="form-control" required name="employee_id">
                                <option value="">Select employee</option>
                                @foreach($employees as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="role">Role</label>
                            <select class="form-control" required name="role">
                                <option value="">Select role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role }}">{{ $role }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3 text-center">
                            <button type="submit" class="btn btn-primary">Create User</button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary ms-2">Back</a>
                        </div>
                    </form>
    <script>
    function togglePassword(id) {
        var input = document.getElementById(id);
        var icon = document.getElementById('togglePasswordIcon');
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
                </div>
            </div>
        </div>
    </div>
@endsection
