<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InquiryListOrderingTest extends TestCase
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
     * Test that the inquiries list returns newest enquiry first.
     */
    public function test_admin_enquiry_list_displays_newest_enquiry_first()
    {
        // Insert a brand new test enquiry
        $uniqueStudentName = 'Brand New Test Enquiry ' . rand(10000, 99999);
        $newId = DB::connection('dynamic')->table('inquiry_registration')->insertGetId([
            'application_for' => 'CBSE',
            'form_number' => 'FN_NEW_' . rand(100, 999),
            'student_name' => $uniqueStudentName,
            'class_name' => '05',
            'session_name' => '2025_2026',
            'status' => 'i',
            'is_delete' => 0,
            'created_at' => now()->addMinutes(5),
            'updated_at' => now()->addMinutes(5),
        ]);

        $response = $this->get('/adminenquirylist');
        $response->assertStatus(200);

        $inquiries = $response->viewData('all_inquiry');
        $this->assertNotEmpty($inquiries);

        // The first inquiry in the collection must be our newest enquiry
        $this->assertEquals($newId, $inquiries->first()->id);
        $this->assertEquals($uniqueStudentName, $inquiries->first()->student_name);

        // Clean up test record
        DB::connection('dynamic')->table('inquiry_registration')->where('id', $newId)->delete();
    }
}
