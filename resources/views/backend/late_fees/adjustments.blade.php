@extends('backend.layouts.main')

@section('main-container')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<style>
    .late-fee-card {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px;
    }
    .kpi-card {
        border-radius: 10px;
        padding: 16px 20px;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .kpi-card h3 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 2px;
        color: #ffffff;
    }
    .kpi-card span {
        font-size: 13px;
        opacity: 0.9;
        font-weight: 500;
    }
    .kpi-icon {
        font-size: 34px;
        opacity: 0.85;
    }
    .badge-waived {
        background-color: #10b981 !important;
        color: #ffffff !important;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11.5px;
    }
    .badge-adjusted {
        background-color: #3b82f6 !important;
        color: #ffffff !important;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11.5px;
    }
    .badge-auto {
        background-color: #64748b !important;
        color: #ffffff !important;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11.5px;
    }
    .badge-term {
        background-color: #e0e7ff !important;
        color: #3730a3 !important;
        border: 1px solid #c7d2fe !important;
        font-weight: 700 !important;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 11.5px;
        letter-spacing: 0.3px;
        display: inline-block;
    }
    .badge-light {
        color: #0f172a !important;
        background-color: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
    }
    .term-row {
        transition: background-color 0.15s ease-in-out;
    }
    .term-row:hover {
        background-color: #f8fafc;
    }
    .late-fee-card .card-header {
        background-color: #e2e8f0 !important;
        border-bottom: 1px solid #cbd5e1 !important;
    }
    .table thead th {
        background-color: #f1f5f9 !important;
        color: #1e293b !important;
        font-weight: 700 !important;
        border-top: none !important;
        border-bottom: 2px solid #cbd5e1 !important;
        font-size: 12.5px;
        vertical-align: middle;
    }
    /* Specific Alignment for Student Terms Breakdown Table */
    #termsBreakdownTable thead th {
        vertical-align: middle !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        color: #1e293b !important;
        padding: 9px 10px !important;
        white-space: nowrap !important;
        border-top: none !important;
        border-bottom: 2px solid #cbd5e1 !important;
    }
    #termsBreakdownTable tbody td {
        vertical-align: middle !important;
        padding: 8px 10px !important;
        font-size: 12.5px;
    }
    .print-only-header,
    .print-only-signatures {
        display: none;
    }
    @media print {
        @page {
            size: landscape;
            margin: 8mm;
        }
        body, body * {
            visibility: hidden;
        }
        #ledgerPrintCard, #ledgerPrintCard * {
            visibility: visible;
        }
        #ledgerPrintCard {
            position: absolute;
            left: 0;
            top: 0;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
            background: #ffffff !important;
        }
        .no-print,
        .no-print * {
            display: none !important;
        }
        .print-only-header {
            display: block !important;
            margin-bottom: 12px !important;
            text-align: center;
        }
        .print-only-signatures {
            display: flex !important;
            justify-content: space-between !important;
            margin-top: 40px !important;
            padding: 0 30px !important;
        }
        .table-responsive {
            overflow: visible !important;
        }
        .table {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 11px !important;
        }
        .table th, .table td {
            border: 1px solid #cbd5e1 !important;
            padding: 4px 6px !important;
            color: #0f172a !important;
        }
        .table thead th {
            background-color: #f1f5f9 !important;
            font-weight: 800 !important;
            border-bottom: 2px solid #94a3b8 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .badge {
            border: none !important;
            background: transparent !important;
            color: #0f172a !important;
            font-weight: 700 !important;
            padding: 0 !important;
        }
    }
</style>

<div class="main-content">
    {{-- Breadcrumb & Title --}}
    <div class="breadcrumb d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="mr-2 font-weight-bold mb-0" style="font-size: 20px; color: #1e293b;">
                <i class="i-Money-2 text-primary mr-1"></i> Student Late Fee Adjustments & Waivers
            </h1>
        </div>
        <div class="d-flex align-items-center mt-2 mt-sm-0">
            <a href="{{ route('defaulters.index') }}" class="btn btn-outline-danger btn-sm font-weight-bold mr-2">
                <i class="i-Receipt mr-1"></i> Defaulters List
            </a>
            <a href="{{ route('late-fees-master') }}" class="btn btn-outline-secondary btn-sm font-weight-bold">
                <i class="i-Gear mr-1"></i> Late Fees Master
            </a>
        </div>
    </div>
    <div class="separator-breadcrumb border-top mb-4"></div>

    {{-- KPI Metric Widgets --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
            <div class="kpi-card" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);">
                <div>
                    <h3>{{ $stats['total_count'] ?? 0 }}</h3>
                    <span>Total Adjustments</span>
                </div>
                <div class="kpi-icon"><i class="i-Check"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
            <div class="kpi-card" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
                <div>
                    <h3>{{ $stats['waived_count'] ?? 0 }}</h3>
                    <span>Fully Waived (₹0)</span>
                </div>
                <div class="kpi-icon"><i class="i-Close"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
            <div class="kpi-card" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                <div>
                    <h3>{{ $stats['adjusted_count'] ?? 0 }}</h3>
                    <span>Custom Reduced Fees</span>
                </div>
                <div class="kpi-icon"><i class="i-Pen-4"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-3 mb-xl-0">
            <div class="kpi-card" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%);">
                <div>
                    <h3>₹ {{ number_format($stats['total_saved'] ?? 0, 2) }}</h3>
                    <span>Total Late Fee Relief</span>
                </div>
                <div class="kpi-icon"><i class="i-Money-Bag"></i></div>
            </div>
        </div>
    </div>

    {{-- CARD 1: Quick Student Lookup & Adjustment Panel --}}
    <div class="late-fee-card">
        <div class="card-header border-bottom p-3" style="background-color: #e2e8f0; border-bottom: 1px solid #cbd5e1 !important;">
            <div class="d-flex align-items-center justify-content-between flex-wrap">
                <div class="font-weight-bold text-dark" style="font-size: 15px;">
                    <i class="i-Search-People text-primary mr-1"></i> Quick Adjust or Waive Late Fee for Student
                </div>
                <small class="text-muted font-weight-bold">Adjusted late fees reflect instantly on the online payment URL</small>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row align-items-end mb-3">
                <div class="col-md-5 col-lg-4 mb-2 mb-md-0">
                    <label class="font-weight-bold text-dark small mb-1">Scholar Number <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="searchScholarNo" placeholder="Enter Scholar No (e.g. 7737)" value="{{ request('scholar_no', '') }}">
                        <button class="btn btn-primary font-weight-bold px-3" type="button" id="lookupStudentBtn" onclick="lookupStudentFeeInfo()">
                            <i class="i-Magnifi-Glass1 mr-1"></i> Search
                        </button>
                    </div>
                </div>
                <div class="col-md-7 col-lg-8">
                    <span class="text-muted small">
                        <i class="i-Information mr-1 text-info"></i>
                        Enter the student's Scholar No to view all terms, automatic late fees, and easily waive or set custom amounts.
                    </span>
                </div>
            </div>

            {{-- Student Fee Breakdown (Dynamic AJAX Container) --}}
            <div id="studentFeeBreakdownContainer" style="display: none;" class="mt-4 pt-3 border-top">
                <div id="studentProfileHeader" class="p-3 mb-3 rounded" style="background-color: #f1f5f9; border: 1px solid #cbd5e1;">
                    {{-- Profile header populated by JS --}}
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0" id="termsBreakdownTable">
                        <thead style="background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                            <tr>
                                <th style="width: 100px;" class="text-center">Term</th>
                                <th style="width: 115px;" class="text-center">Due Date</th>
                                <th style="width: 120px;" class="text-center">Overdue Days</th>
                                <th style="width: 130px;" class="text-right">Tuition Fee</th>
                                <th style="width: 135px;" class="text-right">Auto Late Fee</th>
                                <th style="width: 165px;" class="text-right">Current Payable Late Fee</th>
                                <th class="text-left" style="min-width: 250px;">Action & Reason</th>
                            </tr>
                        </thead>
                        <tbody id="termsBreakdownTbody">
                            {{-- Rows populated dynamically by JS --}}
                        </tbody>
                    </table>
                </div>

                {{-- Bulk Action for All Terms --}}
                <div class="p-3 mt-3 rounded d-flex justify-content-between align-items-center flex-wrap" style="background-color: #e2e8f0; border: 1px solid #cbd5e1 !important;">
                    <div class="font-weight-bold text-dark small mb-2 mb-md-0">
                        <i class="i-Flash text-warning mr-1"></i> Quick Action for All Terms:
                    </div>
                    <div class="d-flex align-items-center flex-wrap gap-2">
                        <button type="button" class="btn btn-success btn-sm font-weight-bold mr-2" onclick="bulkWaiveAllTerms()">
                            <i class="i-Check mr-1"></i> Waive All Terms (₹0.00)
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm font-weight-bold" onclick="bulkResetAllTerms()">
                            <i class="i-Repeat mr-1"></i> Reset All to Auto Calculation
                        </button>
                    </div>
                </div>
            </div>

            <div id="lookupLoadingSpinner" style="display: none;" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <div class="text-muted mt-2 font-weight-bold small">Fetching student details & overdue breakdown...</div>
            </div>
        </div>
    </div>

    {{-- CARD 2: Active Adjustments Ledger --}}
    <div class="late-fee-card" id="ledgerPrintCard">
        <div class="card-header border-bottom p-3 d-flex justify-content-between align-items-center flex-wrap" style="background-color: #e2e8f0; border-bottom: 1px solid #cbd5e1 !important;">
            <div class="font-weight-bold text-dark" style="font-size: 15px;">
                <i class="i-Receipt text-primary mr-1"></i> Late Fee Adjustments Ledger
            </div>
            <div class="d-flex align-items-center flex-wrap mt-2 mt-sm-0 no-print">
                <form method="GET" action="{{ route('late-fee-adjustments.index') }}" class="d-flex align-items-center mr-2 mb-1 mb-sm-0">
                    <select name="status" class="form-control form-control-sm mr-2" style="width: 125px; background-color: #ffffff;" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="waived" {{ ($statusFilter ?? '') === 'waived' ? 'selected' : '' }}>Waived (₹0)</option>
                        <option value="adjusted" {{ ($statusFilter ?? '') === 'adjusted' ? 'selected' : '' }}>Adjusted</option>
                    </select>
                    <div class="input-group input-group-sm" style="width: 220px;">
                        <input type="text" name="search" class="form-control bg-white" placeholder="Search Scholar / Name..." value="{{ $search ?? '' }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary px-2" type="submit" title="Search">
                                <i class="i-Magnifi-Glass1"></i>
                            </button>
                        </div>
                    </div>
                    @if(!empty($search) || !empty($statusFilter))
                        <a href="{{ route('late-fee-adjustments.index') }}" class="btn btn-sm btn-outline-secondary ml-1" title="Clear Filters">
                            <i class="i-Repeat"></i>
                        </a>
                    @endif
                </form>
                <div class="d-flex align-items-center">
                    <a href="{{ route('late-fee-adjustments.export-csv', request()->query()) }}" class="btn btn-sm btn-success font-weight-bold mr-1" title="Export to CSV / Excel">
                        <i class="i-File-CSV mr-1"></i> CSV
                    </a>
                    <button type="button" class="btn btn-sm btn-dark font-weight-bold" onclick="printAdjustmentsLedger()" title="Print Ledger">
                        <i class="i-Billing mr-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            {{-- Institutional Print Header --}}
            <div class="print-only-header p-3">
                <h3 style="font-size: 19px; font-weight: 900; margin-bottom: 2px; text-transform: uppercase; color: #0f172a;">Lokmanya Vidya Niketan</h3>
                <h5 style="font-size: 13px; font-weight: 700; margin-bottom: 4px; color: #334155;">Student Late Fee Adjustments & Waivers Ledger</h5>
                <div style="font-size: 11px; color: #64748b;">
                    <strong>Generated:</strong> {{ now()->format('d-M-Y h:i A') }} &nbsp;|&nbsp;
                    <strong>Total Entries:</strong> {{ $adjustments->total() }}
                    @if(!empty($statusFilter)) &nbsp;|&nbsp; <strong>Status:</strong> {{ ucfirst($statusFilter) }} @endif
                    @if(!empty($search)) &nbsp;|&nbsp; <strong>Search:</strong> "{{ $search }}" @endif
                </div>
                <hr style="border-top: 1.5px solid #0f172a; margin: 8px 0 10px 0;">
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead style="background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th style="width: 100px;">Scholar No</th>
                            <th>Student & Class</th>
                            <th style="width: 100px;" class="text-center">Term</th>
                            <th style="width: 140px;" class="text-right">Original Late Fee</th>
                            <th style="width: 140px;" class="text-right">Adjusted Late Fee</th>
                            <th style="width: 110px;" class="text-center">Status</th>
                            <th>Reason / Remarks</th>
                            <th style="width: 180px;">Adjusted By & Date</th>
                            <th style="width: 100px;" class="text-center no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustments as $idx => $adj)
                        <tr>
                            <td class="text-center font-weight-bold text-muted">{{ $adjustments->firstItem() + $idx }}</td>
                            <td>
                                <span class="badge badge-light border text-dark font-weight-bold px-2 py-1" style="font-size: 11.5px;">
                                    {{ $adj->scholar_no }}
                                </span>
                            </td>
                            <td>
                                <div class="font-weight-bold text-dark">{{ $adj->student_name ?: 'N/A' }}</div>
                                <small class="text-muted">Class: {{ $adj->class_name ?: 'N/A' }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge-term">
                                    {{ $adj->term === 'all' ? 'All Terms' : "Term {$adj->term}" }}
                                </span>
                            </td>
                            <td class="text-right font-weight-bold text-muted">
                                ₹ {{ number_format($adj->original_late_fee, 2) }}
                            </td>
                            <td class="text-right font-weight-bold {{ $adj->adjusted_late_fee == 0 ? 'text-success' : 'text-primary' }}" style="font-size: 13.5px;">
                                ₹ {{ number_format($adj->adjusted_late_fee, 2) }}
                            </td>
                            <td class="text-center">
                                @if($adj->status === 'waived' || $adj->adjusted_late_fee == 0)
                                    <span class="badge-waived">Waived</span>
                                @else
                                    <span class="badge-adjusted">Adjusted</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-dark small font-weight-bold">{{ $adj->reason ?: '-' }}</div>
                            </td>
                            <td class="small text-muted">
                                <div><i class="i-Checked-User mr-1"></i> {{ $adj->admin_name ?: 'Admin' }}</div>
                                <div><i class="i-Clock mr-1"></i> {{ $adj->updated_at ? $adj->updated_at->format('d-M-Y h:i A') : '-' }}</div>
                            </td>
                            <td class="text-center no-print">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-1.5" title="Re-adjust Late Fee" onclick="loadStudentToSearch('{{ $adj->scholar_no }}')">
                                        <i class="i-Pen-4"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1.5 ml-1" title="Reset to Auto Calculation" onclick="confirmResetAdjustment({{ $adj->id }}, '{{ $adj->scholar_no }}', '{{ $adj->term }}')">
                                        <i class="i-Close"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="i-Receipt" style="font-size: 32px;"></i>
                                <div class="mt-2 font-weight-bold">No Late Fee Adjustments Found</div>
                                <small>Use the lookup panel above to search for a student and adjust or waive their late fees.</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($adjustments->hasPages())
            <div class="p-3 d-flex justify-content-end no-print">
                {{ $adjustments->links() }}
            </div>
            @endif

            {{-- Institutional Print Signatures Block --}}
            <div class="print-only-signatures">
                <div style="text-align: center;">
                    <div style="width: 170px; border-top: 1.5px solid #0f172a; margin-bottom: 5px;"></div>
                    <span style="font-size: 11px; font-weight: 700; color: #0f172a;">Prepared By</span>
                </div>
                <div style="text-align: center;">
                    <div style="width: 170px; border-top: 1.5px solid #0f172a; margin-bottom: 5px;"></div>
                    <span style="font-size: 11px; font-weight: 700; color: #0f172a;">Accounts In-Charge</span>
                </div>
                <div style="text-align: center;">
                    <div style="width: 170px; border-top: 1.5px solid #0f172a; margin-bottom: 5px;"></div>
                    <span style="font-size: 11px; font-weight: 700; color: #0f172a;">Principal / Authorised Signatory</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentStudentData = null;

    function printAdjustmentsLedger() {
        window.print();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const autoScholar = urlParams.get('scholar_no');
        if (autoScholar) {
            document.getElementById('searchScholarNo').value = autoScholar;
            lookupStudentFeeInfo();
        }
    });

    function loadStudentToSearch(scholarNo) {
        document.getElementById('searchScholarNo').value = scholarNo;
        window.scrollTo({ top: 150, behavior: 'smooth' });
        lookupStudentFeeInfo();
    }

    function lookupStudentFeeInfo() {
        const scholarNo = document.getElementById('searchScholarNo').value.trim();
        if (!scholarNo) {
            Swal.fire({
                icon: 'warning',
                title: 'Scholar Number Required',
                text: 'Please enter a valid scholar number.'
            });
            return;
        }

        const spinner = document.getElementById('lookupLoadingSpinner');
        const container = document.getElementById('studentFeeBreakdownContainer');
        spinner.style.display = 'block';
        container.style.display = 'none';

        fetch(`{{ route('late-fee-adjustments.student_info') }}?scholar_no=${encodeURIComponent(scholarNo)}`)
            .then(res => res.json())
            .then(data => {
                spinner.style.display = 'none';
                if (data.status !== 'success') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Could not fetch student fee details.'
                    });
                    return;
                }

                currentStudentData = data;
                renderStudentFeeBreakdown(data);
                container.style.display = 'block';
            })
            .catch(err => {
                spinner.style.display = 'none';
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Unable to communicate with the server. Please try again.'
                });
            });
    }

    function renderStudentFeeBreakdown(data) {
        const stu = data.student;
        const profileDiv = document.getElementById('studentProfileHeader');
        profileDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h5 class="font-weight-bold text-dark mb-1">
                        <i class="i-Checked-User text-success mr-1"></i> ${stu.student_name}
                        <span class="badge badge-light border text-primary ml-2">Scholar: ${stu.scholar_no}</span>
                    </h5>
                    <div class="text-muted small">
                        <strong>Class:</strong> ${stu.class_name || 'N/A'} |
                        <strong>Section:</strong> ${stu.section_name || 'N/A'} |
                        <strong>Father:</strong> ${stu.father_name || 'N/A'}
                    </div>
                </div>
                <div class="mt-2 mt-md-0">
                    <a href="{{ url('fee-structure') }}?scholar_id=${stu.scholar_no}" target="_blank" class="btn btn-sm btn-outline-info font-weight-bold">
                        <i class="i-Arrow-Up-Right mr-1"></i> View Parent Payment URL
                    </a>
                </div>
            </div>
        `;

        const tbody = document.getElementById('termsBreakdownTbody');
        tbody.innerHTML = '';

        if (!data.terms || data.terms.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">No tuition fee terms found for this student.</td></tr>`;
            return;
        }

        data.terms.forEach(term => {
            const tr = document.createElement('tr');
            tr.className = 'term-row';

            let statusBadge = '';
            if (term.is_waived) {
                statusBadge = `<div class="mt-1"><span class="badge-waived">Waived (₹0)</span></div>`;
            } else if (term.is_adjusted) {
                statusBadge = `<div class="mt-1"><span class="badge-adjusted">Adjusted: ₹${term.adjusted_late_fee}</span></div>`;
            }

            let overdueBadge = `<span class="badge px-2 py-0.5 font-weight-bold" style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 11px;">Current</span>`;
            if (term.is_overdue) {
                overdueBadge = `<span class="badge px-2 py-0.5 font-weight-bold" style="background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; font-size: 11px;">${term.days_overdue} days</span>`;
            }

            const currentPayableDisplay = `₹ ${parseFloat(term.effective_late_fee).toLocaleString('en-IN', {minimumFractionDigits: 2})}`;

            // Determine action cell HTML
            let actionHtml = '';
            if (term.is_paid) {
                actionHtml = `<span class="badge px-2 py-1 font-weight-bold" style="background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-size: 11.5px;"><i class="i-Check mr-1"></i> Paid in Full</span>`;
            } else {
                actionHtml = `
                    <div class="d-inline-flex align-items-center flex-wrap" style="gap: 5px;">
                        <button type="button" class="btn btn-sm btn-success py-1 px-2 font-weight-bold" onclick="submitAdjustmentDirect(this, '${term.term_code}', 'waive', 0, ${term.auto_late_fee})">
                            <i class="i-Check mr-1"></i> Waive (₹0)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 font-weight-bold" onclick="openCustomAmountModal('${term.term_code}', '${term.term_label}', ${term.auto_late_fee}, ${term.effective_late_fee})">
                            <i class="i-Pen-4 mr-1"></i> Set Custom ₹
                        </button>
                        ${term.is_adjusted ? `
                            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 font-weight-bold" onclick="submitAdjustmentDirect(this, '${term.term_code}', 'reset', 0, ${term.auto_late_fee})">
                                <i class="i-Repeat mr-1"></i> Reset
                            </button>
                        ` : ''}
                    </div>
                    ${term.reason ? `<div class="text-muted small mt-1" style="font-size: 11px;"><i class="i-Information mr-1"></i> <strong>Reason:</strong> ${term.reason}</div>` : ''}
                `;
            }

            tr.innerHTML = `
                <td class="text-center font-weight-bold text-dark text-nowrap">
                    <span class="badge-term" style="font-size: 11.5px; padding: 2px 8px;">${term.term_label}</span>
                    ${statusBadge}
                </td>
                <td class="text-center font-weight-bold text-nowrap" style="color: #475569; font-size: 12px;">${term.due_date || '-'}</td>
                <td class="text-center text-nowrap">${overdueBadge}</td>
                <td class="text-right font-weight-bold text-muted text-nowrap" style="font-size: 13px;">₹ ${parseFloat(term.base_fee).toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
                <td class="text-right font-weight-bold text-nowrap" style="color: ${term.auto_late_fee > 0 ? '#ea580c' : '#64748b'}; font-size: 13px;">
                    ₹ ${parseFloat(term.auto_late_fee).toLocaleString('en-IN', {minimumFractionDigits: 2})}
                </td>
                <td class="text-right font-weight-bold text-nowrap" style="color: ${term.effective_late_fee == 0 ? '#10b981' : '#0284c7'}; font-size: 13.5px;">
                    ${currentPayableDisplay}${term.is_waived ? ' <span class="badge badge-success ml-1 font-weight-bold" style="font-size: 9.5px; padding: 1px 4px;">WAIVED</span>' : ''}
                </td>
                <td class="text-left">
                    ${actionHtml}
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Helper for notifications with Swal or fallback to native alert
    function showNotification(type, title, message) {
        if (typeof Swal !== 'undefined' && Swal.fire) {
            Swal.fire({
                icon: type,
                title: title,
                text: message,
                timer: 2200,
                showConfirmButton: false
            });
        } else {
            alert(`${title}: ${message}`);
        }
    }

    // Open Custom Late Fee Modal
    function openCustomAmountModal(termCode, termLabel, autoLateFee, currentEffective) {
        try {
            document.getElementById('modalTermCode').value = termCode;
            document.getElementById('modalAutoLateFee').value = autoLateFee;
            document.getElementById('modalAdjustedAmount').value = (currentEffective !== null && currentEffective !== undefined) ? currentEffective : autoLateFee;
            document.getElementById('modalAutoInfo').innerHTML = `System calculated late fee for <strong>${termLabel}</strong>: <strong>₹ ${parseFloat(autoLateFee).toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong>`;
            document.getElementById('modalTitle').innerText = `Set Custom Late Fee (${termLabel})`;
            document.getElementById('modalReason').value = 'Adjusted by Admin';

            var modalEl = document.getElementById('customAmountModal');

            // 1. Try jQuery Bootstrap modal (Standard for this app)
            if (window.jQuery && typeof window.jQuery(modalEl).modal === 'function') {
                window.jQuery(modalEl).modal('show');
                return;
            }

            // 2. Try Bootstrap 5 if available
            if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Modal) {
                if (typeof window.bootstrap.Modal.getOrCreateInstance === 'function') {
                    window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
                    return;
                } else if (typeof window.bootstrap.Modal === 'function') {
                    new window.bootstrap.Modal(modalEl).show();
                    return;
                }
            }

            // 3. Fallback CSS display
            modalEl.style.display = 'block';
            modalEl.classList.add('show');
            document.body.classList.add('modal-open');
        } catch (err) {
            console.error('Error opening modal:', err);
            var fallbackAmount = prompt(`Enter custom late fee amount (₹) for Term ${termCode}:`, currentEffective !== null ? currentEffective : autoLateFee);
            if (fallbackAmount !== null && !isNaN(fallbackAmount) && fallbackAmount >= 0) {
                submitAdjustmentDirect(null, termCode, parseFloat(fallbackAmount) === 0 ? 'waive' : 'adjust', parseFloat(fallbackAmount), autoLateFee, 'Adjusted by Admin');
            }
        }
    }

    function closeCustomAmountModal() {
        var modalEl = document.getElementById('customAmountModal');
        if (window.jQuery && typeof window.jQuery(modalEl).modal === 'function') {
            window.jQuery(modalEl).modal('hide');
        }
        if (modalEl) {
            modalEl.style.display = 'none';
            modalEl.classList.remove('show');
        }
        document.body.classList.remove('modal-open');
        var backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(b => b.remove());
    }

    function handleCustomAmountSubmit(e) {
        e.preventDefault();
        const termCode = document.getElementById('modalTermCode').value;
        const autoLateFee = parseFloat(document.getElementById('modalAutoLateFee').value) || 0;
        const adjustedAmount = parseFloat(document.getElementById('modalAdjustedAmount').value);
        const reason = document.getElementById('modalReason').value.trim() || 'Adjusted by Admin';
        const saveBtn = document.getElementById('modalSaveBtn');

        if (isNaN(adjustedAmount) || adjustedAmount < 0) {
            alert('Please enter a valid amount (>= 0)');
            return;
        }

        saveBtn.disabled = true;
        saveBtn.innerHTML = `<span class="spinner-border spinner-border-sm mr-1"></span> Saving...`;

        submitAdjustmentDirect(saveBtn, termCode, adjustedAmount === 0 ? 'waive' : 'adjust', adjustedAmount, autoLateFee, reason, () => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = `<i class="i-Check mr-1"></i> Save Adjustment`;
            closeCustomAmountModal();
        });
    }

    // Direct submit adjustment via AJAX
    function submitAdjustmentDirect(btnEl, termCode, actionType, adjustedAmount = 0, originalLateFee = 0, reason = '', callback = null) {
        if (!currentStudentData || !currentStudentData.student) {
            alert('Student details not loaded. Please search student again.');
            return;
        }

        const stu = currentStudentData.student;
        let originalText = '';
        if (btnEl) {
            originalText = btnEl.innerHTML;
            btnEl.disabled = true;
            btnEl.innerHTML = `<span class="spinner-border spinner-border-sm mr-1"></span> Saving...`;
        }

        const payload = {
            student_id: stu.id,
            scholar_no: stu.scholar_no,
            term: termCode,
            action_type: actionType,
            adjusted_late_fee: adjustedAmount,
            original_late_fee: originalLateFee,
            reason: reason || (actionType === 'waive' ? 'Waived by Admin' : 'Adjusted by Admin')
        };

        fetch('{{ route('late-fee-adjustments.save') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (btnEl) {
                btnEl.disabled = false;
                btnEl.innerHTML = originalText;
            }
            if (callback) callback();

            if (data.status === 'success') {
                showNotification('success', 'Adjustment Saved', data.message);
                lookupStudentFeeInfo();
            } else {
                showNotification('error', 'Failed', data.message || 'Could not save adjustment.');
            }
        })
        .catch(err => {
            if (btnEl) {
                btnEl.disabled = false;
                btnEl.innerHTML = originalText;
            }
            if (callback) callback();
            showNotification('error', 'Error', 'Server communication error while saving adjustment.');
        });
    }

    function bulkWaiveAllTerms() {
        if (!currentStudentData || !currentStudentData.student) return;
        if (confirm(`Waive Late Fee for ALL Terms for student ${currentStudentData.student.student_name}? This will set late fees to ₹0.00 on the payment URL.`)) {
            submitAdjustmentDirect(null, 'all', 'waive', 0, 0, 'All Terms Late Fee Waived by Admin');
        }
    }

    function bulkResetAllTerms() {
        if (!currentStudentData || !currentStudentData.student) return;
        if (confirm(`Reset All Terms to Auto Calculation for student ${currentStudentData.student.student_name}? This will re-enable automatic system late fee calculations.`)) {
            submitAdjustmentDirect(null, 'all', 'reset', 0, 0);
        }
    }

    function confirmResetAdjustment(id, scholarNo, term) {
        const termLabel = term === 'all' ? 'All Terms' : 'Term ' + term;
        if (confirm(`Remove adjustment for Scholar ${scholarNo} (${termLabel}) and restore automatic daily calculation?`)) {
            fetch(`{{ url('late-fee-adjustments/reset') }}/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification('success', 'Reset Successful', data.message);
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    showNotification('error', 'Error', data.message || 'Could not reset adjustment.');
                }
            })
            .catch(() => {
                showNotification('error', 'Error', 'Server error while resetting adjustment.');
            });
        }
    }
</script>

{{-- MODAL: Set Custom Late Fee --}}
<div class="modal fade" id="customAmountModal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1055;">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-light border-bottom p-3">
                <h5 class="modal-title font-weight-bold text-dark" id="modalTitle">Set Custom Late Fee</h5>
                <button type="button" class="close text-dark" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" onclick="closeCustomAmountModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="customAmountForm" onsubmit="handleCustomAmountSubmit(event)">
                <div class="modal-body p-4">
                    <input type="hidden" id="modalTermCode">
                    <input type="hidden" id="modalAutoLateFee">

                    <div class="alert alert-info py-2 px-3 small mb-3 rounded" style="background-color: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af;">
                        <i class="i-Information mr-1"></i>
                        <span id="modalAutoInfo">System auto late fee: <strong>₹ 0.00</strong></span>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark small mb-1">New Late Fee Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control font-weight-bold" id="modalAdjustedAmount" required placeholder="Enter amount (e.g. 500)" style="font-size: 16px;">
                        <div class="mt-2 d-flex align-items-center flex-wrap gap-1">
                            <span class="small text-muted mr-1">Presets:</span>
                            <button type="button" class="btn btn-xs btn-outline-success mr-1 py-0 px-2" onclick="document.getElementById('modalAdjustedAmount').value = 0;">₹0 (Waive)</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary mr-1 py-0 px-2" onclick="document.getElementById('modalAdjustedAmount').value = 100;">₹100</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary mr-1 py-0 px-2" onclick="document.getElementById('modalAdjustedAmount').value = 250;">₹250</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary mr-1 py-0 px-2" onclick="document.getElementById('modalAdjustedAmount').value = 500;">₹500</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" onclick="document.getElementById('modalAdjustedAmount').value = 1000;">₹1000</button>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark small mb-1">Reason / Remarks</label>
                        <select class="form-control mb-2 small" id="modalReasonPreset" onchange="if(this.value) document.getElementById('modalReason').value = this.value;">
                            <option value="">-- Quick Select Reason --</option>
                            <option value="Approved by Principal">Approved by Principal</option>
                            <option value="Parent Request">Parent Request</option>
                            <option value="Financial Hardship Concession">Financial Hardship Concession</option>
                            <option value="Administrative Late Fee Waiver">Administrative Late Fee Waiver</option>
                            <option value="System Correction">System Correction</option>
                        </select>
                        <input type="text" class="form-control" id="modalReason" placeholder="Enter reason..." value="Adjusted by Admin">
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary btn-sm font-weight-bold px-3" data-dismiss="modal" data-bs-dismiss="modal" onclick="closeCustomAmountModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-4" id="modalSaveBtn">
                        <i class="i-Check mr-1"></i> Save Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

