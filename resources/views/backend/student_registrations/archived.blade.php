@extends('backend.layouts.main')

@section('main-container')
<div class="main-content pt-4">
    <div class="breadcrumb">
        <h1 class="me-2">Archived Students</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card text-start">
                <div class="card-body">
                    <h4 class="card-title mb-3 text-end">
                        <a href="{{ route('student-registrations') }}"><button class="btn btn-outline-primary" type="button">Active Students</button></a>
                    </h4>

                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Form No.</th>
                                    <th>Scholar No</th>
                                    <th>Student Name</th>
                                    <th>Father Name</th>
                                    <th>Class Name</th>
                                    <th>Section Name</th>
                                    <th>DOB</th>
                                    <th>Session Name</th>
                                    <th>Mobile Number</th>
                                    <th>Archived Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($all_inquiry as $each_inq)
                                    <?php $notificationData1 = json_decode($each_inq->json_str, true) ?: []; ?>
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $each_inq->form_number }}</td>
                                        <td>{{ $each_inq->scholar_no }}</td>
                                        <td>{{ ucwords($each_inq->student_name ?? '') }}</td>
                                        <td>{{ ucwords($notificationData1['student_father_name'] ?? $notificationData1['fathername'] ?? '') }}</td>
                                        <td>{{ $each_inq->class_name }}</td>
                                        <td>{{ $notificationData1['section_name'] ?? $each_inq->section_name ?? '' }}</td>
                                        <td>{{ !empty($each_inq->date_of_birth) ? date('d-m-Y', strtotime($each_inq->date_of_birth)) : '' }}</td>
                                        <td>{{ $each_inq->session_name }}</td>
                                        <td>{{ $notificationData1['father_mobile'] ?? $each_inq->mobile_number ?? '' }}</td>
                                        <td>{{ !empty($each_inq->updated_at) ? date('d-m-Y', strtotime($each_inq->updated_at)) : '' }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                    <a class="dropdown-item" href="{{ route('registrationviewlist', $each_inq->id) }}">View</a>
                                                    <form method="POST" action="{{ route('restore-student', $each_inq->id) }}">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">Restore Student</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="12" class="text-center">No archived students found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
