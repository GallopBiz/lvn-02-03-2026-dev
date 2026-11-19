<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student_registration;
use App\Models\NextYearStudent;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NextYearStudentAdmissionAndPromotionTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['mysql', 'dynamic', 'next_session_db'];
    protected array $createdScholarNos = [];
    protected array $createdInquiryIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.connections.dynamic.database', '2025_2026');
        DB::purge('dynamic');

        Config::set('database.connections.next_session_db.database', '2026_2027');
        DB::purge('next_session_db');

        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user, 'web');
    }

    protected function tearDown(): void
    {
        if (!empty($this->createdScholarNos)) {
            DB::connection('dynamic')->table('student_registration')
                ->whereIn('scholar_no', $this->createdScholarNos)
                ->delete();
            DB::connection('next_session_db')->table('student_registration')
                ->whereIn('scholar_no', $this->createdScholarNos)
                ->delete();
        }

        if (!empty($this->createdInquiryIds)) {
            DB::connection('dynamic')->table('inquiry_registration')
                ->whereIn('id', $this->createdInquiryIds)
                ->delete();
        }

        parent::tearDown();
    }

    /**
     * Test Task 2: Next Year student registration saves next_year columns correctly.
     */
    public function test_student_registration_saves_next_year_columns_correctly()
    {
        $scholarNo = 'SCH_NY_' . rand(10000, 99999);
        $this->createdScholarNos[] = $scholarNo;

        $response = $this->post('/save-student-registration', [
            'scholar_no' => $scholarNo,
            'studentname' => 'Next Year Admission Test Student',
            'classname' => '04',
            'application_for' => 'CBSE',
            'form_number' => 'FN_' . rand(1000, 9999),
            'student_dob' => '2016-05-15',
            'next_year' => 1,
            'mobile_number' => '9876543210',
            'session_name' => '2025_2026',
        ]);

        $this->assertTrue(in_array($response->status(), [200, 302], true));

        $savedStudent = DB::connection('dynamic')->table('student_registration')
            ->where('scholar_no', $scholarNo)
            ->first();

        $this->assertNotNull($savedStudent, 'Student record should be saved in dynamic database');
        $this->assertEquals(1, (int)$savedStudent->next_year, 'next_year column must be 1');
        $this->assertEquals('D', $savedStudent->next_year_section, 'next_year_section must default to D for class 04');
        $this->assertNull($savedStudent->next_year_stream, 'next_year_stream should be null for class 04');
        $this->assertNull($savedStudent->next_year_subject_combination, 'next_year_subject_combination should be null for class 04');

        $json = json_decode($savedStudent->json_str, true);
        $this->assertEquals(1, $json['next_year']);
        $this->assertEquals('2026_2027', $json['admission_session']);
    }

    /**
     * Test Task 2: Senior class Next Year registration saves stream and combination.
     */
    public function test_senior_class_next_year_registration_saves_stream_and_combination()
    {
        $scholarNo = 'SCH_SR_' . rand(10000, 99999);
        $this->createdScholarNos[] = $scholarNo;

        $response = $this->post('/save-student-registration', [
            'scholar_no' => $scholarNo,
            'studentname' => 'Senior Next Year Student',
            'classname' => '11',
            'stream_id' => 3, // Science
            'combination_id' => 'PCM+CS+MARK',
            'application_for' => 'CBSE',
            'form_number' => 'FN_SR_' . rand(1000, 9999),
            'student_dob' => '2009-08-20',
            'next_year' => 1,
            'mobile_number' => '9123456780',
            'session_name' => '2025_2026',
        ]);

        $this->assertTrue(in_array($response->status(), [200, 302], true));

        $savedStudent = DB::connection('dynamic')->table('student_registration')
            ->where('scholar_no', $scholarNo)
            ->first();

        $this->assertNotNull($savedStudent);
        $this->assertEquals(1, (int)$savedStudent->next_year);
        $this->assertEquals('Kautilya', $savedStudent->next_year_section, 'Science stream must map to Kautilya section');
        $this->assertEquals('3', (string)$savedStudent->next_year_stream);
        $this->assertEquals('PCM+CS+MARK', $savedStudent->next_year_subject_combination);
    }

    /**
     * Test Task 3: Student registration listings isolate current vs next year students.
     */
    public function test_student_registration_tabs_isolate_current_vs_next_year()
    {
        $currentScholar = 'SCH_CUR_' . rand(10000, 99999);
        $nextYearScholar = 'SCH_NY2_' . rand(10000, 99999);
        $this->createdScholarNos[] = $currentScholar;
        $this->createdScholarNos[] = $nextYearScholar;

        // Current student (next_year = 0)
        DB::connection('dynamic')->table('student_registration')->insert([
            'scholar_no' => $currentScholar,
            'student_name' => 'Current Active Student',
            'class_name' => '05',
            'section_name' => 'Aryabhatta',
            'session_name' => '2025_2026',
            'status' => 'r',
            'is_archived' => 0,
            'next_year' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Next Year student (next_year = 1)
        DB::connection('dynamic')->table('student_registration')->insert([
            'scholar_no' => $nextYearScholar,
            'student_name' => 'Next Year Future Student',
            'class_name' => '05',
            'section_name' => 'D',
            'session_name' => '2025_2026',
            'status' => 'r',
            'is_archived' => 0,
            'next_year' => 1,
            'next_year_section' => 'D',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Current tab
        $responseCurrent = $this->withSession(['selectedYear' => '2025_2026', 'db_names' => '2025_2026'])
            ->get('/student-registrations?tab=current&session_name=2025_2026');
        $responseCurrent->assertStatus(200);
        $currentInquiryStudents = collect($responseCurrent->viewData('all_inquiry'))->pluck('student_name')->toArray();
        $this->assertContains('Current Active Student', $currentInquiryStudents);
        $this->assertNotContains('Next Year Future Student', $currentInquiryStudents);
        $responseCurrent->assertSee('Current Active Student');
        $responseCurrent->assertDontSee('Next Year Future Student');

        // Next Year tab
        $responseNextYear = $this->withSession(['selectedYear' => '2025_2026', 'db_names' => '2025_2026'])
            ->get('/student-registrations?tab=next_year&session_name=2025_2026');
        $responseNextYear->assertStatus(200);
        $nextYearInquiryStudents = collect($responseNextYear->viewData('all_inquiry'))->pluck('student_name')->toArray();
        $this->assertContains('Next Year Future Student', $nextYearInquiryStudents);
        $this->assertNotContains('Current Active Student', $nextYearInquiryStudents);
        $responseNextYear->assertSee('Next Year Future Student');
        $responseNextYear->assertSee('Next Year Admissions');
    }

    /**
     * Test Task 4: Exclude Next Year students from current session operations.
     */
    public function test_next_year_students_are_excluded_from_current_operations()
    {
        $nyScholar = 'SCH_EXC_' . rand(10000, 99999);
        $this->createdScholarNos[] = $nyScholar;

        DB::connection('dynamic')->table('student_registration')->insert([
            'scholar_no' => $nyScholar,
            'student_name' => 'Operation Excluded Student',
            'class_name' => '06',
            'section_name' => 'D',
            'session_name' => '2025_2026',
            'status' => 'r',
            'is_archived' => 0,
            'next_year' => 1,
            'next_year_section' => 'D',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Eloquent model query (Attendance, Reports, etc.)
        $eloquentStudents = Student_registration::where('scholar_no', $nyScholar)->first();
        $this->assertNull($eloquentStudents, 'Eloquent global scope must exclude next_year = 1 students');

        // 2. Section strength calculation
        $strengthResponse = $this->withSession(['selectedYear' => '2025_2026', 'db_names' => '2025_2026'])
            ->withCookie('selectedYear', '2025_2026')
            ->getJson('/class-section-strength?class_name=06');
        $strengthResponse->assertStatus(200);
        $strengthData = $strengthResponse->json();
        $this->assertEquals(0, $strengthData['strength']['D'] ?? 0, 'Class section strength must exclude next_year = 1 students');
    }

    /**
     * Test Task 5: Session Transition Logic.
     * Category A (next_year = 0) gets class-promoted.
     * Category B (next_year = 1) is TRANSFERRED to new session in the same class (NO CLASS PROMOTION).
     */
    public function test_session_transition_promotes_regular_and_transfers_next_year_without_class_promotion()
    {
        $regScholar = 'SCH_PROM_' . rand(10000, 99999);
        $nyScholar = 'SCH_TRAN_' . rand(10000, 99999);
        $this->createdScholarNos[] = $regScholar;
        $this->createdScholarNos[] = $nyScholar;

        // Category A: Regular student currently in Class 04, Section Aryabhatta
        $regId = DB::connection('dynamic')->table('student_registration')->insertGetId([
            'scholar_no' => $regScholar,
            'student_name' => 'Category A Regular Student',
            'class_name' => '04',
            'section_name' => 'Aryabhatta',
            'session_name' => '2025_2026',
            'status' => 'r',
            'is_archived' => 0,
            'next_year' => 0,
            'json_str' => json_encode(['section_name' => 'Aryabhatta', 'classname' => '04']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Category B: Next Year student registered for Class 05 (intended upcoming class) in Section D
        $nyId = DB::connection('dynamic')->table('student_registration')->insertGetId([
            'scholar_no' => $nyScholar,
            'student_name' => 'Category B Next Year Student',
            'class_name' => '05',
            'section_name' => 'D',
            'session_name' => '2025_2026',
            'status' => 'r',
            'is_archived' => 0,
            'next_year' => 1,
            'next_year_section' => 'D',
            'json_str' => json_encode(['section_name' => 'D', 'classname' => '05', 'next_year' => 1]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Trigger promote in StudentTransferController using route('student.promote')
        $response = $this->withSession(['selectedYear' => '2025_2026', 'db_names' => '2025_2026'])
            ->post(route('student.promote'), [
                'year' => '2025_2026',
                'selected_students' => [$regId, $nyId],
            ]);

        if (!in_array($response->status(), [200, 302], true)) {
            fwrite(STDERR, "\nPromote error status " . $response->status() . ": " . substr($response->getContent(), 0, 500) . "\n");
        }

        $this->assertTrue(in_array($response->status(), [200, 302], true));

        // Check target session database (2026_2027)
        $promotedRegStudent = DB::connection('next_session_db')->table('student_registration')
            ->where('scholar_no', $regScholar)
            ->first();

        $transferredNyStudent = DB::connection('next_session_db')->table('student_registration')
            ->where('scholar_no', $nyScholar)
            ->first();

        $this->assertNotNull($promotedRegStudent, 'Regular student must be present in next session DB');
        $this->assertNotNull($transferredNyStudent, 'Next Year student must be present in next session DB');

        // Verify Category A was promoted from 04 -> 05
        $this->assertEquals('05', $promotedRegStudent->class_name, 'Category A student must be promoted from Class 04 to Class 05');
        $this->assertEquals('2026_2027', $promotedRegStudent->session_name);
        $this->assertEquals(0, (int)$promotedRegStudent->next_year);

        // Verify Category B was NOT promoted (remains in Class 05)
        $this->assertEquals('05', $transferredNyStudent->class_name, 'Category B Next Year student must NOT be class-promoted; class remains 05');
        $this->assertEquals('2026_2027', $transferredNyStudent->session_name);
        $this->assertEquals(0, (int)$transferredNyStudent->next_year, 'next_year must be reset to 0 in target session');
        $this->assertNull($transferredNyStudent->next_year_section, 'next_year_section must be cleaned up to null in target session');

        // Verify current session DB post-transfer cleanup (Task 7)
        $currentNyStudent = DB::connection('dynamic')->table('student_registration')
            ->where('scholar_no', $nyScholar)
            ->first();

        $this->assertEquals(0, (int)$currentNyStudent->next_year, 'Current session record must have next_year reset to 0 post-transfer');
        $this->assertNull($currentNyStudent->next_year_section, 'Current session record must have next_year_section cleared');
    }
}
