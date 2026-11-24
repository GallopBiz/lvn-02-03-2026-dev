<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class TransferCertificateAutoFetchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        View::share('errors', new ViewErrorBag());
    }

    public function test_tc_create_page_contains_required_fields_with_ids_and_editable()
    {
        $response = $this->call('GET', route('transfercertificate.create'), ['year' => '2025_2026'], ['selectedYear' => '2025_2026']);

        $response->assertStatus(200);

        // Check for inputs with exact IDs
        $response->assertSee('id="tc_roll_no"', false);
        $response->assertSee('id="tc_section"', false);
        $response->assertSee('id="tc_total_working_days"', false);
        $response->assertSee('id="tc_working_days_present"', false);

        // Ensure roll_no and section are NOT readonly so users can edit them
        $this->assertStringNotContainsString('id="tc_roll_no" class="form-control" value="" readonly', $response->getContent());
        $this->assertStringNotContainsString('id="tc_section" class="form-control" value="" readonly', $response->getContent());

        // Conduct dropdown with options
        $response->assertSee('name="conduct"', false);
        $response->assertSee('id="tc_conduct"', false);
        $response->assertSee('value="--"', false);
        $response->assertSee('value="Good"', false);
        $response->assertSee('value="Very Good"', false);
        $response->assertSee('value="Excellent"', false);
    }

    public function test_student_details_fetches_roll_no_section_and_attendance()
    {
        // Student 11630 in 2025_2026 has roll_no 1102, section Kautilya, attendance 33
        $response = $this->call('GET', route('transfercertificate.student-details', ['id' => 11630]), ['year' => '2025_2026'], ['selectedYear' => '2025_2026']);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals(11630, $data['id']);
        $this->assertEquals('1102', $data['roll_no']);
        $this->assertEquals('Kautilya', $data['section_name']);
        $this->assertEquals(33, $data['total_working_days']);
        $this->assertEquals(33, $data['working_days_present']);
    }

    public function test_student_details_fetches_roll_for_student_10094()
    {
        // Student 10094 in 2025_2026 has roll_no 2201, section Aryabhatta
        $response = $this->call('GET', route('transfercertificate.student-details', ['id' => 10094]), ['year' => '2025_2026'], ['selectedYear' => '2025_2026']);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals(10094, $data['id']);
        $this->assertEquals('2201', $data['roll_no']);
        $this->assertEquals('Aryabhatta', $data['section_name']);
    }

    public function test_student_details_fetches_category_and_caste_inline()
    {
        // Student 10061 in 2025_2026: Cat GEN, Caste Brahmin -> General (Category) - Brahmin (Caste)
        $respGen = $this->call('GET', route('transfercertificate.student-details', ['id' => 10061]), ['year' => '2025_2026'], ['selectedYear' => '2025_2026']);
        $respGen->assertStatus(200);
        $this->assertEquals('General (Category) - Brahmin (Caste)', $respGen->json('caste'));

        // Student 10062 in 2025_2026: Cat OBC, Caste Dobhi -> OBC (Category) - Dobhi (Caste)
        $respObc = $this->call('GET', route('transfercertificate.student-details', ['id' => 10062]), ['year' => '2025_2026'], ['selectedYear' => '2025_2026']);
        $respObc->assertStatus(200);
        $this->assertEquals('OBC (Category) - Dobhi (Caste)', $respObc->json('caste'));

        // Student 10065 in 2025_2026: Cat SC, Caste Koli -> SC (Category) - Koli (Caste)
        $respSc = $this->call('GET', route('transfercertificate.student-details', ['id' => 10065]), ['year' => '2025_2026'], ['selectedYear' => '2025_2026']);
        $respSc->assertStatus(200);
        $this->assertEquals('SC (Category) - Koli (Caste)', $respSc->json('caste'));
    }

    public function test_tc_print_meta_alignment_and_dob_bold_formatting()
    {
        $tc = new \App\Models\TransferCertificate([
            'certificate_no' => 'TC/2025-2026/00001',
            'scholar_no' => '6499',
            'student_name' => 'Demo Student',
            'issue_date' => '2026-11-20',
            'tc_form_data' => [
                'name' => 'Demo Student',
                'mother_name' => 'Demo Mother',
                'father_name' => 'Demo Father',
                'date_of_birth' => '0007-08-03',
                'date_of_birth_words' => 'Third August Seven',
            ]
        ]);

        $html = View::make('backend.TransferCertificate.pdf.default', ['certificate' => $tc, 'isPdf' => false])->render();

        // 1. Meta left and right alignment
        $this->assertStringContainsString('class="tc-meta-left"', $html);
        $this->assertStringContainsString('class="tc-meta-right"', $html);
        $this->assertStringContainsString('TC No./ Date', $html);
        $this->assertStringContainsString('Scholar No.', $html);

        // 2. Bold applied to DOB in figures and words
        $this->assertStringContainsString('class="tc-value tc-dob-bold"', $html);
        preg_match_all('/<td class="tc-value\s*tc-dob-bold[^"]*">([^<]*)<\/td>/', $html, $dobMatches);
        $this->assertCount(2, $dobMatches[0]);
        $this->assertStringContainsString('03-Aug-0007', $dobMatches[1][0]);
        $this->assertStringContainsString('Third August Seven', $dobMatches[1][1]);

        // 3. Font size increased by 1px
        $this->assertStringContainsString('font-size: 12px;', $html);
        $this->assertStringContainsString('font-size: 13px;', $html);

        // 4. TC No./ Date contains sequence number / tc generate date in d-m-Y format (e.g. 00001/20-11-2026)
        $this->assertStringContainsString('00001/20-11-2026', $html);
        $this->assertStringNotContainsString('00001/20-Nov-2026', $html);
        $this->assertStringNotContainsString('TC/2025-2026/00001/20-Nov-2026', $html);

        // Date of issue in line item 21 still retains standard d-M-Y format
        $this->assertStringContainsString('Date of issue of certificate', $html);
        $this->assertStringContainsString('20-Nov-2026', $html);

        // 5. Test with simple sequence number e.g. '42'
        $tcSimple = clone $tc;
        $tcSimple->certificate_no = '42';
        $htmlSimple = View::make('backend.TransferCertificate.pdf.default', ['certificate' => $tcSimple, 'isPdf' => false])->render();
        $this->assertStringContainsString('42/20-11-2026', $htmlSimple);
    }
}
