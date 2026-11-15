<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if(($filters['report_type'] ?? '') === 'employee_monthly')
            Employee-Wise Monthly Leave Analysis - Lokmanya Vidya Niketan
        @elseif(($filters['report_type'] ?? '') === 'all_monthly')
            All Employees Monthly Leave Summary - Lokmanya Vidya Niketan
        @else
            Advance Leave Report - Lokmanya Vidya Niketan
        @endif
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: #1e293b;
            padding: 20px;
            font-size: 11.5px;
        }

        .no-print-bar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 10px 18px;
            border-radius: 8px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }

        .btn-primary { background-color: #4f46e5; color: #fff; }
        .btn-secondary { background-color: #e2e8f0; color: #334155; margin-left: 8px; }

        .print-container {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
        }

        .header-section {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .school-name {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #0f172a;
            text-transform: uppercase;
        }

        .report-title {
            font-size: 14px;
            font-weight: 600;
            color: #4338ca;
            margin-top: 3px;
            text-transform: uppercase;
        }

        .report-meta {
            font-size: 10.5px;
            color: #64748b;
            margin-top: 4px;
        }

        .filters-applied {
            background-color: #f1f5f9;
            padding: 7px 12px;
            border-radius: 6px;
            font-size: 10.5px;
            margin-bottom: 14px;
            color: #334155;
        }

        .summary-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 14px;
        }

        .summary-box {
            flex: 1;
            padding: 7px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: #f8fafc;
        }

        .summary-box .label {
            font-size: 9.5px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 600;
        }

        .summary-box .val {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 5px 7px;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 600;
            font-size: 10px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 600; }

        .signature-section {
            margin-top: 35px;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
        }

        .sig-box {
            text-align: center;
            width: 170px;
            border-top: 1px solid #0f172a;
            padding-top: 5px;
            font-weight: 600;
            font-size: 10.5px;
        }

        @media print {
            body { background: #fff !important; padding: 0 !important; }
            .no-print-bar { display: none !important; }
            .print-container { border: none !important; padding: 0 !important; }
            @page { size: A4 landscape; margin: 8mm; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    {{-- Top Action Bar (Hidden on print) --}}
    <div class="no-print-bar">
        <div>
            <strong>Advance Leave Report - Print Preview</strong>
            <span style="color: #64748b; font-size: 11px; margin-left: 8px;">
                Mode: 
                @if(($filters['report_type'] ?? '') === 'employee_monthly')
                    Employee-Wise Monthly Analysis
                @elseif(($filters['report_type'] ?? '') === 'all_monthly')
                    All Employees Monthly Summary
                @else
                    Detailed Records
                @endif
            </span>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="window.print();">🖨️ Print Now</button>
            <button type="button" class="btn btn-secondary" onclick="window.close();">Close</button>
        </div>
    </div>

    {{-- Printable Container --}}
    <div class="print-container">

        {{-- Header Section --}}
        <div class="header-section">
            <div class="school-name">LOKMANYA VIDYA NIKETAN</div>
            <div class="report-title">
                @if(($filters['report_type'] ?? '') === 'employee_monthly')
                    EMPLOYEE-WISE MONTHLY LEAVE ANALYSIS
                @elseif(($filters['report_type'] ?? '') === 'all_monthly')
                    ALL EMPLOYEES – MONTHLY LEAVE SUMMARY MATRIX
                @else
                    ADVANCE LEAVE REPORT (DETAILED)
                @endif
            </div>
            <div class="report-meta">
                Academic Session: {{ str_replace('_', ' - ', $filters['session_year'] ?? '2025_2026') }} (June – May) | 
                Generated Date: {{ \Carbon\Carbon::now()->format('d-m-Y h:i A') }}
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- VIEW 1: DETAILED LEAVE REPORT                                             --}}
        {{-- ========================================================================= --}}
        @if(($filters['report_type'] ?? '') === 'detailed')

            <div class="filters-applied">
                <strong>Filters Applied:</strong>
                @if(!empty($filters['from_date'])) From: <strong>{{ date('d-m-Y', strtotime($filters['from_date'])) }}</strong> @endif
                @if(!empty($filters['to_date'])) | To: <strong>{{ date('d-m-Y', strtotime($filters['to_date'])) }}</strong> @endif
                @if(!empty($filters['status'])) | Status: <strong>{{ $filters['status'] }}</strong> @endif
                @if(!empty($filters['request_date'])) | Req Date: <strong>{{ date('d-m-Y', strtotime($filters['request_date'])) }}</strong> @endif
            </div>

            <div class="summary-bar">
                <div class="summary-box"><div class="label">Total Requests</div><div class="val">{{ number_format($stats['total_requests'] ?? 0) }}</div></div>
                <div class="summary-box"><div class="label">Approved</div><div class="val" style="color: #16a34a;">{{ number_format($stats['approved'] ?? 0) }}</div></div>
                <div class="summary-box"><div class="label">Pending</div><div class="val" style="color: #d97706;">{{ number_format($stats['pending'] ?? 0) }}</div></div>
                <div class="summary-box"><div class="label">Rejected</div><div class="val" style="color: #dc2626;">{{ number_format($stats['rejected'] ?? 0) }}</div></div>
                <div class="summary-box"><div class="label">Total Leave Days</div><div class="val" style="color: #4f46e5;">{{ number_format($stats['total_days'] ?? 0, 1) }} Days</div></div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 30px;" class="text-center">#</th>
                        <th style="width: 140px;">Staff Name</th>
                        <th style="width: 60px;">Code</th>
                        <th style="width: 80px;">Staff Type</th>
                        <th style="width: 110px;">Leave Type</th>
                        <th style="width: 75px;">From Date</th>
                        <th style="width: 75px;">To Date</th>
                        <th style="width: 55px;" class="text-center">Days</th>
                        <th style="width: 75px;">Req. Date</th>
                        <th style="width: 70px;" class="text-center">Status</th>
                        <th>Reason / Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaveRequests as $i => $req)
                        @php
                            $emp = $req->employee;
                            $empName = $emp ? trim($emp->first_name . ' ' . $emp->last_name) : 'N/A';
                            $essCode = optional(optional($emp)->biometricDetail)->ess_emp_code ?: '-';
                            $staffType = optional(optional($emp)->staffType)->staff_type_name ?: '-';
                            $leaveTypeName = optional($req->leaveType)->name ?: 'Leave';
                            $status = strtolower($req->status ?: 'pending');
                        @endphp
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td class="font-bold">{{ $empName }}</td>
                            <td>{{ $essCode }}</td>
                            <td>{{ $staffType }}</td>
                            <td>{{ $leaveTypeName }}</td>
                            <td>{{ !empty($req->start_date) ? date('d-m-Y', strtotime($req->start_date)) : '-' }}</td>
                            <td>{{ !empty($req->end_date) ? date('d-m-Y', strtotime($req->end_date)) : '-' }}</td>
                            <td class="text-center font-bold">{{ $req->total_days }} @if($req->is_half_day) (0.5) @endif</td>
                            <td>{{ $req->created_at ? $req->created_at->format('d-m-Y') : '-' }}</td>
                            <td class="text-center font-bold">{{ ucfirst($status) }}</td>
                            <td>{{ $req->reason ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center py-4">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>

        {{-- ========================================================================= --}}
        {{-- VIEW 2: EMPLOYEE-WISE MONTHLY ANALYSIS                                    --}}
        {{-- ========================================================================= --}}
        @elseif(($filters['report_type'] ?? '') === 'employee_monthly')

            @if($selectedEmployee)
                <div class="filters-applied" style="background: #eef2ff; border-left: 4px solid #4f46e5;">
                    <div style="font-size: 13px; font-weight: 700; color: #1e1b4b;">
                        {{ $selectedEmployee->first_name }} {{ $selectedEmployee->last_name }}
                    </div>
                    <div>
                        ESS Code: <strong>{{ optional($selectedEmployee->biometricDetail)->ess_emp_code ?: 'N/A' }}</strong> | 
                        Staff Type: <strong>{{ optional($selectedEmployee->staffType)->staff_type_name ?: 'N/A' }}</strong> | 
                        Department: <strong>{{ optional($selectedEmployee->department)->department_name ?: 'N/A' }}</strong> | 
                        Session Total Leaves: <strong>{{ number_format($empStats['total_days'] ?? 0, 1) }} Days</strong>
                    </div>
                </div>

                <div class="summary-bar">
                    <div class="summary-box"><div class="label">Session Leave Days</div><div class="val" style="color: #4f46e5;">{{ number_format($empStats['total_days'] ?? 0, 1) }} Days</div></div>
                    <div class="summary-box"><div class="label">Approved Days</div><div class="val" style="color: #16a34a;">{{ number_format($empStats['approved_days'] ?? 0, 1) }}</div></div>
                    <div class="summary-box"><div class="label">Pending Days</div><div class="val" style="color: #d97706;">{{ number_format($empStats['pending_days'] ?? 0, 1) }}</div></div>
                    <div class="summary-box"><div class="label">Rejected Days</div><div class="val" style="color: #dc2626;">{{ number_format($empStats['rejected_days'] ?? 0, 1) }}</div></div>
                    <div class="summary-box"><div class="label">Total Requests</div><div class="val">{{ $empStats['requests_count'] ?? 0 }}</div></div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 30px;" class="text-center">#</th>
                            <th style="width: 120px;">Month</th>
                            @foreach($leaveTypes as $lt)
                                <th class="text-center">{{ $lt->name }}</th>
                            @endforeach
                            <th class="text-center" style="width: 65px;">Approved</th>
                            <th class="text-center" style="width: 65px;">Pending</th>
                            <th class="text-center" style="width: 65px;">Rejected</th>
                            <th class="text-center" style="width: 80px;">Total Days</th>
                            <th class="text-center" style="width: 60px;">Requests</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $rIdx = 1; @endphp
                        @foreach($monthlyData as $ym => $mData)
                            <tr>
                                <td class="text-center">{{ $rIdx++ }}</td>
                                <td class="font-bold">{{ $mData['label'] }}</td>
                                @foreach($leaveTypes as $lt)
                                    @php $td = $mData['by_type'][$lt->id] ?? 0; @endphp
                                    <td class="text-center">{{ $td > 0 ? number_format($td, 1) : '-' }}</td>
                                @endforeach
                                <td class="text-center font-bold">{{ $mData['approved_days'] > 0 ? number_format($mData['approved_days'], 1) : '-' }}</td>
                                <td class="text-center">{{ $mData['pending_days'] > 0 ? number_format($mData['pending_days'], 1) : '-' }}</td>
                                <td class="text-center">{{ $mData['rejected_days'] > 0 ? number_format($mData['rejected_days'], 1) : '-' }}</td>
                                <td class="text-center font-bold" style="background: #f1f5f9;">{{ number_format($mData['total_days'], 1) }}</td>
                                <td class="text-center">{{ $mData['requests_count'] > 0 ? $mData['requests_count'] : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="background: #f8fafc; font-weight: 700;">
                        <tr>
                            <td colspan="2" class="text-right font-bold">SESSION TOTAL:</td>
                            @foreach($leaveTypes as $lt)
                                <td class="text-center font-bold">{{ number_format($empStats['type_totals'][$lt->id] ?? 0, 1) }}</td>
                            @endforeach
                            <td class="text-center font-bold">{{ number_format($empStats['approved_days'] ?? 0, 1) }}</td>
                            <td class="text-center font-bold">{{ number_format($empStats['pending_days'] ?? 0, 1) }}</td>
                            <td class="text-center font-bold">{{ number_format($empStats['rejected_days'] ?? 0, 1) }}</td>
                            <td class="text-center font-bold" style="background: #e2e8f0;">{{ number_format($empStats['total_days'] ?? 0, 1) }} Days</td>
                            <td class="text-center font-bold">{{ $empStats['requests_count'] ?? 0 }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif

        {{-- ========================================================================= --}}
        {{-- VIEW 3: ALL EMPLOYEES MONTHLY SUMMARY                                     --}}
        {{-- ========================================================================= --}}
        @elseif(($filters['report_type'] ?? '') === 'all_monthly')

            <div class="summary-bar">
                <div class="summary-box"><div class="label">Total Staff Count</div><div class="val">{{ number_format($allStats['total_employees'] ?? 0) }}</div></div>
                <div class="summary-box"><div class="label">Staff On Leave</div><div class="val" style="color: #16a34a;">{{ number_format($allStats['employees_with_leaves'] ?? 0) }}</div></div>
                <div class="summary-box"><div class="label">Session Duration</div><div class="val">{{ count($sessionMonths ?? []) }} Months</div></div>
                <div class="summary-box"><div class="label">Grand Total Leave Days</div><div class="val" style="color: #4f46e5;">{{ number_format($allStats['grand_total_days'] ?? 0, 1) }} Days</div></div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 30px;" class="text-center">#</th>
                        <th style="min-width: 130px;">Staff Name</th>
                        <th style="width: 55px;" class="text-center">Code</th>
                        <th style="width: 75px;">Type</th>
                        <th style="width: 80px;">Dept</th>
                        @foreach($sessionMonths as $m)
                            <th class="text-center" style="min-width: 48px;">{{ $m['short_label'] }}</th>
                        @endforeach
                        <th class="text-center" style="width: 65px; background: #e2e8f0;">Total</th>
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
                        @endphp
                        <tr>
                            <td class="text-center">{{ $idx + 1 }}</td>
                            <td class="font-bold">{{ $empName }}</td>
                            <td class="text-center">{{ $essCode }}</td>
                            <td>{{ $staffType }}</td>
                            <td>{{ $dept }}</td>
                            @foreach($sessionMonths as $ym => $m)
                                @php $d = $row['months'][$ym] ?? 0; @endphp
                                <td class="text-center {{ $d > 0 ? 'font-bold' : '' }}">{{ $d > 0 ? number_format($d, 1) : '-' }}</td>
                            @endforeach
                            <td class="text-center font-bold" style="background: #f1f5f9;">{{ number_format($row['total'], 1) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($sessionMonths) + 6 }}" class="text-center py-4">No records found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot style="background: #f8fafc; font-weight: 700;">
                    <tr>
                        <td colspan="5" class="text-right font-bold">TOTAL LEAVE DAYS:</td>
                        @foreach($sessionMonths as $ym => $m)
                            <td class="text-center font-bold">{{ number_format($allStats['month_totals'][$ym] ?? 0, 1) }}</td>
                        @endforeach
                        <td class="text-center font-bold" style="background: #cbd5e1;">{{ number_format($allStats['grand_total_days'] ?? 0, 1) }}</td>
                    </tr>
                </tfoot>
            </table>

        @endif

        {{-- Signature Blocks --}}
        <div class="signature-section">
            <div class="sig-box">Prepared By (HR)</div>
            <div class="sig-box">Checked By (Accounts)</div>
            <div class="sig-box">Principal / Authority</div>
        </div>

    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };
    </script>
</body>
</html>
