<?php

namespace Tests\Feature;

use App\Http\Controllers\backend\StudentTransferController;
use App\Models\NextYearStudent;
use App\Models\Student_registration;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StudentRecordEditAndSectionAllocationTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['mysql', 'dynamic'];
    protected array $createdScholarNos = [];
    protected array $createdNextSessionScholarNos = [];

    protected function setUp(): void
    {
        parent::setUp();

        $activeDb = '2025_2026';
        Config::set('database.connections.dynamic.database', $activeDb);
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
        }
        if (!empty($this->createdNextSessionScholarNos)) {
            DB::connection('next_session_db')->table('student_registration')
                ->whereIn('scholar_no', $this->createdNextSessionScholarNos)
                ->delete();
        }

        parent::tearDown();
    }

    /**
     * Task 1: Verify student editing does not lose existing saved fields.
     */
    public function test_student_editing_preserves_existing_unmodified_fields()
    {
        $scholarNo = 'SCH_EDIT_' . rand(10000, 99999);
        $this->createdScholarNos[] = $scholarNo;

        $initialJson = [
            'scholar_no' => $scholarNo,
            'studentname_prefix' => 'Master',
            'studentname' => 'Original Student Name',
            'fathername_prefix' => 'Mr.',
            'fathername' => 'Original Father Name',
            'mothername_prefix' => 'Mrs',
            'mothername' => 'Original Mother Name',
            'mobile_number' => '9876543210',
            'phone_number' => '0123456789',
            'present_address' => '123 Test Street, Original City',
            'permanent_address' => '456 Native Place',
            'bankName' => 'State Bank of India',
            'branchName' => 'Main Branch',
            'account_number' => '123456789012',
            'ifsc_code' => 'SBIN0001234',
            'father_organization' => 'Original Father Org',
            'father_office_telephone' => '0755123456',
            'father_education' => 'Post Graduate',
            'mother_education' => 'Graduate',
            'father_res_address' => 'Father Address Line 1',
            'father_emergency_contact' => '9876543211',
            'guardian_name' => 'Original Guardian Name',
            'guardian_emergency_contact' => '9876543212',
            'guardian_relation' => 'Uncle',
            'classname' => '05',
            'section_name' => 'Aryabhatta',
            'session_name' => '2025_2026',
            'batch' => '2025_2026',
            'student_caste' => 'General',
            'religion' => 'Hindu',
            'category' => 'General',
        ];

        $studentId = DB::connection('dynamic')->table('student_registration')->insertGetId([
            'scholar_no' => $scholarNo,
            'student_name' => 'Original Student Name',
            'class_name' => '05',
            'section_name' => 'Aryabhatta',
            'session_name' => '2025_2026',
            'mobile_number' => '9876543210',
            'phone_number' => '0123456789',
            'status' => 'r',
            'is_archived' => 0,
            'json_str' => json_encode($initialJson),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Submit edit updating only student remarks and present address, omitting other fields
        $editPayload = [
            'id' => $studentId,
            'scholar_no' => $scholarNo,
            'classname' => '05',
            'section_name' => 'Aryabhatta',
            'session_name' => '2025_2026',
            'remarks' => 'Updated Remarks Value',
            'present_address' => '999 New Street, Updated City',
        ];

        $response = $this->post('/saveeditregistration', $editPayload);
        $response->assertStatus(302);

        $updated = DB::connection('dynamic')->table('student_registration')->where('id', $studentId)->first();
        $this->assertNotNull($updated);

        $updatedJson = json_decode($updated->json_str, true);
        $this->assertEquals('Updated Remarks Value', $updatedJson['remarks']);
        $this->assertEquals('999 New Street, Updated City', $updatedJson['present_address']);

        // Verify previously saved fields are NOT lost or wiped out
        $this->assertEquals('Original Student Name', $updated->student_name);
        $this->assertEquals('Original Student Name', $updatedJson['studentname']);
        $this->assertEquals('Master', $updatedJson['studentname_prefix']);
        $this->assertEquals('Mr.', $updatedJson['fathername_prefix']);
        $this->assertEquals('Original Father Name', $updatedJson['fathername']);
        $this->assertEquals('Original Mother Name', $updatedJson['mothername']);
        $this->assertEquals('9876543210', $updated->mobile_number);
        $this->assertEquals('9876543210', $updatedJson['mobile_number']);
        $this->assertEquals('State Bank of India', $updatedJson['bankName']);
        $this->assertEquals('123456789012', $updatedJson['account_number']);
        $this->assertEquals('SBIN0001234', $updatedJson['ifsc_code']);
        $this->assertEquals('Original Father Org', $updatedJson['father_organization']);
        $this->assertEquals('0755123456', $updatedJson['father_office_telephone']);
        $this->assertEquals('Original Guardian Name', $updatedJson['guardian_name']);
        $this->assertEquals('Uncle', $updatedJson['guardian_relation']);
    }

    /**
     * Verify that when SSSMID, mother_tongue, and family_SSSMID are emptied/removed on edit, they are saved as empty.
     */
    public function test_clearing_optional_sssmid_mother_tongue_and_family_sssmid_saves_empty_values()
    {
        $scholarNo = 'SCH_CLR_' . rand(10000, 99999);
        $this->createdScholarNos[] = $scholarNo;

        $initialJson = [
            'scholar_no' => $scholarNo,
            'studentname' => 'Clear Test Student',
            'classname' => '05',
            'session_name' => '2025_2026',
            'SSSMID' => '123456789',
            'mother_tongue' => 'Hindi',
            'family_SSSMID' => '98765432',
        ];

        $studentId = DB::connection('dynamic')->table('student_registration')->insertGetId([
            'scholar_no' => $scholarNo,
            'student_name' => 'Clear Test Student',
            'class_name' => '05',
            'session_name' => '2025_2026',
            'status' => 'r',
            'json_str' => json_encode($initialJson),
            'registration_date' => '2025-04-01',
        ]);

        // Submit edit form with SSSMID, mother_tongue, and family_SSSMID completely cleared (empty strings)
        $editPayload = [
            'id' => $studentId,
            'scholar_no' => $scholarNo,
            'classname' => '05',
            'session_name' => '2025_2026',
            'SSSMID' => '',
            'mother_tongue' => '',
            'family_SSSMID' => '',
        ];

        $response = $this->post('/saveeditregistration', $editPayload);
        $response->assertStatus(302);

        $updated = DB::connection('dynamic')->table('student_registration')->where('id', $studentId)->first();
        $this->assertNotNull($updated);

        $updatedJson = json_decode($updated->json_str, true);
        $this->assertEquals('', $updatedJson['SSSMID'] ?? '');
        $this->assertEquals('', $updatedJson['mother_tongue'] ?? '');
        $this->assertEquals('', $updatedJson['family_SSSMID'] ?? '');
    }

    /**
     * Task 3: Stream-based Section Assignment for Class 11 and Class 12:
     * Science -> Kautilya, Humanities/Commerce -> Ramanujan during registration.
     */
    public function test_senior_class_registration_assigns_kautilya_for_science_and_ramanujan_for_commerce()
    {
        // 1. Science student (stream_id = 3)
        $scienceScholarNo = 'SCH_SCI_' . rand(10000, 99999);
        $this->createdScholarNos[] = $scienceScholarNo;

        $sciencePayload = [
            'studentname' => 'Science Senior Student',
            'student_dob' => '2008-05-15',
            'classname' => '11',
            'stream_id' => 3, // SCIENCE
            'scholar_no' => $scienceScholarNo,
            'session_name' => '2025_2026',
            'nationality' => 'Indian',
            'category' => 'General',
            'religion' => 'Hindu',
            'mobile_number' => '9111111111',
        ];

        $responseSci = $this->post('/save-student-registration', $sciencePayload);
        $responseSci->assertStatus(302);

        $sciStudent = DB::connection('dynamic')->table('student_registration')->where('scholar_no', $scienceScholarNo)->first();
        $this->assertNotNull($sciStudent);
        $this->assertEquals('Kautilya', $sciStudent->section_name);

        $sciJson = json_decode($sciStudent->json_str, true);
        $this->assertEquals('Kautilya', $sciJson['section_name']);

        // 2. Commerce student (stream_id = 2)
        $commScholarNo = 'SCH_COMM_' . rand(10000, 99999);
        $this->createdScholarNos[] = $commScholarNo;

        $commPayload = [
            'studentname' => 'Commerce Senior Student',
            'student_dob' => '2008-08-20',
            'classname' => '11',
            'stream_id' => 2, // COMMERCE
            'scholar_no' => $commScholarNo,
            'session_name' => '2025_2026',
            'nationality' => 'Indian',
            'category' => 'General',
            'religion' => 'Hindu',
            'mobile_number' => '9222222222',
        ];

        $responseComm = $this->post('/save-student-registration', $commPayload);
        $responseComm->assertStatus(302);

        $commStudent = DB::connection('dynamic')->table('student_registration')->where('scholar_no', $commScholarNo)->first();
        $this->assertNotNull($commStudent);
        $this->assertEquals('Ramanujan', $commStudent->section_name);

        $commJson = json_decode($commStudent->json_str, true);
        $this->assertEquals('Ramanujan', $commJson['section_name']);
    }

    /**
     * Tasks 2 & 4: Automatic Round Robin section allocation balances Section D new admissions
     * into Aryabhatta, Kautilya, and Ramanujan.
     */
    public function test_automatic_round_robin_merges_section_d_and_balances_strength()
    {
        $targetSession = '2026_2027';
        $testClass = '04';

        // Clean any existing test records for this class in target session if needed
        $prefix = 'TEST_RR_' . rand(100, 999) . '_';

        // Setup base promoted students:
        // Aryabhatta: 2 students
        // Kautilya: 4 students
        // Ramanujan: 3 students
        $existingScholarsMap = [];
        $setupStudent = function ($sec, $idx) use ($targetSession, $testClass, $prefix, &$existingScholarsMap) {
            $scholarNo = $prefix . $sec . '_' . $idx;
            $this->createdNextSessionScholarNos[] = $scholarNo;
            $existingScholarsMap[$scholarNo] = $sec;

            DB::connection('next_session_db')->table('student_registration')->insert([
                'scholar_no' => $scholarNo,
                'student_name' => "Promoted {$sec} {$idx}",
                'class_name' => $testClass,
                'section_name' => $sec,
                'session_name' => $targetSession,
                'is_archived' => 0,
                'status' => 'r',
                'json_str' => json_encode(['section_name' => $sec, 'classname' => $testClass]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        };

        $setupStudent('Aryabhatta', 1);
        $setupStudent('Aryabhatta', 2); // Aryabhatta = 2

        $setupStudent('Kautilya', 1);
        $setupStudent('Kautilya', 2);
        $setupStudent('Kautilya', 3);
        $setupStudent('Kautilya', 4); // Kautilya = 4

        $setupStudent('Ramanujan', 1);
        $setupStudent('Ramanujan', 2);
        $setupStudent('Ramanujan', 3); // Ramanujan = 3

        // Now add 4 new admissions in temporary Section D
        for ($i = 1; $i <= 4; $i++) {
            $dScholarNo = $prefix . 'D_' . $i;
            $this->createdNextSessionScholarNos[] = $dScholarNo;

            DB::connection('next_session_db')->table('student_registration')->insert([
                'scholar_no' => $dScholarNo,
                'student_name' => "New Admission D {$i}",
                'class_name' => $testClass,
                'section_name' => 'D',
                'session_name' => $targetSession,
                'is_archived' => 0,
                'status' => 'r',
                'json_str' => json_encode(['section_name' => 'D', 'classname' => $testClass]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Run the allocation controller method for this class
        $controller = new StudentTransferController();
        $summary = $controller->allocateSectionsForSession($targetSession, [$testClass]);

        $this->assertArrayHasKey($testClass, $summary);
        $this->assertEquals('round_robin_balanced', $summary[$testClass]['type']);
        $this->assertEquals(4, $summary[$testClass]['allocated_from_section_d']);

        // Check that none of the test students remain in Section D
        $remainingD = DB::connection('next_session_db')->table('student_registration')
            ->whereIn('scholar_no', $this->createdNextSessionScholarNos)
            ->where('section_name', 'D')
            ->count();
        $this->assertEquals(0, $remainingD);

        // Before: Aryabhatta=2, Ramanujan=3, Kautilya=4.
        // 4 pending students:
        // Student 1 -> Aryabhatta (2 -> 3)
        // Student 2 -> Aryabhatta (tied at 3 with Ramanujan, RR chooses Aryabhatta -> 4)
        // Student 3 -> Ramanujan (3 -> 4)
        // Student 4 -> Aryabhatta (tied with Kautilya & Ramanujan at 4, RR chooses Aryabhatta -> 5)
        // Final expected: Aryabhatta=5, Kautilya=4, Ramanujan=4 (Max diff <= 1)
        $finalCounts = [
            'Aryabhatta' => DB::connection('next_session_db')->table('student_registration')->whereIn('scholar_no', $this->createdNextSessionScholarNos)->where('section_name', 'Aryabhatta')->count(),
            'Kautilya' => DB::connection('next_session_db')->table('student_registration')->whereIn('scholar_no', $this->createdNextSessionScholarNos)->where('section_name', 'Kautilya')->count(),
            'Ramanujan' => DB::connection('next_session_db')->table('student_registration')->whereIn('scholar_no', $this->createdNextSessionScholarNos)->where('section_name', 'Ramanujan')->count(),
        ];

        $this->assertEquals(5, $finalCounts['Aryabhatta']);
        $this->assertEquals(4, $finalCounts['Kautilya']);
        $this->assertEquals(4, $finalCounts['Ramanujan']);
        $this->assertLessThanOrEqual(1, max($finalCounts) - min($finalCounts));

        // Condition: Existing students must NOT have their sections changed
        foreach ($existingScholarsMap as $sNo => $expectedSec) {
            $stuRecord = DB::connection('next_session_db')->table('student_registration')->where('scholar_no', $sNo)->first();
            $this->assertEquals($expectedSec, $stuRecord->section_name, "Existing student {$sNo} section must remain unchanged!");
        }
    }

    /**
     * Test direct new admission registration assigns lowest-strength section via Round Robin.
     */
    public function test_new_admission_direct_registration_assigns_lowest_strength_section_via_round_robin()
    {
        $testClass = '03';
        $prefix = 'TEST_NA_' . rand(100, 999) . '_';

        // Setup base existing students in dynamic database:
        // Aryabhatta: 3 students
        // Kautilya: 2 students
        // Ramanujan: 3 students
        $setupDynamicStudent = function ($sec, $idx) use ($testClass, $prefix) {
            $scholarNo = $prefix . $sec . '_' . $idx;
            $this->createdScholarNos[] = $scholarNo;

            DB::connection('dynamic')->table('student_registration')->insert([
                'scholar_no' => $scholarNo,
                'student_name' => "Existing Dynamic {$sec} {$idx}",
                'class_name' => $testClass,
                'section_name' => $sec,
                'session_name' => '2025_2026',
                'is_archived' => 0,
                'status' => 'r',
                'json_str' => json_encode(['section_name' => $sec, 'classname' => $testClass]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        };

        $setupDynamicStudent('Aryabhatta', 1);
        $setupDynamicStudent('Aryabhatta', 2);
        $setupDynamicStudent('Aryabhatta', 3); // 3

        $setupDynamicStudent('Kautilya', 1);
        $setupDynamicStudent('Kautilya', 2); // 2 (lowest!)

        $setupDynamicStudent('Ramanujan', 1);
        $setupDynamicStudent('Ramanujan', 2);
        $setupDynamicStudent('Ramanujan', 3); // 3

        // Register a new admission without specifying section
        $newScholar = $prefix . 'NEW_ADM';
        $this->createdScholarNos[] = $newScholar;

        $postData = [
            'scholar_no' => $newScholar,
            'application_for' => 'CBSE',
            'form_number' => 'FN_' . rand(1000, 9999),
            'studentname' => 'New Admission RR Student',
            'gender' => 'Male',
            'student_dob' => '2015-08-10',
            'session_name' => '2025_2026',
            'batch' => '2025-2026',
            'classname' => $testClass,
            'next_year' => 0,
            'required_school_transport' => 'no',
        ];

        $response = $this->withSession(['selectedYear' => '2025_2026', 'db_names' => '2025_2026'])
            ->post('save-student-registration', $postData);
        $response->assertStatus(302);

        $savedStudent = DB::connection('dynamic')->table('student_registration')->where('scholar_no', $newScholar)->first();
        $this->assertNotNull($savedStudent);
        // Lowest strength among (Aryabhatta: 3, Kautilya: 2, Ramanujan: 3) is Kautilya!
        $this->assertEquals('Kautilya', $savedStudent->section_name);
    }

    /**
     * Test console command session:allocate-sections.
     */
    public function test_artisan_session_allocate_sections_command_executes_successfully()
    {
        $this->artisan('session:allocate-sections 2026_2027 --class=04')
            ->assertExitCode(0);
    }
}
