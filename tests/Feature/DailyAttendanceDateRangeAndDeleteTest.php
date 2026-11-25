<?php

namespace Tests\Feature;

use App\Models\Academic\Attendance;
use App\Models\Classes;
use App\Models\Classname;
use App\Models\HrmsEmployee;
use App\Models\Staff;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyAttendanceDateRangeAndDeleteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create test classes
        Classname::updateOrCreate(['id' => 999], ['class_name' => '99', 'is_delete' => 0]);
        Classes::updateOrCreate(['id' => 999], ['class_name' => '99', 'section_name' => 'Aryabhatta', 'is_delete' => 0]);

        // Create teachers
        HrmsEmployee::updateOrCreate(['id' => 201], ['first_name' => 'Teacher', 'last_name' => 'One', 'is_delete' => 0]);
        HrmsEmployee::updateOrCreate(['id' => 202], ['first_name' => 'Teacher', 'last_name' => 'Two', 'is_delete' => 0]);

        TeacherSubject::updateOrCreate(['id' => 901], [
            'teacher_id' => 201,
            'class_id' => 999,
            'section_name' => 'Aryabhatta',
            'role' => 'Class Teacher',
            'is_delete' => 0,
        ]);
    }

    protected function tearDown(): void
    {
        Attendance::whereIn('Teacher_id', [201, 202])->delete();
        TeacherSubject::whereIn('id', [901])->delete();
        Classes::where('id', 999)->delete();
        Classname::where('id', 999)->delete();
        HrmsEmployee::whereIn('id', [201, 202])->delete();

        parent::tearDown();
    }

    public function test_date_range_filter_returns_records_within_range()
    {
        $admin = User::first() ?: User::factory()->create();

        $att1 = Attendance::create([
            'Teacher_id' => 201,
            'class_id' => 999,
            'class_name' => '99',
            'section_name' => 'Aryabhatta',
            'attendance_date' => '2026-11-10',
            'academic_session' => '2026-2027',
            'total_students' => 10,
            'present_count' => 10,
            'absent_count' => 0,
            'leave_count' => 0,
            'is_delete' => 0,
        ]);

        $att2 = Attendance::create([
            'Teacher_id' => 201,
            'class_id' => 999,
            'class_name' => '99',
            'section_name' => 'Aryabhatta',
            'attendance_date' => '2026-11-15',
            'academic_session' => '2026-2027',
            'total_students' => 10,
            'present_count' => 9,
            'absent_count' => 1,
            'leave_count' => 0,
            'is_delete' => 0,
        ]);

        $att3 = Attendance::create([
            'Teacher_id' => 201,
            'class_id' => 999,
            'class_name' => '99',
            'section_name' => 'Aryabhatta',
            'attendance_date' => '2026-11-20',
            'academic_session' => '2026-2027',
            'total_students' => 10,
            'present_count' => 8,
            'absent_count' => 2,
            'leave_count' => 0,
            'is_delete' => 0,
        ]);

        $response = $this->actingAs($admin)->get(route('dailyattandence', [
            'from_date' => '2026-11-12',
            'to_date' => '2026-11-18',
        ]));

        $response->assertStatus(200);
        $recent = $response->viewData('recentAttendance');
        $recentIds = $recent->pluck('id')->toArray();

        $this->assertContains($att2->id, $recentIds);
        $this->assertNotContains($att1->id, $recentIds);
        $this->assertNotContains($att3->id, $recentIds);
    }

    public function test_single_delete_deletes_unlocked_attendance()
    {
        $admin = User::first() ?: User::factory()->create();

        $att = Attendance::create([
            'Teacher_id' => 201,
            'class_id' => 999,
            'class_name' => '99',
            'section_name' => 'Aryabhatta',
            'attendance_date' => '2026-11-10',
            'academic_session' => '2026-2027',
            'total_students' => 10,
            'present_count' => 10,
            'absent_count' => 0,
            'leave_count' => 0,
            'is_locked' => 0,
            'is_delete' => 0,
        ]);

        $response = $this->actingAs($admin)->delete(route('dailyattandence.delete', $att->id));
        $response->assertRedirect(route('dailyattandence'));
        $response->assertSessionHas('success');

        $this->assertEquals(1, $att->fresh()->is_delete);
    }

    public function test_single_delete_fails_when_attendance_is_locked()
    {
        $admin = User::first() ?: User::factory()->create();

        $att = Attendance::create([
            'Teacher_id' => 201,
            'class_id' => 999,
            'class_name' => '99',
            'section_name' => 'Aryabhatta',
            'attendance_date' => '2026-11-10',
            'academic_session' => '2026-2027',
            'total_students' => 10,
            'present_count' => 10,
            'absent_count' => 0,
            'leave_count' => 0,
            'is_locked' => 1,
            'is_delete' => 0,
        ]);

        $response = $this->actingAs($admin)->delete(route('dailyattandence.delete', $att->id));
        $response->assertRedirect(route('dailyattandence'));
        $response->assertSessionHas('error', 'Attendance for this date is locked and cannot be deleted.');

        $this->assertEquals(0, $att->fresh()->is_delete);
    }

    public function test_bulk_delete_deletes_multiple_unlocked_attendances()
    {
        $admin = User::first() ?: User::factory()->create();

        $att1 = Attendance::create([
            'Teacher_id' => 201,
            'class_id' => 999,
            'class_name' => '99',
            'section_name' => 'Aryabhatta',
            'attendance_date' => '2026-11-10',
            'academic_session' => '2026-2027',
            'total_students' => 10,
            'present_count' => 10,
            'absent_count' => 0,
            'leave_count' => 0,
            'is_locked' => 0,
            'is_delete' => 0,
        ]);

        $att2 = Attendance::create([
            'Teacher_id' => 201,
            'class_id' => 999,
            'class_name' => '99',
            'section_name' => 'Aryabhatta',
            'attendance_date' => '2026-11-11',
            'academic_session' => '2026-2027',
            'total_students' => 10,
            'present_count' => 9,
            'absent_count' => 1,
            'leave_count' => 0,
            'is_locked' => 0,
            'is_delete' => 0,
        ]);

        $response = $this->actingAs($admin)->post(route('dailyattandence.bulk-delete'), [
            'attendance_ids' => [$att1->id, $att2->id],
        ]);

        $response->assertRedirect(route('dailyattandence'));
        $response->assertSessionHas('success');

        $this->assertEquals(1, $att1->fresh()->is_delete);
        $this->assertEquals(1, $att2->fresh()->is_delete);
    }

    public function test_bulk_delete_fails_if_any_selected_attendance_is_locked()
    {
        $admin = User::first() ?: User::factory()->create();

        $att1 = Attendance::create([
            'Teacher_id' => 201,
            'class_id' => 999,
            'class_name' => '99',
            'section_name' => 'Aryabhatta',
            'attendance_date' => '2026-11-10',
            'academic_session' => '2026-2027',
            'total_students' => 10,
            'present_count' => 10,
            'absent_count' => 0,
            'leave_count' => 0,
            'is_locked' => 0,
            'is_delete' => 0,
        ]);

        $att2 = Attendance::create([
            'Teacher_id' => 201,
            'class_id' => 999,
            'class_name' => '99',
            'section_name' => 'Aryabhatta',
            'attendance_date' => '2026-11-11',
            'academic_session' => '2026-2027',
            'total_students' => 10,
            'present_count' => 9,
            'absent_count' => 1,
            'leave_count' => 0,
            'is_locked' => 1,
            'is_delete' => 0,
        ]);

        $response = $this->actingAs($admin)->post(route('dailyattandence.bulk-delete'), [
            'attendance_ids' => [$att1->id, $att2->id],
        ]);

        $response->assertRedirect(route('dailyattandence'));
        $response->assertSessionHas('error', 'Attendance is locked for one or more selected records and cannot be deleted.');

        $this->assertEquals(0, $att1->fresh()->is_delete);
        $this->assertEquals(0, $att2->fresh()->is_delete);
    }

    private function createTeacher(int $empId, string $name, string $role = 'Academic')
    {
        return new class($empId, $name, $role) extends \Illuminate\Foundation\Auth\User {
            public $id;
            public $name;
            public $role;
            public $employee_id;

            public function __construct($empId, $name, $role)
            {
                $this->id = 1000 + $empId;
                $this->name = $name;
                $this->role = $role;
                $this->employee_id = $empId;
            }
        };
    }

    public function test_staff_can_delete_own_unlocked_attendance_but_not_locked()
    {
        $staffUser = $this->createTeacher(201, 'Teacher One');

        $att = Attendance::create([
            'Teacher_id' => 201,
            'class_id' => 999,
            'class_name' => '99',
            'section_name' => 'Aryabhatta',
            'attendance_date' => '2026-11-10',
            'academic_session' => '2026-2027',
            'total_students' => 10,
            'present_count' => 10,
            'absent_count' => 0,
            'leave_count' => 0,
            'is_locked' => 0,
            'is_delete' => 0,
        ]);

        $response = $this->actingAs($staffUser, 'staff')->delete(route('dailyattandence.delete', $att->id));
        $response->assertRedirect(route('dailyattandence'));
        $response->assertSessionHas('success');
        $this->assertEquals(1, $att->fresh()->is_delete);

        // Now test locked attendance deletion by staff
        $lockedAtt = Attendance::create([
            'Teacher_id' => 201,
            'class_id' => 999,
            'class_name' => '99',
            'section_name' => 'Aryabhatta',
            'attendance_date' => '2026-11-12',
            'academic_session' => '2026-2027',
            'total_students' => 10,
            'present_count' => 10,
            'absent_count' => 0,
            'leave_count' => 0,
            'is_locked' => 1,
            'is_delete' => 0,
        ]);

        $lockedResponse = $this->actingAs($staffUser, 'staff')->delete(route('dailyattandence.delete', $lockedAtt->id));
        $lockedResponse->assertRedirect(route('dailyattandence'));
        $lockedResponse->assertSessionHas('error', 'Attendance for this date is locked and cannot be deleted.');
        $this->assertEquals(0, $lockedAtt->fresh()->is_delete);
    }
}
