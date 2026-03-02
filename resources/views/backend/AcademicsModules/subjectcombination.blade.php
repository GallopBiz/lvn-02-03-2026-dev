@extends('backend.layouts.main')
@section('main-container')
<style>
    .uperletter {
        text-transform: capitalize;
    }
</style>
@php
$i = 0;
@endphp

<style>
    .serial-number {
        min-width: 40px;
        /* Adjust the width as needed */
    }

    .app-admin-wrap {
        margin-top: -30px;
    }
</style>
<div class="main-content">
    <div class="main-content pt-4">
        <div class="form_section1_div">
            <div class="breadcrumb">
                <h1 class="me-2">@if(!empty($single_combinations)) Edit Subject Combinations @else Subject Combinations @endif</h1>
            </div>
            <div class="separator-breadcrumb border-top"></div>
            @if(!empty($single_combinations))
                <form id="subjectCombinationForm" class="p-4 progress-form">
                    <input type="hidden" @if(!empty($single_combinations)) value=" {{ $single_combinations->id }}" @else value="" @endif name="id">
            @else
                <form id="subjectCombinationForm" class="p-4 progress-form">
            @endif
                    @csrf
                        <div class=" row">
                            <div class="col-md-3 form-group mb-3 uperletter">
                                <label class="uperletter" for="combination_name">Combination Name</label>
                                <input @if (!empty($single_combinations)) value="{{ $single_combinations->combination_name }}" @endif
                                    class="form-control uperletter" id="combination_name" name="combination_name"
                                    type="text" placeholder="Combination Name" />
                            </div>
                            <div class="col-md-3 form-group mb-3">
                                <label for="class_select">Classes</label>
                                @php
                                    // Explode class_id if single_combinations is not empty
                                    $selectedClassIds = !empty($single_combinations)
                                        ? explode(',', $single_combinations->class_id)
                                        : [];
                                @endphp

                                <select name="classes[]" class="form-control select2" id="class_select" multiple>
                                    <option value="">-- Please select --</option>
                                    @foreach ($studentclasses as $studentclass)
                                        <option value="{{ $studentclass->id }}"
                                            @if (in_array($studentclass->id, $selectedClassIds)) selected @endif>
                                            {{ $studentclass->class_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 form-group mb-3">
                                <label for="stream">Stream</label>
                                <select name="streams" class="form-control select2" id="stream_select">
                                    <option value="">-- Please select --</option>
                                    @foreach ($streamlist as $stream)
                                        <option value="{{ $stream->id }}"
                                            @if (!empty($single_combinations) && $single_combinations->streams == $stream->id) selected @endif>
                                            {{ $stream->streams }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="col-md-12 form-group mb-3">
                                        <h4>Academic Subjects :-</h4>
                                    </div>
                                    <div class="table-responsive col-md-12">
                                        <table class="display table table-striped table-bordered"
                                            id="selectedSubjectsTable" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Sr.</th>
                                                    <th>Subject</th>
                                                    <th class="serial-number"> Subject Order</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="selected_academic_subjects">
                                                @php $indexNo = 1; @endphp
                                                @if (!empty($single_combinations))
                                                    @foreach ($single_combinations->subjects->where('subject_type', 'Academic') as $subject)
                                                        <tr>
                                                            <td>{{ $indexNo++}}</td>
                                                            <td data-id="{{ $subject->id }}">{{ $subject->subject_name }}</td>
                                                            <td><input name="order" class="form-control" type="number" value="{{ $subject->pivot->subject_order }}"/></td>
                                                            <td class='d-flex'>
                                                                <a class="btn btn-raised ripple btn-danger m-1" href="#" onclick="deleteSelectedSubject(event)">Delete</a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                        <div id="subject-error-message" class="text-danger"></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="col-md-12 form-group mb-3">
                                        <h4>Select Optional Subjects :-</h4>
                                        <div class="col-md-7 form-group mb-3">
                                            <label for="lastName1">Subjects</label>
                                            <div class="d-flex align-items-center">
                                                <select id="selected_optional_subjects" class="form-control uperletter"
                                                    name="subject_name" >
                                                </select>
                                                <div class="ms-2">
                                                    <button type="button" id="loadOptionalSubjectsButton" class="btn btn-primary">
                                                        <svg style="filter: invert(100%);" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 320 512">
                                                            <path
                                                                d="M137.4 374.6c12.5 12.5 32.8 12.5 45.3 0l128-128c9.2-9.2 11.9-22.9 6.9-34.9s-16.6-19.8-29.6-19.8L32 192c-12.9 0-24.6 7.8-29.6 19.8s-2.2 25.7 6.9 34.9l128 128z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive col-md-12">
                                        <table class="display table table-striped table-bordered"
                                            id="selectedSubjectsTable" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Sr.</th>
                                                    <th>Subject</th>
                                                    <th class="serial-number"> Subject Order</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="selected_optional_subjects_list">
                                                @php $indexNo = 1; @endphp
                                                @if (!empty($single_combinations))
                                                    @foreach ($single_combinations->subjects->where('subject_type', 'Optional') as $subject)
                                                        <tr>
                                                            <td>{{ $indexNo++}}</td>
                                                            <td data-id="{{ $subject->id }}">{{ $subject->subject_name }}</td>
                                                            <td><input name="order" class="form-control" type="number" value="{{ $subject->pivot->subject_order }}"/></td>
                                                            <td class='d-flex'>
                                                                <a class="btn btn-raised ripple btn-danger m-1" href="#" onclick="deleteSelectedSubject(event)">Delete</a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                        <div id="subject-error-message" class="text-danger"></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="col-md-12 form-group mb-3">
                                        <h4>Select Non Academic Subjects :-</h4>
                                        <div class="col-md-7 form-group mb-3">
                                            <label for="lastName1">Subjects</label>
                                            <div class="d-flex align-items-center">
                                                <select id="selected_non_academic_subjects"
                                                    class="form-control uperletter" name="subject_name"
                                                    autocomplete="shipping address-level1">
                                                </select>
                                                <div class="ms-2">
                                                    <button type="button" id="loadNonAcademicSubjectsButton"
                                                        class="btn btn-primary">
                                                        <svg style="filter: invert(100%);"
                                                            xmlns="http://www.w3.org/2000/svg" height="1em"
                                                            viewBox="0 0 320 512">
                                                            <!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                                                            <path
                                                                d="M137.4 374.6c12.5 12.5 32.8 12.5 45.3 0l128-128c9.2-9.2 11.9-22.9 6.9-34.9s-16.6-19.8-29.6-19.8L32 192c-12.9 0-24.6 7.8-29.6 19.8s-2.2 25.7 6.9 34.9l128 128z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive col-md-12">
                                        <table class="display table table-striped table-bordered"
                                            id="selectedSubjectsTable" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Sr.</th>
                                                    <th>Subject</th>
                                                    <th class="serial-number"> Subject Order</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="selected_non_academic_subjects_list">
                                                @php $indexNo = 1; @endphp
                                                @if (!empty($single_combinations))
                                                    @foreach ($single_combinations->subjects->where('subject_type', 'Non Academic') as $subject)
                                                        <tr>
                                                            <td>{{ $indexNo++}}</td>
                                                            <td data-id="{{ $subject->id }}">{{ $subject->subject_name }}</td>
                                                            <td><input name="order" class="form-control" type="number" value="{{ $subject->pivot->subject_order }}"/></td>
                                                            <td class='d-flex'>
                                                                <a class="btn btn-raised ripple btn-danger m-1" href="#" onclick="deleteSelectedSubject(event)">Delete</a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                        <div id="subject-error-message" class="text-danger"></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="col-md-12 form-group mb-3">
                                        <h4>Select Skilled Subjects :-</h4>
                                        <div class="col-md-7 form-group mb-3">
                                            <label for="lastName1">Subjects</label>
                                            <div class="d-flex align-items-center">
                                                <select id="selected_skilled_subjects" class="form-control uperletter"
                                                    name="subject_name" autocomplete="shipping address-level1">
                                                </select>
                                                <div class="ms-2">
                                                    <button type="button" id="loadSkilledSubjectsButton" class="btn btn-primary">
                                                        <svg style="filter: invert(100%);" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 320 512">
                                                            <path
                                                                d="M137.4 374.6c12.5 12.5 32.8 12.5 45.3 0l128-128c9.2-9.2 11.9-22.9 6.9-34.9s-16.6-19.8-29.6-19.8L32 192c-12.9 0-24.6 7.8-29.6 19.8s-2.2 25.7 6.9 34.9l128 128z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="table-responsive col-md-12">
                                        <table class="display table table-striped table-bordered"
                                            id="selectedSubjectsTable" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>Sr.</th>
                                                    <th>Subject</th>
                                                    <th class="serial-number"> Subject Order</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="selected_skilled_subjects_list">
                                                @php $indexNo = 1; @endphp
                                                @if (!empty($single_combinations))
                                                    @foreach ($single_combinations->subjects->where('subject_type', 'Skilled') as $subject)
                                                        <tr>
                                                            <td>{{ $indexNo++}}</td>
                                                            <td data-id="{{ $subject->id }}">{{ $subject->subject_name }}</td>
                                                            <td><input name="order" class="form-control" type="number" value="{{ $subject->pivot->subject_order }}"/></td>
                                                            <td class='d-flex'>
                                                                <a class="btn btn-raised ripple btn-danger m-1" href="#" onclick="deleteSelectedSubject(event)">Delete</a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                        <div id="subject-error-message" class="text-danger"></div>
                                    </div>
                                </div>
                                <div class="col-md-12"><br>
                                    <button type="button" id="submitForm" class="btn btn-primary">Submit</button>
                                    <button type="button" id="reset" class="btn btn-primary" name="btn"
                                        value="Reset Form">Reset</button>
                                    @if(request()->route()->getName() !== 'subjectcombinatiomaster')
                                    <a href="{{ url('subjectcombinatiomaster') }}" class="btn btn-primary">Add New</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                        <br>
        </div>
    </div>
</div>
@if(empty($single_combinations))
<div class="breadcrumb">
    <h1 class="me-2">list of saved Subject Combination :-</h1>
</div>
<div class="separator-breadcrumb border-top"></div>
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card text-start">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="zero_configuration_table"
                        style="width: 100%">
                        <thead>
                            <tr>
                                <th>Sr.</th>
                                <th>Combination Name</th>
                                <th>Class</th>
                                <th>Academic Subjects</th>
                                <th>Optional Subjects</th>
                                <th>Non Academic Subjects</th>
                                <th>SKiled Subjects</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($combinations))
                                @foreach ($combinations as $index => $combination)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="uperletter">{{ $combination->combination_name }}</td>
                                        @php
                                            $streamName = \App\Models\Stream::find($combination->streams)?->streams;
                                            $classIds = explode(',', $combination->class_id);
                                            $classes = \App\Models\Classname::whereIn('id', $classIds)->get();
                                            $className = "";
                                            foreach ($classes as $class) {
                                                $className .= $class->class_name . ", ";
                                            }
                                            $className = rtrim($className, ', ');
                                        @endphp
                                        <td>{{ $className ?? 'N/A' }}@if($streamName)({{ $streamName }})@endif</td>
                                        <td>
                                            @foreach ($combination->subjects->where('subject_type', 'Academic') as $subject)
                                                <span class="uperletter badge bg-primary">{{ $subject->subject_name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach ($combination->subjects->where('subject_type', 'Optional') as $subject)
                                                <span class="uperletter badge bg-primary">{{ $subject->subject_name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach ($combination->subjects->where('subject_type', 'Non Academic') as $subject)
                                                <span class="uperletter badge bg-warning">{{ $subject->subject_name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach ($combination->subjects->where('subject_type', 'Skilled') as $subject)
                                                <span class="uperletter badge bg-success">{{ $subject->subject_name }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <a href="{{ url('view-subjectcombinatiomaster') .'/'.$combination->id}}" class="btn btn-primary m-1">Edit</a>
                                            <a class="btn btn-raised ripple btn-danger m-1" href="{{url('delete-subjectcombinatiomaster').'/'.$combination->id}}" onclick="confirmDelete(event)">Delete</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                            <tr>
                                <td colspan="9" class="text-center">No Data Found</td>
                            </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Sr.</th>
                                <th>Combination Name</th>
                                <th>Class</th>
                                <th>Academic Subjects</th>
                                <th>Optional Subjects</th>
                                <th>Non Academic Subjects</th>
                                <th>SKiled Subjects</th>
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
        console.log('confirmDelete function called');
        Swal.fire({
            title: 'Are you sure?'
            , text: "You won't be able to revert this!"
            , icon: 'warning'
            , showCancelButton: true
            , confirmButtonColor: '#d33'
            , cancelButtonColor: '#3085d6'
            , confirmButtonText: 'Yes, delete it!'
            , cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // If the user clicks on "Yes, delete it!", navigate to the delete URL
                window.location.href = event.target.href;
            }
        });
    }
    function confirmDeletesub(event) {
        event.preventDefault(); // Prevents the default link navigation
        console.log('confirmDelete function called');
        Swal.fire({
            title: 'Are you sure?'
            , text: "You won't be able to revert this!"
            , icon: 'warning'
            , showCancelButton: true
            , confirmButtonColor: '#d33'
            , cancelButtonColor: '#3085d6'
            , confirmButtonText: 'Yes, delete it!'
            , cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = event.target.href;
            }
        });
    }
</script>
<script>
    function hasDuplicates(array) {
        return (new Set(array)).size !== array.length;
    }
    function extractSubjectsFromTable(tableId) {
        let rows = document.getElementById(tableId).children;
        let result = [];

        for (let i = 0; i < rows.length; i++) {
            let td = rows[i].children[1];
            let subjectId = td.getAttribute('data-id');
            let order = rows[i].children[2]?.children[0]?.value?.trim();

            if (subjectId && order !== undefined) {
                result.push({
                    subject_id: subjectId,
                    order: order
                });
            }
        }

        return result;
    }
    document.getElementById("submitForm").addEventListener("click", function (e) {
        e.preventDefault();
        $("#subject-error-message").html("");
        $("#classes-error-message").html("");
        let academicSubjects = extractSubjectsFromTable("selected_academic_subjects");
        let nonAcademicSubjects = extractSubjectsFromTable("selected_non_academic_subjects_list");
        let skilledSubjects = extractSubjectsFromTable("selected_skilled_subjects_list");
        let optionalSubjects = extractSubjectsFromTable("selected_optional_subjects_list");
        // Validation
        if (academicSubjects.length === 0 && nonAcademicSubjects.length === 0 && skilledSubjects.length === 0) {
            $("#subject-error-message").html("Please select at least one subject.");
            return;
        }
        let allOrders = [...academicSubjects, ...nonAcademicSubjects, ...skilledSubjects].map(s => s.order);
        if (allOrders.includes("") || allOrders.includes(null)) {
            $("#subject-error-message").html("Each subject must have an order.");
            return;
        }
        if (new Set(allOrders).size !== allOrders.length) {
            $("#subject-error-message").html("Duplicate subject order detected.");
            return;
        }
        // Prepare form data
        let form = document.getElementById("subjectCombinationForm");
        let formData = new FormData(form);
        formData.append("academic_subjects", JSON.stringify(academicSubjects));
        formData.append("non_academic_subjects", JSON.stringify(nonAcademicSubjects));
        formData.append("skilled_subjects", JSON.stringify(skilledSubjects));
        formData.append("optional_subjects", JSON.stringify(optionalSubjects));
        // Optional: Append other custom fields here if needed
        $.ajax({
            type: "POST",
            url: "{{ route('store-subjectcombinatiomaster') }}",
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                toastr.success("Subject combination saved successfully.");
                setTimeout(() => {
                    window.location.href = "{{ route('subjectcombinatiomaster') }}";
                }, 1500);
            },
            error: function (error) {
                toastr.error("A combination with the subjects already exists.");
                console.error(error);
            }
        });
    });
</script>
<script>
    document.getElementById("loadOptionalSubjectsButton").addEventListener("click", function () {
        var subjectSelect = document.getElementById("selected_optional_subjects");
        var selectedSubjectId = subjectSelect.value;
        var selectedSubject = subjectSelect.options[subjectSelect.selectedIndex].text;

        if (selectedSubject) {
            var newRow = document.createElement("tr");
            let indexNo = document.getElementById("selected_optional_subjects_list").children.length + 1;

            newRow.innerHTML = `
                <td>${indexNo}</td>
                <td data-id="${selectedSubjectId}">${selectedSubject}</td>
                <td><input name="order" class="form-control" type="number" /></td>
                <td class='d-flex'>
                    <a class="btn btn-raised ripple btn-danger m-1" href="#" onclick="deleteSelectedSubject(event)">Delete</a>
                </td>
            `;

            document.getElementById("selected_optional_subjects_list").appendChild(newRow);
            subjectSelect.value = "";
        }
    });
    document.getElementById("loadSkilledSubjectsButton").addEventListener("click", function () {
        var subjectSelect = document.getElementById("selected_skilled_subjects");
        var selectedSubjectId = subjectSelect.value;
        var selectedSubject = subjectSelect.options[subjectSelect.selectedIndex].text;

        if (selectedSubject) {
            var newRow = document.createElement("tr");
            let indexNo = document.getElementById("selected_skilled_subjects_list").children.length + 1;

            newRow.innerHTML = `
                <td>${indexNo}</td>
                <td data-id="${selectedSubjectId}">${selectedSubject}</td>
                <td><input name="order" class="form-control" type="number" /></td>
                <td class='d-flex'>
                    <a class="btn btn-raised ripple btn-danger m-1" href="#" onclick="deleteSelectedSubject(event)">Delete</a>
                </td>
            `;

            document.getElementById("selected_skilled_subjects_list").appendChild(newRow);
            subjectSelect.value = "";
        }
    });
    document.getElementById("loadNonAcademicSubjectsButton").addEventListener("click", function () {
        var subjectSelect = document.getElementById("selected_non_academic_subjects");
        var selectedSubjectId = subjectSelect.value;
        var selectedSubject = subjectSelect.options[subjectSelect.selectedIndex].text;

        if (selectedSubject) {
            var newRow = document.createElement("tr");
            let indexNo = document.getElementById("selected_non_academic_subjects_list").children.length + 1;

            newRow.innerHTML = `
                <td>${indexNo}</td>
                <td data-id="${selectedSubjectId}">${selectedSubject}</td>
                <td><input name="order" class="form-control" type="number" /></td>
                <td class='d-flex'>
                    <a class="btn btn-raised ripple btn-danger m-1" href="#" onclick="deleteSelectedSubject(event)">Delete</a>
                </td>
            `;

            document.getElementById("selected_non_academic_subjects_list").appendChild(newRow);
            subjectSelect.value = "";
        }
    });
</script>
@if(!empty($single_combinations))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const preSelectedClassId = "{{ $single_combinations->class_id }}".split(',');
        const preSelectedstreams = {{ $single_combinations->streams }};
        $.get('{{ route("fetch-subjects") }}', { class_id: preSelectedClassId,stream_id: preSelectedstreams, _token: '{{ csrf_token() }}' }, function (data) {
            @if(empty($single_combinations))
                resetFieldsData();
            @endif
            // Populate academic subjects
            let rows = '';
            let srn = 1;
            data.academic.forEach(function(subject) {
                console.log(subject);
                rows += `
                    <tr>
                        <td>${srn}</td>
                        <td data-id="${subject.id}">${subject.subject_name}</td>
                        <td><input name="order" class="form-control" type="number"/></td>
                        <td class='d-flex'>
                            <a class="btn btn-raised ripple btn-danger m-1" href="#" onclick="deleteSelectedSubject(event)">Delete</a>
                        </td>
                    </tr>
                `;
                srn += 1;
            });
            //$('#selected_academic_subjects').html(rows);

            // Populate non-academic dropdown
            let nonAcademicOptions = `<option value="">--Please Select--</option>`;
            data.non_academic.forEach(function(subject) {
                nonAcademicOptions += `<option value="${subject.id}">${subject.subject_name}</option>`;
            });
            $('#selected_non_academic_subjects').html(nonAcademicOptions);

            // Populate skilled dropdown
            let skilledOptions = `<option value="">--Please Select--</option>`;
            data.skilled.forEach(function(subject) {
                skilledOptions += `<option value="${subject.id}">${subject.subject_name}</option>`;
            });
            $('#selected_skilled_subjects').html(skilledOptions);
            let optionalOptions = `<option value="">--Please Select--</option>`;
            data.optional.forEach(function(subject) {
                optionalOptions += `<option value="${subject.id}">${subject.subject_name}</option>`;
            });
            $('#selected_optional_subjects').html(optionalOptions);
        });
    });
</script>
@endif
<script>
    function fetchSubjects(classId,streamId) {
        $.get('{{ route("fetch-subjects") }}', { class_id: classId,stream_id: streamId, _token: '{{ csrf_token() }}' }, function (data) {
            @if(empty($single_combinations))
                resetFieldsData();
            @endif
            // Populate academic subjects
            let rows = '';
            let srn = 1;
            data.academic.forEach(function(subject) {
                rows += `
                    <tr>
                        <td>${srn}</td>
                        <td data-id="${subject.id}">${subject.subject_name}</td>
                        <td><input name="order" class="form-control" type="number"/></td>
                        <td class='d-flex'>
                            <a class="btn btn-raised ripple btn-danger m-1" href="#" onclick="deleteSelectedSubject(event)">Delete</a>
                        </td>
                    </tr>

                `;
                srn += 1;
            });
            $('#selected_academic_subjects').html(rows);

            // Populate non-academic dropdown
            let nonAcademicOptions = `<option value="">--Please Select--</option>`;
            data.non_academic.forEach(function(subject) {
                nonAcademicOptions += `<option value="${subject.id}">${subject.subject_name}</option>`;
            });
            $('#selected_non_academic_subjects').html(nonAcademicOptions);

            // Populate skilled dropdown
            let skilledOptions = `<option value="">--Please Select--</option>`;
            data.skilled.forEach(function(subject) {
                skilledOptions += `<option value="${subject.id}">${subject.subject_name}</option>`;
            });
            $('#selected_skilled_subjects').html(skilledOptions);
            let optionalOptions = `<option value="">--Please Select--</option>`;
            data.optional.forEach(function(subject) {
                optionalOptions += `<option value="${subject.id}">${subject.subject_name}</option>`;
            });
            $('#selected_optional_subjects').html(optionalOptions);
        });
    }
    $(document).ready(function () {
        const classSelect = $('#class_select');

        // Initialize select2
        classSelect.select2();

        // Handle change event
        classSelect.on('change', function () {
            const selectedOptions = $(this).find('option:selected');
            let class_ids = [];
            let classNames = [];
            let hasSeniorClass = false;
            selectedOptions.each(function () {
                const classId = $(this).val();
                const className = $(this).text();
                if (!classId) return;
                class_ids.push(classId);
                classNames.push(className);
                if (className.includes('11') || className.includes('12')) {
                    hasSeniorClass = true;
                }
            });
            // No valid class selected
            if (class_ids.length === 0) return;
            let streamId = "";
            if (hasSeniorClass) {
                streamId = $('#stream_select').val();
                if (!streamId) {
                    console.warn("Stream ID is required for senior classes (11 or 12), but not selected.");
                    return;
                }
            }
            fetchSubjects(class_ids, streamId);
        });
    });

    $(document).ready(function () {

        const streamSelect = $('#stream_select');
        streamSelect.on('change', function () {
            const selectedOptions = $('#class_select').find('option:selected');
            let class_ids = [];
            let classNames = [];
            let hasSeniorClass = false;
            selectedOptions.each(function () {
                const classId = $(this).val();
                const className = $(this).text();
                if (!classId) return;
                class_ids.push(classId);
                classNames.push(className);
                if (className.includes('11') || className.includes('12')) {
                    hasSeniorClass = true;
                }
            });
            // No valid class selected
            if (class_ids.length === 0) return;
            let streamId = "";
            if (hasSeniorClass) {
                streamId = $('#stream_select').val();
                if (!streamId) {
                    console.warn("Stream ID is required for senior classes (11 or 12), but not selected.");
                    return;
                }
            }
            fetchSubjects(class_ids, streamId);
        });
    });
</script>
<script defer>
    // Function to delete a selected subject row
    function deleteSelectedSubject(event) {
        event.preventDefault();
        event.target.parentElement.parentElement.remove();
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $("#reset").on("click", function () {
            $("#combination_name").val("");
            $("#class_select").val("");
            $("#selected_subjects").html("");
            $("#selected_academic_subjects").html("");
            $("#selected_non_academic_subjects").html("");
            $("#selected_skilled_subjects").html("");
            $("#selected_non_academic_subjects_list").html("");
            $("#selected_skilled_subjects_list").html("");
        });
    })
    function resetFieldsData(){
        $("#selected_subjects").html("");
        $("#selected_academic_subjects").html("");
        $("#selected_non_academic_subjects").html("");
        $("#selected_skilled_subjects").html("");
        $("#selected_non_academic_subjects_list").html("");
        $("#selected_skilled_subjects_list").html("");
    }
</script>
<script>
    jQuery(document).ready(function($) {
        //$('#class_select').select2();
    });
</script>
@endsection
