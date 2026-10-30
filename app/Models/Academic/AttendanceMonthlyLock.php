<?php

namespace App\Models\Academic;

use App\Models\Classname;
use App\Models\Classes;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AttendanceMonthlyLock extends Model
{
    protected $table = 'academic_attendance_monthly_locks';

    protected $fillable = [
        'academic_session',
        'class_id',
        'section_name',
        'year',
        'month',
        'is_locked',
        'locked_at',
        'locked_by',
        'unlocked_at',
        'unlocked_by',
        'remarks',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'unlocked_at' => 'datetime',
    ];

    public function lockedByUser()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function unlockedByUser()
    {
        return $this->belongsTo(User::class, 'unlocked_by');
    }

    /**
     * Determine if attendance for a specific class, section, year, and month is locked.
     */
    public static function isMonthLocked($classId, $sectionName, int $year, int $month, ?string $session = null): bool
    {
        if (!$classId || !$sectionName) {
            return false;
        }

        $class = Classes::query()->where('id', $classId)->orWhere('class_name', $classId)->first();
        if (!$class && class_exists(Classname::class)) {
            $class = Classname::query()->where('id', $classId)->orWhere('class_name', $classId)->first();
        }
        $className = optional($class)->class_name ?: (string) $classId;
        $classIds = Classes::query()->where('class_name', $className)->pluck('id')->push($classId)->unique()->values();

        // 1. Check explicit monthly lock table record
        $monthlyLock = self::where(function ($query) use ($classIds, $className, $classId) {
                $query->whereIn('class_id', $classIds)
                    ->orWhere('class_id', $className)
                    ->orWhere('class_id', $classId);
            })
            ->where(function ($query) use ($sectionName) {
                $query->where('section_name', $sectionName)
                    ->orWhere('section_name', 'All');
            })
            ->where('year', $year)
            ->where('month', $month)
            ->latest('id')
            ->first();

        return (bool) optional($monthlyLock)->is_locked;
    }

    /**
     * Get lock information for display.
     */
    public static function getLockInfo($classId, $sectionName, int $year, int $month, ?string $session = null): ?self
    {
        if (!$classId || !$sectionName) {
            return null;
        }

        $class = Classes::query()->where('id', $classId)->orWhere('class_name', $classId)->first();
        if (!$class && class_exists(Classname::class)) {
            $class = Classname::query()->where('id', $classId)->orWhere('class_name', $classId)->first();
        }
        $className = optional($class)->class_name ?: (string) $classId;
        $classIds = Classes::query()->where('class_name', $className)->pluck('id')->push($classId)->unique()->values();

        return self::where(function ($query) use ($classIds, $className, $classId) {
                $query->whereIn('class_id', $classIds)
                    ->orWhere('class_id', $className)
                    ->orWhere('class_id', $classId);
            })
            ->where(function ($query) use ($sectionName) {
                $query->where('section_name', $sectionName)
                    ->orWhere('section_name', 'All');
            })
            ->where('year', $year)
            ->where('month', $month)
            ->latest('id')
            ->first();
    }
}
