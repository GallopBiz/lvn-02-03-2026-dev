@extends('backend.layouts.main')

@section('main-container')
<style>
    .rv-detail-card {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        margin-bottom: 20px;
    }
    .rv-detail-label {
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 2px;
    }
    .rv-detail-val {
        font-size: 15px;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 12px;
    }
</style>

<div class="main-content">
    <div class="breadcrumb d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="font-weight-bold text-dark mb-1">
                <i class="i-Receipt text-primary mr-1"></i> Refund Voucher #{{ $voucher->voucher_no }}
            </h1>
            <p class="text-muted mb-0" style="font-size: 13px;">Detailed view and audit record of student refund</p>
        </div>
        <div class="d-flex align-items-center" style="gap: 8px;">
            <a href="{{ route('refund-vouchers.print', $voucher->id) }}" target="_blank" class="btn btn-warning text-dark font-weight-bold shadow-sm">
                <i class="fa fa-print mr-1"></i> Print Voucher Slip
            </a>
            <a href="{{ route('view-student-ledger', $voucher->student_id) }}" target="_blank" class="btn btn-info font-weight-bold shadow-sm">
                <i class="i-Receipt mr-1"></i> View Student Ledger
            </a>
            <a href="{{ route('refund-vouchers.index') }}" class="btn btn-outline-secondary font-weight-bold shadow-sm">
                <i class="i-Arrow-Back-2 mr-1"></i> Back to Register
            </a>
        </div>
    </div>
    <div class="separator-breadcrumb border-top mb-4"></div>

    <div class="row">
        {{-- Card 1: Voucher & Student Information --}}
        <div class="col-lg-6">
            <div class="rv-detail-card">
                <div class="card-body">
                    <h5 class="card-title font-weight-bold text-primary mb-3">
                        <i class="i-ID-Card mr-1"></i> Student & Voucher Information
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="rv-detail-label">Voucher Number</div>
                            <div class="rv-detail-val text-primary font-weight-bold" style="font-size: 18px;">{{ $voucher->voucher_no }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="rv-detail-label">Voucher Date</div>
                            <div class="rv-detail-val">{{ optional($voucher->voucher_date)->format('d-M-Y') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="rv-detail-label">Scholar Number</div>
                            <div class="rv-detail-val">
                                <span class="badge badge-light border text-dark font-weight-bold px-2 py-1">{{ $voucher->scholar_no }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="rv-detail-label">Student Name</div>
                            <div class="rv-detail-val font-weight-bold">{{ $voucher->student_name }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="rv-detail-label">Father's Name</div>
                            <div class="rv-detail-val">{{ $voucher->father_name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="rv-detail-label">Class & Section</div>
                            <div class="rv-detail-val">{{ $voucher->class_name ?? 'N/A' }} {{ $voucher->section_name ? '(' . $voucher->section_name . ')' : '' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="rv-detail-label">Academic Session</div>
                            <div class="rv-detail-val">{{ $voucher->session_name ?? 'Current' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="rv-detail-label">Status</div>
                            <div class="rv-detail-val">
                                @if($voucher->status === 'completed')
                                    <span class="badge badge-success px-2 py-1 font-weight-bold">Completed</span>
                                @else
                                    <span class="badge badge-danger px-2 py-1 font-weight-bold">Cancelled</span>
                                @endif
                                @if($voucher->is_discontinued)
                                    <span class="badge badge-warning text-dark ml-1">T.C. Issued</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Payment & Financial Details --}}
        <div class="col-lg-6">
            <div class="rv-detail-card">
                <div class="card-body">
                    <h5 class="card-title font-weight-bold text-success mb-3">
                        <i class="i-Money mr-1"></i> Payment & Financial Details
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="rv-detail-label">Refund Fee Head</div>
                            <div class="rv-detail-val font-weight-bold text-info">{{ $voucher->refund_head }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="rv-detail-label">Refund Amount</div>
                            <div class="rv-detail-val font-weight-bold text-success" style="font-size: 20px;">
                                ₹{{ number_format($voucher->refund_amount, 2) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="rv-detail-label">Payment Mode</div>
                            <div class="rv-detail-val">
                                <span class="badge badge-primary px-2 py-1 font-weight-bold">{{ $voucher->payment_mode }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="rv-detail-label">Bank Account / Counter</div>
                            <div class="rv-detail-val">{{ $voucher->bank_name ?? $voucher->cash_account ?? 'N/A' }}</div>
                        </div>
                        @if($voucher->payment_mode === 'Cheque')
                            <div class="col-md-6">
                                <div class="rv-detail-label">Cheque Number</div>
                                <div class="rv-detail-val font-weight-bold">{{ $voucher->cheque_no ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="rv-detail-label">Cheque Date</div>
                                <div class="rv-detail-val">{{ $voucher->cheque_date ? optional($voucher->cheque_date)->format('d-M-Y') : 'N/A' }}</div>
                            </div>
                        @endif
                        <div class="col-md-12">
                            <div class="rv-detail-label">Remarks / Narration</div>
                            <div class="rv-detail-val text-muted">{{ $voucher->remarks ?: 'No remarks provided.' }}</div>
                        </div>
                    </div>

                    <hr>

                    {{-- Current Balance Status for this head --}}
                    <div class="p-3 bg-light rounded">
                        <h6 class="font-weight-bold text-dark small mb-2 text-uppercase">Student Refundable Balance Status Now:</h6>
                        <div class="row">
                            <div class="col-4">
                                <span class="text-muted small">Original Credit:</span><br>
                                <strong>₹{{ number_format($balanceInfo['original_refundable'], 2) }}</strong>
                            </div>
                            <div class="col-4">
                                <span class="text-muted small">Total Refunded:</span><br>
                                <strong class="text-danger">₹{{ number_format($balanceInfo['already_refunded'], 2) }}</strong>
                            </div>
                            <div class="col-4">
                                <span class="text-muted small">Balance Left:</span><br>
                                <strong class="text-success">₹{{ number_format($balanceInfo['available_balance'], 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Audit Trail --}}
        <div class="col-12">
            <div class="rv-detail-card">
                <div class="card-body">
                    <h5 class="card-title font-weight-bold text-secondary mb-3">
                        <i class="i-Clock mr-1"></i> Audit Trail & Ledger Linkage
                    </h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="rv-detail-label">Created By</div>
                            <div class="rv-detail-val">{{ $voucher->created_by_name ?? 'Administrator' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="rv-detail-label">Created At</div>
                            <div class="rv-detail-val">{{ optional($voucher->created_at)->format('d-M-Y h:i A') }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="rv-detail-label">Ledger Integration</div>
                            <div class="rv-detail-val text-success">
                                <i class="i-Checked-User mr-1 font-weight-bold"></i> Active Debit Entry in Student Ledger
                            </div>
                        </div>
                        @if($voucher->status === 'cancelled')
                            <div class="col-md-4">
                                <div class="rv-detail-label text-danger">Cancelled At</div>
                                <div class="rv-detail-val text-danger">{{ optional($voucher->cancelled_at)->format('d-M-Y h:i A') }}</div>
                            </div>
                            <div class="col-md-8">
                                <div class="rv-detail-label text-danger">Cancellation Reason</div>
                                <div class="rv-detail-val text-danger">{{ $voucher->cancel_reason }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
