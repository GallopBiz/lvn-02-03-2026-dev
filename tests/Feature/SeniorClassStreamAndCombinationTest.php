<?php

namespace Tests\Feature;

use App\Models\Classes;
use App\Models\Student_registration;
use App\Models\SubjectAssignStudent;
use App\Models\SubjectCombination;
use App\Models\Stream;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeniorClassStreamAndCombinationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user, 'web');
    }

    /**
     * Test GET academic/class-streams for Class 11 returns is_senior: true and active streams.
     */
    public function test_get_class_streams_for_class_11()
    {
        $response = $this->getJson(route('academic.class-streams', ['class_name' => '11']));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_senior' => true,
            'class_name' => '11',
        ]);
        $streams = $response->json('streams');
        $this->assertNotEmpty($streams);
        $streamNames = array_column($streams, 'stream_name');
        $this->assertContains('SCIENCE', $streamNames);
    }

    /**
     * Test GET academic/class-streams for Class 12 returns is_senior: true and active streams.
     */
    public function test_get_class_streams_for_class_12()
    {
        $response = $this->getJson(route('academic.class-streams', ['class_name' => '12']));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_senior' => true,
            'class_name' => '12',
        ]);
        $streams = $response->json('streams');
        $streamNames = array_column($streams, 'stream_name');
        $this->assertContains('SCIENCE', $streamNames);
        $this->assertContains('COMMERCE', $streamNames);
        $this->assertContains('HUMANITIES', $streamNames);
    }

    /**
     * Test GET academic/class-streams for junior classes (e.g., Class 10, Nursery) returns is_senior: false.
     */
    public function test_get_class_streams_for_junior_classes_returns_empty_and_not_senior()
    {
        foreach (['10', '09', 'KG1', 'Nursery'] as $cls) {
            $response = $this->getJson(route('academic.class-streams', ['class_name' => $cls]));
            $response->assertStatus(200);
            $response->assertJson([
                'success' => true,
                'is_senior' => false,
                'streams' => [],
            ]);
        }
    }

    /**
     * Test GET academic/stream-combinations for Class 11 and Science.
     */
    public function test_get_stream_combinations_for_class_11_science()
    {
        $scienceStream = DB::connection('dynamic')->table('streams')->where('streams', 'SCIENCE')->where('is_delete', 0)->first();
        $this->assertNotNull($scienceStream);

        $response = $this->getJson(route('academic.stream-combinations', [
            'class_name' => '11',
            'stream_id' => $scienceStream->id,
        ]));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_senior' => true,
            'class_name' => '11',
            'stream_id' => (string) $scienceStream->id,
        ]);
        $combs = $response->json('combinations');
        $this->assertNotEmpty($combs);
        $combNames = array_column($combs, 'combination_name');
        $hasPcm = false;
        foreach ($combNames as $cn) {
            if (stripos($cn, 'PCM') !== false) {
                $hasPcm = true;
                break;
            }
        }
        $this->assertTrue($hasPcm, 'Class 11 Science combinations should contain PCM combinations.');
    }

    /**
     * Test GET academic/stream-combinations for Class 12 and Commerce.
     */
    public function test_get_stream_combinations_for_class_12_commerce()
    {
        $commerceStream = DB::connection('dynamic')->table('streams')->where('streams', 'COMMERCE')->where('is_delete', 0)->first();
        $this->assertNotNull($commerceStream);

        $response = $this->getJson(route('academic.stream-combinations', [
            'class_name' => '12',
            'stream_id' => $commerceStream->id,
        ]));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_senior' => true,
            'class_name' => '12',
            'stream_id' => (string) $commerceStream->id,
        ]);
        $combs = $response->json('combinations');
        $this->assertNotEmpty($combs);
    }

    /**
     * Test GET academic/stream-combinations for junior classes returns empty.
     */
    public function test_get_stream_combinations_for_junior_class_returns_empty()
    {
        $response = $this->getJson(route('academic.stream-combinations', [
            'class_name' => '10',
            'stream_id' => 1,
        ]));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'is_senior' => false,
            'combinations' => [],
        ]);
    }

    /**
     * Test saving new student registration for Class 11 with stream and combination
     * automatically creates assignment in subject_assign_student.
     */
    public function test_save_student_registration_for_class_11_assigns_combination()
    {
        $scienceStream = DB::connection('dynamic')->table('streams')->where('streams', 'SCIENCE')->where('is_delete', 0)->first();
        $comb = DB::connection('dynamic')->table('subject_combinations')
            ->where('is_delete', 0)
            ->where('streams', $scienceStream->id)
            ->whereIn('class_id', [14, '11'])
            ->first();

        $scholarNo = 'TEST' . rand(100000, 999999);
        $postData = [
            'scholar_no' => $scholarNo,
            'registration_date' => date('Y-m-d'),
            'application_for' => 'New',
            'form_number' => 'FN' . rand(1000, 9999),
            'studentname' => 'Test Class 11 Senior Student',
            'gender' => 'Male',
            'student_dob' => '2009-05-15',
            'session_name' => '2026_2027',
            'batch' => '2026-2027',
            'category' => 'GEN',
            'religion' => 'HINDU',
            'student_caste' => 'General',
            'classname' => '11',
            'section_name' => 'Aryabhatta',
            'stream_id' => $scienceStream->id,
            'combination_id' => $comb->id,
            'required_school_transport' => 'no',
        ];

        $response = $this->post('save-student-registration', $postData);
        $response->assertStatus(302);

        $student = DB::connection('dynamic')->table('student_registration')->where('scholar_no', $scholarNo)->first();
        $this->assertNotNull($student);
        $this->assertEquals('11', $student->class_name);
        $this->assertEquals('Aryabhatta', $student->section_name);

        $json = json_decode($student->json_str, true);
        $this->assertEquals($scienceStream->id, $json['stream_id']);
        $this->assertEquals($comb->id, $json['combination_id']);

        // Verify subject_assign_student record
        $assignment = DB::connection('dynamic')->table('subject_assign_student')
            ->where('students_details', $student->id)
            ->where('is_delete', 0)
            ->first();
        $this->assertNotNull($assignment, 'subject_assign_student record must be created for Class 11 student.');
        $this->assertEquals($comb->id, $assignment->assign_this_combtoall);
        $this->assertEquals('11', $assignment->class_name);
        $this->assertEquals('Aryabhatta', $assignment->section_name);
    }

    /**
     * Test saving new student registration for junior class (Class 05) does NOT create subject_assign_student.
     */
    public function test_save_student_registration_for_junior_class_does_not_create_subject_assignment()
    {
        $scholarNo = 'TEST' . rand(100000, 999999);
        $postData = [
            'scholar_no' => $scholarNo,
            'registration_date' => date('Y-m-d'),
            'application_for' => 'New',
            'form_number' => 'FN' . rand(1000, 9999),
            'studentname' => 'Test Class 05 Junior Student',
            'gender' => 'Female',
            'student_dob' => '2016-08-20',
            'session_name' => '2026_2027',
            'batch' => '2026-2027',
            'category' => 'GEN',
            'religion' => 'HINDU',
            'student_caste' => 'General',
            'classname' => '05',
            'section_name' => 'D',
            'required_school_transport' => 'no',
        ];

        $response = $this->post('save-student-registration', $postData);
        $response->assertStatus(302);

        $student = DB::connection('dynamic')->table('student_registration')->where('scholar_no', $scholarNo)->first();
        $this->assertNotNull($student);
        $this->assertEquals('05', $student->class_name);

        $assignment = DB::connection('dynamic')->table('subject_assign_student')
            ->where('students_details', $student->id)
            ->where('is_delete', 0)
            ->first();
        $this->assertNull($assignment, 'Junior class student should not have subject_assign_student record.');
    }

    /**
     * Test changing student combination later through Academic Module (AssignSubjectController::store).
     */
    public function test_changing_combination_through_academic_module_updates_assignment_and_student_record()
    {
        // 1. Create a Class 12 student
        $studentId = DB::connection('dynamic')->table('student_registration')->insertGetId([
            'scholar_no' => 'SCH' . rand(10000, 99999),
            'student_name' => 'Class 12 Academic Test Student',
            'class_name' => '12',
            'section_name' => 'Aryabhatta',
            'session_name' => '2026_2027',
            'json_str' => json_encode([
                'classname' => '12',
                'section_name' => 'Aryabhatta',
                'stream_id' => 3, // Science
                'combination_id' => 4, // PCM 12+PE
            ]),
            'is_archived' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Initial assignment
        DB::connection('dynamic')->table('subject_assign_student')->insert([
            'students_details' => $studentId,
            'class_name' => '12',
            'section_name' => 'Aryabhatta',
            'assign_this_combtoall' => 4,
            'is_delete' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Change combination through Academic Module: store-AssignSubject (e.g. switch to combination 5: pCM 12+CS)
        $response = $this->post('store-AssignSubject', [
            'studentclass' => '12',
            'section_name' => 'Aryabhatta',
            'student_id' => $studentId,
            'combination_name' => 5,
        ]);
        $response->assertStatus(302);

        // Verify subject_assign_student updated
        $updatedAssignment = DB::connection('dynamic')->table('subject_assign_student')
            ->where('students_details', $studentId)
            ->where('is_delete', 0)
            ->first();
        $this->assertNotNull($updatedAssignment);
        $this->assertEquals(5, $updatedAssignment->assign_this_combtoall);

        // Verify student_registration json_str also updated in sync
        $updatedStudent = DB::connection('dynamic')->table('student_registration')->where('id', $studentId)->first();
        $json = json_decode($updatedStudent->json_str, true);
        $this->assertEquals(5, $json['combination_id']);
    }

    /**
     * Test updating student combination via student edit registration (saveeditregistration).
     */
    public function test_updating_combination_via_edit_registration()
    {
        $studentId = DB::connection('dynamic')->table('student_registration')->insertGetId([
            'scholar_no' => 'SCH' . rand(10000, 99999),
            'student_name' => 'Class 12 Edit Student',
            'class_name' => '12',
            'section_name' => 'Aryabhatta',
            'session_name' => '2026_2027',
            'json_str' => json_encode([
                'classname' => '12',
                'section_name' => 'Aryabhatta',
                'stream_id' => 3,
                'combination_id' => 4,
            ]),
            'is_archived' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('saveeditregistration', [
            'id' => $studentId,
            'classname' => '12',
            'section_name' => 'Kautilya',
            'session_name' => '2026_2027',
            'studentname' => 'Class 12 Edit Student',
            'stream_id' => 3,
            'combination_id' => 6, // pCM 12+IP
        ]);
        $response->assertStatus(302);

        $assignment = DB::connection('dynamic')->table('subject_assign_student')
            ->where('students_details', $studentId)
            ->where('is_delete', 0)
            ->first();
        $this->assertNotNull($assignment);
        $this->assertEquals(6, $assignment->assign_this_combtoall);
        $this->assertEquals('12', $assignment->class_name);
        $this->assertEquals('Kautilya', $assignment->section_name);
    }
}
