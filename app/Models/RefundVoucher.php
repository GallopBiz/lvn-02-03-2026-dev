<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class RefundVoucher extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'refund_vouchers';

    protected $fillable = [
        'voucher_no',
        'voucher_date',
        'student_id',
        'scholar_no',
        'student_name',
        'class_name',
        'section_name',
        'father_name',
        'tc_id',
        'is_discontinued',
        'refund_head',
        'refundable_amount',
        'refund_amount',
        'payment_mode',
        'cheque_no',
        'cheque_date',
        'bank_name',
        'cash_account',
        'remarks',
        'status',
        'cancel_reason',
        'cancelled_at',
        'cancelled_by',
        'created_by',
        'created_by_name',
        'session_name',
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'cheque_date' => 'date',
        'refundable_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'is_discontinued' => 'boolean',
    ];

    /**
     * Relationship to Student Registration
     */
    public function student()
    {
        return $this->belongsTo(StudentRegistration::class, 'student_id');
    }

    /**
     * Relationship to Transfer Certificate
     */
    public function transferCertificate()
    {
        return $this->belongsTo(TransferCertificate::class, 'tc_id');
    }

    /**
     * Generate next sequential voucher number
     * Format: RV-YYYY-0001 (e.g. RV-2026-0001)
     */
    public static function generateNextVoucherNo(?string $sessionYear = null): string
    {
        $year = $sessionYear ? substr($sessionYear, 0, 4) : date('Y');
        $prefix = "RV-{$year}-";

        // Find highest existing sequence with this prefix
        $latest = static::withTrashed()
            ->where('voucher_no', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('voucher_no');

        if ($latest && preg_match('/-(\d+)$/', $latest, $matches)) {
            $nextSeq = (int) $matches[1] + 1;
        } else {
            // Count all vouchers + 1 as fallback starting point
            $count = static::withTrashed()->count();
            $nextSeq = $count + 1;
        }

        $voucherNo = $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);

        // Ensure uniqueness
        while (static::withTrashed()->where('voucher_no', $voucherNo)->exists()) {
            $nextSeq++;
            $voucherNo = $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
        }

        return $voucherNo;
    }
}
