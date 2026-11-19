<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentRegistrationRedirectAndReceiptTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Config::set('database.connections.dynamic.database', '2025_2026');
        DB::purge('dynamic');

        $user = User::first();
        if ($user) {
            $this->actingAs($user, 'web');
        }
    }

    /**
     * Test adding a new student redirects to student-registrations list page.
     */
    public function test_add_new_student_redirects_to_student_registrations_list()
    {
        $scholarNo = 'SCH_TEST_' . rand(10000, 99999);
        $postData = [
            'scholar_no' => $scholarNo,
            'form_number' => 'FN_' . rand(1000, 9999),
            'studentname' => 'Redirect Test Student',
            'classname' => 'KG1',
            'student_dob' => '2020-01-01',
            'application_for' => 'CBSE',
            'session_name' => '2025_2026',
            'category' => 'GEN',
            'religion' => 'HINDU',
            'student_caste' => 'General',
            'required_school_transport' => 'no',
        ];

        $response = $this->post('/save-student-registration', $postData);

        $response->assertStatus(302);
        $response->assertRedirect(route('student-registrations'));
    }

    /**
     * Test editing an existing student redirects to student-registrations list page.
     */
    public function test_edit_student_redirects_to_student_registrations_list()
    {
        $scholarNo = 'SCH_EDIT_' . rand(10000, 99999);
        $studentId = DB::connection('dynamic')->table('student_registration')->insertGetId([
            'scholar_no' => $scholarNo,
            'student_name' => 'Original Student Name',
            'class_name' => '05',
            'section_name' => 'Aryabhatta',
            'session_name' => '2025_2026',
            'mobile_number' => '9876543210',
            'status' => 'r',
            'is_archived' => 0,
            'json_str' => json_encode(['studentname' => 'Original Student Name']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $editPayload = [
            'id' => $studentId,
            'scholar_no' => $scholarNo,
            'studentname' => 'Updated Student Name',
            'classname' => '05',
            'section_name' => 'Aryabhatta',
            'session_name' => '2025_2026',
            'remarks' => 'Updated Remarks Value',
        ];

        $response = $this->post('/saveeditregistration', $editPayload);

        $response->assertStatus(302);
        $response->assertRedirect(route('student-registrations'));
    }

    /**
     * Test enquiry receipt 832 displays next session name 2026-2027.
     */
    public function test_enquiry_receipt_displays_next_session_for_next_year_student()
    {
        $response = $this->get('/enquiryrecipt/832');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('Admission for Session 2026-2027', $content);
        $this->assertStringContainsString('<strong>Session:</strong> 2026-2027', $content);
        $this->assertStringContainsString('500', $content);
        $this->assertStringContainsString('Admission Form', $content);
    }

    /**
     * Test enquiry receipt displays current session for regular current session student.
     */
    public function test_enquiry_receipt_displays_current_session_for_current_year_student()
    {
        $id = DB::connection('dynamic')->table('inquiry_registration')->insertGetId([
            'application_for' => 'CBSE',
            'form_number' => 'FN_CURR_' . rand(100, 999),
            'student_name' => 'Current Session Student',
            'class_name' => '02',
            'session_name' => '2025_2026',
            'next_year' => 0,
            'status' => 'i',
            'json_str' => json_encode([
                'student_name' => 'Current Session Student',
                'fathername' => 'Current Father',
                'admission_session' => '2025-2026',
                'next_year' => 0,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/enquiryrecipt/' . $id);

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('Admission for Session 2025-2026', $content);
        $this->assertStringContainsString('<strong>Session:</strong> 2025-2026', $content);
        $this->assertStringContainsString('500', $content);
    }
}
