<?php

namespace Tests\Feature;

use App\Http\Controllers\backend\TuitionCertificateController;
use Illuminate\Http\Request;
use Tests\TestCase;

class TuitionCertificateTest extends TestCase
{
    public function test_convert_number_to_words_without_intl_extension(): void
    {
        $controller = new class extends TuitionCertificateController {
            public function testWords($num)
            {
                return $this->convertNumberToWords($num);
            }
        };

        $this->assertEquals('Twenty-Five Thousand', $controller->testWords(25000));
        $this->assertEquals('Three Thousand', $controller->testWords(3000));
        $this->assertEquals('One Lakh Twenty-Five Thousand Three Hundred Fifty', $controller->testWords(125350));
        $this->assertEquals('Zero', $controller->testWords(0));
    }

    public function test_generate_tuition_certificate_renders_view_successfully(): void
    {
        $controller = new TuitionCertificateController();
        $request = Request::create('/generate-tuition-certificate', 'POST', [
            'studentname' => 'Rahul Sharma',
            'fathername' => 'Suresh Sharma',
            'student_dob' => '2010-05-15',
            'classname' => '10',
            'section_name' => 'A',
            'batch' => '2026',
            'scholar_no' => 'SCH1001',
            'total_credited' => '35000',
            'gender' => 'Male',
            'session' => '2025-2026',
        ]);

        $response = $controller->generate($request);
        $this->assertNotNull($response);
        $viewData = $response->getData();
        $this->assertEquals('Thirty-Five Thousand', $viewData['merged_data']['fees_paid_words']);
        $this->assertEquals('S/O', $viewData['merged_data']['gender_prefix']);
    }
}
