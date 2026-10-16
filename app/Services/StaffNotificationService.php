<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\HrmsEmployee;
use App\Models\HrmsLeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class StaffNotificationService
{
    public const TYPE_LEAVE = 'STAFF_LEAVE';
    public const TYPE_BIRTHDAY = 'STAFF_BIRTHDAY';

    /**
     * Synchronize leave requests and upcoming staff birthdays for the given user.
     */
    public function syncFor(User $user): void
    {
        $userId = $user->getAuthIdentifier();
        $today = Carbon::today();

        $activeLeaveKeys = [];
        $activeBirthdayKeys = [];

        // -------------------------------------------------------------
        // 1. PENDING LEAVE REQUESTS SUBMITTED BY EMPLOYEES
        // -------------------------------------------------------------
        if (Schema::hasTable('hrms_leave_requests')) {
            $pendingLeaves = HrmsLeaveRequest::with(['employee', 'leaveType'])
                ->where('status', 'Pending')
                ->whereNull('deleted_at')
                ->orderByDesc('id')
                ->get();

            foreach ($pendingLeaves as $leave) {
                $emp = $leave->employee;
                $empName = $emp ? trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) : 'Employee';
                $leaveType = $leave->leaveType->name ?? 'Leave';

                $startDate = !empty($leave->start_date) ? Carbon::parse($leave->start_date)->format('d-m-Y') : '';
                $endDate = !empty($leave->end_date) ? Carbon::parse($leave->end_date)->format('d-m-Y') : '';

                if ($leave->is_half_day) {
                    $duration = 'Half Day' . (!empty($leave->half_day_type) ? ' (' . ucfirst($leave->half_day_type) . ')' : '');
                } elseif ($startDate === $endDate || empty($endDate)) {
                    $duration = $startDate;
                } else {
                    $duration = $startDate . ' to ' . $endDate;
                }

                $reasonText = !empty($leave->reason) ? 'Reason: ' . $leave->reason : 'No reason provided';
                $message = sprintf('%s submitted a %s request (%s). %s', $empName, $leaveType, $duration, $reasonText);

                $eventKey = 'STAFF_LEAVE:' . $leave->id;
                $activeLeaveKeys[] = $eventKey;

                $existing = AppNotification::query()
                    ->where('user_id', $userId)
                    ->where('event_key', $eventKey)
                    ->first();

                if (!$existing) {
                    AppNotification::query()->create([
                        'user_id' => $userId,
                        'event_key' => $eventKey,
                        'type' => self::TYPE_LEAVE,
                        'title' => 'Leave Request: ' . $empName,
                        'message' => $message,
                        'entity_id' => $leave->id,
                        'entity_type' => 'hrms_leave_request',
                        'created_at' => $leave->created_at ?: now(),
                        'expires_at' => !empty($leave->end_date) ? Carbon::parse($leave->end_date)->toDateString() : null,
                        'url' => url('employeeleaves?search=' . urlencode($emp ? ($emp->first_name ?: $empName) : '')),
                        'is_read' => false,
                        'is_hidden' => false,
                    ]);
                }
            }
        }

        // Clean up leave notifications that are no longer pending or deleted
        AppNotification::query()
            ->where('user_id', $userId)
            ->where('type', self::TYPE_LEAVE)
            ->whereNotIn('event_key', $activeLeaveKeys)
            ->delete();

        // -------------------------------------------------------------
        // 2. UPCOMING STAFF BIRTHDAYS (UNDER 3 DAYS: 0, 1, 2, 3 DAYS)
        // -------------------------------------------------------------
        if (Schema::hasTable('hrms_employees')) {
            $employees = HrmsEmployee::query()
                ->whereNull('deleted_at')
                ->where('employee_status', 'active')
                ->whereNotNull('date_of_birth')
                ->where('date_of_birth', '!=', '')
                ->where('date_of_birth', '!=', '0000-00-00')
                ->get(['id', 'first_name', 'last_name', 'date_of_birth', 'department_id', 'position_id']);

            foreach ($employees as $employee) {
                $rawDob = (string) $employee->date_of_birth;
                if (empty($rawDob) || str_starts_with($rawDob, '0000-00-00') || str_starts_with($rawDob, '-')) {
                    continue;
                }

                try {
                    $dob = Carbon::parse($rawDob);
                    $month = (int) $dob->month;
                    $day = (int) $dob->day;

                    // Handle Feb 29 on non-leap years
                    $safeDayThisYear = ($month === 2 && $day === 29 && !Carbon::create($today->year, 1, 1)->isLeapYear()) ? 28 : $day;
                    $bdayThisYear = Carbon::create($today->year, $month, $safeDayThisYear)->startOfDay();

                    if ($bdayThisYear->gte($today)) {
                        $nextBday = $bdayThisYear;
                    } else {
                        $nextYear = $today->year + 1;
                        $safeDayNextYear = ($month === 2 && $day === 29 && !Carbon::create($nextYear, 1, 1)->isLeapYear()) ? 28 : $day;
                        $nextBday = Carbon::create($nextYear, $month, $safeDayNextYear)->startOfDay();
                    }

                    $daysLeft = (int) $today->diffInDays($nextBday, false);
                } catch (\Throwable $e) {
                    continue;
                }

                // Check if birthday is under 3 days (0, 1, 2, 3 days from today)
                if ($daysLeft >= 0 && $daysLeft <= 3) {
                    $empName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
                    $eventKey = 'STAFF_BIRTHDAY:' . $employee->id . ':' . $nextBday->toDateString();
                    $activeBirthdayKeys[] = $eventKey;

                    if ($daysLeft === 0) {
                        $message = sprintf('🎂 Today is %s\'s birthday! Wish them a very happy birthday.', $empName);
                        $title = '🎉 Birthday Today: ' . $empName;
                    } elseif ($daysLeft === 1) {
                        $message = sprintf('🎂 Tomorrow (%s) is %s\'s birthday!', $nextBday->format('d-m-Y'), $empName);
                        $title = 'Upcoming Birthday: ' . $empName;
                    } else {
                        $message = sprintf('🎂 %s\'s birthday is in %d days on %s.', $empName, $daysLeft, $nextBday->format('d-m-Y'));
                        $title = 'Upcoming Birthday: ' . $empName;
                    }

                    $existingBday = AppNotification::query()
                        ->where('user_id', $userId)
                        ->where('event_key', $eventKey)
                        ->first();

                    if (!$existingBday) {
                        AppNotification::query()->create([
                            'user_id' => $userId,
                            'event_key' => $eventKey,
                            'type' => self::TYPE_BIRTHDAY,
                            'title' => $title,
                            'message' => $message,
                            'entity_id' => $employee->id,
                            'entity_type' => 'hrms_employee',
                            'created_at' => now(),
                            'expires_at' => $nextBday->toDateString(),
                            'url' => url('view-employee/' . $employee->id),
                            'is_read' => false,
                            'is_hidden' => false,
                        ]);
                    } else {
                        // Update title and message in case daysLeft changed, but preserve is_read and is_hidden
                        $existingBday->update([
                            'title' => $title,
                            'message' => $message,
                        ]);
                    }
                }
            }
        }

        // Clean up birthday notifications that are no longer active (e.g. birthday passed or outside 3-day window)
        AppNotification::query()
            ->where('user_id', $userId)
            ->where('type', self::TYPE_BIRTHDAY)
            ->whereNotIn('event_key', $activeBirthdayKeys)
            ->delete();
    }

    /**
     * Get unread, non-hidden staff notifications for the user.
     * Once read or dismissed, items disappear from this list.
     */
    public function forUser(User $user, int $limit = 50)
    {
        $this->syncFor($user);

        return AppNotification::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->whereIn('type', [self::TYPE_LEAVE, self::TYPE_BIRTHDAY])
            ->where('is_read', false)
            ->where('is_hidden', false)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Return total unread count for staff notifications.
     */
    public function unreadCount(User $user): int
    {
        return AppNotification::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->whereIn('type', [self::TYPE_LEAVE, self::TYPE_BIRTHDAY])
            ->where('is_read', false)
            ->where('is_hidden', false)
            ->count();
    }

    /**
     * Mark an item as read (which makes it disappear).
     */
    public function markRead(User $user, int $id): int
    {
        AppNotification::query()
            ->where('id', $id)
            ->where('user_id', $user->getAuthIdentifier())
            ->whereIn('type', [self::TYPE_LEAVE, self::TYPE_BIRTHDAY])
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return $this->unreadCount($user);
    }

    /**
     * Dismiss an item.
     */
    public function dismiss(User $user, int $id): int
    {
        AppNotification::query()
            ->where('id', $id)
            ->where('user_id', $user->getAuthIdentifier())
            ->whereIn('type', [self::TYPE_LEAVE, self::TYPE_BIRTHDAY])
            ->update([
                'is_read' => true,
                'is_hidden' => true,
                'read_at' => now(),
                'hidden_at' => now(),
            ]);

        return $this->unreadCount($user);
    }

    /**
     * Mark all staff notifications as read.
     */
    public function markAllRead(User $user): int
    {
        AppNotification::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->whereIn('type', [self::TYPE_LEAVE, self::TYPE_BIRTHDAY])
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return 0;
    }
}
