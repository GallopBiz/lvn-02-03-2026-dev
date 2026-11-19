<?php

namespace Tests\Feature;

use App\Models\Classes;
use App\Models\Student_registration;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SectionAssignmentAndClassStandardizationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::first() ?? User::factory()->create();
        $this->actingAs($user, 'web');
    }

    /**
     * Test that class names are standardized to KG1 and KG2, and no binary Kg1/Kg2 remains.
     */
    public function test_classes_table_has_kg1_and_kg2_and_no_binary_kg_remains()
    {
        $binaryMatches = DB::select("SELECT COUNT(*) as cnt FROM classes WHERE BINARY class_name IN ('Kg1', 'Kg2')");
        $this->assertEquals(0, $binaryMatches[0]->cnt, 'There should be no binary Kg1 or Kg2 in classes table.');

        $kg1Sections = Classes::where('class_name', 'KG1')->where('is_delete', 0)->pluck('section_name')->all();
        $this->assertContains('Aryabhatta', $kg1Sections);
        $this->assertContains('Kautilya', $kg1Sections);
        $this->assertContains('Ramanujan', $kg1Sections);
        $this->assertContains('D', $kg1Sections);

        $kg2Sections = Classes::where('class_name', 'KG2')->where('is_delete', 0)->pluck('section_name')->all();
        $this->assertContains('Aryabhatta', $kg2Sections);
        $this->assertContains('Kautilya', $kg2Sections);
        $this->assertContains('Ramanujan', $kg2Sections);
        $this->assertContains('D', $kg2Sections);
    }

    /**
     * Test that Section D exists in classes table for Nursery to Class 10.
     */
    public function test_section_d_exists_for_nursery_to_class_10()
    {
        $expectedClasses = ['Nursery', 'KG1', 'KG2', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10'];

        foreach ($expectedClasses as $cls) {
            $exists = Classes::where('class_name', $cls)->where('section_name', 'D')->where('is_delete', 0)->exists();
            $this->assertTrue($exists, "Section D should exist for class {$cls}");
        }
    }

    /**
     * Test class promotion mapping in StudentTransferController.
     */
    public function test_class_promotion_mapping_handles_kg1_and_kg2()
    {
        $controller = new \App\Http\Controllers\backend\StudentTransferController();
        $ref = new \ReflectionClass($controller);
        $method = $ref->getMethod('getNextClassName');
        $method->setAccessible(true);

        $this->assertEquals('KG1', $method->invoke($controller, 'Nursery'));
        $this->assertEquals('KG2', $method->invoke($controller, 'KG1'));
        $this->assertEquals('KG2', $method->invoke($controller, 'Kg1'));
        $this->assertEquals('01', $method->invoke($controller, 'KG2'));
        $this->assertEquals('01', $method->invoke($controller, 'Kg2'));
        $this->assertEquals('02', $method->invoke($controller, '01'));
        $this->assertEquals('10', $method->invoke($controller, '09'));
        $this->assertEquals('11', $method->invoke($controller, '10'));
    }

    /**
     * Test the class section strength endpoint.
     */
    public function test_class_section_strength_endpoint()
    {
        $response = $this->get('/class-section-strength?class_name=KG1');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'class_name',
            'is_applicable',
            'sections' => [
                '*' => ['section', 'strength'],
            ],
            'total',
        ]);

        $data = $response->json();
        $this->assertTrue($data['is_applicable']);
        $this->assertEquals('KG1', $data['class_name']);

        $sections = collect($data['sections'])->pluck('section')->all();
        $this->assertContains('Aryabhatta', $sections);
        $this->assertContains('Kautilya', $sections);
        $this->assertContains('Ramanujan', $sections);
        $this->assertContains('D', $sections);
    }

    /**
     * Test class section strength endpoint for Class 11 and 12 is marked not applicable.
     */
    public function test_class_section_strength_endpoint_for_senior_classes()
    {
        $response = $this->get('/class-section-strength?class_name=11');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertFalse($data['is_applicable'], 'Class 11 should not be applicable for Nursery-10 rule.');
    }

    /**
     * Test that new registration in Nursery to Class 10 defaults to Section D when section is not provided.
     */
    public function test_new_student_registration_defaults_to_section_d()
    {
        $scholarNo = 'TEST_SCH_' . rand(10000, 99999);
        $postData = [
            'scholar_no' => $scholarNo,
            'form_number' => 'FN_' . rand(1000, 9999),
            'studentname' => 'Auto Section D Student',
            'classname' => 'KG1',
            'student_dob' => '2020-05-15',
            'application_for' => 'CBSE',
            'session_name' => '2026_2027',
            'category' => 'GEN',
            'religion' => 'HINDU',
            'student_caste' => 'General',
            'required_school_transport' => 'no',
        ];

        $response = $this->post('/save-student-registration', $postData);
        $response->assertStatus(302);

        $student = DB::connection('dynamic')->table('student_registration')
            ->where('scholar_no', $scholarNo)
            ->first();

        $this->assertNotNull($student);
        $this->assertEquals('D', $student->section_name);
        $this->assertEquals('KG1', $student->class_name);

        // Verify class_id points to KG1 Section D
        $classD = Classes::where('class_name', 'KG1')->where('section_name', 'D')->first();
        $this->assertNotNull($classD);
        $this->assertEquals($classD->id, $student->class_id);

        $json = json_decode($student->json_str, true);
        $this->assertEquals('D', $json['section_name']);
        $this->assertEquals($classD->id, $json['class_id']);
    }

    /**
     * Test that new registration assigns explicitly chosen section (e.g. Aryabhatta).
     */
    public function test_new_student_registration_assigns_explicit_section()
    {
        $scholarNo = 'TEST_SCH_' . rand(10000, 99999);
        $postData = [
            'scholar_no' => $scholarNo,
            'form_number' => 'FN_' . rand(1000, 9999),
            'studentname' => 'Explicit Section Student',
            'classname' => '02',
            'section_name' => 'Aryabhatta',
            'student_dob' => '2019-03-10',
            'application_for' => 'CBSE',
            'session_name' => '2026_2027',
            'category' => 'GEN',
            'religion' => 'HINDU',
            'student_caste' => 'General',
            'required_school_transport' => 'no',
        ];

        $response = $this->post('/save-student-registration', $postData);
        $response->assertStatus(302);

        $student = DB::connection('dynamic')->table('student_registration')
            ->where('scholar_no', $scholarNo)
            ->first();

        $this->assertNotNull($student);
        $this->assertEquals('Aryabhatta', $student->section_name);
        $this->assertEquals('02', $student->class_name);

        $classArya = Classes::where('class_name', '02')->where('section_name', 'Aryabhatta')->first();
        $this->assertNotNull($classArya);
        $this->assertEquals($classArya->id, $student->class_id);
    }

    /**
     * Test that admin can manually reallocate Section D student using SectionController.
     */
    public function test_admin_can_reallocate_section_d_student_to_actual_section()
    {
        // 1. Create a student in Section D
        $classD = Classes::where('class_name', 'KG1')->where('section_name', 'D')->first();
        $classRamanujan = Classes::where('class_name', 'KG1')->where('section_name', 'Ramanujan')->first();

        $studentId = DB::connection('dynamic')->table('student_registration')->insertGetId([
            'scholar_no' => 'TEST_REALLOC_' . rand(1000, 9999),
            'student_name' => 'Realloc Candidate',
            'class_name' => 'KG1',
            'section_name' => 'D',
            'class_id' => $classD->id,
            'session_name' => '2026_2027',
            'status' => 'r',
            'is_archived' => 0,
            'json_str' => json_encode(['classname' => 'KG1', 'section_name' => 'D', 'class_id' => $classD->id]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Test fetch_section_student_data returns this student
        $controller = new \App\Http\Controllers\SectionController();
        $request = new \Illuminate\Http\Request(['class_name' => 'KG1']);
        $fetched = $controller->fetch_section_student_data($request);

        $found = collect($fetched['studentds'])->firstWhere('id', $studentId);
        $this->assertNotNull($found, 'Student in Section D should be fetched for section assignment.');

        // 3. Reallocate to Ramanujan
        $updateRequest = new \Illuminate\Http\Request([
            'studentclass' => 'KG1',
            'student_id' => $studentId,
            'section' => 'Ramanujan',
        ]);
        $updateResp = $controller->updateStudentSectio($updateRequest);
        $this->assertEquals(200, $updateResp->getStatusCode());

        // 4. Verify updated student record
        $updatedStudent = DB::connection('dynamic')->table('student_registration')->where('id', $studentId)->first();
        $this->assertEquals('Ramanujan', $updatedStudent->section_name);
        $this->assertEquals($classRamanujan->id, $updatedStudent->class_id);
    }

    /**
     * Test edit registration saves updated section and updates class_id.
     */
    public function test_saveeditregistration_updates_section_and_class_id()
    {
        $classD = Classes::where('class_name', '01')->where('section_name', 'D')->first();
        $classKautilya = Classes::where('class_name', '01')->where('section_name', 'Kautilya')->first();

        $studentId = DB::connection('dynamic')->table('student_registration')->insertGetId([
            'scholar_no' => 'TEST_EDIT_' . rand(1000, 9999),
            'student_name' => 'Edit Student Test',
            'class_name' => '01',
            'section_name' => 'D',
            'class_id' => $classD->id,
            'session_name' => '2026_2027',
            'status' => 'r',
            'is_archived' => 0,
            'json_str' => json_encode(['classname' => '01', 'section_name' => 'D', 'class_id' => $classD->id]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $editData = [
            'id' => $studentId,
            'classname' => '01',
            'section_name' => 'Kautilya',
            'session_name' => '2026_2027',
            'studentname' => 'Edit Student Test',
            'scholar_no' => 'TEST_EDIT_' . rand(1000, 9999),
            'application_for' => 'CBSE',
            'form_number' => 'FN_ED_' . rand(100, 999),
            'student_dob' => '2019-01-01',
        ];

        $response = $this->post('/saveeditregistration', $editData);
        $response->assertStatus(302);

        $student = DB::connection('dynamic')->table('student_registration')->where('id', $studentId)->first();
        $this->assertEquals('Kautilya', $student->section_name);
        $this->assertEquals($classKautilya->id, $student->class_id);

        $json = json_decode($student->json_str, true);
        $this->assertEquals('Kautilya', $json['section_name']);
    }

    /**
     * Test add student registrations page renders section dropdown and strength widget container.
     */
    public function test_add_student_registration_page_renders_section_and_strength_widget()
    {
        $response = $this->get('/add-student-registrations');
        $response->assertStatus(200);
        $response->assertSee('id="section_container"', false);
        $response->assertSee('id="section_name"', false);
        $response->assertSee('id="section_strength_wrapper"', false);
        $response->assertSee('Current Student Strength', false);
        $response->assertSee('D (Temporary)', false);
    }

    /**
     * Test sectionAssign page renders Section D column in summary table.
     */
    public function test_section_assign_page_renders_section_d_column()
    {
        $response = $this->get('/sectionAssign');
        $response->assertStatus(200);
        $response->assertSee('D (Temporary)', false);
        $response->assertSee('Aryabhatta', false);
        $response->assertSee('Kautilya', false);
        $response->assertSee('Ramanujan', false);
    }
}
