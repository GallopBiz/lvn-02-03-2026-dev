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
                @if (!empty($subject_s))
                <h1 class="me-2">Edit Subject Master :-</h1>
                @else
                <h1 class="me-2">Subject Master :-</h1>
                @endif
            </div>
            <div class="separator-breadcrumb border-top"></div>
            @if (!empty($subject_s))
                <form id="progress-form" class="p-4 progress-form" action="{{ url('store-subject') }}" method="post">
                    <input type="hidden"
                        @if (!empty($subject_s)) @foreach ($subject_s as $subject_)
                  value=" {{ $subject_->id }}"
                  @endforeach
              @else
                  value="" @endif
                        name="id">
                @else
                    <form id="progress-form" class="p-4 progress-form" action="{{ url('save-subject') }}" method="post">
            @endif
            @csrf

            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label for="firstName1">Subject Name:</label>
                    <input required name="subject_name" class="form-control uperletter" id="subject_name" type="text"
                        value="<?php if (!empty($subject_s)) {
                            foreach ($subject_s as $subject_) {
                                echo $subject_->subject_name;
                            }
                        } else {
                        } ?>" placeholder="Subject_Name" />
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="classes">Classes</label>
                    <select name="classes[]" multiple class="form-control select2" id="classes">
                        <option value="">-- Please select --</option>
                        @foreach ($studentclasses as $studentclass)
                            <option value="{{ $studentclass->id }}"
                                {{ in_array($studentclass->id, old('classes', $selectedClasses ?? [])) ? 'selected' : '' }}>
                                {{ $studentclass->class_name }}
                            </option>
                        @endforeach
                    </select>

                </div>
                 <div class="col-md-4 form-group mb-3">
                    <label for="stream">Stream</label>
                    <select name="stream[]" multiple class="form-control select2" id="stream">
                        <option value="">-- Please select --</option>
                        @foreach ($streams as $stream)
                            <option value="{{ $stream->id }}"
                                {{ in_array($stream->id, old('stream', $selectedStreams ?? [])) ? 'selected' : '' }}>
                                {{ $stream->streams }}
                            </option>
                        @endforeach
                    </select>

                </div>

                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Subject Type</label><br>
                    <?php if (!empty($subject_s)) {
                        foreach ($subject_s as $subject_) {
                            // echo"<pre>";print_r($subject_);exit;
                            if ($subject_->subject_type == 'Academic') {
                                $check_1 = 'checked';
                            }elseif ($subject_->subject_type == 'Non Academic'){
                                $check_3 = 'checked';
                            }elseif ($subject_->subject_type == 'Skilled'){
                                $check_3 = 'checked';
                            } else {
                                $check_4 = 'checked';
                            }
                        }
                    }

                    $check_11 = '<label class="radio-inline">
                                              <input type="radio" name="subject_type" value="Academic"';
                    $check_11 .= !empty($check_1) ? $check_1 : '';
                    $check_11 .= '> Academic </label>';

                    $check_12 = '<label class="radio-inline">
                                              <input type="radio" name="subject_type" value="Non Academic"';
                    $check_12 .= !empty($check_2) ? $check_2 : '';
                    $check_12 .= '>  Non Academic </label>';

                    $check_13 = '<label class="radio-inline">
                                              <input type="radio" name="subject_type" value="Skilled"';
                    $check_13 .= !empty($check_3) ? $check_3 : '';
                    $check_13 .= '>  Skilled </label>';

                    $check_14 = '<label class="radio-inline">
                                              <input type="radio" name="subject_type" value="Optional"';
                    $check_14 .= !empty($check_4) ? $check_4 : '';
                    $check_14 .= '>  Optional </label>';

                    echo $check_11;
                    echo $check_12;
                    echo $check_13;
                    echo $check_14;

                    ?>

                    <!-- <label class="radio-inline">
                        <input type="radio" name="subject_type" value="Non Academic">Non Academic
                        </label> -->
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Evaluation</label><br>

                    <?php if (!empty($subject_s)) {
                        foreach ($subject_s as $subject_) {
                            if ($subject_->evaluation != 'Gradewise') {
                                $echeck_1 = 'checked';
                            } else {
                                $echeck_2 = 'checked';
                            }
                        }
                    }

                    $check_e1 = '<label class="radio-inline">
                                              <input type="radio" name="evaluation" value="Digtwise"';
                    $check_e1 .= !empty($echeck_1) ? $echeck_1 : '';
                    $check_e1 .= '>Digtwise </label>';

                    $check_e2 = '<label class="radio-inline">
                                              <input type="radio" name="evaluation" value="Gradewise"';
                    $check_e2 .= !empty($echeck_2) ? $echeck_2 : '';
                    $check_e2 .= '>Gradewise </label>';

                    echo $check_e1;
                    echo $check_e2;

                    ?>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="lastName1">Practical Type</label><br>
                    <input type="checkbox" id="vehicle1" name="practical" <?php if (!empty($subject_s[0]['practical'])) {
                        echo 'checked';
                    } ?> value="Yes">
                    <label for="vehicle1"> Yes</label><br>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label for="special_subject_formatting">Special Subject Formatting</label><br>
                    <input type="checkbox" id="special_subject_formatting" name="special_subject_formatting" value="1" <?php if (!empty($subject_s[0]['do_not_print_in_main_scholastic_area'])) {
                        echo 'checked';
                    } ?>>
                    <label for="special_subject_formatting"> Yes</label><br>
                </div>
                <div class="col-md-12">
                    <button class="btn btn-primary">Submit</button>
                    <button type="button" id="reset" class="btn btn-primary" name="btn"
                        value="Reset Form">Reset</button>


                    @if (request()->route()->getName() !== 'subjectmaster')
                        <a href="{{ url('subjectmaster') }}" class="btn btn-primary">Add New</a>
                    @endif





                </div>
            </div>
            </form><br>
        </div>
         @if (empty($subject_s))
        <div class="breadcrumb">
            <h1 class="me-2">List of saved subjects :-</h1>
        </div>
        <div class="separator-breadcrumb border-top"></div>

        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card text-start">
                    <div class="card-body">
                        <!-- <h4 class="card-title mb-3 text-end"><a href="{{ url('add-student-registrations') }}"><button class="btn btn-outline-primary" type="button">Create Registration</button></a></h4> -->
                        <div class="table-responsive">
                            <table class="display table table-striped table-bordered" id="zero_configuration_table"
                                style="width: 100%">
                                <thead>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Subject Name</th>
                                        <th>Classes Name</th>
                                        <th>Subject Type</th>
                                        <th>Is Practical Type</th>
                                        <th>Special Subject Formatting</th>
                                        <th>Evaluation</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody><?php $i = 1; ?>
                                    @if (!empty($datas))
                                        @foreach ($datas as $data)
                                            <tr>
                                                <td>{{ $i }}</td>
                                                <td>{{ $data->subject_name }}</td>
                                                <td>@foreach ($data->Classname as $class)
                                                     @php
                                                        $streamName = \App\Models\Stream::find($class->pivot->stream_id)?->streams;
                                                    @endphp
                                                    {{ $class->class_name }}{{ $class->section_name ? ' - ' . $class->section_name : '' }}@if($streamName)({{ $streamName }})@endif,
                                                @endforeach</td>
                                                <td>{{ $data->subject_type }}</td>
                                                <td>{{ $data->practical }}</td>
                                                <td>{{ !empty($data->do_not_print_in_main_scholastic_area) ? 'Yes' : 'No' }}</td>
                                                <td>{{ $data->evaluation }}</td>
                                                <td>
                                                    <a class="btn btn-primary m-1"
                                                        href="{{ url('view-subject') . '/' . $data->id }}">Edit</a>
                                                    <!-- <form id="deleteForm" method="post" action="{{ url('delete-subject') }}">
                                      @csrf
                                      <input type="hidden" name="table_name" value="natureofwork">
                                      <input type="hidden" name="delete_id" value="{{ $data->id }}">
                                      <button type="submit" class="btn btn-danger " onclick="confirmDelete(event)">Delete</button>
                                  </form> -->
                                                    <?php $a = 'subjectmaster' . '-' . $data->id; ?>
                                                    <a class="btn btn-raised ripple btn-danger m-1"
                                                        href="{{ url('delete-subject') . '/' . $a }}"
                                                        onclick="confirmDelete(event)">Delete</a>
                                                </td>
                                            </tr>
                                            <?php $i++; ?>
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Sr.</th>
                                        <th>Subject Name</th>
                                        <th>Classes Name</th>
                                        <th>Subject Type</th>
                                        <th>Is Practical Type</th>
                                        <th>Special Subject Formatting</th>
                                        <th>Evaluation</th>
                                        <th>Action</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <!-- end of main-content -->
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
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


        document.addEventListener('DOMContentLoaded', function() {
            $("#reset").on("click", function() {
                $("#subject_name").val("");
                $("input[type='checkbox']").prop('checked', false);
                $("input[type='radio']").prop('checked', false);
                $("#classes").val(null).trigger('change');
            });

        })
    </script>


@endsection
