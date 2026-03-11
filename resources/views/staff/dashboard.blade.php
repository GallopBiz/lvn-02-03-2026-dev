@extends('backend.layouts.main')

@section('main-container')
    <div class="main-content-wrap sidenav-open d-flex flex-column">
        <h2>Welcome to the Staff Dashboard</h2>
        <p>Hello, {{ Auth::guard('staff')->user()->username ?? Auth::guard('staff')->user()->name }}!</p>
        <!-- Add your menu and dashboard widgets here -->
        <form method="POST" action="{{ route('staff.logout') }}">
            @csrf
            <button type="submit">Logout</button>
        </form>
    </div>
@endsection
