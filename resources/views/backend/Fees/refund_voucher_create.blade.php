@extends('backend.layouts.main')

@section('main-container')
<style>
    .rv-card {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
    }
    .rv-card > .card-body {
        padding: 24px 28px;
    }
    .rv-section-header {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 8px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .rv-section-header i {
        color: #4f46e5;
        margin-right: 6px;
    }
    .rv-label {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 4px;
    }
    .rv-input-readonly {
        background-color: #f8fafc !important;
        font-weight: 600;
        color: #1e293b;
    }
    .balance-badge-box {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 1px solid #86efac;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 16px;
    }
    .nav-tabs .nav-link.active {
        font-weight: 700;
        color: #4f46e5;
        border-bottom: 3px solid #4f46e5;
    }
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: 4px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        font-size: 14px !important;
        color: #1e293b !important;
        padding-left: 10px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
    }
    .select2-dropdown {
        border: 1px solid #ced4da !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
    }
    .select2-search--dropdown .select2-search__field {
        border: 1px solid #cbd5e1 !important;
        border-radius: 4px !important;
        padding: 6px 10px !important;
        font-size: 13px !important;
    }
</style>

<div class="main-content">
    {{-- Breadcrumb --}}
    <div class="breadcrumb d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="font-weight-bold text-dark mb-1">
                <i class="i-Add-File text-primary mr-1"></i> Generate Refund Voucher
            </h1>
            <p class="text-muted mb-0" style="font-size: 13px;">Create Caution Money and refundable fee reimbursement vouchers</p>
        </div>
        <div>
            <a href="{{ route('refund-vouchers.index') }}" class="btn btn-outline-secondary font-weight-bold shadow-sm">
                <i class="i-Arrow-Back-2 mr-1"></i> Back to Register
            </a>
        </div>
    </div>
    <div class="separator-breadcrumb border-top mb-3"></div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-4" id="rvTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active font-weight-bold" href="javascript:void(0)">
                <i class="i-Receipt mr-1"></i> Refund Voucher
            </a>
        </li>
    </ul>

    {{-- Flash Notifications --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="i-Close-Window mr-1 font-weight-bold"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <form method="POST" action="{{ route('refund-vouchers.store') }}" id="refundVoucherForm">
        @csrf

        {{-- CARD 1: Identification of Voucher and Student --}}
        <div class="rv-card">
            <div class="card-body">
                <div class="rv-section-header d-flex justify-content-between align-items-center">
                    <div>
                        <span><i class="i-ID-Card mr-1"></i> 1. Identification of Voucher and Student</span>
                        <span class="text-muted small ml-2">Step 1: Select Student</span>
                    </div>
                    <div>
                        <a href="{{ $selectedStudent ? url('view-student-ledger/' . $selectedStudent->id) : '#' }}" 
                           id="view_ledger_btn" 
                           target="_blank" 
                           class="btn btn-sm btn-outline-primary font-weight-bold shadow-sm" 
                           style="{{ $selectedStudent ? 'display: inline-flex;' : 'display: none;' }} align-items: center; padding: 4px 14px; font-size: 12px; border-radius: 6px; letter-spacing: 0.2px;">
                            <i class="i-Receipt mr-1" style="font-size: 14px;"></i> View Student Ledger <i class="fa fa-external-link ml-1" style="font-size: 11px;"></i>
                        </a>
                    </div>
                </div>

                {{-- Student Autocomplete / Search --}}
                <div class="row mb-3">
                    <div class="col-md-8 form-group">
                        <label class="rv-label">Search Student (By Scholar No, Student Name or Form No) <span class="text-danger">*</span></label>
                        <select name="student_id" id="student_search_select" class="form-control select2" style="width: 100%;" required data-placeholder="Type Scholar No or Student Name...">
                            <option value="">Type Scholar No or Student Name...</option>
                            @if(!empty($students))
                                @foreach($students as $st)
                                    <option value="{{ $st->id }}"
                                        data-scholar="{{ $st->scholar_no }}"
                                        data-name="{{ $st->student_name }}"
                                        data-father="{{ $st->father_name }}"
                                        data-class="{{ $st->class_name }}"
                                        data-section="{{ $st->section_name }}"
                                        {{ (old('student_id', $selectedStudent->id ?? '') == $st->id) ? 'selected' : '' }}>
                                        {{ $st->scholar_no ? $st->scholar_no . ' - ' : '' }}{{ $st->student_name }} (Class: {{ $st->class_name ?? 'N/A' }}{{ ($st->section_name && $st->section_name !== 'N/A') ? ' / ' . $st->section_name : '' }})
                                    </option>
                                @endforeach
                            @elseif($selectedStudent)
                                <option value="{{ $selectedStudent->id }}" 
                                    data-scholar="{{ $selectedStudent->scholar_no }}"
                                    data-name="{{ $selectedStudent->student_name }}"
                                    data-father="{{ $selectedStudent->father_name }}"
                                    data-class="{{ $selectedStudent->class_name }}"
                                    data-section="{{ $selectedStudent->section_name }}"
                                    selected>
                                    {{ $selectedStudent->scholar_no ? $selectedStudent->scholar_no . ' - ' : '' }}{{ $selectedStudent->student_name }} (Class: {{ $selectedStudent->class_name ?? 'N/A' }}{{ ($selectedStudent->section_name && $selectedStudent->section_name !== 'N/A') ? ' / ' . $selectedStudent->section_name : '' }})
                                </option>
                            @endif
                        </select>
                        <small class="form-text text-muted">Begin typing the student's name or scholar number to auto-populate details.</small>
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="rv-label">Voucher Date <span class="text-danger">*</span></label>
                        <input type="date" name="voucher_date" class="form-control" value="{{ old('voucher_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="rv-label">Voucher No. <span class="text-danger">*</span></label>
                        <input type="text" name="voucher_no" id="voucher_no" class="form-control rv-input-readonly" value="{{ old('voucher_no', $suggestedVoucherNo) }}" required readonly>
                    </div>
                </div>

                {{-- Auto-populated Student Details --}}
                <div class="row">
                    <div class="col-md-2 form-group">
                        <label class="rv-label">Scholar ID</label>
                        <input type="text" id="disp_scholar_no" class="form-control rv-input-readonly" value="{{ $selectedStudent->scholar_no ?? '' }}" readonly placeholder="Scholar No">
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="rv-label">Student Name</label>
                        <input type="text" id="disp_student_name" class="form-control rv-input-readonly" value="{{ $selectedStudent->student_name ?? '' }}" readonly placeholder="Student Name">
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="rv-label">Father's Name</label>
                        <input type="text" id="disp_father_name" class="form-control rv-input-readonly" value="{{ $selectedStudent->father_name ?? '' }}" readonly placeholder="Father's Name">
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="rv-label">Class</label>
                        <input type="text" id="disp_class_name" class="form-control rv-input-readonly" value="{{ $selectedStudent->class_name ?? '' }}" readonly placeholder="Class">
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="rv-label">Section</label>
                        <input type="text" id="disp_section_name" class="form-control rv-input-readonly font-weight-bold" value="{{ $selectedStudent->section_name ?? '' }}" readonly placeholder="Section">
                    </div>
                </div>

                <div class="row align-items-center mt-2">
                    <div class="col-md-12">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="is_discontinued" name="is_discontinued" value="1">
                            <label class="custom-control-label font-weight-bold text-dark" for="is_discontinued">
                                Discontinued / Transfer Certificate (T.C.) Student
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 2: Head-Wise Fee Balances & Outstanding Dues (Previous & Current Sessions) --}}
        <div class="rv-card" id="headWiseCard" style="{{ (!empty($studentBalance) && !empty($studentBalance['head_wise_details'])) ? '' : 'display: none;' }}">
            <div class="card-body">
                <div class="rv-section-header d-flex justify-content-between align-items-center">
                    <div>
                        <span><i class="i-Financial mr-1 text-primary"></i> 2. Head-Wise Fee Balances & Outstanding Dues (Previous & Current Sessions)</span>
                        <span class="badge badge-info px-2 py-1 ml-2" style="font-size: 11px;"><i class="i-Refresh mr-1"></i> Auto-Loaded from Session Ledgers</span>
                    </div>
                    <div>
                        <a href="{{ $selectedStudent ? url('view-student-ledger/' . $selectedStudent->id) : '#' }}" 
                           id="view_ledger_btn_card2" 
                           target="_blank" 
                           class="btn btn-sm btn-outline-info font-weight-bold shadow-sm" 
                           style="{{ $selectedStudent ? 'display: inline-flex;' : 'display: none;' }} align-items: center; padding: 3px 12px; font-size: 12px; border-radius: 6px;">
                            <i class="i-Receipt mr-1"></i> Full Ledger <i class="fa fa-external-link ml-1"></i>
                        </a>
                    </div>
                </div>

                {{-- Status / Alert Banner --}}
                <div id="duesAlertBox" class="mb-3">
                    @if(!empty($studentBalance))
                        @if(!empty($studentBalance['has_outstanding_dues']))
                            <div class="alert alert-danger d-flex align-items-center mb-0" role="alert" style="border-left: 5px solid #dc2626;">
                                <i class="i-Danger font-weight-bold mr-2" style="font-size: 26px;"></i>
                                <div>
                                    <strong>Attention: Outstanding Fee Dues Detected!</strong>
                                    <div>This student has pending fee dues of <strong class="text-danger">₹{{ number_format($studentBalance['total_dues'], 2) }}</strong> across academic sessions. Available Caution Money is <strong class="text-success">₹{{ number_format($studentBalance['total_advance'], 2) }}</strong>. Please review pending dues before issuing refund.</div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-success d-flex align-items-center mb-0" role="alert" style="border-left: 5px solid #16a34a;">
                                <i class="i-Yes font-weight-bold mr-2" style="font-size: 26px;"></i>
                                <div>
                                    <strong>All Academic Fees Cleared!</strong>
                                    <div>No outstanding fee dues found across previous or current sessions. Student is eligible for full Caution Money refund.</div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- 3 KPI Summary Cards: Advance, Dues, Net Position --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="p-3 rounded border" style="background-color: #f0fdf4; border-color: #86efac !important;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-uppercase small font-weight-bold" style="color: #166534;">Total Advance / Refundable</span>
                                    <h4 class="font-weight-bold mb-0 text-success" id="kpi_total_advance">
                                        ₹{{ number_format($studentBalance['total_advance'] ?? 0, 2) }}
                                    </h4>
                                </div>
                                <span class="badge badge-success px-2 py-1"><i class="i-Up"></i> Advance</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded border" style="background-color: #fef2f2; border-color: #fca5a5 !important;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-uppercase small font-weight-bold" style="color: #991b1b;">Total Outstanding Dues</span>
                                    <h4 class="font-weight-bold mb-0 text-danger" id="kpi_total_dues">
                                        ₹{{ number_format($studentBalance['total_dues'] ?? 0, 2) }}
                                    </h4>
                                </div>
                                <span class="badge badge-danger px-2 py-1"><i class="i-Down"></i> Dues</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded border" id="kpi_net_box" style="background-color: {{ ($studentBalance['net_balance'] ?? 0) >= 0 ? '#f0fdf4' : '#fff7ed' }}; border-color: {{ ($studentBalance['net_balance'] ?? 0) >= 0 ? '#86efac' : '#fdba74' }} !important;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-uppercase small font-weight-bold" id="kpi_net_label" style="color: {{ ($studentBalance['net_balance'] ?? 0) >= 0 ? '#166534' : '#c2410c' }};">
                                        Net Student Position
                                    </span>
                                    <h4 class="font-weight-bold mb-0" id="kpi_net_balance" style="color: {{ ($studentBalance['net_balance'] ?? 0) >= 0 ? '#15803d' : '#ea580c' }};">
                                        {{ ($studentBalance['net_balance'] ?? 0) >= 0 ? '+' : '' }}₹{{ number_format($studentBalance['net_balance'] ?? 0, 2) }}
                                    </h4>
                                </div>
                                <span class="badge badge-dark px-2 py-1" id="kpi_net_badge">
                                    {{ ($studentBalance['net_balance'] ?? 0) >= 0 ? 'Credit Balance' : 'Net Due' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Head-Wise Breakdown Table --}}
                <div class="table-responsive border rounded" style="max-height: 380px; overflow-y: auto;">
                    <table class="table table-bordered table-hover table-striped mb-0 text-center" style="font-size: 13px;">
                        <thead style="background-color: #f1f5f9; color: #334155;" class="sticky-top">
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th class="text-left">Fee Head Name</th>
                                <th style="width: 155px;">Classification</th>
                                <th style="width: 110px;">Session</th>
                                <th style="width: 70px;">Class</th>
                                <th style="width: 130px;">Installment / Term</th>
                                <th style="width: 110px;" class="text-right">Fee / Due (₹)</th>
                                <th style="width: 110px;" class="text-right">Paid (₹)</th>
                                <th style="width: 130px;" class="text-right">Net Balance (₹)</th>
                                <th class="text-left" style="width: 200px;">Remarks / Due Date</th>
                            </tr>
                        </thead>
                        <tbody id="headWiseTableBody">
                            @if(!empty($studentBalance) && !empty($studentBalance['head_wise_details']))
                                @foreach($studentBalance['head_wise_details'] as $idx => $item)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td class="text-left font-weight-bold text-dark">{{ $item['head_name'] }}</td>
                                        <td>
                                            @if($item['type'] === 'advance')
                                                <span class="badge font-weight-bold" style="background-color: #ecfdf5 !important; color: #065f46 !important; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 4px; display: inline-flex; align-items: center; letter-spacing: 0.2px; border: 1px solid #a7f3d0 !important;">
                                                    <i class="fa fa-arrow-circle-up mr-1" style="font-size: 12px; color: #059669;"></i> Advance / Refundable
                                                </span>
                                            @else
                                                <span class="badge font-weight-bold" style="background-color: #fef2f2 !important; color: #991b1b !important; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 4px; display: inline-flex; align-items: center; letter-spacing: 0.2px; border: 1px solid #fecaca !important;">
                                                    <i class="fa fa-exclamation-circle mr-1" style="font-size: 12px; color: #dc2626;"></i> Outstanding Due
                                                </span>
                                            @endif
                                        </td>
                                        <td><span class="badge badge-light border text-dark font-weight-bold">{{ $item['session'] }}</span></td>
                                        <td><strong>{{ $item['class_name'] }}</strong></td>
                                        <td>{{ $item['term'] }}</td>
                                        <td class="text-right font-weight-bold" style="color: #0f172a;">₹{{ number_format($item['fee_amount'], 2) }}</td>
                                        <td class="text-right font-weight-bold" style="color: #0284c7;">₹{{ number_format($item['paid_amount'], 2) }}</td>
                                        <td class="text-right font-weight-bold {{ $item['type'] === 'advance' ? 'text-success' : 'text-danger' }}" style="font-size: 13.5px;">
                                            {{ $item['balance_display'] }}
                                        </td>
                                        <td class="text-left text-muted small">{{ $item['remarks'] ?? $item['due_date'] }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-3">
                                        Select a student above to automatically load head-wise dues and refundable balances across sessions.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot style="background: #1e293b; color: #ffffff; font-weight: 700; font-size: 13.5px; border-top: 2px solid #0f172a;">
                            <tr>
                                <td colspan="6" class="text-right" style="color: #cbd5e1; letter-spacing: 0.5px; vertical-align: middle;">TOTAL SUMMARY:</td>
                                <td class="text-right font-weight-bold" id="foot_fee_total" style="color: #ffffff; vertical-align: middle; font-size: 13.5px;">
                                    ₹{{ number_format(collect($studentBalance['head_wise_details'] ?? [])->sum('fee_amount'), 2) }}
                                </td>
                                <td class="text-right font-weight-bold" id="foot_paid_total" style="color: #38bdf8; vertical-align: middle; font-size: 13.5px;">
                                    ₹{{ number_format(collect($studentBalance['head_wise_details'] ?? [])->sum('paid_amount'), 2) }}
                                </td>
                                <td class="text-right font-weight-bold" id="foot_net_total" style="color: {{ ($studentBalance['net_balance'] ?? 0) >= 0 ? '#4ade80' : '#f87171' }}; vertical-align: middle; font-size: 14.5px;">
                                    {{ ($studentBalance['net_balance'] ?? 0) >= 0 ? '+' : '' }}₹{{ number_format($studentBalance['net_balance'] ?? 0, 2) }}
                                </td>
                                <td style="vertical-align: middle;">
                                    <span class="badge font-weight-bold" id="foot_net_badge" style="font-size: 11px; padding: 3px 8px; border-radius: 4px; {{ ($studentBalance['net_balance'] ?? 0) >= 0 ? 'background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0;' : 'background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca;' }}">
                                        {{ ($studentBalance['net_balance'] ?? 0) >= 0 ? 'Credit' : 'Net Due' }}
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- CARD 3: Refund Amount & Payment Details --}}
        <div class="rv-card">
            <div class="card-body" style="padding: 26px 30px;">
                <div class="rv-section-header d-flex justify-content-between align-items-center">
                    <div>
                        <span><i class="i-Money-2 mr-1"></i> 3. Refund Amount & Payment Details</span>
                        <span class="text-muted small ml-2">Step 3: Enter Refund Info</span>
                    </div>
                </div>

                {{-- Live Balance Banner --}}
                <div class="balance-badge-box" id="balanceBanner" style="display: none; padding: 18px 24px; margin-bottom: 24px;">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <span class="text-muted small font-weight-bold text-uppercase">Original Refundable Head:</span>
                            <div class="font-weight-bold text-dark mt-1" style="font-size: 17px;" id="disp_orig_refundable">₹0.00</div>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted small font-weight-bold text-uppercase">Already Refunded:</span>
                            <div class="font-weight-bold text-danger mt-1" style="font-size: 17px;" id="disp_already_refunded">₹0.00</div>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted small font-weight-bold text-uppercase">Available to Refund Now:</span>
                            <div class="font-weight-bold text-success mt-1" style="font-size: 22px;" id="disp_available_balance">₹0.00</div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    {{-- Refund Head --}}
                    <div class="col-md-4 form-group">
                        <label class="rv-label">Refund Fee Head <span class="text-danger">*</span></label>
                        <select name="refund_head" id="refund_head" class="form-control" required style="height: 40px;">
                            @foreach($refundHeads as $head)
                                <option value="{{ $head }}" {{ old('refund_head') === $head ? 'selected' : '' }}>
                                    {{ $head }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Supports Caution Money and any future refundable fee head.</small>
                    </div>

                    {{-- Refund Amount --}}
                    <div class="col-md-4 form-group">
                        <label class="rv-label">Refund Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="1" name="refund_amount" id="refund_amount" class="form-control font-weight-bold" style="font-size: 18px; color: #047857; height: 40px;" value="{{ old('refund_amount', 3000) }}" required placeholder="0.00">
                        <small id="amountHelp" class="text-muted d-block mt-1">Max refundable balance: <span id="maxBalanceText" class="font-weight-bold text-success">₹0.00</span></small>
                    </div>

                    {{-- Payment Mode --}}
                    <div class="col-md-4 form-group">
                        <label class="rv-label">Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode" id="payment_mode" class="form-control font-weight-bold" required style="height: 40px;">
                            <option value="Cheque" {{ old('payment_mode', 'Cheque') === 'Cheque' ? 'selected' : '' }}>Cheque</option>
                            <option value="Cash" {{ old('payment_mode') === 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="Online/NEFT" {{ old('payment_mode') === 'Online/NEFT' ? 'selected' : '' }}>Online / NEFT</option>
                        </select>
                    </div>
                </div>

                {{-- Cheque Specific Fields --}}
                <div id="chequeDetailsSection" class="p-4 mb-4 border rounded" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                    <h6 class="font-weight-bold text-primary mb-3">
                        <i class="i-Credit-Card mr-1"></i> Cheque / Bank Details
                    </h6>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="rv-label">Cheque Number <span class="text-danger">*</span></label>
                            <input type="text" name="cheque_no" id="cheque_no" class="form-control" placeholder="e.g. 444444" value="{{ old('cheque_no') }}" style="height: 40px;">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="rv-label">Cheque Date <span class="text-danger">*</span></label>
                            <input type="date" name="cheque_date" id="cheque_date" class="form-control" value="{{ old('cheque_date', date('Y-m-d')) }}" style="height: 40px;">
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="rv-label">Bank Account Name</label>
                            <select name="bank_name" id="bank_name" class="form-control" style="height: 40px;">
                                <option value="">-- Select Bank Account --</option>
                                @foreach($bankAccounts as $acc)
                                    <option value="{{ $acc }}" {{ old('bank_name', 'CHEQUE RETURN A/C') === $acc ? 'selected' : '' }}>{{ $acc }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Cash Specific Field --}}
                <div id="cashDetailsSection" class="p-4 mb-4 border rounded" style="background-color: #f8fafc; border-color: #e2e8f0 !important; display: none;">
                    <h6 class="font-weight-bold text-warning mr-1 mb-3">
                        <i class="i-Money mr-1"></i> Cash Counter Details
                    </h6>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="rv-label">Cash Account</label>
                            <input type="text" name="cash_account" id="cash_account" class="form-control" value="{{ old('cash_account', 'CASH A/C') }}" placeholder="CASH A/C" style="height: 40px;">
                        </div>
                    </div>
                </div>

                {{-- Remarks --}}
                <div class="row">
                    <div class="col-md-12 form-group mb-0">
                        <label class="rv-label">Remarks / Narration (Optional)</label>
                        <textarea name="remarks" id="remarks" rows="2" class="form-control" style="padding: 10px 14px;" placeholder="e.g. Caution Money refunded upon T.C. generation. Handed over to student's father.">{{ old('remarks') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="d-flex justify-content-between align-items-center mb-5">
            <a href="{{ route('refund-vouchers.index') }}" class="btn btn-secondary font-weight-bold px-4">
                <i class="i-Close mr-1"></i> Cancel
            </a>
            <div style="gap: 10px;" class="d-flex">
                <button type="reset" class="btn btn-outline-danger font-weight-bold px-3">
                    <i class="i-Repeat-2 mr-1"></i> Reset
                </button>
                <button type="submit" id="saveVoucherBtn" class="btn btn-success btn-lg font-weight-bold px-4 shadow">
                    <i class="i-Checked-User mr-1"></i> Save & Generate Voucher
                </button>
            </div>
        </div>
    </form>
</div>

<script>
let currentMaxBalance = 0;

function togglePaymentMode() {
    const mode = document.getElementById('payment_mode').value;
    const chqSection = document.getElementById('chequeDetailsSection');
    const cashSection = document.getElementById('cashDetailsSection');
    const chqNo = document.getElementById('cheque_no');
    const chqDt = document.getElementById('cheque_date');

    if (mode === 'Cheque') {
        chqSection.style.display = 'block';
        cashSection.style.display = 'none';
        chqNo.required = true;
        chqDt.required = true;
    } else if (mode === 'Cash') {
        chqSection.style.display = 'none';
        cashSection.style.display = 'block';
        chqNo.required = false;
        chqDt.required = false;
    } else {
        chqSection.style.display = 'none';
        cashSection.style.display = 'none';
        chqNo.required = false;
        chqDt.required = false;
    }
}

function clearStudentInfo() {
    document.getElementById('disp_scholar_no').value = '';
    document.getElementById('disp_student_name').value = '';
    document.getElementById('disp_father_name').value = '';
    document.getElementById('disp_class_name').value = '';
    document.getElementById('disp_section_name').value = '';
    document.getElementById('is_discontinued').checked = false;
    const ledgerBtn = document.getElementById('view_ledger_btn');
    if (ledgerBtn) ledgerBtn.style.display = 'none';
    const ledgerBtn2 = document.getElementById('view_ledger_btn_card2');
    if (ledgerBtn2) ledgerBtn2.style.display = 'none';
    document.getElementById('balanceBanner').style.display = 'none';
    document.getElementById('refund_amount').value = '0.00';
    document.getElementById('saveVoucherBtn').disabled = true;

    // Reset head-wise card
    const hwCard = document.getElementById('headWiseCard');
    if (hwCard) hwCard.style.display = 'none';
    const hwBody = document.getElementById('headWiseTableBody');
    if (hwBody) {
        hwBody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-3">Select a student above to automatically load head-wise dues and refundable balances across sessions.</td></tr>';
    }
    const alertBox = document.getElementById('duesAlertBox');
    if (alertBox) alertBox.innerHTML = '';
}

function updateStudentInfo(student) {
    if (!student || !student.id) {
        clearStudentInfo();
        return;
    }

    document.getElementById('disp_scholar_no').value = student.scholar_no || '';
    document.getElementById('disp_student_name').value = student.student_name || '';
    document.getElementById('disp_father_name').value = student.father_name || '';
    document.getElementById('disp_class_name').value = student.class_name || '';
    document.getElementById('disp_section_name').value = student.section_name || 'N/A';

    if (student.has_tc) {
        document.getElementById('is_discontinued').checked = true;
    }

    // Ledger links
    const ledgerBtn = document.getElementById('view_ledger_btn');
    if (ledgerBtn) {
        ledgerBtn.href = "{{ url('view-student-ledger') }}/" + student.id;
        ledgerBtn.style.display = 'inline-flex';
    }
    const ledgerBtn2 = document.getElementById('view_ledger_btn_card2');
    if (ledgerBtn2) {
        ledgerBtn2.href = "{{ url('view-student-ledger') }}/" + student.id;
        ledgerBtn2.style.display = 'inline-flex';
    }

    // Fetch balance
    fetchBalance(student.id);
}

function onStudentSelected() {
    const select = document.getElementById('student_search_select');
    if (!select) return;
    const selectedOption = select.options[select.selectedIndex];
    if (!selectedOption || !selectedOption.value) {
        clearStudentInfo();
        return;
    }

    const student = {
        id: selectedOption.value,
        scholar_no: selectedOption.getAttribute('data-scholar') || '',
        student_name: selectedOption.getAttribute('data-name') || '',
        father_name: selectedOption.getAttribute('data-father') || '',
        class_name: selectedOption.getAttribute('data-class') || '',
        section_name: selectedOption.getAttribute('data-section') || 'N/A',
        has_tc: false
    };

    updateStudentInfo(student);
}

function fetchBalance(studentId) {
    if (!studentId) return;
    const head = document.getElementById('refund_head').value || 'CAUTION MONEY';
    const url = "{{ url('refund-vouchers/student-balance') }}/" + studentId + "?head=" + encodeURIComponent(head);

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(data => {
            currentMaxBalance = parseFloat(data.available_balance || 0);

            if (data.father_name && !document.getElementById('disp_father_name').value) {
                document.getElementById('disp_father_name').value = data.father_name;
            }
            if (data.section_name) {
                document.getElementById('disp_section_name').value = data.section_name;
            } else if (!document.getElementById('disp_section_name').value) {
                document.getElementById('disp_section_name').value = 'N/A';
            }
            if (data.class_name && !document.getElementById('disp_class_name').value) {
                document.getElementById('disp_class_name').value = data.class_name;
            }
            if (data.scholar_no && !document.getElementById('disp_scholar_no').value) {
                document.getElementById('disp_scholar_no').value = data.scholar_no;
            }
            if (data.student_name && !document.getElementById('disp_student_name').value) {
                document.getElementById('disp_student_name').value = data.student_name;
            }
            if (data.has_tc) {
                document.getElementById('is_discontinued').checked = true;
            }

            document.getElementById('disp_orig_refundable').textContent = '₹' + parseFloat(data.original_refundable || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('disp_already_refunded').textContent = '₹' + parseFloat(data.already_refunded || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('disp_available_balance').textContent = '₹' + currentMaxBalance.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('maxBalanceText').textContent = '₹' + currentMaxBalance.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

            document.getElementById('balanceBanner').style.display = 'block';

            // Update Head-Wise Dues & Advance Card
            const hwCard = document.getElementById('headWiseCard');
            if (hwCard) {
                hwCard.style.display = 'block';

                const totalAdv = parseFloat(data.total_advance || 0);
                const totalDue = parseFloat(data.total_dues || 0);
                const netBal = parseFloat(data.net_balance || 0);

                const kpiAdv = document.getElementById('kpi_total_advance');
                if (kpiAdv) kpiAdv.textContent = '₹' + totalAdv.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                const kpiDue = document.getElementById('kpi_total_dues');
                if (kpiDue) kpiDue.textContent = '₹' + totalDue.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                const kpiNet = document.getElementById('kpi_net_balance');
                const kpiBadge = document.getElementById('kpi_net_badge');
                const kpiLabel = document.getElementById('kpi_net_label');
                const kpiBox = document.getElementById('kpi_net_box');

                if (kpiNet) {
                    const sign = netBal >= 0 ? '+' : '';
                    kpiNet.textContent = sign + '₹' + netBal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    if (netBal >= 0) {
                        kpiNet.style.color = '#15803d';
                        if (kpiLabel) kpiLabel.style.color = '#166534';
                        if (kpiBadge) {
                            kpiBadge.textContent = 'Credit Balance';
                            kpiBadge.className = 'badge badge-success px-2 py-1';
                        }
                        if (kpiBox) {
                            kpiBox.style.backgroundColor = '#f0fdf4';
                            kpiBox.style.borderColor = '#86efac';
                        }
                    } else {
                        kpiNet.style.color = '#ea580c';
                        if (kpiLabel) kpiLabel.style.color = '#c2410c';
                        if (kpiBadge) {
                            kpiBadge.textContent = 'Net Due';
                            kpiBadge.className = 'badge badge-danger px-2 py-1';
                        }
                        if (kpiBox) {
                            kpiBox.style.backgroundColor = '#fff7ed';
                            kpiBox.style.borderColor = '#fdba74';
                        }
                    }
                }

                // Alert Banner
                const alertBox = document.getElementById('duesAlertBox');
                if (alertBox) {
                    if (data.has_outstanding_dues) {
                        alertBox.innerHTML = `
                            <div class="alert alert-danger d-flex align-items-center mb-0 shadow-sm" role="alert" style="border-left: 5px solid #dc2626; background-color: #fef2f2; color: #991b1b;">
                                <i class="i-Danger font-weight-bold mr-2" style="font-size: 26px;"></i>
                                <div>
                                    <strong style="font-size: 14px;">Attention: Outstanding Fee Dues Detected!</strong>
                                    <div>This student has pending fee dues of <strong class="text-danger">₹${totalDue.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong> across academic sessions. Available Caution Money is <strong class="text-success">₹${totalAdv.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>. Please review pending dues before issuing refund.</div>
                                </div>
                            </div>
                        `;
                    } else {
                        alertBox.innerHTML = `
                            <div class="alert alert-success d-flex align-items-center mb-0 shadow-sm" role="alert" style="border-left: 5px solid #16a34a; background-color: #f0fdf4; color: #166534;">
                                <i class="i-Yes font-weight-bold mr-2" style="font-size: 26px;"></i>
                                <div>
                                    <strong style="font-size: 14px;">All Academic Fees Cleared!</strong>
                                    <div>No outstanding fee dues found across previous or current sessions. Student is eligible for full Caution Money refund.</div>
                                </div>
                            </div>
                        `;
                    }
                }

                // Table rows
                const hwBody = document.getElementById('headWiseTableBody');
                if (hwBody) {
                    const details = data.head_wise_details || [];
                    if (details.length === 0) {
                        hwBody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-3">No dues or advance balances found for this student.</td></tr>';
                    } else {
                        let html = '';
                        let sumFee = 0;
                        let sumPaid = 0;
                        details.forEach((item, idx) => {
                            const fee = parseFloat(item.fee_amount || 0);
                            const paid = parseFloat(item.paid_amount || 0);
                            sumFee += fee;
                            sumPaid += paid;

                            const isAdv = item.type === 'advance';
                            const badge = isAdv
                                ? `<span class="badge font-weight-bold" style="background-color: #ecfdf5 !important; color: #065f46 !important; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 4px; display: inline-flex; align-items: center; letter-spacing: 0.2px; border: 1px solid #a7f3d0 !important;"><i class="fa fa-arrow-circle-up mr-1" style="font-size: 12px; color: #059669;"></i> Advance / Refundable</span>`
                                : `<span class="badge font-weight-bold" style="background-color: #fef2f2 !important; color: #991b1b !important; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 4px; display: inline-flex; align-items: center; letter-spacing: 0.2px; border: 1px solid #fecaca !important;"><i class="fa fa-exclamation-circle mr-1" style="font-size: 12px; color: #dc2626;"></i> Outstanding Due</span>`;

                            const balClass = isAdv ? 'text-success' : 'text-danger';
                            const rem = item.remarks || item.due_date || '-';

                            html += `
                                <tr>
                                    <td>${idx + 1}</td>
                                    <td class="text-left font-weight-bold text-dark">${item.head_name}</td>
                                    <td>${badge}</td>
                                    <td><span class="badge badge-light border text-dark font-weight-bold">${item.session}</span></td>
                                    <td><strong>${item.class_name}</strong></td>
                                    <td>${item.term}</td>
                                    <td class="text-right font-weight-bold" style="color: #0f172a;">₹${fee.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    <td class="text-right font-weight-bold" style="color: #0284c7;">₹${paid.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                    <td class="text-right font-weight-bold ${balClass}" style="font-size: 13.5px;">${item.balance_display}</td>
                                    <td class="text-left text-muted small">${rem}</td>
                                </tr>
                            `;
                        });
                        hwBody.innerHTML = html;

                        const footFee = document.getElementById('foot_fee_total');
                        if (footFee) footFee.textContent = '₹' + sumFee.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        const footPaid = document.getElementById('foot_paid_total');
                        if (footPaid) footPaid.textContent = '₹' + sumPaid.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        const footNet = document.getElementById('foot_net_total');
                        if (footNet) {
                            const sign = netBal >= 0 ? '+' : '';
                            footNet.textContent = sign + '₹' + netBal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            footNet.style.color = netBal >= 0 ? '#4ade80' : '#f87171';
                        }
                        const footBadge = document.getElementById('foot_net_badge');
                        if (footBadge) {
                            footBadge.textContent = netBal >= 0 ? 'Credit' : 'Net Due';
                            footBadge.className = 'badge font-weight-bold';
                            footBadge.style.cssText = 'font-size: 11px; padding: 3px 8px; border-radius: 4px; ' + (netBal >= 0 ? 'background-color: #ecfdf5 !important; color: #065f46 !important; border: 1px solid #a7f3d0 !important;' : 'background-color: #fef2f2 !important; color: #991b1b !important; border: 1px solid #fecaca !important;');
                        }
                    }
                }
            }

            // Auto-set refund amount to available balance if currently higher or empty
            const amountInput = document.getElementById('refund_amount');
            if (currentMaxBalance > 0) {
                amountInput.value = currentMaxBalance.toFixed(2);
                amountInput.max = currentMaxBalance;
                document.getElementById('saveVoucherBtn').disabled = false;
            } else {
                amountInput.value = '0.00';
                amountInput.max = 0;
                document.getElementById('saveVoucherBtn').disabled = true;
                alert('Notice: Student has ₹0.00 refundable balance for ' + head + '. Already fully refunded or not deposited.');
            }
        })
        .catch(err => console.error(err));
}

document.addEventListener('DOMContentLoaded', function () {
    const paymentModeSelect = document.getElementById('payment_mode');
    paymentModeSelect.addEventListener('change', togglePaymentMode);
    togglePaymentMode();

    const refundHeadSelect = document.getElementById('refund_head');
    refundHeadSelect.addEventListener('change', function () {
        const studentId = document.getElementById('student_search_select').value;
        if (studentId) {
            fetchBalance(studentId);
        }
    });

    const refundAmountInput = document.getElementById('refund_amount');
    refundAmountInput.addEventListener('input', function () {
        const entered = parseFloat(this.value || 0);
        if (entered > currentMaxBalance) {
            this.classList.add('is-invalid');
            document.getElementById('amountHelp').innerHTML = '<span class="text-danger font-weight-bold">Amount exceeds available balance of ₹' + currentMaxBalance.toFixed(2) + '!</span>';
            document.getElementById('saveVoucherBtn').disabled = true;
        } else if (entered <= 0) {
            this.classList.add('is-invalid');
            document.getElementById('amountHelp').innerHTML = '<span class="text-danger font-weight-bold">Amount must be greater than ₹0.00!</span>';
            document.getElementById('saveVoucherBtn').disabled = true;
        } else {
            this.classList.remove('is-invalid');
            document.getElementById('amountHelp').innerHTML = 'Max refundable balance: <span class="font-weight-bold text-success">₹' + currentMaxBalance.toFixed(2) + '</span>';
            document.getElementById('saveVoucherBtn').disabled = false;
        }
    });

    if (window.jQuery) {
        // Initialize Select2 if not yet initialized
        if ($.fn.select2) {
            $('#student_search_select').select2({
                width: '100%',
                placeholder: 'Type Scholar No or Student Name...',
                allowClear: true,
                ajax: {
                    url: "{{ route('refund-vouchers.search-student') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                            head: document.getElementById('refund_head').value || 'CAUTION MONEY'
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data.map(function (item) {
                                return {
                                    id: item.id,
                                    text: item.scholar_no ? (item.scholar_no + ' - ' + item.student_name + ' (Class: ' + (item.class_name || 'N/A') + ((item.section_name && item.section_name !== 'N/A') ? ' / ' + item.section_name : '') + ')') : (item.student_name + ' (Class: ' + (item.class_name || 'N/A') + ')'),
                                    scholar_no: item.scholar_no,
                                    student_name: item.student_name,
                                    father_name: item.father_name,
                                    class_name: item.class_name,
                                    section_name: item.section_name,
                                    has_tc: item.has_tc,
                                    available_balance: item.available_balance
                                };
                            })
                        };
                    },
                    cache: true
                }
            });
        }

        // Attach event listener for select2 selection
        $('#student_search_select').on('select2:select', function (e) {
            const data = e.params.data;
            updateStudentInfo(data);
        });

        $('#student_search_select').on('change', function () {
            if (!$(this).val()) {
                clearStudentInfo();
            } else {
                onStudentSelected();
            }
        });

        // Trigger if a student is already selected on page load
        if ($('#student_search_select').val()) {
            onStudentSelected();
        }
    } else {
        document.getElementById('student_search_select')?.addEventListener('change', onStudentSelected);
    }
});
</script>
@endsection
