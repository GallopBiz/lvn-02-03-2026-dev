<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NextYearIdentificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_COOKIE['selectedYear'] = '2025_2026';
        Config::set('database.connections.dynamic.database', '2025_2026');
        DB::purge('dynamic');

        $user = User::first();
        if ($user) {
            $this->actingAs($user, 'web');
        }
    }

    /**
     * Test Next Year column in Enquiry List (/adminenquirylist)
     */
    public function test_admin_enquiry_list_has_next_year_column_and_no_year_column()
    {
        $uniqueCurrent = 'Curr Inq ' . rand(10000, 99999);
        $uniqueNext = 'Next Inq ' . rand(10000, 99999);

        $currId = DB::connection('dynamic')->table('inquiry_registration')->insertGetId([
            'application_for' => 'CBSE',
            'form_number' => 'FN_C_' . rand(100, 999),
            'student_name' => $uniqueCurrent,
            'class_name' => 'KG1',
            'session_name' => '2025_2026',
            'next_year' => 0,
            'status' => 'i',
            'is_delete' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $nextId = DB::connection('dynamic')->table('inquiry_registration')->insertGetId([
            'application_for' => 'CBSE',
            'form_number' => 'FN_N_' . rand(100, 999),
            'student_name' => $uniqueNext,
            'class_name' => 'KG1',
            'session_name' => '2025_2026',
            'next_year' => 1,
            'status' => 'i',
            'is_delete' => 0,
            'created_at' => now()->addMinutes(1),
            'updated_at' => now()->addMinutes(1),
        ]);

        $response = $this->withSession(['selectedYear' => '2025_2026'])->get('/adminenquirylist');
        $response->assertStatus(200);

        $content = $response->getContent();

        // Must contain Next Year header
        $this->assertStringContainsString('<th>Next Year</th>', $content);
        // Must NOT contain old Year header
        $this->assertStringNotContainsString('<th>Year</th>', $content);

        // Verify Yes / No presence
        $this->assertStringContainsString('Yes', $content);
        $this->assertStringContainsString('No', $content);

        // Pre Enquiry List (/admin-preenquiryform or preenquirylist)
        $preResponse = $this->withSession(['selectedYear' => '2025_2026'])->get(route('admin-pre-enquiryfeeslist'));
        $preResponse->assertStatus(200);
        $preContent = $preResponse->getContent();
        $this->assertStringContainsString('<th>Next Year</th>', $preContent);
        $this->assertStringNotContainsString('<th>Year</th>', $preContent);

        // Clean up
        DB::connection('dynamic')->table('inquiry_registration')->whereIn('id', [$currId, $nextId])->delete();
    }

    /**
     * Test Selection Process page (/selection-process) Next Year indicator
     */
    public function test_selection_process_displays_next_year_indicators()
    {
        $uniqueCurrent = 'Curr Sel ' . rand(10000, 99999);
        $uniqueNext = 'Next Sel ' . rand(10000, 99999);

        $currId = DB::connection('dynamic')->table('inquiry_registration')->insertGetId([
            'application_for' => 'CBSE',
            'form_number' => 'FN_SEL_C_' . rand(100, 999),
            'student_name' => $uniqueCurrent,
            'class_name' => 'KG1',
            'session_name' => '2025_2026',
            'next_year' => 0,
            'status' => 'i',
            'is_delete' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $nextId = DB::connection('dynamic')->table('inquiry_registration')->insertGetId([
            'application_for' => 'CBSE',
            'form_number' => 'FN_SEL_N_' . rand(100, 999),
            'student_name' => $uniqueNext,
            'class_name' => 'KG1',
            'session_name' => '2025_2026',
            'next_year' => 1,
            'status' => 'i',
            'is_delete' => 0,
            'created_at' => now()->addMinutes(1),
            'updated_at' => now()->addMinutes(1),
        ]);

        $response = $this->withSession(['selectedYear' => '2025_2026'])->get('/selection-process');
        $response->assertStatus(200);

        $content = $response->getContent();

        // Must contain Next Year header
        $this->assertStringContainsString('<th>Next Year</th>', $content);

        // Must contain Yes and No badges (without repeating "Next Year:")
        $this->assertStringContainsString('badge bg-warning text-dark font-weight-bold', $content);
        $this->assertStringContainsString('badge bg-secondary text-white', $content);
        $this->assertStringNotContainsString('Next Year: Yes', $content);

        // Must contain session indicators
        $this->assertStringContainsString('Admission for Next Session', $content);
        $this->assertStringContainsString('Current Session', $content);

        // Clean up
        DB::connection('dynamic')->table('inquiry_registration')->whereIn('id', [$currId, $nextId])->delete();
    }

    /**
     * Test Selection Process CSV export contains Next Year column
     */
    public function test_selection_process_csv_export_contains_next_year_column()
    {
        $uniqueNext = 'Next Csv ' . rand(10000, 99999);
        $formNo = 'FN_CSV_' . rand(100, 999);

        $nextId = DB::connection('dynamic')->table('inquiry_registration')->insertGetId([
            'application_for' => 'CBSE',
            'form_number' => $formNo,
            'student_name' => $uniqueNext,
            'class_name' => 'KG1',
            'session_name' => '2025_2026',
            'next_year' => 1,
            'status' => 'i',
            'is_delete' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession(['selectedYear' => '2025_2026'])->post(route('export_selection_process.csv'), [
            'form_number' => $formNo,
        ]);

        $response->assertStatus(200);
        $csvContent = $response->getContent();

        $this->assertStringContainsString('Next Year', $csvContent);
        $this->assertStringContainsString('Yes', $csvContent);

        // Clean up
        DB::connection('dynamic')->table('inquiry_registration')->where('id', $nextId)->delete();
    }
}
