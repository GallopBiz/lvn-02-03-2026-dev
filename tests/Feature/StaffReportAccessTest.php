<?php

namespace Tests\Feature;

use App\Http\Controllers\Academic\AttendanceController;
use App\Http\Middleware\EnsureStaffReportAccess;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StaffReportAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.connections.dynamic' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('dynamic');

        Schema::connection('dynamic')->create('teacher_subjects', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('teacher_id');
            $table->string('class_id');
            $table->string('section_name')->nullable();
            $table->unsignedInteger('subject_id')->nullable();
            $table->string('role')->nullable();
            $table->boolean('is_delete')->default(false);
            $table->timestamps();
        });
        Schema::connection('dynamic')->create('classes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('class_name');
            $table->string('section_name')->nullable();
        });
        Schema::connection('dynamic')->create('class_name', function (Blueprint $table) {
            $table->increments('id');
            $table->string('class_name');
        });
        Schema::connection('dynamic')->create('subjectmaster', function (Blueprint $table) {
            $table->increments('id');
            $table->string('subject_name');
        });
        Schema::connection('dynamic')->create('student_registration', function (Blueprint $table) {
            $table->increments('id');
            $table->string('class_name')->nullable();
            $table->string('class_id')->nullable();
            $table->string('section_name')->nullable();
            $table->text('json_str')->nullable();
            $table->string('student_name')->nullable();
            $table->string('form_number')->nullable();
            $table->string('scholar_no')->nullable();
            $table->string('session_name')->nullable();
            $table->boolean('is_archived')->default(false);
        });

        DB::connection('dynamic')->table('classes')->insert([
            'id' => 100,
            'class_name' => '10',
            'section_name' => 'A,B',
        ]);
        DB::connection('dynamic')->table('class_name')->insert([
            'id' => 100,
            'class_name' => '10',
        ]);
        DB::connection('dynamic')->table('subjectmaster')->insert([
            'id' => 21,
            'subject_name' => 'Mathematics',
        ]);

        Route::get('/staff/report-access-test', fn () => response('allowed'))
            ->middleware(EnsureStaffReportAccess::class);
    }

    public function test_staff_can_access_a_report_for_an_assigned_class_section_and_subject(): void
    {
        $this->assignStaffTo('A', 21);
        $this->actAsStaff();

        $this->getJson('/staff/report-access-test?class_id=100&section_name=A&subject_id=21')
            ->assertOk()
            ->assertSeeText('allowed');
    }

    public function test_staff_cannot_access_a_report_for_an_unassigned_class_section_or_subject(): void
    {
        $this->assignStaffTo('A', 21);
        $this->actAsStaff();

        $this->getJson('/staff/report-access-test?class_id=100&section_name=B&subject_id=21')
            ->assertForbidden()
            ->assertJsonPath('error', 'You are not authorized to access reports for this class, section, or subject.');

        $this->getJson('/staff/report-access-test?class_id=100&section_name=A&subject_id=22')
            ->assertForbidden();
    }

    public function test_staff_without_assignments_cannot_access_reports(): void
    {
        $this->actAsStaff();

        $this->getJson('/staff/report-access-test?class_id=100&section_name=A')
            ->assertForbidden();
    }

    public function test_attendance_filter_options_include_assigned_classes_and_sections(): void
    {
        $this->assignStaffTo('A', 21);
        $this->assignStaffTo('B', 21, ' Class Teacher ');
        DB::connection('dynamic')->table('classes')->insert([
            'id' => 101,
            'class_name' => '11',
            'section_name' => 'C',
        ]);
        DB::connection('dynamic')->table('class_name')->insert([
            'id' => 101,
            'class_name' => '11',
        ]);
        $this->assignStaffTo('C', 21, 'Regular Teacher', '101');
        $this->actAsStaff();

        $controller = new AttendanceController();
        $classesMethod = new \ReflectionMethod($controller, 'classes');
        $classesMethod->setAccessible(true);
        $sectionsMethod = new \ReflectionMethod($controller, 'sections');
        $sectionsMethod->setAccessible(true);

        $reportClasses = $classesMethod->invoke($controller);
        $dailyClasses = $classesMethod->invoke($controller);
        $classTeacherClasses = $classesMethod->invoke($controller, true);
        $reportSections = $sectionsMethod->invoke($controller, '100');
        $dailySections = $sectionsMethod->invoke($controller, '100');
        $classTeacherSections = $sectionsMethod->invoke($controller, '100', true);

        $this->assertSame(['10', '11'], $reportClasses->pluck('class_name')->all());
        $this->assertSame(['10', '11'], $dailyClasses->pluck('class_name')->all());
        $this->assertSame(['10'], $classTeacherClasses->pluck('class_name')->all());
        $this->assertSame(['A', 'B'], $reportSections->all());
        $this->assertSame(['A', 'B'], $dailySections->all());
        $this->assertSame(['B'], $classTeacherSections->all());

        $accessMethod = new \ReflectionMethod($controller, 'canStaffAccessClassSection');
        $accessMethod->setAccessible(true);
        $this->assertTrue($accessMethod->invoke($controller, '100', 'A'));
        $this->assertFalse($accessMethod->invoke($controller, '100', 'A', true));
        $this->assertTrue($accessMethod->invoke($controller, '100', 'B', true));
    }

    public function test_admin_daily_attendance_student_fetching_matches_the_selected_class_and_section(): void
    {
        DB::connection('dynamic')->table('classes')->insert([
            [
                'id' => 101,
                'class_name' => '11',
                'section_name' => 'C',
            ],
            [
                'id' => 102,
                'class_name' => '10',
                'section_name' => 'A',
            ],
        ]);
        DB::connection('dynamic')->table('student_registration')->insert([
            [
                'id' => 1,
                'class_name' => '10',
                'class_id' => '100',
                'section_name' => 'A',
                'json_str' => null,
                'student_name' => 'Section A student',
            ],
            [
                'id' => 2,
                'class_name' => '10',
                'class_id' => '100',
                'section_name' => 'B',
                'json_str' => null,
                'student_name' => 'Section B student',
            ],
            [
                'id' => 3,
                'class_name' => '10',
                'class_id' => '100',
                'section_name' => null,
                'json_str' => '{"section_name":"A"}',
                'student_name' => 'Legacy Section A student',
            ],
            [
                'id' => 4,
                'class_name' => '11',
                'class_id' => '101',
                'section_name' => 'A',
                'json_str' => null,
                'student_name' => 'Other class student',
            ],
            [
                'id' => 5,
                'class_name' => '100',
                'class_id' => '101',
                'section_name' => 'A',
                'json_str' => null,
                'student_name' => 'Class name collision student',
            ],
            [
                'id' => 6,
                'class_name' => '10',
                'class_id' => '102',
                'section_name' => 'B',
                'json_str' => null,
                'student_name' => 'Wrong section student',
            ],
            [
                'id' => 7,
                'class_name' => '10',
                'class_id' => '102',
                'section_name' => null,
                'json_str' => null,
                'student_name' => 'Unsectioned student',
            ],
        ]);

        $controller = new AttendanceController();
        $studentsMethod = new \ReflectionMethod($controller, 'studentsForClass');
        $studentsMethod->setAccessible(true);

        $sectionAStudents = $studentsMethod->invoke($controller, '100', 'A', true)->get();
        $allClassStudents = $studentsMethod->invoke($controller, '100', null, true)->get();

        $this->assertSame([3, 1, 7], $sectionAStudents->pluck('id')->all());
        $this->assertSame([3, 1, 2, 7, 6], $allClassStudents->pluck('id')->all());
    }

    public function test_attendance_filter_options_do_not_treat_an_assigned_class_id_as_another_class_name(): void
    {
        DB::connection('dynamic')->table('classes')->insert([
            ['id' => 12, 'class_name' => '09', 'section_name' => 'A'],
            ['id' => 15, 'class_name' => '12', 'section_name' => 'B'],
        ]);
        DB::connection('dynamic')->table('class_name')->insert([
            ['id' => 12, 'class_name' => '09'],
            ['id' => 15, 'class_name' => '12'],
        ]);
        $this->assignStaffTo('A', 21, 'Teacher', '12');
        $this->actAsStaff();

        $controller = new AttendanceController();
        $classesMethod = new \ReflectionMethod($controller, 'classes');
        $classesMethod->setAccessible(true);

        $this->assertSame(['09'], $classesMethod->invoke($controller)->pluck('class_name')->all());
    }

    public function test_daily_attendance_uses_staff_assignments_when_both_guards_are_authenticated(): void
    {
        DB::connection('dynamic')->table('classes')->insert([
            'id' => 101,
            'class_name' => '11',
            'section_name' => 'C',
        ]);
        DB::connection('dynamic')->table('class_name')->insert([
            'id' => 101,
            'class_name' => '11',
        ]);
        $this->assignStaffTo('A', 21);
        DB::connection('dynamic')->table('teacher_subjects')->insert([
            'teacher_id' => 7,
            'class_id' => '101',
            'section_name' => 'C',
            'subject_id' => 21,
            'role' => 'Class Teacher',
            'is_delete' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actAsStaff();
        $this->actingAs(new class extends Authenticatable {}, 'web');

        request()->cookies->set('auth_portal', 'staff');

        $controller = new AttendanceController();
        $classesMethod = new \ReflectionMethod($controller, 'classes');
        $classesMethod->setAccessible(true);
        $dailyClasses = $classesMethod->invoke($controller, true);

        $this->assertSame(['11'], $dailyClasses->pluck('class_name')->all());
    }

    private function assignStaffTo(
        string $section,
        int $subjectId,
        string $role = 'Teacher',
        string $classId = '100'
    ): void
    {
        DB::connection('dynamic')->table('teacher_subjects')->insert([
            'teacher_id' => 7,
            'class_id' => $classId,
            'section_name' => $section,
            'subject_id' => $subjectId,
            'role' => $role,
            'is_delete' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function actAsStaff(): void
    {
        $staff = new class extends Authenticatable
        {
            public $employee_id = 7;
        };

        $this->actingAs($staff, 'staff');
    }
}
