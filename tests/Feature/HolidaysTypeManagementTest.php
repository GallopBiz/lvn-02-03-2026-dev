<?php

namespace Tests\Feature;

use App\Models\Academic\Attendance;
use App\Models\Classes;
use App\Models\Holidays;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HolidaysTypeManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user);
    }

    public function test_holidays_page_renders_with_type_checkboxes()
    {
        $response = $this->get(route('holidays'));
        $response->assertStatus(200);
        $response->assertSee('For HRMS Only');
        $response->assertSee('Academic');
        $response->assertSee('name="for_hrms"', false);
        $response->assertSee('name="for_academic"', false);
        $response->assertSee('Applicable For');
    }

    public function test_creating_holiday_with_neither_type_selected_fails_validation()
    {
        $response = $this->from(route('holidays'))->post(url('save-holidays'), [
            'HolidayName' => 'Test Holiday No Type',
            'HolidayStartDate' => '2026-11-20',
            'HolidayEndDate' => '2026-11-20',
        ]);

        $response->assertRedirect(route('holidays'));
        $response->assertSessionHasErrors('holiday_type');
        $this->assertDatabaseMissing('hrms_holidays', [
            'HolidayName' => 'Test Holiday No Type',
        ]);
    }

    public function test_creating_hrms_only_holiday()
    {
        $startDate = '2026-11-25';
        $endDate = '2026-11-25';

        $response = $this->post(url('save-holidays'), [
            'HolidayName' => 'HRMS Only Special Holiday',
            'HolidayStartDate' => $startDate,
            'HolidayEndDate' => $endDate,
            'HolidayDescription' => 'Testing HRMS only holiday',
            'for_hrms' => '1',
        ]);

        $response->assertRedirect(route('holidays'));

        $holiday = Holidays::where('HolidayName', 'HRMS Only Special Holiday')->first();
        $this->assertNotNull($holiday);
        $this->assertEquals(1, $holiday->for_hrms);
        $this->assertEquals(0, $holiday->for_academic);

        // Check HRMS scope: must include
        $hrmsCount = Holidays::forHrms()->where('HolidayID', $holiday->HolidayID)->count();
        $this->assertEquals(1, $hrmsCount);

        // Check Academic scope: must NOT include
        $academicCount = Holidays::forAcademic()->where('HolidayID', $holiday->HolidayID)->count();
        $this->assertEquals(0, $academicCount);

        // Academic nonWorkingDayInfo should return null for this date (teachers CAN mark attendance)
        $academicController = app(\App\Http\Controllers\Academic\AttendanceController::class);
        $refMethod = new \ReflectionMethod($academicController, 'nonWorkingDayInfo');
        $refMethod->setAccessible(true);
        $nonWorking = $refMethod->invoke($academicController, $startDate);
        $this->assertNull($nonWorking, 'HRMS Only holiday must NOT block academic attendance.');
    }

    public function test_creating_academic_holiday()
    {
        $startDate = '2026-11-26';
        $endDate = '2026-11-26';

        $response = $this->post(url('save-holidays'), [
            'HolidayName' => 'Academic Annual Holiday',
            'HolidayStartDate' => $startDate,
            'HolidayEndDate' => $endDate,
            'HolidayDescription' => 'Testing Academic holiday',
            'for_academic' => '1',
        ]);

        $response->assertRedirect(route('holidays'));

        $holiday = Holidays::where('HolidayName', 'Academic Annual Holiday')->first();
        $this->assertNotNull($holiday);
        $this->assertEquals(0, $holiday->for_hrms);
        $this->assertEquals(1, $holiday->for_academic);

        // Check HRMS scope: must include (since Academic also applies HRMS rules)
        $hrmsCount = Holidays::forHrms()->where('HolidayID', $holiday->HolidayID)->count();
        $this->assertEquals(1, $hrmsCount);

        // Check Academic scope: must include
        $academicCount = Holidays::forAcademic()->where('HolidayID', $holiday->HolidayID)->count();
        $this->assertEquals(1, $academicCount);

        // Academic nonWorkingDayInfo should return the holiday (teachers CANNOT mark attendance)
        $academicController = app(\App\Http\Controllers\Academic\AttendanceController::class);
        $refMethod = new \ReflectionMethod($academicController, 'nonWorkingDayInfo');
        $refMethod->setAccessible(true);
        $nonWorking = $refMethod->invoke($academicController, $startDate);
        $this->assertNotNull($nonWorking, 'Academic holiday must return non-working day info.');
        $this->assertEquals('holiday', $nonWorking['type']);
        $this->assertStringContainsString('Academic Annual Holiday', $nonWorking['label']);
    }

    public function test_creating_both_hrms_and_academic_holiday()
    {
        $startDate = '2026-11-27';
        $endDate = '2026-11-27';

        $response = $this->post(url('save-holidays'), [
            'HolidayName' => 'Dual Module Holiday',
            'HolidayStartDate' => $startDate,
            'HolidayEndDate' => $endDate,
            'HolidayDescription' => 'Testing both selected',
            'for_hrms' => '1',
            'for_academic' => '1',
        ]);

        $response->assertRedirect(route('holidays'));

        $holiday = Holidays::where('HolidayName', 'Dual Module Holiday')->first();
        $this->assertNotNull($holiday);
        $this->assertEquals(1, $holiday->for_hrms);
        $this->assertEquals(1, $holiday->for_academic);

        $this->assertEquals(1, Holidays::forHrms()->where('HolidayID', $holiday->HolidayID)->count());
        $this->assertEquals(1, Holidays::forAcademic()->where('HolidayID', $holiday->HolidayID)->count());
    }

    public function test_updating_existing_holiday_types()
    {
        $holiday = Holidays::create([
            'HolidayName' => 'Update Test Holiday',
            'HolidayStartDate' => '2026-11-28',
            'HolidayEndDate' => '2026-11-28',
            'HolidayDescription' => 'Initial',
            'for_hrms' => 1,
            'for_academic' => 1,
        ]);

        // Edit mode view should render with both checked
        $viewResponse = $this->get(url('view-holidays/' . $holiday->HolidayID));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('value="Update Test Holiday"', false);

        // Update to HRMS only
        $updateResponse = $this->post(url('store-holidays'), [
            'id' => $holiday->HolidayID,
            'HolidayName' => 'Updated to HRMS Only Holiday',
            'HolidayStartDate' => '2026-11-28',
            'HolidayEndDate' => '2026-11-28',
            'HolidayDescription' => 'Updated description',
            'for_hrms' => '1',
            // for_academic omitted (unchecked)
        ]);

        $updateResponse->assertRedirect(route('holidays'));

        $holiday->refresh();
        $this->assertEquals(1, $holiday->for_hrms);
        $this->assertEquals(0, $holiday->for_academic);
        $this->assertEquals('Updated to HRMS Only Holiday', $holiday->HolidayName);
    }

    public function test_academic_attendance_is_blocked_on_academic_holiday_but_allowed_on_hrms_only()
    {
        $hrmsOnlyDate = '2026-12-02';
        $academicHolidayDate = '2026-12-03';

        Holidays::create([
            'HolidayName' => 'HRMS Only Day',
            'HolidayStartDate' => $hrmsOnlyDate,
            'HolidayEndDate' => $hrmsOnlyDate,
            'for_hrms' => 1,
            'for_academic' => 0,
        ]);

        Holidays::create([
            'HolidayName' => 'Academic Holiday Day',
            'HolidayStartDate' => $academicHolidayDate,
            'HolidayEndDate' => $academicHolidayDate,
            'for_hrms' => 1,
            'for_academic' => 1,
        ]);

        $academicController = app(\App\Http\Controllers\Academic\AttendanceController::class);
        $refMethod = new \ReflectionMethod($academicController, 'nonWorkingDayInfo');
        $refMethod->setAccessible(true);

        // HRMS only date is NOT considered non-working in Academic
        $this->assertNull($refMethod->invoke($academicController, $hrmsOnlyDate));

        // Academic holiday date IS considered non-working in Academic
        $academicNonWorking = $refMethod->invoke($academicController, $academicHolidayDate);
        $this->assertNotNull($academicNonWorking);
        $this->assertEquals('holiday', $academicNonWorking['type']);

        // Testing applyWorkingDayOnlyFilter
        $refFilter = new \ReflectionMethod($academicController, 'applyWorkingDayOnlyFilter');
        $refFilter->setAccessible(true);

        $query = Attendance::query();
        $refFilter->invoke($academicController, $query);
        $sql = $query->toSql();

        $this->assertStringContainsString('for_academic', $sql);
    }
}
