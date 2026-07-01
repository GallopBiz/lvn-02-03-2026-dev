@extends('backend.layouts.main')
@section('main-container')
@php
    $editingGrade = !empty($stream_master) ? $stream_master->first() : null;
    $selectedClasses = collect(json_decode($editingGrade->groups ?? '[]', true) ?: []);
@endphp

<style>
    .uperletter {
        text-transform: capitalize;
    }

    .class-list-panel {
        max-height: 355px;
        overflow-y: auto;
    }

    .grade-range-table input {
        min-width: 110px;
    }
</style>

<div class="main-content">
    <div class="breadcrumb">
        <h1 class="me-2">Grading Master</h1>
    </div>

    <div class="separator-breadcrumb border-top"></div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <h4>Set Grading Scheme Here :-</h4>

            <div class="separator-breadcrumb border-top"></div>
            <form id="progress-form" class="p-4 progress-form" action="{{ !empty($editingGrade) ? url('store-gradingmaster') : url('save-gradingmaster') }}" method="post">
                @csrf
                @if(!empty($editingGrade))
                    <input type="hidden" value="{{ $editingGrade->id }}" name="id">
                @endif

                <div class="row">
                    <div class="col-md-6 form-group mb-3">
                        <label for="grading_name">Grading Name</label>
                        <input required class="form-control uperletter" id="grading_name" name="grading_name" type="text" value="{{ $editingGrade->grading_name ?? '' }}" placeholder="Grading" />
                    </div>

                    <div class="col-md-6 form-group mb-3">
                        <label for="subject_type">Subject Type</label>
                        <select required class="form-control" id="subject_type" name="subject_type">
                            <option value="">-- Please select --</option>
                            @foreach($subjectTypes ?? [] as $subjectType)
                                <option value="{{ $subjectType }}" {{ (($editingGrade->subject_type ?? '') == $subjectType) ? 'selected' : '' }}>{{ $subjectType }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12">
                        <h5>Grade Details Below Grade Point Wise :-</h5>
                    </div>

                    <div class="col-md-12 form-group mb-3">
                        <div class="table-responsive">
                            <table class="table table-bordered grade-range-table mb-2">
                                <thead>
                                    <tr>
                                        <th>From Grade Point</th>
                                        <th>To Grade Point</th>
                                        <th>Grade</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="grade-range-body">
                                    <tr class="grade-range-row">
                                        <td>
                                            <input required class="form-control" name="grade_ranges[0][min_per]" type="number" step="0.01" value="{{ $editingGrade->min_per ?? '' }}" placeholder="From" />
                                        </td>
                                        <td>
                                            <input required class="form-control" name="grade_ranges[0][max_per]" type="number" step="0.01" value="{{ $editingGrade->max_per ?? '' }}" placeholder="To" />
                                        </td>
                                        <td>
                                            <input required class="form-control uperletter" name="grade_ranges[0][grade]" type="text" value="{{ $editingGrade->grade ?? '' }}" placeholder="Grade" />
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm remove-grade-row">Remove</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" id="add-grade-row" class="btn btn-info btn-sm">Add More</button>
                    </div>

                    <input type="hidden" name="groups" id="groups" value="{{ $editingGrade->groups ?? '[]' }}">

                    <div class="col-md-12">
                        <div id="classes_error" class="text-danger mb-2"></div>
                        <button type="submit" id="submitBtn" class="btn btn-primary">Save Grade</button>
                        <button type="button" id="reset" class="btn btn-secondary" name="btn" value="Reset Form">Reset</button>

                        @if(request()->route()->getName() !== 'gradingmaster')
                            <a href="{{ url('gradingmaster') }}" class="btn btn-primary">Add New</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card text-start">
                <div class="card-body">
                    <h4 class="mb-3">Select Classes</h4>
                    <div class="table-responsive class-list-panel">
                        <table class="display table table-striped table-bordered" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Class Name</th>
                                    <th>Action <input type="checkbox" class="class_checkbox_header"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($studentclasses ?? [] as $index => $class)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $class->class_name }}</td>
                                        <td>
                                            <input type="checkbox" class="class_checkbox" name="class_checkbox" value="{{ $class->class_name }}" {{ $selectedClasses->contains($class->class_name) ? 'checked' : '' }}>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center">No Data Found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="separator-breadcrumb border-top"></div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="breadcrumb">
                <h1 class="me-2">List of Saved Records :-</h1>
            </div>
            <div class="separator-breadcrumb border-top"></div>

            <div class="card text-start">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="display table table-striped table-bordered" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Sr.</th>
                                    <th>Grading Name</th>
                                    <th>Subject Type</th>
                                    <th>From Grade Point</th>
                                    <th>To Grade Point</th>
                                    <th>Grade</th>
                                    <th>Classes</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stream ?? [] as $index => $streams)
                                    @php
                                        $classes = collect(json_decode($streams->groups ?? '[]', true) ?: [])->implode(', ');
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="uperletter">{{ $streams->grading_name }}</td>
                                        <td>{{ $streams->subject_type ?? '-' }}</td>
                                        <td>{{ $streams->min_per }}</td>
                                        <td>{{ $streams->max_per }}</td>
                                        <td class="uperletter">{{ $streams->grade }}</td>
                                        <td>{{ $classes ?: '-' }}</td>
                                        <td class="d-flex">
                                            <a class="btn btn-primary m-1" href="{{ url('view-gradingmaster') . '/' . $streams->id }}">Edit</a>
                                            @php $deleteKey = 'grademaster-' . $streams->id; @endphp
                                            <a class="btn btn-raised ripple btn-danger m-1" href="{{ url('delete-gradingmaster') . '/' . $deleteKey }}" onclick="confirmDelete(event)">Delete</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No Data Found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    function confirmDelete(event) {
        event.preventDefault();

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
                window.location.href = event.target.href;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        $('.class_checkbox_header').change(function () {
            $('.class_checkbox').prop('checked', $(this).prop('checked'));
        });

        $('#progress-form').on('submit', function(e) {
            const classes = [];
            $("input:checkbox[name=class_checkbox]:checked").each(function() {
                classes.push($(this).val());
            });

            if (classes.length < 1) {
                e.preventDefault();
                $('#classes_error').html('Please select at least one class.');
                return;
            }

            $('#classes_error').html('');
            $('#groups').val(JSON.stringify(classes));
        });

        let gradeRangeIndex = 1;
        $('#add-grade-row').on('click', function () {
            $('#grade-range-body').append(`
                <tr class="grade-range-row">
                    <td>
                        <input required class="form-control" name="grade_ranges[${gradeRangeIndex}][min_per]" type="number" step="0.01" placeholder="From" />
                    </td>
                    <td>
                        <input required class="form-control" name="grade_ranges[${gradeRangeIndex}][max_per]" type="number" step="0.01" placeholder="To" />
                    </td>
                    <td>
                        <input required class="form-control uperletter" name="grade_ranges[${gradeRangeIndex}][grade]" type="text" placeholder="Grade" />
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-grade-row">Remove</button>
                    </td>
                </tr>
            `);
            gradeRangeIndex++;
        });

        $(document).on('click', '.remove-grade-row', function () {
            if ($('.grade-range-row').length === 1) {
                $(this).closest('tr').find('input').val('');
                return;
            }

            $(this).closest('tr').remove();
        });

        $('#reset').on('click', function () {
            $('#grading_name, #subject_type').val('');
            $('#grade-range-body').html(`
                <tr class="grade-range-row">
                    <td>
                        <input required class="form-control" name="grade_ranges[0][min_per]" type="number" step="0.01" placeholder="From" />
                    </td>
                    <td>
                        <input required class="form-control" name="grade_ranges[0][max_per]" type="number" step="0.01" placeholder="To" />
                    </td>
                    <td>
                        <input required class="form-control uperletter" name="grade_ranges[0][grade]" type="text" placeholder="Grade" />
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-grade-row">Remove</button>
                    </td>
                </tr>
            `);
            gradeRangeIndex = 1;
            $('.class_checkbox, .class_checkbox_header').prop('checked', false);
            $('#groups').val('[]');
            $('#classes_error').html('');
        });
    });
</script>
@endsection
