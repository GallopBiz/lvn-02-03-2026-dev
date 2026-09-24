@extends('backend.layouts.main')
@section('main-container')
<div class="main-content pt-4">
    <div class="breadcrumb d-flex justify-content-between align-items-center">
        <h1>Transport Assignment Report</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="row mb-4">
        <div class="col-md-4"><div class="card"><div class="card-body"><h6 class="text-muted">Assigned Students</h6><h3>{{ $studentsTotal }}</h3></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><h6 class="text-muted">Drivers / Bus Groups</h6><h3>{{ $summaryTotal }}</h3></div></div></div>
        <div class="col-md-4"><div class="card"><div class="card-body"><h6 class="text-muted">Bus Fees Total</h6><h3>{{ number_format($busFeesTotal, 2) }}</h3></div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h3>Driver and Bus Strength</h3>
            <div class="row mb-2">
                <div class="col-sm-12 col-md-6">
                    <a id="driver_bus_strength_export" class="btn btn-success btn-sm" href="{{ route('transport.assignment-report.summary-export', request()->except(['summary_page'])) }}" data-export-url="{{ route('transport.assignment-report.summary-export') }}">Export CSV</a>
                </div>
                <div class="col-sm-12 col-md-6">
                    <form method="GET" class="d-flex justify-content-md-end transport-table-search" data-table-id="driver_bus_strength_table" data-page-param="summary_page">
                        <input type="hidden" name="driver" value="{{ $driver }}">
                        <input type="hidden" name="bus" value="{{ $bus }}">
                        <input type="hidden" name="students_search" value="{{ $studentsSearch }}">
                        <input type="hidden" name="student_driver" value="{{ $studentDriver }}">
                        <input type="hidden" name="student_bus_no" value="{{ $studentBusNo }}">
                        <input type="hidden" name="student_bus_amount" value="{{ $studentBusAmount }}">
                        <input type="search" name="summary_search" value="{{ $summarySearch }}" class="form-control form-control-sm" placeholder="" aria-controls="driver_bus_strength_table" style="max-width: 220px;">
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead><tr><th>S.No.</th><th>Driver</th><th>Driver Mobile No.</th><th>Bus No.</th><th>Students</th><th>Bus Fees Total</th></tr></thead>
                    <tbody id="driver_bus_strength_table_body">
                    @forelse($summary as $key => $row)
                        <tr><td>{{ $summary->firstItem() + $key }}</td><td>{{ $row['driver_name'] }}</td><td>{{ $row['driver_mobile_no'] ?: '-' }}</td><td>{{ $row['bus_no'] }}</td><td>{{ $row['student_count'] }}</td><td>{{ number_format($row['total_bus_fees'], 2) }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No bus assignments found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @include('backend.Transport.partials.datatables-pagination', [
                'paginator' => $summary,
                'tableId' => 'driver_bus_strength_table',
            ])
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3>Student Fee-Master Assignments</h3>
            <div class="row mb-2">
                <div class="col-sm-12 col-md-6">
                    <a id="student_fee_master_export" class="btn btn-success btn-sm" href="{{ route('transport.assignment-report.students-export', request()->except(['students_page'])) }}" data-export-url="{{ route('transport.assignment-report.students-export') }}">Export CSV</a>
                </div>
                <div class="col-sm-12 col-md-6">
                    <form method="GET" class="d-flex flex-wrap justify-content-md-end transport-table-search" data-table-id="student_fee_master_table" data-page-param="students_page">
                        <input type="hidden" name="driver" value="{{ $driver }}">
                        <input type="hidden" name="bus" value="{{ $bus }}">
                        <input type="hidden" name="summary_search" value="{{ $summarySearch }}">
                        <select name="student_driver" class="form-control form-control-sm mb-2 ml-2" aria-controls="student_fee_master_table" style="max-width: 180px;">
                            <option value="">Driver Name</option>
                            @foreach($studentDrivers as $studentDriverName)
                                <option value="{{ $studentDriverName }}" @selected($studentDriver === $studentDriverName)>{{ $studentDriverName }}</option>
                            @endforeach
                        </select>
                        <select name="student_bus_amount" class="form-control form-control-sm mb-2 ml-2" aria-controls="student_fee_master_table" style="max-width: 150px;">
                            <option value="">Bus Amount</option>
                            @foreach($studentBusAmounts as $busAmount)
                                <option value="{{ number_format($busAmount, 2, '.', '') }}" @selected($studentBusAmount !== '' && number_format((float) $studentBusAmount, 2, '.', '') === number_format($busAmount, 2, '.', ''))>{{ number_format($busAmount, 2) }}</option>
                            @endforeach
                        </select>
                        <select name="student_bus_no" class="form-control form-control-sm mb-2 ml-2" aria-controls="student_fee_master_table" style="max-width: 130px;">
                            <option value="">Bus No.</option>
                            @foreach($studentBusNos as $studentBusNumber)
                                <option value="{{ $studentBusNumber }}" @selected($studentBusNo === $studentBusNumber)>{{ $studentBusNumber }}</option>
                            @endforeach
                        </select>
                        <input type="search" name="students_search" value="{{ $studentsSearch }}" class="form-control form-control-sm mb-2 ml-2" placeholder="Search" aria-controls="student_fee_master_table" style="max-width: 180px;">
                        <button type="button" class="btn btn-light btn-sm mb-2 ml-2 transport-table-reset">Reset</button>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead><tr><th>S.No.</th><th>Student</th><th>Scholar No.</th><th>Class</th><th>Driver</th><th>Driver Contact No.</th><th>Bus No.</th><th>Bus Amount</th><th>Start Date</th><th>Stop Date</th></tr></thead>
                    <tbody id="student_fee_master_table_body">
                    @forelse($students as $key => $student)
                        <tr><td>{{ $students->firstItem() + $key }}</td><td>{{ $student['student_name'] }}</td><td>{{ $student['scholar_no'] }}</td><td>{{ $student['class_name'] }}{{ $student['section_name'] ? ' / '.$student['section_name'] : '' }}</td><td>{{ $student['driver_name'] ?: 'Unassigned' }}</td><td>{{ $student['driver_mobile_no'] ?: '-' }}</td><td>{{ $student['bus_no'] ?: 'Unassigned' }}</td><td>{{ number_format($student['bus_fee'], 2) }}</td><td>{{ $student['start_date'] ?: '-' }}</td><td>{{ $student['stop_date'] ?: '-' }}</td></tr>
                    @empty
                        <tr><td colspan="10" class="text-center">No students found for the selected filters.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @include('backend.Transport.partials.datatables-pagination', [
                'paginator' => $students,
                'tableId' => 'student_fee_master_table',
            ])
        </div>
    </div>
</div>
<script>
    document.querySelectorAll('.transport-table-search').forEach(function (form) {
        var searchInput = form.querySelector('input[type="search"]');
        var timer = null;
        var controller = null;
        var summaryExportLink = document.getElementById('driver_bus_strength_export');
        var studentsExportLink = document.getElementById('student_fee_master_export');

        function updateExportLink(exportLink, url, pageParam) {
            if (!exportLink) {
                return;
            }

            var exportUrl = new URL(exportLink.dataset.exportUrl, window.location.origin);
            ['driver', 'bus', 'summary_search', 'students_search', 'student_driver', 'student_bus_no', 'student_bus_amount'].forEach(function (key) {
                var value = url.searchParams.get(key);
                if (value) {
                    exportUrl.searchParams.set(key, value);
                } else {
                    exportUrl.searchParams.delete(key);
                }
            });

            exportUrl.searchParams.delete(pageParam);
            exportLink.href = exportUrl.toString();
        }

        function refreshTable(delay) {
            clearTimeout(timer);
            timer = setTimeout(function () {
                var tableId = form.dataset.tableId;
                var pageParam = form.dataset.pageParam;
                var url = new URL(window.location.href);
                var formData = new FormData(form);

                formData.forEach(function (value, key) {
                    if (value) {
                        url.searchParams.set(key, value);
                    } else {
                        url.searchParams.delete(key);
                    }
                });

                url.searchParams.delete(pageParam);

                if (controller) {
                    controller.abort();
                }

                controller = new AbortController();

                fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: controller.signal
                })
                    .then(function (response) {
                        return response.text();
                    })
                    .then(function (html) {
                        var documentFragment = new DOMParser().parseFromString(html, 'text/html');
                        var nextBody = documentFragment.getElementById(tableId + '_body');
                        var nextPagination = documentFragment.getElementById(tableId + '_pagination_wrapper');
                        var body = document.getElementById(tableId + '_body');
                        var pagination = document.getElementById(tableId + '_pagination_wrapper');

                        if (nextBody && body) {
                            body.innerHTML = nextBody.innerHTML;
                        }

                        if (nextPagination && pagination) {
                            pagination.innerHTML = nextPagination.innerHTML;
                        }

                        window.history.replaceState({}, '', url.toString());
                        updateExportLink(summaryExportLink, url, 'summary_page');
                        updateExportLink(studentsExportLink, url, 'students_page');
                    })
                    .catch(function (error) {
                        if (error.name !== 'AbortError') {
                            console.error(error);
                        }
                    });
            }, delay);
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            refreshTable(0);
        });

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                refreshTable(400);
            });
        }

        form.querySelectorAll('select').forEach(function (select) {
            select.addEventListener('change', function () {
                refreshTable(0);
            });
        });

        var resetButton = form.querySelector('.transport-table-reset');
        if (resetButton) {
            resetButton.addEventListener('click', function () {
                ['student_driver', 'student_bus_amount', 'student_bus_no', 'students_search'].forEach(function (name) {
                    var field = form.querySelector('[name="' + name + '"]');
                    if (field) {
                        field.value = '';
                    }
                });

                refreshTable(0);
            });
        }
    });
</script>
@endsection
