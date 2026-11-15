@extends('backend.layouts.main')

@section('main-container')
@php
if (!function_exists('formatIndianRupees')) {
    function formatIndianRupees($amount) {
        $amount = round((float) $amount, 2);
        $parts = explode('.', sprintf('%.2f', $amount));
        $intPart = $parts[0];
        $decPart = $parts[1];

        $isNegative = false;
        if (str_starts_with($intPart, '-')) {
            $isNegative = true;
            $intPart = substr($intPart, 1);
        }

        $lastThree = substr($intPart, -3);
        $otherNumbers = substr($intPart, 0, -3);
        if ($otherNumbers != '') {
            $lastThree = ',' . $lastThree;
        }
        $formatted = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $otherNumbers) . $lastThree;

        return ($isNegative ? '-' : '') . '₹ ' . $formatted . '.' . $decPart;
    }
}
@endphp
<style>
    .defaulters-page {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        color: #2b3548;
    }
    .kpi-cards-row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -8px;
        margin-left: -8px;
        margin-bottom: 24px;
    }
    .col-kpi {
        flex: 0 0 20%;
        max-width: 20%;
        width: 20%;
        padding-right: 8px;
        padding-left: 8px;
        display: flex;
    }
    @media (max-width: 1200px) {
        .col-kpi {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
            width: 33.333333%;
            margin-bottom: 16px;
        }
    }
    @media (max-width: 768px) {
        .col-kpi {
            flex: 0 0 50%;
            max-width: 50%;
            width: 50%;
            margin-bottom: 16px;
        }
    }
    @media (max-width: 480px) {
        .col-kpi {
            flex: 0 0 100%;
            max-width: 100%;
            width: 100%;
            margin-bottom: 16px;
        }
    }
    .kpi-card {
        width: 100%;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        padding: 16px 18px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 112px;
        height: 100%;
        margin-bottom: 0;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.07);
    }
    .kpi-card .kpi-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 4px;
    }
    .kpi-card .kpi-value {
        font-size: 19px;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.2;
        word-break: break-word;
    }
    .kpi-card .kpi-icon {
        position: absolute;
        right: 14px;
        top: 14px;
        width: 38px;
        height: 38px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .kpi-card .kpi-icon .kpi-rupee-symbol {
        font-size: 20px;
        font-weight: 800;
        line-height: 1;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }
    .filter-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 16px 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        margin-bottom: 20px;
    }
    .data-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        padding: 0;
        overflow: hidden;
    }
    .table-defaulters {
        width: 100% !important;
        margin-bottom: 0;
        font-size: 12px;
        vertical-align: middle;
    }
    .table-defaulters thead th {
        background: #f1f5f9;
        color: #334155;
        font-weight: 700;
        border-bottom: 2px solid #cbd5e1;
        padding: 8px 6px;
        font-size: 11.5px;
        line-height: 1.25;
        vertical-align: middle;
    }
    .table-defaulters tbody td {
        padding: 6px 6px;
        vertical-align: middle;
        border-color: #f1f5f9;
        font-size: 12px;
        line-height: 1.25;
    }
    .table-defaulters tbody tr:hover {
        background-color: #f8fafc;
    }
    .scholar-badge {
        font-family: monospace;
        font-size: 11px;
        font-weight: 700;
        background: #ede9fe;
        color: #6b21a8;
        padding: 2px 5px;
        border-radius: 4px;
        display: inline-block;
    }
    .due-breakdown-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        margin: 6px 0;
    }
    .badge-bus {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        font-size: 9.5px !important;
        padding: 1px 4px !important;
    }
    .badge-nobus {
        background-color: #f1f5f9;
        color: #64748b;
        font-size: 9.5px !important;
        padding: 1px 4px !important;
    }
    .btn-detail-toggle {
        font-size: 10.5px;
        padding: 2px 6px;
        border-radius: 4px;
    }
    /* Formal Print-Only Elements (hidden on screen) */
    .print-only-header,
    .print-only-signatures {
        display: none;
    }

    @media print {
        @page {
            size: landscape;
            margin: 8mm 6mm;
        }

        /* 1. Global Reset & Complete Elimination of Rounded Corners and Box Shadows */
        * {
            border-radius: 0 !important;
            -webkit-border-radius: 0 !important;
            box-shadow: none !important;
            text-shadow: none !important;
        }

        html, body {
            background: #ffffff !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            color: #000000 !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
            font-size: 9.5px !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* 2. Theme CSS Override: Unhide print-area */
        body, body * {
            visibility: visible !important;
        }
        #print-area, #print-area * {
            visibility: visible !important;
        }
        #print-area {
            position: static !important;
            width: 100% !important;
            left: auto !important;
            top: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            display: block !important;
        }

        /* 3. Hide all UI layout elements, sidebars, headers, search bars, buttons, and footers */
        .no-print,
        .no-print *,
        .main-header,
        .main-header *,
        .sidebar,
        .sidebar *,
        .sidebar-left,
        .sidebar-left-secondary,
        .sidebar-panel,
        .app-footer,
        .breadcrumb,
        .filter-card,
        .filter-card *,
        .btn,
        .btn-detail-toggle,
        .app-admin-wrap > .sidebar,
        .separator-breadcrumb,
        .kpi-cards-row,
        .header-top,
        .search-ui,
        th.no-print,
        td.no-print,
        .details-collapse-row {
            display: none !important;
            visibility: hidden !important;
        }

        /* 4. Reset containers to full page width, removing sidebar margins and scroll overflows */
        .app-admin-wrap,
        .main-content-wrap,
        .main-content,
        .defaulters-page {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            position: static !important;
            float: none !important;
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
            box-shadow: none !important;
            background: transparent !important;
        }

        .table-responsive {
            overflow: visible !important;
            display: block !important;
            width: 100% !important;
        }
        .data-card {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            background: transparent !important;
            overflow: visible !important;
        }

        /* 5. Institutional Print Header */
        .print-only-header {
            display: block !important;
            margin-bottom: 8px !important;
            padding-bottom: 6px !important;
            border-bottom: 2px solid #0f172a !important;
        }
        .print-school-name {
            font-size: 18px !important;
            font-weight: 900 !important;
            text-align: center !important;
            letter-spacing: 0.8px !important;
            color: #0f172a !important;
            margin: 0 !important;
            text-transform: uppercase !important;
        }
        .print-report-title {
            font-size: 12px !important;
            font-weight: 700 !important;
            text-align: center !important;
            color: #334155 !important;
            letter-spacing: 0.5px !important;
            margin: 2px 0 6px 0 !important;
            text-transform: uppercase !important;
        }
        .print-meta-bar {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            flex-wrap: wrap !important;
            font-size: 9.5px !important;
            color: #1e293b !important;
            background-color: #f8fafc !important;
            padding: 4px 8px !important;
            border: 1px solid #cbd5e1 !important;
        }
        .print-meta-item {
            margin: 1px 4px !important;
        }

        /* 6. Professional Ledger Grid (Clean straight lines, no heavy dark clutter) */
        .table-defaulters {
            width: 100% !important;
            border-collapse: collapse !important;
            font-size: 9.5px !important;
            border: 1px solid #94a3b8 !important;
            color: #0f172a !important;
            margin-top: 4px !important;
        }
        .table-defaulters thead {
            display: table-header-group !important;
        }
        .table-defaulters thead th {
            border: 1px solid #94a3b8 !important;
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            font-size: 9px !important;
            letter-spacing: 0.3px !important;
            padding: 4px 6px !important;
            vertical-align: middle !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .table-defaulters tbody tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .table-defaulters td {
            border: 1px solid #cbd5e1 !important;
            padding: 3.5px 5px !important;
            color: #1e293b !important;
            font-size: 9.5px !important;
            vertical-align: middle !important;
            line-height: 1.25 !important;
        }
        .table-defaulters tfoot {
            display: table-footer-group !important;
        }
        .table-defaulters tfoot tr td {
            border-top: 2px solid #0f172a !important;
            border-bottom: 2px double #0f172a !important;
            border-left: 1px solid #94a3b8 !important;
            border-right: 1px solid #94a3b8 !important;
            background-color: #f8fafc !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            padding: 5px 6px !important;
            font-size: 10px !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* 7. REMOVE ALL BORDERS, PILLS, BACKGROUNDS & ROUNDED CORNERS ON TEXT SNIPPETS */
        .table-defaulters .badge,
        .table-defaulters .scholar-badge,
        .table-defaulters .class-badge,
        .table-defaulters .badge-bus,
        .table-defaulters .badge-nobus,
        .table-defaulters .badge-info,
        .table-defaulters .badge-light,
        .table-defaulters .badge-danger,
        .table-defaulters .border {
            border: none !important;
            background: transparent !important;
            background-color: transparent !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            display: inline !important;
            font-weight: inherit !important;
            color: inherit !important;
            font-size: inherit !important;
            line-height: inherit !important;
        }

        /* Specific typographic treatment in print */
        .table-defaulters .scholar-badge {
            font-weight: 700 !important;
            color: #0f172a !important;
        }
        .table-defaulters td.text-right {
            text-align: right !important;
            font-variant-numeric: tabular-nums !important;
        }
        .table-defaulters td.text-center {
            text-align: center !important;
        }
        .table-defaulters a {
            color: inherit !important;
            text-decoration: none !important;
        }
        .table-defaulters i {
            display: none !important;
        }

        /* 8. Institutional Signatures at Bottom of Report */
        .print-only-signatures {
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-end !important;
            margin-top: 28px !important;
            padding-top: 10px !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            font-size: 10px !important;
            font-weight: 700 !important;
            color: #1e293b !important;
        }
        .sig-block {
            width: 28% !important;
            text-align: center !important;
        }
        .sig-line {
            border-top: 1px solid #475569 !important;
            margin-bottom: 5px !important;
        }
    }
</style>

<div class="main-content defaulters-page pt-3" id="print-area">
    <!-- Formal Print-Only Report Header -->
    <div class="print-only-header">
        <div class="print-header-top">
            <div class="print-school-name">LOKMANYA VIDYA NIKETAN</div>
            <div class="print-report-title">STUDENT-WISE FEE DEFAULTER STATEMENT &mdash; SESSION {{ $currentSession }}</div>
        </div>
        <div class="print-meta-bar">
            <div class="print-meta-item"><strong>Generated Date:</strong> {{ now()->format('d/m/Y h:i A') }}</div>
            <div class="print-meta-item"><strong>Total Defaulters:</strong> {{ count($defaulters) }} Students</div>
            <div class="print-meta-item"><strong>Academic Dues:</strong> {{ formatIndianRupees($totals['academic_due']) }}</div>
            <div class="print-meta-item"><strong>Bus Dues:</strong> {{ formatIndianRupees($totals['bus_due']) }}</div>
            <div class="print-meta-item"><strong>Late Fine:</strong> {{ formatIndianRupees($totals['late_fees']) }}</div>
            <div class="print-meta-item"><strong>Net Overdue:</strong> <span style="font-weight: 900;">{{ formatIndianRupees($totals['grand_total']) }}</span></div>
        </div>
    </div>

    <!-- Top Breadcrumb & Actions -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 no-print">
        <div>
            <h2 class="font-weight-bold mb-1" style="color: #1e293b;">
                <i class="i-File-Clipboard-Text--Image text-primary mr-1"></i> Fee Defaulter List
            </h2>
            <span class="text-muted small">
                Showing students with overdue academic/tuition fees for Session: <strong>{{ $currentSession }}</strong>
            </span>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2 mt-2 mt-md-0">
            <form method="GET" action="{{ route('defaulters.export') }}" class="d-inline mr-2">
                <input type="hidden" name="transport_filter" value="{{ $filters['transport_filter'] }}">
                <input type="hidden" name="class_filter" value="{{ $filters['class_filter'] }}">
                <input type="hidden" name="batch_filter" value="{{ $filters['batch_filter'] }}">
                <input type="hidden" name="include_late" value="{{ $filters['include_late'] ? '1' : '0' }}">
                <input type="hidden" name="search" value="{{ $filters['search'] }}">
                <button type="submit" class="btn btn-success btn-sm shadow-sm font-weight-bold">
                    <i class="i-File-Excel mr-1 font-weight-bold"></i> Export CSV
                </button>
            </form>
            <button type="button" class="btn btn-secondary btn-sm shadow-sm font-weight-bold mr-2" id="printReportBtn" onclick="printDefaultersReport(); return false;">
                <i class="i-Billing mr-1 font-weight-bold"></i> Print
            </button>
            <a href="{{ route('late-fee-adjustments.index') }}" class="btn btn-outline-warning btn-sm font-weight-bold mr-2">
                <i class="i-Money-2 mr-1"></i> Late Fee Adjustments
            </a>
            <a href="{{ route('fees_receipt_challan') }}" class="btn btn-outline-primary btn-sm font-weight-bold">
                <i class="i-Receipt-4 mr-1"></i> Fee Collection
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards: 5 Equal-Sized Boxes -->
    <div class="row kpi-cards-row no-print">
        <div class="col-kpi">
            <div class="kpi-card" style="border-left: 4px solid #6366f1;">
                <div>
                    <div class="kpi-label">Defaulter Students</div>
                    <div class="kpi-value text-primary">{{ number_format($totals['count']) }}</div>
                </div>
                <small class="text-muted mt-2">Total overdue accounts</small>
                <div class="kpi-icon" style="background:#eef2ff; color:#6366f1;">
                    <i class="i-Student-Hat"></i>
                </div>
            </div>
        </div>

        <div class="col-kpi">
            <div class="kpi-card" style="border-left: 4px solid #0284c7;">
                <div>
                    <div class="kpi-label">Academic Dues</div>
                    <div class="kpi-value" style="color: #0284c7;">{{ formatIndianRupees($totals['academic_due']) }}</div>
                </div>
                <small class="text-muted mt-2">Tuition &amp; Lunch Fees</small>
                <div class="kpi-icon" style="background:#e0f2fe; color:#0284c7;">
                    <span class="kpi-rupee-symbol">₹</span>
                </div>
            </div>
        </div>

        <div class="col-kpi">
            <div class="kpi-card" style="border-left: 4px solid #d97706;">
                <div>
                    <div class="kpi-label">Bus / Transport Dues</div>
                    <div class="kpi-value" style="color: #d97706;">{{ formatIndianRupees($totals['bus_due']) }}</div>
                </div>
                <small class="text-muted mt-2">Overdue bus fees</small>
                <div class="kpi-icon" style="background:#fef3c7; color:#d97706;">
                    <span class="kpi-rupee-symbol">₹</span>
                </div>
            </div>
        </div>

        <div class="col-kpi">
            <div class="kpi-card" style="border-left: 4px solid #ea580c;">
                <div>
                    <div class="kpi-label">Late Fees</div>
                    <div class="kpi-value" style="color: #ea580c;">{{ formatIndianRupees($totals['late_fees']) }}</div>
                </div>
                <small class="text-muted mt-2">Calculated fine</small>
                <div class="kpi-icon" style="background:#ffedd5; color:#ea580c;">
                    <i class="i-Clock"></i>
                </div>
            </div>
        </div>

        <div class="col-kpi">
            <div class="kpi-card" style="border-left: 4px solid #dc2626;">
                <div>
                    <div class="kpi-label">Total Outstanding</div>
                    <div class="kpi-value text-danger">{{ formatIndianRupees($totals['grand_total']) }}</div>
                </div>
                <small class="text-muted mt-2">Grand total overdue</small>
                <div class="kpi-icon" style="background:#fee2e2; color:#dc2626;">
                    <span class="kpi-rupee-symbol">₹</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="filter-card no-print">
        <form method="GET" action="{{ route('defaulters.index') }}" id="defaulterFilterForm">
            <div class="row align-items-end">
                <div class="col-lg-3 col-md-6 mb-2">
                    <label class="font-weight-bold small text-muted mb-1">Search Student / Parent:</label>
                    <input type="text" name="search" id="liveSearchInput" class="form-control" placeholder="Name, Scholar No, Mobile, Section..." value="{{ $filters['search'] }}">
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                    <label class="font-weight-bold small text-muted mb-1">Class:</label>
                    <select name="class_filter" class="form-control" onchange="this.form.submit()">
                        <option value="">-- All Classes --</option>
                        @foreach($allClasses as $class)
                            <option value="{{ $class }}" @selected($filters['class_filter'] === $class)>
                                {{ \App\Academic\AcademicHelper::toRoman($class) ?: $class }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                    <label class="font-weight-bold small text-muted mb-1">Batch / Session:</label>
                    <select name="batch_filter" class="form-control" onchange="this.form.submit()">
                        <option value="">-- All Batches --</option>
                        @foreach($allBatches as $batch)
                            <option value="{{ $batch }}" @selected($filters['batch_filter'] === $batch)>
                                {{ $batch }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                    <label class="font-weight-bold small text-muted mb-1">Transport Filter:</label>
                    <select name="transport_filter" class="form-control" onchange="this.form.submit()">
                        <option value="">All Students</option>
                        <option value="with" @selected($filters['transport_filter'] === 'with')>With Bus Only</option>
                        <option value="without" @selected($filters['transport_filter'] === 'without')>Without Bus Only</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6 mb-2 text-right">
                    <a href="{{ route('defaulters.index') }}" class="btn btn-outline-secondary btn-block" title="Reset Filters">
                        <i class="i-Repeat mr-1"></i> Reset
                    </a>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap mt-2 pt-2 border-top">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="includeLateToggle" name="include_late" value="1" @checked($filters['include_late']) onchange="this.form.submit()">
                    <label class="custom-control-label small font-weight-bold text-muted" for="includeLateToggle">
                        Include Estimated Late Fees in Outstanding Totals
                    </label>
                </div>

                <div class="text-muted small">
                    Showing <strong>{{ count($defaulters) }}</strong> defaulter records
                    @if($filters['transport_filter'] === 'without')
                        <span class="badge badge-secondary ml-1">Without Bus Only</span>
                    @elseif($filters['transport_filter'] === 'with')
                        <span class="badge badge-warning ml-1">With Bus Only</span>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Defaulters Table Card -->
    <div class="data-card">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap no-print" style="background-color: #e2e8f0; border-bottom: 1px solid #cbd5e1 !important;">
            <div class="font-weight-bold text-dark" style="font-size: 14.5px;">
                <i class="i-Receipt text-primary mr-1"></i> Student-wise Overdue Accounts
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-outline-dark py-1 px-2 font-weight-bold" id="toggleAllDetailsBtn" onclick="toggleAllDetails()">
                    <i class="i-Arrow-Down-2 mr-1"></i> Expand All Details
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-defaulters" id="defaultersTable">
                <thead>
                    <tr>
                        <th style="width: 35px;" class="text-center">#</th>
                        <th style="width: 80px;">Scholar No</th>
                        <th>Student & Parent Name</th>
                        <th style="width: 75px;" class="text-center">Class</th>
                        <th style="width: 85px;" class="text-center">Section</th>
                        <th style="width: 85px;" class="text-center">Batch</th>
                        <th class="text-right" style="width: 115px;">Tuition Due (₹)</th>
                        <th class="text-right" style="width: 90px;">Bus Due (₹)</th>
                        <th class="text-right" style="width: 95px;">Late Fee (₹)</th>
                        <th class="text-right" style="width: 120px;">Total Overdue (₹)</th>
                        <th class="text-center no-print" style="width: 115px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sno = 1; @endphp
                    @forelse($defaulters as $studentId => $data)
                        @php
                            $info = $data['info'];
                            $rowTargetId = 'details_row_' . $studentId;
                        @endphp
                        <tr class="defaulter-row" data-search="{{ strtolower($info['name'] . ' ' . $info['scholar_no'] . ' ' . $info['father_name'] . ' ' . $info['father_mobile'] . ' ' . $info['classname'] . ' ' . ($info['class_roman'] ?? \App\Academic\AcademicHelper::toRoman($info['classname'])) . ' ' . ($info['section'] ?? '')) }}">
                            <td class="text-center font-weight-bold text-muted">{{ $sno++ }}</td>
                            <td>
                                <span class="scholar-badge">{{ $info['scholar_no'] ?: '-' }}</span>
                            </td>
                            <td>
                                <div class="font-weight-bold text-dark" style="font-size: 13px; line-height: 1.2;">{{ $info['name'] }}</div>
                                <div class="text-muted" style="font-size: 11px; margin-top: 1px;">
                                    <i class="i-Checked-User mr-1"></i>Father: {{ $info['father_name'] }}
                                    @if($info['has_transport'])
                                        <span class="badge badge-bus font-weight-bold ml-1" style="font-size: 9px; padding: 1px 4px;">
                                            <i class="i-Bus mr-1"></i>With Bus
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light border text-dark px-1.5 py-0.5 font-weight-bold" style="font-size: 11px; color: #1e293b !important;">
                                    {{ $info['class_roman'] ?? (\App\Academic\AcademicHelper::toRoman($info['classname']) ?: ($info['classname'] ?: '-')) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if(!empty($info['section']) && $info['section'] !== '-')
                                    <span class="badge badge-light border text-dark px-1 py-0.5 font-weight-bold" style="font-size: 11px;">
                                        {{ $info['section'] }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge badge-light border text-muted px-1 py-0.5" style="font-size: 10.5px;">
                                    {{ $info['batch'] ?: '-' }}
                                </span>
                            </td>
                            <td class="text-right font-weight-bold text-nowrap" style="color: #0284c7; font-size: 12px;">
                                {{ formatIndianRupees($data['tuition_due']) }}
                                @if($data['lunch_due'] > 0)
                                    <br><small class="text-muted" style="font-size: 9.5px;">+ Lunch: {{ formatIndianRupees($data['lunch_due']) }}</small>
                                @endif
                                @php
                                    $overdueTerms = collect($data['dues'])->pluck('term')->unique()->filter()->values()->all();
                                @endphp
                                @if(!empty($overdueTerms))
                                    <br><span class="badge badge-light border text-primary px-1 py-0" style="font-size: 9.5px;" title="Overdue Terms">
                                        Term: {{ implode(', ', $overdueTerms) }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-right font-weight-bold text-nowrap" style="color: #d97706; font-size: 12px;">
                                @if($data['bus_due'] > 0)
                                    {{ formatIndianRupees($data['bus_due']) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-right font-weight-bold text-nowrap" style="color: #ea580c; font-size: 12px;">
                                @if($data['effective_late_fee'] > 0)
                                    {{ formatIndianRupees($data['effective_late_fee']) }}
                                    <br><small class="badge badge-danger p-0 px-1 font-weight-normal" style="font-size: 9.5px;">{{ $data['max_days_overdue'] }}d overdue</small>
                                @else
                                    <span class="text-muted">{{ formatIndianRupees(0) }}</span>
                                @endif
                            </td>
                            <td class="text-right text-nowrap">
                                <span class="badge badge-danger px-2 py-1 font-weight-bold" style="font-size: 12px;">
                                    {{ formatIndianRupees($data['total_due']) }}
                                </span>
                            </td>
                            <td class="text-center no-print text-nowrap">
                                <div class="d-inline-flex align-items-center">
                                    <button type="button" class="btn btn-outline-info py-0 px-1 mr-1 btn-detail-toggle" style="font-size: 10.5px; height: 26px; line-height: 24px;" onclick="toggleDefaulterDetails('{{ $rowTargetId }}', this)" title="View Term Breakdown">
                                        <i class="i-Arrow-Down"></i> Details
                                    </button>
                                    <a href="{{ route('late-fee-adjustments.index') }}?scholar_no={{ $info['scholar_no'] }}" target="_blank" class="btn btn-outline-warning py-0 px-1.5 mr-1" style="font-size: 10.5px; height: 26px; line-height: 24px;" title="Adjust or Waive Late Fee">
                                        <i class="i-Pen-4"></i>
                                    </a>
                                    <a href="{{ route('fees_receipt_challan') }}?search_student={{ $studentId }}" target="_blank" class="btn btn-primary py-0 px-2" style="font-size: 10.5px; height: 26px; line-height: 24px;" title="Collect Fees">
                                        <i class="i-Receipt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Collapsible Sub-Row: Overdue Term Breakdown -->
                        <tr class="details-collapse-row no-print" id="{{ $rowTargetId }}" style="display: none; background-color: #f8fafc;">
                            <td colspan="11" class="p-0 border-top-0">
                                <div class="due-breakdown-box m-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="font-weight-bold text-dark">
                                            <i class="i-Check text-success mr-1"></i> Overdue Fee Heads for {{ $info['name'] }}
                                            <span class="text-muted font-weight-normal small ml-2">(Oldest Due Date: <strong>{{ $data['oldest_due_date'] }}</strong>)</span>
                                        </div>
                                        <div>
                                            <a href="{{ route('fees_receipt_challan') }}?search_student={{ $studentId }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 font-weight-bold">
                                                <i class="i-Receipt-4 mr-1"></i> Open Fee Receipt Challan
                                            </a>
                                        </div>
                                    </div>

                                    <table class="table table-sm table-bordered bg-white mb-0" style="font-size: 12.5px;">
                                        <thead style="background-color: #e2e8f0; color: #0f172a; font-weight: 700; border-bottom: 2px solid #94a3b8;">
                                            <tr>
                                                <th>Fee Account Name</th>
                                                <th>Term</th>
                                                <th>Due Date</th>
                                                <th class="text-center">Overdue Days</th>
                                                <th class="text-right">Original Due (₹)</th>
                                                <th class="text-right">Paid Amount (₹)</th>
                                                <th class="text-right">Remaining Due (₹)</th>
                                                <th class="text-right">Est. Late Fee (₹)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data['dues'] as $due)
                                                <tr>
                                                    <td class="font-weight-bold">{{ $due['account_name'] }}</td>
                                                    <td>{{ $due['term'] }}</td>
                                                    <td class="text-danger font-weight-bold">{{ $due['due_date'] }}</td>
                                                    <td class="text-center">
                                                        <span class="badge badge-warning px-2 py-0">{{ $due['days_overdue'] }} days</span>
                                                    </td>
                                                    <td class="text-right text-muted">{{ formatIndianRupees($due['due_amount']) }}</td>
                                                    <td class="text-right text-success">{{ formatIndianRupees($due['paid_amount']) }}</td>
                                                    <td class="text-right font-weight-bold text-danger">{{ formatIndianRupees($due['amount']) }}</td>
                                                    <td class="text-right font-weight-bold text-warning">{{ formatIndianRupees($due['late_fee']) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot style="background-color: #f1f5f9; border-top: 3px solid #64748b; border-bottom: 2px solid #64748b;">
                                            <tr style="vertical-align: middle;">
                                                <td colspan="6" class="text-right py-2" style="font-size: 14px; font-weight: 800; color: #0f172a; letter-spacing: 0.3px;">
                                                    TOTAL FOR THIS STUDENT:
                                                </td>
                                                <td class="text-right py-2" style="vertical-align: middle;">
                                                    <span class="badge badge-danger px-2 py-1 shadow-sm" style="font-size: 14px; font-weight: 800; background-color: #dc2626; color: #ffffff; display: inline-block;">
                                                        {{ formatIndianRupees($data['academic_due'] + $data['bus_due']) }}
                                                    </span>
                                                </td>
                                                <td class="text-right py-2" style="vertical-align: middle;">
                                                    @if($data['effective_late_fee'] > 0)
                                                        <span class="badge badge-warning px-2 py-1 shadow-sm" style="font-size: 14px; font-weight: 800; background-color: #ea580c; color: #ffffff; display: inline-block;">
                                                            {{ formatIndianRupees($data['effective_late_fee']) }}
                                                        </span>
                                                    @else
                                                        <span class="text-dark font-weight-bold" style="font-size: 14px;">₹ 0.00</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr style="background-color: #e2e8f0; border-top: 2px solid #94a3b8; vertical-align: middle;">
                                                <td colspan="6" class="text-right py-2" style="font-size: 14px; font-weight: 900; color: #0f172a; letter-spacing: 0.3px;">
                                                    GRAND TOTAL OVERDUE (DUES + LATE FINE):
                                                </td>
                                                <td colspan="2" class="text-right py-2" style="vertical-align: middle;">
                                                    <span class="badge badge-danger px-3 py-1 shadow-sm" style="font-size: 15px; font-weight: 900; background-color: #b91c1c; color: #ffffff; letter-spacing: 0.5px; display: inline-block;">
                                                        {{ formatIndianRupees($data['total_due']) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="i-Happy text-success" style="font-size: 36px; display: block; margin-bottom: 8px;"></i>
                                    <h5>No Fee Defaulters Found!</h5>
                                    <p class="small text-muted mb-0">No matching student accounts with overdue fees found for the selected filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($defaulters) > 0)
                <tfoot style="background-color: #f1f5f9; border-top: 2px solid #64748b; font-weight: 800;">
                    <tr>
                        <td colspan="6" class="text-right py-2 text-dark font-weight-bold" style="font-size: 11.5px;">
                            Total Outstanding Dues ({{ count($defaulters) }} Defaulters):
                        </td>
                        <td class="text-right py-2 font-weight-bold text-nowrap" style="color: #0284c7; font-size: 11.5px;">
                            {{ formatIndianRupees($totals['academic_due']) }}
                        </td>
                        <td class="text-right py-2 font-weight-bold text-nowrap" style="color: #d97706; font-size: 11.5px;">
                            {{ formatIndianRupees($totals['bus_due']) }}
                        </td>
                        <td class="text-right py-2 font-weight-bold text-nowrap" style="color: #ea580c; font-size: 11.5px;">
                            {{ formatIndianRupees($totals['late_fees']) }}
                        </td>
                        <td class="text-right py-2 font-weight-bold text-nowrap" style="color: #b91c1c; font-size: 12px;">
                            {{ formatIndianRupees($totals['grand_total']) }}
                        </td>
                        <td class="no-print"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Print-Only Signatures Block -->
    <div class="print-only-signatures">
        <div class="sig-block">
            <div class="sig-line"></div>
            <div>Prepared By (Accounts Clerk)</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div>Checked & Verified By</div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div>Principal / Authorized Signatory</div>
        </div>
    </div>
</div>

<script>
    // Toggle single student breakdown
    function toggleDefaulterDetails(rowId, btn) {
        var row = document.getElementById(rowId);
        if (!row) return;

        var isHidden = (row.style.display === 'none' || window.getComputedStyle(row).display === 'none');
        if (isHidden) {
            row.style.display = 'table-row';
            if (btn) {
                btn.classList.remove('btn-outline-info');
                btn.classList.add('btn-info');
                btn.innerHTML = '<i class="i-Arrow-Up"></i> Hide';
            }
        } else {
            row.style.display = 'none';
            if (btn) {
                btn.classList.remove('btn-info');
                btn.classList.add('btn-outline-info');
                btn.innerHTML = '<i class="i-Arrow-Down"></i> Details';
            }
        }
    }

    // Dedicated Print Function
    function printDefaultersReport() {
        window.print();
    }

    // Live Search Filter across rows without reloading
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('liveSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                var query = e.target.value.toLowerCase().trim();
                var rows = document.querySelectorAll('.defaulter-row');

                rows.forEach(function(row) {
                    var searchData = row.getAttribute('data-search') || '';
                    var nextRow = row.nextElementSibling;

                    if (!query || searchData.indexOf(query) !== -1) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                        if (nextRow && nextRow.classList.contains('details-collapse-row')) {
                            nextRow.style.display = 'none';
                            var btn = row.querySelector('.btn-detail-toggle');
                            if (btn) {
                                btn.classList.remove('btn-info');
                                btn.classList.add('btn-outline-info');
                                btn.innerHTML = '<i class="i-Arrow-Down"></i> Details';
                            }
                        }
                    }
                });
            });
        }
    });

    var allExpanded = false;
    function toggleAllDetails() {
        var btn = document.getElementById('toggleAllDetailsBtn');
        var detailRows = document.querySelectorAll('.details-collapse-row');
        var toggleBtns = document.querySelectorAll('.btn-detail-toggle');

        allExpanded = !allExpanded;
        detailRows.forEach(function(row) {
            row.style.display = allExpanded ? 'table-row' : 'none';
        });

        toggleBtns.forEach(function(b) {
            if (allExpanded) {
                b.classList.remove('btn-outline-info');
                b.classList.add('btn-info');
                b.innerHTML = '<i class="i-Arrow-Up"></i> Hide';
            } else {
                b.classList.remove('btn-info');
                b.classList.add('btn-outline-info');
                b.innerHTML = '<i class="i-Arrow-Down"></i> Details';
            }
        });

        if (allExpanded) {
            btn.innerHTML = '<i class="i-Arrow-Up-2 mr-1"></i> Collapse All Details';
        } else {
            btn.innerHTML = '<i class="i-Arrow-Down-2 mr-1"></i> Expand All Details';
        }
    }
</script>
@endsection
