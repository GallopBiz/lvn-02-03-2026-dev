@extends('backend.layouts.main')
@section('main-container')

<div class="main-content" id="print-area">

    {{-- Official Print Header (Visible ONLY on printouts) --}}
    <div class="print-only-header d-none">
        <div style="text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 8px; margin-bottom: 12px;">
            <h2 style="font-size: 19px; font-weight: 700; margin: 0; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">Lokmanya Vidya Niketan</h2>
            <h4 style="font-size: 13.5px; font-weight: 600; margin: 4px 0 0; color: #4338ca; text-transform: uppercase;">
                @if($filters['report_type'] === 'employee_monthly')
                    Employee-Wise Monthly Leave Analysis @if($selectedEmployee) &bull; {{ $selectedEmployee->first_name }} {{ $selectedEmployee->last_name }} ({{ optional($selectedEmployee->biometricDetail)->ess_emp_code ?: 'N/A' }}) @endif
                @elseif($filters['report_type'] === 'all_monthly')
                    All Employees Monthly Leave Summary (Org-Wide)
                @else
                    Advance Leave Report &bull; Detailed Records
                @endif
            </h4>
            <div style="font-size: 10px; color: #64748b; margin-top: 4px;">
                HRMS Academic Session: {{ reset($sessionMonths)['label'] }} to {{ end($sessionMonths)['label'] }} &bull; Printed On: {{ date('d-m-Y h:i A') }}
            </div>
        </div>
    </div>

    {{-- Title & Header Actions --}}
    <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
        <div>
            <h1 class="font-weight-bold mb-0 text-dark" style="font-size: 22px;">
                <i class="i-Calendar-4 text-primary mr-2"></i> Advance Leave Report
            </h1>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2 mt-2 mt-md-0 no-print">
            {{-- Print Button --}}
            <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold mr-2 shadow-sm" onclick="printCurrentView();" title="Print currently viewed report">
                <i class="i-Billing mr-1"></i> Print Current View
            </button>
            <a href="{{ route('advance.leave.report', array_merge(request()->query(), ['print' => 1])) }}" target="_blank" class="btn btn-primary btn-sm font-weight-bold mr-2 shadow-sm" title="Open complete printable report">
                <i class="i-Printer mr-1"></i> Print Complete Report
            </a>
            {{-- CSV Export Button --}}
            <a href="{{ route('advance.leave.report.csv', request()->query()) }}" class="btn btn-success btn-sm font-weight-bold shadow-sm" title="Export report to CSV">
                <i class="i-File-CSV mr-1"></i> Export to CSV
            </a>
        </div>
    </div>

    <div class="separator-breadcrumb border-top mb-4"></div>

    {{-- Filter & Report Type Selector Card --}}
    <div class="card mb-4 border shadow-sm no-print" style="border-radius: 10px; border-color: #e2e8f0 !important; background-color: #ffffff;">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap" style="border-top-left-radius: 10px; border-top-right-radius: 10px; border-bottom-color: #e2e8f0 !important;">
            <div class="d-flex align-items-center flex-wrap">
                <div class="font-weight-bold text-dark mr-3" style="font-size: 15px;">
                    <i class="i-Filter-2 text-primary mr-1"></i> Report Controls & Filters
                </div>
                {{-- Quick Tab Selector for Report Type --}}
                <div class="btn-group btn-group-sm mt-2 mt-sm-0" role="group">
                    <button type="button" class="btn {{ $filters['report_type'] === 'detailed' ? 'btn-primary' : 'btn-outline-primary' }} font-weight-bold" onclick="switchReportType('detailed')">
                        <i class="i-Receipt mr-1"></i> 1. Detailed Records
                    </button>
                    <button type="button" class="btn {{ $filters['report_type'] === 'employee_monthly' ? 'btn-primary' : 'btn-outline-primary' }} font-weight-bold" onclick="switchReportType('employee_monthly')">
                        <i class="i-Checked-User mr-1"></i> 2. Employee-Wise Monthly
                    </button>
                    <button type="button" class="btn {{ $filters['report_type'] === 'all_monthly' ? 'btn-primary' : 'btn-outline-primary' }} font-weight-bold" onclick="switchReportType('all_monthly')">
                        <i class="i-Bar-Chart mr-1"></i> 3. All Employees Monthly Summary
                    </button>
                </div>
            </div>
            <div class="d-flex align-items-center mt-2 mt-md-0">
                <button class="btn btn-link btn-sm p-0 text-decoration-none text-muted" type="button" data-toggle="collapse" data-target="#filterCollapse" aria-expanded="true">
                    <i class="i-Arrow-Down font-weight-bold"></i>
                </button>
            </div>
        </div>

        <div class="collapse show" id="filterCollapse">
            <div class="card-body p-4" style="background-color: #f8fafc; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px; border-top: 1px solid #edf2f7;">
                <form method="GET" action="{{ route('advance.leave.report') }}" id="advanceLeaveFilterForm">
                    <input type="hidden" name="report_type" id="report_type_input" value="{{ $filters['report_type'] }}">

                    <div class="row">

                        {{-- Report Type Dropdown --}}
                        <div class="col-lg-3 col-md-6 form-group mb-3">
                            <label class="form-label font-weight-bold text-dark small mb-1">
                                <i class="i-Receipt text-primary mr-1"></i> Report Type
                            </label>
                            <select name="report_type_select" id="report_type_select" class="form-control form-control-sm bg-white font-weight-bold" onchange="switchReportType(this.value)">
                                <option value="detailed" {{ $filters['report_type'] === 'detailed' ? 'selected' : '' }}>1. Detailed Leave Report</option>
                                <option value="employee_monthly" {{ $filters['report_type'] === 'employee_monthly' ? 'selected' : '' }}>2. Employee-Wise Monthly Analysis</option>
                                <option value="all_monthly" {{ $filters['report_type'] === 'all_monthly' ? 'selected' : '' }}>3. All Employees Monthly Summary</option>
                            </select>
                        </div>

                        {{-- Academic Session Year --}}
                        <div class="col-lg-3 col-md-6 form-group mb-3">
                            <label class="form-label font-weight-bold text-dark small mb-1">
                                <i class="i-Calendar-4 text-primary mr-1"></i> HRMS Session (June–May)
                            </label>
                            <select name="session_year" id="session_year" class="form-control form-control-sm bg-white font-weight-bold" onchange="document.getElementById('advanceLeaveFilterForm').submit();">
                                <option value="2025_2026" {{ $filters['session_year'] === '2025_2026' ? 'selected' : '' }}>Session 2025 - 2026 (June 2025 – May 2026)</option>
                                <option value="2026_2027" {{ $filters['session_year'] === '2026_2027' ? 'selected' : '' }}>Session 2026 - 2027 (June 2026 – May 2027)</option>
                                <option value="2024_2025" {{ $filters['session_year'] === '2024_2025' ? 'selected' : '' }}>Session 2024 - 2025 (June 2024 – May 2025)</option>
                                <option value="2023_2024" {{ $filters['session_year'] === '2023_2024' ? 'selected' : '' }}>Session 2023 - 2024 (June 2023 – May 2024)</option>
                            </select>
                        </div>

                        {{-- Staff Name (Employee) --}}
                        <div class="col-lg-3 col-md-6 form-group mb-3 {{ $filters['report_type'] === 'all_monthly' ? 'd-none' : '' }}">
                            <label class="form-label font-weight-bold text-dark small mb-1">
                                <i class="i-Checked-User text-primary mr-1"></i> Staff Name
                                @if($filters['report_type'] === 'employee_monthly')
                                    <span class="text-danger">*</span>
                                @endif
                            </label>
                            <select name="employee_id" id="employee_id" class="form-control form-control-sm bg-white select2">
                                @if($filters['report_type'] !== 'employee_monthly')
                                    <option value="">All Staff Members</option>
                                @endif
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ (string)$filters['employee_id'] === (string)$emp->id ? 'selected' : '' }}>
                                        {{ $emp->first_name }} {{ $emp->last_name }} 
                                        @if(!empty(optional($emp->biometricDetail)->ess_emp_code))
                                            (Code: {{ $emp->biometricDetail->ess_emp_code }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Staff Type --}}
                        <div class="col-lg-3 col-md-6 form-group mb-3 {{ $filters['report_type'] === 'employee_monthly' ? 'd-none' : '' }}">
                            <label class="form-label font-weight-bold text-dark small mb-1">
                                <i class="i-ID-Card text-primary mr-1"></i> Staff Type
                            </label>
                            <select name="staff_type_id" id="staff_type_id" class="form-control form-control-sm bg-white">
                                <option value="">All Staff Types</option>
                                @foreach($staffTypes as $st)
                                    <option value="{{ $st->id }}" {{ (string)$filters['staff_type_id'] === (string)$st->id ? 'selected' : '' }}>
                                        {{ $st->staff_type_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Leave Type --}}
                        <div class="col-lg-3 col-md-6 form-group mb-3">
                            <label class="form-label font-weight-bold text-dark small mb-1">
                                <i class="i-Tag text-primary mr-1"></i> Leave Type
                            </label>
                            <select name="leave_type_id" id="leave_type_id" class="form-control form-control-sm bg-white">
                                <option value="">All Leave Types</option>
                                @foreach($leaveTypes as $lt)
                                    <option value="{{ $lt->id }}" {{ (string)$filters['leave_type_id'] === (string)$lt->id ? 'selected' : '' }}>
                                        {{ $lt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Leave Status --}}
                        <div class="col-lg-3 col-md-6 form-group mb-3">
                            <label class="form-label font-weight-bold text-dark small mb-1">
                                <i class="i-Check text-primary mr-1"></i> Leave Status
                            </label>
                            <select name="status" id="status" class="form-control form-control-sm bg-white">
                                <option value="">All Statuses (Approved, Pending, Rejected)</option>
                                <option value="Approved" {{ $filters['status'] === 'Approved' ? 'selected' : '' }}>Approved Only</option>
                                <option value="Pending" {{ $filters['status'] === 'Pending' ? 'selected' : '' }}>Pending Only</option>
                                <option value="Rejected" {{ $filters['status'] === 'Rejected' ? 'selected' : '' }}>Rejected Only</option>
                            </select>
                        </div>

                        {{-- Custom Date Range: From Date --}}
                        <div class="col-lg-3 col-md-6 form-group mb-3">
                            <label class="form-label font-weight-bold text-dark small mb-1">
                                <i class="i-Calendar text-primary mr-1"></i> Custom From Date
                            </label>
                            <input type="date" name="from_date" id="from_date" class="form-control form-control-sm bg-white" value="{{ $filters['from_date'] }}">
                        </div>

                        {{-- Custom Date Range: To Date --}}
                        <div class="col-lg-3 col-md-6 form-group mb-3">
                            <label class="form-label font-weight-bold text-dark small mb-1">
                                <i class="i-Calendar text-primary mr-1"></i> Custom To Date
                            </label>
                            <input type="date" name="to_date" id="to_date" class="form-control form-control-sm bg-white" value="{{ $filters['to_date'] }}">
                        </div>

                        {{-- Leave Request Date (Only for Detailed Report) --}}
                        @if($filters['report_type'] === 'detailed')
                            <div class="col-lg-3 col-md-6 form-group mb-3">
                                <label class="form-label font-weight-bold text-dark small mb-1">
                                    <i class="i-Stopwatch text-primary mr-1"></i> Leave Request Date
                                </label>
                                <input type="date" name="request_date" id="request_date" class="form-control form-control-sm bg-white" value="{{ $filters['request_date'] }}" title="Filter by date requested">
                            </div>
                        @endif

                        {{-- General Keyword Search --}}
                        <div class="col-lg-3 col-md-6 form-group mb-3">
                            <label class="form-label font-weight-bold text-dark small mb-1">
                                <i class="i-Magnifi-Glass2 text-primary mr-1"></i> Search Keyword
                            </label>
                            <input type="text" name="search" id="search" class="form-control form-control-sm bg-white" placeholder="Staff name, ESS code, or reason..." value="{{ $filters['search'] }}">
                        </div>

                    </div>

                    {{-- Form Footer: Actions --}}
                    <div class="d-flex align-items-center justify-content-between flex-wrap pt-2 border-top mt-2">
                        <div class="d-flex align-items-center flex-wrap gap-1 mb-2 mb-md-0">
                            <small class="text-muted mr-2 font-weight-bold">Active Academic Window:</small>
                            <span class="badge badge-light border text-primary font-weight-bold px-2 py-1">
                                <i class="i-Calendar mr-1"></i> {{ reset($sessionMonths)['label'] }} to {{ end($sessionMonths)['label'] }} ({{ count($sessionMonths) }} Months)
                            </span>
                        </div>

                        <div class="d-flex align-items-center">
                            @if($filters['report_type'] === 'detailed' || $filters['report_type'] === 'all_monthly')
                                <div class="d-flex align-items-center mr-3">
                                    <small class="text-muted mr-1">Show:</small>
                                    <select name="per_page" class="form-control form-control-sm bg-white" style="width: 80px;" onchange="document.getElementById('advanceLeaveFilterForm').submit();">
                                        <option value="25" {{ (string)$filters['per_page'] === '25' ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ (string)$filters['per_page'] === '50' ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ (string)$filters['per_page'] === '100' ? 'selected' : '' }}>100</option>
                                        <option value="all" {{ (string)$filters['per_page'] === 'all' ? 'selected' : '' }}>All</option>
                                    </select>
                                </div>
                            @endif

                            <button type="submit" class="btn btn-primary btn-sm px-3 font-weight-bold mr-2 shadow-sm">
                                <i class="i-Filter-2 mr-1"></i> Apply Filters
                            </button>
                            <a href="{{ route('advance.leave.report', ['report_type' => $filters['report_type']]) }}" class="btn btn-secondary btn-sm px-3 font-weight-bold">
                                <i class="i-Repeat mr-1"></i> Reset
                            </a>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- REPORT TYPE 1: DETAILED LEAVE REPORT                                      --}}
    {{-- ========================================================================= --}}
    @if($filters['report_type'] === 'detailed')

        {{-- KPI Cards --}}
        <div class="row mb-4 no-print">
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3 mb-lg-0">
                <div class="card border shadow-xs h-100" style="border-radius: 8px; border-color: #e2e8f0 !important; background: #ffffff;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="d-block text-muted text-uppercase font-weight-bold mb-1" style="font-size: 10.5px; letter-spacing: 0.5px;">
                                Total Requests
                            </span>
                            <span class="d-block font-weight-bold text-dark" style="font-size: 20px; line-height: 1.2;">
                                {{ number_format($stats['total_requests']) }}
                            </span>
                            <small class="text-muted" style="font-size: 11px;">Applications</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background-color: #eff6ff; color: #2563eb;">
                            <i class="i-Calendar-4" style="font-size: 16px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6 mb-3 mb-lg-0">
                <div class="card border shadow-xs h-100" style="border-radius: 8px; border-color: #e2e8f0 !important; background: #ffffff;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="d-block text-muted text-uppercase font-weight-bold mb-1" style="font-size: 10.5px; letter-spacing: 0.5px;">
                                Approved
                            </span>
                            <span class="d-block font-weight-bold text-success" style="font-size: 20px; line-height: 1.2;">
                                {{ number_format($stats['approved']) }}
                            </span>
                            <small class="text-muted" style="font-size: 11px;">Granted</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background-color: #ecfdf5; color: #059669;">
                            <i class="i-Yes" style="font-size: 16px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6 mb-3 mb-lg-0">
                <div class="card border shadow-xs h-100" style="border-radius: 8px; border-color: #e2e8f0 !important; background: #ffffff;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="d-block text-muted text-uppercase font-weight-bold mb-1" style="font-size: 10.5px; letter-spacing: 0.5px;">
                                Pending
                            </span>
                            <span class="d-block font-weight-bold text-warning" style="font-size: 20px; line-height: 1.2; color: #d97706 !important;">
                                {{ number_format($stats['pending']) }}
                            </span>
                            <small class="text-muted" style="font-size: 11px;">Awaiting review</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background-color: #fffbeb; color: #d97706;">
                            <i class="i-Clock" style="font-size: 16px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-2 col-md-4 col-sm-6 mb-3 mb-lg-0">
                <div class="card border shadow-xs h-100" style="border-radius: 8px; border-color: #e2e8f0 !important; background: #ffffff;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="d-block text-muted text-uppercase font-weight-bold mb-1" style="font-size: 10.5px; letter-spacing: 0.5px;">
                                Rejected
                            </span>
                            <span class="d-block font-weight-bold text-danger" style="font-size: 20px; line-height: 1.2;">
                                {{ number_format($stats['rejected']) }}
                            </span>
                            <small class="text-muted" style="font-size: 11px;">Declined</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background-color: #fef2f2; color: #dc2626;">
                            <i class="i-Close" style="font-size: 16px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-8 col-sm-12 mb-3 mb-lg-0">
                <div class="card border shadow-xs h-100" style="border-radius: 8px; border-color: #e2e8f0 !important; background: #ffffff;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="d-block text-muted text-uppercase font-weight-bold mb-1" style="font-size: 10.5px; letter-spacing: 0.5px;">
                                Total Leave Days
                            </span>
                            <span class="d-block font-weight-bold" style="font-size: 20px; line-height: 1.2; color: #4f46e5;">
                                {{ number_format($stats['total_days'], 1) }} <small class="text-muted font-weight-normal" style="font-size: 12.5px;">Days</small>
                            </span>
                            <small class="text-muted" style="font-size: 11px;">Cumulative filtered duration</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background-color: #f5f3ff; color: #7c3aed;">
                            <i class="i-Over-Time" style="font-size: 18px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Detailed Table Card --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
            <div class="card-body p-0">
                <div class="table-responsive table-sticky-container detailed-sticky-container">
                    <table class="table table-hover table-striped mb-0 text-left align-middle detailed-table-matrix" style="font-size: 13px;">
                        <thead style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569;">
                            <tr>
                                <th style="width: 50px;" class="text-center py-3 detailed-sticky-sr">#</th>
                                <th style="width: 220px; min-width: 220px;" class="py-3 detailed-sticky-name">Staff Name</th>
                                <th style="min-width: 120px;" class="py-3">Staff Type</th>
                                <th style="min-width: 140px;" class="py-3">Leave Type</th>
                                <th style="min-width: 110px;" class="py-3">From Date</th>
                                <th style="min-width: 110px;" class="py-3">To Date</th>
                                <th style="min-width: 110px;" class="text-center py-3">Total Days</th>
                                <th style="min-width: 130px;" class="py-3">Request Date</th>
                                <th style="min-width: 110px;" class="text-center py-3">Approval Status</th>
                                <th style="min-width: 200px;" class="py-3">Reason / Remarks</th>
                                <th style="width: 80px;" class="text-center py-3 no-print">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaveRequests as $index => $req)
                                @php
                                    $emp = $req->employee;
                                    $empName = $emp ? trim($emp->first_name . ' ' . $emp->last_name) : 'Unknown Staff';
                                    $essCode = optional(optional($emp)->biometricDetail)->ess_emp_code;
                                    $staffType = optional(optional($emp)->staffType)->staff_type_name ?: 'N/A';
                                    $leaveTypeName = optional($req->leaveType)->name ?: 'Leave';
                                    $status = $req->status ?: 'Pending';
                                    $rowNumber = method_exists($leaveRequests, 'firstItem') ? ($leaveRequests->firstItem() + $index) : ($index + 1);
                                @endphp
                                <tr>
                                    <td class="text-center font-weight-bold text-muted detailed-sticky-sr">{{ $rowNumber }}</td>
                                    <td class="detailed-sticky-name">
                                        <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ $empName }}</div>
                                        <div class="text-muted small">
                                            @if($essCode) <span class="mr-2"><i class="i-ID-Card mr-1"></i>Code: <strong>{{ $essCode }}</strong></span> @endif
                                            @if(optional(optional($emp)->department)->department_name)
                                                <span class="badge badge-light bg-light text-dark border px-1.5 py-0.5" style="font-size: 10px; background-color: #f1f5f9 !important; color: #334155 !important; border: 1px solid #cbd5e1 !important; display: inline-block;">{{ $emp->department->department_name }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $stLower = strtolower($staffType);
                                            if (str_contains($stLower, 'permanent')) {
                                                $bBg = '#e0e7ff'; $bCol = '#3730a3'; $bBrd = '#c7d2fe';
                                            } elseif (str_contains($stLower, 'temporary')) {
                                                $bBg = '#fef3c7'; $bCol = '#92400e'; $bBrd = '#fde68a';
                                            } elseif (str_contains($stLower, 'guest')) {
                                                $bBg = '#f1f5f9'; $bCol = '#334155'; $bBrd = '#cbd5e1';
                                            } else {
                                                $bBg = '#e2e8f0'; $bCol = '#1e293b'; $bBrd = '#cbd5e1';
                                            }
                                        @endphp
                                        <span class="badge px-2 py-1 font-weight-bold" style="background-color: {{ $bBg }}; color: {{ $bCol }}; border: 1px solid {{ $bBrd }}; font-size: 11px; letter-spacing: 0.2px; display: inline-block;">
                                            {{ $staffType }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-light bg-light border px-2 py-1 font-weight-bold" style="background-color: #f1f5f9 !important; color: #1e293b !important; border: 1px solid #cbd5e1 !important; font-size: 11.5px; display: inline-block;">
                                            {{ $leaveTypeName }}
                                        </span>
                                    </td>
                                    <td class="font-weight-bold text-dark">{{ !empty($req->start_date) ? date('d-m-Y', strtotime($req->start_date)) : '-' }}</td>
                                    <td class="font-weight-bold text-dark">{{ !empty($req->end_date) ? date('d-m-Y', strtotime($req->end_date)) : '-' }}</td>
                                    <td class="text-center">
                                        @if($req->total_days == 0.5)
                                            <span class="badge badge-warning bg-warning px-2 py-1 font-weight-bold" style="color: #92400e !important; background-color: #fef3c7 !important; border: 1px solid #fde68a !important; display: inline-block;">0.5 Day</span>
                                        @else
                                            <span class="badge badge-primary bg-primary font-weight-bold px-2 py-1 text-white" style="color: #ffffff !important; background-color: #4f46e5 !important; font-size: 11.5px; display: inline-block;">{{ $req->total_days }} Days</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">
                                        <div class="font-weight-bold text-dark">{{ $req->created_at ? $req->created_at->format('d-m-Y') : '-' }}</div>
                                        <div style="font-size: 11px;">{{ $req->created_at ? $req->created_at->format('h:i A') : '' }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if(strtolower($status) === 'approved')
                                            <span class="badge badge-success bg-success px-2 py-1 font-weight-bold text-white" style="background-color: #16a34a !important; color: #ffffff !important; font-size: 11.5px; display: inline-block;"><i class="i-Yes mr-1"></i> Approved</span>
                                        @elseif(strtolower($status) === 'rejected')
                                            <span class="badge badge-danger bg-danger px-2 py-1 font-weight-bold text-white" style="background-color: #dc2626 !important; color: #ffffff !important; font-size: 11.5px; display: inline-block;"><i class="i-Close mr-1"></i> Rejected</span>
                                        @else
                                            <span class="badge badge-warning bg-warning px-2 py-1 font-weight-bold text-dark" style="color: #92400e !important; background-color: #fef3c7 !important; border: 1px solid #fde68a !important; font-size: 11.5px; display: inline-block;"><i class="i-Clock mr-1"></i> Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 220px;" title="{{ $req->reason }}">{{ $req->reason ?: '-' }}</span>
                                        @if(!empty($req->approver))
                                            <div class="small text-muted mt-1" style="font-size: 11px;"><i class="i-Administrator mr-1 text-primary"></i>By: {{ $req->approver->first_name }} {{ $req->approver->last_name }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center no-print">
                                        <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2 view-detail-btn" onclick="openDetailModal('{{ $req->id }}')" data-toggle="modal" data-bs-toggle="modal" data-target="#detailModal{{ $req->id }}" data-bs-target="#detailModal{{ $req->id }}" title="View details">
                                            <i class="i-Eye font-weight-bold"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5 text-muted">
                                        <i class="i-File-Search mb-2 d-block" style="font-size: 40px; color: #cbd5e1;"></i>
                                        <div class="font-weight-bold text-dark mb-1">No Leave Records Found</div>
                                        <p class="small text-muted mb-0">Try adjusting your filters or resetting.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Modals rendered outside scroll container to prevent stacking context & backdrop trapping --}}
                @foreach($leaveRequests as $req)
                    @php
                        $mEmp = $req->employee;
                        $mEmpName = $mEmp ? trim($mEmp->first_name . ' ' . $mEmp->last_name) : 'Unknown Staff';
                        $mStaffType = optional(optional($mEmp)->staffType)->staff_type_name ?: 'N/A';
                        $mLeaveTypeName = optional($req->leaveType)->name ?: 'Leave';
                        $mStatus = $req->status ?: 'Pending';
                    @endphp
                    <div class="modal fade" id="detailModal{{ $req->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content text-left border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                                <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: #ffffff; border-bottom: none;">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center mr-2.5 shadow-xs" style="width: 32px; height: 32px; background-color: rgba(255,255,255,0.15); color: #c7d2fe;">
                                            <i class="i-Receipt font-weight-bold" style="font-size: 15px;"></i>
                                        </div>
                                        <div>
                                            <h5 class="modal-title font-weight-bold text-white mb-0" style="font-size: 15.5px;">
                                                Leave Request Details <span style="color: #a5b4fc; font-weight: 500;">#{{ $req->id }}</span>
                                            </h5>
                                            <small style="color: #cbd5e1; font-size: 10.5px;">HRMS Academic Session {{ reset($sessionMonths)['label'] }} to {{ end($sessionMonths)['label'] }}</small>
                                        </div>
                                    </div>
                                    <button type="button" class="close text-white" onclick="closeDetailModal('{{ $req->id }}')" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.85; text-shadow: none; font-size: 24px; font-weight: 300; outline: none;">&times;</button>
                                </div>
                                <div class="modal-body p-4" style="background-color: #ffffff;">
                                    <div class="row mb-2">
                                        <div class="col-6 mb-2">
                                            <div class="p-2.5 rounded border" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                                <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.3px;">Staff Name</small>
                                                <div class="font-weight-bold text-dark" style="font-size: 13.5px;">{{ $mEmpName }}</div>
                                                @if(optional(optional($mEmp)->biometricDetail)->ess_emp_code)
                                                    <small class="text-muted" style="font-size: 10.5px;">Code: <strong>{{ $mEmp->biometricDetail->ess_emp_code }}</strong></small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <div class="p-2.5 rounded border" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                                <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.3px;">Staff Type & Dept</small>
                                                <div class="font-weight-bold text-dark" style="font-size: 13px;">{{ $mStaffType }}</div>
                                                <small class="text-muted" style="font-size: 10.5px;">{{ optional(optional($mEmp)->department)->department_name ?: 'N/A' }}</small>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <div class="p-2.5 rounded border" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                                <small class="text-muted d-block text-uppercase font-weight-bold mb-1" style="font-size: 9.5px; letter-spacing: 0.3px;">Leave Type</small>
                                                <span class="badge badge-light bg-light border px-2 py-1 font-weight-bold" style="background-color: #eff6ff !important; color: #1d4ed8 !important; border-color: #bfdbfe !important; font-size: 11.5px;">
                                                    {{ $mLeaveTypeName }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-6 mb-2">
                                            <div class="p-2.5 rounded border" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                                <small class="text-muted d-block text-uppercase font-weight-bold mb-1" style="font-size: 9.5px; letter-spacing: 0.3px;">Status</small>
                                                @if(strtolower($mStatus) === 'approved')
                                                    <span class="badge px-2 py-1 font-weight-bold" style="background-color: #16a34a; color: #fff; font-size: 11px;"><i class="i-Yes mr-1"></i> Approved</span>
                                                @elseif(strtolower($mStatus) === 'rejected')
                                                    <span class="badge px-2 py-1 font-weight-bold" style="background-color: #dc2626; color: #fff; font-size: 11px;"><i class="i-Close mr-1"></i> Rejected</span>
                                                @else
                                                    <span class="badge px-2 py-1 font-weight-bold" style="background-color: #f59e0b; color: #fff; font-size: 11px;"><i class="i-Clock mr-1"></i> Pending</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2.5 rounded border" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                                <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9.5px; letter-spacing: 0.3px;">Leave Duration</small>
                                                <div class="font-weight-bold text-dark" style="font-size: 12.5px;">
                                                    {{ date('d-m-Y', strtotime($req->start_date)) }} <span class="text-muted font-weight-normal">to</span> {{ date('d-m-Y', strtotime($req->end_date)) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2.5 rounded border" style="background-color: #eef2ff; border-color: #c7d2fe !important;">
                                                <small class="d-block text-uppercase font-weight-bold" style="font-size: 9.5px; color: #4338ca; letter-spacing: 0.3px;">Total Days</small>
                                                <div class="font-weight-bold" style="font-size: 15px; color: #4338ca;">
                                                    {{ $req->total_days }} {{ $req->total_days == 1 ? 'Day' : 'Days' }}
                                                    @if($req->is_half_day == 1) <small class="text-muted font-weight-normal font-italic" style="font-size: 11px;">({{ ucfirst($req->half_day_type ?: 'Half') }})</small> @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Reason Card with Professional Indigo Bar --}}
                                    <div class="p-3 rounded mt-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #4f46e5;">
                                        <small class="text-uppercase font-weight-bold d-block mb-1" style="font-size: 10px; color: #4338ca; letter-spacing: 0.5px;">
                                            <i class="i-Speach-Bubble-Dialog mr-1"></i> Reason / Purpose of Leave:
                                        </small>
                                        <p class="mb-0 text-dark" style="font-size: 13px; font-weight: 500; line-height: 1.5; white-space: pre-wrap;">{{ $req->reason ?: 'No reason provided.' }}</p>
                                    </div>
                                </div>
                                <div class="modal-footer py-2.5 px-4" style="background-color: #ffffff; border-top: 1px solid #e2e8f0;">
                                    <div class="d-flex align-items-center justify-content-between w-100">
                                        <small class="text-muted" style="font-size: 11px;">
                                            @if(!empty($req->approver))
                                                <i class="i-Administrator text-primary mr-1"></i> Actioned By: <strong>{{ $req->approver->first_name }} {{ $req->approver->last_name }}</strong>
                                            @else
                                                <i class="i-Calendar text-muted mr-1"></i> Applied: {{ $req->created_at ? $req->created_at->format('d-m-Y h:i A') : '-' }}
                                            @endif
                                        </small>
                                        <button type="button" class="btn btn-dark btn-sm font-weight-bold px-3 shadow-xs" onclick="closeDetailModal('{{ $req->id }}')" data-dismiss="modal" data-bs-dismiss="modal" style="background-color: #1e293b; border-color: #1e293b; border-radius: 6px;">
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if(method_exists($leaveRequests, 'hasPages') && $leaveRequests->hasPages())
                    <div class="p-3 border-top d-flex align-items-center justify-content-between flex-wrap no-print">
                        <div class="text-muted small">Showing <strong>{{ $leaveRequests->firstItem() }}</strong> to <strong>{{ $leaveRequests->lastItem() }}</strong> of <strong>{{ $leaveRequests->total() }}</strong> records</div>
                        <div>{{ $leaveRequests->links('pagination::bootstrap-4') }}</div>
                    </div>
                @endif
            </div>
        </div>

    {{-- ========================================================================= --}}
    {{-- REPORT TYPE 2: EMPLOYEE-WISE MONTHLY ANALYSIS                             --}}
    {{-- ========================================================================= --}}
    @elseif($filters['report_type'] === 'employee_monthly')

        @if($selectedEmployee)
            {{-- Selected Employee Profile & Stats Header --}}
            <div class="card mb-3 border-0 shadow-sm" style="border-radius: 10px; background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%); border-left: 5px solid #4f46e5 !important;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap">
                    <div class="d-flex align-items-center mb-2 mb-md-0">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white mr-3 shadow-sm font-weight-bold" style="width: 46px; height: 46px; font-size: 18px; background: #4f46e5;">
                            {{ strtoupper(substr($selectedEmployee->first_name, 0, 1) . substr($selectedEmployee->last_name, 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="font-weight-bold text-dark mb-0" style="font-size: 16px;">
                                {{ $selectedEmployee->first_name }} {{ $selectedEmployee->last_name }}
                            </h4>
                            <div class="d-flex align-items-center flex-wrap gap-2 text-muted small mt-1">
                                <span class="mr-3"><i class="i-ID-Card mr-1 text-primary"></i> ESS Code: <strong>{{ optional($selectedEmployee->biometricDetail)->ess_emp_code ?: 'N/A' }}</strong></span>
                                <span class="mr-3"><i class="i-Administrator mr-1 text-primary"></i> Staff Type: <strong>{{ optional($selectedEmployee->staffType)->staff_type_name ?: 'N/A' }}</strong></span>
                                <span><i class="i-Building mr-1 text-primary"></i> Dept: <strong>{{ optional($selectedEmployee->department)->department_name ?: 'N/A' }}</strong></span>
                            </div>
                        </div>
                    </div>

                    {{-- Session Metrics for this Employee --}}
                    <div class="d-flex align-items-center flex-wrap">
                        <div class="bg-white p-2 rounded shadow-sm border text-center mr-2 mb-1" style="min-width: 105px;">
                            <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9px;">Total Leave Days</small>
                            <span class="font-weight-bold" style="font-size: 17px; color: #4f46e5;">{{ number_format($empStats['total_days'], 1) }}</span>
                            <small class="text-muted d-block" style="font-size: 10px;">Days in Session</small>
                        </div>
                        <div class="bg-white p-2 rounded shadow-sm border text-center mr-2 mb-1" style="min-width: 90px; border-top: 3px solid #22c55e !important;">
                            <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9px;">Approved</small>
                            <span class="font-weight-bold text-success" style="font-size: 17px;">{{ number_format($empStats['approved_days'], 1) }}</span>
                            <small class="text-muted d-block" style="font-size: 10px;">Days</small>
                        </div>
                        <div class="bg-white p-2 rounded shadow-sm border text-center mr-2 mb-1" style="min-width: 90px; border-top: 3px solid #f59e0b !important;">
                            <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9px;">Pending</small>
                            <span class="font-weight-bold text-warning" style="font-size: 17px; color: #d97706 !important;">{{ number_format($empStats['pending_days'], 1) }}</span>
                            <small class="text-muted d-block" style="font-size: 10px;">Days</small>
                        </div>
                        <div class="bg-white p-2 rounded shadow-sm border text-center mr-2 mb-1" style="min-width: 90px; border-top: 3px solid #ef4444 !important;">
                            <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9px;">Rejected</small>
                            <span class="font-weight-bold text-danger" style="font-size: 17px; color: #dc2626 !important;">{{ number_format($empStats['rejected_days'], 1) }}</span>
                            <small class="text-muted d-block" style="font-size: 10px;">Days</small>
                        </div>
                        <div class="bg-white p-2 rounded shadow-sm border text-center mb-1" style="min-width: 90px;">
                            <small class="text-muted d-block text-uppercase font-weight-bold" style="font-size: 9px;">Applications</small>
                            <span class="font-weight-bold text-dark" style="font-size: 17px;">{{ $empStats['requests_count'] }}</span>
                            <small class="text-muted d-block" style="font-size: 10px;">Requests</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Month-Wise Breakdown Table Card --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
                <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between flex-wrap">
                    <div class="font-weight-bold text-dark" style="font-size: 14.5px;">
                        <i class="i-Calendar-4 text-primary mr-1"></i> Month-by-Month Leave Breakdown (HRMS Session: {{ reset($sessionMonths)['label'] }} to {{ end($sessionMonths)['label'] }})
                    </div>
                    <small class="text-muted font-italic">Calculation follows the academic session months (hover month for exact session date range)</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0 text-left align-middle w-100" style="font-size: 12.5px;">
                            <thead style="background-color: #f1f5f9; color: #334155;">
                                <tr>
                                    <th style="width: 40px;" class="text-center py-2">#</th>
                                    <th style="width: 120px;" class="py-2">Month</th>
                                    {{-- Leave Types Columns --}}
                                    @foreach($leaveTypes as $lt)
                                        <th class="text-center py-2" style="min-width: 65px;">{{ $lt->name }}</th>
                                    @endforeach
                                    <th class="text-center font-weight-bold py-2" style="width: 85px; background-color: #dcfce7 !important; color: #15803d !important; border-bottom: 2px solid #86efac;">
                                        <i class="i-Yes mr-1"></i> Approved
                                    </th>
                                    <th class="text-center font-weight-bold py-2" style="width: 80px; background-color: #fef3c7 !important; color: #b45309 !important; border-bottom: 2px solid #fcd34d;">
                                        <i class="i-Clock mr-1"></i> Pending
                                    </th>
                                    <th class="text-center font-weight-bold py-2" style="width: 80px; background-color: #fee2e2 !important; color: #b91c1c !important; border-bottom: 2px solid #fca5a5;">
                                        <i class="i-Close mr-1"></i> Rejected
                                    </th>
                                    <th class="text-center font-weight-bold py-2" style="width: 95px; background-color: #e0e7ff !important; color: #3730a3 !important; border-bottom: 2px solid #a5b4fc;">
                                        <i class="i-Over-Time mr-1"></i> Total Days
                                    </th>
                                    <th class="text-center py-2" style="width: 80px;">
                                        <i class="i-Receipt mr-1"></i> Requests
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $rowIdx = 1; @endphp
                                @foreach($monthlyData as $ym => $mData)
                                    <tr class="{{ $mData['total_days'] > 0 ? 'table-warning-soft' : '' }}" style="{{ $mData['total_days'] > 0 ? 'background-color: #fdfbf7;' : '' }}">
                                        <td class="text-center text-muted font-weight-bold py-1.5">{{ $rowIdx++ }}</td>
                                        <td class="font-weight-bold text-dark py-1.5" title="HRMS Session Period: {{ $mData['start']->format('d-m-Y') }} to {{ $mData['end']->format('d-m-Y') }}" data-toggle="tooltip">
                                            <i class="i-Calendar mr-1 text-primary"></i> {{ $mData['label'] }}
                                        </td>

                                        {{-- Days for each leave type in this month --}}
                                        @foreach($leaveTypes as $lt)
                                            @php $tDays = $mData['by_type'][$lt->id] ?? 0; @endphp
                                            <td class="text-center font-weight-bold py-1.5 {{ $tDays > 0 ? 'text-primary' : 'text-muted' }}">
                                                {{ $tDays > 0 ? number_format($tDays, 1) : '-' }}
                                            </td>
                                        @endforeach

                                        {{-- Approved --}}
                                        <td class="text-center font-weight-bold py-1.5" style="background-color: #f0fdf4;">
                                            @if($mData['approved_days'] > 0)
                                                <span class="badge px-2 py-0.5 font-weight-bold" style="background-color: #22c55e; color: #ffffff; font-size: 11.5px;">
                                                    {{ number_format($mData['approved_days'], 1) }}
                                                </span>
                                            @else
                                                <span style="color: #cbd5e1;">-</span>
                                            @endif
                                        </td>

                                        {{-- Pending --}}
                                        <td class="text-center font-weight-bold py-1.5" style="background-color: #fffbeb;">
                                            @if($mData['pending_days'] > 0)
                                                <span class="badge px-2 py-0.5 font-weight-bold" style="background-color: #f59e0b; color: #ffffff; font-size: 11.5px;">
                                                    {{ number_format($mData['pending_days'], 1) }}
                                                </span>
                                            @else
                                                <span style="color: #cbd5e1;">-</span>
                                            @endif
                                        </td>

                                        {{-- Rejected --}}
                                        <td class="text-center font-weight-bold py-1.5" style="background-color: #fef2f2;">
                                            @if($mData['rejected_days'] > 0)
                                                <span class="badge px-2 py-0.5 font-weight-bold" style="background-color: #ef4444; color: #ffffff; font-size: 11.5px;">
                                                    {{ number_format($mData['rejected_days'], 1) }}
                                                </span>
                                            @else
                                                <span style="color: #cbd5e1;">-</span>
                                            @endif
                                        </td>

                                        {{-- Month Total Days --}}
                                        <td class="text-center font-weight-bold py-1.5" style="background-color: #eef2ff;">
                                            @if($mData['total_days'] > 0)
                                                <span class="badge px-2 py-0.5 font-weight-bold" style="font-size: 12px; background-color: #4f46e5; color: #ffffff;">
                                                    {{ number_format($mData['total_days'], 1) }} Days
                                                </span>
                                            @else
                                                <span style="color: #cbd5e1;">0.0</span>
                                            @endif
                                        </td>

                                        {{-- Request count --}}
                                        <td class="text-center font-weight-bold py-1.5">
                                            @if($mData['requests_count'] > 0)
                                                <span class="badge badge-light border text-dark px-2 py-0.5 font-weight-bold" style="font-size: 11px;">
                                                    {{ $mData['requests_count'] }} App{{ $mData['requests_count'] > 1 ? 's' : '' }}
                                                </span>
                                            @else
                                                <span style="color: #cbd5e1;">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            {{-- Table Footer Summary --}}
                            <tfoot style="background-color: #0f172a; color: #fff; font-size: 13px;">
                                <tr>
                                    <th colspan="2" class="text-right py-2.5 text-uppercase font-weight-bold">
                                        Total Leave Days for Selected Session:
                                    </th>
                                    @foreach($leaveTypes as $lt)
                                        <th class="text-center py-2.5 text-warning font-weight-bold">
                                            {{ number_format($empStats['type_totals'][$lt->id] ?? 0, 1) }}
                                        </th>
                                    @endforeach
                                    <th class="text-center py-2.5 font-weight-bold" style="background-color: #14532d; color: #86efac;">
                                        {{ number_format($empStats['approved_days'], 1) }}
                                    </th>
                                    <th class="text-center py-2.5 font-weight-bold" style="background-color: #78350f; color: #fde68a;">
                                        {{ number_format($empStats['pending_days'], 1) }}
                                    </th>
                                    <th class="text-center py-2.5 font-weight-bold" style="background-color: #7f1d1d; color: #fca5a5;">
                                        {{ number_format($empStats['rejected_days'], 1) }}
                                    </th>
                                    <th class="text-center py-2.5 text-white font-weight-bold" style="background-color: #4338ca; font-size: 13.5px;">
                                        {{ number_format($empStats['total_days'], 1) }} Days
                                    </th>
                                    <th class="text-center py-2.5 text-white font-weight-bold">
                                        {{ $empStats['requests_count'] }}
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Actual Leave Records in Session --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="font-weight-bold text-dark" style="font-size: 15px;">
                        <i class="i-Receipt text-primary mr-1"></i> Leave Applications Applied in Session ({{ count($employeeLeaves) }} Records)
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 text-left align-middle" style="font-size: 13px;">
                            <thead style="background-color: #f8fafc; color: #475569;">
                                <tr>
                                    <th style="width: 40px;" class="text-center">#</th>
                                    <th>Leave Type</th>
                                    <th>From Date</th>
                                    <th>To Date</th>
                                    <th class="text-center">Total Days</th>
                                    <th>Leave Request Date</th>
                                    <th class="text-center">Status</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employeeLeaves as $k => $lReq)
                                    @php $lDays = ($lReq->is_half_day == 1) ? 0.5 : (\Carbon\Carbon::parse($lReq->start_date)->diffInDays(\Carbon\Carbon::parse($lReq->end_date)) + 1); @endphp
                                    <tr>
                                        <td class="text-center text-muted font-weight-bold">{{ $k + 1 }}</td>
                                        <td class="font-weight-bold text-primary">{{ optional($lReq->leaveType)->name ?: 'Leave' }}</td>
                                        <td class="font-weight-bold text-dark">{{ date('d-m-Y', strtotime($lReq->start_date)) }}</td>
                                        <td class="font-weight-bold text-dark">{{ date('d-m-Y', strtotime($lReq->end_date)) }}</td>
                                        <td class="text-center font-weight-bold">
                                            @if($lDays == 0.5)
                                                <span class="badge badge-warning px-2 py-1">0.5 Day ({{ ucfirst($lReq->half_day_type ?: 'Half') }})</span>
                                            @else
                                                <span class="badge badge-light border px-2 py-1 text-indigo font-weight-bold">{{ $lDays }} Days</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $lReq->created_at ? $lReq->created_at->format('d-m-Y h:i A') : '-' }}</td>
                                        <td class="text-center">
                                            @if(strtolower($lReq->status) === 'approved')
                                                <span class="badge badge-success px-2 py-1 font-weight-bold">Approved</span>
                                            @elseif(strtolower($lReq->status) === 'rejected')
                                                <span class="badge badge-danger px-2 py-1 font-weight-bold">Rejected</span>
                                            @else
                                                <span class="badge badge-warning px-2 py-1 font-weight-bold">Pending</span>
                                            @endif
                                        </td>
                                        <td class="small">{{ $lReq->reason ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center py-4 text-muted">No leave applications found for this employee in the selected session.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        @else
            <div class="alert alert-info border-0 shadow-sm text-center py-5">
                <i class="i-Checked-User d-block mb-2 text-primary" style="font-size: 40px;"></i>
                <div class="font-weight-bold text-dark mb-1" style="font-size: 16px;">Please Select an Employee</div>
                <p class="small text-muted mb-0">Select an employee from the dropdown filter above to view their month-by-month leave analysis.</p>
            </div>
        @endif

    {{-- ========================================================================= --}}
    {{-- REPORT TYPE 3: ALL EMPLOYEES MONTHLY SUMMARY                              --}}
    {{-- ========================================================================= --}}
    @elseif($filters['report_type'] === 'all_monthly')

        {{-- Org Summary Metric Cards (Small, Equal Size, Elegant & Professional) --}}
        <div class="row mb-4 no-print">
            <div class="col-lg-3 col-md-6 col-sm-6 mb-3 mb-lg-0">
                <div class="card border shadow-xs h-100" style="border-radius: 8px; border-color: #e2e8f0 !important; background: #ffffff;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="d-block text-muted text-uppercase font-weight-bold mb-1" style="font-size: 10.5px; letter-spacing: 0.5px;">
                                Total Staff Count
                            </span>
                            <span class="d-block font-weight-bold text-dark" style="font-size: 20px; line-height: 1.2;">
                                {{ number_format($allStats['total_employees']) }}
                            </span>
                            <small class="text-muted" style="font-size: 11px;">All active staff</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background-color: #eff6ff; color: #2563eb;">
                            <i class="i-Checked-User" style="font-size: 18px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 mb-3 mb-lg-0">
                <div class="card border shadow-xs h-100" style="border-radius: 8px; border-color: #e2e8f0 !important; background: #ffffff;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="d-block text-muted text-uppercase font-weight-bold mb-1" style="font-size: 10.5px; letter-spacing: 0.5px;">
                                Staff On Leave
                            </span>
                            <span class="d-block font-weight-bold text-dark" style="font-size: 20px; line-height: 1.2;">
                                {{ number_format($allStats['employees_with_leaves']) }}
                            </span>
                            <small class="text-muted" style="font-size: 11px;">With &ge; 1 day leave taken</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background-color: #ecfdf5; color: #059669;">
                            <i class="i-Calendar-4" style="font-size: 18px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 mb-3 mb-lg-0">
                <div class="card border shadow-xs h-100" style="border-radius: 8px; border-color: #e2e8f0 !important; background: #ffffff;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="d-block text-muted text-uppercase font-weight-bold mb-1" style="font-size: 10.5px; letter-spacing: 0.5px;">
                                Session Duration
                            </span>
                            <span class="d-block font-weight-bold text-dark" style="font-size: 20px; line-height: 1.2;">
                                {{ count($sessionMonths) }} <small class="text-muted font-weight-normal" style="font-size: 12.5px;">Months</small>
                            </span>
                            <small class="text-muted" style="font-size: 11px;">{{ reset($sessionMonths)['short_label'] }} &ndash; {{ end($sessionMonths)['short_label'] }}</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background-color: #fffbeb; color: #d97706;">
                            <i class="i-Clock" style="font-size: 18px;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 mb-3 mb-lg-0">
                <div class="card border shadow-xs h-100" style="border-radius: 8px; border-color: #e2e8f0 !important; background: #ffffff;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="d-block text-muted text-uppercase font-weight-bold mb-1" style="font-size: 10.5px; letter-spacing: 0.5px;">
                                Grand Total Leave Days
                            </span>
                            <span class="d-block font-weight-bold" style="font-size: 20px; line-height: 1.2; color: #4f46e5;">
                                {{ number_format($allStats['grand_total_days'], 1) }} <small class="text-muted font-weight-normal" style="font-size: 12.5px;">Days</small>
                            </span>
                            <small class="text-muted" style="font-size: 11px;">Across entire staff</small>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; background-color: #f5f3ff; color: #7c3aed;">
                            <i class="i-Over-Time" style="font-size: 18px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Monthly Breakdown Pills & Quick Jump --}}
        <div class="card border-0 shadow-sm mb-4 no-print" style="border-radius: 10px; background: #ffffff;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center mr-2 shadow-xs" style="width: 32px; height: 32px; background-color: #e0e7ff; color: #4338ca;">
                        <i class="i-Bar-Chart font-weight-bold" style="font-size: 15px;"></i>
                    </div>
                    <div>
                        <div class="font-weight-bold text-dark" style="font-size: 14.5px;">
                            Org-Wide Leaves Per Month Summary
                        </div>
                        <small class="text-muted">Academic Cycle (June 2025 – May 2026)</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 mt-2 mt-sm-0">
                    <span class="badge badge-light border text-muted px-2 py-1 small">
                        <i class="i-Cursor-Click text-primary mr-1"></i> Click any month card to jump in table
                    </span>
                </div>
            </div>
            <div class="card-body p-3" style="background-color: #f8fafc;">
                <div class="row g-2">
                    @php
                        $maxMonthVal = 0;
                        foreach($sessionMonths as $ym => $m) {
                            $t = $allStats['month_totals'][$ym] ?? 0;
                            if ($t > $maxMonthVal) $maxMonthVal = $t;
                        }
                        if ($maxMonthVal <= 0) $maxMonthVal = 1;
                    @endphp
                    @foreach($sessionMonths as $ym => $m)
                        @php
                            $mTot = $allStats['month_totals'][$ym] ?? 0;
                            $isActive = $mTot > 0;
                            $pct = round(($mTot / $maxMonthVal) * 100);
                        @endphp
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6 mb-2">
                            <div class="month-stat-card {{ $isActive ? 'is-active' : 'is-zero' }}" onclick="scrollToMonth('{{ $ym }}')" title="Click to view {{ $m['label'] }} in matrix table">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="month-tag">{{ $m['short_label'] }}</span>
                                    @if($isActive)
                                        <span class="month-pill-badge active-pill">{{ number_format($mTot, 1) }} d</span>
                                    @else
                                        <span class="month-pill-badge zero-pill">0.0 d</span>
                                    @endif
                                </div>

                                <div class="d-flex align-items-baseline justify-content-between my-1">
                                    <div class="month-numeric-val {{ $isActive ? 'text-active' : 'text-zero' }}">
                                        {{ number_format($mTot, 1) }}
                                        <span class="month-unit-label">Days</span>
                                    </div>
                                    <i class="i-Calendar-4 month-bg-icon {{ $isActive ? 'icon-active' : 'icon-zero' }}"></i>
                                </div>

                                <div class="month-mini-bar">
                                    <div class="month-mini-bar-fill" style="width: {{ $pct }}%;"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- All Employees Matrix Table Card --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap">
                <div class="font-weight-bold text-dark" style="font-size: 15px;">
                    <i class="i-Table text-primary mr-1"></i> All Employees – Monthly Leave Summary Matrix ({{ $allStats['total_employees'] }} Staff Members)
                </div>
                <small class="text-muted font-italic">Scroll right to view all 12 months • Header and staff columns remain pinned</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive table-sticky-container">
                    <table class="table table-hover mb-0 text-left align-middle table-sticky-matrix" style="font-size: 12.5px;">
                        <thead style="background-color: #f1f5f9; color: #334155;">
                            <tr>
                                <th class="text-center sticky-col-sr">#</th>
                                <th class="sticky-col-name">Staff Name</th>
                                <th class="text-center sticky-col-code">Code</th>
                                <th class="sticky-col-type">Staff Type</th>
                                <th class="sticky-col-dept">Dept</th>
                                {{-- Month Columns --}}
                                @foreach($sessionMonths as $ymKey => $m)
                                    <th class="text-center col-month month-header-cell" id="month-header-{{ str_replace('-', '', $ymKey) }}" style="min-width: 75px;">
                                        {{ $m['short_label'] }}
                                    </th>
                                @endforeach
                                <th class="text-center font-weight-bold col-total" style="min-width: 95px; background-color: #e0e7ff !important; color: #3730a3 !important;">Total Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($matrix as $idx => $row)
                                @php
                                    $emp = $row['employee'];
                                    $empName = trim($emp->first_name . ' ' . $emp->last_name);
                                    $essCode = optional(optional($emp)->biometricDetail)->ess_emp_code ?: '-';
                                    $staffType = optional(optional($emp)->staffType)->staff_type_name ?: '-';
                                    $dept = optional(optional($emp)->department)->department_name ?: '-';
                                    $hasLeaves = $row['total'] > 0;
                                @endphp
                                <tr class="{{ $hasLeaves ? 'row-has-leaves' : 'row-no-leaves' }}">
                                    <td class="text-center text-muted font-weight-bold sticky-col-sr">
                                        {{ method_exists($matrix, 'firstItem') && $matrix->firstItem() ? ($matrix->firstItem() + $idx) : ($idx + 1) }}
                                    </td>
                                    <td class="sticky-col-name" title="{{ $empName }}">
                                        <div class="font-weight-bold text-dark text-truncate">{{ $empName }}</div>
                                    </td>
                                    <td class="text-center font-weight-bold text-muted small sticky-col-code">{{ $essCode }}</td>
                                    <td class="sticky-col-type">
                                        @php
                                            $stLower = strtolower($staffType);
                                            if (str_contains($stLower, 'permanent')) {
                                                $bBg = '#e0e7ff'; $bCol = '#3730a3'; $bBrd = '#c7d2fe';
                                            } elseif (str_contains($stLower, 'temporary')) {
                                                $bBg = '#fef3c7'; $bCol = '#92400e'; $bBrd = '#fde68a';
                                            } elseif (str_contains($stLower, 'guest')) {
                                                $bBg = '#f1f5f9'; $bCol = '#334155'; $bBrd = '#cbd5e1';
                                            } else {
                                                $bBg = '#e2e8f0'; $bCol = '#1e293b'; $bBrd = '#cbd5e1';
                                            }
                                        @endphp
                                        <span class="badge px-2 py-1 font-weight-bold text-truncate d-inline-block" style="background-color: {{ $bBg }}; color: {{ $bCol }}; border: 1px solid {{ $bBrd }}; font-size: 11px; letter-spacing: 0.2px; max-width: 95px;">
                                            {{ $staffType }}
                                        </span>
                                    </td>
                                    <td class="sticky-col-dept" title="{{ $dept }}">
                                        <small class="text-muted text-truncate d-block">{{ $dept }}</small>
                                    </td>

                                    {{-- Month Days Cells --}}
                                    @foreach($sessionMonths as $ym => $m)
                                        @php $days = $row['months'][$ym] ?? 0; @endphp
                                        <td class="text-center font-weight-bold {{ $days > 0 ? 'text-primary' : 'text-muted' }}" style="{{ $days > 0 ? 'background-color: #f5f3ff;' : '' }}">
                                            {{ $days > 0 ? number_format($days, 1) : '-' }}
                                        </td>
                                    @endforeach

                                    {{-- Row Total --}}
                                    <td class="text-center font-weight-bold" style="background-color: #eef2ff;">
                                        @if($row['total'] > 0)
                                            <span class="badge badge-primary px-2 py-1 font-weight-bold" style="background-color: #4f46e5; font-size: 12px;">
                                                {{ number_format($row['total'], 1) }}
                                            </span>
                                        @else
                                            <span style="color: #cbd5e1;">0.0</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ count($sessionMonths) + 6 }}" class="text-center py-5 text-muted">No employee records found.</td></tr>
                            @endforelse
                        </tbody>

                        {{-- Matrix Footer Totals --}}
                        <tfoot style="background-color: #0f172a; color: #fff; font-size: 13px;">
                            <tr>
                                <th colspan="5" class="text-right py-3 text-uppercase font-weight-bold sticky-col-footer-label">
                                    Organization Total Leave Days Per Month:
                                </th>
                                @foreach($sessionMonths as $ym => $m)
                                    <th class="text-center py-3 text-warning font-weight-bold">
                                        {{ number_format($allStats['month_totals'][$ym] ?? 0, 1) }}
                                    </th>
                                @endforeach
                                <th class="text-center py-3 text-white font-weight-bold" style="background-color: #4338ca; font-size: 14px;">
                                    {{ number_format($allStats['grand_total_days'], 1) }} Days
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Matrix Pagination Links --}}
                @if(method_exists($matrix, 'hasPages') && $matrix->hasPages())
                    <div class="p-3 border-top d-flex align-items-center justify-content-between flex-wrap no-print">
                        <div class="text-muted small">
                            Showing <strong>{{ $matrix->firstItem() }}</strong> to <strong>{{ $matrix->lastItem() }}</strong> of <strong>{{ $matrix->total() }}</strong> staff members
                        </div>
                        <div>{{ $matrix->links('pagination::bootstrap-4') }}</div>
                    </div>
                @endif
            </div>
        </div>

    @endif

    {{-- Official Print Signature Section (Visible ONLY on printouts) --}}
    <div class="print-signature-section d-none">
        <div class="print-signature-box">Prepared By (HR)</div>
        <div class="print-signature-box">Checked By (Accounts)</div>
        <div class="print-signature-box">Principal / Authority</div>
    </div>

</div>

{{-- Styles --}}
<style>
    .badge { letter-spacing: 0.3px; }
    .table-warning-soft { background-color: #fffbeb !important; }
    .shadow-xs { box-shadow: 0 1px 3px rgba(0,0,0,0.06); }

    /* Bootstrap 4/5 Badge Visibility Compatibility */
    .badge-light, .badge.badge-light {
        background-color: #f1f5f9 !important;
        color: #334155 !important;
        border: 1px solid #cbd5e1 !important;
    }
    .badge-success, .badge.badge-success {
        background-color: #16a34a !important;
        color: #ffffff !important;
    }
    .badge-danger, .badge.badge-danger {
        background-color: #dc2626 !important;
        color: #ffffff !important;
    }
    .badge-warning, .badge.badge-warning {
        background-color: #f59e0b !important;
        color: #ffffff !important;
    }
    .badge-primary, .badge.badge-primary {
        background-color: #4f46e5 !important;
        color: #ffffff !important;
    }

    /* Executive Month Summary Cards */
    .month-stat-card {
        position: relative;
        padding: 11px 12px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        user-select: none;
        background: #ffffff;
    }
    .month-stat-card:hover {
        transform: translateY(-2px);
    }
    .month-tag {
        font-size: 12px;
        letter-spacing: 0.2px;
    }
    .month-pill-badge {
        font-weight: 700;
        font-size: 10px;
        padding: 2px 7px;
        border-radius: 12px;
        letter-spacing: 0.2px;
    }
    .month-numeric-val {
        font-size: 16px;
        font-weight: 800;
        line-height: 1.1;
    }
    .month-unit-label {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        margin-left: 2px;
    }
    .month-mini-bar {
        height: 3px;
        background-color: #e2e8f0;
        border-radius: 2px;
        overflow: hidden;
        margin-top: 5px;
    }
    .month-mini-bar-fill {
        height: 100%;
        border-radius: 2px;
        transition: width 0.3s ease;
    }

    /* Active Month with leaves taken */
    .month-stat-card.is-active {
        border: 1px solid #c7d2fe;
        border-top: 3px solid #4f46e5;
        background: linear-gradient(180deg, #ffffff 0%, #faf5ff 100%);
        box-shadow: 0 1px 3px rgba(79, 70, 229, 0.08);
    }
    .month-stat-card.is-active:hover {
        border-color: #818cf8;
        box-shadow: 0 6px 14px rgba(79, 70, 229, 0.16);
        background: #ffffff;
    }
    .month-stat-card.is-active .month-tag {
        color: #1e1b4b;
        font-weight: 700;
    }
    .month-stat-card.is-active .text-active {
        color: #4338ca;
    }
    .month-stat-card.is-active .active-pill {
        background-color: #e0e7ff;
        color: #3730a3;
        border: 1px solid #c7d2fe;
    }
    .month-stat-card.is-active .icon-active {
        color: #6366f1;
        font-size: 16px;
        opacity: 0.85;
    }
    .month-stat-card.is-active .month-mini-bar-fill {
        background: linear-gradient(90deg, #4f46e5 0%, #7c3aed 100%);
    }

    /* Zero Month with no leaves */
    .month-stat-card.is-zero {
        border: 1px solid #e2e8f0;
        border-top: 3px solid #cbd5e1;
        background-color: #f8fafc;
        opacity: 0.9;
    }
    .month-stat-card.is-zero:hover {
        background-color: #ffffff;
        border-color: #cbd5e1;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        opacity: 1;
    }
    .month-stat-card.is-zero .month-tag {
        color: #475569;
        font-weight: 600;
    }
    .month-stat-card.is-zero .text-zero {
        color: #94a3b8;
    }
    .month-stat-card.is-zero .zero-pill {
        background-color: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
    .month-stat-card.is-zero .icon-zero {
        color: #94a3b8;
        font-size: 15px;
        opacity: 0.45;
    }
    .month-stat-card.is-zero .month-mini-bar-fill {
        background-color: transparent;
    }

    /* Month Header Cell Flash on Jump */
    @keyframes monthFlash {
        0% { background-color: #fef08a !important; color: #854d0e !important; }
        100% { background-color: #f1f5f9 !important; color: #334155 !important; }
    }
    .month-column-flash {
        animation: monthFlash 1.2s ease-out !important;
    }

    /* Sticky Matrix Table */
    .table-sticky-container {
        max-height: 70vh;
        overflow-x: auto;
        overflow-y: auto;
        position: relative;
        background: #ffffff;
    }
    .table-sticky-matrix {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100%;
    }
    .table-sticky-matrix th,
    .table-sticky-matrix td {
        border-top: none;
        border-left: none;
        border-right: 1px solid #cbd5e1;
        border-bottom: 1px solid #cbd5e1;
        white-space: nowrap;
    }
    .table-sticky-matrix thead th {
        border-top: 1px solid #cbd5e1;
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #f1f5f9;
        color: #334155;
    }
    .table-sticky-matrix th:first-child,
    .table-sticky-matrix td:first-child {
        border-left: 1px solid #cbd5e1;
    }

    /* Fixed Left Columns Offsets */
    /* Col 1: # (42px) */
    .sticky-col-sr {
        position: sticky !important;
        left: 0px !important;
        width: 42px !important;
        min-width: 42px !important;
        max-width: 42px !important;
    }
    /* Col 2: Staff Name (180px) */
    .sticky-col-name {
        position: sticky !important;
        left: 42px !important;
        width: 180px !important;
        min-width: 180px !important;
        max-width: 180px !important;
    }
    /* Col 3: Code (65px) */
    .sticky-col-code {
        position: sticky !important;
        left: 222px !important;
        width: 65px !important;
        min-width: 65px !important;
        max-width: 65px !important;
    }
    /* Col 4: Staff Type (105px) */
    .sticky-col-type {
        position: sticky !important;
        left: 287px !important;
        width: 105px !important;
        min-width: 105px !important;
        max-width: 105px !important;
    }
    /* Col 5: Dept (110px) with right border & subtle drop-shadow */
    .sticky-col-dept {
        position: sticky !important;
        left: 392px !important;
        width: 110px !important;
        min-width: 110px !important;
        max-width: 110px !important;
        border-right: 2px solid #94a3b8 !important;
        box-shadow: 4px 0 6px -2px rgba(0, 0, 0, 0.12) !important;
    }

    /* Sticky Header on Left Columns (Top + Left Intersection) */
    .table-sticky-matrix thead th.sticky-col-sr,
    .table-sticky-matrix thead th.sticky-col-name,
    .table-sticky-matrix thead th.sticky-col-code,
    .table-sticky-matrix thead th.sticky-col-type,
    .table-sticky-matrix thead th.sticky-col-dept {
        top: 0 !important;
        z-index: 30 !important;
        background-color: #f1f5f9 !important;
    }

    /* Sticky Body Cells Backgrounds */
    .table-sticky-matrix tbody td.sticky-col-sr,
    .table-sticky-matrix tbody td.sticky-col-name,
    .table-sticky-matrix tbody td.sticky-col-code,
    .table-sticky-matrix tbody td.sticky-col-type,
    .table-sticky-matrix tbody td.sticky-col-dept {
        z-index: 5 !important;
        background-color: #ffffff;
    }
    .table-sticky-matrix tbody tr.row-no-leaves td.sticky-col-sr,
    .table-sticky-matrix tbody tr.row-no-leaves td.sticky-col-name,
    .table-sticky-matrix tbody tr.row-no-leaves td.sticky-col-code,
    .table-sticky-matrix tbody tr.row-no-leaves td.sticky-col-type,
    .table-sticky-matrix tbody tr.row-no-leaves td.sticky-col-dept {
        background-color: #fcfcfc;
    }
    .table-sticky-matrix tbody tr:hover td.sticky-col-sr,
    .table-sticky-matrix tbody tr:hover td.sticky-col-name,
    .table-sticky-matrix tbody tr:hover td.sticky-col-code,
    .table-sticky-matrix tbody tr:hover td.sticky-col-type,
    .table-sticky-matrix tbody tr:hover td.sticky-col-dept,
    .table-sticky-matrix tbody tr:hover td {
        background-color: #f1f5f9 !important;
    }

    /* Sticky Footer Label (Colspan 5 = 502px) */
    .sticky-col-footer-label {
        position: sticky !important;
        left: 0px !important;
        width: 502px !important;
        min-width: 502px !important;
        max-width: 502px !important;
        z-index: 20 !important;
        background-color: #0f172a !important;
        border-right: 2px solid #475569 !important;
        box-shadow: 4px 0 6px -2px rgba(0, 0, 0, 0.25) !important;
    }

    /* Detailed Table Sticky Scroll Container & Columns */
    .detailed-sticky-container {
        max-height: 70vh;
        overflow-x: auto;
        overflow-y: auto;
        position: relative;
        background: #ffffff;
    }
    .detailed-table-matrix {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100%;
    }
    .detailed-table-matrix th,
    .detailed-table-matrix td {
        white-space: nowrap;
        border-top: none;
        border-left: none;
        border-right: 1px solid #cbd5e1;
        border-bottom: 1px solid #cbd5e1;
    }
    .detailed-table-matrix thead th {
        position: sticky !important;
        top: 0 !important;
        z-index: 20 !important;
        background-color: #f8fafc !important;
        border-top: 1px solid #cbd5e1 !important;
        box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.08) !important;
    }
    .detailed-table-matrix th:first-child,
    .detailed-table-matrix td:first-child {
        border-left: 1px solid #cbd5e1;
    }

    /* Fixed Left Column 1: # (50px) */
    .detailed-sticky-sr {
        position: sticky !important;
        left: 0px !important;
        width: 50px !important;
        min-width: 50px !important;
        max-width: 50px !important;
        z-index: 10 !important;
    }
    /* Fixed Left Column 2: Staff Name (220px) with drop shadow */
    .detailed-sticky-name {
        position: sticky !important;
        left: 50px !important;
        width: 220px !important;
        min-width: 220px !important;
        max-width: 220px !important;
        border-right: 2px solid #94a3b8 !important;
        box-shadow: 4px 0 6px -2px rgba(0, 0, 0, 0.12) !important;
        z-index: 10 !important;
    }

    /* Sticky Intersection Header Cells (# and Staff Name in thead) */
    .detailed-table-matrix thead th.detailed-sticky-sr,
    .detailed-table-matrix thead th.detailed-sticky-name {
        top: 0 !important;
        z-index: 30 !important;
        background-color: #f1f5f9 !important;
    }

    /* Sticky Body Cells Backgrounds on Alternating & Hover */
    .detailed-table-matrix tbody td.detailed-sticky-sr,
    .detailed-table-matrix tbody td.detailed-sticky-name {
        background-color: #ffffff;
    }
    .detailed-table-matrix tbody tr:nth-of-type(odd) td.detailed-sticky-sr,
    .detailed-table-matrix tbody tr:nth-of-type(odd) td.detailed-sticky-name {
        background-color: #fcfcfd;
    }
    .detailed-table-matrix tbody tr:hover td.detailed-sticky-sr,
    .detailed-table-matrix tbody tr:hover td.detailed-sticky-name,
    .detailed-table-matrix tbody tr:hover td {
        background-color: #f1f5f9 !important;
    }

    /* Filter Form Professional Styling */
    #advanceLeaveFilterForm .form-control {
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        color: #1e293b;
        font-size: 13px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    #advanceLeaveFilterForm .form-control:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
    }
    #advanceLeaveFilterForm label {
        color: #475569 !important;
        font-weight: 600;
    }
    #advanceLeaveFilterForm .select2-container .select2-selection--single {
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important;
        height: 31px !important;
        display: flex !important;
        align-items: center !important;
    }
    #advanceLeaveFilterForm .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 29px !important;
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        *, *:before, *:after {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            color: #000000 !important;
            width: 100% !important;
            height: auto !important;
            overflow: visible !important;
        }

        /* Override Lite-Purple Admin Theme Hiding body * */
        body * {
            visibility: visible !important;
        }

        .app-admin-wrap,
        .main-content-wrap,
        .main-content {
            display: block !important;
            position: static !important;
            width: 100% !important;
            min-height: 0 !important;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            float: none !important;
            border: none !important;
            box-shadow: none !important;
        }

        #print-area,
        #print-area * {
            visibility: visible !important;
        }

        #print-area {
            position: relative !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
        }

        /* Strictly hide UI navigation, buttons, and filter card */
        .no-print,
        .no-print *,
        .main-header,
        .sidebar-left,
        .sidebar-left-secondary,
        .sidebar-overlay,
        .header-main,
        .footer,
        .app-footer,
        .breadcrumb,
        .separator-breadcrumb,
        .btn,
        form,
        .pagination,
        .dataTables_paginate,
        .dataTables_info,
        .layout-sidebar-large .main-header,
        .layout-sidebar-large .sidebar-left,
        .layout-sidebar-large .sidebar-left-secondary {
            display: none !important;
            visibility: hidden !important;
        }

        /* Print Header visible only in print */
        .print-only-header {
            display: block !important;
            margin-bottom: 12px !important;
        }

        /* Print Signatures */
        .print-signature-section {
            display: flex !important;
            justify-content: space-between !important;
            margin-top: 30px !important;
            padding-top: 10px !important;
            page-break-inside: avoid !important;
        }
        .print-signature-box {
            width: 28% !important;
            border-top: 1px solid #334155 !important;
            text-align: center !important;
            padding-top: 6px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            color: #1e293b !important;
        }

        /* Cards and tables */
        .card {
            box-shadow: none !important;
            border: 1px solid #cbd5e1 !important;
            margin-bottom: 12px !important;
            page-break-inside: auto !important;
            background: #fff !important;
        }

        .card-header {
            display: block !important;
            background-color: #f8fafc !important;
            border-bottom: 1px solid #cbd5e1 !important;
            padding: 8px 12px !important;
            font-size: 13px !important;
            color: #0f172a !important;
        }

        .card-body {
            padding: 8px 10px !important;
        }

        .table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 10.5px !important;
            page-break-inside: auto !important;
        }

        .table th,
        .table td {
            border: 1px solid #cbd5e1 !important;
            padding: 4px 6px !important;
            color: #0f172a !important;
        }

        .table tr {
            page-break-inside: avoid !important;
            page-break-after: auto !important;
        }

        .table thead {
            display: table-header-group !important;
        }

        .table thead th {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
        }

        .table tfoot {
            display: table-footer-group !important;
        }

        /* Unstick all sticky columns and headers so they don't overlap or truncate in print */
        .table-sticky-container,
        .detailed-sticky-container {
            max-height: none !important;
            height: auto !important;
            overflow: visible !important;
        }

        .table-sticky-matrix thead th,
        .table-sticky-matrix .sticky-col-sr,
        .table-sticky-matrix .sticky-col-name,
        .table-sticky-matrix .sticky-col-code,
        .table-sticky-matrix .sticky-col-type,
        .table-sticky-matrix .sticky-col-dept,
        .table-sticky-matrix .sticky-col-footer-label,
        .detailed-table-matrix thead th,
        .detailed-table-matrix .detailed-sticky-sr,
        .detailed-table-matrix .detailed-sticky-name {
            position: static !important;
            box-shadow: none !important;
            left: auto !important;
            top: auto !important;
        }

        /* Badges in print */
        .badge {
            border: 1px solid #94a3b8 !important;
            padding: 2px 4px !important;
        }
    }
</style>

{{-- JS for Switching Report Type, Printing & Month Jump --}}
<script>
    function printCurrentView() {
        window.print();
    }

    function switchReportType(type) {
        document.getElementById('report_type_input').value = type;
        document.getElementById('report_type_select').value = type;
        document.getElementById('advanceLeaveFilterForm').submit();
    }

    function scrollToMonth(ym) {
        var cellId = 'month-header-' + ym.replace('-', '');
        var $targetCell = $('#' + cellId);
        var $container = $('.table-sticky-container');
        if ($targetCell.length && $container.length) {
            var currentScroll = $container.scrollLeft();
            var cellOffset = $targetCell.position().left;
            var targetScroll = currentScroll + cellOffset - 502; // 502px frozen left columns width
            $container.animate({ scrollLeft: Math.max(0, targetScroll) }, 350);

            $targetCell.addClass('month-column-flash');
            setTimeout(function() {
                $targetCell.removeClass('month-column-flash');
            }, 1200);
        }
    }

    // Detail Modal Handler (Bootstrap 4 & 5 Compatible)
    function openDetailModal(id) {
        var modalSelector = '#detailModal' + id;
        var $modal = $(modalSelector);
        if ($modal.length) {
            // 1. Standard Bootstrap 4 jQuery modal
            if (typeof $modal.modal === 'function') {
                $modal.modal('show');
                return;
            }
            // 2. Bootstrap 5 instance modal
            if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Modal) {
                if (typeof window.bootstrap.Modal.getOrCreateInstance === 'function') {
                    window.bootstrap.Modal.getOrCreateInstance($modal[0]).show();
                    return;
                } else if (typeof window.bootstrap.Modal === 'function') {
                    var m = new window.bootstrap.Modal($modal[0]);
                    m.show();
                    return;
                }
            }
            // 3. Raw DOM Fallback
            $modal.addClass('show').css('display', 'block');
            $('body').addClass('modal-open');
            if (!$('.modal-backdrop').length) {
                $('<div class="modal-backdrop fade show"></div>').appendTo('body');
            }
        }
    }

    function closeDetailModal(id) {
        var modalSelector = id ? '#detailModal' + id : '.modal';
        var $modal = $(modalSelector);
        if ($modal.length) {
            if (typeof $modal.modal === 'function') {
                $modal.modal('hide');
            } else {
                $modal.removeClass('show').css('display', 'none');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            }
        }
    }

    // Delegated click support
    $(document).on('click', '.view-detail-btn', function(e) {
        var targetModal = $(this).attr('data-target') || $(this).attr('data-bs-target');
        if (targetModal && $(targetModal).length) {
            var modalId = targetModal.replace('#detailModal', '');
            openDetailModal(modalId);
        }
    });

    $(document).on('click', '[data-dismiss="modal"], [data-bs-dismiss="modal"]', function(e) {
        var $modal = $(this).closest('.modal');
        if ($modal.length) {
            var modalId = $modal.attr('id').replace('detailModal', '');
            closeDetailModal(modalId);
        }
    });
</script>

@endsection
