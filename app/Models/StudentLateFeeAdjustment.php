<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentLateFeeAdjustment extends Model
{
    use HasFactory;

    protected $connection = 'dynamic';

    protected $table = 'student_late_fee_adjustments';

    protected $fillable = [
        'student_id',
        'scholar_no',
        'student_name',
        'class_name',
        'term',
        'original_late_fee',
        'adjusted_late_fee',
        'status',
        'reason',
        'admin_id',
        'admin_name',
        'session_name',
    ];

    /**
     * Normalize term identifier ('1st' -> '1', 'term 1' -> '1', etc.)
     */
    public static function normalizeTerm($term)
    {
        if (empty($term) || strtolower(trim($term)) === 'all') {
            return 'all';
        }
        $digits = preg_replace('/[^0-9]/', '', (string)$term);
        return !empty($digits) ? $digits : strtolower(trim((string)$term));
    }

    /**
     * Find active adjustment for a given student and term (falling back to 'all')
     */
    public static function getAdjustmentForStudentTerm($studentId, $term = null)
    {
        $normalizedTerm = self::normalizeTerm($term);

        // Check specific term first
        if ($normalizedTerm !== 'all') {
            $adj = self::where('student_id', $studentId)
                ->where('term', $normalizedTerm)
                ->first();
            if ($adj) {
                return $adj;
            }
        }

        // Fallback to 'all' terms adjustment
        return self::where('student_id', $studentId)
            ->where('term', 'all')
            ->first();
    }

    /**
     * Resolve late fee: returns adjusted amount if an adjustment exists, else default amount.
     *
     * @param int|string $studentId
     * @param string|int $term
     * @param float $defaultLateFee
     * @return float
     */
    public static function resolveLateFee($studentId, $term, $defaultLateFee)
    {
        $adjustment = self::getAdjustmentForStudentTerm($studentId, $term);
        if ($adjustment) {
            return floatval($adjustment->adjusted_late_fee);
        }
        return floatval($defaultLateFee);
    }
}
