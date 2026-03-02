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
                        <h1 class="me-2">Section Assign to Student</h1>
                    </div>
                    <div class="separator-breadcrumb border-top"></div>
                    @if(empty($student))
                        <form id="progress-form" class="p-4 progress-form" action="{{ url('save-AssignSubject') }}" novalidate method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-3 form-group mb-3">
                                <label for="firstName1">Class Name</label>
                                <select id="class_name" class="form-control" name="class_name" autocomplete="" required>
                                <option value="" disabled selected>Please select</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->class_name }}">{{ $class->class_name }}</option>
                                    @endforeach
                                </select>
                                <span class="classname_msg validation_err"></span>
                            </div>

                            <div class="col-md-3 form-group mb-3">
                                <br>
                                <button type="button" id="show-students-button" class="btn btn-primary">Fetch Students</button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @if(empty($students))
            <div class="col-md-12 mb-3 form-group ">
                <div class="col-md-3 form-group mb-3">
                    <label for="section_name">Bulk Assign Section</label>
                    <select id="bulk_section_name" class="form-control" name="section_name" required>
                            <option value="">Please select section </option>
                            <option value="Aryabhatta">Aryabhatta</option>
                            <option value="Kautilya">Kautilya</option>
                            <option value="Ramanujan">Ramanujan</option>
                    </select>
                </div>
                <div class="col-md-3 form-group mb-3">
                            <button type="button" class="btn btn-primary bulk-assign-comb-btn">Bulk Assigns</button>
                </div>
            </div>
            @endif
        </div>
        <div class="row" >
            <div class="col-md-12">
                <div class="row" id="section_summary"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
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
                                                <th>Assign</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="student_list_table">
                                            @if(!empty($students))
                                                @foreach($students as $student)
                                                    <tr>
                                                        <td><input type="checkbox" name="check_bulk" id="check_bulk"></td>
                                                        <td>1</td>
                                                        <td>{{ $student->class_name }}</td>
                                                        <td>{{ $student->student_name }}</td>
                                                        <td>
                                                            <select name="section_name" class="form-control section_name" data-student-id="{{ $student->id }}">
                                                                <option value="">Please select section </option>
                                                                <option value="Aryabhatta" {{ $student->section->section_name === 'Aryabhatta' ? 'selected' : ''}}>Aryabhatta</option>
                                                                <option value="Kautilya" {{ $student->section->section_name === 'Kautilya' ? 'selected' : ''}}>Kautilya</option>
                                                                <option value="Ramanujan" {{ $student->section->section_name === 'Ramanujan' ? 'selected' : ''}}>Ramanujan</option>
                                                            </select>
                                                        </td>
                                                        <td><button type="button" class="btn btn-primary update-comb-btn" data-studentclass="{{ $student->class_name }}" data-student-id="{{ $student->id }}">Update</button></td>

                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="6" class="text-center"> No Data Found</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if(empty($students))
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="breadcrumb">
                    <h1 class="me-2">List of Student Assigned Section :-</h1>
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
                                        <th>Aryabhatta</th>
                                        <th>Kautilya</th>
                                        <th>Ramanujan</th>
                                        <th>N/A</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (!empty($classSectionCounts))
                                        @foreach ($classSectionCounts as $class => $sections)
                                            <tr>
                                                <td>{{ ++$i }}</td>
                                                <td>{{ $class }}</td>
                                                <td> Students: {{ $sections['Aryabhatta'] ?? "0" }} <a class="btn btn-primary m-1" href="{{ url('view-AssignSection/'. $class) . '?section=Aryabhatta'}}">Edit</a></td>
                                                <td> Students: {{ $sections['Kautilya'] ?? "0" }} <a class="btn btn-primary m-1" href="{{ url('view-AssignSection/'. $class) . '?section=Kautilya'}}">Edit</a></td>
                                                <td> Students: {{ $sections['Ramanujan'] ?? "0" }} <a class="btn btn-primary m-1" href="{{ url('view-AssignSection/'. $class) . '?section=Ramanujan'}}">Edit</a></td>
                                                <td> Students: {{ $sections['Uknown'] ?? "0" }} </td>
                                                @foreach ($sections as $section => $count)

                                                    {{-- echo "  Section: $section → Students: $count\n"; --}}
                                                @endforeach
                                                {{-- <td>{{ $student->student_name }}</td>
                                                <td>{{ $student->section->section_name }}</td> --}}

                                                {{-- <td class='d-flex'>
                                                    <a class="btn btn-primary m-1" href="{{ url('view-AssignSection') .'/'.$student->id}}">Edit</a>
                                                </td> --}}
                                            </tr>
                                        @endforeach
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
        @endif

        <!-- end of main-content -->
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {


            $('#show-students-button').click(function() {
                var selectedClass = $('#class_name').val();
                $.ajax({
                    url: "{{ url('fetch-section-student-data') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        class_name: selectedClass,
                    },
                    success: function(response) {
                        let data = response['studentds'];
                        $('#student_list_table').html('');
                        for (var i = 0; i < data.length; i++) {
                            var student_name = data[i].student_name;
                            var student_id = data[i].id;
                            var class_name = data[i].class_name;
                            let section_name = '';
                            try {
                                let json = JSON.parse(data[i].json_str);
                                section_name = json.section_name ?? '';
                            } catch (e) {
                                console.error('Invalid JSON for student ID:', student_id);
                            }
                            let dropdownStr = `
                                <select name="section_name" class="form-control section_name" data-student-id="${student_id}">
                                    <option value="">Please select section </option>
                                    <option value="Aryabhatta" ${section_name === 'Aryabhatta' ? 'selected' : ''}>Aryabhatta</option>
                                    <option value="Kautilya" ${section_name === 'Kautilya' ? 'selected' : ''}>Kautilya</option>
                                    <option value="Ramanujan" ${section_name === 'Ramanujan' ? 'selected' : ''}>Ramanujan</option>
                                </select>
                            `;
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
                            $('#student_list_table').append(tRow);
                        }
                        let sectionCounts = response['sectionCounts'];
                        let summaryHtml = '<h3>Section-wise Student Count</h3>';

                        for (let section in sectionCounts) {
                            summaryHtml += `
                                <div class="col-md-2 mx-1 card text-center">
                                    <div class="card-body"><h4>${section}</h4>
                                        <h4>${sectionCounts[section]}</h4></div>
                                </div>
                            `;
                        }


                        //$('#section_summary').html(summaryHtml);

                    }
                });
            });

        })
    </script>
    <script>
        $(document).on('click', '.assign-comb-btn', function () {
            const studentId = $(this).data('student-id');
            const studentclass = $(this).data('studentclass');
            const section = $(`.section_name[data-student-id="${studentId}"]`).val();
            const token = $('meta[name="csrf-token"]').attr('content');

            if (!section) {
                toastr.error("Please select a section.");
                return;
            }

            $.ajax({
                url: "{{ url('update-student-section') }}", // You need to define this route in web.php
                type: 'POST',
                data: {
                    _token: token,
                    studentclass: studentclass,
                    student_id: studentId,
                    section: section
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
        // update section
        $(document).on('click', '.update-comb-btn', function () {
            const studentId = $(this).data('student-id');
            const studentclass = $(this).data('studentclass');
            const section = $(`.section_name[data-student-id="${studentId}"]`).val();
            const token = $('meta[name="csrf-token"]').attr('content');

            if (!section) {
                toastr.error("Please select a section.");
                return;
            }

            $.ajax({
                url: "{{ url('update-student-section') }}", // You need to define this route in web.php
                type: 'POST',
                data: {
                    _token: token,
                    studentclass: studentclass,
                    student_id: studentId,
                    section: section
                },
                success: function (response) {
                    toastr.success("Section Update successfully");
                    setTimeout(() => {
                        window.location.href = '{{ url('sectionAssign') }}';
                    }, 1500);

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
                const section = document.getElementById('bulk_section_name').value;

                if (!section) {
                    toastr.error("Please select a section.");
                    return;
                }

                selected.push({
                    student_id: studentId,
                    studentclass: studentClass,
                    section: section
                });
            });

            if (selected.length === 0) {
                toastr.warning("No students selected");
                return;
            }

            Swal.fire({
                title: 'Confirm Assignment',
                text: `You are about to assign Section to ${selected.length} students.`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Assign'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('update-student-section') }}",
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
@endsection
