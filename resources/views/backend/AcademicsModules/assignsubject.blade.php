@extends('backend.layouts.main')
@section('main-container')

    @php
        $i = 0;
    @endphp
    <div class="main-content pt-4">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <div class="row">
            <div class="col-md-12 form-group mb-3">
                <div class="form_section1_div">
                    <div class="breadcrumb">
                        <h1 class="me-2">Subject Combination Assign to Student</h1>
                    </div>

                    <div class="separator-breadcrumb border-top"></div>
                    @if (!empty($stream_master))
                        <form id="progress-form" class="p-4 progress-form" action="{{ url('store-AssignSubject') }}"
                            novalidate method="post">
                            <input type="hidden"
                                @if (!empty($stream_master))
                                    value=" {{ $stream_master->id }}"
                                @else
                                    value="" @endif
                                name="id">
                    @else
                            <form id="progress-form" class="p-4 progress-form" action="{{ url('save-AssignSubject') }}"
                                novalidate method="post">
                    @endif

                    @csrf
                    <div class="row">


                        <div class="col-md-3 form-group mb-3">
                            <label for="firstName1">Class Name</label>
                            <select id="class_name" class="form-control" name="class_name" autocomplete="" required @if (!empty($stream_master)) disabled @endif>
                                @if (!empty($stream_master))
                                    <option value="" disabled hidden>Please select</option>
                                    @foreach ($classlist as $each)
                                        <option value="{{ $each->class_name }}"
                                            {{ $stream_master->class_name == $each->class_name ? 'selected' : '' }}>
                                            {{ $each->class_name }}</option>
                                    @endforeach
                                @else
                                    <option value="" disabled selected>Please select</option>
                                    @foreach($classNames as $class)
                                        <option value="{{ $class->class_name }}">{{ $class->class_name }}</option>
                                    @endforeach
                                @endif
                                {{-- @foreach (config('global.class_name') as $each)
                       <option value="{{$each}}">{{$each}}</option>
                        @endforeach --}}
                            </select>
                            <span class="classname_msg validation_err"></span>
                        </div>


                        <div class="col-md-3 form-group mb-3">
                            <label for="section_name">Section</label>
                            <select id="section_name" class="form-control" name="section_name"
                                autocomplete="shipping address-level1" required @if (!empty($stream_master)) disabled @endif>
                                <?php //print_r($sectionname);die();
                                ?>

                                @if (!empty($stream_master))
                                    <option value="{{ $stream_master->section_name }}" selected>
                                        {{ $stream_master->section_name }}</option>
                                @else
                                @endif
                            </select>
                        </div>


                        <div class="col-md-3 form-group mb-3">
                            <br>
                            <button @if (!empty($stream_master)) disabled @endif type="button" id="show-students-button" class="btn btn-primary">Show Students</button>
                        </div>



                        <div class="col-md-4 form-group mb-3">
                            <label for="combination_name_dropdown">Assign this comb. to all:-</label>
                            <select name="combination_name_dropdown" class="form-control" id="combination_name_dropdown" @if (!empty($stream_master)) disabled @endif>
                                <option value="">-- Bulk Assign to All --</option>
                                @foreach ($comlist as $com)
                                    <option value="{{ $com->id }}">{{ $com->combination_name }}</option>
                                @endforeach
                            </select>
                        </div>



                        <div class="col-md-3 form-group mb-3">
                            <br>
                            <button type="button" class="btn btn-primary bulk-assign-comb-btn" @if (!empty($stream_master)) disabled @endif>Assigns</button>
                            {{-- <button type="reset" class="btn btn-primary" name="btn" value="Reset Form">Reset</button> --}}
                            {{-- @if (request()->route()->getName() !== 'AssignSubject')
                                <a href="{{ url('AssignSubject') }}" class="btn btn-primary">Add New</a>
                            @endif --}}

                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="row">
                <div class="col-md-12 mb-4">
                    <h3>Students Details</h3>
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <div class="card text-start">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="display table table-striped table-bordered"
                                            id="deafult_ordering_table_wrapper" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" name="check_all" id="check_all"></th>
                                                    <th>Sr.</th>
                                                    <th>Class</th>
                                                    <th>Name</th>
                                                    <th>Assign Combination</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="student_combination_table">
                                                @if (!empty($stream_master))
                                                    <tr>
                                                        <td><input type="checkbox" name="check_bulk" id="check_bulk"></td>
                                                        <td>1</td>
                                                        <td>{{ $stream_master->class_name }}</td>
                                                        <td>{{ $stream_master->Student->student_name }}</td>
                                                        <td>
                                                            <select name="combination_name" class="form-control student-combination" data-student-id="{{ $stream_master->Student->id }}">
                                                                <option value="">-- Please select --</option>
                                                                @foreach ($comlist as $com)
                                                                    <option value="{{ $com->id }}" {{ $stream_master->assign_this_combtoall == $com->id ? 'selected' : '' }}>{{ $com->combination_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-primary assign-comb-btn" data-studentclass="{{ $stream_master->class_name }}" data-student-id="{{ $stream_master->Student->id }}">Assign</button>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th><input type="checkbox" name="check_all" id="check_all"></th>
                                                    <th>Sr.</th>
                                                    <th>Class</th>
                                                    <th>Name</th>
                                                    <th>Assign Combination</th>
                                                    <th>Action</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </form>

                        <div class="col-md-12 mb-4">
                            <div class="breadcrumb">
                                <h1 class="me-2">List of Saved Assignment :-</h1>
                            </div>
                            <div class="separator-breadcrumb border-top"></div>


                            <div class="card text-start">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="display table table-striped table-bordered"
                                            id="zero_configuration_table" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Sr.</th>
                                                    <th>Class</th>
                                                    <th>Student Name </th>
                                                    <th>Assign Com.</th>
                                                    <th>Action </th>

                                                </tr>
                                            </thead>
                                            <tbody>


                                                @if (!empty($subjectsassign))
                                                    @foreach ($subjectsassign as $streams)
                                                        <tr>
                                                            <td>{{ ++$i }}</td>
                                                            <td>{{ $streams->class_name }}</td>
                                                            <td>{{ $streams->Student->student_name }}</td>
                                                            <td>{{ $streams->combination->combination_name }}</td>

                                                            <td class='d-flex'>
                                                                <a class="btn btn-primary m-1" href="{{ url('view-AssignSubject') .'/'.$streams->id}}">Edit</a>
                                                                <!-- <form id="deleteForm" method="post" action="{{ url('delete-streammaster') }}">
                                        @csrf
                                        <input type="hidden" name="table_name" value="streams">
                                        <input type="hidden" name="delete_id" value="{{ $streams->id }}">
                                        <button type="button" class="btn btn-danger m-1" onclick="confirmDelete(event)">Delete</button>
                                    </form> -->
                                                                <?php $a = 'subject_assign_student' . '-' . $streams->id; ?>
                                                                <a class="btn btn-raised ripple btn-danger m-1"
                                                                    href="{{ url('delete-AssignSubject') . '/' . $a }}"
                                                                    onclick="confirmDelete(event)">Delete</a>
                                                            </td>
                                                        </tr>
                                                        <!-- </?php $i++; ?> -->
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="9" class="text-center">No Data Found</td>
                                                    </tr>
                                                @endif
                                            </tbody>

                                            <tfoot>

                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end of main-content -->
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        // Toggle all checkboxes when "check_all" is clicked
        $(document).on('change', '#check_all', function () {
            const isChecked = $(this).is(':checked');
            $('input[name="check_bulk"]').prop('checked', isChecked);
        });
    </script>
    <script>
        $(document).on('click', '.bulk-assign-comb-btn', function () {
            const token = $('meta[name="csrf-token"]').attr('content');

            const selected = [];
            $('input[name="check_bulk"]:checked').each(function () {
                const row = $(this).closest('tr');
                const studentId = row.find('.assign-comb-btn').data('student-id');
                const studentClass = row.find('.assign-comb-btn').data('studentclass');
                const combination = document.getElementById('combination_name_dropdown').value;

                if (!combination) {
                    toastr.error(`Please select combination for student ID ${studentId}`);
                    return;
                }

                selected.push({
                    student_id: studentId,
                    class_name: studentClass,
                    combination_name: combination
                });
            });

            if (selected.length === 0) {
                toastr.warning("No students selected or combinations missing.");
                return;
            }

            Swal.fire({
                title: 'Confirm Assignment',
                text: `You are about to assign combinations to ${selected.length} students.`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Assign'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('store-AssignSubject') }}",
                        type: 'POST',
                        data: {
                            _token: token,
                            bulk_assign: true,
                            assignments: selected
                        },
                        success: function (response) {
                            toastr.success("Combinations assigned successfully.");
                            setTimeout(() => location.reload(), 1500);
                        },
                        error: function (xhr) {
                            toastr.error("Bulk assignment failed: " + xhr.responseJSON?.message || "Unknown error");
                        }
                    });
                }
            });
        });
    </script>

    <script>
        $(document).on('click', '.assign-comb-btn', function () {
            const studentId = $(this).data('student-id');
            const studentclass = $(this).data('studentclass');
            const combination = $(`.student-combination[data-student-id="${studentId}"]`).val();
            const token = $('meta[name="csrf-token"]').attr('content');

            if (!combination) {
                toastr.error("Please select a combination.");
                return;
            }

            $.ajax({
                url: "{{ url('store-AssignSubject') }}", // You need to define this route in web.php
                type: 'POST',
                data: {
                    _token: token,
                    studentclass: studentclass,
                    student_id: studentId,
                    combination_name: combination
                },
                success: function (response) {
                    toastr.success("Assigned successfully");
                    //alert("Assigned successfully");
                },
                error: function (xhr) {
                    toastr.error("Assignment failed: " + xhr.responseJSON?.message || "Unknown error");
                    //alert("Assignment failed: " + xhr.responseJSON?.message || "Unknown error");
                }
            });
        });

    </script>
    <script>
        /* $(document).on('click', '.bulk-assign-comb-btn', function () {
            const class_name = document.getElementById('class_name').value;
            const combination = document.getElementById('combination_name_dropdown').value;
            const token = $('meta[name="csrf-token"]').attr('content');
            if (!class_name) {
                toastr.error("Please select a Class first.");
                return;
            }
            if (!combination) {
                toastr.error("Please select a combination.");
                return;
            }
            $.ajax({
                url: "{{ url('store-AssignSubject') }}",
                type: 'POST',
                data: {
                    _token: token,
                    bulkupdate: "bulkupdate",
                    class_name: class_name,
                    combination: combination
                },
                success: function (response) {
                    toastr.success("Assigned successfully");
                    setTimeout(() => {
                        window.location.href = "{{ route('AssignSubject') }}";
                    }, 1500);
                },
                error: function (xhr) {
                    toastr.error("Assignment failed: " + xhr.responseJSON?.message || "Unknown error");
                }
            });
        }); */

    </script>
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

        document.addEventListener("DOMContentLoaded", function() {


            $('#show-students-button').click(function() {
                var selectedClass = $('#class_name').val();
                var selectedSection = $('#section_name').val();


                $.ajax({
                    url: "{{ url('find-student_combination') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        class_name: selectedClass,
                        section_name: selectedSection
                    },
                    success: function(response) {

                        let data = response;
                        $('#student_combination_table').html('');
                        for (var i = 0; i < data.length; i++) {
                            var student_name = data[i].student_name;
                            var student_id = data[i].id;
                            var class_name = data[i].class_name;

                            let dropdownStr = `
                                <select name="combination_name" class="form-control student-combination" data-student-id="${student_id}">
                                    <option value="">-- Please select --</option>
                                    @foreach ($comlist as $com)
                                        <option value="{{ $com->id }}">{{ $com->combination_name }}</option>
                                    @endforeach
                                </select>

                                `

                            let tRow = `
                                <tr>
                                        <td><input type="checkbox" name="check_bulk" id="check_bulk"></td>
                                        <td>${i+1}</td>
                                        <td>${class_name}</td>
                                        <td>${student_name}</td>
                                        <td>${dropdownStr}</td>
                                        <td><button type="button" class="btn btn-primary assign-comb-btn" data-studentclass="${class_name}" data-student-id="${student_id}">Assign</button></td>

                                    </tr>
                                `

                            $('#student_combination_table').append(tRow);
                        }
                    }
                });
            });

        })
    </script>
    <script>
        const allClasses = @json($classes);

        document.getElementById('class_name').addEventListener('change', function () {
            const selectedClass = this.value;
            const sectionDropdown = document.getElementById('section_name');
            sectionDropdown.innerHTML = '<option value="">-- Select Section --</option>';

            const sections = allClasses
                .filter(c => c.class_name === selectedClass)
                .map(c => c.section_name);

            [...new Set(sections)].forEach(section => {
                const opt = document.createElement('option');
                opt.value = section;
                opt.textContent = section;
                sectionDropdown.appendChild(opt);
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var select = document.getElementById('combination_name_dropdown');
            for (var i = 0; i < select.options.length; i++) {
                select.options[i].innerText = capitalizeFirstLetter(select.options[i].innerText);
            }
        });

        function capitalizeFirstLetter(str) {
            var words = str.toLowerCase().split(' ');
            for (var i = 0; i < words.length; i++) {
                words[i] = words[i].charAt(0).toUpperCase() + words[i].substring(1);
            }
            return words.join(' ');
        }
    </script>

@endsection
