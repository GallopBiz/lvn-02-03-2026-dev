<?php

namespace Tests\Feature;

use App\Http\Controllers\Academic\AttendanceController;
use App\Models\Academic\Attendance;
use App\Models\Classes;
use App\Models\Student_registration;
use App\Models\TeacherSubject;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class TeacherDailyAttendanceIsolationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
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

    public function test_teacher_only_sees_own_attendance_records()
    {
        $teacherA = $this->createTeacher(101, 'Teacher One', 'Academic');
        $teacherB = $this->createTeacher(102, 'Teacher Two', 'Academic');

        TeacherSubject::whereIn('teacher_id', [101, 102])->delete();
        TeacherSubject::create([
            'teacher_id' => 101,
            'class_id' => '4',
            'section_name' => 'Kautilya',
            'role' => 'Class Teacher',
            'is_delete' => 0,
        ]);
        TeacherSubject::create([
            'teacher_id' => 102,
            'class_id' => '10',
            'section_name' => 'Kautilya',
            'role' => 'Class Teacher',
            'is_delete' => 0,
        ]);

        Attendance::whereIn('Teacher_id', [101, 102])->delete();

        $attA = Attendance::create([
            'Teacher_id' => 101,
            'class_id' => '4',
            'section_name' => 'Kautilya',
            'attendance_date' => '2026-11-17',
            'academic_session' => '2025-2026',
            'total_students' => 10,
            'present_count' => 10,
            'absent_count' => 0,
            'leave_count' => 0,
            'is_delete' => 0,
        ]);

        $attB = Attendance::create([
            'Teacher_id' => 102,
            'class_id' => '10',
            'section_name' => 'Kautilya',
            'attendance_date' => '2026-11-17',
            'academic_session' => '2025-2026',
            'total_students' => 10,
            'present_count' => 10,
            'absent_count' => 0,
            'leave_count' => 0,
            'is_delete' => 0,
        ]);

        // When Teacher A is logged in as staff
        $this->actingAs($teacherA, 'staff');
        session(['auth_portal' => 'staff']);
        $controller = app(AttendanceController::class);
        $method = new \ReflectionMethod(AttendanceController::class, 'accessibleAttendanceQuery');
        $method->setAccessible(true);

        /** @var \Illuminate\Database\Eloquent\Builder $queryA */
        $queryA = $method->invoke($controller, false);
        $recordsA = $queryA->pluck('id')->all();

        $this->assertContains($attA->id, $recordsA, 'Teacher A should see own attendance record.');
        $this->assertNotContains($attB->id, $recordsA, 'Teacher A must NOT see Teacher B attendance record.');

        // When Teacher B is logged in as staff
        $this->actingAs($teacherB, 'staff');
        /** @var \Illuminate\Database\Eloquent\Builder $queryB */
        $queryB = $method->invoke($controller, false);
        $recordsB = $queryB->pluck('id')->all();

        $this->assertContains($attB->id, $recordsB, 'Teacher B should see own attendance record.');
        $this->assertNotContains($attA->id, $recordsB, 'Teacher B must NOT see Teacher A attendance record.');
    }

    public function test_duplicate_attendance_check_is_teacher_and_date_isolated()
    {
        $teacherA = $this->createTeacher(101, 'Teacher One', 'Academic');
        $teacherB = $this->createTeacher(102, 'Teacher Two', 'Academic');

        TeacherSubject::whereIn('teacher_id', [101, 102])->delete();
        TeacherSubject::create([
            'teacher_id' => 101,
            'class_id' => '4',
            'section_name' => 'Kautilya',
            'role' => 'Class Teacher',
            'is_delete' => 0,
        ]);
        TeacherSubject::create([
            'teacher_id' => 102,
            'class_id' => '10',
            'section_name' => 'Kautilya',
            'role' => 'Class Teacher',
            'is_delete' => 0,
        ]);

        Attendance::whereIn('Teacher_id', [101, 102])->delete();

        Attendance::create([
            'Teacher_id' => 101,
            'class_id' => '4',
            'section_name' => 'Kautilya',
            'attendance_date' => '2026-11-17',
            'academic_session' => '2025-2026',
            'total_students' => 10,
            'present_count' => 10,
            'absent_count' => 0,
            'leave_count' => 0,
            'is_delete' => 0,
        ]);

        $controller = app(AttendanceController::class);

        // Teacher A on the marked date (2026-11-17): duplicate alert should trigger
        $this->actingAs($teacherA, 'staff');
        session(['auth_portal' => 'staff']);
        $reqMarkedDate = Request::create(route('dailyattandence.check-marked'), 'GET', [
            'date' => '2026-11-17',
            'class_id' => '4',
            'section_name' => 'Kautilya',
        ]);
        $resMarkedDate = $controller->checkMarked($reqMarkedDate)->getData(true);
        $this->assertTrue($resMarkedDate['already_marked'], 'Teacher A should see already_marked = true on the date they submitted.');

        // Teacher A on a different date (2026-11-18): duplicate alert should NOT trigger
        $reqOtherDate = Request::create(route('dailyattandence.check-marked'), 'GET', [
            'date' => '2026-11-18',
            'class_id' => '4',
            'section_name' => 'Kautilya',
        ]);
        $resOtherDate = $controller->checkMarked($reqOtherDate)->getData(true);
        $this->assertFalse($resOtherDate['already_marked'], 'Teacher A should NOT see already_marked on a date they have not submitted.');

        // Teacher B on the same date (2026-11-17): duplicate alert should NOT trigger for Teacher B
        $this->actingAs($teacherB, 'staff');
        session(['auth_portal' => 'staff']);
        $reqTeacherB = Request::create(route('dailyattandence.check-marked'), 'GET', [
            'date' => '2026-11-17',
            'class_id' => '10',
            'section_name' => 'Kautilya',
        ]);
        $resTeacherB = $controller->checkMarked($reqTeacherB)->getData(true);
        $this->assertFalse($resTeacherB['already_marked'], 'Teacher B must NOT see duplicate alert for Teacher A submission.');
    }

    public function test_teacher_cannot_submit_duplicate_attendance_for_same_date()
    {
        $this->withoutMiddleware([\App\Http\Middleware\CheckMenuPermission::class]);

        $teacherA = $this->createTeacher(101, 'Teacher One', 'Academic');

        TeacherSubject::where('teacher_id', 101)->delete();
        TeacherSubject::create([
            'teacher_id' => 101,
            'class_id' => '4',
            'section_name' => 'Kautilya',
            'role' => 'Class Teacher',
            'is_delete' => 0,
        ]);

        $student = new Student_registration();
        $student->class_id = '4';
        $student->class_name = '01';
        $student->section_name = 'Kautilya';
        $student->student_name = 'Isolation Test Student';
        $student->form_number = 'ISOLATION_TEST_001';
        $student->registration_date = '2026-04-01';
        $student->save();

        Attendance::where('Teacher_id', 101)->delete();

        Attendance::create([
            'Teacher_id' => 101,
            'class_id' => '4',
            'section_name' => 'Kautilya',
            'attendance_date' => '2026-11-17',
            'academic_session' => '2025-2026',
            'total_students' => 1,
            'present_count' => 1,
            'absent_count' => 0,
            'leave_count' => 0,
            'is_delete' => 0,
        ]);

        $this->actingAs($teacherA, 'staff');
        session(['auth_portal' => 'staff']);

        // Attempting to submit attendance again for 2026-11-17
        $response = $this->from(route('dailyattandence'))->post(route('dailyattandence'), [
            'attendance_date' => '2026-11-17',
            'class_id' => '4',
            'section_name' => 'Kautilya',
            'academic_session' => '2025-2026',
            'attendance' => [
                (string) $student->id => 'present',
            ],
        ]);

        if ($response->exception) {
            throw $response->exception;
        }

        $response->assertSessionHas('error', 'Attendance has already been submitted for this date.');
    }

    public function test_other_teachers_can_mark_attendance_independently_on_same_date()
    {
        $this->withoutMiddleware([\App\Http\Middleware\CheckMenuPermission::class]);

        $teacherA = $this->createTeacher(101, 'Teacher One', 'Academic');
        $teacherB = $this->createTeacher(102, 'Teacher Two', 'Academic');

        $classBId = (string) (Classes::where('class_name', '07')->where('section_name', 'like', '%Kautilya%')->value('id') ?? 33);

        TeacherSubject::whereIn('teacher_id', [101, 102])->delete();
        TeacherSubject::create([
            'teacher_id' => 101,
            'class_id' => '4',
            'section_name' => 'Kautilya',
            'role' => 'Class Teacher',
            'is_delete' => 0,
        ]);
        TeacherSubject::create([
            'teacher_id' => 102,
            'class_id' => $classBId,
            'section_name' => 'Kautilya',
            'role' => 'Class Teacher',
            'is_delete' => 0,
        ]);

        Attendance::whereIn('Teacher_id', [101, 102])->delete();

        // Teacher A has already marked attendance for 2026-11-17
        Attendance::create([
            'Teacher_id' => 101,
            'class_id' => '4',
            'section_name' => 'Kautilya',
            'attendance_date' => '2026-11-17',
            'academic_session' => '2025-2026',
            'total_students' => 1,
            'present_count' => 1,
            'absent_count' => 0,
            'leave_count' => 0,
            'is_delete' => 0,
        ]);

        // Student for Teacher B's class
        $studentB = new Student_registration();
        $studentB->class_id = $classBId;
        $studentB->class_name = '07';
        $studentB->section_name = 'Kautilya';
        $studentB->student_name = 'Teacher B Student';
        $studentB->form_number = 'TB_STUDENT_001';
        $studentB->registration_date = '2026-04-01';
        $studentB->save();

        // Teacher B logs in and marks attendance on the SAME date (2026-11-17)
        $this->actingAs($teacherB, 'staff');
        session(['auth_portal' => 'staff']);

        $response = $this->from(route('dailyattandence'))->post(route('dailyattandence'), [
            'attendance_date' => '2026-11-17',
            'class_id' => $classBId,
            'section_name' => 'Kautilya',
            'academic_session' => '2025-2026',
            'attendance' => [
                (string) $studentB->id => 'present',
            ],
        ]);

        $response->assertSessionHas('success', 'Attendance saved successfully.');

        // Verify attendance record exists in DB for Teacher B
        $this->assertDatabaseHas('academic_attendance', [
            'Teacher_id' => 102,
            'class_id' => $classBId,
            'section_name' => 'Kautilya',
            'attendance_date' => '2026-11-17',
            'is_delete' => 0,
        ]);
    }

    public function test_admin_can_view_all_teachers_attendance_records()
    {
        $teacherA = $this->createTeacher(101, 'Teacher One', 'Academic');
        $teacherB = $this->createTeacher(102, 'Teacher Two', 'Academic');

        Attendance::whereIn('Teacher_id', [101, 102])->delete();

        $attA = Attendance::create([
            'Teacher_id' => 101,
            'class_id' => '4',
            'section_name' => 'Kautilya',
            'attendance_date' => '2026-11-17',
            'academic_session' => '2025-2026',
            'total_students' => 10,
            'present_count' => 10,
            'absent_count' => 0,
            'leave_count' => 0,
            'is_delete' => 0,
        ]);

        $attB = Attendance::create([
            'Teacher_id' => 102,
            'class_id' => '10',
            'section_name' => 'Kautilya',
            'attendance_date' => '2026-11-17',
            'academic_session' => '2025-2026',
            'total_students' => 10,
            'present_count' => 10,
            'absent_count' => 0,
            'leave_count' => 0,
            'is_delete' => 0,
        ]);

        // Admin login
        $admin = new class extends \Illuminate\Foundation\Auth\User {
            public $id = 9999;
            public $name = 'Admin User';
            public $role = 'Admin';
            public $type = 'a';
        };

        $this->actingAs($admin, 'web');

        $controller = app(AttendanceController::class);
        $method = new \ReflectionMethod(AttendanceController::class, 'accessibleAttendanceQuery');
        $method->setAccessible(true);

        /** @var \Illuminate\Database\Eloquent\Builder $queryAdmin */
        $queryAdmin = $method->invoke($controller, false);
        $recordsAdmin = $queryAdmin->pluck('id')->all();

        $this->assertContains($attA->id, $recordsAdmin, 'Admin should be able to view Teacher A attendance.');
        $this->assertContains($attB->id, $recordsAdmin, 'Admin should be able to view Teacher B attendance.');
    }
}
