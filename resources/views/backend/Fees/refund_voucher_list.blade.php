@extends('backend.layouts.main')

@section('main-container')
<style>
    .rv-card {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        margin-bottom: 20px;
    }
    .kpi-stat-card {
        border-radius: 10px;
        padding: 16px 20px;
        color: #fff;
        margin-bottom: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .kpi-stat-card h3 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 4px;
        color: #fff;
    }
    .kpi-stat-card p {
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 0;
        opacity: 0.9;
    }
    .table-rv th {
        background-color: #f8fafc;
        color: #334155;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
        padding: 10px 12px;
        vertical-align: middle;
    }
    .table-rv td {
        padding: 10px 12px;
        vertical-align: middle;
        font-size: 13px;
        color: #1e293b;
    }
    .badge-status-completed {
        background-color: #dcfce7;
        color: #166534;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
    }
    .badge-status-cancelled {
        background-color: #fee2e2;
        color: #991b1b;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
    }
    /* Ensure table-responsive does not clip action dropdown menus */
    .rv-table-wrap {
        position: relative;
        min-height: 260px;
    }
    .rv-table-wrap .table-responsive {
        overflow: visible;
    }
    @media (max-width: 991.98px) {
        .rv-table-wrap .table-responsive {
            overflow-x: auto;
            overflow-y: visible;
        }
    }
    /* Clean, pixel-perfect alignment for table action buttons */
    .rv-action-group {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        vertical-align: middle !important;
        white-space: nowrap !important;
        gap: 6px;
    }
    .rv-action-group .btn {
        height: 30px !important;
        line-height: 28px !important;
        padding: 0 10px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        vertical-align: middle !important;
        border-radius: 4px !important;
        margin: 0 !important;
        box-sizing: border-box !important;
    }
    .rv-action-group .btn i {
        font-size: 13px !important;
        line-height: 1 !important;
        vertical-align: middle !important;
    }
    .rv-action-group .dropdown {
        display: inline-flex !important;
        align-items: center !important;
        vertical-align: middle !important;
        position: relative !important;
        margin: 0 !important;
    }
    .rv-action-group .dropdown-menu {
        position: absolute !important;
        top: 100% !important;
        right: 0 !important;
        left: auto !important;
        margin-top: 4px !important;
        transform: none !important;
    }
    .dropdown-menu {
        z-index: 1065 !important;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        min-width: 190px;
    }
    .dropdown-item {
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 500;
        display: flex;
        align-items: center;
        transition: background-color 0.15s ease;
    }
    .dropdown-item i {
        font-size: 15px;
        width: 20px;
    }
    .dropdown-item:hover {
        background-color: #f1f5f9;
    }
</style>

<div class="main-content">
    {{-- Breadcrumb & Title --}}
    <div class="breadcrumb d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="font-weight-bold text-dark mb-1">
                <i class="i-Receipt text-primary mr-1"></i> Refund Vouchers Register
            </h1>
            <p class="text-muted mb-0" style="font-size: 13px;">Manage student Caution Money and other refundable fee disbursements</p>
        </div>
        <div class="d-flex align-items-center" style="gap: 8px;">
            <a href="{{ route('refund-vouchers.create') }}" class="btn btn-primary font-weight-bold shadow-sm">
                <i class="i-Add mr-1"></i> Create New Voucher
            </a>
            <a href="{{ route('refund-vouchers.print-register', request()->query()) }}" target="_blank" class="btn btn-warning text-dark font-weight-bold shadow-sm">
                <i class="fa fa-print mr-1"></i> Print Register
            </a>
            <a href="{{ route('refund-vouchers.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-success font-weight-bold shadow-sm">
                <i class="i-File-Excel mr-1"></i> Export to Excel
            </a>
        </div>
    </div>
    <div class="separator-breadcrumb border-top mb-4"></div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="i-Checked-User mr-1 font-weight-bold"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="i-Close-Window mr-1 font-weight-bold"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- KPI Summary Cards --}}
    <div class="row mb-2">
        <div class="col-lg-3 col-md-6">
            <div class="kpi-stat-card" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);">
                <p>TOTAL VOUCHERS</p>
                <h3>{{ number_format($kpis['total_count']) }}</h3>
                <small class="text-white-50">All registered records</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="kpi-stat-card" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
                <p>TOTAL REFUNDED AMOUNT</p>
                <h3>₹{{ number_format($kpis['total_amount'], 2) }}</h3>
                <small class="text-white-50">Caution Money & Heads</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="kpi-stat-card" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                <p>CHEQUE DISBURSEMENTS</p>
                <h3>₹{{ number_format($kpis['cheque_amount'], 2) }}</h3>
                <small class="text-white-50">Paid via bank cheque</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="kpi-stat-card" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%);">
                <p>CASH DISBURSEMENTS</p>
                <h3>₹{{ number_format($kpis['cash_amount'], 2) }}</h3>
                <small class="text-white-50">Paid via cash counter</small>
            </div>
        </div>
    </div>

    {{-- Filters Card --}}
    <div class="rv-card">
        <div class="card-body">
            <h5 class="card-title font-weight-bold text-primary mb-3">
                <i class="i-Filter-2 mr-1"></i> Search Refund Vouchers
            </h5>
            <form method="GET" action="{{ route('refund-vouchers.index') }}">
                <div class="row">
                    <div class="col-md-2 form-group mb-2">
                        <label class="font-weight-bold text-muted small mb-1">From Date</label>
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-2 form-group mb-2">
                        <label class="font-weight-bold text-muted small mb-1">To Date</label>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-2 form-group mb-2">
                        <label class="font-weight-bold text-muted small mb-1">Voucher No.</label>
                        <input type="text" name="voucher_no" class="form-control form-control-sm" placeholder="e.g. RV-2026-0001" value="{{ request('voucher_no') }}">
                    </div>
                    <div class="col-md-2 form-group mb-2">
                        <label class="font-weight-bold text-muted small mb-1">Scholar No. / Name</label>
                        <input type="text" name="scholar_no" class="form-control form-control-sm" placeholder="Scholar or name" value="{{ request('scholar_no') }}">
                    </div>
                    <div class="col-md-2 form-group mb-2">
                        <label class="font-weight-bold text-muted small mb-1">Refund Head</label>
                        <select name="refund_head" class="form-control form-control-sm">
                            <option value="">-- All Heads --</option>
                            @foreach($heads as $h)
                                <option value="{{ $h }}" {{ request('refund_head') === $h ? 'selected' : '' }}>{{ $h }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-group mb-2">
                        <label class="font-weight-bold text-muted small mb-1">Payment Mode</label>
                        <select name="payment_mode" class="form-control form-control-sm">
                            <option value="">-- All Modes --</option>
                            <option value="Cheque" {{ request('payment_mode') === 'Cheque' ? 'selected' : '' }}>Cheque</option>
                            <option value="Cash" {{ request('payment_mode') === 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="Online/NEFT" {{ request('payment_mode') === 'Online/NEFT' ? 'selected' : '' }}>Online / NEFT</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-2" style="gap: 8px;">
                    <a href="{{ route('refund-vouchers.index') }}" class="btn btn-outline-secondary btn-sm font-weight-bold">
                        <i class="i-Repeat-2 mr-1"></i> Reset
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold px-3">
                        <i class="i-Magnifi-Glass1 mr-1"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Vouchers Table Card --}}
    <div class="rv-card">
        <div class="card-body p-0">
            <div class="rv-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover table-rv mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Voucher No</th>
                                <th>Date</th>
                                <th>Scholar ID</th>
                                <th>Student Name</th>
                                <th>Class / Sec</th>
                                <th>Refund Head</th>
                                <th class="text-right">Amount (₹)</th>
                                <th>Payment Mode</th>
                                <th>Cheque / Bank Details</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" style="width: 175px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vouchers as $index => $v)
                                <tr>
                                    <td>{{ $vouchers->firstItem() + $index }}</td>
                                    <td>
                                        <strong class="text-primary font-weight-bold">{{ $v->voucher_no }}</strong>
                                    </td>
                                    <td>{{ optional($v->voucher_date)->format('d-m-Y') }}</td>
                                    <td>
                                        <span class="badge badge-light border text-dark font-weight-bold">{{ $v->scholar_no }}</span>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold">{{ $v->student_name }}</span>
                                        @if($v->father_name)
                                            <br><small class="text-muted">S/D/O {{ $v->father_name }}</small>
                                        @endif
                                        @if($v->is_discontinued)
                                            <span class="badge badge-warning text-dark ml-1" style="font-size: 10px;">TC / Discontinued</span>
                                        @endif
                                    </td>
                                    <td>{{ $v->class_name ?? '-' }} {{ $v->section_name ? '(' . $v->section_name . ')' : '' }}</td>
                                    <td>
                                        <span class="badge badge-info text-white" style="font-size: 11px;">{{ $v->refund_head }}</span>
                                    </td>
                                    <td class="text-right font-weight-bold" style="font-size: 14px; color: #047857;">
                                        ₹{{ number_format($v->refund_amount, 2) }}
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $v->payment_mode === 'Cheque' ? 'primary' : 'warning text-dark' }}">
                                            {{ $v->payment_mode }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($v->payment_mode === 'Cheque')
                                            <div style="line-height: 1.3;">
                                                <span class="font-weight-bold text-dark">Chq: {{ $v->cheque_no ?? 'N/A' }}</span><br>
                                                <small class="text-muted">
                                                    Dt: {{ $v->cheque_date ? optional($v->cheque_date)->format('d-m-Y') : '-' }} | {{ $v->bank_name ?? '-' }}
                                                </small>
                                            </div>
                                        @elseif($v->cash_account)
                                            <small class="text-muted">A/C: {{ $v->cash_account }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($v->status === 'completed')
                                            <span class="badge-status-completed">Completed</span>
                                        @else
                                            <span class="badge-status-cancelled" title="{{ $v->cancel_reason ?? 'Cancelled' }}">Cancelled</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="rv-action-group">
                                            <a href="{{ route('refund-vouchers.show', $v->id) }}" class="btn btn-outline-info btn-sm" title="View Voucher Details">
                                                <i class="i-Eye mr-1"></i> View
                                            </a>
                                            <div class="dropdown">
                                                <button class="btn btn-outline-primary btn-sm dropdown-toggle font-weight-bold rv-action-btn" type="button" id="actionMenu{{ $v->id }}" data-toggle="dropdown" data-bs-toggle="dropdown" data-display="static" data-bs-display="static" data-boundary="viewport" data-bs-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-end shadow-sm" aria-labelledby="actionMenu{{ $v->id }}">
                                                    <a class="dropdown-item text-primary" href="{{ route('refund-vouchers.print', $v->id) }}" target="_blank">
                                                        <i class="fa fa-print mr-2 text-primary"></i> Print Voucher Slip
                                                    </a>
                                                    <a class="dropdown-item text-info" href="{{ route('refund-vouchers.show', $v->id) }}">
                                                        <i class="i-Eye mr-2 text-info"></i> View Details
                                                    </a>
                                                    <a class="dropdown-item text-success" href="{{ route('view-student-ledger', $v->student_id) }}" target="_blank">
                                                        <i class="i-Receipt mr-2 text-success"></i> Student Ledger
                                                    </a>
                                                    @if($v->status === 'completed')
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="confirmCancelVoucher({{ $v->id }}, '{{ $v->voucher_no }}', '{{ number_format($v->refund_amount, 2) }}')">
                                                            <i class="i-Close mr-2 text-danger"></i> Cancel Voucher
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-4 text-muted">
                                        <i class="i-Receipt text-muted" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                                        No refund vouchers found matching your filter criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            @if($vouchers->hasPages())
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        Showing {{ $vouchers->firstItem() }} to {{ $vouchers->lastItem() }} of {{ $vouchers->total() }} vouchers
                    </span>
                    <div>
                        {{ $vouchers->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
<div class="modal fade" id="cancelVoucherModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="cancelVoucherForm" method="POST" action="">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title font-weight-bold text-white">
                        <i class="i-Close-Window mr-1"></i> Cancel Refund Voucher
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-dark">
                        Are you sure you want to cancel Refund Voucher <strong id="cancelVoucherNo" class="text-danger"></strong> for <strong id="cancelVoucherAmount"></strong>?
                    </p>
                    <div class="alert alert-warning small mb-3">
                        <i class="i-Information mr-1"></i> Cancelling this voucher will automatically reverse the debit entry and restore the refundable Caution Money balance in the student's ledger.
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small text-muted">Cancellation Reason <span class="text-danger">*</span></label>
                        <textarea name="cancel_reason" class="form-control" rows="2" placeholder="State reason for cancelling this refund voucher..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger font-weight-bold">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmCancelVoucher(id, voucherNo, amount) {
    document.getElementById('cancelVoucherNo').textContent = voucherNo;
    document.getElementById('cancelVoucherAmount').textContent = '₹' + amount;
    document.getElementById('cancelVoucherForm').action = "{{ url('refund-vouchers/cancel') }}/" + id;
    $('#cancelVoucherModal').modal('show');
}

$(document).ready(function() {
    // Robust dropdown toggle across all Bootstrap variations and table wrappers
    $(document).on('click', '.rv-action-btn, [data-bs-toggle="dropdown"], [data-toggle="dropdown"]', function(e) {
        var $btn = $(this);
        var $dropdown = $btn.closest('.dropdown');
        var $menu = $btn.siblings('.dropdown-menu');

        setTimeout(function() {
            if (!$menu.hasClass('show')) {
                $('.dropdown-menu.show').not($menu).removeClass('show');
                $('.dropdown.show').not($dropdown).removeClass('show');
                $dropdown.addClass('show');
                $menu.addClass('show');
                $btn.attr('aria-expanded', 'true');
            }
        }, 10);
    });

    // Close open dropdowns on click outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('.dropdown.show').removeClass('show');
            $('.dropdown-menu.show').removeClass('show');
            $('[data-toggle="dropdown"], [data-bs-toggle="dropdown"]').attr('aria-expanded', 'false');
        }
    });
});
</script>
@endsection
