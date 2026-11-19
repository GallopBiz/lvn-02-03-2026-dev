@extends('backend.layouts.main')

@section('main-container')
<style type="text/css">
   /* Sticky table container and header */
   .table-responsive.table-sticky-container {
      max-height: 72vh;
      min-height: 380px;
      overflow-y: auto;
      overflow-x: auto;
      position: relative;
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      background: #fff;
   }

   #zero_configuration_table {
      border-collapse: separate !important;
      border-spacing: 0;
      width: 100% !important;
      margin-bottom: 0 !important;
   }

   #zero_configuration_table thead th {
      position: sticky !important;
      top: 0 !important;
      z-index: 100 !important;
      background: #f1f3f7 !important;
      color: #333333 !important;
      font-weight: 700 !important;
      border-top: 1px solid #d1d5db !important;
      border-bottom: 2px solid #bbc1cb !important;
      border-right: 1px solid #e5e7eb !important;
      white-space: nowrap !important;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
      vertical-align: middle !important;
      padding: 10px 12px !important;
   }

   #zero_configuration_table tbody td {
      white-space: nowrap !important;
      vertical-align: middle !important;
      padding: 9px 12px !important;
      border-bottom: 1px solid #e5e7eb !important;
      border-right: 1px solid #f1f3f7 !important;
   }

   /* Increase width of the S.No / first column so numbers are not cramped */
   #zero_configuration_table th.sno-column,
   #zero_configuration_table td.sno-column,
   #zero_configuration_table th:first-child,
   #zero_configuration_table td:first-child,
   #zero_configuration_table thead th.sorting_asc:first-child,
   #zero_configuration_table thead th.sorting_desc:first-child,
   #zero_configuration_table td.sorting_1:first-child {
      min-width: 85px !important;
      width: 85px !important;
      max-width: 95px !important;
      text-align: center !important;
      padding-left: 12px !important;
      padding-right: 12px !important;
   }

   #zero_configuration_table tbody tr:hover {
      background-color: #f8fafc !important;
   }

   #zero_configuration_table_filter {
      display: none !important;
   }

   .student-search-box {
      min-width: 300px;
      max-width: 520px;
   }

   .student-search-box .input-group-text {
      background-color: #f8fafc;
      font-weight: 600;
      color: #4f46e5;
   }
</style>
<div class="main-content pt-4">
    <div class="breadcrumb">
        <h1 class="me-2">Archived Students</h1>
    </div>
    <div class="separator-breadcrumb border-top"></div>

    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card text-start">
                <div class="card-body">
                    <!-- Card Header: Title & Action Buttons -->
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title mb-0 me-2">Archived Students</h4>
                            <span class="badge bg-secondary text-white border">{{ count($all_inquiry) }} Archived Students</span>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <a href="{{ route('student-registrations') }}" class="btn btn-outline-secondary btn-sm">Registration List</a>
                            <a href="{{ route('student-registrations') }}" class="btn btn-outline-primary btn-sm">Active Students</a>
                        </div>
                    </div>

                    <!-- Live Search Bar: Arranged properly above table -->
                    <div class="row align-items-center mb-3">
                        <div class="col-12 col-md-8 col-lg-6">
                            <div class="input-group student-search-box shadow-sm">
                                <span class="input-group-text">
                                    <i class="i-Search-on-Cloud me-1"></i> Search
                                </span>
                                <input type="text" id="live_student_search" class="form-control" placeholder="Search Student Name, Parent Name, Scholar No, Mobile No..." autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="clear_live_student_search" title="Clear Search">
                                    Clear
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                <i class="i-Information me-1 text-primary"></i> Instant search as you type: Student Name, Parent Name, Scholar No., Mobile No.
                            </small>
                        </div>
                    </div>

                    <div class="table-responsive table-sticky-container">
                        <table class="display table table-striped table-bordered" id="zero_configuration_table" style="width: 100%">
                            <thead>
                                <tr>
                                    <th class="sno-column">S.No.</th>
                                    <th>Form No.</th>
                                    <th>Scholar No</th>
                                    <th>Student Name</th>
                                    <th>Father Name</th>
                                    <th>Class Name</th>
                                    <th>Section</th>
                                    <th>DOB</th>
                                    <th>Current Session</th>
                                    <th>Admission Session</th>
                                    <th>Batch</th>
                                    <th>Mobile Number</th>
                                    <th>Archived Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($all_inquiry as $each_inq)
                                    <?php
                                        $notificationData1 = json_decode($each_inq->json_str, true) ?: [];
                                        $rawCurrent = $each_inq->session_name ?: (session('db_names') ?: '2025_2026');
                                        $currentSessionDisplay = str_replace('_', '-', $rawCurrent);

                                        $isNextYear = !empty($notificationData1['next_year']) && ($notificationData1['next_year'] == 1 || $notificationData1['next_year'] === '1');
                                        $rawAdmission = $notificationData1['admission_session'] ?? ($notificationData1['intended_session'] ?? null);

                                        if ($isNextYear && empty($rawAdmission)) {
                                            $parts = preg_split('/[_-]/', $rawCurrent);
                                            if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                                                $rawAdmission = ((int)$parts[0] + 1) . '-' . ((int)$parts[1] + 1);
                                            }
                                        }
                                        if (empty($rawAdmission)) {
                                            $rawAdmission = $rawCurrent;
                                        }
                                        $admissionSessionDisplay = str_replace('_', '-', $rawAdmission);

                                        $rawBatch = $notificationData1['batch'] ?? $rawAdmission;
                                        $batchDisplay = str_replace('_', '-', $rawBatch);

                                        $isUpcoming = ($isNextYear || $admissionSessionDisplay !== $currentSessionDisplay);
                                    ?>
                                    <tr>
                                        <td class="sno-column">{{ $loop->iteration }}</td>
                                        <td>{{ $each_inq->form_number }}</td>
                                        <td>{{ $each_inq->scholar_no }}</td>
                                        <td>{{ ucwords($each_inq->student_name ?? '') }}</td>
                                        <td>
                                            <?php
                                                $fatherName = ucwords($notificationData1['student_father_name'] ?? $notificationData1['fathername'] ?? '');
                                                $motherName = ucwords($notificationData1['mothername'] ?? $notificationData1['mother_name'] ?? '');
                                            ?>
                                            {{ !empty($fatherName) ? $fatherName : (!empty($motherName) ? $motherName : '-') }}
                                            @if(!empty($motherName))
                                                <span class="d-none search-parent-mother">{{ $motherName }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $each_inq->class_name }}</td>
                                        <td>{{ !empty($each_inq->section_name) ? $each_inq->section_name : (!empty($notificationData1['section_name']) ? $notificationData1['section_name'] : '-') }}</td>
                                        <td>{{ !empty($each_inq->date_of_birth) ? date('d-m-Y', strtotime($each_inq->date_of_birth)) : '' }}</td>
                                        <td>{{ $currentSessionDisplay }}</td>
                                        <td>
                                            {{ $admissionSessionDisplay }}
                                            @if($isUpcoming)
                                                <span class="badge bg-success text-white ms-1" style="font-size: 11px;">Next Year</span>
                                            @endif
                                        </td>
                                        <td>{{ $batchDisplay }}</td>
                                        <td>
                                            <?php
                                                $primaryMobile = $notificationData1['father_mobile'] ?? $each_inq->mobile_number ?? '';
                                                $altMobile = $notificationData1['mother_mobile'] ?? '';
                                            ?>
                                            {{ $primaryMobile ?: '-' }}
                                            @if(!empty($altMobile) && $altMobile != $primaryMobile)
                                                <span class="d-none search-alt-mobile">{{ $altMobile }}</span>
                                            @endif
                                        </td>
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
                                    <tr><td colspan="14" class="text-center">No archived students found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        function applyLiveStudentSearch(query) {
            query = (query || '').trim();
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#zero_configuration_table')) {
                $('#zero_configuration_table').DataTable().search(query).draw();
            } else {
                var tokens = query.toLowerCase().split(/\s+/).filter(Boolean);
                $('#zero_configuration_table tbody tr').each(function () {
                    var row = $(this);
                    if (row.find('td').length <= 1) {
                        return;
                    }
                    if (tokens.length === 0) {
                        row.show();
                        return;
                    }
                    var rowText = row.text().toLowerCase();
                    var matches = tokens.every(function (t) {
                        return rowText.indexOf(t) > -1;
                    });
                    row.toggle(matches);
                });
            }
        }

        $(document).on('keyup input', '#live_student_search', function () {
            applyLiveStudentSearch($(this).val());
        });

        $(document).on('click', '#clear_live_student_search', function () {
            $('#live_student_search').val('');
            applyLiveStudentSearch('');
            $('#live_student_search').focus();
        });

        function keepLatestFirst() {
            if (!$.fn.DataTable || !$('#zero_configuration_table').length) {
                return;
            }

            var currentSearch = $('#live_student_search').val() || '';

            if ($.fn.DataTable.isDataTable('#zero_configuration_table')) {
                var dt = $('#zero_configuration_table').DataTable();
                dt.order([0, 'asc']);
                if (currentSearch) {
                    dt.search(currentSearch);
                }
                dt.draw();
                return;
            }

            var dt = $('#zero_configuration_table').DataTable({
                order: [[0, 'asc']]
            });
            if (currentSearch) {
                dt.search(currentSearch).draw();
            }
        }

        keepLatestFirst();
        setTimeout(keepLatestFirst, 500);
    });
</script>
@endsection
