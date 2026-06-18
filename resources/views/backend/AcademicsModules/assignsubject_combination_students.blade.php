@extends('backend.layouts.main')
@section('main-container')

@php
    $i = 0;
@endphp

<div class="main-content pt-4">
    <div class="form_section1_div">
        <div class="breadcrumb d-flex justify-content-between align-items-center">
            <h1 class="me-2">Combination Wise Student Records</h1>
            <a href="{{ route('AssignSubject') }}" class="btn btn-secondary">Back to Assign Subject</a>
        </div>
        <div class="separator-breadcrumb border-top"></div>

        <div class="card text-start">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3 form-group mb-2">
                        <label for="report_filter_combination">Combination</label>
                        <select id="report_filter_combination" class="form-control">
                            <option value="">All Combinations</option>
                            @foreach ($comlist as $combination)
                                <option value="{{ $combination->id }}">{{ $combination->combination_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-2">
                        <label for="report_filter_class">Class</label>
                        <select id="report_filter_class" class="form-control">
                            <option value="">All Classes</option>
                            @foreach ($classlist as $class)
                                <option value="{{ $class->class_name }}">{{ $class->class_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-2">
                        <label for="report_filter_section">Section</label>
                        <select id="report_filter_section" class="form-control">
                            <option value="">All Sections</option>
                            @foreach ($classes->pluck('section_name')->filter()->unique()->values() as $section)
                                <option value="{{ $section }}">{{ $section }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-2">
                        <label>&nbsp;</label>
                        <div class="d-flex">
                            <button type="button" id="reset_combination_student_filters" class="btn btn-secondary mr-2">Reset</button>
                            <button type="button" id="export_combination_students_excel" class="btn btn-success">Export Excel</button>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 col-sm-6 mb-2">
                        <div class="combination-report-box">
                            <span>Total Students</span>
                            <strong id="report_total_students">0</strong>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <div class="combination-report-box">
                            <span>Combinations</span>
                            <strong id="report_total_combinations">0</strong>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <div class="combination-report-box">
                            <span>Classes</span>
                            <strong id="report_total_classes">0</strong>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <div class="combination-report-box">
                            <span>Sections</span>
                            <strong id="report_total_sections">0</strong>
                        </div>
                    </div>
                </div>

                <div class="mb-2">
                    <span class="badge badge-info" id="combination_student_result_count">0 records</span>
                </div>

                <div class="table-responsive">
                    <table class="display table table-striped table-bordered" id="combination_student_table" style="width: 100%">
                        <thead>
                            <tr>
                                <th>Sr. No</th>
                                <th>Combination</th>
                                <th>Student Name</th>
                                <th>Scholar No.</th>
                                <th>Class</th>
                                <th>Section</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assignments as $assignment)
                                <tr class="combination-student-row"
                                    data-combination="{{ $assignment->assign_this_combtoall }}"
                                    data-class="{{ $assignment->class_name }}"
                                    data-section="{{ $assignment->section_name }}"
                                    data-student="{{ $assignment->students_details }}">
                                    <td class="combination-student-sr">{{ ++$i }}</td>
                                    <td>{{ $assignment->combination->combination_name ?? 'N/A' }}</td>
                                    <td>{{ $assignment->Student->student_name ?? 'N/A' }}</td>
                                    <td>{{ $assignment->Student->scholar_no ?? 'N/A' }}</td>
                                    <td>{{ $assignment->class_name ?? 'N/A' }}</td>
                                    <td>{{ $assignment->section_name ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .combination-report-box {
        border: 1px solid #d8dde6;
        border-radius: 6px;
        padding: 12px 14px;
        background: #fff;
        min-height: 76px;
    }

    .combination-report-box span {
        display: block;
        color: #6c757d;
        font-size: 13px;
        margin-bottom: 8px;
    }

    .combination-report-box strong {
        display: block;
        color: #1f2937;
        font-size: 26px;
        line-height: 1;
    }
</style>

<script>
    let combinationStudentTable = null;
    const combinationStudentFilterName = 'combination-student-report-filters';

    function normalizeCombinationReportValue(value) {
        return (value || '').toString().trim().toLowerCase();
    }

    function registerCombinationStudentFilter() {
        if (!$.fn.DataTable || $.fn.dataTable.ext.search.some(filter => filter.filterName === combinationStudentFilterName)) {
            return;
        }

        const filter = function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'combination_student_table') {
                return true;
            }

            const row = $(settings.aoData[dataIndex].nTr);
            const selectedCombination = normalizeCombinationReportValue($('#report_filter_combination').val());
            const selectedClass = normalizeCombinationReportValue($('#report_filter_class').val());
            const selectedSection = normalizeCombinationReportValue($('#report_filter_section').val());

            return (!selectedCombination || normalizeCombinationReportValue(row.data('combination')) === selectedCombination)
                && (!selectedClass || normalizeCombinationReportValue(row.data('class')) === selectedClass)
                && (!selectedSection || normalizeCombinationReportValue(row.data('section')) === selectedSection);
        };

        filter.filterName = combinationStudentFilterName;
        $.fn.dataTable.ext.search.push(filter);
    }

    function getCombinationStudentTable() {
        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#combination_student_table')) {
            combinationStudentTable = $('#combination_student_table').DataTable();
            return combinationStudentTable;
        }

        if ($.fn.DataTable) {
            combinationStudentTable = $('#combination_student_table').DataTable({
                pageLength: 25,
                lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'All']],
                ordering: true,
                searching: true,
                language: {
                    zeroRecords: 'No student records found for selected filters.'
                }
            });

            combinationStudentTable.on('draw.combinationStudentReport', updateCombinationStudentSummary);
        }

        return combinationStudentTable;
    }

    function updateCombinationStudentSummary() {
        const table = getCombinationStudentTable();
        const rows = table ? $(table.rows({ search: 'applied' }).nodes()) : $('.combination-student-row:visible');
        const students = new Set();
        const combinations = new Set();
        const classes = new Set();
        const sections = new Set();

        rows.each(function (index) {
            const row = $(this);
            row.find('.combination-student-sr').text(index + 1);

            const student = normalizeCombinationReportValue(row.data('student'));
            const combination = normalizeCombinationReportValue(row.data('combination'));
            const className = normalizeCombinationReportValue(row.data('class'));
            const section = normalizeCombinationReportValue(row.data('section'));

            if (student) students.add(student);
            if (combination) combinations.add(combination);
            if (className) classes.add(className);
            if (section) sections.add(section);
        });

        $('#report_total_students').text(students.size);
        $('#report_total_combinations').text(combinations.size);
        $('#report_total_classes').text(classes.size);
        $('#report_total_sections').text(sections.size);
        $('#combination_student_result_count').text(rows.length + (rows.length === 1 ? ' record' : ' records'));
    }

    function applyCombinationStudentFilters() {
        const table = getCombinationStudentTable();

        if (table) {
            table.draw();
        } else {
            const selectedCombination = normalizeCombinationReportValue($('#report_filter_combination').val());
            const selectedClass = normalizeCombinationReportValue($('#report_filter_class').val());
            const selectedSection = normalizeCombinationReportValue($('#report_filter_section').val());

            $('.combination-student-row').each(function () {
                const row = $(this);
                const isVisible = (!selectedCombination || normalizeCombinationReportValue(row.data('combination')) === selectedCombination)
                    && (!selectedClass || normalizeCombinationReportValue(row.data('class')) === selectedClass)
                    && (!selectedSection || normalizeCombinationReportValue(row.data('section')) === selectedSection);

                row.toggle(isVisible);
            });

            updateCombinationStudentSummary();
        }
    }

    function exportCombinationStudentsExcel() {
        applyCombinationStudentFilters();

        const table = getCombinationStudentTable();
        const filteredRows = table
            ? $(table.rows({ search: 'applied' }).nodes())
            : $('.combination-student-row:visible');
        const rows = [];

        rows.push(['Sr. No', 'Combination', 'Student Name', 'Scholar No.', 'Class', 'Section']);

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
            toastr.warning('No student records to export.');
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
        link.download = 'combination-wise-students-' + date + '.xls';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(link.href);
    }

    $(document).ready(function () {
        registerCombinationStudentFilter();
        getCombinationStudentTable();
        applyCombinationStudentFilters();
    });

    $(document).on('change', '#report_filter_combination, #report_filter_class, #report_filter_section', applyCombinationStudentFilters);

    $(document).on('click', '#reset_combination_student_filters', function () {
        $('#report_filter_combination, #report_filter_class, #report_filter_section').val('');
        applyCombinationStudentFilters();
    });

    $(document).on('click', '#export_combination_students_excel', exportCombinationStudentsExcel);
</script>

@endsection
