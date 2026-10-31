<?php

namespace Tests\Unit;

use App\Services\Hrms\CasualLeaveBalanceService;
use PHPUnit\Framework\TestCase;

class CasualLeaveBalanceServiceTest extends TestCase
{
    public function test_half_day_shortfall_is_split_between_cl_and_lwp(): void
    {
        $this->assertSame(
            ['cl_days' => 0.2, 'lwp_days' => 0.3],
            CasualLeaveBalanceService::allocate(0.2, 0.5)
        );
    }

    public function test_available_cl_cannot_become_negative(): void
    {
        $this->assertSame(
            ['cl_days' => 0.0, 'lwp_days' => 0.5],
            CasualLeaveBalanceService::allocate(-0.2, 0.5)
        );
    }

    public function test_fully_covered_leave_has_no_lwp(): void
    {
        $this->assertSame(
            ['cl_days' => 0.5, 'lwp_days' => 0.0],
            CasualLeaveBalanceService::allocate(1.0, 0.5)
        );
    }

    public function test_full_day_absence_uses_remaining_cl_and_sends_the_rest_to_lwp(): void
    {
        $this->assertSame(
            ['cl_days' => 0.5, 'lwp_days' => 0.5],
            CasualLeaveBalanceService::allocate(0.5, 1.0)
        );
    }
}
