<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NextYearStudentRegistrationAndSessionDisplayTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['mysql', 'dynamic'];
    protected array $createdInquiryIds = [];
    protected array $createdScholarNos = [];

    protected function setUp(): void
    {
        parent::setUp();

        $activeDb = '2025_2026';
        Config::set('database.connections.dynamic.database', $activeDb);
        DB::purge('dynamic');

        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user, 'web');
    }

    protected function tearDown(): void
    {
        if (!empty($this->createdScholarNos)) {
            DB::connection('dynamic')->table('student_registration')
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
     * Test that getDataByFormNumberstudent_registration fetches exact inquiry by ID
     * and does NOT return soft-deleted records with the same form number.
     */
    public function test_get_data_by_inquiry_id_prioritizes_active_record()
    {
        $uniqueFormNo = '99' . rand(10000, 99999);

        // 1. Insert an older deleted inquiry with this form number
        $deletedId = DB::connection('dynamic')->table('inquiry_registration')->insertGetId([
            'application_for' => 'CBSE',
            'form_number' => $uniqueFormNo,
            'date_of_birth' => '2015-01-01',
            'class_name' => '05',
            'student_name' => 'Old Deleted Student',
            'gender' => 'Male',
            'session_name' => '2025_2026',
            'json_str' => json_encode([
                'studentname' => 'Old Deleted Student',
                'fathername' => 'Old Father',
                'admission_type' => 'CBSE',
                'caste' => 'Old Caste',
            ]),
            'status' => 'i',
            'next_year' => 0,
            'save_status' => 'Form Selected',
            'is_delete' => 1,
            'created_at' => now()->subYear(),
            'updated_at' => now()->subYear(),
        ]);
        $this->createdInquiryIds[] = $deletedId;

        // 2. Insert a new active next-year inquiry with same form number
        $activeNextYearId = DB::connection('dynamic')->table('inquiry_registration')->insertGetId([
            'application_for' => 'CBSE',
            'form_number' => $uniqueFormNo,
            'date_of_birth' => '2018-05-10',
            'class_name' => '01',
            'student_name' => 'Next Year Active Student',
            'gender' => 'Female',
            'session_name' => '2025_2026',
            'json_str' => json_encode([
                'studentname' => 'Next Year Active Student',
                'fathername' => 'New Father',
                'admission_type' => 'CBSE',
                'caste' => 'New Caste',
            ]),
            'status' => 'i',
            'next_year' => 1,
            'save_status' => 'Form Selected',
            'is_delete' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->createdInquiryIds[] = $activeNextYearId;

        // Case A: Query by inquiry_id
        $response = $this->withSession(['db_names' => '2025_2026', 'selectedYear' => '2025_2026'])
            ->postJson('/getDataByFormNumberstudent_registration', [
                'form_number' => $uniqueFormNo,
                'inquiry_id' => $activeNextYearId,
            ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertNotNull($data['inq_data']);
        $this->assertEquals($activeNextYearId, $data['inq_data']['id']);
        $this->assertEquals('Next Year Active Student', $data['inq_data']['student_name']);
        $this->assertEquals('01', $data['inq_data']['class_name']);
        $this->assertEquals(1, $data['next_year']);

        // Case B: Query by form_number only without inquiry_id
        $responseFallback = $this->withSession(['db_names' => '2025_2026', 'selectedYear' => '2025_2026'])
            ->postJson('/getDataByFormNumberstudent_registration', [
                'form_number' => $uniqueFormNo,
            ]);

        $responseFallback->assertStatus(200);
        $dataFallback = $responseFallback->json();
        $this->assertNotNull($dataFallback['inq_data']);
        $this->assertEquals($activeNextYearId, $dataFallback['inq_data']['id']);
        $this->assertEquals('Next Year Active Student', $dataFallback['inq_data']['student_name']);
    }

    /**
     * Test saving next-year student registration correctly calculates admission session
     * as 2026_2027 when registered from 2025_2026 session with next_year = 1.
     */
    public function test_save_student_registration_sets_admission_session_for_next_year()
    {
        $uniqueScholarNo = '99' . rand(10000, 99999);
        $this->createdScholarNos[] = $uniqueScholarNo;

        $inquiryId = DB::connection('dynamic')->table('inquiry_registration')->insertGetId([
            'application_for' => 'CBSE',
            'form_number' => '88' . rand(1000, 9999),
            'date_of_birth' => '2019-02-14',
            'class_name' => 'KG1',
            'student_name' => 'Master Future Star',
            'gender' => 'Male',
            'session_name' => '2025_2026',
            'status' => 'i',
            'next_year' => 1,
            'save_status' => 'Form Selected',
            'is_delete' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->createdInquiryIds[] = $inquiryId;

        $response = $this->withSession(['db_names' => '2025_2026', 'selectedYear' => '2025_2026'])
            ->post('/save-student-registration', [
                'scholar_no' => $uniqueScholarNo,
                'registration_date' => '2026-11-17',
                'application_for' => 'CBSE',
                'form_number' => '88801',
                'studentname' => 'Master Future Star',
                'student_dob' => '2019-02-14',
                'classname' => 'KG1',
                'session_name' => '2025_2026',
                'batch' => '2025_2026',
                'iid' => $inquiryId,
                'next_year' => '1',
            ]);

        $response->assertRedirect();

        $savedStudent = DB::connection('dynamic')->table('student_registration')
            ->where('scholar_no', $uniqueScholarNo)
            ->first();

        $this->assertNotNull($savedStudent);
        $this->assertEquals('Master Future Star', $savedStudent->student_name);
        $this->assertEquals('KG1', $savedStudent->class_name);
        $this->assertEquals('D', $savedStudent->section_name);

        $jsonData = json_decode($savedStudent->json_str, true);
        $this->assertEquals(1, $jsonData['next_year']);
        $this->assertEquals('2026_2027', $jsonData['admission_session']);
        $this->assertEquals('2026_2027', $jsonData['batch']);

        // Inquiry must now be consumed (is_delete = 1)
        $inquiryAfter = DB::connection('dynamic')->table('inquiry_registration')
            ->where('id', $inquiryId)
            ->first();
        $this->assertEquals(1, $inquiryAfter->is_delete);
    }

    /**
     * Test that student registration index view renders Current Session, Admission Session,
     * and Batch columns with Next Year badge for upcoming session students.
     */
    public function test_student_registration_list_displays_admission_session_and_batch()
    {
        $uniqueScholarNo = '99' . rand(10000, 99999);
        $this->createdScholarNos[] = $uniqueScholarNo;

        DB::connection('dynamic')->table('student_registration')->insert([
            'scholar_no' => $uniqueScholarNo,
            'form_number' => '77' . rand(1000, 9999),
            'student_name' => 'Upcoming Session Candidate',
            'date_of_birth' => '2018-09-01',
            'class_name' => '02',
            'section_name' => 'D',
            'session_name' => '2025_2026',
            'status' => 'r',
            'is_archived' => 0,
            'registration_date' => '2026-11-17',
            'json_str' => json_encode([
                'scholar_no' => $uniqueScholarNo,
                'studentname' => 'Upcoming Session Candidate',
                'next_year' => 1,
                'admission_session' => '2026_2027',
                'batch' => '2026_2027',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession(['db_names' => '2025_2026', 'selectedYear' => '2025_2026'])
            ->get(route('student-registrations'));

        $response->assertStatus(200);

        // Header verification
        $response->assertSee('Current Session');
        $response->assertSee('Admission Session');
        $response->assertSee('Batch');
        $response->assertSee('Section');
        $response->assertSee('table-sticky-container');
        $response->assertSee('min-width: 85px');

        // Content verification
        $response->assertSee('Upcoming Session Candidate');
        $response->assertSee('2025-2026');
        $response->assertSee('2026-2027');
        $response->assertSee('Next Year');
    }

    /**
     * Test that student registration list, current year registration list,
     * archived students list, and CSV export all display the Section column and sticky header container.
     */
    public function test_section_column_and_sticky_header_across_views_and_csv()
    {
        $uniqueScholarNo = '99' . rand(10000, 99999);
        $this->createdScholarNos[] = $uniqueScholarNo;

        DB::connection('dynamic')->table('student_registration')->insert([
            'scholar_no' => $uniqueScholarNo,
            'form_number' => '77' . rand(1000, 9999),
            'student_name' => 'Section Test Student',
            'date_of_birth' => '2018-09-01',
            'class_name' => '05',
            'section_name' => 'D',
            'session_name' => '2025_2026',
            'status' => 'r',
            'is_archived' => 0,
            'registration_date' => '2026-11-17',
            'json_str' => json_encode([
                'scholar_no' => $uniqueScholarNo,
                'studentname' => 'Section Test Student',
                'section_name' => 'D',
                'next_year' => 0,
                'admission_session' => '2025_2026',
                'batch' => '2025_2026',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Check all registrations list
        $indexResponse = $this->withSession(['db_names' => '2025_2026', 'selectedYear' => '2025_2026'])
            ->get(route('student-registrations'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('<th>Section</th>', false);
        $indexResponse->assertSee('table-sticky-container');
        $indexResponse->assertSee('sno-column');

        // 2. Check current year registrations list
        $currentResponse = $this->withSession(['db_names' => '2025_2026', 'selectedYear' => '2025_2026'])
            ->get(url('curren-year-student-registrations'));
        $currentResponse->assertStatus(200);
        $currentResponse->assertSee('<th>Section</th>', false);
        $currentResponse->assertSee('table-sticky-container');
        $currentResponse->assertSee('sno-column');

        // 3. Check archived students list
        $archivedResponse = $this->withSession(['db_names' => '2025_2026', 'selectedYear' => '2025_2026'])
            ->get(route('archived-students'));
        $archivedResponse->assertStatus(200);
        $archivedResponse->assertSee('<th>Section</th>', false);
        $archivedResponse->assertSee('table-sticky-container');
        $archivedResponse->assertSee('sno-column');

        // 4. Check CSV export contains Section header
        $csvResponse = $this->withSession(['db_names' => '2025_2026', 'selectedYear' => '2025_2026'])
            ->post(route('export_registrations.csv'), [
                'student_name' => 'Section Test Student',
            ]);
        $csvResponse->assertStatus(200);
        $csvContent = $csvResponse->getContent();
        $this->assertStringContainsString('Section', $csvContent);
        $this->assertStringContainsString('Section Test Student', $csvContent);
    }

    /**
     * Test live search box UI elements and backend filter support across
     * Student Name, Parent Name, Scholar No., and Mobile No.
     */
    public function test_live_search_box_and_filtering_for_student_parent_scholar_and_mobile()
    {
        $uniqueScholarNo = '99' . rand(10000, 99999);
        $this->createdScholarNos[] = $uniqueScholarNo;

        DB::connection('dynamic')->table('student_registration')->insert([
            'scholar_no' => $uniqueScholarNo,
            'form_number' => '55' . rand(1000, 9999),
            'student_name' => 'Aarav Patel',
            'mobile_number' => '9876543210',
            'date_of_birth' => '2017-04-12',
            'class_name' => '04',
            'section_name' => 'Aryabhatta',
            'session_name' => '2025_2026',
            'status' => 'r',
            'is_archived' => 0,
            'registration_date' => '2026-11-18',
            'json_str' => json_encode([
                'scholar_no' => $uniqueScholarNo,
                'studentname' => 'Aarav Patel',
                'student_father_name' => 'Vikram Patel',
                'father_mobile' => '9876543210',
                'mothername' => 'Meera Patel',
                'mother_mobile' => '9123456789',
                'admission_session' => '2025_2026',
                'batch' => '2025_2026',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 1. Check UI elements on registration list
        $response = $this->withSession(['db_names' => '2025_2026', 'selectedYear' => '2025_2026'])
            ->get(route('student-registrations'));
        $response->assertStatus(200);
        $response->assertSee('id="live_student_search"', false);
        $response->assertSee('id="clear_live_student_search"', false);
        $response->assertSee('Search Student Name, Parent Name, Scholar No, Mobile No...', false);
        $response->assertSee('applyLiveStudentSearch', false);
        // Verify redundant Student Name field was removed from the top filter form
        $response->assertDontSee('name="student_name" class="form-control uperletter" id="studentname"', false);

        // 2. Check rendered student row has all target values for live typing match
        $response->assertSee('Aarav Patel');
        $response->assertSee('Vikram Patel');
        $response->assertSee('Meera Patel');
        $response->assertSee($uniqueScholarNo);
        $response->assertSee('9876543210');
        $response->assertSee('9123456789');

        // 3. Backend filter check: search by Student Name
        $filterStudent = $this->withSession(['db_names' => '2025_2026', 'selectedYear' => '2025_2026'])
            ->post('/student-registration-filter', ['student_name' => 'Aarav']);
        $filterStudent->assertStatus(200);
        $filterStudent->assertSee('Aarav Patel');

        // 4. Backend filter check: search by Parent Name (Mother)
        $filterParent = $this->withSession(['db_names' => '2025_2026', 'selectedYear' => '2025_2026'])
            ->post('/student-registration-filter', ['parent_name' => 'Meera']);
        $filterParent->assertStatus(200);
        $filterParent->assertSee('Aarav Patel');

        // 5. Backend filter check: search by Scholar No
        $filterScholar = $this->withSession(['db_names' => '2025_2026', 'selectedYear' => '2025_2026'])
            ->post('/student-registration-filter', ['scholar_no' => $uniqueScholarNo]);
        $filterScholar->assertStatus(200);
        $filterScholar->assertSee('Aarav Patel');

        // 6. Backend filter check: search by Mobile No
        $filterMobile = $this->withSession(['db_names' => '2025_2026', 'selectedYear' => '2025_2026'])
            ->post('/student-registration-filter', ['mobile_number' => '9876543210']);
        $filterMobile->assertStatus(200);
        $filterMobile->assertSee('Aarav Patel');

        // 7. Backend filter check: quick_search unified input
        $filterQuick = $this->withSession(['db_names' => '2025_2026', 'selectedYear' => '2025_2026'])
            ->post('/student-registration-filter', ['quick_search' => 'Vikram']);
        $filterQuick->assertStatus(200);
        $filterQuick->assertSee('Aarav Patel');
    }

    /**
     * Test that only active students are displayed in student registration list,
     * archived students (is_archived = 1) are excluded, and the badge clearly displays Active Students.
     */
    public function test_active_students_list_strictly_excludes_archived_students()
    {
        // 1. Fetch real active student and archived student from 2025_2026
        $activeStudent = DB::connection('dynamic')->table('student_registration')
            ->where(function ($query) {
                $query->where('is_archived', 0)->orWhereNull('is_archived');
            })
            ->where('status', 'r')
            ->first();

        $archivedStudent = DB::connection('dynamic')->table('student_registration')
            ->where('is_archived', 1)
            ->first();

        $this->assertNotNull($activeStudent, 'An active student should exist in 2025_2026 database');
        $this->assertNotNull($archivedStudent, 'An archived student should exist in 2025_2026 database');

        $activeName = ucwords($activeStudent->student_name);
        $archivedName = ucwords($archivedStudent->student_name);

        // 2. Check student-registrations (all active registrations)
        $response = $this->withSession(['db_names' => '2025_2026', 'selectedYear' => '2025_2026'])
            ->get(route('student-registrations'));
        $response->assertStatus(200);
        $response->assertSee('Active Students');
        $allInquiry = $response->viewData('all_inquiry');
        $this->assertTrue($allInquiry->contains('id', $activeStudent->id), 'Active student must be present in all registrations');
        $this->assertFalse($allInquiry->contains('id', $archivedStudent->id), 'Archived student must NOT be present in all registrations');

        // 3. Check current session registrations
        $currentResponse = $this->withSession(['db_names' => '2025_2026', 'selectedYear' => '2025_2026'])
            ->get(url('curren-year-student-registrations'));
        $currentResponse->assertStatus(200);
        $currentResponse->assertSee('Active Students');
        $currentInquiry = $currentResponse->viewData('all_inquiry');
        $this->assertFalse($currentInquiry->contains('id', $archivedStudent->id), 'Archived student must NOT be present in current year registrations');

        // 4. Check archived students page DOES include the archived student and NOT active student
        $archivedResponse = $this->withSession(['db_names' => '2025_2026', 'selectedYear' => '2025_2026'])
            ->get(route('archived-students'));
        $archivedResponse->assertStatus(200);
        $archivedResponse->assertSee('Archived Students');
        $archivedInquiry = $archivedResponse->viewData('all_inquiry');
        $this->assertTrue($archivedInquiry->contains('id', $archivedStudent->id), 'Archived student must be present in archived students view');
        $this->assertFalse($archivedInquiry->contains('id', $activeStudent->id), 'Active student must NOT be present in archived students view');
    }
}
