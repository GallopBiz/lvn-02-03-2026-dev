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
                            <label for="combination_name_dropdown">Bulk Combination</label>
                            <select name="combination_name_dropdown" class="form-control" id="combination_name_dropdown" @if (!empty($stream_master)) disabled @endif>
                                <option value="">-- Select combination for selected students --</option>
                                @foreach ($comlist as $com)
                                    <option value="{{ $com->id }}">{{ $com->combination_name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select students below, then click bulk assign.</small>
                        </div>



                        <div class="col-md-3 form-group mb-3">
                            <br>
                            <button type="button" class="btn btn-success bulk-assign-comb-btn" disabled @if (!empty($stream_master)) disabled @endif>
                                Bulk Assign
                            </button>
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
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3 class="mb-0">Students Details</h3>
                        <span class="badge badge-primary" id="selected_students_count">0 selected</span>
                    </div>
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
                                                        <td>{{ $stream_master->Student->student_name ?? 'N/A' }}</td>
                                                        <td>
                                                            <select name="combination_name" class="form-control student-combination" data-student-id="{{ $stream_master->Student->id ?? '' }}">
                                                                <option value="">-- Please select --</option>
                                                                @foreach ($comlist as $com)
                                                                    <option value="{{ $com->id }}" {{ $stream_master->assign_this_combtoall == $com->id ? 'selected' : '' }}>{{ $com->combination_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-primary assign-comb-btn" data-studentclass="{{ $stream_master->class_name }}" data-student-id="{{ $stream_master->Student->id ?? '' }}">Assign</button>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th><input type="checkbox" name="check_all" id="check_all_footer"></th>
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
                                    <div class="row mb-3">
                                        <div class="col-md-3 form-group mb-2">
                                            <label for="saved_filter_class">Class</label>
                                            <select id="saved_filter_class" class="form-control">
                                                <option value="">All Classes</option>
                                                @foreach ($classlist as $each)
                                                    <option value="{{ $each->class_name }}">{{ $each->class_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 form-group mb-2">
                                            <label for="saved_filter_section">Section</label>
                                            <select id="saved_filter_section" class="form-control">
                                                <option value="">All Sections</option>
                                                @foreach ($classes->pluck('section_name')->filter()->unique()->values() as $section)
                                                    <option value="{{ $section }}">{{ $section }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 form-group mb-2">
                                            <label for="saved_filter_combination">Combination</label>
                                            <select id="saved_filter_combination" class="form-control">
                                                <option value="">All Combinations</option>
                                                @foreach ($comlist as $com)
                                                    <option value="{{ $com->id }}">{{ $com->combination_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 form-group mb-2">
                                            <label>&nbsp;</label>
                                            <div class="d-flex">
                                                <button type="button" id="reset_saved_assignment_filters" class="btn btn-secondary mr-2">Reset</button>
                                                <button type="button" id="export_saved_assignments_excel" class="btn btn-success">Export Excel</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <span class="badge badge-info" id="saved_assignment_result_count">0 records</span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="display table table-striped table-bordered"
                                            id="zero_configuration_table" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Sr. No</th>
                                                    <th>Student Name</th>
                                                    <th>Class</th>
                                                    <th>Scholar No.</th>
                                                    <th>Section</th>
                                                    <th>Assigned Combination</th>
                                                    <th>Action</th>

                                                </tr>
                                            </thead>
                                            <tbody>


                                                @if (!empty($subjectsassign))
                                                    @foreach ($subjectsassign as $streams)
                                                        <tr class="saved-assignment-row"
                                                            data-class="{{ $streams->class_name }}"
                                                            data-section="{{ $streams->section_name }}"
                                                            data-combination="{{ $streams->assign_this_combtoall }}">
                                                            <td>{{ ++$i }}</td>
                                                            <td>{{ $streams->Student->student_name ?? 'N/A' }}</td>
                                                            <td>{{ $streams->class_name }}</td>
                                                            <td>{{ $streams->Student->scholar_no ?? 'N/A' }}</td>
                                                            <td>{{ $streams->section_name ?? 'N/A' }}</td>
                                                            <td>{{ $streams->combination->combination_name ?? 'N/A' }}</td>

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
                                                        <td colspan="7" class="text-center">No Data Found</td>
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
        const bulkAssignLocked = @json(!empty($stream_master));

        function updateBulkAssignState() {
            const selectedCount = $('input[name="check_bulk"]:checked').length;
            const hasCombination = $('#combination_name_dropdown').val() !== '';

            $('#selected_students_count').text(selectedCount + ' selected');
            $('.bulk-assign-comb-btn').prop('disabled', bulkAssignLocked || selectedCount === 0 || !hasCombination);

            const totalRows = $('input[name="check_bulk"]').length;
            $('#check_all, #check_all_footer').prop('checked', totalRows > 0 && selectedCount === totalRows);
        }

        // Toggle all checkboxes when "check_all" is clicked
        $(document).on('change', '#check_all, #check_all_footer', function () {
            const isChecked = $(this).is(':checked');
            const combination = $('#combination_name_dropdown').val();

            $('input[name="check_bulk"]').prop('checked', isChecked);

            if (isChecked && combination) {
                $('input[name="check_bulk"]').closest('tr').find('.student-combination').val(combination);
            }

            updateBulkAssignState();
        });

        $(document).on('change', 'input[name="check_bulk"]', function () {
            const combination = $('#combination_name_dropdown').val();

            if ($(this).is(':checked') && combination) {
                $(this).closest('tr').find('.student-combination').val(combination);
            }

            updateBulkAssignState();
        });

        $(document).on('change', '#combination_name_dropdown', function () {
            const combination = $(this).val();

            if (combination) {
                $('input[name="check_bulk"]:checked').each(function () {
                    $(this).closest('tr').find('.student-combination').val(combination);
                });
            }

            updateBulkAssignState();
        });
    </script>
    <script>
        $(document).on('click', '.bulk-assign-comb-btn', function () {
            const button = $(this);
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
                    section_name: $('#section_name').val(),
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
                    button.prop('disabled', true).text('Assigning...');

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
                            button.text('Bulk Assign');
                            updateBulkAssignState();
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
                    section_name: $('#section_name').val(),
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


            function renderStudentCombinationRows(rows) {
                const tableSelector = '#deafult_ordering_table_wrapper';
                const tableBody = $('#student_combination_table');

                if ($.fn.DataTable && $.fn.DataTable.isDataTable(tableSelector)) {
                    $(tableSelector).DataTable().destroy();
                }

                tableBody.html(rows.join(''));
            }

            function emptyStudentCombinationRow(message) {
                return `
                    <tr>
                        <td colspan="6" class="text-center">${message}</td>
                    </tr>
                `;
            }

            $('#show-students-button').click(function() {
                const button = $(this);
                var selectedClass = $('#class_name').val();
                var selectedSection = $('#section_name').val();

                if (!selectedClass) {
                    renderStudentCombinationRows([emptyStudentCombinationRow('Please select a class first.')]);
                    return;
                }

                $.ajax({
                    url: "{{ url('find-student_combination') }}",
                    type: 'POST',
                    beforeSend: function() {
                        button.prop('disabled', true).text('Loading...');
                        renderStudentCombinationRows([emptyStudentCombinationRow('Loading students...')]);
                        updateBulkAssignState();
                    },
                    data: {
                        _token: '{{ csrf_token() }}',
                        class_name: selectedClass,
                        section_name: selectedSection
                    },
                    success: function(response) {

                        let data = Array.isArray(response) ? response : [];
                        let rows = [];

                        if (data.length === 0) {
                            renderStudentCombinationRows([emptyStudentCombinationRow('No unassigned students found for selected class/section.')]);
                            updateBulkAssignState();
                            return;
                        }

                        for (var i = 0; i < data.length; i++) {
                            var student_name = data[i].student_name;
                            var student_id = data[i].id;
                            var class_name = data[i].class_name;
                            var section_name = data[i].section_name;

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
                                        <td><input type="checkbox" name="check_bulk"></td>
                                        <td>${i+1}</td>
                                        <td>${class_name}</td>
                                        <td>${student_name}</td>
                                        <td>${dropdownStr}</td>
                                        <td><button type="button" class="btn btn-primary assign-comb-btn" data-studentsection="${section_name}" data-studentclass="${class_name}" data-student-id="${student_id}">Assign</button></td>

                                    </tr>
                                `

                            rows.push(tRow);
                        }
                        renderStudentCombinationRows(rows);
                        updateBulkAssignState();
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Unable to fetch students. Please try again.';
                        renderStudentCombinationRows([emptyStudentCombinationRow(message)]);
                        updateBulkAssignState();
                    },
                    complete: function() {
                        button.prop('disabled', false).text('Show Students');
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
                select.options[i].innerText = select.options[i].innerText.toUpperCase();
            }
        });
    </script>
    <script>
        let savedAssignmentTable = null;
        const savedAssignmentFilterName = 'saved-assignment-filters';

        function normalizeFilterValue(value) {
            return (value || '').toString().trim().toLowerCase();
        }

        function bindSavedAssignmentTableEvents(table) {
            table.off('draw.savedAssignment');
            table.on('draw.savedAssignment', function () {
                const info = table.page.info();

                table.rows({ page: 'current', search: 'applied' }).every(function (rowIndex, tableLoop, rowLoop) {
                    $(this.node()).find('td:first').text(info.start + rowLoop + 1);
                });

                updateSavedAssignmentResultCount();
            });
        }

        function registerSavedAssignmentFilter() {
            if (!$.fn.DataTable || $.fn.dataTable.ext.search.some(filter => filter.filterName === savedAssignmentFilterName)) {
                return;
            }

            const savedAssignmentFilter = function(settings, data, dataIndex) {
                if (settings.nTable.id !== 'zero_configuration_table') {
                    return true;
                }

                const row = $(settings.aoData[dataIndex].nTr);
                const selectedClass = normalizeFilterValue($('#saved_filter_class').val());
                const selectedSection = normalizeFilterValue($('#saved_filter_section').val());
                const selectedCombination = normalizeFilterValue($('#saved_filter_combination').val());
                const rowClass = normalizeFilterValue(row.data('class'));
                const rowSection = normalizeFilterValue(row.data('section'));
                const rowCombination = normalizeFilterValue(row.data('combination'));

                const matchesClass = !selectedClass || rowClass === selectedClass;
                const matchesSection = !selectedSection || rowSection === selectedSection;
                const matchesCombination = !selectedCombination || rowCombination === selectedCombination;

                return matchesClass && matchesSection && matchesCombination;
            };

            savedAssignmentFilter.filterName = savedAssignmentFilterName;
            $.fn.dataTable.ext.search.push(savedAssignmentFilter);
        }

        function getSavedAssignmentTable() {
            const tableSelector = '#zero_configuration_table';

            if ($.fn.DataTable && $.fn.DataTable.isDataTable(tableSelector)) {
                savedAssignmentTable = $(tableSelector).DataTable();
                bindSavedAssignmentTableEvents(savedAssignmentTable);
                return savedAssignmentTable;
            }

            if ($.fn.DataTable) {
                savedAssignmentTable = $(tableSelector).DataTable({
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                    ordering: true,
                    searching: true,
                    columnDefs: [
                        { orderable: false, targets: 6 }
                    ],
                    language: {
                        zeroRecords: 'No saved assignments found for selected filters.'
                    }
                });
                bindSavedAssignmentTableEvents(savedAssignmentTable);
            }

            return savedAssignmentTable;
        }

        function updateSavedAssignmentResultCount() {
            const table = getSavedAssignmentTable();
            let count = 0;

            if (table) {
                count = table.rows({ search: 'applied' }).count();
            } else {
                count = $('.saved-assignment-row:visible').length;
            }

            $('#saved_assignment_result_count').text(count + (count === 1 ? ' record' : ' records'));
        }

        function applySavedAssignmentFilters() {
            const table = getSavedAssignmentTable();

            if (table) {
                table.draw();
            } else {
                const selectedClass = normalizeFilterValue($('#saved_filter_class').val());
                const selectedSection = normalizeFilterValue($('#saved_filter_section').val());
                const selectedCombination = normalizeFilterValue($('#saved_filter_combination').val());
                let visibleCount = 0;

                $('.saved-assignment-row').each(function () {
                    const row = $(this);
                    const rowClass = normalizeFilterValue(row.data('class'));
                    const rowSection = normalizeFilterValue(row.data('section'));
                    const rowCombination = normalizeFilterValue(row.data('combination'));
                    const isVisible = (!selectedClass || rowClass === selectedClass)
                        && (!selectedSection || rowSection === selectedSection)
                        && (!selectedCombination || rowCombination === selectedCombination);

                    row.toggle(isVisible);

                    if (isVisible) {
                        visibleCount++;
                        row.find('td:first').text(visibleCount);
                    }
                });
            }

            updateSavedAssignmentResultCount();
        }

        function exportSavedAssignmentsExcel() {
            applySavedAssignmentFilters();

            const rows = [];
            const table = getSavedAssignmentTable();
            rows.push(['Sr. No', 'Student Name', 'Class', 'Scholar No.', 'Section', 'Assigned Combination']);

            const filteredRows = table
                ? $(table.rows({ search: 'applied' }).nodes())
                : $('.saved-assignment-row:visible');

            filteredRows.each(function (index) {
                const cells = $(this).find('td');
                rows.push([
                    index + 1,
                    $(cells[1]).text().trim(),
                    $(cells[2]).text().trim(),
                    $(cells[3]).text().trim(),
                    $(cells[4]).text().trim(),
                    $(cells[5]).text().trim()
                ]);
            });

            if (rows.length === 1) {
                toastr.warning('No saved assignments to export.');
                return;
            }

            const htmlRows = rows.map(function (row) {
                return '<tr>' + row.map(function (cell) {
                    return '<td>' + $('<div>').text(cell).html() + '</td>';
                }).join('') + '</tr>';
            }).join('');

            const excelHtml = `
                <html>
                    <head><meta charset="utf-8"></head>
                    <body>
                        <table>${htmlRows}</table>
                    </body>
                </html>
            `;
            const blob = new Blob([excelHtml], { type: 'application/vnd.ms-excel' });
            const link = document.createElement('a');
            const date = new Date().toISOString().slice(0, 10);

            link.href = URL.createObjectURL(blob);
            link.download = 'saved-subject-assignments-' + date + '.xls';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        }

        $(document).ready(function () {
            registerSavedAssignmentFilter();
            getSavedAssignmentTable();
            applySavedAssignmentFilters();
        });

        $(document).on('change', '#saved_filter_class, #saved_filter_section, #saved_filter_combination', applySavedAssignmentFilters);

        $(document).on('click', '#reset_saved_assignment_filters', function () {
            $('#saved_filter_class, #saved_filter_section, #saved_filter_combination').val('');
            applySavedAssignmentFilters();
        });

        $(document).on('click', '#export_saved_assignments_excel', exportSavedAssignmentsExcel);
    </script>

@endsection
