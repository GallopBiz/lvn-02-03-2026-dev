<?php

namespace Tests\Feature;

use App\Models\Academic\Attendance;
use App\Models\Academic\AttendanceDetail;
use App\Models\Classes;
use App\Models\HrmsEmployee;
use App\Models\Student_registration;
use App\Models\TeacherSubject;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DailyAttendanceBulkImportTest extends TestCase
{
    protected string $tempDbFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDbFile = tempnam(sys_get_temp_dir(), 'test_att_');

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $this->tempDbFile,
            'database.connections.sqlite.prefix' => '',
            'database.connections.dynamic' => [
                'driver' => 'sqlite',
                'database' => $this->tempDbFile,
                'prefix' => '',
            ],
        ]);
        DB::purge('sqlite');
        DB::purge('dynamic');
        DB::setDefaultConnection('sqlite');

        \Illuminate\Support\Facades\View::share('errors', new \Illuminate\Support\ViewErrorBag());

        Schema::create('classes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('class_name');
            $table->string('section_name')->nullable();
            $table->timestamps();
        });

        Schema::create('student_registration', function (Blueprint $table) {
            $table->increments('id');
            $table->string('scholar_no')->nullable();
            $table->string('student_name');
            $table->string('class_name')->nullable();
            $table->string('class_id')->nullable();
            $table->string('section_name')->nullable();
            $table->string('session_name')->nullable();
            $table->text('json_str')->nullable();
            $table->boolean('is_archived')->nullable()->default(0);
            $table->integer('next_year')->nullable()->default(0);
            $table->timestamps();
        });

        Schema::create('academic_attendance', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('teacher_id')->nullable();
            $table->string('class_id');
            $table->string('class_name')->nullable();
            $table->string('section_name');
            $table->date('attendance_date');
            $table->string('academic_session')->nullable();
            $table->integer('total_students')->default(0);
            $table->integer('present_count')->default(0);
            $table->integer('absent_count')->default(0);
            $table->integer('leave_count')->default(0);
            $table->integer('half_day_count')->default(0);
            $table->integer('pl_count')->default(0);
            $table->boolean('is_locked')->default(0);
            $table->dateTime('locked_at')->nullable();
            $table->unsignedInteger('locked_by')->nullable();
            $table->boolean('is_delete')->default(0);
            $table->timestamps();
        });

        Schema::create('academic_attendance_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('attendance_id');
            $table->unsignedInteger('student_id');
            $table->string('status', 20)->default('present');
            $table->string('remarks')->nullable();
        });

        Schema::create('academic_attendance_monthly_locks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('class_id');
            $table->string('section_name');
            $table->integer('year');
            $table->integer('month');
            $table->boolean('is_locked')->default(1);
            $table->unsignedInteger('locked_by')->nullable();
            $table->dateTime('locked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('class_id');
            $table->string('section_name')->nullable();
            $table->unsignedInteger('teacher_id');
            $table->string('role')->default('Class Teacher');
            $table->integer('is_delete')->default(0);
            $table->timestamps();
        });

        Schema::create('hrms_employees', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('employee_status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hrms_holidays', function (Blueprint $table) {
            $table->increments('id');
            $table->string('HolidayName')->nullable();
            $table->date('HolidayStartDate');
            $table->date('HolidayEndDate')->nullable();
            $table->boolean('for_academic')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('role')->nullable();
            $table->unsignedInteger('employee_id')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->timestamps();
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type']);
        });

        $this->withoutMiddleware([
            \App\Http\Middleware\CheckMenuPermission::class,
            \App\Http\Middleware\EnsureStaffReportAccess::class,
        ]);

        $adminUser = \App\Models\User::create([
            'id' => 999,
            'student_name' => 'Admin User',
            'type' => 'a',
            'role' => 'admin',
        ]);
        $this->actingAs($adminUser);
    }

    protected function tearDown(): void
    {
        DB::disconnect('sqlite');
        DB::disconnect('dynamic');
        if (isset($this->tempDbFile) && file_exists($this->tempDbFile)) {
            @unlink($this->tempDbFile);
        }
        parent::tearDown();
    }

    public function test_import_exact_sample_csv_marks_attendance_and_skips_summary_columns(): void
    {
        // 1. Create Class VI Aryabhatta
        $class = Classes::create([
            'id' => 9,
            'class_name' => '06',
            'section_name' => 'Aryabhatta',
        ]);

        // 2. Create Students from the sample CSV
        $student1 = Student_registration::create([
            'scholar_no' => '6747',
            'student_name' => 'Aaditya Bahrani',
            'class_name' => '06',
            'class_id' => '9',
            'section_name' => 'Aryabhatta',
        ]);

        $student2 = Student_registration::create([
            'scholar_no' => '6748',
            'student_name' => 'Aadya Perlekar',
            'class_name' => '06',
            'class_id' => '9',
            'section_name' => 'Aryabhatta',
        ]);

        $student3 = Student_registration::create([
            'scholar_no' => '6749',
            'student_name' => 'Aarav Singodia',
            'class_name' => '06',
            'class_id' => '9',
            'section_name' => 'Aryabhatta',
        ]);

        // Sample CSV matching user's exact structure
        $csvContent = "Month : April - 2026,Class : VI Aryabhatt,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,\n"
            . "Student,,Days,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,Current Month,,,Previous Month,,,,Total,\n"
            . ",,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,WD    P           %,,,WD            , P,%,WD,P    ,  %\n"
            . "1,Aaditya Bahrani,,P,,P,S,P,P,P,P,P,P,S,P,,P,P,P,P,S,,P,A,P,P,P,S,,,,,,18,17,94.44,0,0,0,18,17,94.44\n"
            . "2,Aadya Perlekar,,P,,P,S,P,P,P,P,P,P,S,P,,P,P,P,P,S,,P,P,P,P,P,S,,,,,,18,18,100,0,0,0,18,18,100\n"
            . "3,Aarav Singodia,,P,,A,S,P,P,P,A,P,P,S,P,,P,P,P,P,S,,P,A,P,P,P,S,,,,,,18,15,83.33,0,0,0,18,15,83.33\n";

        $file = UploadedFile::fake()->createWithContent('attendance_april_2026.csv', $csvContent);

        // Upload as Admin
        $response = $this->post(route('dailyattandence.import'), [
            'attendance_file' => $file,
            'class_id' => $class->id,
            'section_name' => 'Aryabhatta',
            'month_year' => '2026-04',
            'academic_session' => '2026-2027',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify: Day 2 (2026-04-02) should exist and all 3 students should be Present
        $attDay2 = Attendance::whereDate('attendance_date', '2026-04-02')->first();
        $this->assertNotNull($attDay2, 'Day 2 attendance must be created');
        $this->assertEquals(3, $attDay2->present_count);
        $this->assertEquals(0, $attDay2->absent_count);

        $detail1 = AttendanceDetail::where('attendance_id', $attDay2->id)->where('student_id', $student1->id)->first();
        $this->assertEquals('present', $detail1->status);

        // Verify: Day 4 (2026-04-04): Student 1 Present, Student 2 Present, Student 3 Absent
        $attDay4 = Attendance::whereDate('attendance_date', '2026-04-04')->first();
        $this->assertNotNull($attDay4, 'Day 4 attendance must be created');
        $this->assertEquals(2, $attDay4->present_count);
        $this->assertEquals(1, $attDay4->absent_count);

        $detail3 = AttendanceDetail::where('attendance_id', $attDay4->id)->where('student_id', $student3->id)->first();
        $this->assertEquals('absent', $detail3->status);

        // Verify: Day 5 (Sunday 'S') should NOT have an attendance record
        $attDay5 = Attendance::whereDate('attendance_date', '2026-04-05')->first();
        $this->assertNull($attDay5, 'Sundays with S must be skipped');

        // Total attendance working days created must be 18
        $totalDays = Attendance::count();
        $this->assertEquals(18, $totalDays, 'Total working attendance days must be exactly 18');
    }

    public function test_staff_cannot_import_for_unassigned_class(): void
    {
        // Class 1 (Assigned)
        $class1 = Classes::create(['id' => 1, 'class_name' => '05', 'section_name' => 'A']);
        // Class 2 (Unassigned)
        $class2 = Classes::create(['id' => 2, 'class_name' => '06', 'section_name' => 'B']);

        $teacherEmp = HrmsEmployee::create(['id' => 50, 'first_name' => 'Suman', 'last_name' => 'Sharma']);
        TeacherSubject::create([
            'class_id' => $class1->id,
            'section_name' => 'A',
            'teacher_id' => $teacherEmp->id,
            'role' => 'Class Teacher',
            'is_delete' => 0,
        ]);

        $staffUser = \App\Models\Staff::create([
            'id' => 50,
            'name' => 'Suman Sharma',
            'email' => 'suman@example.com',
        ]);
        \Illuminate\Support\Facades\DB::table('users')->where('id', 50)->update(['employee_id' => $teacherEmp->id]);
        $staffUser->employee_id = $teacherEmp->id;
        $this->actingAs($staffUser, 'staff');

        $csvContent = "Month : April - 2026,Class : 06 B\n,,1,2\n1,Test Student,P,P\n";
        $file = UploadedFile::fake()->createWithContent('test.csv', $csvContent);

        // Try importing for unassigned Class 2
        $response = $this->post(route('dailyattandence.import'), [
            'attendance_file' => $file,
            'class_id' => $class2->id,
            'section_name' => 'B',
            'month_year' => '2026-04',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, Attendance::count(), 'No attendance records should be created for unassigned class');
    }

    public function test_staff_assigned_to_multiple_classes_can_import_for_any_assigned_class(): void
    {
        $class1 = Classes::create(['id' => 101, 'class_name' => '05', 'section_name' => 'A']);
        $class2 = Classes::create(['id' => 102, 'class_name' => '06', 'section_name' => 'B']);
        $class3 = Classes::create(['id' => 103, 'class_name' => '07', 'section_name' => 'C']);

        $studentInClass2 = Student_registration::create([
            'scholar_no' => 'SCH-102',
            'student_name' => 'Class Two Student',
            'class_name' => '06',
            'class_id' => '102',
            'section_name' => 'B',
        ]);

        $teacherEmp = HrmsEmployee::create(['id' => 51, 'first_name' => 'Ravi', 'last_name' => 'Gupta']);

        // Assign teacher to BOTH Class 101 and Class 102
        TeacherSubject::create([
            'class_id' => $class1->id,
            'section_name' => 'A',
            'teacher_id' => $teacherEmp->id,
            'role' => 'Class Teacher',
            'is_delete' => 0,
        ]);
        TeacherSubject::create([
            'class_id' => $class2->id,
            'section_name' => 'B',
            'teacher_id' => $teacherEmp->id,
            'role' => 'Class Teacher',
            'is_delete' => 0,
        ]);

        $staffUser = \App\Models\Staff::create([
            'id' => 51,
            'name' => 'Ravi Gupta',
            'email' => 'ravi@example.com',
        ]);
        \Illuminate\Support\Facades\DB::table('users')->where('id', 51)->update(['employee_id' => $teacherEmp->id]);
        $staffUser->employee_id = $teacherEmp->id;
        $this->actingAs($staffUser, 'staff');

        // 1. Staff can download template for their second assigned class (Class 102)
        $dlResponse = $this->get(route('dailyattandence.sample-csv', [
            'class_id' => $class2->id,
            'section_name' => 'B',
            'month' => 4,
            'year' => 2026,
        ]));
        $dlResponse->assertOk();
        $this->assertStringContainsString('Class Two Student', $dlResponse->streamedContent());

        // 2. Staff can import attendance for Class 102 (Class 06 Section B)
        $daysHeader = implode(',', range(1, 31));
        $csvContent = "Month : April - 2026,Class : 06 B\n"
            . "Sr,Scholar No,Student Name,Days\n"
            . ",,,{$daysHeader}\n"
            . "1,SCH-102,Class Two Student,P,P\n";
        $file = UploadedFile::fake()->createWithContent('class2.csv', $csvContent);

        $response = $this->post(route('dailyattandence.import'), [
            'attendance_file' => $file,
            'class_id' => $class2->id,
            'section_name' => 'B',
            'month_year' => '2026-04',
            'academic_session' => '2026-2027',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $att = Attendance::where('class_id', $class2->id)->whereDate('attendance_date', '2026-04-01')->first();
        $this->assertNotNull($att, 'Attendance for assigned Class 2 should be successfully created');

        // 3. Staff cannot import for unassigned Class 103 (Class 07 Section C)
        $unassignedFile = UploadedFile::fake()->createWithContent('class3.csv', "Month : April - 2026,Class : 07 C\n,,,1,2\n1,X,Y,P,P\n");
        $failResponse = $this->post(route('dailyattandence.import'), [
            'attendance_file' => $unassignedFile,
            'class_id' => $class3->id,
            'section_name' => 'C',
            'month_year' => '2026-04',
            'academic_session' => '2026-2027',
        ]);
        $failResponse->assertSessionHas('error');
    }

    public function test_template_download_generates_valid_csv(): void
    {
        $class = Classes::create(['id' => 10, 'class_name' => '07', 'section_name' => 'Kautilya']);
        Student_registration::create([
            'scholar_no' => 'SCH-1001',
            'student_name' => 'Rahul Sharma',
            'class_name' => '07',
            'class_id' => '10',
            'section_name' => 'Kautilya',
        ]);

        $response = $this->get(route('dailyattandence.sample-csv', [
            'class_id' => $class->id,
            'section_name' => 'Kautilya',
            'month' => 4,
            'year' => 2026,
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('Scholar No', $content);
        $this->assertStringContainsString('SCH-1001', $content);
        $this->assertStringContainsString('Rahul Sharma', $content);
        $this->assertStringContainsString('Month : April - 2026', $content);
    }

    public function test_import_with_scholar_no_correctly_maps_students_even_with_identical_names(): void
    {
        $class = Classes::create(['id' => 11, 'class_name' => '08', 'section_name' => 'A']);

        // Two students with the exact same name but different scholar numbers
        $studentA = Student_registration::create([
            'scholar_no' => 'SCH-8001',
            'student_name' => 'Same Name Student',
            'class_name' => '08',
            'class_id' => '11',
            'section_name' => 'A',
        ]);

        $studentB = Student_registration::create([
            'scholar_no' => 'SCH-8002',
            'student_name' => 'Same Name Student',
            'class_name' => '08',
            'class_id' => '11',
            'section_name' => 'A',
        ]);

        // CSV template structure with Scholar No column:
        // Col 0: Sr, Col 1: Scholar No, Col 2: Student Name, Col 3: Day 1, Col 4: Day 2
        $csvContent = "Month : April - 2026,Class : 08 A\n"
            . "Sr,Scholar No,Student Name,Days\n"
            . ",,,1,2\n"
            . "1,SCH-8001,Same Name Student,P,A\n"
            . "2,SCH-8002,Same Name Student,A,P\n";

        $file = UploadedFile::fake()->createWithContent('scholar_attendance.csv', $csvContent);

        $response = $this->post(route('dailyattandence.import'), [
            'attendance_file' => $file,
            'class_id' => $class->id,
            'section_name' => 'A',
            'month_year' => '2026-04',
            'academic_session' => '2026-2027',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check Day 1 (2026-04-01): Student A must be Present, Student B must be Absent
        $attDay1 = Attendance::whereDate('attendance_date', '2026-04-01')->first();
        $this->assertNotNull($attDay1);

        $detailA1 = AttendanceDetail::where('attendance_id', $attDay1->id)->where('student_id', $studentA->id)->first();
        $this->assertEquals('present', $detailA1->status);

        $detailB1 = AttendanceDetail::where('attendance_id', $attDay1->id)->where('student_id', $studentB->id)->first();
        $this->assertEquals('absent', $detailB1->status);

        // Check Day 2 (2026-04-02): Student A must be Absent, Student B must be Present
        $attDay2 = Attendance::whereDate('attendance_date', '2026-04-02')->first();
        $this->assertNotNull($attDay2);

        $detailA2 = AttendanceDetail::where('attendance_id', $attDay2->id)->where('student_id', $studentA->id)->first();
        $this->assertEquals('absent', $detailA2->status);

        $detailB2 = AttendanceDetail::where('attendance_id', $attDay2->id)->where('student_id', $studentB->id)->first();
        $this->assertEquals('present', $detailB2->status);
    }

    public function test_import_ignores_accidental_punches_on_sundays_and_holidays(): void
    {
        $class = Classes::create(['id' => 12, 'class_name' => '09', 'section_name' => 'B']);

        $student = Student_registration::create([
            'scholar_no' => 'SCH-9001',
            'student_name' => 'Test Student',
            'class_name' => '09',
            'class_id' => '12',
            'section_name' => 'B',
        ]);

        // Register a public holiday for 2026-04-14
        \App\Models\Holidays::create([
            'HolidayName' => 'Ambedkar Jayanti',
            'HolidayStartDate' => '2026-04-14',
            'HolidayEndDate' => '2026-04-14',
            'for_academic' => 1,
        ]);

        // Days: Day 1 (Wed, working), Day 5 (Sun, off), Day 14 (Holiday, off)
        $days = range(1, 31);
        $headerDays = implode(',', $days);

        // Teacher accidentally marked 'P' on Day 1 (working), Day 5 (Sunday), and Day 14 (Holiday)
        $punches = array_fill(1, 31, '');
        $punches[1] = 'P';  // Working day
        $punches[5] = 'P';  // Accidentally marked on Sunday!
        $punches[14] = 'P'; // Accidentally marked on Holiday!
        $punchesStr = implode(',', $punches);

        $csvContent = "Month : April - 2026,Class : 09 B\n"
            . "Sr,Scholar No,Student Name,Days\n"
            . ",,,{$headerDays}\n"
            . "1,SCH-9001,Test Student,{$punchesStr}\n";

        $file = UploadedFile::fake()->createWithContent('accidental_punches.csv', $csvContent);

        $response = $this->post(route('dailyattandence.import'), [
            'attendance_file' => $file,
            'class_id' => $class->id,
            'section_name' => 'B',
            'month_year' => '2026-04',
            'academic_session' => '2026-2027',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Day 1 (working day) must be imported
        $attDay1 = Attendance::whereDate('attendance_date', '2026-04-01')->first();
        $this->assertNotNull($attDay1, 'Working day attendance must be created');

        // Day 5 (Sunday) must be ignored despite having 'P'
        $attDay5 = Attendance::whereDate('attendance_date', '2026-04-05')->first();
        $this->assertNull($attDay5, 'Accidentally marked Sunday must be safely ignored');

        // Day 14 (Holiday) must be ignored despite having 'P'
        $attDay14 = Attendance::whereDate('attendance_date', '2026-04-14')->first();
        $this->assertNull($attDay14, 'Accidentally marked Holiday must be safely ignored');

        // Verify session message mentions skipping non-working days
        $successMsg = session('success');
        $this->assertStringContainsString('Skipped 2 non-working day(s)', $successMsg);
    }
}
